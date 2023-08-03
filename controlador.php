<?php
	session_start();
	include "vista.php";
	include "modelo.php";
	include "llamadasApi.php";
	include "llamadasDrive.php";
	if (isset($_GET['accion'])) {
		$accion = $_GET['accion'];
		if($accion == 'login'){
			vmostrarlogin(mloginUsuario());
		}
		elseif($accion == 'asignaturas'){
			vmostrarasignaturas(mcogerasignaturas());
		}elseif($accion == 'cerrarsesion'){
			mcerrarSesion();
		}elseif($accion == 'participantes'){
			vexportarParticipantes(mDescargarExcel());
		}
		elseif($accion == 'contenidoAsignatura'){
			vmostrarContenido(mcogerUsuariosTabla());
		}
		elseif($accion == 'crearSesionAnonima'){
			mcrearSesionAnonima();
		}
		elseif($accion == 'redireccionarInicio'){
			mredireccionarInicio();
		}
		elseif($accion == 'registro'){
			vmostrarregistro(mregistroUsuario());
		}
		elseif($accion == 'getNombre'){
			vmostrarNombre();
		}
		elseif($accion == 'getEstaEnAsignatura'){
			vmostrarBotonAccion(mgetEstaEnAsignatura());
		}
		elseif($accion == 'UNIRMEUsuarioAsignatura'){
			munirmeUsuarioAsignatura();
			$urlAnterior = $_SESSION['urlAnterior'];
			header("Location: $urlAnterior");
		}
		elseif($accion == 'SALIRUsuarioAsignatura' || $accion == 'SALIRMEUsuarioAsignatura' ){
			msalirUsuarioAsignatura();
			$urlAnterior = $_SESSION['urlAnterior'];
			header("Location: $urlAnterior");
		}
		elseif($accion == 'ELIMINARUsuarioAsignatura'){
			meliminarAsignatura();
			mredireccionarInicio();
		}
		elseif($accion == 'crearAsignatura'){
			vCrearAsignatura(mcrearAsignatura());
		}
		elseif($accion == 'subirArchivoaOneDrive'){
			vsubirArchivoaOneDrive(msubirArchivoaOneDrive());
		}
		elseif($accion == 'modificarCuenta'){
			vmostrarDatosModificar(mGetDatosCuenta());
		}
		elseif($accion == 'cambiarDatosBBDD'){
			mactualizarBBDDusuario();
			$urlAnterior = $_SESSION['urlAnterior'];
			header("Location: $urlAnterior");
		}elseif($accion == 'eliminarCuenta'){
			vborrarCuenta(mborrarCuenta());
		}elseif($accion == 'mostrarBotonRecursosDrive'){
			vmostrarBotonRecursosDrive();
		}elseif($accion == 'mostrarChat'){
			vmostrarChat(mObtenerChat());
		}elseif($accion == 'enviarMensaje'){
			vmostrarChat(menviarMensaje());
		}elseif($accion == 'unirUsuarioTeams'){
			unirUsuarioTeams();
			$urlAnterior = $_SESSION['urlAnterior'];
			header("Location: $urlAnterior");
		}elseif($accion == 'sacarUsuarioTeams'){
			sacarUsuarioTeams();
			$urlAnterior = $_SESSION['urlAnterior'];
			header("Location: $urlAnterior");
		}
	}
?>
