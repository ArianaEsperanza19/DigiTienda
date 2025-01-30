<?php
require_once './config/DB.php';
class verificarIdentidad
{
    # verificar
    # id
    # nombre (no estatica)
    public $conexion;

    public function __construct()
    {
        $this->conexion = DB::Connect();
    }
    public static function verificar()
    {
        $centinela = null;
        if (isset($_SESSION['identidad'])) {
            $centinela = true;
        } else {
            $centinela = false;
        }
        return $centinela;
    }

    public static function id()
    {
        //Verifica si el usuario esta identificado y devuelve el id en la variable de sesion.
        $id = null;
        if (isset($_SESSION['identidad'])) {
            $id = $_SESSION['identidad'];
        } else {
            $id = "No logueado";
        }
        return $id;
    }

    public function nombre()
    {
        //Busca en la base de datos el nombre del usuario identificado.
        //Devuelve un string comun y corriente.
        //Si no hay usuario logueado, entonces develve un string anunciandolo.
        //Nota: a diferencia de los demas metodos en esta clase, este no es estatico y necesita ser instanciado.
        $nombre = null;
        if (isset($_SESSION['identidad'])) {
            $id = $_SESSION['identidad'];
            $query = $this->conexion->prepare("SELECT nombre FROM usuarios WHERE id=$id;");
            $query->execute();
            $nombre = $query->fetch();
            $nombre = $nombre["nombre"];
            $this->conexion = NULL;
        } else {
            $nombre = "No logueado";
        }

        return $nombre;
    }


}
