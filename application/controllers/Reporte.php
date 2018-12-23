<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(APPPATH.'/libraries/REST_Controller.php');
use Restserver\Libraries\REST_Controller;

class Reporte extends REST_Controller {
	public function __construct()
	{
		header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
		header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding");
		header("Access-Control-Allow-Origins: *");

		parent::__construct();
		$this->load->database();
	}

	public function index(){}

	public function nuevo_post(){
		//SI NO SE ENVIA TOKEN
		
		if($this->post('token') === null){
			$respuesta = array('error' => TRUE,
								'mensaje' => 'No Autorizado');
			$this->reponse($respuesta,REST_Controller::HTTP_BAD_REQUEST);
			return;
		}
		$ubicacionServicio = "'".$this->post('modulo')."' '".$this->post('piso')."' '".$this->post('aula')."'";
		//OBTENER ID Y NOMBRE A PARTIR DEL CORREO DADO
		$correo = $this->post('correo');
		$query = $this->db->query(
			"SELECT id,nombre,a_paterno,a_materno FROM usuario WHERE correo='".$correo."'");
		$data = $query->result_array();
		foreach ($data as $key) {
			$id = $key['id'];
			$nombre = $key['nombre'];
			$aPaterno = $key['a_paterno'];
			$aMaterno = $key['a_materno'];
		}
/*
		$datos = array('recibe' => $this->post('recibe'),
					   'nombre' => $nombre,
					   'aPaterno' => $aPaterno,
					   'aMaterno' => $aMaterno,
					   'telefono' => $this->post('telefono'),
					   'area' => $this->post('area'),
					   'ubicacionServicio' => $ubicacionServicio,
					   'option' => $this->post('option'),
					   'descripcionProblema' => $this->post('descripcionProblema'),
					   'idUsuario' => $idUsuario
					);

		foreach ($infoReporte as $key) {

		}

		$respuesta = array('error' => FALSE,
							'folio' => '125');

		$this->response($respuesta);
*/
		//$datos = array('recibe' => $data['recibe'], );
		//$query = $this->db->get('reportemanten');
	}
}