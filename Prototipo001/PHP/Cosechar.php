<?php
/*
NOTA en la  funcion cosechar 
*/
include("Conectar.php");
///////////////////////////////////////////////////////////////////
class Recolector{
/////////////////////////////ATRIBUTOS/////////////////////////////
	private $retardar;
	private $direccionURL;
	#--------------------atributos Conectar-------------------------		
	private $nombreBot;
	private $tiempoFuera;
	private $archivoCookie;
	#---------------------------------------------------------------
///////////////////////////////////////////////////////////////////
	public function __construct(  $sNombreBot, $iRetardar ){

		$this->retardar= $iRetardar;
		$this->direccionURL="";
		#--------------------atributos Conectar-------------------------		
		$this->tiempoFuera= 60;//1 minuto
	    $this->nombreBot=   $sNombreBot;
		$this->archivoCookie=  "c:\cookie".$sNombreBot.".txt";
		#---------------------------------------------------------------
		
	}#fin_constructor
///////////////////////////////////////////////////////////////////

	public function cosechar($semilla){
		
		#instanciacion sesion curl
		$estaSesion= new Conectar($semilla,               $this->nombreBot, 
		                          $this->tiempoFuera, $this->archivoCookie);
		
		$arrEnlace = array();
		# Get page base for $semilla
		$dominio = $this->ObtenerDireccionRaiz($semilla);
		# Download webpage
		sleep($this->retardar); 
//////////////////////////////////////////////////////////////////////////////		
		$pagina = $estaSesion->sesionCurl();#SESION CURL
/////////////////////////////////////////////////////////////////////////////
		preg_match_all("(<a(.*)</a>)siU",  $pagina, $data);
		
		$etiquetas = $data[0]; 
		#Put http attributes for each tag into an array
		for($xx=0; $xx<count($etiquetas); $xx++){
			
			$arrAux = $this->obtenerAtributo($etiquetas[$xx]);
		
			foreach( $arrAux as $elemento ){
			
				$directorio= $elemento;
				$reDireccion = $this->resolverDireccion($directorio, $dominio);
				$arrEnlace[] = $reDireccion;
				#echo "Cosechado: "." ".$reDireccion."\n";
				
			}#fin_foreach
			
		}#fin_for
		
		return $arrEnlace;
		
	}#fin_cosechar
///////////////////////////////////////////////////////////////////
	public function ObtenerDireccionRaiz($url){

		$slash_position = strrpos($url, "/");
	
		if ($slash_position>8)
		
			$page_base = substr($url, 0, $slash_position+1);  	// "$slash_position+1" to include the "/".
	
		else{
	
			$page_base = $url;  	// $url is already the page base, without modification.
			if($slash_position!=strlen($url))
				$page_base=$page_base."/";
		
		}#fin_if
	
		# If the page base ends with a \\, replace with a \
		$last_two_characters = substr($page_base, strlen($page_base)-2, 2);
	
		if($last_two_characters=="//")
			$page_base = substr($page_base, 0, strlen($page_base)-1);
		
		return $page_base;

	}#fin_ ObtenerDireccionRaiz
///////////////////////////////////////////////////////////////////
	public function obtenerAtributo( $tag ){
		# Use Tidy library to 'clean' input
		$cleaned_html = $this->tidy_html($tag);//Listo
		# Remove all line feeds from the string
		$cleaned_html = str_replace("\r", "", $cleaned_html);   
		$cleaned_html = str_replace("\n", "", $cleaned_html);
		$arrLimpiar= array();	
		
		for($i=0; $i < count( $cleaned_html ); $i++ ){
		
			preg_match_all("(href=\"(.*)\")siU",  $cleaned_html, $data);// no importa si es mayuscula o minuscula
		//----------------------------------------------------------------------------------- mejorado
			for($j= 0; $j < count($data[0]); $j++){
				$limpiar= $data[0][$j]."\n";
				$limpiar= str_replace("HREF", "", $limpiar);
				$limpiar= str_replace("href", "", $limpiar);
				$limpiar= str_replace("=", "", $limpiar);
				$limpiar= str_replace("\"", "", $limpiar);
				$arrLimpiar[]= $limpiar;
			}#fin_for
			
		}#fin_for
		//----------------------------------------------------------------------------------- mejorado 
		return $arrLimpiar;
	}#fin_obtenerAtributo
///////////////////////////////////////////////////////////////////
	public function tidy_html( $input_string ){
		// Detect if Tidy is in configured
		if( function_exists( 'tidy_get_release' ) ){
			# Tidy for PHP version 5
			if( substr( phpversion( ), 0, 1 ) == 5 ){
			
				$config = array(
				'uppercase-attributes' => true,
				'wrap'                 => 800);
				$tidy = new tidy;
				$tidy->parseString($input_string, $config, 'utf8');
				$tidy->cleanRepair();
				$cleaned_html  = tidy_get_output($tidy);  
				
			}#fin_if
			
		}else{
		
		# Tidy not configured for this computer
		$cleaned_html = $input_string;
		
		}#fin_if
		
		return $cleaned_html;
		
	}#fin_tidy_html
///////////////////////////////////////////////////////////////////
	public function resolverDireccion(	$directorio, $dominio	){ # directorio y pagina base $directorio, $dominio
		#---------------------------------------------------------- 
		#CONDITION INCOMING directorio ADDRESS
		#
		$directorio = trim($directorio);#limpiar espacios en blanco
		$dominio = trim($dominio);#limpiar espacios en blanco
		$dominio = trim($dominio);#limpiar espacios en blanco ojo
		#si no tiene colocar al final de la cadena un "/"
		if( (strrpos($dominio, "/")+1) != strlen($dominio) )
			$dominio = $dominio."/";
		#eliminar caracteres extraños del directorio
		$directorio = str_replace(";", "", $directorio);#limpiar caracter  ;		
		$directorio = str_replace("\"", "", $directorio);#limpiar caracter "	
		$directorio = str_replace("'", "", $directorio);#limpiar caracter '
		#une el dominio con el directorio
		$this->direccionURL= $dominio.$directorio;
		$this->direccionURL = str_replace("/./", "/",  $this->direccionURL);
		$suiche= 0;
		#---------------------------------------------------------- 
		$suiche= $this->resolverDominio($directorio, $dominio, $suiche);
		#---------------------------------------------------------- 
		$suiche=  $this->resolverSuperiores($directorio, $dominio, $suiche);
		#---------------------------------------------------------- 
		$suiche= $this->resolverRaiz($directorio, $dominio, $suiche);
		#---------------------------------------------------------- 
		$this->resolverCompletos($directorio, $suiche);
		#---------------------------------------------------------- 
		# ADD PROTOCOL IDENTIFIER IF NEEDED
		#
		if( (substr( $this->direccionURL, 0, 7)!="http://") && (substr( $this->direccionURL, 0, 8)!="https://") )
			 $this->direccionURL = "http://". $this->direccionURL;

		return  $this->direccionURL;  

	}#fin_resolverDireccion
///////////////////////////////////////////////////////////////////
	public function resolverDominio($directorio, $dominio, $suiche){
		# LOOK FOR REFERENCES TO THE BASE DOMAIN ADDRESS
		if($suiche == 0){
			# Use domain base address if $directorio starts with "/"
			if (substr($directorio, 0, 1) == "/"){
				//find the left_most "."
//////////////////////////////////BORRADO EN PYTHON///////////////////////////////////				
				$pos_left_most_dot = strrpos($dominio, ".");
			#Find the left-most "/" in $dominio after the dot 
			for($xx=$pos_left_most_dot; $xx<strlen($dominio); $xx++){#no veo que sirva para algo
				if( substr($dominio, $xx, 1)=="/")
					break;
			}#fin_for
//////////////////////////////////BORRADO EN PYTHON///////////////////////////////////
			$domain_base_address =  $this->obtenerDominio($dominio);//OJOOOOO LISTO
			$this->direccionURL = $domain_base_address.$directorio;
			$suiche=1;
			
			}#fin_if
			
		}#fin_if
		
		return $suiche;
		
	}#fin_resolverDominio
///////////////////////////////////////////////////////////////////
	public function resolverSuperiores($directorio, $dominio, $suiche){
		# LOOK FOR REFERENCES TO HIGHER DIRECTORIES
		#
		if($suiche==0){
		if (substr($directorio, 0, 3) == "../"){
		$dominio=trim($dominio);
		$right_most_slash = strrpos($dominio, "/");
		//remove slash if at end of $page base
		if($right_most_slash==strlen($dominio)-1){
		$dominio = substr($dominio, 0, strlen($dominio)-1);
		$right_most_slash = strrpos($dominio, "/");
		}#fin_if
		if($right_most_slash<8)
		$unadjusted_base_address = $dominio;
		$not_done=TRUE;
		while($not_done){
		//bring page base back one level
		list($dominio, $directorio) = $this->moverUnNivelAtras($dominio, $directorio);//OJOOO LISTO
		if(substr($directorio, 0, 3)!="../")
		$not_done=FALSE;
		}#fin_while
		if(isset($unadjusted_base_address))		
		$this->direccionURL = $unadjusted_base_address."/".$directorio;
		else
		$this->direccionURL = $dominio."/".$directorio;
		$suiche= 1;
		}#fin_if
		}#fin_if
		return $suiche;
	}#fin_resolverSuperiores
///////////////////////////////////////////////////////////////////
public function resolverRaiz($directorio, $dominio, $suiche){

# LOOK FOR REFERENCES TO BASE DIRECTORY
#
if($suiche==0){
if (substr($directorio, 0, "1") == "/"){
$directorio = substr($directorio, 1, strlen($directorio)-1);	// remove leading "/"
$this->direccionURL = $dominio.$directorio;			// combine object with base address
$suiche=1;
}
} 
return $suiche;
}#fin_ resolverRaiz
public function resolverCompletos($directorio, $suiche){// argumentos= ojo$directorio, $dominio, $suiche

# LOOK FOR REFERENCES THAT ARE ALREADY ABSOLUTE
#
if($suiche==0){
if (substr($directorio, 0, 4) == "http"){
$this->direccionURL = $directorio;
$suiche= 1;
}#fin_if
}#fin_if
return $suiche;
}#fin_resolverCompletos
///////////////////////////////////////////////////////////////////
public function obtenerDominio($dominio){
for ($pointer=8; $pointer<strlen($dominio); $pointer++){
if (substr($dominio, $pointer, 1)=="/"){
$domain_base=substr($dominio, 0, $pointer);
break;
}#fin_if
}#fin_for
/////////////////////////////BORRADO EN PYTHON/////////////////////////////////////////
$last_two_characters = substr($dominio, strlen($dominio)-2, 2);// OJO ESTO NADA HACE
if($last_two_characters=="//")// OJO ESTO NADA HACE
$dominio = substr($dominio, 0, strlen($dominio)-1);// OJO ESTO NADA HACE
/////////////////////////////BORRADO EN PYTHON/////////////////////////////////////////
return $domain_base;
}#fin_obtener
///////////////////////////////////////////////////////////////////
public function moverUnNivelAtras($dominio, $object_source){
// bring page base back one leve
$right_most_slash = strrpos($dominio, "/");
$new_page_base = substr($dominio, 0, $right_most_slash);
// remove "../" from front of object_source
$object_source = substr($object_source, 3, strlen($object_source)-3);
$return_array[0]=$new_page_base;
$return_array[1]=$object_source;
return $return_array;
}
///////////////////////////////////////////////////////////////////
}#fin_class
?>