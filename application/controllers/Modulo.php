<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(APPPATH.'/libraries/REST_Controller.php');
use Restserver\Libraries\REST_Controller;

class Modulo extends REST_Controller {

	public function __construct()
	{
		header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
		header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding");
		header("Access-Control-Allow-Origins: *");
		parent::__construct();
		$this->load->database();
	}
	public function index(){}
	public function modulos_get() {
		$this->db->select('id, module_name');
		$query = $this->db->get('moduleList');
		$this->response($query->result());
	}
	public function altamodulo_post(){
		//SI NO SE ENVIA TOKEN NI EL ID DEL USUARIO
		$token = $this->post('token');
		$idUsuario = $this->post('idUsuario');
		if($token === "" || $idUsuario === ""){
			$respuesta = array('error' => TRUE,
								'mensaje' => 'No Autorizado');
			$this->response($respuesta,REST_Controller::HTTP_UNAUTHORIZED);
			return;
		}
		//VALIDAR SI EL TOKEN ENVIADO CORRESPONDE AL ID DEL USUARIO QUE SOLICITA
		$condiciones = array('id' => $idUsuario,
							 'token' => $token );
		$this->db->where($condiciones);
		$query = $this->db->get('personal');
		$existe = $query->row();
		if (!$existe) {
			$respuesta = array('error' => TRUE,
								'mensaje' => 'Usuario y token incorrectos');
			$this->response($respuesta,REST_Controller::HTTP_UNAUTHORIZED);
			return;
		}
		//AQUI YA ESTA VALIDADO EL USUARIO
		$this->db->reset_query();
		$modulo = $this->post('modulo');
		//VALIDAR SI EL MODULO YA EXISTE
		$this->db->select('module_name');
		$this->db->where('module_name',$modulo);
		$query = $this->db->get('moduleList')->result();
		if($query){
			$respuesta = array('error' => TRUE,
								'mensaje' => 'El módulo ya se encuentra registrado.');
			$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
			return;
		}
		//SE DA DE ALTA EL NUEVO MODULO
		$datos = array('module_name' => $modulo );
		$this->db->insert('moduleList',$datos);
		//SE ENVIA LA RESPUESTA
		$respuesta = array('error' => FALSE,
							'mensaje' => 'Se ha registrado el módulo correctamente.');
		$this->response($respuesta);
	}
	public function modmodulo_post() {
		//SI NO SE ENVIA TOKEN NI EL ID DEL USUARIO
		$token = $this->post('token');
		$idUsuario = $this->post('idUsuario');
		if($token === "" || $idUsuario === ""){
			$respuesta = array('error' => TRUE,
								'mensaje' => 'No Autorizado');
			$this->response($respuesta,REST_Controller::HTTP_UNAUTHORIZED);
			return;
		}
		//VALIDAR SI EL TOKEN ENVIADO CORRESPONDE AL ID DEL USUARIO QUE SOLICITA
		$condiciones = array('id' => $idUsuario,
							 'token' => $token );
		$this->db->where($condiciones);
		$query = $this->db->get('personal');
		$existe = $query->row();
		if (!$existe) {
			$respuesta = array('error' => TRUE,
								'mensaje' => 'Usuario y token incorrectos');
			$this->response($respuesta,REST_Controller::HTTP_UNAUTHORIZED);
			return;
		}
		//AQUI YA ESTA VALIDADO EL USUARIO
		$this->db->reset_query();
		$moduloMod = $this->post('moduloMod');
		//VALIDAR SI EL MODULO YA EXISTE
		$this->db->select('module_name');
		$this->db->where('module_name',$moduloMod);
		$query = $this->db->get('moduleList')->result();
		if($query){
			$respuesta = array('error' => TRUE,
								'mensaje' => 'El módulo ya se encuentra registrado.');
			$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
			return;
		}
		//SE MODIFICA EL MODULO
		$idModulo = $this->post('idModulo');
		$condiciones = array('module_name' => $moduloMod);
		$this->db->where('id',$idModulo);
		$resultado = $this->db->update('moduleList',$condiciones);
		//SE ENVIA LA RESPUESTA
		$respuesta = array('error' => FALSE,
							'mensaje' => 'Se ha Modificado el módulo correctamente.');
		$this->response($respuesta);
	}
}