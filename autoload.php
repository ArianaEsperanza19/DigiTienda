<?php

function autocargar($classname){
	include 'config/otros.php';
	include 'controllers/' . $classname . '.php';
}

spl_autoload_register('autocargar');