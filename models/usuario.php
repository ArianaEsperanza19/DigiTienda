<?php
require_once 'utiles/escaparDatos.php';
require_once 'ModeloDB.php';

class Usuario
{
	private $nombre;
	private $apellidos;
	private $rol;
	private $email;
	private $password;

	public function __construct()
	{
		$this->conexion = DB::Connect();
	}
	//GETTERS
	public function getNombre()
	{
		return $this->nombre;
	}

	public function getApellidos()
	{
		return $this->apellidos;
	}
	public function getRol()
	{
		return $this->rol;
	}

	public function getEmail()
	{
		return $this->email;
	}

	public function getPassword()
	{
		return $this->password;
	}
	//SETTERS
	public function setNombre($nombre)
	{
		$nombre = escaparDatos::escapar($nombre);
		$this->nombre = $nombre;
	}

	public function setApellidos($apellidos)
	{
		$apellidos = escaparDatos::escapar($apellidos);
		$this->apellidos = $apellidos;
	}
	public function setRol($rol)
	{
		$rol = escaparDatos::escapar($rol);
		$this->rol = $rol;
	}

	public function setEmail($email)
	{
		$email = escaparDatos::escapar($email);
		$this->email = $email;
	}

	public function setPassword($password)
	{
		$password = escaparDatos::escapar($password);
		$this->password = $password;
	}


	public function listarTodos()
	{
		//Obtiene un arreglo con todas lus usuarios en el sistema.
		//Salida: Devuelve un arreglo PDO con el que sera necesario trabajar para obtener los datos.
		//Nota: Atencion, es necesaria la clase ModeloDD.php, para funcionar.
		$db = new ModeloDB;
		$listado = $db->conseguirTodos("usuarios");
		return $listado;
	}

	public function guardar()
	{
		//El presente metodo guarda en la DB los datos del usuario que se tengan almacenados en este objeto.
		//Salida: Devolvera una variable que indicara si la operacion fue exitosa o no.
		//Nota: Atencion, sin acceso al objeto ModeloDB no se podra realizar las operaciones.
		$db = new ModeloDB;
		$n = $this->nombre;
		$a = $this->apellidos;
		$r = $this->rol;
		$e = $this->email;
		//Cifrar contrase;a
		$p = password_hash($this->password, PASSWORD_BCRYPT, ['cost' => 4]);
		//Verificar si ya hay usuarios en la DB.
		$admin = $db->conseguirTodos('usuarios');
		$admin = $admin->fetchAll();
		if ($admin) {
			$sql = "INSERT INTO usuarios (nombre, apellido, rol, email, contrasenya, permisos) VALUES ('$n', '$a', '$r', '$e', '$p',0);";
		} else {
			$sql = "INSERT INTO usuarios (nombre, apellido, rol, email, contrasenya, permisos) VALUES ('$n', '$a', '$r', '$e', '$p',1);";
		}

		$resultado = $db->ejecutar($sql);
		$db->cerrar();
		return $resultado;
	}

	public function loguear()
	{
		//Metodo para identificar al usuario.
		//Nota: Atencion, sin acceso al objeto ModeloDB no se podra realizar las operaciones.
		//Salida: Devolvera una variable indicando si el permiso fue otorgado o denegado.
		$db = new ModeloDB();
		$email = $this->email;
		$password = $this->password;
		$condicion = "email = '$email'";
		$consulta = $db->conseguir('usuarios', 'contrasenya', $condicion);
		$login = false;
		if ($consulta) {
			//echo "<pre>";print_r($consulta);echo "<pre>";
			foreach ($consulta as $i) {
				if (password_verify($password, $i[0])) {
					$login = true;
				}
			}
		}
		$db->cerrar();
		return $login;
	}

	public function verificarIdentidad()
	{	//Permite identificar al usuario logueado, en base al email UNICO que se almacene en este objeto.
		//Nota: Atencion, sin acceso al objeto ModeloDB no se podra realizar las operaciones.
		//Salida: Devuelve un objeto PDO con la id del usuario logueado.
		$db = new ModeloDB();
		$email = $this->email;
		$condicion = "email = '$email'";
		$consulta = $db->conseguir('usuarios', 'id', $condicion);
		$db->cerrar();
		if ($consulta) {
			$identidad = $consulta->fetchColumn();
		} else {
			$identidad = false;
		}
		return $identidad;
	}

	public function verificarAdmin()
	{	//Determina si el usuario logueado es o no es un usuario administrador.
		//Nota: Atencion, sin acceso al objeto ModeloDB no se podra realizar las operaciones.
		//Salida: Devuelve una variable indicado si el permiso fue concedido, o no.
		$db = new ModeloDB();
		$email = $this->email;
		$condicion = "email = '$email'";
		$consulta = $db->conseguir('usuarios', 'permisos', $condicion);
		$consulta = $consulta->fetchColumn();
		$db->cerrar();

		if ($consulta == 1) {
			$centinela = true;
		} else {
			$centinela = false;
		}

		return $centinela;
	}
}
