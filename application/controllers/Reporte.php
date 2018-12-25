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
		//OBTENER ID Y NOMBRE A PARTIR DEL CORREO DEL USUARIO DADO
		$correo = $this->post('correo');
		$this->db->select('id,nombre,a_paterno,a_materno');
		$this->db->where('correo',$correo);
		$query = $this->db->get('usuario')->result_array();

		foreach ($query as $key) {
				   $id = $key['id'];
				   $nombre = $key['nombre'];
		           $aPaterno = $key['a_paterno'];
		           $aMaterno = $key['a_materno'];
		}

		$this->db->reset_query();

		//SE PREPARAN LOS DATOS A INSERTAR
		$ubicacionServicio = "".$this->post('modulo')." ".$this->post('piso')." ".$this->post('aula')."";
		$datos = array('recibe' => $this->post('recibe'),
					   'nombre' => $nombre,
					   'a_paterno' => $aPaterno,
					   'a_materno' => $aMaterno,
					   'telefono' => $this->post('telefono'),
					   'area_solicitante' => $this->post('area'),
					   'ubicacion_servicio' => $ubicacionServicio,
					   'descripcion_servicio' => 'Descripcion Problema',
					   'descripcion_problema' => $this->post('option'),
					   'idUsuario' => $id
					);
		$this->db->insert('reportemanten',$datos);
		//OBTENER EL ULTIMO FOLIO REGISTRADO
		$ultimoFolio = $this->db->insert_id();
		$this->db->reset_query();
		//PREPARAN LOS DATOS PARA INSERTAR EN LA TABLA STATUSREPORTE
		$datosEstatusReporte = array('idUsuario' => $id, 
									 'idStatus' => '1',
									 'folio', $ultimoFolio);
		$this->db->insert('statusreporte',$datosEstatusReporte);

		//SE ENVIA LA RESPUESTA
		$respuesta = array('error' => FALSE,
							'mensaje' => 'Se ha realizado el reporte correctamente',
							'folio' => $ultimoFolio);

		$this->response($respuesta);
	}
	public function nuevor_post(){
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
		$query = $this->db->get('usuario');
		$existe = $query->row();
		if (!$existe) {
			$respuesta = array('error' => TRUE,
								'mensaje' => 'Usuario y token incorrectos');
			$this->response($respuesta,REST_Controller::HTTP_UNAUTHORIZED);
			return;
		}
		//AQUI YA ESTA VALIDADO EL USUARIO
		$this->db->reset_query();
		//OBTENER ID Y NOMBRE A PARTIR DEL CORREO DEL USUARIO DADO
		$correo = $this->post('correo');
		$this->db->select('id,nombre,a_paterno,a_materno');
		$this->db->where('correo',$correo);
		$query = $this->db->get('usuario')->result_array();

		foreach ($query as $key) {
				   $id = $key['id'];
				   $nombre = $key['nombre'];
		           $aPaterno = $key['a_paterno'];
		           $aMaterno = $key['a_materno'];
		}

		$this->db->reset_query();

		//SE PREPARAN LOS DATOS A INSERTAR
		$ubicacionServicio = "".$this->post('modulo')." ".$this->post('piso')." ".$this->post('aula')."";
		$datos = array('recibe' => $this->post('recibe'),
					   'nombre' => $nombre,
					   'a_paterno' => $aPaterno,
					   'a_materno' => $aMaterno,
					   'telefono' => $this->post('telefono'),
					   'area_solicitante' => $this->post('area'),
					   'ubicacion_servicio' => $ubicacionServicio,
					   'descripcion_servicio' => 'Descripcion Problema',
					   'descripcion_problema' => $this->post('option'),
					   'idUsuario' => $id
					);
		$this->db->insert('reportemanten',$datos);
		//OBTENER EL ULTIMO FOLIO REGISTRADO
		$ultimoFolio = $this->db->insert_id();
		$this->db->reset_query();
		//PREPARAN LOS DATOS PARA INSERTAR EN LA TABLA STATUSREPORTE
		$datosEstatusReporte = array('idUsuario' => $id, 
									 'idStatus' => '1',
									 'folio' => $ultimoFolio);
		$this->db->insert('statusreporte',$datosEstatusReporte);

		//SE ENVIA LA RESPUESTA
		$respuesta = array('error' => FALSE,
							'mensaje' => 'Se ha realizado el reporte correctamente',
							'folio' => $ultimoFolio);

		$this->response($respuesta);
	}
}