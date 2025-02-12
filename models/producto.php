<?php
require_once 'utiles/escaparDatos.php';
require_once 'ModeloDB.php';

class Producto
{
	private $nombre;
	private $precio;
	private $descripcion;
	private $imagen;
	private $categorias;
	private $stock;

	public function __construct()
	{
		$this->conexion = DB::Connect();
	}
	//GETTERS
	public function getNombre()
	{
		return $this->nombre;
	}

	public function getPrecio()
	{
		return $this->precio;
	}
	public function getDescripcion()
	{
		return $this->descripcion;
	}

	public function getImagen()
	{
		return $this->imagen;
	}

	public function getCategorias()
	{
		return $this->categorias;
	}
	public function getStock()
	{
		return $this->stock;
	}
	//SETTERS
	public function setNombre($nombre)
	{
		$nombre = escaparDatos::escapar($nombre);
		$this->nombre = $nombre;
	}

	public function setPrecio($precio)
	{
		$precio = escaparDatos::escapar($precio);
		$this->precio = $precio;
	}
	public function setDescripcion($descripcion)
	{
		$descripcion = escaparDatos::escapar($descripcion);
		$this->descripcion = $descripcion;
	}

	public function setImagen($img)
	{
		$img = escaparDatos::escapar($img);
		$this->imagen = $img;
	}

	public function setCategorias($categoria)
	{
		$categoria = escaparDatos::escapar($categoria);
		$this->categorias = $categoria;
	}
	public function setStock($s)
	{
		$stock = escaparDatos::escapar($s);
		$this->stock = $stock;
	}
	//OTROS METODOS
	public function listarTodos()
	{
		//Obtiene un arreglo con todos los productos en el sistema.
		//Salida: Devuelve un arreglo PDO con el que sera necesario trabajar para obtener los datos.
		//Nota: Atencion, es necesaria la clase ModeloDB.php, para funcionar.
		$db = new ModeloDB;
		$listado = $db->conseguirTodos("productos");
		return $listado;
	}

	public function guardar()
	{
		//El presente metodo guarda en la DB los datos del producto que se tengan almacenados en este objeto.
		//Salida: Devolvera una variable que indicara si la operacion fue exitosa o no.
		//Nota: Atencion, sin acceso al objeto ModeloDB no se podra realizar las operaciones.
		$db = new ModeloDB;
		$n = $this->nombre;
		$p = $this->precio;
		$d = $this->descripcion;
		$i = $this->imagen;
		$c = $this->categorias;
		$s = $this->stock;
		$sql = "INSERT INTO productos (categoria_id, nombre, precio, descripcion, imagen, stock) VALUES ('$c', '$n', '$p', '$d', '$i', '$s');";
		$resultado = $db->ejecutar($sql);
		$db->cerrar();
		return $resultado;
	}
	public function borrar($id)
	{
		/*
		El presente metodo borra tanto el registro del producto, como la imagen asociada.
		Variable: id-> Numero del producto en cuestion.
		Nota: Atencion, sin acceso al objeto ModeloDB no se podra realizar las operaciones.
		Salida: Devolvera una variable que indicara si la operacion fue exitosa o no.
		*/
		$db = new ModeloDB;

		$imagen = $db->conseguir('productos', 'imagen', "id=$id");
		$imagen = $imagen->fetchColumn();
		$directorio = $_SESSION['url_img'];
		$sql = "DELETE FROM productos WHERE id = $id";
		$resultado = $db->ejecutar($sql);
		$fecha = new DateTime();
		//Borrar imagen
		if ($resultado) {
			if (file_exists($directorio . $imagen)) {
				unlink($directorio . $imagen);				
			}
		}
		$db->cerrar();
		return $resultado;
	}

	public function actualizarRegistro($id, $imagen)
	{
		/*
		Actualiza uno de los productos, modificando uno o varios de sus campos.
		Variables:
		id-> Identificador del producto a editar.
		imagen-> Objeto de la nueva imagen.
		Nota: Atencion al directorio, en caso de que la carpeta para imagenes se encuentre en otro lugar.
		Nota2: Atencion, sin acceso al objeto ModeloDB, encargado de gestionar la DB, no se podra realizar las operaciones.
		Salida: Devolvera una variable que indicara si la operacion fue exitosa o no.
		*/
		$n = $this->nombre;
		$p = $this->precio;
		$d = $this->descripcion;
		$i = $this->imagen;//Solo el nombre de la nueva imagen 
		$c = $this->categorias;
		$s = $this->stock;

		$directorio = $_SESSION['url_img'];
		$img_temporal = $imagen['tmp_name'];
		$db = new ModeloDB();
		$original_img = $db->conseguir('productos', 'imagen', "id=$id");//Conseguir datos de la img original.
		$original_img = $original_img->fetchColumn();

		#Comparar imagen nueva e imagen original.
		if ($i && $i != $original_img) {
			#Se actualiza todo.
			$fecha = new DateTime();
			$time = $fecha->getTimestamp();
			$Nueva_version = $time . "_" . $i;
			$sql = "UPDATE productos SET 
			nombre = '$n', 
			precio = $p, 
			descripcion = '$d', 
			categoria_id = $c, 
			imagen = '$Nueva_version',
			stock = $s
			WHERE id = $id;";

		} else {

			#No se actualiza la imagen.
			$sql = "UPDATE productos SET 
			nombre = '$n', 
			precio = $p, 
			descripcion = '$d', 
			categoria_id = $c, 
			stock = $s
			WHERE id = $id;";

		}
		
		$resultado = $db->ejecutar($sql);
		if ($resultado) {
			if (file_exists($directorio . $original_img)) {
				unlink($directorio . $original_img);
				move_uploaded_file($img_temporal, $directorio . $Nueva_version);
			}

		} else {
			echo "Error en la operacion con la base de datos.";
			die();
		}
		$db->cerrar();
		return $resultado;
	}

	public function conseguirUno($id)
	{
		//Consigue un producto de la base de datos en base a su id.
		//Devuelve un objeto PDO con el que es necesario trabajar.
		$db = new ModeloDB();
		$producto = $db->conseguir('productos', '*', "id=$id");
		$db->cerrar();
		return $producto;
	}
}

?>