<?php
	function conexion () {
		//Conexion local
		//$con = mysqli_connect("localhost", "root", "", "siw");

		//Conexion nube
		$con = mysqli_connect("dbserver", "grupo41", "EiHahHing9", "db_grupo41");

		return $con;
	}
	function mayusContra($cadenas){
		for($i=0;$i<strlen($cadenas);$i++){
		    if (ctype_upper($cadenas[$i])) {
		        return True;
		    }
		}
		return False;
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
			if(strlen($_POST['password']) > 8 && ctype_alnum($_POST['password']) && mayusContra($_POST['password'])){
				//prueba username
				$prueba = "select username from final_usuarios where username = '$username'";
				$resultado = $con->query($prueba);
				if($resultado->num_rows == 0){
					$username = str_replace(" ","",$username);
					$acces_token = mOpenAccesToken();
					$_SESSION['token_admin'] = $acces_token;
					$displayName = $name." ".$surname;
					$n = crearUsuario($acces_token,$displayName,$username,$_POST['password']);
					if($n == -1){
						return -5;//problema con la api
					}
					$userMail = "$username@upna460.onmicrosoft.com";
					$idPersona =  obtenerIdUsuario($userMail,$acces_token);
					$n = asignarLicenciaUser($acces_token,$idPersona);
					if($n == -1){
						return -5;//problema con la api
					}
					//$username = str_replace(' ','',$username);
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
				return -4;//cotraseña debil.
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
			$acces_token = mOpenAccesToken();
			$_SESSION['token_admin'] = $acces_token;
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
			$nombreAsignatura = $_GET['nombreAsignatura'];
			escribirExcel($participantes,$format,$nombreAsignatura);
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
					/***CAMBIO***/
					$consulta = "select U.id,U.name,U.lastName,U.email,U.rol,UA.teams,UA.idAsignatura from final_usuarios U, final_UsuarioAsignatura UA where U.id in $idsusuarios and UA.idUsuario in $idsusuarios and UA.idAsignatura = '$idAsignatura' and U.id = UA.idUsuario;";
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
	//********la he cambiado**************//
	function mgetEstaEnAsignatura(){
		if(mEsPropietarioAsignatura()){
			return 3;
		}else{
			$con = conexion();
			$idAsignatura = $_GET['idAsignatura'];
			$idUsuario = $_SESSION['userIdBBDD'];
			$consulta = "select id,teams from final_UsuarioAsignatura where idAsignatura = '$idAsignatura' and idUsuario = '$idUsuario'";
			$resultado = $con->query($consulta);
			if ($resultado->num_rows == 0){
				return 0;
			}
			$datos = mysqli_fetch_assoc($resultado);
			if($datos['teams'] == 1){
				return 2;
			}else{
				return 1;
			}
		}
	}

	function mEsPropietarioAsignatura(){
		$con = conexion();
		$idAsignatura = $_GET['idAsignatura'];
		$idUsuario = $_SESSION['userIdBBDD'];
		$consulta = "select id from final_Asignaturas where id = '$idAsignatura' and idpropietario = '$idUsuario'";
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
		crearCarpetaDrive();
		$idCurso = $_GET['idCurso'];
		$semestre = $_GET['semestre'];
		$nombreAsignatura = $_POST['nombreAsignatura'];
		$nombreAsignatura = str_replace('Á','A',$nombreAsignatura);
		$nombreAsignatura = str_replace('É','E',$nombreAsignatura);
		$nombreAsignatura = str_replace('Í','I',$nombreAsignatura);
		$nombreAsignatura = str_replace('Ó','O',$nombreAsignatura);
		$nombreAsignatura = str_replace('Ú','U',$nombreAsignatura);
		$nombreAsignatura = str_replace('á','a',$nombreAsignatura);
		$nombreAsignatura = str_replace('é','e',$nombreAsignatura);
		$nombreAsignatura = str_replace('í','i',$nombreAsignatura);
		$nombreAsignatura = str_replace('ó','o',$nombreAsignatura);
		$nombreAsignatura = str_replace('ú','u',$nombreAsignatura);
		$nombreAsignatura = strtoupper($nombreAsignatura);
		$idUsuario = $_SESSION['userIdBBDD'];
		$acces_token =  mOpenAccesToken();
		crearGrupoTeams($acces_token,$nombreAsignatura,"");
		$idEquipo = obtenerIdGrupo($nombreAsignatura,$acces_token);
		crearCanal($acces_token, $idEquipo);
		$nombreCarpeta = str_replace(' ','_',$nombreAsignatura);
		$link = mgetUrlCarpetaRecursos($nombreCarpeta);
		$consulta = "insert into final_Asignaturas (curso,semestre,nombre,idpropietario,urlCarpetaRecursos) values ('$idCurso', '$semestre', '$nombreAsignatura','$idUsuario','$link')";
		$con = conexion();
		$resultado = $con->query($consulta);
		if($resultado){
			$idAsignatura = $con->insert_id;
			$consulta = "insert into final_UsuarioAsignatura (idUsuario,idAsignatura) values ('$idUsuario', '$idAsignatura')";
			$resultado = $con->query($consulta);
			return 1;
		}else{
			return -1;
		}
	}

	function meliminarAsignatura(){
		$idUsuario = $_GET['idUsuario'];
		$idAsignatura = $_GET['idAsignatura'];
		$nombreCarpeta = $_GET['nombreAsignatura'];
		$nombreCarpeta = str_replace('Á','A',$nombreCarpeta);
		$nombreCarpeta = str_replace('É','E',$nombreCarpeta);
		$nombreCarpeta = str_replace('Í','I',$nombreCarpeta);
		$nombreCarpeta = str_replace('Ó','O',$nombreCarpeta);
		$nombreCarpeta = str_replace('Ú','U',$nombreCarpeta);
		$nombreAsignatura = $nombreCarpeta;
		$nombreCarpeta = str_replace(' ','_',$nombreCarpeta);
		$acces_token =  mOpenAccesToken();
		$idGrupo = obtenerIdGrupo($nombreAsignatura,$acces_token);
		eliminarGrupoTeams($acces_token,$idGrupo);
		eliminarCarpeta($nombreCarpeta);
		$consulta = "delete from final_UsuarioAsignatura where idAsignatura=$idAsignatura;";
		$con = conexion();
		$resultado = $con->query($consulta);
		if($resultado){
			$consulta = "delete from final_Asignaturas where id=$idAsignatura;";
			$con = conexion();
			$resultado = $con->query($consulta);
		}
	}

	function msubirArchivoaOneDrive(){
		$fichero = $_FILES['fichero'];
		$nombreCarpeta = $_GET['nombreAsignatura'];
		$nombreCarpeta = str_replace(' ','_',$nombreCarpeta);
		$nombreCarpeta = str_replace('Á','A',$nombreCarpeta);
		$nombreCarpeta = str_replace('É','E',$nombreCarpeta);
		$nombreCarpeta = str_replace('Í','I',$nombreCarpeta);
		$nombreCarpeta = str_replace('Ó','O',$nombreCarpeta);
		$nombreCarpeta = str_replace('Ú','U',$nombreCarpeta);
		$nombreFichero = str_replace(' ','_',$fichero['name']);
		move_uploaded_file($fichero['tmp_name'],$nombreFichero);
		$response = subirArchivoaOneDrive($nombreCarpeta);
		unlink($nombreFichero);
		return $response;
	}

	function mOpenAccesToken(){
		$filename =  $_SERVER['DOCUMENT_ROOT'] ."/final/datos_token.json";
		//$filename = "datos_token.json";
		$res = file_get_contents($filename);
		$result = json_decode($res,true);
		$acces_token = $result["access_token"];
		return $acces_token;
	}

	function mGetDatosCuenta(){
		$idUsuario = $_SESSION['userIdBBDD'];
		$consulta = "select username,name,lastName,email,rol from final_usuarios where id=$idUsuario;";
		$con = conexion();
		$resultado = $con->query($consulta);
		return $resultado;
	}

	function mactualizarBBDDusuario(){
		$idUsuario = $_SESSION['userIdBBDD'];
		$name = $_POST['name'];
		$username = $_POST['username'];
		$surname = $_POST['surname'];
		$email = $_POST['email'];
		$rol = $_POST['select'];
		//llamada
		$acces_token = $_SESSION['token_admin'];
		//tengo el name nuevo
		$displayName = $name." ".$surname;
		//***OBTENGO USERNAME ANTERIOR
		$consulta = "select username from final_usuarios where id=$idUsuario;";
		$con = conexion();
		$resultado = $con->query($consulta);
		$datos = mysqli_fetch_assoc($resultado);
		$usernameAnt = $datos['username'];
		$userMail = "$usernameAnt@upna460.onmicrosoft.com";
		$idPersona =  obtenerIdUsuario($userMail,$acces_token);
		//actualizo
		actualizarUsuario($acces_token,$idPersona,$displayName,$username);
		$_SESSION['username'] = $username;
		$_SESSION['userLevel'] = $rol;
		$consulta = "update final_usuarios set username = '$username', name = '$name', lastName = '$surname', email = '$email', rol = '$rol' where id=$idUsuario";
		$resultado = $con->query($consulta);
		return $resultado;
	}

	function mborrarCuenta(){
		$confirmacion = "
			<script type=\"text/JavaScript\">
				confirmacion = confirm('Su cuenta se borrará si acepta');
				if(!confirmacion){
					window.location = '##url##';
				}
			</script>
		";
		$urlAnterior = $_SESSION['urlAnterior'];
		$confirmacion = str_replace('##url##',$urlAnterior,$confirmacion);
		echo $confirmacion;
		//si sigue aqui es porque el usuario ha confirmado
		$acces_token = $_SESSION['token_admin'];
		$idUsuario = $_SESSION['userIdBBDD'];
		$consulta = "select username from final_usuarios where id=$idUsuario;";
		$con = conexion();
		$resultado = $con->query($consulta);
		$datos = mysqli_fetch_assoc($resultado);
		$usernameAnt = $datos['username'];
		$userMail = "$usernameAnt@upna460.onmicrosoft.com";
		$idPersona =  obtenerIdUsuario($userMail,$acces_token);
		$n = eliminarUsuario($acces_token,$idPersona);
		if($n == -1){
			return -1;
		}
		$consulta = "delete from final_UsuarioAsignatura where idUsuario=$idUsuario";
		$con = conexion();
		$resultado = $con->query($consulta);
		if($resultado){
			$consulta = "delete from final_usuarios where id=$idUsuario";
			$con = conexion();
			$resultado = $con->query($consulta);
		}
		return $resultado;
	}

	function mgetUrlCarpetaRecursos($nombreCarpeta){
		return obtenerLinkCarpeta($nombreCarpeta);
	}

	function unirUsuarioTeams(){
		$idUsuario = $_GET['id'];
		$idAsignatura = $_GET['idAsignatura'];
		$con = conexion();
		$consulta1 = "select nombre from final_Asignaturas where id = $idAsignatura";
		//***si va bien**/
		if($resultado = $con->query($consulta1)){
			$datos = mysqli_fetch_assoc($resultado);
			$nombreAsignatura = $datos['nombre'];
			$consulta2 = "select username from final_usuarios where id = $idUsuario";
			if($resultado = $con->query($consulta2)){
				$datos = mysqli_fetch_assoc($resultado);
				$username = $datos['username'];
				//HAGO LLAMADA API
				$acces_token =  mOpenAccesToken();
				$idGrupo = obtenerIdGrupo($nombreAsignatura,$acces_token);
				$userMail = "$username@upna460.onmicrosoft.com";
				$idPersona =  obtenerIdUsuario($userMail,$acces_token);
				agregarMiembroTeams($acces_token, $idPersona, $idGrupo);
				//YA AGREGADO EL MIEMBRO A TEAMS LO MUESTRO EN LA BBDD
				$consulta3 = "update final_UsuarioAsignatura set teams = '1' where idUsuario=$idUsuario and idAsignatura = $idAsignatura";
				if($resultado = $con->query($consulta3)){
					return 1;
				}else{
					return -1;
				}
			}
		}
	}
	function mObtenerChat(){
		//$filename =  $_SERVER['DOCUMENT_ROOT'] ."/final/datos_token.json";
		//****LLAMAR LUEGO A LA FUNCIÓN
		if(isset($_GET["nombreAsignatura"])){
			$nombreAsignatura = $_GET["nombreAsignatura"];
		}else{
			$nombreAsignatura = $_POST["nombreAsignatura"];
		}
		$acces_token = mOpenAccesToken();
		$idEquipo =   obtenerIdGrupo($nombreAsignatura,$acces_token);
		$idCanal =  buscarIdCanal($acces_token,$idEquipo, "FORO");
		$chat = enumerarMensajes($acces_token, $idEquipo, $idCanal);
		return array($chat,$nombreAsignatura);
	}

	function mEnviarMensaje(){
		$acces_token = mOpenAccesToken();
		$nombreAsignatura = $_POST["nombreAsignatura"];
		$idEquipo =   obtenerIdGrupo($nombreAsignatura,$acces_token);
		$idCanal =  buscarIdCanal($acces_token,$idEquipo, "FORO");
		$mensaje = $_POST['mensaje'];
		enviarMensaje($acces_token,$idCanal,$idEquipo,$mensaje);
		return mObtenerChat();
	}

	function sacarUsuarioTeams(){
		$idUsuario = $_GET['id'];
		$idAsignatura = $_GET['idAsignatura'];
		$con = conexion();
		$consulta1 = "select nombre from final_Asignaturas where id = $idAsignatura";
		//***si va bien**/
		if($resultado = $con->query($consulta1)){
			$datos = mysqli_fetch_assoc($resultado);
			$nombreAsignatura = $datos['nombre'];
			$consulta2 = "select username from final_usuarios where id = $idUsuario";
			if($resultado = $con->query($consulta2)){
				$datos = mysqli_fetch_assoc($resultado);
				$username = $datos['username'];
				//HAGO LLAMADA API
				$acces_token =  mOpenAccesToken();
				$idGrupo = obtenerIdGrupo($nombreAsignatura,$acces_token);
				$userMail = "$username@upna460.onmicrosoft.com";
				$idPersona =  obtenerIdUsuario($userMail,$acces_token);
				eliminarMiembroTeams($acces_token, $idPersona, $idGrupo);
				//YA AGREGADO EL MIEMBRO A TEAMS LO MUESTRO EN LA BBDD
				$consulta3 = "update final_UsuarioAsignatura set teams = '0' where idUsuario=$idUsuario and idAsignatura = $idAsignatura";
				if($resultado = $con->query($consulta3)){
					return 1;
				}else{
					return -1;
				}
			}
		}

	}

	function mgetUrlCarpetaRecursosBBDD($nombreAsignatura){
		$consulta = "select urlCarpetaRecursos from final_Asignaturas where nombre='$nombreAsignatura';";
		$con = conexion();
		$resultado = $con->query($consulta);
		$datos = mysqli_fetch_assoc($resultado);
		$url = $datos['urlCarpetaRecursos'];
		return $url;
	}
?>
