<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(APPPATH.'/libraries/REST_Controller.php');
use Restserver\Libraries\REST_Controller;

class Sreporte extends REST_Controller {
	public function __construct()
	{
		header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
		header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding");
		header("Access-Control-Allow-Origins: *");

		parent::__construct();
		$this->load->database();
	}

    public function index(){}
    
    public function nuevors1_post(){
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
        $this->db->reset_query();
        //OBTENER ID Y NOMBRE A PARTIR DEL CORREO DEL USUARIO DADO
        //VALIDAR A SU VEZ EL CORREO SEA VALIDO
        $correo = $this->post('correo');
		$this->db->select('id,nombre,a_paterno,a_materno');
		$this->db->where('correo',$correo);
		$query = $this->db->get('usuario')->result_array();
		if (!$query) {
			$respuesta = array('error' => TRUE,
								'mensaje' => 'El correo dado no esta registrado');
			$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
			return;
		}

		foreach ($query as $key) {
                   $id = $key['id'];
                   $nombre = $key['nombre'];
		           $aPaterno = $key['a_paterno'];
		           $aMaterno = $key['a_materno'];
        }
        $this->db->reset_query();
        $nombreCompleto = $nombre." ".$aPaterno." ".$aMaterno;
        //PREPARAN LOS DATOS A INSERTAR
        $datos = array('tipo_servicio' => $this->post('tipoServicio'),
                        'afectado' => $nombreCompleto,
                        'edad' => $this->post('edad'),
                        'carrera' => $this->post('carrera'),
                        'codigo' => $this->post('codigo'),
                        'telefono' => $this->post('telefono'),
                        'fecha' => $this->post('fecha'),
                        'hora' => $this->post('hora'),
                        'lugar' => $this->post('lugar'),
                        'hechos' => $this->post('hechos'),
                        'idUsuario' => $id);

        $this->db->insert('reporte1seguridad',$datos);
        //OBTENER EL ULTIMO FOLIO REGISTRADO
        $ultimoFolio = $this->db->insert_id();
        $this->db->reset_query();

        $objetosReporte = array('modelo' => $this->post('modelo'),
                           'marca' => $this->post('marca'),
                           'tipo' => $this->post('tipo'),
                           'year' => $this->post('año'),
                           'color' => $this->post('color'),
                           'rodado' => $this->post('rodado'),
                           'folioReporte' => $ultimoFolio);

        $this->db->insert('objetosreporte1seguridad',$objetosReporte);
        //SE ENVIA LA RESPUESTA
		$respuesta = array('error' => FALSE,
							'mensaje' => 'Se ha realizado el reporte correctamente',
							'folio' => $ultimoFolio);

		$this->response($respuesta);  
    }

}