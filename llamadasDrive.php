<?php
	function obtenerIdLibro($nombreLibro,$access_token,$curl) {
		curl_setopt_array($curl, array(
		  CURLOPT_URL => 'https://graph.microsoft.com/v1.0/me/drive/root/children?$select=id,name',
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'GET',
		  CURLOPT_HTTPHEADER => array(
			"Authorization: Bearer $access_token"
		  ),
		));

		$response = curl_exec($curl);
		$datos = json_decode($response);
		for ($driveItem=0; $driveItem < count($datos->{'value'}); $driveItem++) {

			$nombre = $datos->{'value'}[$driveItem]->{'name'};

			if($nombre == "$nombreLibro.xlsx"){
				$idLibro = $datos->{'value'}[$driveItem]->{'id'};
			}

		}
		return $idLibro;
	}

	function crearPaginaLibro($access_token,$curl,$idLibro,$nombrePagina) {
		$parametros = '{
		  "name": "##nombrePagina##"
		}';
		$parametros = str_replace("##nombrePagina##", $nombrePagina, $parametros);
		curl_setopt_array($curl, array(
		  CURLOPT_URL => "https://graph.microsoft.com/v1.0/me/drive/items/$idLibro/workbook/worksheets/",
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'POST',
		  CURLOPT_POSTFIELDS =>$parametros,
		  CURLOPT_HTTPHEADER => array(
			'Content-type: application/json',
			"Authorization: Bearer $access_token"
		  ),
		));
		$response = curl_exec($curl);
		return $response;
	}

	function meterTabla($access_token,$curl,$tabla,$idLibro,$primeraCelda,$ultimaCelda) {
		$url = str_replace('##idlibro##',$idLibro,'https://graph.microsoft.com/v1.0/me/drive/items/##idlibro##/workbook/worksheets(\'Hoja1\')/range(address=\'##primeraCelda##:##ultimaCelda##\')');
		$url = str_replace('##primeraCelda##',$primeraCelda,$url);
		$url = str_replace('##ultimaCelda##',$ultimaCelda,$url);
		curl_setopt_array($curl, array(
		  CURLOPT_URL => $url,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'PATCH',
		  CURLOPT_POSTFIELDS =>$tabla,
		  CURLOPT_HTTPHEADER => array(
			"Authorization: Bearer $access_token"
		  ),
		));
		$response = curl_exec($curl);
		return $response;
	}

	function addressbyrowcol($row,$col) {
		return getColFromNumber($col).$row;
	}

	function getColFromNumber($num) {
		$numeric = ($num - 1) % 26;
		$letter = chr(65 + $numeric);
		$num2 = intval(($num - 1) / 26);
		if ($num2 > 0) {
			return getColFromNumber($num2) . $letter;
		} else {
			return $letter;
		}
	}

	function descargarPdf($access_token,$curl,$idLibro,$nombreFichero) {
		$url = str_replace('##idLibro##',$idLibro,'https://graph.microsoft.com/v1.0/me/drive/items/##idLibro##/content?format=pdf');
		curl_setopt_array($curl, array(
		  CURLOPT_URL => $url,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'GET',
		  CURLOPT_HTTPHEADER => array(
			"Authorization: Bearer $access_token"
		  ),
		));
		$response = curl_exec($curl);
		if(file_put_contents($nombreFichero.".pdf",$response)){
			//echo "<br>PDF GUARDADO CORRECTAMENTE<br><br>";
		}else{
			echo "<br>NO SE HA PODIDO GUARDAR EL PDF<br><br>";
		}
		header("Content-disposition: attachment; filename=$nombreFichero.pdf");
		header("Content-type: application/pdf");
		readfile("$nombreFichero.pdf");
		unlink("$nombreFichero.pdf");
		return $response;
	}

	function descargarCsv($access_token,$curl,$idLibro,$nombreFichero) {
		$url = str_replace('##idLibro##',$idLibro,'https://graph.microsoft.com/v1.0/me/drive/items/##idLibro##/content');
		curl_setopt_array($curl, array(
		  CURLOPT_URL => $url,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'GET',
		  CURLOPT_HTTPHEADER => array(
			"Authorization: Bearer $access_token"
		  ),
		));
		$response = curl_exec($curl);
		if(file_put_contents($nombreFichero.".xlsx",$response)){
			//echo "<br>CSV GUARDADO CORRECTAMENTE<br><br>";
		}else{
			//echo "<br>NO SE HA PODIDO GUARDAR EL CSV<br><br>";
		}
		header("Content-disposition: attachment; filename=$nombreFichero.xlsx");
		header("Content-type: application/pdf");
		readfile("$nombreFichero.xlsx");
		unlink("$nombreFichero.xlsx");
		return $response;
	}

	function crearWorkbook($nombreWorkbook){
		$curl = curl_init();
		$fichero = 'Libro.xlsx';
		$datos = file('datos_token.json')[0];
		$token = json_decode($datos);
		$access_token = $token->{'access_token'};
		$url = 'https://graph.microsoft.com/v1.0/me/drive/root:/##nombreWorkbook##:/content';
		$url = str_replace('##nombreWorkbook##',"$nombreWorkbook.xlsx",$url);

		curl_setopt_array($curl, array(
		  CURLOPT_URL => $url,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'PUT',
		  CURLOPT_POSTFIELDS => file_get_contents($fichero),
		  CURLOPT_HTTPHEADER => array(
			'Content-Type: application/form-data',
			"Authorization: Bearer $access_token"
		  ),
		));
		$response = curl_exec($curl);
		curl_close($curl);
		return $response;
	}

	function escribirExcel($datos,$format,$nombreAsignatura){
		$curl = curl_init();
		$ficheroToken = file('datos_token.json')[0];
		$token = json_decode($ficheroToken);
		$access_token = $token->{'access_token'};
		$nombreWorkbook = str_replace(' ','_',$nombreAsignatura);
		$nombreWorkbook = str_replace('Á','A',$nombreWorkbook);
		$nombreWorkbook = str_replace('É','E',$nombreWorkbook);
		$nombreWorkbook = str_replace('Í','I',$nombreWorkbook);
		$nombreWorkbook = str_replace('Ó','O',$nombreWorkbook);
		$nombreWorkbook = str_replace('Ú','U',$nombreWorkbook);
		$nombreWorkbook = 'participantes_'.strtolower($nombreWorkbook);
		crearWorkbook($nombreWorkbook);
		$idLibro = obtenerIdLibro($nombreWorkbook,$access_token,$curl);
		//crearPaginaLibro($access_token,$curl,$idLibro,"nueva pagina2");

		$tabla = array('values'=>$datos);
		$matriz = $tabla['values'];
		$filas = count($matriz);
		$columnas = count($matriz[0]);
		//minimo es 1
		$primeraFila = 1;
		$primeraColumna = 1;
		$primeraCelda = addressbyrowcol($primeraFila,$primeraColumna);
		$ultimaCelda = addressbyrowcol($filas+$primeraFila-1,$columnas+$primeraColumna-1);
		$tabla = json_encode($tabla);
		$response = meterTabla($access_token,$curl,$tabla,$idLibro,$primeraCelda,$ultimaCelda);
		//echo "se ha añadido la tabla<br><br>";
		if($format == "pdf"){
			descargarPdf($access_token,$curl,$idLibro,$nombreWorkbook);
		}else if($format == "csv"){
			descargarCsv($access_token,$curl,$idLibro,$nombreWorkbook);
		}
		curl_close($curl);
	}

	function subirArchivoaOneDrive($nombreCarpeta){
		$curl = curl_init();
		$fichero = $_FILES['fichero'];
		$nombreFichero = $_FILES['fichero']['name'];
		$nombreFichero = str_replace(' ','_',$nombreFichero);
		$datos = file('datos_token.json')[0];
		$token = json_decode($datos);
		$access_token = $token->{'access_token'};
		$idCarpeta = obtenerIdCarpeta($nombreCarpeta,$access_token,$curl);
		//al root de drive
		//$url = 'https://graph.microsoft.com/v1.0/me/drive/root:/##nombrePDF##:/content';
		$url = 'https://graph.microsoft.com/v1.0/me/drive/items/##idCarpeta##:/##nombrePDF##:/content';
		$url = str_replace('##nombrePDF##',$nombreFichero,$url);
		$url = str_replace('##idCarpeta##',$idCarpeta,$url);

		curl_setopt_array($curl, array(
		  CURLOPT_URL => $url,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'PUT',
		  CURLOPT_POSTFIELDS => file_get_contents($nombreFichero),
		  CURLOPT_HTTPHEADER => array(
			'Content-Type: application/form-data',
			"Authorization: Bearer $access_token"
		  ),
		));
		$response = curl_exec($curl);
		curl_close($curl);
		return $response;
	}

	function crearCarpetaDrive(){
		$curl = curl_init();
		$nombreCarpeta = $_POST['nombreAsignatura'];
		$nombreCarpeta = str_replace(' ','_',$nombreCarpeta);
		$nombreCarpeta = str_replace('Á','A',$nombreCarpeta);
		$nombreCarpeta = str_replace('É','E',$nombreCarpeta);
		$nombreCarpeta = str_replace('Í','I',$nombreCarpeta);
		$nombreCarpeta = str_replace('Ó','O',$nombreCarpeta);
		$nombreCarpeta = str_replace('Ú','U',$nombreCarpeta);
		$nombreCarpeta = str_replace('á','a',$nombreCarpeta);
		$nombreCarpeta = str_replace('é','e',$nombreCarpeta);
		$nombreCarpeta = str_replace('í','i',$nombreCarpeta);
		$nombreCarpeta = str_replace('ó','o',$nombreCarpeta);
		$nombreCarpeta = str_replace('ú','u',$nombreCarpeta);
		$nombreCarpeta = strtoupper($nombreCarpeta);
		$datos = file('datos_token.json')[0];
		$token = json_decode($datos);
		$access_token = $token->{'access_token'};
		curl_setopt_array($curl, array(
		  CURLOPT_URL => 'https://graph.microsoft.com/v1.0/me/drive/root/children',
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'POST',
		  CURLOPT_POSTFIELDS =>"{
		    \"name\": \"$nombreCarpeta\",
		    \"folder\": {}
		}",
		  CURLOPT_HTTPHEADER => array(
		    'Content-type: application/json',
		    "Authorization: Bearer $access_token"
		  ),
		));

		$response = curl_exec($curl);

		curl_close($curl);
	}

	function obtenerIdCarpeta($nombreCarpeta,$access_token,$curl) {
		curl_setopt_array($curl, array(
		  CURLOPT_URL => 'https://graph.microsoft.com/v1.0/me/drive/root/children?$select=id,name',
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'GET',
		  CURLOPT_HTTPHEADER => array(
			"Authorization: Bearer $access_token"
		  ),
		));

		$response = curl_exec($curl);
		$datos = json_decode($response);
		for ($driveItem=0; $driveItem < count($datos->{'value'}); $driveItem++) {

			$nombre = $datos->{'value'}[$driveItem]->{'name'};

			if($nombre == $nombreCarpeta){
				$idCarpeta = $datos->{'value'}[$driveItem]->{'id'};
			}

		}
		return $idCarpeta;
	}

	function obtenerLinkCarpeta($nombreCarpeta){
		$curl = curl_init();
		$datos = file('datos_token.json')[0];
		$token = json_decode($datos);
		$access_token = $token->{'access_token'};
		$idCarpeta = obtenerIdCarpeta($nombreCarpeta,$access_token,$curl);


		$url = 'https://graph.microsoft.com/v1.0/me/drive/items/##idCarpeta##/createLink';
		$url = str_replace('##idCarpeta##',$idCarpeta,$url);

		curl_setopt_array($curl, array(
			CURLOPT_URL => $url,
  		  CURLOPT_RETURNTRANSFER => true,
  		  CURLOPT_ENCODING => '',
  		  CURLOPT_MAXREDIRS => 10,
  		  CURLOPT_TIMEOUT => 0,
  		  CURLOPT_FOLLOWLOCATION => true,
  		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'POST',
		  CURLOPT_POSTFIELDS => '{
		  "type": "view",
		  "scope": "anonymous"
		}',
		  CURLOPT_HTTPHEADER => array(
			'Content-Type: application/json',
			"Authorization: Bearer $access_token"
		  ),
		));
		$response = curl_exec($curl);
		curl_close($curl);
		$datos = json_decode($response);
		$link = $datos->{'link'}->{'webUrl'};
		return $link;
	}

	function eliminarCarpeta($nombreCarpeta){
		$curl = curl_init();
		$datos = file('datos_token.json')[0];
		$token = json_decode($datos);
		$access_token = $token->{'access_token'};
		$idCarpeta = obtenerIdCarpeta($nombreCarpeta,$access_token,$curl);

		$url = 'https://graph.microsoft.com/v1.0/me/drive/items/##idCarpeta##';
		$url = str_replace('##idCarpeta##',$idCarpeta,$url);

		curl_setopt_array($curl, array(
		  CURLOPT_URL => $url,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'DELETE',
		  CURLOPT_HTTPHEADER => array(
			'Content-Type: application/json',
			"Authorization: Bearer $access_token"
		  ),
		));
		$response = curl_exec($curl);
		curl_close($curl);
		$datos = json_decode($response);
		echo $response;
		return $response;
	}
?>
