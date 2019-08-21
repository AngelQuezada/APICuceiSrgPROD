<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(APPPATH.'/libraries/REST_Controller.php');
use Restserver\Libraries\REST_Controller;
// Use the REST API Client to make requests to the Twilio REST API
use Twilio\Rest\Client;

class Sms extends REST_Controller {

	public function __construct()
	{
		header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
		header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding");
		header("Access-Control-Allow-Origins: *");
		parent::__construct();
        $this->load->database();
	}

	public function index(){}
	public function enviarsms_get() {
		$this->db->select('telefono');
		$query = $this->db->get('personal')->result();
		foreach($query as $key => $value) {
			$data = ['phone' => $value->telefono, 'text' => 'Se ha registrado un nuevo reporte de mantenimiento.'];
			$sid = 'ACbebe4fa7bc87018f990225d66fc4687e';
			$token = 'e504241e490c30d9591c3a698cf6abc6';
			$client = new Client($sid, $token);
			$client->messages->create(
			$data['phone'],
			array(
				// A Twilio phone number you purchased at twilio.com/console
				'from' => '+17014015202',
				// the body of the text message you'd like to send
				'body' => $data['text']
			)
			);
		  }
		$arrayName = array('error' => FALSE,
							'mensaje' => 'sms enviados correctamente');
		return $this->response($arrayName);
    }
    public function registrarnumero_post() {
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
		//SE PREPARAN LOS DATOS A INSERTAR
        $condiciones = array('telefono' => $this->post('telefono'));
			$this->db->where('id',$idUsuario);
			$resultado = $this->db->update('personal',$condiciones);
		//SE ENVIA LA RESPUESTA
		$respuesta = array('error' => FALSE,
							'mensaje' => 'Se ha registrado el número de telefono correctamente. Contacte al administrador del Sistema para que actualice su número en el sistema de SMS.');

		$this->response($respuesta);
	}
	public function registrarnumerouser_post() {
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
		//SE PREPARAN LOS DATOS A INSERTAR
        $condiciones = array('telefono' => $this->post('telefono'));
		$this->db->where('id',$idUsuario);
		$resultado = $this->db->update('usuario',$condiciones);
		//SE ENVIA LA RESPUESTA
		$respuesta = array('error' => FALSE,
							'mensaje' => 'Se ha registrado el número de telefono correctamente.');
		$this->response($respuesta);
	}
}