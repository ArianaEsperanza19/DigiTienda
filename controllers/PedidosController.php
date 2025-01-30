<?php
class PedidosController
{

	//Metodos
	# hacer
	# guardar
	# pedidos_usuario
	# detalles
	# confirmar

	public function hacer()
	{
		//El presente metodo realiza el pedido del cliente.
		require_once "models/utiles/verificarIdentidad.php";
		$centinela = verificarIdentidad::verificar();
		if ($centinela) {
			require_once "views/pedido/formulario.phtml";
		} else {
			echo "<span style='color: red'><h2>Error. NO estas identificado.</h2>
			<p>Por favor inicia sesion</p></span>";
		}

	}

	public function guardar()
	{

		//El presente metodo se encarga de guardar los datos enviados por el formulario del pedido.

		require_once "models/utiles/verificarIdentidad.php";
		require_once "models/utiles/borrarSession.php";
		$centinela = verificarIdentidad::verificar();
		if ($centinela) {
			require_once "models/pedido.php";
			$pedido = new Pedido();

			if (isset($_POST)) {
				$provincia = isset($_POST["provincia"]) ? $_POST["provincia"] : false;
				$ciudad = isset($_POST["ciudad"]) ? $_POST["ciudad"] : false;
				$direccion = isset($_POST["direccion"]) ? $_POST["direccion"] : false;
			}

			if ($provincia && $ciudad && $direccion) {
				#Introducir la provincia
				$pedido->setProvincia($provincia);
				#Introducir la ciudad
				$pedido->setCiudad($ciudad);
				#Introducir la direccion;
				$pedido->setDireccion($direccion);
				#Guardar pedido
				$resultado = $pedido->guardar();
			}
		} else {
			echo "no logueado";
		}

		if ($resultado) {
			borrarSession::borrar('carrito');
			$_SESSION['pedido'] = "completado";
			header("Location: ?controller=Pedidos&action=confirmar");
		} else {
			$_SESSION['pedido'] = "fallido";
			echo "Operacion fallida";
		}

	}

	public function pedidos_usuario()
	{
		//El presente metodo lleva a los pedidos del usuario logueado.
		#Verifica si existe la session de 'pedidos' si no existe no se puede acceder a esta seccion.
		if ($_SESSION['pedidos']) {
			#Si no se recibe la id, tampoco se pueda acceder.
			if ($_GET) {
				$id = isset($_GET['id_usuario']) ? $_GET['id_usuario'] : false;

				if ($id) {

					require_once "views/pedido/mispedidos.phtml";



				}
			} else {
				header("Location: ?controller=Productos&action=index");

			}
		} else {

			header("Location: ?controller=Productos&action=index");

		}

	}

	public function detalles()
	{
		//Lleva a la pagina de detalles del pedido.
		require_once "models/utiles/verificarIdentidad.php";
		$centinela = verificarIdentidad::verificar();
		if ($centinela) {

			if (isset($_GET)) {
				require_once "models/pedido.php";
				require_once "models/producto.php";
				$id = isset($_GET['id']) ? $_GET['id'] : false;
				if ($id) {
					$pedido = new Pedido();
					$producto = new Producto();
					$productos_pedido = $pedido->informacion_pedido($id);
					//var_dump($productos);


					require_once "views/pedido/detalles.phtml";


				} else {
					header("Location: index.php?controller=Productos&action=index");
				}

			} else {
				echo "error";
				header("Location: index.php?controller=Productos&action=index");
			}

		} else {
			header("Location: index.php?controller=Productos&action=index");
		}



	}

	public function confirmar()
	{
		//Confirma el pedido y adjunta la pagina donde se muestra la info.
		require_once "models/ModeloDB.php";
		require_once "models/pedido.php";
		$db = new ModeloDB();
		$pedido = new Pedido();
		$productos = $pedido->conseguir_productos_del_ultimo_pedido();
		$datos_pedido = $pedido->conseguir_datos_del_ultimo_pedido();
		$id = $datos_pedido[0]["id"];
		$coste = $datos_pedido[0]["coste"];


		require_once "views/pedido/confirmar.phtml";
	}

	public function gestionar()
	{
		//Consigue toda la informacion de los pedidos en la BD y lleva a gestionar.phtml, donde se permite cambiar su status.
		require_once "models/utiles/verificarAdmin.php";
		$centinela = verificarAdmin::verificar();
		if ($centinela) {
			require_once "models/pedido.php";
			$consulta = new Pedido();
			$pedidos = $consulta->listarTodos();
			$pedidos = $pedidos->fetchAll();

			require_once "views/pedido/gestionar.phtml";
		} else {
			header("Location: index.php?controller=Productos&action=index");
		}

	}

	public function actualizar()
	{
		//El presente metodo se encarga de actualizar el estatus de un pedido.
		require_once "models/utiles/verificarAdmin.php";
		$centinela = verificarAdmin::verificar();
		if ($centinela) {
			if (isset($_GET) && isset($_POST)) {
				$id = isset($_GET['id']) ? $_GET['id'] : false;
				$nuevo_status = isset($_POST) ? $_POST : false;
				$nuevo_status = $nuevo_status['estado'];
				require_once "models/pedido.php";
				$pedido = new Pedido();
				$pedido->actualizar($id, $nuevo_status);
				header("Location: index.php?controller=Pedidos&action=gestionar");
			}
		} else {
			header("Location: index.php?controller=Productos&action=index");
		}
	}
}