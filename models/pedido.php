<?php
require_once 'utiles/escaparDatos.php';
require_once 'ModeloDB.php';

class Pedido
{
    private $provincia;
    private $ciudad;
    private $direccion;

    public function __construct()
    {
        $this->conexion = DB::Connect();
    }
    //GETTERS
    public function getProvincia()
    {
        return $this->provincia;
    }

    public function getCiudad()
    {
        return $this->ciudad;
    }
    public function getDireccion()
    {
        return $this->direccion;
    }
    //SETTERS
    public function setProvincia($provincia)
    {
        $provincia = escaparDatos::escapar($provincia);
        $this->provincia = $provincia;
    }

    public function setCiudad($ciudad)
    {
        $ciudad = escaparDatos::escapar($ciudad);
        $this->ciudad = $ciudad;
    }
    public function setDireccion($direccion)
    {
        $direccion = escaparDatos::escapar($direccion);
        $this->direccion = $direccion;
    }

    //OTROS METODOS
    #No en uso
    public function listarTodos()
    {
        //Obtiene un arreglo con todos los pedidos en el sistema.
        //Salida: Devuelve un arreglo PDO con el que sera necesario trabajar para obtener los datos.
        //Nota: Atencion, es necesaria la clase ModeloDb.php, para funcionar.
        require_once "models/ModeloDB.php";
        $listado = new ModeloDB();
        $listado = $listado->conseguirTodos("pedidos");
        return $listado;
    }


    #Metodo en edicion para ser usado por pedidos;
    public function guardar()
    {

        /*El presente metodo guarda en la DB los datos del pedido.
        Salida:
        Nota: Atencion, sin acceso al objeto ModeloDB no se podra realizar las operaciones.
        Requiere los archivos:
        -verificarIdentidad.php
        -JsonManager.php
        -centinela.php
        */

        require_once "models/utiles/verificarIdentidad.php";
        $usuario = verificarIdentidad::verificar();
        if ($usuario && isset($_SESSION["carrito"])) {
            require_once "models/utiles/JsonManager.php";
            require_once "models/utiles/centinela.php";
            $db = new ModeloDB;
            $provincia = $this->provincia;
            $ciudad = $this->ciudad;
            $direccion = $this->direccion;
            $id_usuario = $_SESSION['identidad'];
            $carrito = $_SESSION['carrito'];
            JsonManager::set_json($carrito);
            $carrito = JsonManager::decodificar();
            $centinela = centinela::verificar($carrito);
            //Verificar si hay uno o muchos elementos.
            if ($centinela) {
                //Un solo elemento
                $numero_productos = 1;
                $id_producto = $carrito->id_producto;
                $p = $carrito->precio;
                $u = $carrito->unidades;
                $precio_total = $p * $u;
                //Sentencia del pedido
                $sql = "INSERT INTO pedidos (usuario_id, provincia, ciudad, direccion, coste, fecha, unidades, status) VALUES ('$id_usuario', '$provincia', '$ciudad', '$direccion', '$precio_total', CURDATE(), '$numero_productos', '0');";
                $resultado = $db->ejecutar($sql);

                //Si se realiza con exito se procede a las lineas de pedido.
                if ($resultado) {
                    $id_pedido = $db->id_anterior1();
                    $resultado = $this->guardar_linea($id_producto, $id_pedido, $u, $db);
                }

            } else {
                //Varios elementos
                $numero_productos = count($carrito);
                $precio_total_producto = 0;
                $precio_total = 0;
                //Sentencia del pedido
                //$sql = "INSERT INTO pedidos (usuario_id, provincia, ciudad, direccion, coste, fecha, unidades, status) VALUES ('$id_usuario', '$provincia', '$ciudad', '$direccion', '$precio_total', CURDATE(), '$numero_productos', 'status');";
                $provincia = $this->provincia;
                $ciudad = $this->ciudad;
                $direccion = $this->direccion;
                $sql = "INSERT INTO pedidos (usuario_id, provincia, ciudad, direccion, coste, fecha, unidades, status) VALUES ('$id_usuario', '$provincia', '$ciudad', '$direccion', '$precio_total', CURDATE(), '$numero_productos', 'status');";
                $resultado = $db->ejecutar($sql);
                $id_pedido = $db->id_anterior1();
                //Determinar si el guardado del pedido fue exitoso.
                if ($resultado) {
                    foreach ($carrito as $producto) {
                        $id_producto = $producto->id_producto;
                        $p = $producto->precio;
                        $u = $producto->unidades;
                        //Cacular precios
                        $precio_total_producto = $p * $u;
                        $precio_total += $precio_total_producto;
                        //Guardar la linea del pedido.

                        $this->guardar_linea($id_producto, $id_pedido, $u, $db);
                    }
                    $sql = "UPDATE pedidos SET coste=$precio_total";
                    $db->ejecutar($sql);

                }
            }


        } else {
            //error

        }

        $db->cerrar();
        return $resultado;


    }

    public function guardar_linea($id_producto, $id_pedido, $u, $db)
    {
        /*
        El siguiente metodo guarda una linea de producto en la base de datos.
        Nota: Requiere
        -$id_producto -> la id del producto.
        -$id_pedido -> la id del ultimo pedido realizado.
        -$u -> el numero de unidades.
        -$db -> La conexion abierta a la base de datos.
        NOTA: Es importante que este abierta en la misma instancia que la sentencia sql anterior, sino, no reconocera el ultimo registro y dara 0.
        */

        $sql = "INSERT INTO lineas_pedidos (pedido_id, producto_id, unidades) VALUES ($id_pedido, $id_producto,$u)";
        $resultado = $db->ejecutar($sql);
        return $resultado;
    }

    public function conseguir_productos_del_ultimo_pedido()
    {
        $db = new ModeloDB();
        $id = $db->id_anterior2('pedidos');
        $condicion = "pedido_id = $id";
        $productos = $db->conseguir('lineas_pedidos', '*', $condicion);
        $productos = $productos->fetchAll();

        return $productos;
    }

    public function conseguir_datos_del_ultimo_pedido()
    {
        //Consigue los datos del pedido.
        $db = new ModeloDB();
        $id = $db->id_anterior2('pedidos');
        $condicion = "id = $id";
        $datos = $db->conseguir('pedidos', '*', $condicion);
        $pedido = $datos->fetchAll();
        return $pedido;

    }

    public function informacion_pedido($id)
    {
        $db = new ModeloDB();
        $condicion = "pedido_id=$id";
        $productos = $db->conseguir('lineas_pedidos', '*', $condicion);
        $productos = $productos->fetchAll();
        return $productos;

    }

    public function actualizar($id, $nuevo_status)
    {
        //El presente metodo actualiza el estado de un pedido.
        require_once "models/ModeloDB.php";
        $db = new ModeloDB();
        $sql = "UPDATE pedidos SET status = $nuevo_status WHERE id = $id;";
        $db->ejecutar($sql);
        $db->cerrar();

    }
}

?>