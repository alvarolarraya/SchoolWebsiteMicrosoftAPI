<?php
	function conexion () {
		//Conexion local
		//$con = mysqli_connect("localhost", "root", "", "siw");

		//Conexion nube
		$con = mysqli_connect("dbserver", "grupo41", "EiHahHing9", "db_grupo41");

		return $con;
	}

	function mregistroUsuario(){
		//crear conexion
		$con = conexion();
		//guardar datos
		$name = $_POST['name'];
		$username = $_POST['username'];
		$surname = $_POST['surname'];
		$email = $_POST['email'];
		$password = md5($_POST['password']);
		$password2 = md5($_POST['password2']);
		$rol = $_POST['select'];
		if($password == $password2){
			//prueba username
			$prueba = "select username from final_usuarios where username = '$username'";
			$resultado = $con->query($prueba);
			if($resultado->num_rows == 0){
				$consulta = "insert into final_usuarios (username, name, lastname, email, password,rol) values ('$username', '$name', '$surname', '$email', '$password', '$rol')";
				$resultado = $con->query($consulta);
				if ($resultado) {
					$id = $con->insert_id;
					$_SESSION['username'] = $username;
					$_SESSION['psswd'] = $password;
					$_SESSION['userLevel'] = $rol;
					$_SESSION['userIdBBDD'] = $id;
					return 1;
				} else {
					return -2;//mala consulta
				}
			}else{
				return -3;//username duplicado
			}
		}else{
			return -1;//passwords diferentes
		}


	}
	function mloginUsuario(){
		//crear conexion
		$con = conexion();
		$username = $_POST['username'];
		$password = md5($_POST['password']);
		//print_r(md5($password));
		$prueba = "select username,password,rol,id from final_usuarios where username = '$username' and password = '$password'";
		$resultado = $con->query($prueba);
		$usuario = mysqli_fetch_assoc($resultado);
		if($resultado->num_rows == 0){
			return -1; //usuario no existe
		}else{
			$_SESSION['username'] = $username;
			$_SESSION['psswd'] = $password;
			$_SESSION['userLevel'] = $usuario['rol'];
			$_SESSION['userIdBBDD'] = $usuario['id'];
			return 1;
		}

	}

	function mcogerasignaturas(){
		//crear conexion
		$con = conexion();
		$idCurso = $_GET['idcurso'];
		$semestre = $_GET['semestre'];
		$consulta = "select nombre,id from final_Asignaturas where curso = '$idCurso' and semestre = '$semestre'";
		$resultado = $con->query($consulta);
		if ($resultado) {
			return $resultado;
		} else {
			return -1;
		}

	}

	function mDescargarExcel(){
		$con = conexion();
		$format = $_GET['format'];
		$idAsignatura = $_GET['idAsignatura'];
		$consulta = "select idUsuario from final_UsuarioAsignatura where idAsignatura = '$idAsignatura'";
		//primera consulta para ver que usuarios hay en esa asignatura
		$resultado = $con->query($consulta);
		if($resultado){
			//Array que lo usare para escribir en la tabla de excel
			$participantes = array();
			$i = 0;
			$participantes[$i] = array("participantes","","","Correo","","","Rol");
			//por cada usuario de esa asignatura
			while($datos = mysqli_fetch_assoc($resultado)){
				$idUsuario = $datos['idUsuario'];
				// segunda consulta pillo su username.email,rol...
				$consulta2 = "select username ,email ,rol from final_usuarios where id = '$idUsuario'";
				$resultado2 = $con->query($consulta2);
				//si consulta es correcta
				if($resultado2){
					$i = $i +1 ;
					$datos2 = mysqli_fetch_assoc($resultado2);
					//inserto los datos al array
					$participantes[$i] = array($datos2['username'],"","",$datos2['email'],"","",$datos2['rol']);
				}else{
					return -1; //error consulta.
				}
			}
			$i= $i+1;
			//Nmero maximo canal teams (250) recorro esas filas para dejarlas en blanco por si hay escrito algo en el excel
			for($j = $i; $j < 251; $j++){
				$participantes[$j] = array("","","","","","","");
			}
			//llamo a la funcion escribir excwel de descargar csv de datos (TE LO HE MODIFICADO UN POCO (LE PONGO EL TOKEN A MANO.))
			escribirExcel($participantes,$format);
			return 1; //si llego aqui todo correcto
		}else{
			return -1;//error consulta.
		}
	}

	function mcogerusuariosTabla(){
		$con = conexion();
		$nombreAsignatura = $_GET['nombreAsignatura'];
		$consulta = "select id from final_Asignaturas where nombre = '$nombreAsignatura'";
		$resultado = $con->query($consulta);
		if($resultado){
			while($fila = mysqli_fetch_assoc($resultado)){
				$idAsignatura = $fila['id'];
			}
			$consulta = "select idUsuario from final_UsuarioAsignatura where idAsignatura = '$idAsignatura'";
			$resultado2 = $con->query($consulta);
			if($resultado2){
				if($resultado2->num_rows != 0){
					$idsusuarios = '(';
					while($fila = mysqli_fetch_assoc($resultado2)){
						$idUsuario = $fila['idUsuario'];
						$idsusuarios .= "'$idUsuario',";
					}

					$idsusuarios = substr_replace($idsusuarios, ')', -1);
					$consulta = "select name,lastName,email,rol from final_usuarios where id in $idsusuarios";
					$resultado3 = $con->query($consulta);
					if($resultado3){
						return $resultado3;
					}else{
						return -1;
					}
				}else{
					return $resultado2;
				}
			}else{
				return -1;
			}
		}else{
			return -1;//error consulta
		}
	}

	function mcrearSesionAnonima(){
		$_SESSION['userLevel'] = 'anonimo';
		$_SESSION['userIdBBDD'] = -1;
	}

	function mcerrarSesion(){
		$_SESSION['userLevel'] = 'anonimo';
		$_SESSION['userIdBBDD'] = -1;
		$_SESSION['userLevel'] = 'anonimo';
		$_SESSION['username'] = '';
		unset($_SESSION['passwd']);
		unset($_SESSION['urlAnterior']);
		mredireccionarInicio();
	}

	function mredireccionarInicio(){
		if ($_SESSION['userLevel'] == 'anonimo'){
			header('Location: INICIO.html');
		}else{
			header('Location: INICIO2.html');
		}
	}

	function mgetEstaEnAsignatura(){
		//***************
		//si es usuario es anonimo no va a estar
		if($_SESSION['userLevel'] == 'anonimo'){
			return false;
		}
		$con = conexion();
		$idAsignatura = $_GET['idAsignatura'];
		$idUsuario = $_SESSION['userIdBBDD'];
		$consulta = "select id from final_UsuarioAsignatura where idAsignatura = '$idAsignatura' and idUsuario = '$idUsuario'";
		$resultado = $con->query($consulta);
		if ($resultado->num_rows == 0){
			return false;
		}
		return true;
	}

	function msalirUsuarioAsignatura(){
		$idUsuario = $_GET['idUsuario'];
		$idAsignatura = $_GET['idAsignatura'];
		$consulta = "delete from final_UsuarioAsignatura where idUsuario=$idUsuario and idAsignatura=$idAsignatura;";
		$con = conexion();
		$resultado = $con->query($consulta);
	}

	function munirmeUsuarioAsignatura(){
		$idUsuario = $_GET['idUsuario'];
		$idAsignatura = $_GET['idAsignatura'];
		$consulta = "insert into final_UsuarioAsignatura (idAsignatura,idUsuario) values ('$idAsignatura', '$idUsuario')";
		$con = conexion();
		$resultado = $con->query($consulta);
	}

	function mcrearAsignatura(){
		$idCurso = $_GET['idCurso'];
		$semestre = $_GET['semestre'];
		$nombreAsignatura = $_POST['nombreAsignatura'];
		$consulta = "insert into final_Asignaturas (curso,semestre,nombre) values ('$idCurso', '$semestre', '$nombreAsignatura')";
		$con = conexion();
		$resultado = $con->query($consulta);
		$urlAnterior = $_SESSION['urlAnterior'];
		echo $consulta.'<br>';
		print_r($resultado);
		//header("Location: $urlAnterior");
	}
?>
