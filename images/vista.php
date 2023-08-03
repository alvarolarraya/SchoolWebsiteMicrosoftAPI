<?php
	function vmostrarregistro ($response) {
		if($response < 0){
			$cadena =  file_get_contents("REGISTRO.html");
			if($response == -1){
				$cadena = str_replace('##alert##', "ContraseñaDiferente()", $cadena);
			}
			if($response == -2){
				$cadena = str_replace('##alert##', "falloConsulta()", $cadena);
			}
			if($response == -3){
				$cadena = str_replace('##alert##', "usuariosDuplicados()", $cadena);
			}
			echo $cadena;
		}
		else{
			$cadena = file_get_contents("INICIO2.html");
			$cadena = str_replace("##username##", $_SESSION['username'], $cadena);
			echo $cadena;
		}
	}
	function vmostrarlogin($response) {
		if($response < 0){
			$cadena =  file_get_contents("LOGIN.html");
			if($response == -1){
				$cadena = str_replace('##alert##', "loginIncorrecto()", $cadena);
			}
			echo $cadena;
		}
		else{
			header('Location: INICIO2.html');
		}
	}
	function vmostrarasignaturas($resultado) {
		if (!is_object($resultado)) {
			echo "adios";
		} else {
			$cadena = file_get_contents("asignaturas.html");

			$cuerpo = "";
			$idUsuario = $_SESSION['userIdBBDD'];
			while ($datos = $resultado->fetch_assoc()) {
				$aux = str_replace("##nombreAsignatura##", $datos["nombre"], $cadena);
				$nombreAsignatura = $datos["nombre"];
				$idAsignatura = $datos['id'];
				$url = "controlador.php?accion=contenidoAsignatura&nombreAsignatura=$nombreAsignatura&idAsignatura=$idAsignatura&idUsuario=$idUsuario";
				$aux = str_replace("##url##", $url, $aux);
				$cuerpo .= $aux;
			}
			if($_SESSION['userLevel'] == 'profesor')
			{
				$idCurso = $_GET['idcurso'];
				$semestre = $_GET['semestre'];
				$url = "controlador.php?accion=crearAsignatura&idCurso=$idCurso&semestre=$semestre";
				$formNuevaAsignatura = file_get_contents("crearAsignatura.html");
				$formNuevaAsignatura = str_replace("##url##", $url, $formNuevaAsignatura);
				$cuerpo .= $formNuevaAsignatura;
			}

			echo  $cuerpo ;
		}
	}

	function vexportarParticipantes($resultado){
		$alert = "
			<script type=\"text/JavaScript\">
				alert(\"##mensaje##\");
				window.location = '##url##';
			</script>
		";
		if($resultado == 1){
			$format = $_GET['format'];
			if($format == "pdf"){
				$alert = str_replace('##mensaje##', 'PDF exportado correctamnete', $alert);
			}else if($format == "csv"){
				$alert = str_replace('##mensaje##', 'CSV exportado correctamente', $alert);
			}
		}else{
			$alert = str_replace('##mensaje##', 'No se ha podido realizar la exportacion correctamente', $alert);
		}
		$nombreAsignatura = $_GET["nombreAsignatura"];
		$idAsignatura = $_GET["idAsignatura"];
		if($_SESSION['userLevel'] == "anonimo"){
			$idUsuario = -1;
		}else{
			$idUsuario = $_SESSION['userIdBBDD'];
		}
		$url = "controlador.php?accion=contenidoAsignatura&nombreAsignatura=$nombreAsignatura&idAsignatura=$idAsignatura&idUsuario=$idUsuario";
		$alert = str_replace('##url##', $url, $alert);
		echo $alert;
	}

	function vmostrarContenido($usuarios) {
		$cadena = file_get_contents("ASIGNATURA.html");
		$cadena = str_replace("##nombreAsignatura##", $_GET['nombreAsignatura'], $cadena);
		if($usuarios->num_rows != 0){
			$trozos = explode('##fila##',$cadena);
			$cuerpo = '';
			while($usuario = mysqli_fetch_assoc($usuarios)){
				$aux = str_replace('##nombreYApellido##',$usuario['name'].' '.$usuario['lastName'],$trozos[1]);
				$aux = str_replace('##correo##',$usuario['email'],$aux);
				$aux = str_replace('##rol##',$usuario['rol'],$aux);
				$cuerpo .= $aux;
			}
			echo $trozos[0].$cuerpo.$trozos[2];
		}else{
			$cadena = str_replace('##fila##','',$cadena);
			$cadena = str_replace('##nombreYApellido##','-',$cadena);
			$cadena = str_replace('##correo##','-',$cadena);
			$cadena = str_replace('##rol##','-',$cadena);
			echo $cadena;
		}
	}

	function vmostrarNombre(){
		echo $_SESSION['username'];
		$pagina = $_GET['urlAnterior'];
		if($pagina == 'INICIO2'){
			$_SESSION['urlAnterior'] = "INICIO2.html";
		}elseif($pagina == 'ASIGNATURA'){
			$nombreAsignatura = $_GET['nombreAsignatura'];
			$idAsignatura = $_GET['idAsignatura'];
			$idUsuario = $_GET['idUsuario'];
			$_SESSION['urlAnterior'] = "controlador.php?accion=contenidoAsignatura&nombreAsignatura=$nombreAsignatura&idUsuario=$idUsuario&idAsignatura=$idAsignatura";
		}
	}

	function vmostrarBotonAccion($estaEnAsignatura){
		if($estaEnAsignatura){
			$esPropietario = mEsPropietarioAsignatura();
			if($esPropietario){
				echo 'ELIMINAR';
			}else{
				echo 'SALIR';
			}
		}else{
			echo 'UNIRME';
		}
	}

	function vCrearAsignatura($resultado){
		$alert = "
			<script type=\"text/JavaScript\">
				alert(\"##mensaje##\");
				window.location = '##url##';
			</script>
		";
		if($resultado == 1){
			$alert = str_replace('##mensaje##', 'Asignatura creada correctamente', $alert);
		}else{
			$alert = str_replace('##mensaje##', 'No se ha podido crear la asignatura correctamente', $alert);
		}
		$urlAnterior = $_SESSION['urlAnterior'];
		$alert = str_replace('##url##', $urlAnterior, $alert);
		echo $alert;
	}

	function vSubirPDFaOneDrive($resultado){
		$alert = "
			<script type=\"text/JavaScript\">
				alert(\"##mensaje##\");
				window.location = '##url##';
			</script>
		";
		if($resultado == 1){
			$alert = str_replace('##mensaje##', 'PDF subido correctamente', $alert);
		}else{
			$alert = str_replace('##mensaje##', 'No se ha podido subir el PDF correctamente', $alert);
			print_r($resultado);
		}
		$urlAnterior = $_SESSION['urlAnterior'];
		//$alert = str_replace('##url##', $urlAnterior, $alert);
		echo $alert;
	}
?>
