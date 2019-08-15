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
	public function alumno_get($correo){
		if ($correo === null) {
			$respuesta = array('error' => TRUE,
								'mensaje' => 'No se envio correo');
			$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
		}
		//VERIFICA SI EL PERSONAL ES ACTIVO
		$this->db->select('status');
		$this->db->where('correo',$correo);
		$query = $this->db->get('usuario')->result_array();
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
		$query = $this->db->get('usuario');
		$informacion = $query->row();
		$this->response($informacion);
	}
	public function loginalumno_post(){
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
		$query = $this->db->get('usuario');
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
		$query = $this->db->get('usuario')->result_array();
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
		$resultado = $this->db->update('usuario',$condiciones);
		$respuesta = array('error' => FALSE,
						   'mensaje' => 'Token agregado',
						   'token' => $token );
		$this->response($respuesta);
	}
	public function nuevoalumno_post(){
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
					   'status' => '3');
		$this->db->insert('usuario',$datos);
		$respuesta = array('error' => FALSE,
						   'mensaje' => 'Se ha registrado el usuario');
		$this->response($respuesta);
	}
	public function reportealumno_get($correo){
		$this->db->select('*');
		$this->db->where('correo',$correo);
		$query = $this->db->get('reporte1Seguridad')->result();
		if(empty($query)){
			$respuesta = array('error' => TRUE,
								'mensaje' => 'No hay resultados');
			$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
			return;
		}
		$this->response($query);
	}
	public function reportes_get($correo){
	$query = $this->db->query("SELECT * FROM reporte1Seguridad WHERE correo = '$correo'");
		$cantidad;
		if(!$query){
			$cantidad = 0;
			$this->response($cantidad);
			return;
		}
		$this->response($query->num_rows());
	}
	public function reportes2_get($correo){
		$query = $this->db->query("SELECT * FROM reporte2Seguridad WHERE correo = '$correo'");
			$cantidad;
			if(!$query){
				$cantidad = 0;
				$this->response($cantidad);
				return;
			}
			$this->response($query->num_rows());
	}
	public function getsreporte2_get($correo){
        $query = $this->db->query("SELECT * FROM reporte2Seguridad WHERE correo = '$correo'");
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
	public function deletetoken_post(){
		$correo = $this->post('correo');
		$token = $this->post('token');
		$condiciones = array('token' => null );
		$this->db->where('correo',$correo);
		$resultado = $this->db->update('usuario',$condiciones);
		$respuesta = array('error' => FALSE,
						   'mensaje' => 'Token eliminado');
		$this->response($respuesta);
	}
}