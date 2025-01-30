<?php
require_once 'config/DB.php';
class ModeloDB
{
	public $conexion;

	public function __construct()
	{
		//Utilizado por aquellos que no tienen una conexion desde otro lugar.
		$this->conexion = DB::Connect();
	}

	public function conseguir($tabla, $campo, $condicion)
	{

		//Permite devolver los datos de una consulta Mysql.
		//Variables:
		//tabla-> La tabla de donde se sacara la info.
		//campo-> El campo, o los campos solicitados.
		//condicion-> condicion o condiciones para los datos.
		//Salida: Se recibira un objeto PDO, asi que sera necesario trabajarlo para obtener la informacion.


		$query = $this->conexion->query("SELECT $campo FROM $tabla WHERE $condicion;");
		return $query;
	}

	public function conseguirTodos($tabla, $condicion = "", $order_by = 'ASC')
	{

		//Devuelve un objeto PDO con todos los elementos de una tabla.
		//Variables:
		//tabla-> La tabla de donde se sacara la info.
		//condicion-> Condicion que filtrara los elementos.
		//order_by-> El orden en el que vendran los datos, por defecto es ascendente, de menor a mayor.
		//Salida: Se recibira un objeto PDO, asi que sera necesario trabajarlo para obtener la informacion.
		$sql = "SELECT * FROM $tabla ORDER BY id $order_by";
		if ($condicion == "") {
			$query = $this->conexion->query($sql);
		} else {
			$sql = "SELECT * FROM $tabla $condicion ORDER BY id $order_by";
			$query = $this->conexion->query($sql);
		}
		return $query;
	}

	public function conseguirRandom($tabla, $condicion = "", $limite)
	{
		//Devuelve una seleccion al azar de elementos dentro de una tabla, 
		//opcionalmente se puede introducir una condicion.
		//tabla-> La tabla de donde se sacaran los elementos.
		//condicion-> Condicion que filtrara los elementos.
		//limite-> Establece el numero maximo de elementos obtenidos.
		$select = "SELECT * FROM $tabla ";
		$order = "";
		if ($condicion == "") {
			$order = "ORDER BY RAND() LIMIT $limite";
			$sql = $select . $order;
		} else {
			$order = " ORDER BY RAND() LIMIT $limite";
			$sql = $select . $condicion . $order;
		}
		$elementos = $this->consultar($sql);
		return $elementos;
	}

	public function actualizarUno($tabla, $campo, $nuevo)
	{

		//Actualiza uno o varios campos de una tabla dada.
		//Variables:
		//tabla-> La tabla donde se hara el cambio.
		//campo-> El campo a editar.
		//nuevo-> La nueva informacion.
		//Salida: Devolvera una variable que indicara si la operacion fue exitosa o no.

		$sql = "UPDATE $tabla SET $campo = :$campo";
		$query = $this->conexion->prepare($sql);
		$query->bindParam(":$campo", $nuevo);
		$resultado = $query->execute();
		return $resultado;
	}

	public function ejecutar($orden)
	{

		//Ejecuta una orden SQL.
		//Variable:
		//orden-> La declaracion SQL a ejecutar.
		//Salida: Devolvera una variable que indicara si la operacion fue exitosa o no. 

		$ejecucion = $this->conexion->prepare($orden);
		$resultado = $ejecucion->execute();
		return $resultado;
	}
	public function consultar($consulta)
	{

		//Ejecuta una consulta SQL.
		//Variable:
		//consulta-> La orden SQL.
		//Salida: Se recibira un objeto PDO con los datos, asi que sera necesario trabajarlo para obtener la informacion.

		$query = $this->conexion->prepare($consulta);
		$query->execute();
		return $query;
	}

	public function id_anterior1()
	{
		$sql = "SELECT LAST_INSERT_ID() as id;";
		$consulta = $this->conexion->query($sql);
		$consulta = $consulta->fetch(PDO::FETCH_ASSOC);
		$id = $consulta['id'];
		return $id;
	}

	public function id_anterior2($tabla)
	{
		$sql = "SELECT id FROM pedidos ORDER BY id DESC LIMIT 1;";
		$consulta = $this->conexion->query($sql);
		$consulta = $consulta->fetch(PDO::FETCH_ASSOC);
		$id = $consulta['id'];
		return $id;
	}
	public function cerrar()
	{
		//Cierra la conexion con la base de datos.
		$this->conexion = NULL;
	}

}
