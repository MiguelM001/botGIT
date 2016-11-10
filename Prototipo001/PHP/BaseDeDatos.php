<?php
#include("Conservar.php");
/*
NOTA cuando el $estado es

 1 = encendido-> revisado
 0 = apagado->   no revisado
 
NOTA Tener cuidado con el id que se va a repetir cuando 
	  encuentre nuevos links o visite nuevas paginas 
	  cear dos variable miembros para aumentar

*/
class BD{
////////////////////////////ATRIBUTOS/////////////////////////////
	private $host;
	private $usuario;
	private $contrasenia;
	private $baseDeDatos;
	private $mysqli;
	private $dominio;
	private $estado;
	private $ultimoId;
	private $arrAux;
///////////////////////////////////////////////////////////////////
	public function __construct(){
	    $this->dominio= "";
	    $this->mysqli="";
		$this->host="localhost";
		$this->usuario="root";
		$this->contrasenia="heterismo";
		$this->baseDeDatos="BOTGIT";
		$this->estado= 0;# estado apagado por defecto
		$this->ultimoId= 1;
		$this->arrAux= array();
	}#fin_constructor
///////////////////////////////////////////////////////////////////
	public function conectarBD(){
		#if($cedulamed &&  $cedulapac  &&  $numdep  &&  $pisodep && $nomcon){
		$this->mysqli = new mysqli($this->host, $this->usuario, $this->contrasenia, $this->baseDeDatos);
		#}#fin_if
	}#fin_conectar
///////////////////////////////////////////////////////////////////	
	public function llenarDominio($arreglo){
	
	
	for($i=0; $i < count($arreglo); $i++){
		
		if(isset($arreglo[$i]) && isset($this->estado)){
#------------------------------------------------------------------
			$sql= 'SELECT MAX(iddom) FROM Dominio';# el ultimo numero id de la tabla
			$consulta= $this->mysqli->query($sql);
			$aux= $consulta->fetch_row();
	
			$id= $aux[0];
	
			if(!$id)#si $id esta vacio llenarlo con 1
				$id= 0;
#------------------------------------------------------------------
			if($id){
				$id++;
				$this->mysqli->query("INSERT INTO Dominio(iddom,enlacedom, suichedom) 
									VALUES('$id','$arreglo[$i]', '$this->estado')");
			}#fin_if
			
	    }#fin_if
		
    }#fin_for
	}#fin_llenarDominio
///////////////////////////////////////////////////////////////////	
	public function desbordarDominio(){
	
		$sql= "SELECT * FROM Dominio";
		$consulta= $this->mysqli->query($sql);
		return $consulta;
	
	}#fin_desbordarDominio
/////////////////////////////////////////////////////////////////
	public function llenarNiveles($enlace, $dominio , $numero){
	
		if($numero >= 1){
		  
		   echo "OJOOOOOOOOOOO ENTRO EN LLENAR NIVELES NUMERO ".$numero."\n\n";
			//$nPadre= $numero - 1;
			$sql= 'CREATE TABLE Nivel'.$numero.'(enlacen'.$numero.'  VARCHAR(270), 
			            enlacedom VARCHAR(90), PRIMARY KEY (enlacen'.$numero.' ), 
			            FOREIGN KEY (enlacedom) REFERENCES Dominio(enlacedom)  )';

			$this->mysqli->query($sql);
#-----------------------------------------------------------------
			//$referencia= $this->desbordarEnlacesNiveles($enlace, $nPadre);
#-----------------------------------------------------------------
			if($enlace){
			
				$this->mysqli->query("INSERT INTO Nivel$numero(enlacen$numero,  enlacedom) 
											VALUES('$enlace', '$dominio')");
											
			}#fin_if
		
#-----------------------------------------------------------------
			
			//if($enlace && $dominio && $numero && $id)#si ninguno de estos elementos esta vacio llenar BD
						
		}#fin_if
		
		// dominio par el primer nivel
	}#fin_llenarNiveles
///////////////////////////////////////////////////////////////////	
	/*
	public function desbordarEnlacesNiveles($enlace, $n){
	
		$sql= 'SELECT enlacen'.$n.' FROM Nivel'.$n;
		$consulta= $this->mysqli->query($sql);
		
		if($consulta){
		
			while( $arreglo= $consulta->fetch_row( ) ){
	
				if(stristr($enlace,  $arreglo[0])){
					$referencia=  $arreglo[0];
					break;
				
				}//fin_if
	
		}#fin_while
		}#fin_if
		
		return $referencia;
	
	}#fin_desbordarEnlacesNiveles
	
*/
///////////////////////////////////////////////////////////////////	
	public function desbordarNiveles(){
	
	}//fin_desbordarNiveles
///////////////////////////////////////////////////////////////////
	public function cambiarEstado($dominio){
		
		$sql = "UPDATE Dominio SET suichedom='1' WHERE enlacedom='$dominio'";
		$consulta= $this->mysqli->query($sql);
	
	}#fin_cambiarEstado
///////////////////////////////////////////////////////////////////
}#fin_class

?>