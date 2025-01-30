<?php
class centinela
{
    public static function verificar($datos_session)
    {   /*El presente metodo tiene como funcion determinar 
        si se recibe uno o varios elementos a procesar en la variable
        dada.*/
        $contador = 0;
        $centinela = false;
        foreach($datos_session as $d){
            if(is_object($d)){
                $contador += 1;
            }
        }
        
        if($contador == 1){
            $centinela = true;
        }
        
        return $centinela;
}
}

?>