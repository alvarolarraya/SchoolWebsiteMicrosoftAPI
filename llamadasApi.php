<?php
function conectarApi(){
  $curl = curl_init();
  return $curl;
}
function crearGrupoTeams($acces_token,$name,$description){
  $curl = conectarApi();

  curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://graph.microsoft.com/v1.0/teams',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS =>"{
    'template@odata.bind': 'https://graph.microsoft.com/v1.0/teamsTemplates(\'standard\')',
    'displayName': '$name',
    'description': '$description',
    'scope':'https://graph.microsoft.com/.default Team.Create  Group.ReadWrite.All Directory.ReadWrite.All'
  }",
    CURLOPT_HTTPHEADER => array(
      "Authorization: Bearer $acces_token",
      "Content-Type: application/json"
    ),
  ));

  $response = curl_exec($curl);

  curl_close($curl);
  $response = json_decode($response);
  if(isset($response->error)){
      return -1;
  }else{
      return 1;
  }

}
function eliminarGrupoTeams($acces_token,$idGrupo){
  $curl = conectarApi();

  curl_setopt_array($curl, array(
  CURLOPT_URL => "https://graph.microsoft.com/v1.0/groups/$idGrupo",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'DELETE',
  CURLOPT_HTTPHEADER => array(
    "Authorization: Bearer $acces_token"
    ),
  ));

  $response = curl_exec($curl);

  curl_close($curl);
  $response = json_decode($response);
  if(isset($response->error)){
      return -1;
  }else{
      return 1;
  }
}
//****ESTA PARTE REVISAR PARA AGREGAR A UN MIEMBRO NORMAL****//
function agregarMiembroTeams($acces_token, $idUsuario, $idGrupo){
  $curl = conectarApi();
    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://graph.microsoft.com/v1.0/groups/'."$idGrupo".'/members/$ref',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    //****ESTA PARTE REVISAR PARA AGREGAR A UN MIEMBRO NORMAL****//
    CURLOPT_POSTFIELDS =>"{
    '@odata.id': 'https://graph.microsoft.com/v1.0/users/$idUsuario'
  }",
    CURLOPT_HTTPHEADER => array(
      "Authorization: Bearer $acces_token",
      "Content-Type: application/json"
    ),
  ));

  $response = curl_exec($curl);
  curl_close($curl);
  $response = json_decode($response);
  if(isset($response->error)){
      return -1;
  }else{
      return 1;
  }
}

function eliminarMiembroTeams($acces_token, $idUsuario,$idGrupo){
  $curl = conectarApi();
  curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://graph.microsoft.com/v1.0/groups/'."$idGrupo".'/members/'."$idUsuario".'/$ref',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'DELETE',
    CURLOPT_HTTPHEADER => array(
      "Authorization: Bearer $acces_token"
    ),
  ));

  $response = curl_exec($curl);

  curl_close($curl);
 $response = json_decode($response);
  if(isset($response->error)){
      return -1;
  }else{
      return 1;
  }
}

function obtenerIdUsuario($userMail,$acces_token){
  $curl = conectarApi();
  curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://graph.microsoft.com/v1.0/users?$select=id,userPrincipalName',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array(
      "Authorization: Bearer $acces_token"
    ),
  ));

  $response = curl_exec($curl);

  curl_close($curl);
  $response = json_decode($response);
  if(!isset($response->value)){
      return -1;
  }else{
      $usuarios = $response->value;
      $num = count($usuarios);
      $i = 0;
      $idUsuario = -1;
      while ($idUsuario == -1 && $i < $num){
        $usuario = $usuarios[$i];
        if($usuario->userPrincipalName == $userMail){
          $idUsuario = $usuario->id;
        }
        $i++;
      }
      return $idUsuario;
  }
}

function obtenerIdGrupo($nombreGrupo,$acces_token){
  $nombreGrupo = str_replace('Á','A',$nombreGrupo);
  $nombreGrupo = str_replace('É','E',$nombreGrupo);
  $nombreGrupo = str_replace('Í','I',$nombreGrupo);
  $nombreGrupo = str_replace('Ó','O',$nombreGrupo);
  $nombreGrupo = str_replace('Ú','U',$nombreGrupo);
  $nombreGrupo = str_replace('á','a',$nombreGrupo);
  $nombreGrupo = str_replace('é','e',$nombreGrupo);
  $nombreGrupo = str_replace('í','i',$nombreGrupo);
  $nombreGrupo = str_replace('ó','o',$nombreGrupo);
  $nombreGrupo = str_replace('ú','u',$nombreGrupo);
  $curl = conectarApi();
  curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://graph.microsoft.com/v1.0/me/joinedTeams?$select=id,displayName',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array(
      "Authorization: Bearer $acces_token"
    ),
  ));

  $response = curl_exec($curl);

  curl_close($curl);
  $response = json_decode($response);
  if(!isset($response->value)){
      return -1;
  }else{
      $equipos = $response->value;
      $num = count($equipos);
      $i = 0;
      $idEquipo = -1;
      while ($idEquipo == -1 && $i < $num){
        $equipo = $equipos[$i];
        if($equipo->displayName == $nombreGrupo){
          $idEquipo = $equipo->id;
        }
        $i = $i + 1;
      }
      return $idEquipo;
  }
}
function enviarMensaje($acces_token,$idCanal,$idEquipo,$mensaje){
  $curl = conectarApi();

  curl_setopt_array($curl, array(
    CURLOPT_URL => "https://graph.microsoft.com/v1.0/teams/$idEquipo/channels/$idCanal/messages",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS =>"{
    'body': {
      'content': '$mensaje'
    }
  }
  ",
    CURLOPT_HTTPHEADER => array(
      "Authorization: Bearer $acces_token",
      "Content-Type: application/json"
    ),
  ));

  $response = curl_exec($curl);

  curl_close($curl);
  $response = json_decode($response);
  if(isset($response->error)){
      return -1;
  }else{
      return 1;
  }
  //echo $response;
}

function responderMensaje($acces_token,$idCanal,$idEquipo,$mensaje,$idMensaje){
  $curl = conectarApi();

  curl_setopt_array($curl, array(
    CURLOPT_URL => "https://graph.microsoft.com/v1.0/teams/$idEquipo/channels/$idCanal/messages/$idMensaje/replies",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS =>"{
    'body': {
      'contentType': 'html',
      'content': '$mensaje'
    }
  }",
    CURLOPT_HTTPHEADER => array(
      "Authorization: Bearer $acces_token",
      "Content-Type: application/json"
    ),
  ));

  $response = curl_exec($curl);

  curl_close($curl);
  $response = json_decode($response);
  if(isset($response->error)){
      return -1;
  }else{
      return 1;
  }
}
function enumerarMensajes($acces_token, $idEquipo, $idCanal){
  $curl = conectarApi();
  curl_setopt_array($curl, array(
    CURLOPT_URL => "https://graph.microsoft.com/v1.0/teams/$idEquipo/channels/$idCanal/messages",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array(
      "Authorization: Bearer $acces_token"
    ),
  ));

  $response = curl_exec($curl);
  curl_close($curl);
  $response = json_decode($response);
  if(!isset($response->value)){
      return -1;
  }else{
      $mensajes = $response->value;
      $num = count($mensajes);
      $i = 0;
      $chat = array();
      for($i = 0; $i<$num;$i = $i +1){
        $chat[$i] = array($mensajes[$i]->from->user->displayName,$mensajes[$i]->body->content);
      }
      return $chat;
  }
}
function enumerarRespuestasMensajes($acces_token, $idEquipo, $idCanal, $idMensaje){
  $curl = conectarApi();
  curl_setopt_array($curl, array(
    CURLOPT_URL => "https://graph.microsoft.com/v1.0/teams/$idEquipo/channels/$idCanal/messages/$idMensaje/replies",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array(
      "Authorization: Bearer $acces_token"
    ),
  ));

  $response = curl_exec($curl);

  curl_close($curl);
  $response = json_decode($response);
  if(!isset($response->value)){
      return -1;
  }else{
      $mensajes = $response->value;
      $num = count($mensajes);
      $i = 0;
      $chat = array();
      for($i = 0; $i<$num;$i = $i +1){
        $chat[$i] = array($mensajes[$i]->from->user->displayName,$mensajes[$i]->body->content);
      }
      return $chat;
  }
}

function buscarIdCanal($acces_token,$idGrupo, $nameGrupo){
  $curl = conectarApi();
  curl_setopt_array($curl, array(
    CURLOPT_URL => "https://graph.microsoft.com/v1.0/teams/$idGrupo/channels",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array(
      "Authorization: Bearer $acces_token"
    ),
  ));

  $response = curl_exec($curl);

  curl_close($curl);
  $response = json_decode($response);
  if(!isset($response->value)){
      return -1;
  }else{
      $grupos = $response->value;
      $num = count($grupos);
      $i = 0;
      $idGrupo = -1;
      while ($idGrupo == -1 && $i < $num){
        $grupo = $grupos[$i];
        if($grupo->displayName == $nameGrupo){
          $idGrupo = $grupo->id;
        }
        $i++;
      }
      return $idGrupo;
  }
}

function crearCanal($acces_token, $idEquipo){
  $curl = conectarApi();
  curl_setopt_array($curl, array(
    CURLOPT_URL => "https://graph.microsoft.com/v1.0/teams/$idEquipo/channels",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS =>'{
    "displayName": "FORO",
    "description": "This channel is where we debate all future architecture plans",
    "membershipType": "standard"
  }',
    CURLOPT_HTTPHEADER => array(
      "Authorization: Bearer $acces_token",
      "Content-Type: application/json"
    ),
  ));

  $response = curl_exec($curl);

  curl_close($curl);
  $response = json_decode($response);
  if(isset($response->error)){
      return -1;
  }else{
      return $response->id;
  }
}
function crearUsuario($acces_token,$name,$username,$psswd){
  $curl = conectarApi();
  curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://graph.microsoft.com/v1.0/users/',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS =>"{
    'accountEnabled': true,
    'displayName': '$name',
    'mailNickname': '$username',
    'userPrincipalName': '$username@upna460.onmicrosoft.com',
    'officeLocation':'Spain',
    'usageLocation':'US',
    'mail':'$username@upna460.onmicrosoft.com',
    'passwordProfile' : {
      'forceChangePasswordNextSignIn': false,
      'password': '$psswd'
    }
  }",
    CURLOPT_HTTPHEADER => array(
      "Authorization: Bearer $acces_token",
      "Content-Type: application/json"
    ),
  ));

  $response = curl_exec($curl);

  curl_close($curl);
  $response = json_decode($response);
  if(!isset($response->id)){
      return -1;
  }else{
      return $response->id;
  }
}

function asignarLicenciaUser($acces_token,$idUsuario){
  $curl = conectarApi();
  curl_setopt_array($curl, array(
    CURLOPT_URL => "https://graph.microsoft.com/v1.0/users/$idUsuario/assignLicense",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS =>'
  {
    "addLicenses": [
      {
        "disabledPlans": [ ],
        "skuId": "c7df2760-2c81-4ef7-b578-5b5392b571df"
      }
    ],
    "removeLicenses": [ ]
  }',
    CURLOPT_HTTPHEADER => array(
      "Authorization: Bearer $acces_token",
      "Content-Type: application/json"
    ),
  ));

  $response = curl_exec($curl);
  curl_close($curl);
  $response = json_decode($response);
  if(isset($response->error)){
      return -1;
  }else{
      return 1;
  }

  }

  function eliminarUsuario($acces_token,$idUsuario){
    $curl = conectarApi();
    curl_setopt_array($curl, array(
    CURLOPT_URL => "https://graph.microsoft.com/v1.0/users/$idUsuario",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'DELETE',
    CURLOPT_HTTPHEADER => array(
      "Authorization: Bearer $acces_token"
    ),
  ));

  $response = curl_exec($curl);

  curl_close($curl);
  $response = json_decode($response);
  if(isset($response->error)){
      return -1;
  }else{
      return 1;
  }
  }
  //PENSAR PARAMETROS****
  function actualizarUsuario($acces_token,$idUsuario,$name,$username){
    $curl = conectarApi();
    curl_setopt_array($curl, array(
    CURLOPT_URL => "https://graph.microsoft.com/v1.0/users/$idUsuario",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'PATCH',
    CURLOPT_POSTFIELDS =>"{
    'displayName': '$name',
    'mailNickname': '$username',
    'userPrincipalName': '$username@upna460.onmicrosoft.com',
    'mail':'$username@upna460.onmicrosoft.com',
  }",
    CURLOPT_HTTPHEADER => array(
      "Authorization: Bearer $acces_token",
      "Content-Type: application/json"
    ),
  ));

  $response = curl_exec($curl);

  curl_close($curl);
  $response = json_decode($response);
  if(!isset($response->id)){
      return -1;
  }else{
      return 1;
  }
  }

?>