<?php
class Conectar{
/////////////////////////////ATRIBUTOS/////////////////////////////
	private $portal;
	
	private $nombreBot;
	private $tiempoFuera;
	private $archivoCookie;
	
///////////////////////////////////////////////////////////////////
	public function __construct($sPortal,  $sNombreBot, $iTiempo, $sArchivoCookie){#$sNombreBot, $iTiempo, $sArchivoCookie,

	    $this->portal=        $sPortal;
		$this->tiempoFuera=   $iTiempo;
	    $this->nombreBot=     $sNombreBot;
		$this->archivoCookie= $sArchivoCookie;
		
	}#fin_constructor
///////////////////////////////////////////////////////////////////	
	public function sesionCurl(){
	
		# Inicializar secion curl
		$sc = curl_init();
	
		# Opciones CURL/PHP
		curl_setopt($sc , CURLOPT_COOKIEJAR,  $this->archivoCookie);   #  Define fichero que guarda las cookies
		curl_setopt($sc , CURLOPT_COOKIEFILE, $this->archivoCookie);   #  Define fichero que guarda las cookies
		curl_setopt($sc , CURLOPT_TIMEOUT,      $this->tiempoFuera);   #  Tiempo fuera a ejecutar funciones curl
		curl_setopt($sc , CURLOPT_USERAGENT,    $this-> nombreBott);   #  Nombre del bot a registrar en los logs
		curl_setopt($sc , CURLOPT_URL,               $this->portal);   #       Pagina web que se ha de descargar 
		curl_setopt($sc , CURLOPT_VERBOSE,                   FALSE);    #  Reducir al minimo los logs o registros
		// vervose TRUE con  CURLOPT_STDERR. para escribir la salida se usa con fopen wr
		curl_setopt($sc , CURLOPT_SSL_VERIFYPEER,            FALSE);    #            Certificados SSL no retornar
		curl_setopt($sc , CURLOPT_FOLLOWLOCATION,             TRUE);    #   Seguir redirecciones php de la pagina
		curl_setopt($sc , CURLOPT_MAXREDIRS,                     4);    #          Numero maximo de redirecciones
		curl_setopt($sc , CURLOPT_RETURNTRANSFER,             TRUE);    # Retornar en cadena de caracteres string
		# inicializar variable de valores a retornar
		$retornarValores   = curl_exec($sc ); 
		# Cerrar sesion curl
		curl_close($sc);
		# Retornar valores en string
		return $retornarValores ;
	}#fin_sesionCurl
	
}#fin_class
?>