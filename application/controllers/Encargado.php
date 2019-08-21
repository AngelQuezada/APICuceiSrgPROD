<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(APPPATH.'/libraries/REST_Controller.php');
use Restserver\libraries\REST_Controller;

class Encargado extends REST_Controller {
	public function __construct()
	{
		header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
		header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding");
		header("Access-Control-Allow-Origins: *");
		parent::__construct();
		$this->load->database();
	}
	public function index(){}
    public function altaencargado_post() {
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
		//VALIDA SI EL ENCARGADO NO EXISTE EN LA BD
		$nombre = $this->post('nombre');
		$aPaterno = $this->post('aPaterno');
		$aMaterno = $this->post('aMaterno');
		$this->db->select('*');
		$this->db->where('a_paterno',$aPaterno)->or_where('a_materno',$aMaterno)->or_where('nombre',$nombre);
		$query = $this->db->get('encargadoList')->result();
		if(!empty($query)){
			$respuesta = array('error' => TRUE,
								'mensaje' => 'El encargado existe.');
			$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
			return;
		}
		//SE PREPARAN LOS DATOS A INSERTAR
		$this->db->reset_query();
		$datos = array('nombre' => $nombre,
					   'a_paterno' => $aPaterno,
					   'a_materno' => $aMaterno);

		$this->db->insert('encargadoList',$datos);
		//SE ENVIA LA RESPUESTA
		$respuesta = array('error' => FALSE,
							'mensaje' => 'Se ha dado de alta el encargado correctamente.');
		$this->response($respuesta);
	}
	public function encargados_get() {
		$this->db->select('*');
		$query = $this->db->get('encargadoList');
		$this->response($query->result());
	}
	public function buscaencargado_get($aPaterno,$aMaterno,$nombre) {
		$name = urldecode($nombre);
		$this->db->select('*');
		$this->db->like('a_paterno',$aPaterno)->or_like('a_materno',$aMaterno)->or_like('nombre',$name);
		$query = $this->db->get('encargadoList')->result();
		if(empty($query)){
			$respuesta = array('error' => TRUE,
								'mensaje' => 'No hay resultados');
			$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
			return;
		}
		$this->response($query);
	}
	public function asignarencargado_post() {
		$token = $this->post('token');
		$folio = $this->post('folio');
		$idUsuario = $this->post('idUsuario');
		$idPersonal = $this->post('idPersonal');
		if(empty($token) || empty($idUsuario) ){
			$respuesta = array('error' => TRUE,
								'mensaje' => 'No Autorizado');
			$this->response($respuesta,REST_Controller::HTTP_UNAUTHORIZED);
			return;
		}
		if($idPersonal == null){
			$respuesta = array('error' => TRUE,
								'mensaje' => 'No hay encargado por Asignar o el correo es invalido');
			$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
			return;
		}
		$this->db->reset_query();
		//VALIDA QUE NO SEA EL MISMO ENCARGADO
		$query = $this->db->query("SELECT id FROM encargado WHERE folioReporte = {$folio}");
		$row = $query->row();
		//$idPersonalq = $row->id;

		if($row){
			$respuesta = array('error' => TRUE,
							'mensaje' => 'Ya se ha asignado el encargado.');
		$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
			return;
		}
		$this->db->reset_query();
		//VALIDAR SI EL REPORTE YA FUE CANCELADO
		$this->db->select('idStatus');
		$this->db->where('folio',$folio);
		$query = $this->db->get('statusReporte')->result();
		if($query == 4){
		$respuesta = array('error' => TRUE,
							'mensaje' => 'Ya se ha cancelado el reporte, no se puede asignar un encargado.');
		$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
			return;
		}
		$this->db->reset_query();
		$condiciones = array('folioReporte' => $folio,
							 'idPersonal' => $idPersonal);
		$resultado = $this->db->insert('encargado',$condiciones);
		$respuesta = array('error' => FALSE,
						   'mensaje' => 'Se ha asignado correctamente el encargado.');
		$this->response($respuesta);
	}
	public function getreporteencargado_get() {
		$query = $this->db->query('SELECT r.folio, encargadoList.nombre,encargadoList.a_paterno,encargadoList.a_materno, encargadoList.id FROM encargadoList INNER JOIN encargado ON encargadoList.id=encargado.idPersonal INNER JOIN reporteManten r ON encargado.folioReporte=r.folio');
		$this->response($query->result());
	}
	public function getreporte_get($folio) {
		$query = $this->db->query("SELECT encargadoList.id,encargadoList.nombre,encargadoList.a_paterno,encargadoList.a_materno FROM encargadoList INNER JOIN encargado ON encargadoList.id=encargado.idPersonal WHERE encargado.folioReporte={$folio}");
		$this->response($query->result());
	}
	public function bajaencargado_post() {
		$token = $this->post('token');
		$folio = $this->post('folio');
		$idUsuario = $this->post('idUsuario');
		$idPersonal = $this->post('idPersonal');
		if($token === "" || $idUsuario === ""){
			$respuesta = array('error' => TRUE,
								'mensaje' => 'No Autorizado');
			$this->response($respuesta,REST_Controller::HTTP_UNAUTHORIZED);
			return;
		}
		if($idPersonal == null){
			$respuesta = array('error' => TRUE,
								'mensaje' => 'No hay encargado por Asignar o el correo es invalido');
			$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
			return;
		}
		$this->db->reset_query();
		//VALIDAR SI EL REPORTE YA FUE CANCELADO
		$this->db->select('idStatus');
		$this->db->where('folio',$folio);
		$query = $this->db->get('statusReporte')->result();
		if($query == 4){
		$respuesta = array('error' => TRUE,
							'mensaje' => 'Ya se ha cancelado el reporte, no se puede eliminar el encargado.');
		$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
			return;
		}
		$this->db->reset_query();
		//PREPARAN DATOS
		$this->db->where('idPersonal', $idPersonal);
		$this->db->delete('encargado');
		$respuesta = array('error' => FALSE,
						   'mensaje' => 'Se ha quitado el encargado correctamente.');
		$this->response($respuesta);
	}
}