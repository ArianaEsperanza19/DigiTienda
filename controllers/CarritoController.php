<?php
class CarritoController
{
    //Metodos
    # ver
    # agregar
    # quitar
    # poner
    # borrarTodo
    # borrarUno
    # session

    public function ver()
    {
        //El presente metodo envia a la pagina donde se muestra el contenido del carrito.
        # $datos -> sera usado para formar la lista de ver.phtml.
        require_once ("models/carrito.php");
        $carrito = new Carrito();
        $datos = $carrito->ver();
        require_once ("views/carrito/ver.phtml");
    }

    public function agregar()
    {
        /*El presente metodo obtiene la informacion del producto dado por su id y pasa la informacion 
        al metodo agregar del modelo 'carrito' que se encargara nuevo elemento
        #Nota: Se emplea el metodo agregar_primero() para productos no ingresados al carrito. 
        Por otro lado simplemente agregar() gestiona la adicion de mas unidades a un mismo producto.*/

        if ($_GET['producto']) {
            $id_producto = isset($_GET['producto']) ? $_GET['producto'] : false;
            if ($id_producto) {
                require_once "models/producto.php";
                require_once "models/carrito.php";
                $producto = new Producto();
                $datos = $producto->conseguirUno($id_producto);
                $precio = $datos->fetchColumn(3);
                $carrito = new Carrito();
                $carrito->setId_producto($id_producto);
                $carrito->set_datos($datos);
                $carrito->set_precio($precio);

                if (isset($_SESSION['carrito'])) {
                    #Sumar unidades a producto ya agregado.
                    $carrito->agregar();
                } else {
                    #Producto nuevo.
                    $carrito->agregar_primero();
                }
                $this->ver();
            } else {
                header("Location: index.php?controller=Productos&action=index");
            }

        }

    }


    public function quitar()
    {
        /*El presente metodo recibe la id del producto ya en el carrito y se remite al modelo 'carrito' 
        para que use su metodo 'remover' y le reste 1 a la cantidad del producto en cuestion */
        if ($_GET['producto']) {
            $id_producto = isset($_GET['producto']) ? $_GET['producto'] : false;
            if ($id_producto) {
                require_once "models/carrito.php";
                $carrito = new Carrito();
                $carrito->setId_producto($id_producto);
                if (isset($_SESSION['carrito'])) {
                    $actualizacion = $carrito->remover();
                    $_SESSION['carrito'] = $actualizacion;
                    header("Location: index.php?controller=Carrito&action=ver");
                }

            } else {
                header("Location: index.php?controller=Productos&action=index");
            }

        }
    }

    public function poner()
    {
        /*El metodo a continuacion agrega un elemento del mismo tipo al carrito. 
        Dicho elemento es especificado por la id del producto y se realiza mediante 
        el metodo 'suma' del modelo 'carrito'*/
        if ($_GET['producto']) {
            $id_producto = isset($_GET['producto']) ? $_GET['producto'] : false;
            if ($id_producto) {
                //require_once "models/producto.php";
                require_once "models/carrito.php";
                //$producto = new Producto();
                //$datos = $producto->conseguirUno($id_producto);
                $carrito = new Carrito();
                $carrito->setId_producto($id_producto);
                if (isset($_SESSION['carrito'])) {
                    $actualizacion = $carrito->sumar();
                    require_once ("models/utiles/JsonManager.php");
                    JsonManager::set_json($actualizacion);
                    $actualizacion = JsonManager::codificar();
                    $_SESSION['carrito'] = $actualizacion;
                    header("Location: index.php?controller=Carrito&action=ver");
                }



            } else {
                header("Location: index.php?controller=Productos&action=index");
            }

        }
    }

    public function borrarTodo()
    {
        /*La funcion de este metodo es vacia el carrito por completo, emplea el util 'borrarSession.php' 
        para destruir la sesion que guarda la info del carrito.*/
        require_once "models/utiles/borrarSession.php";
        borrarSession::borrar('carrito');
        header("Location: index.php?controller=Productos&action=index");
    }

    public function borrarUno()
    {
        /*El presente metodo permite tomar el id del producto y mediate el metodo 'eliminar' del modelo 'Carrito'
        borrar un producto en su totalidad de la lista de compras.*/
        if ($_GET['producto']) {
            $id_producto = isset($_GET['producto']) ? $_GET['producto'] : false;
            if ($id_producto) {
                require_once "models/carrito.php";
                $carrito = new Carrito();
                $carrito->setId_producto($id_producto);
                $datos = $carrito->eliminar();
                JsonManager::set_json($datos);
                $datos = JsonManager::decodificar();
                if (is_array($datos) && count($datos) == 1) {
                    $correcion = $datos;
                    $datos = $correcion[0];
                    JsonManager::set_json($datos);
                    $datos = JsonManager::codificar();
                }
                $_SESSION['carrito'] = $datos;
                header("Location: index.php?controller=Carrito&action=ver");
            }
        }
    }

    public function session()
    {
        //Este metodo ofrece una vista de lo que hay dentro del carrito.
        echo "<pre>El objeto Json dentro de la session:\n\n";
        print_r($_SESSION['carrito']);
        echo "</pre>";

        $actualizacion = $_SESSION['carrito'];
        require_once ("models/utiles/JsonManager.php");
        JsonManager::set_json($actualizacion);
        $actualizacion = JsonManager::decodificar();
        echo "<pre>Su contenido en forma de objeto: \n\n";
        print_r($actualizacion);
        echo "</pre>";
    }

    public function pedidos()
    {
        //ver pedidos del usuario
    }
}