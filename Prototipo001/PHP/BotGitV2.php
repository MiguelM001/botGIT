<?php
/*
NOTA  hay que hacer que los elementos de l BD se actualicen cada vez
NOTA cuando termine el primer desbordamiento vaciar de nuevo la BD con la info actualizada
NOTA para cada elemento de $dominioBD[$cont++] crear nuevo objeto de todas las clases menos la clase cosechar
*/
include("Cosechar.php");
include("Conservar.php");
include("BaseDeDatos.php");
///////////////////////////////////////////////////////////////////////////////////////
function recorrerNiveles($botGIT, $estaCosecha, $estaReserva, $nuevaBD, $dominioBD){

	$nivel=0;# nivel de la pagina raiz
	
	while(isset($botGIT[$nivel])){
		
		$sNivel= $nivel+1;// BORRAR SI NO IMPRIMO
		
		for($xx=0; $xx<count($botGIT[$nivel]); $xx++){
		
			$sPagina= $xx + 1;// BORRAR SI NO IMPRIMO
		
			echo "\n\NIVEL ".$sNivel." \PAGINA ".$sPagina."\n\n";// BORRAR SI NO IMPRIMO
		
			unset($cosecha);
			$cosecha= $estaCosecha->cosechar($botGIT[$nivel][$xx]);//areglo dominio $dominioBD[ ] for =OJOOOOOOOOOOOOOOOOOOOOOOOOOOO
			
			if(isset($cosecha)){#validar que no este vacio
				$siguienteNivel=  $nivel+1;
				$botGIT=  $estaReserva->almacenar( $botGIT, $siguienteNivel, $cosecha );#guarda enlaces en el siguiente nivel
		
				almacenarEnBD($botGIT, $estaReserva, $nuevaBD, $dominioBD,	$sNivel);
			
			}else
				echo "\n\nFallo! de La sesion cURL ( sin conexion a internet )\n\n";
		
		}#fin_for
		
		++$nivel;
		
	}#fin_while
	
}#fin_recorrerNiveles
//////////////////////////////////////////////////////////////////////////////////////
 function almacenarEnBD($botGIT, $estaReserva, $nuevaBD, $dominioBD, $penetracion){
	
	$filtarDominio= array();
    $enDominio=     array();
	$externos=      array();
	
	$externos= $estaReserva->setEnlacesExternos( );# arreglo de enlaces externos
	//echo "\n\COSECHA\n\n";
	
	$cont= 0;#contador para elementos de $filtarDominio
	for($i=0; $i < count($botGIT); $i++){

		//echo "nivel ".$i."\n\n";
	
		for($j= 0; $j < count( $botGIT[$i] ); $j++){
			
			//echo "nivel directorio ".$estaReserva->contarNiveles($botGIT[$i][$j])."\n\n";//BORRAR
			//echo "cosecha ".$botGIT[$i][$j]."\n\n";
			$numero= $estaReserva->contarNiveles($botGIT[$i][$j]);
			$nuevaBD->llenarNiveles($botGIT[$i][$j], $dominioBD, $numero);// OJO cuidado con dominioBD[$portal]
			#-------------------------------------------------
			$filtarDominio[$cont++]= $botGIT[$i][$j];
		
		}#fin_for
	
	}#fin_for

	//echo "\n\EXTERNOS\n\n";

	for($i=0; $i < count($externos); $i++){
	
		//echo "externos ".$externos[$i]."\n\n";
		$filtarDominio[$cont++]= $externos[$i];
	//enviar con numero del nivel para meterlo en la tabla
		
	}#fin_for

	#ARREGLO BD DOMINIO
	$cont= 0;
	//echo "\n\DOMINIO\n\n";
///////////////////////////////////// EL FILTRO////////////////////////////////////
	for($i=0; $i < count($filtarDominio); $i++){
	
	    preg_match("(^(http(s?)\:\/\/)(.*)(\.com|net|org))siU", $filtarDominio[$i], $data);// OJO CON ESTO ORG
		//preg_match("(^(http(s?)\:\/\/)(.*)(\.gob.ve|\.mil.ve|\.edu.ve|\.tec.ve))siU", $filtarDominio[$i], $data);// OJO CON ESTO ORG
		if(isset($data[0])){
		
			//echo "dominio puro ".$data[0]."\n\n";
			if(!$estaReserva->validarRedundanciasVector($enDominio, $data[0]))
			$enDominio[$cont++]= $data[0];
			
		}#fin_if
		
	}#fin_for

	//echo "\n\DOMINIO PURO\n\n";

	//for($i=0; $i < count($enDominio); $i++)
		//echo "enDominio ".$enDominio[$i]."\n\n";

	////////////////////////////////////LLENAR BD////////////////////////////////////////
	if(isset($enDominio)){#si $enDominio esta lleno
		$nuevaBD->llenarDominio($enDominio);
	}#fin_if
	
	unset($externos); #cuando repita
	unset($filtarDominio); #cuando repita
	unset($enDominio); #cuando repita
    
	 }#fin_ almacenarEnBD
/////////////////////////////////////INICIO///////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////
$nombreBot= "botGIT (www.vencert.gob.ve)";
#"Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:2.0) Treco/20110515 Fireweb Navigator/2.4";
$botGIT= array();
$esperar= 0;

//////////////////////INICIAR BD////////////////////////////
$iniciarBD= new BD();
$iniciarBD->conectarBD();
//////////////////DESBORDAR BD////////////////////////
$consulta= $iniciarBD->desbordarDominio();//$dominioBD OJO LA TABLA Dominio HA DE TENER AL MENOS 1 ELEMENTO 
$cont=0;
 while ($fila = $consulta->fetch_row()){
	$dominioBD[$cont++]= $fila[1];#SEMILLAS fila[0] -> los ID
 }#fin_while
//////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////
#$dominioBD=  "http://www.schrenk.com"; //desbordar dominios aqui en arreglo
//////////////////////////////////////////////////////////
/*
$tiempoF=   25;
$aCookie=   "c:\cookie.txt";
*/
//$estaSesion= new Conectar($nombreBot, $tiempoF, $aCookie,  $url);
//$pagina= $estaSesion->sesionCurl();

$estaCosecha= new Recolector($nombreBot, $esperar);// for para varios bots???????
//FOR( count dominioBD)

###################################################
#OJO VALIDAR ESTADO DE LA BASE DE DATO ANTES DE ENTRAR
###################################################
$cosecha= $estaCosecha->cosechar($dominioBD[0]);//areglo dominio $dominioBD[ ] for =OJOOOOOOOOOOOOOOOOOOOOOOOOOOO

if($cosecha){#validar que no este vacio

////////////////////////////////////GENERAR DIRECTORIO DE LA SEMILLA////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////////////
	$nuevaBD= new BD();
	$nuevaBD->conectarBD();
	echo "\n\NIVEL 0 \n\n";// BORRAR SI NO IMPRIMO
	$estaReserva= new Conservar($dominioBD[0]);// EXACTO PJOO 
	##################################################
	$botGIT=  $estaReserva->almacenar( $botGIT, 0, $cosecha );# matriz de enlaces internos a la pagina actual
	#unset($cosecha); cuando repita
//////////////////////////////////////////////////////////////////////////////////////LUEGO
	almacenarEnBD($botGIT, $estaReserva, $nuevaBD, $dominioBD[0], 1);
////////////////////////////////////CICLO DE RECORRIDO/////////////////////////////////////////////////////////////
	recorrerNiveles($botGIT, $estaCosecha, $estaReserva, $nuevaBD, $dominioBD[0]);

}else#fin de validar la sesion curl

	echo "\n\nFALLO! DE LA SESION cURL",
	       "\n\nAsegurese que proporciona al menos 1 dominio a la base de datos",
		   "\n\nRevise su conexion a internet\n\n ";
/////////////////////////////////////FINAL///////////////////////////////////////////
?>