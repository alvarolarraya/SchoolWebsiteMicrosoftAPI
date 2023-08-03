<?php
	session_start();
	include "vista.php";
	include "modelo.php";
	include "descargar_csv_de_datos.php";
	include "llamadasApi.php";
	include "llamadasDrive.php";
	//*******COMPROBAR PARAMETROS***************+
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
		elseif($accion == 'SALIRUsuarioAsignatura'){
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
		elseif($accion == 'subirPDFaOneDrive'){
			vSubirPDFaOneDrive(mSubirPDFaOneDrive());
		}
		elseif($accion == 'modificarCuenta'){
			vmostrarDatosModificar(mGetDatosCuenta());
		}
		elseif($accion == 'cambiarDatosBBDD'){
			mactualizarBBDDusuario();
			$urlAnterior = $_SESSION['urlAnterior'];
			header("Location: $urlAnterior");
		}elseif($accion == 'eliminarCuenta'){
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
			echo 'sigue';
		}
	}
?>
