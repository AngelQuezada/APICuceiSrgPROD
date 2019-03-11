<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once(APPPATH.'/libraries/REST_Controller.php');
use Restserver\libraries\REST_Controller;

class Usuario extends REST_Controller
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
	 //TODO: CUANDO SE CREE METODO CREAR USUARIO PONER COMO STATUS 1 POR DEFECTO
	public function usuarios_get($correo){
		$query = $this->db->query("SELECT id FROM usuario WHERE correo='".$correo."'");
			$this->response($query->result());
	}
	public function totalusuarios_get(){
		$query = $this->db->query('SELECT * FROM usuario');
		$cantidad;
		if(!$query){
			$cantidad = 0;
			$this->response($cantidad);
			return;
		}
		$this->response($query->num_rows());
	}
	public function banearusuario_post(){
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
		$query = $this->db->get('personal')->result_array();
		foreach ($query as $key) {
				$status = $key['status'];
			}
			if($status !== '3'){
				$respuesta = array('error' => TRUE,
								'mensaje' => 'El Usuario NO Administrador del Sistema.');
			$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
				return;
			}
			$this->db->reset_query();
		//VERIFICA SI EL USUARIO ESTA BANEADO
		$this->db->select('status');
		$this->db->where('correo',$correo);
		$query = $this->db->get('usuario')->result_array();
		foreach ($query as $key) {
			$status = $key['status'];
		 }
		 if($status == '2'){
			$respuesta = array('error' => TRUE,
								'mensaje' => 'El Usuario ya está dado de Baja.');
			$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
			return;
		 }
		$this->db->reset_query();
		//VERIFICA SI EL CORREO NO EXISTE EN BD
		$condiciones = array('correo' => $correo);
		$this->db->where($condiciones);
		$query = $this->db->get('usuario');
		$existe = $query->row();
		if (!$existe) {
			$respuesta = array('error' => TRUE,
								'mensaje' => 'El Correo NO existe.');
			$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
			return;
		}
		$this->db->reset_query();
		//ACTUALIZA EL STATUS DEL PERSONAL
		$condiciones = array('status' => '2');
		$this->db->where('correo',$correo);
		$resultado = $this->db->update('usuario',$condiciones);
		$respuesta = array('error' => FALSE,
						   'mensaje' => 'Se ha dado de baja el usuario correctamente.');
		$this->response($respuesta);
	}
	public function habilitarusuario_post(){
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
		$query = $this->db->get('personal')->result_array();
		foreach ($query as $key) {
				$status = $key['status'];
			}
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
		$query = $this->db->get('usuario');
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
		$query = $this->db->get('usuario')->result_array();
		foreach ($query as $key) {
			$status = $key['status'];
		 }
		 if($status == '1'){
			$respuesta = array('error' => TRUE,
								'mensaje' => 'El Usuario ya está dado de Alta.');
			$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
			return;
		 }
		$this->db->reset_query();	
		$condiciones = array('status' => '1');
		$this->db->where('correo',$correo);
		$resultado = $this->db->update('usuario',$condiciones);
		$respuesta = array('error' => FALSE,
						   'mensaje' => 'El Usuario se ha habilitado Correctamente.');
		$this->response($respuesta);
	}
}