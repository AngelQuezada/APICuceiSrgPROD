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
	public function index(){
	}
	public function login_post(){
		$correo = $this->post('correo');
		//VERIFICA SI SE ENVIO CORREO
		if ($correo === "") {
			$respuesta = array('error' => TRUE,
								'mensaje' => 'No Autorizado');
			$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
			return;
		}
		//VERIFICA SI EL CORREO NO EXISTE YA EN BD
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
		$query = $this->db->get('personal')->result_array();
		foreach ($query as $key) {
			$status = $key['status'];
			}
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
	public function nuevo_post(){
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
	public function empleado_get($correo){
		if ($correo === null) {
			$respuesta = array('error' => TRUE,
								'mensaje' => 'No se envio correo');
			$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
		}
		//VERIFICA SI EL PERSONAL ES ACTIVO
		$this->db->select('status');
		$this->db->where('correo',$correo);
		$query = $this->db->get('personal')->result_array();
		foreach ($query as $key) {
			$status = $key['status'];
		 }
		 if($status == '2'){
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
	public function deletetoken_post(){
		$correo = $this->post('correo');
		$token = $this->post('token');
		$condiciones = array('token' => null );
		$this->db->where('correo',$correo);
		$resultado = $this->db->update('personal',$condiciones);
		$respuesta = array('error' => FALSE,
						   'mensaje' => 'Token eliminado');
		$this->response($respuesta);
	}
	public function revokepersonal_post(){
		$correo = $this->post('correo');
		$token = $this->post('token');

	}
}