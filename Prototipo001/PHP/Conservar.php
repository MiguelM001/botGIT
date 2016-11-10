<?php
/*
NOTA en la funcion dominio sustituir por la expresion regular 
*/
class Conservar{
/////////////////////////////ATRIBUTOS/////////////////////////////
	private $arrValidar;#restricciones
	private $arrExternos;#enlaces externos
	private $indice;#enlaces externos indice
	private $semilla;

///////////////////////////////////////////////////////////////////
	public function __construct($sSemillas){
	
	    $this->indice= 0;
		$this->arrExternos= array();
		$this->semilla= $sSemillas;
		$this->arrValidar=  array(
			0 => "#",
			1 => "?",
			2 => ".pdf",	
			3 => ".mp3",
			4 => ".mp4",
			5 => ".jpg",
			6 => ".png",
			7 => ".gif",
			8 => ".doc",
			9 => "index",
		);
		
	}#fin_constructor
///////////////////////////////////////////////////////////////////
///////GUARDA Y RETORNA MATRIZ POR NIVELES, ENLACES VALIDOS////////
///////////////////////////////////////////////////////////////////
	public function almacenar( $arreglo, $nivel, $enlaces ){
		
		$cont= 0;
		
		for( $url= 0; $url < count( $enlaces ); $url++ ){
			# Don't add exlcuded links to $spider_array
			
			if( !$this->validar( $arreglo, $enlaces[$url]) ){
			
				$arreglo[$nivel][$cont++] = $enlaces[$url];// Su propio contador
			
			}#fin_if
			
		}#fin_for
		
		return $arreglo;
		
	}#fin_archivar
///////////////////////////////////////////////////////////////////
	public function validar($spider_array, $link){
		
		#Initialization
		$excluir = false;  
#---------------------------------------------------------- 	
		#Exclude links that are JavaScript commands
	    $excluir= $this->validarJS( $link,  $excluir); 
#---------------------------------------------------------- 	
		// Exclude redundant links
		$excluir= $this->validarRedundancias( $spider_array, $link,  $excluir );
#---------------------------------------------------------- 			
		// Exclude links found in $exclusion_array
		$excluir= $this->validarEnlaces($link,  $excluir);
#---------------------------------------------------------- 
	    $excluir= $this->validarExternos($link,  $excluir);
#---------------------------------------------------------- 
		
		//OJO VALIDAR DESBORDADOS DE LA BASE DE DATOS
		
#----------------------------------------------------------

		//¿¿¿¿ OJO VALIDAR DOMINIOS org ETC ????

#----------------------------------------------------------

		return $excluir;
		
	}#fin_validar
///////////////////////////////////////////////////////////////////
	#Descartar enlaces de comando js
	public function validarJS( $link,  $excluir ){
			
		if(stristr($link, "javascript")){
			
			echo "Ignored javascript  link: $link\n";
			$excluir = true;
				
		}#fin_if
			
		return $excluir;
			
	}#fin_descartarJf
///////////////////////////////////////////////////////////////////
	public function validarRedundancias($spider_array, $link,  $excluir){
		  
		for($i=0; $i < count($spider_array); $i++){
				
			for($j=0; $j < count($spider_array[$i]); $j++){
	
				if( $link ==  $spider_array[$i][$j]  ){
		           
					$excluir= true;
					echo "Ignorar  enlace redundante: $link\n";
					$i= count($spider_array);# final del bucle
					$j= count($spider_array[$i]);# final del bucle
				
				}#fin_if
		
			}#fin_for
				
		}#fin_for
		
		return $excluir;
			
	}#fin_descartarRedundancias
///////////////////////////////////////////////////////////////////	
	public function validarEnlaces( $link,  $excluir ){
			
		// Exclude links found in  $this->arrValidar
		for($xx=0; $xx<count( $this->arrValidar ); $xx++){
			
			if(stristr($link,  $this->arrValidar[$xx])){
				
				echo "ignorar enlace: $link\n";
				$excluir= true;
					
			}#fin_if
				
		}#fin_for    
			
		return $excluir;
		
	}#fin_descartarEnlaces
///////////////////////////////////////////////////////////////////		
# RESTRICCION DE LIMITE DE FRONTERA LA ARANIA NO SALE DEL DOMINIO
///////////////////////////////////////////////////////////////////	
	public function validarExternos($link,  $excluir){
		
		if($this->dominio($link)!= $this->dominio($this->semilla)){
		
			echo "Ignorar enlace externo: $link\n";#crear un arreglo para guardarlos
			$excluir= true;
			
		    if(!$this->descartarExternos($link)){
			
				$this->arrExternos[$this->indice++]= $link;#OJOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOO
				echo "Entra para llenar enlaces externos"."\n\n";
				
			}#end_if
			
		}#end_if
		
		return $excluir;
		
	}#fin_descartarExternos
///////////////////////////////////////////////////////////////////	
	//OJO VALIDAR DESBORDADOS DE LA BASE DE DATOS
	//OJO VALIDAR DESBORDADOS DE LA BASE DE DATOS
	//OJO VALIDAR DESBORDADOS DE LA BASE DE DATOS
	//OJO VALIDAR DESBORDADOS DE LA BASE DE DATOS
	//OJO VALIDAR DESBORDADOS DE LA BASE DE DATOS
//////////////////////////////////////////////////////////////////

	public function descartarExternos($link){

##############################################################################################
		$excluir= $this->validarRedundanciasVector($this->arrExternos, $link);
##############################################################################################
		$excluir= $this->validarEnlaces($link,  $excluir);

		return $excluir;
	
	}#descartarExternos
///////////////////////////////////////////////////////////////////
	public function validarRedundanciasVector($arreglo, $link){

		$excluir= false;
	
		for($j=0; $j < count($arreglo); $j++){
	
			if( $link ==  $arreglo[$j]  ){
		
				$excluir= true;
				echo "Ignorar  enlace redundante: $link\n";
				$j= count($arreglo);# final del bucle
				
			}#fin_if
		
		}#fin_for
					
		return $excluir;
			
	}#fin_descartarRedundancias
///////////////////////////////////////////////////////////////////	
# DEVUELVE EL DOMINIO 
///////////////////////////////////////////////////////////////////
	public function dominio($enlace){#USAR PARA GUARDAR EN BD
	
	    // Remove protocol from $url
    $enlace = str_replace("http://", "", $enlace);
    $enlace = str_replace("https://", "", $enlace);
    
    // Remove page and directory references
    if(stristr($enlace, "/"))
        $enlace = substr($enlace, 0, strpos($enlace, "/"));
  		
		return $enlace;
		
	}#fin_dominio
///////////////////////////////////////////////////////////////////

	public function contarNiveles($directorio){
	
		$directorio= str_replace("http://", "", $directorio);
		$directorio= str_replace("https://", "", $directorio);

		$directorio= stristr($directorio, "/");// elimina hasta 

		if(stristr($directorio[strripos($directorio, "/")], $directorio[strlen($directorio)-1])) 
			$directorio= substr($directorio, 0, -1); 
			$cont= 0; 
			for($i=0; $i <  strlen($directorio); $i++){
				if(stristr($directorio[$i], "/"))
					$cont++;
			}#fin_for
		
		return $cont;
	
	}#fin_contarNiveles

	public function setEnlacesExternos( ){return $this->arrExternos;}#fin_setEnlacesExternos
}#fin_class
?>