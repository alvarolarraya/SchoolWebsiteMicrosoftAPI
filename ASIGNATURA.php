<?php
	$cadena = file_get_contents("ASIGNATURA.html");
	$cadena = str_replace("##nombreAsignatura##", $_GET['nombreAsignatura'], $cadena);
	echo $cadena;
?>
