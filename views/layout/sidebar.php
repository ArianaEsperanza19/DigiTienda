<aside id="lateral">

	<div id="login" class="block_aside">
		<?php
		require_once "models/utiles/verificarIdentidad.php";
		//$centinela = verificarIdentidad::verificar();
		if (!isset($_SESSION['identidad'])):
			echo "<h3>Entrar a la web</h3>
						<form action='index.php?controller=Usuarios&action=login' method='post'>
							<label for='email'>Email</label>
							<input type='email' name='email' />
							<label for='password'>Contraseña</label>
							<input type='password' name='password' />
							<input type='submit' value='Enviar' />
							<ul>
							<li><a href='index.php?controller=Usuarios&action=registrar'>Registrarse</a></li>
							</ul>
						</form>
					";
		else:


			$centinela = verificarIdentidad::verificar();
			$id = verificarIdentidad::id();
			$Identidad = new verificarIdentidad();
			$nombre = $Identidad->nombre();

			require_once "models/utiles/verificarAdmin.php";
			$centinela = verificarAdmin::verificar();


			echo "<h3>#$id $nombre</h3>";

			require_once "models/ModeloDB.php";
			$db = new ModeloDB();
			$condicion = "usuario_id=$id";
			$pedidos = $db->conseguir('pedidos', '*', $condicion);
			$pedidos = $pedidos->fetchAll();
			$db->cerrar();
			$_SESSION['pedidos'] = $pedidos;
			if ($_SESSION['pedidos']) {
				echo "<ul><li><a href='index.php?controller=Pedidos&action=pedidos_usuario&id_usuario=$id'>Mis pedidos</a></li>";

			}

			if ($centinela) {
				echo "
				<li><a href='index.php?controller=Pedidos&action=gestionar'>Gestionar pedidos</a></li>
				<li><a href='index.php?controller=Categorias&action=gestionar'>Gestionar categorias</a></li>
				<li><a href='index.php?controller=Productos&action=gestionar'>Gestionar productos</a></li>
				<li><a href='index.php?controller=Default&action=EnDesarrollo'>Importar base de datos</a></li>
				<li><a href='index.php?controller=Default&action=EnDesarrollo'>Emportar base de datos</a></li>
					";
			}
			if (isset($_SESSION["carrito"])) {
				echo "<li><a href='index.php?controller=Carrito&action=ver'>Ver carrito</a></li>";
			}
			echo "<li><a href='index.php?controller=Usuarios&action=logout'>Cerrar Sesion</a></li></ul>";

		endif;
		?>
	</div>

</aside>
<div id="central">