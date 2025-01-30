<?php
require_once 'utiles/escaparDatos.php';
require_once 'ModeloDB.php';

class Carrito
{

    //Metodos
    # agregar
    # agregar_primero
    # eliminar
    # verificarProducto
    # verificarProductos
    # guardarTodo
    # remover
    # sumar
    # ver
    private $id_producto;
    private $datos;
    private $precio;

    public function __construct()
    {
        $this->conexion = DB::Connect();
    }

    public function getId_producto()
    {
        return $this->id_producto;
    }

    public function get_datos()
    {
        return $this->datos;
    }

    public function get_precio()
    {
        return $this->precio;
    }

    public function setId_producto($id_producto)
    {
        $this->id_producto = $id_producto;

        return $this;
    }

    public function set_datos($datos)
    {
        $this->datos = $datos;

        return $this;
    }

    public function set_precio($precio)
    {
        $this->precio = $precio;

        return $this;
    }

    public function agregar()
    {
        /*El presente metodo permite agregar nuevo producto o productos al carrito.
        Nota: JsonManager.php y centinela.php es necesario para 
        su buen funcionamiento y depende de los metodos para verificarProducto y verificarProductos.
        Retorna los datos actualizados del carrito en formato json.*/
        $id_producto = $this->id_producto;
        $datos = $this->datos;
        $precio = $this->precio;
        $actualizacion = "";

        $datos_carrito = $_SESSION['carrito'];

        require_once ("models/utiles/JsonManager.php");
        JsonManager::set_json($datos_carrito);
        $datos_carrito = JsonManager::decodificar();

        require_once "models/utiles/centinela.php";
        $centinela = centinela::verificar($datos_carrito);
        if ($centinela == true) {
            $actualizacion = $this->verificarProducto($datos_carrito);
        } else {
            $actualizacion = $this->verificarProductos($datos_carrito);
        }
        JsonManager::set_json($actualizacion);
        $actualizacion = JsonManager::codificar();
        $_SESSION['carrito'] = $actualizacion;

    }

    public function agregar_primero()
    {
        #Agrega el primer producto al carrito
        $id_producto = $this->id_producto;
        $datos = $this->datos;
        $precio = $this->precio;
        $nuevo = [
            "id_producto" => $id_producto,
            "precio" => $precio,
            "unidades" => 1,
            "objeto" => $datos
        ];
        $nuevo = json_encode($nuevo);
        $_SESSION['carrito'] = $nuevo;
    }

    public function eliminar()
    {
        /*El presente metodo elimina un producto en su totalidad del carrito.
        Nota: Para su funcionamiento requiere JsonManager.
        Devuelve un objeto json.*/
        $id_producto = $this->id_producto;
        $carrito = $_SESSION['carrito'];
        require_once ("models/utiles/JsonManager.php");
        JsonManager::set_json($carrito);
        $carrito = JsonManager::decodificar();
        $indice = 0;
        $centinela = false;
        foreach ($carrito as $producto) {
            if ($producto->id_producto == $id_producto) {
                $centinela = true;
                break;
            }
            $indice++;
        }
        array_splice($carrito, $indice, 1);
        JsonManager::set_json($carrito);
        $carrito = JsonManager::codificar();
        return $carrito;
    }

    public function verificarProductos($datos_carrito)
    {
        /*El metodo a continuacion se asegura de que no se repitan los registros de productos
        en el carrito, en lugar de eso, los productos repetidos solo sumaran a la cantidad del registro
        correspondiente.
        Nota: depende de JsonManager.php y del metodo local guardarTodo.
        Este metodo devuelve un arreglo.*/
        $id_producto = $this->id_producto;
        $datos = $this->datos;
        $precio = $this->precio;

        foreach ($datos_carrito as $producto) {
            if ($producto->id_producto == $id_producto) {
                $producto->unidades = $producto->unidades + 1;
                return $datos_carrito;
            }
            $producto = $_SESSION['carrito'];
            JsonManager::set_json($producto);
            $datos_session = JsonManager::decodificar();
            $nuevoProducto = [
                "id_producto" => $id_producto,
                "precio" => $precio,
                "unidades" => 1,
                "objeto" => $datos
            ];
            $carrito = $this->guardarTodo($datos_session, $nuevoProducto);
        }
        return $carrito;
    }


    public function verificarProducto($datos_carrito)
    {
        /*El metodo a continuacion verifica el unico registro que se encuentra en el carrito.
        Nota: depende de JsonManager.php y del metodo local guardarTodo.
        Este metodo devuelve un arreglo.*/
        $id_producto = $this->id_producto;
        $datos = $this->datos;
        $precio = $this->precio;

        //Cuando el producto ya esta en el carrito, se le suma 1.
        if ($datos_carrito->id_producto == $id_producto) {
            $datos_carrito->unidades = $datos_carrito->unidades + 1;
            return $datos_carrito;
        }

        // Cuando el producto NO está en el carrito
        $producto = $_SESSION['carrito'];
        JsonManager::set_json($producto);
        $datos_carrito = JsonManager::decodificar();
        $nuevoProducto = [
            "id_producto" => $id_producto,
            "precio" => $precio,
            "unidades" => 1,
            "objeto" => $datos
        ];

        $carrito = $this->guardarTodo($datos_carrito, $nuevoProducto);

        return $carrito;
    }

    public function guardarTodo($datos_session, $nuevo)
    {
        /*
        Recibe todos los datos de los productos ya en el carrito y los nuevos, se encarga 
        de combilarlos todos.
        NOTA: EL ELEMENTO DE LA SESSION ENTRA COMO OBJETO Y EL NUEVO COMO ARREGLO; 
        Devuelve un arreglo dentro del que hay objetos.*/
        require_once "models/utiles/centinela.php";
        $centinela = centinela::verificar($datos_session);

        if ($centinela) {
            $carrito[] = $datos_session;
            $carrito[] = $nuevo;
        } else {
            foreach ($datos_session as $producto) {
                $carrito[] = $producto;
            }
            $carrito[] = $nuevo;

        }
        return $carrito;
    }
    //remover puede mejorarse mediante centinela.php
    public function remover()
    {
        /*El presente metodo elimina un 1 de la cantidad del producto dado.
        Nota: Requiere el uso de JsonManager para su correcto funcionamiento.
        Devuelve un objeto Json.
        */
        $id_producto = $this->id_producto;
        if (isset($_SESSION['carrito'])) {
            require_once ("models/utiles/JsonManager.php");
            require_once ("models/utiles/centinela.php");
            $datos = $_SESSION['carrito'];
            JsonManager::set_json($datos);
            $session = JsonManager::decodificar();
            $session = (array) $session;
            $verifica = new centinela();
            $centinela = $verifica->verificar($session);
            foreach ($session as $producto) {

                if (!$centinela) {
                    #SI es un objeto.
                    if (@$producto->id_producto == $id_producto && $producto->unidades >= 1) {
                        $unidades = $producto->unidades - 1;
                        $producto->unidades = $unidades;
                        if ($producto->unidades < 1)#control para impedir que el contador llegue a cero.
                        {
                            $producto->unidades = 1;
                        }

                    }

                } else {

                    if ($session['id_producto'] == $id_producto && $session['unidades'] >= 1) {
                        $centinela = true;
                        break;
                    }
                }
            }

            #La resta si es un solo tipo de producto.
            if ($centinela == true) {
                $session['unidades'] = $session['unidades'] - 1;
                if ($session['unidades'] < 1) {
                    $session['unidades'] = 1;
                }
                $actualizacion = (object) $session;
                JsonManager::set_json($actualizacion);
                $session = JsonManager::codificar();
            }

            #Transforma Objetos y Arreglos en Json
            if (is_object($session) || is_array($session)) {
                $actualizacion = $session;
                JsonManager::set_json($actualizacion);
                $session = JsonManager::codificar();
            }
            return $session;
        }
    }

    public function sumar()
    {
        /*Este metodo verifica si hay el producto en efecto existe y si de existir le suma 1.
        Nota: Necesita JsonManager.php para funcionar.
        Devuelve un arreglo con objetos.*/
        $id_producto = $this->id_producto;
        if (isset($_SESSION['carrito'])) {
            require_once ("models/utiles/JsonManager.php");
            $datos = $_SESSION['carrito'];
            JsonManager::set_json($datos);
            $session = JsonManager::decodificar();
            $centinela = false;
            foreach ($session as $producto) {
                if (is_object($producto)) {
                    if (@$producto->id_producto == $id_producto && $producto->unidades >= 1) {
                        $producto->unidades = $producto->unidades + 1;
                    }
                } else {

                    if ($session->id_producto == $id_producto && $session->unidades >= 1) {
                        $centinela = true;
                        break;
                    }

                }
            }
            if ($centinela == true) {
                $session->unidades = $session->unidades + 1;
            }
            return $session;
        }
    }

    public function ver()
    {
        /*La presente funcio recopila lo que se encuentra en la sesion de carrito
        para luego mostrarlo en la vista ver.phtml.
        Nota: Requiere JsonManager.php para funcionar.
        Devuelve un arreglo con objetos.
        */
        if (isset($_SESSION['carrito'])) {
            require_once ("models/utiles/JsonManager.php");
            $datos = $_SESSION['carrito'];
            JsonManager::set_json($_SESSION['carrito']);
            $datos = JsonManager::decodificar();
            return $datos;
        } else {
            header("Location: index.php?controller=Productos&action=index");
        }
    }

}

?>