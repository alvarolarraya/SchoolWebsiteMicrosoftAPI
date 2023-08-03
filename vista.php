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
			}if($response == -4){
				$cadena = str_replace('##alert##', "contraseñaDebil()", $cadena);
			}if($response == -5){
				$cadena = str_replace('##alert##', "falloApi()", $cadena);
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
				$alert = str_replace('##mensaje##', 'PDF exportado correctamente', $alert);
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
				if($usuario['rol'] == 'profesor'){
					$aux = str_replace('##TEAMS##','Propietario',$aux);
				}else{
					if($usuario['teams'] == 0){
						if($_SESSION['userLevel'] != 'profesor'){
							$aux = str_replace('##TEAMS##','NO UNIDO',$aux);
						}else{
							$idUsuario = $usuario["id"];
							$idAsignatura = $usuario["idAsignatura"];
							$url = "controlador.php?accion=unirUsuarioTeams&id=$idUsuario&idAsignatura=$idAsignatura";
							$aux = str_replace('##TEAMS##',"<a href =$url>unir a Teams</a>",$aux);
						}
					}else{
						if($_SESSION['userLevel'] != 'profesor'){
							$aux = str_replace('##TEAMS##','UNIDO',$aux);
						}else{
							$idUsuario = $usuario["id"];
							$idAsignatura = $usuario["idAsignatura"];
							$url = "controlador.php?accion=sacarUsuarioTeams&id=$idUsuario&idAsignatura=$idAsignatura";
							$aux = str_replace('##TEAMS##',"<a href =$url>sacar de Teams</a>",$aux);
						}

					}
				}
				$cuerpo .= $aux;
			}
			echo $trozos[0].$cuerpo.$trozos[2];
		}else{
			$cadena = str_replace('##fila##','',$cadena);
			$cadena = str_replace('##nombreYApellido##','-',$cadena);
			$cadena = str_replace('##correo##','-',$cadena);
			$cadena = str_replace('##rol##','-',$cadena);
			$cadena = str_replace('##TEAMS##','-',$cadena);

			echo $cadena;
		}
	}

	function vmostrarNombre(){
		if(isset($_SESSION['username'])){
			echo $_SESSION['username'];
		}
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
		if($estaEnAsignatura ==3){
			echo 'ELIMINAR';
		}
		else if($estaEnAsignatura ==2){
			echo 'SALIR';
		}else if($estaEnAsignatura ==1){
			echo 'SALIRME';
		}else{
			if($_SESSION['userLevel'] == 'profesor'){
				echo '';
			}else{
				echo 'UNIRME';
			}
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

	function vsubirArchivoaOneDrive($resultado){
		$alert = "
			<script type=\"text/JavaScript\">
				alert(\"##mensaje##\");
				window.location = '##url##';
			</script>
		";
		if($resultado){
			$alert = str_replace('##mensaje##', 'Archivo subido correctamente', $alert);
		}else{
			$alert = str_replace('##mensaje##', $_FILES['fichero']['name'].'No se ha podido subir el archivo correctamente', $alert);
		}
		$urlAnterior = $_SESSION['urlAnterior'];
		$alert = str_replace('##url##', $urlAnterior, $alert);
		echo $alert;
	}

	function vmostrarDatosModificar($resultado){
		$usuario = mysqli_fetch_assoc($resultado);
		$nombre = $usuario['name'];
		$username = $usuario['username'];
		$lastName = $usuario['lastName'];
		$email = $usuario['email'];
		$rol = $usuario['rol'];
		$plantilla = file_get_contents("MODIFICAR_CUENTA.html");
		$plantilla = str_replace("##valorNombre##",$nombre,$plantilla);
		$plantilla = str_replace("##valorApellido##",$lastName,$plantilla);
		$plantilla = str_replace("##valorUsername##",$username,$plantilla);
		$plantilla = str_replace("##valorEmail##",$email,$plantilla);
		if($rol == 'estudiante'){
			$plantilla = str_replace("##seleccionarEstudiante##",'selected',$plantilla);
			$plantilla = str_replace("##seleccionarProfesor##",'',$plantilla);
		}elseif($rol == 'profesor'){
			$plantilla = str_replace("##seleccionarEstudiante##",'',$plantilla);
			$plantilla = str_replace("##seleccionarProfesor##",'selected',$plantilla);
		}
		echo $plantilla;
	}

	function vborrarCuenta($resultado){
		$alert = "
			<script type=\"text/JavaScript\">
				alert(\"##mensaje##\");
				window.location = '##url##';
			</script>
		";
		if($resultado){
			$alert = str_replace('##mensaje##', 'Cuenta borrada correctamente', $alert);
		}else{
			$alert = str_replace('##mensaje##', 'No se ha podido borrar la cuenta correctamente', $alert);
		}
		$cerrarSesion = "controlador.php?accion=cerrarsesion";
		$alert = str_replace('##url##', $cerrarSesion, $alert);
		echo $alert;
	}

	function vmostrarBotonRecursosDrive(){
		$nombreAsignatura = $_GET['nombreAsignatura'];
		/*
		$nombreCarpeta = str_replace(' ','_',$nombreCarpeta);
		$nombreCarpeta = str_replace('Á','A',$nombreCarpeta);
		$nombreCarpeta = str_replace('É','E',$nombreCarpeta);
		$nombreCarpeta = str_replace('Í','I',$nombreCarpeta);
		$nombreCarpeta = str_replace('Ó','O',$nombreCarpeta);
		$nombreCarpeta = str_replace('Ú','U',$nombreCarpeta);
		$link = mgetUrlCarpetaRecursos($nombreCarpeta);
		*/
		$link = mgetUrlCarpetaRecursosBBDD($nombreAsignatura);
		$url = '';
		if($_SESSION['userLevel'] == 'estudiante'){
			$url = '<a class="u-button-style u-nav-link" href=##link##>RECURSOS</a>';
		}elseif($_SESSION['userLevel'] == 'profesor'){
			$nombreAsignatura = $_GET['nombreAsignatura'];
			$url = '<a class="u-button-style u-nav-link" href=##link##>RECURSOS DRIVE</a><div class="u-nav-popup"><ul class="u-h-spacing-20 u-nav u-unstyled u-v-spacing-10"><li class="u-nav-item"><a class="u-button-style u-nav-link" href=>SUBIR ARCHIVO A DRIVE</a><div class="u-nav-popup"><ul class="u-h-spacing-20 u-nav u-unstyled u-v-spacing-10"><li class="u-nav-item"><form enctype="multipart/form-data" action="controlador.php?accion=subirArchivoaOneDrive&nombreAsignatura=##nombreAsignatura##" method="POST" redirect="true"><input type="file" onchange="this.form.submit()" name="fichero"></form></li></ul></div></ul></div>';
			$url = str_replace('##nombreAsignatura##',$nombreAsignatura,$url);
		}
		$url = str_replace('##link##',$link,$url);
		echo $url;
	}

	function vmostrarChat($array){
		$chat = $array[0];
		$nombreAsignatura = $array[1];
		$cadena = file_get_contents("Página-1.html");
		$trozos = explode('##fila##',$cadena);
		if($chat == -1){
			$trozos[0] = str_replace('##function##', "falloApi()", $trozos[0]);
			$trozos[2] = str_replace("##nombreAsignatura##",$nombreAsignatura,$trozos[2]);
			$trozos[0] = str_replace("##nombreAsignatura##",$nombreAsignatura,$trozos[0]);
			echo $trozos[0].$trozos[2];
		}else{
			$cuerpo = "";
			for ($i = count($chat)-1; $i >= 0; $i--)
			{
				$aux = $trozos[1];
				$aux = str_replace("##mensaje##",$chat[$i][0]." : ".$chat[$i][1],$aux);
				$cuerpo .= $aux;
			}
			$trozos[2] = str_replace("##nombreAsignatura##",$nombreAsignatura,$trozos[2]);
			$trozos[0] = str_replace("##nombreAsignatura##",$nombreAsignatura,$trozos[0]);
			$trozos[0] = str_replace('##function##', "vacio()", $trozos[0]);
			echo $trozos[0].$cuerpo.$trozos[2];
		}
	}
?>
