<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(APPPATH.'/libraries/REST_Controller.php');
use Restserver\libraries\REST_Controller;

class Personal extends REST_Controller
{
	public function __construct()
	{
		header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
		header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding");
		header("Access-Control-Allow-Origins: *");
		parent::__construct();
		$this->load->database();
	}
	public function index(){}
	public function login_post() {
		$correo = $this->post('correo');
		//VERIFICA SI SE ENVIO CORREO
		if ($correo === "") {
			$respuesta = array('error' => TRUE,
								'mensaje' => 'No Autorizado');
			$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
			return;
		}
		//VERIFICA SI EL CORREO NO EXISTE EN BD
		$condiciones = array('correo' => $correo);
		$this->db->where($condiciones);
		$query = $this->db->get('personal');
		$existe = $query->row();
		if (!$existe) {
			$respuesta = array('error' => FALSE,
								'code' => '1');
			$this->response($respuesta);
			return;
		}
		$this->db->reset_query();
		//VERIFICA SI EL PERSONAL ES ACTIVO
		$this->db->select('status');
		$this->db->where('correo',$correo);
		$query = $this->db->get('personal')->result();
		$status = $query;
		if($status == '2'){
			$respuesta = array('error' => TRUE,
								'mensaje' => 'Usuario dado de Baja');
			$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
			return;
		}
		$this->db->reset_query();
		//GENERA EL TOKEN RANDOM Y SE INSERTA EN LA DB
		$token = bin2hex(openssl_random_pseudo_bytes(20));
		$condiciones = array('token' => $token );
		$this->db->where('correo',$correo);
		$resultado = $this->db->update('personal',$condiciones);
		$respuesta = array('error' => FALSE,
						   'mensaje' => 'Token agregado',
						   'token' => $token );
		$this->response($respuesta);
	}
	public function nuevopersonal_post() {
		$rol = $this->post('rol');
		$nombre = $this->post('nombre');
		$aPaterno = $this->post('aPaterno');
		$aMaterno = $this->post('aMaterno');
		$correo = $this->post('correo');
		//VALIDA SI EL ARRAY ESTA VACIO
		if (empty($this->post())) {
			$respuesta = array('error' => TRUE,
							   'mensaje' => 'No se envio la informacion necesaria');
			$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
		}
		//VALIDA QUE LOS CAMPOS NECESARIOS ESTAN COMPLETADOS
		if($correo === "" || $nombre === ""  || $aPaterno === "") {
			$respuesta = array('error' => TRUE,
							   'mensaje' => 'No se envio la informacion necesaria');
			$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
		}
		//VALIDAR QUE EL CORREO YA EXISTE
		$condiciones = array('correo' => $correo);
		$this->db->where($condiciones);
		$query = $this->db->get('personal');
		$existe = $query->row();
		if ($existe) {
			$respuesta = array('error' => TRUE,
								'mensaje' => 'El Correo ya esta registrado.');
			$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
			return;
		}
		$this->db->reset_query();
		//PREPARAN LOS DATOS
		$datos = array('nombre' => $nombre,
					   'a_paterno' =>$aPaterno,
					   'a_materno' =>$aMaterno,
					   'correo' => $correo,
					   'status' => $rol);
		$this->db->insert('personal',$datos);
		$respuesta = array('error' => FALSE,
						   'mensaje' => 'Se ha registrado el usuario');
		$this->response($respuesta);
	}
	public function nuevo_post() {
		$nombre = $this->post('nombre');
		$aPaterno = $this->post('aPaterno');
		$aMaterno = $this->post('aMaterno');
		$correo = $this->post('correo');
		//VALIDA SI EL ARRAY ESTA VACIO
		if (empty($this->post())) {
			$respuesta = array('error' => TRUE,
							   'mensaje' => 'No se envio la informacion necesaria');
			$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
		}
		//VALIDA QUE LOS CAMPOS NECESARIOS ESTAN COMPLETADOS
		if($correo === "" || $nombre === ""  || $aPaterno === "") {
			$respuesta = array('error' => TRUE,
							   'mensaje' => 'No se envio la informacion necesaria');
			$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
		}
		$datos = array('nombre' => $nombre,
					   'a_paterno' =>$aPaterno,
					   'a_materno' =>$aMaterno,
					   'correo' => $correo,
					   'status' => '1');
		$this->db->insert('personal',$datos);
		$respuesta = array('error' => FALSE,
						   'mensaje' => 'Se ha registrado el usuario');
		$this->response($respuesta);
	}
	public function empleado_get($correo) {
		if ($correo === null) {
			$respuesta = array('error' => TRUE,
								'mensaje' => 'No se envio correo');
			$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
		}
		//VERIFICA SI EL PERSONAL ES ACTIVO
		$this->db->select('status');
		$this->db->where('correo',$correo);
		$query = $this->db->get('personal')->result();
		if($query == '2'){
			$respuesta = array('error' => TRUE,
								'mensaje' => 'Usuario dado de Baja');
			$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
			return;
		 }
		$this->db->reset_query();
		$condiciones = array('correo' => $correo);
		$this->db->where($condiciones);
		$query = $this->db->get('personal');
		$informacion = $query->row();
		$this->response($informacion);
	}
	public function deletetoken_post() {
		$correo = $this->post('correo');
		$token = $this->post('token');
		$condiciones = array('token' => null );
		$this->db->where('correo',$correo);
		$resultado = $this->db->update('personal',$condiciones);
		$respuesta = array('error' => FALSE,
						   'mensaje' => 'Token eliminado');
		$this->response($respuesta);
	}
	public function revokepersonal_post() {
		$correo = $this->post('correo');
		$token = $this->post('token');
		$idUsuario = $this->post('idUsuario');
		if($token === "" || $correo === "" || $idUsuario === ""){
			$respuesta = array('error' => TRUE,
								'mensaje' => 'No Autorizado');
			$this->response($respuesta,REST_Controller::HTTP_UNAUTHORIZED);
			return;
		}
		//VALIDAR STATUS 3 ADMIN
		$this->db->select('status');
		$this->db->where('id',$idUsuario);
		$query = $this->db->get('personal')->result();
		if($query !== '3'){
			$respuesta = array('error' => TRUE,
							'mensaje' => 'No tienes privilegios suficientes.');
			$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
			return;
		}
		$this->db->reset_query();
		//VERIFICA SI EL PERSONAL ESTA DADO DE BAJA
		$this->db->select('status');
		$this->db->where('correo',$correo);
		$query = $this->db->get('personal')->result();
		if($query == '2'){
			$respuesta = array('error' => TRUE,
								'mensaje' => 'El Usuario ya está dado de Baja.');
			$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
			return;
		 }
		$this->db->reset_query();
		//VERIFICA SI EL CORREO NO EXISTE EN BD
		$condiciones = array('correo' => $correo);
		$this->db->where($condiciones);
		$query = $this->db->get('personal');
		$existe = $query->row();
		if (!$existe) {
			$respuesta = array('error' => TRUE,
								'mensaje' => 'El Correo no existe.');
			$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
			return;
		}
		$this->db->reset_query();
		//ACTUALIZA EL STATUS DEL PERSONAL
		$condiciones = array('status' => '2');
		$this->db->where('correo',$correo);
		$resultado = $this->db->update('personal',$condiciones);
		$respuesta = array('error' => FALSE,
						   'mensaje' => 'Se ha dado de baja el usuario correctamente.');
		$this->response($respuesta);
	}
	public function unrevokepersonal_post() {
		$correo = $this->post('correo');
		$token = $this->post('token');
		$idUsuario = $this->post('idUsuario');
		if($token === "" || $correo === "" || $idUsuario === ""){
			$respuesta = array('error' => TRUE,
								'mensaje' => 'No Autorizado');
			$this->response($respuesta,REST_Controller::HTTP_UNAUTHORIZED);
			return;
		}
		//VALIDAR STATUS 3 ADMIN
		$this->db->select('status');
		$this->db->where('id',$idUsuario);
		$query = $this->db->get('personal')->result();
		if($query !== '3'){
			$respuesta = array('error' => TRUE,
							'mensaje' => 'El Usuario NO Administrador del Sistema.');
			$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
			return;
		}
		$this->db->reset_query();
		//VERIFICA SI EL CORREO NO EXISTE EN BD
		$condiciones = array('correo' => $correo);
		$this->db->where($condiciones);
		$query = $this->db->get('personal');
		$existe = $query->row();
		if (!$existe) {
			$respuesta = array('error' => TRUE,
								'mensaje' => 'El Correo NO existe.');
			$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
			return;
		}
		$this->db->reset_query();
		//VERIFICA SI EL PERSONAL ESTA DADO DE BAJA
		$this->db->select('status');
		$this->db->where('correo',$correo);
		$query = $this->db->get('personal')->result();
		$status = $query;
		 if($status == '1'){
			$respuesta = array('error' => TRUE,
								'mensaje' => 'El Usuario ya está dado de Alta.');
			$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
			return;
		 }
		 if($status == '3'){
			$respuesta = array('error' => TRUE,
								'mensaje' => 'El Usuario es Administrador.');
			$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
			return;
		 }
		$this->db->reset_query();
		$condiciones = array('status' => '1');
		$this->db->where('correo',$correo);
		$resultado = $this->db->update('personal',$condiciones);
		$respuesta = array('error' => FALSE,
						   'mensaje' => 'El Usuario se ha habilitado Correctamente.');
		$this->response($respuesta);
	}

	public function asignarrol_post() {
		$correo = $this->post('correo');
		$token = $this->post('token');
		$idUsuario = $this->post('idUsuario');
		$rol = $this->post('rol');
		if($token === "" || $correo === "" || $idUsuario === ""){
			$respuesta = array('error' => TRUE,
								'mensaje' => 'No Autorizado');
			$this->response($respuesta,REST_Controller::HTTP_UNAUTHORIZED);
			return;
		}
		//VALIR SI SE RECIBE ROL
		if($rol === ""){
			$respuesta = array('error' => TRUE,
								'mensaje' => 'No se recibio tipo de rol');
			$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
			return;
		}
		//VALIDAR STATUS 3 ADMIN
		$this->db->select('status');
		$this->db->where('id',$idUsuario);
		$query = $this->db->get('personal')->result();
		$status = $query;
		if($status !== '3'){
			$respuesta = array('error' => TRUE,
							'mensaje' => 'El Usuario NO Administrador del Sistema.');
			$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
			return;
		}
		$this->db->reset_query();
		//VERIFICA SI EL CORREO NO EXISTE EN BD
		$condiciones = array('correo' => $correo);
		$this->db->where($condiciones);
		$query = $this->db->get('personal');
		$existe = $query->row();
		if (!$existe) {
			$respuesta = array('error' => TRUE,
								'mensaje' => 'El Correo NO existe.');
			$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
			return;
		}
		$this->db->reset_query();
		//VERIFICA SI YA ES STATUS 3 ADMIN
		$this->db->select('status');
		$this->db->where('correo',$correo);
		$query = $this->db->get('personal')->result();
		$status = $query;
		if($status === '3'){
			$respuesta = array('error' => TRUE,
							'mensaje' => 'El Usuario es Administrador del Sistema, no se puede asignar otro rol.');
		$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
			return;
		}
		$this->db->reset_query();
		//VERIFICA SI YA ES STATUS 4 ENCARGADO
		$this->db->select('status');
		$this->db->where('correo',$correo);
		$query = $this->db->get('personal')->result();
		$status = $query;
		if($status === '4'){
			$respuesta = array('error' => TRUE,
							'mensaje' => 'El Usuario ya es encargado.');
		$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
			return;
		}
		$this->db->reset_query();
		//VERIFICA SI YA ES STATUS 5 SERVICIO SOCIAL
		$this->db->select('status');
		$this->db->where('correo',$correo);
		$query = $this->db->get('personal')->result();
		$status = $query;
		if($status === '5'){
			$respuesta = array('error' => TRUE,
							'mensaje' => 'El Usuario ya cuenta con rol de Servicio Social.');
		$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
			return;
		}
		$this->db->reset_query();
		//VERIFICA SI YA ES STATUS 6 SEGURIDAD
		$this->db->select('status');
		$this->db->where('correo',$correo);
		$query = $this->db->get('personal')->result();
		$status = $query;
		if($status === '6'){
			$respuesta = array('error' => TRUE,
							'mensaje' => 'El Usuario es Administrador del Sistema, no se puede asignar otro rol.');
		$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
			return;
		}
		$this->db->reset_query();
		//ACTUALIZA EL STATUS DEL PERSONAL
		$condiciones = array('status' => $rol);
		$this->db->where('correo',$correo);
		$resultado = $this->db->update('personal',$condiciones);
		$respuesta = array('error' => FALSE,
						   'mensaje' => 'Se ha asignado correctamente el rol.');
		$this->response($respuesta);
	}
	// OBTENER ENCARGADOS
	public function getidempleado_get($aPaterno,$aMaterno,$nombre) {
		$condiciones = array('nombre' => $nombre,
							'a_paterno' => $aPaterno,
							'a_materno' => $aMaterno,
							'nombre' => $Nombre);
		$this->db->select('id,nombre,a_paterno,a_materno');
		$this->db->where($condiciones);
		$query = $this->db->get('encargadoList');
		$informacion = $query->row();
		if(!$informacion){
			$respuesta = array('error' => TRUE,
							'mensaje' => 'No hay encargados o el nombre no es valido.');
			$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
			return;
		}
		$this->response($informacion);
	}
	// OBTENER SERVICIO SOCIAL
	public function getidss_get() {
		$condiciones = array('status' => '5');
		$this->db->select('id,correo');
		$this->db->where($condiciones);
		$query = $this->db->get('personal');
		$informacion = $query->row();
		$this->response($informacion);
	}
	public function verifypersonal_get($correo) {
		//VERIFICA SI EL CORREO EXISTE O NO EN BD
		$this->db->select('correo');
		$this->db->where('correo',$correo);
		$query = $this->db->get('personal');
		$existe = $query->row();
		if(!$existe){
			$respuesta = array('error' => TRUE,
							'mensaje' => 'El correo no existe.');
			$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
			return;
		}
		$this->db->reset_query();
		//VERIFICA SI EL USUARIO YA ESTA LOGEADO EN OTRA COMPU O NAVEGADOR
		$this->db->select('token');
		$this->db->where('correo',$correo);
		$query = $this->db->get('personal');
		$existe1 = $query->row();
		if($existe1->token !== NULL){
			$respuesta = array('error' => TRUE,
			'mensaje' => 'Ya tiene una sesión activa, cierre sesión antes de continuar.');
			$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
			return;
		}else{
			$respuesta = array('error' => FALSE,
								'correo' => $correo);
			$this->response($respuesta);
			return;
		}
	}
}
