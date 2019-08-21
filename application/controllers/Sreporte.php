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
    public function nuevors1_post() {
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
		$query = $this->db->get('usuario')->row();
		if (!$query) {
			$respuesta = array('error' => TRUE,
								'mensaje' => 'El correo dado no esta registrado');
			$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
			return;
		}
        $id = $query->id;
        $nombre = $query->nombre;
        $aPaterno = $query->a_paterno;
        $aMaterno = $query->a_materno;
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
                        'idUsuario' => $id,
                        'correo' => $this->post('correo'));
        $this->db->insert('reporte1Seguridad',$datos);
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
        $this->db->insert('objetosReporte1Seguridad',$objetosReporte);
        //SE ENVIA LA RESPUESTA
		$respuesta = array('error' => FALSE,
							'mensaje' => 'Se ha realizado el reporte correctamente',
							'folio' => $ultimoFolio);
		$this->response($respuesta);
    }
    public function agregarobjeto_post() {
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
        //VALIDAR SI FALTAN CAMPOS
        if(empty($this->post('modelo')) || empty($this->post('marca')) || empty($this->post('tipo')) ||
             empty($this->post('fecha')) || empty($this->post('color')) || empty($this->post('rodado')) ||
             empty($this->post('folio')) ){
                $respuesta = array('error' => TRUE,
                'mensaje' => 'Verifique que todos los campos estén completos.');
                $this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
                return;
             }
        //SE PREPARAN LOS DATOS A INSERTAR
        $objetosReporte = array('modelo' => $this->post('modelo'),
                           'marca' => $this->post('marca'),
                           'tipo' => $this->post('tipo'),
                           'year' => $this->post('fecha'),
                           'color' => $this->post('color'),
                           'rodado' => $this->post('rodado'),
                           'folioReporte' => $this->post('folio'));
        $this->db->insert('objetosReporte1Seguridad',$objetosReporte);
        //SE ENVIA LA RESPUESTA
		$respuesta = array('error' => FALSE,
							'mensaje' => 'Se ha registrado el objeto correctamente');
		$this->response($respuesta);  
    }
    public function getsreporte_get() {
        $this->db->select('*');
        $query = $this->db->get('reporte1Seguridad');
        $this->response($query->result());
    }
    public function getsreportepa_get($folio) {
        $this->db->select('*');
        $this->db->where('id', $folio);
        $query = $this->db->get('reporte1Seguridad');
        $this->response($query->result());
    }
    public function getobjsreporte_get($folio) {
        $this->db->select('*');
        $this->db->where('folioReporte', $folio);
        $query = $this->db->get('objetosReporte1Seguridad');
        $this->response($query->result());
    }
    //SEGUNDO REPORTE DE SEGURIDAD
    public function getinstituciones_get() {
        $this->db->select('*');
        $query = $this->db->get('instituciones');
        $this->response($query->result());
    }
    public function nuevors2_post() {
        $token = $this->post('token');
        $institucion = $this->post('institucion');
        $edad = $this->post('edad');
        $codigo = $this->post('codigo');
        $carrera = $this->post('carrera');
        $email = $this->post('email');
        $telefono = $this->post('telefono');
        $fecha = $this->post('fecha');
        $hora = $this->post('hora');
        $lugar = $this->post('lugar');
        $suceso = $this->post('suceso');
        $robado = $this->post('robado');
        $estatura = $this->post('estatura');
        $apariencia = $this->post('apariencia');
        $tez = $this->post('tez');
        $cabello = $this->post('cabello');
        $ojos = $this->post('ojos');
        $cara = $this->post('cara');
        $boca = $this->post('boca');
        $ropa = $this->post('ropa');
        $uso = $this->post('uso');
        $edadAgresor = $this->post('edadAgresor');
        $cicatrices = $this->post('cicatrices');
        $tatuajes = $this->post('tatuajes');
        $piercing = $this->post('piercing');
        $señaParticular = $this->post('señaParticular');
        $metodoHuida = $this->post('metodoHuida');
        $observaciones = $this->post('observaciones');

        //SI NO SE ENVIA TOKEN NI EL ID DEL USUARIO
		$token = $this->post('token');
		$idUsuario = $this->post('idUsuario');
		if($token === "" || $idUsuario === ""){
			$respuesta = array('error' => TRUE,
								'mensaje' => 'No Autorizado');
			$this->response($respuesta,REST_Controller::HTTP_UNAUTHORIZED);
			return;
		}
        //OBTENER ID Y NOMBRE A PARTIR DEL CORREO DEL USUARIO DADO
		//VALIDAR A SU VEZ EL CORREO SEA VALIDO
		$this->db->select('id,nombre,a_paterno,a_materno');
		$this->db->where('correo',$email);
		$query = $this->db->get('usuario')->row();
		if (!$query) {
			$respuesta = array('error' => TRUE,
								'mensaje' => 'El correo dado no esta registrado');
			$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
			return;
		}
		$idUsuario =  $query->id;
		$nombre = $query->nombre;
		$aPaterno = $query->a_paterno;
		$aMaterno = $query->a_materno;

        $this->db->reset_query();
        //OBTENER ID DE LA INSTITUCION"
        $this->db->select('id');
        $this->db->where('institucion', $institucion);
        $query = $this->db->get('instituciones')->row();
        $idInstitucion = $query->id;
        //SE PREPARAN LOS DATOS A INSERTAR
        $this->db->reset_query();
        $nombreCompleto = $nombre." ".$aPaterno." ".$aMaterno;
        $datos = array( 'id' => NULL,
                        'nombre' => $nombreCompleto,
                        'edad' => $edad,
                        'codigo' => $codigo,
                        'carrera' => $carrera,
                        'correo' => $email,
                        'telefono' => $telefono,
                        'fecha_incidente' => $fecha,
                        'hora_incidente' => $hora,
                        'lugar' => $lugar,
                        'descripcion_suceso' => $suceso,
                        'tipo_robo' => $robado,
                        'estatura' => $estatura,
                        'apariencia' => $apariencia,
                        'tez' => $tez,
                        'cabello' => $cabello,
                        'ojos' => $ojos,
                        'cara' => $cara,
                        'boca' => $boca,
                        'tipo_ropa' => $ropa,
                        'objeto_rostro' => $uso,
                        'edad_aprox' => $edad,
                        'cicatriz' => $cicatrices,
                        'tatuaje' => $tatuajes,
                        'piercing' => $piercing,
                        'otro' => $señaParticular,
                        'metodo_huida' => $metodoHuida,
                        'observaciones' => $observaciones,
                        'idUsuario' => $idUsuario,
                        'idInstitucion' => $idInstitucion);
        $this->db->insert('reporte2Seguridad',$datos);
        //SE ENVIA LA RESPUESTA
		$respuesta = array('error' => FALSE,
                            'mensaje' => 'Se ha realizado el reporte correctamente');
        $this->response($respuesta);
    }
    public function getsreporte2_get() {
        $this->db->select('*');
        $query = $this->db->get('reporte2Seguridad');
        $this->response($query->result());
    }
    public function getsreporte2pa_get($folio) {
        $this->db->select('*');
        $this->db->where('id', $folio);
        $query = $this->db->get('reporte2Seguridad');
        $this->response($query->result());
    }
    public function getnuevos1_get() {
        $this->db->select('*');
        $query = $this->db->get('reporte1Seguridad');
		$cantidad;
		if(!$query){
			$cantidad = 0;
			$this->response($cantidad);
			return;
		}
		$this->response($query->num_rows());
    }
    public function getnuevos2_get() {
        $this->db->select('*');
        $query = $this->db->get('reporte2Seguridad');
		$cantidad;
		if(!$query){
			$cantidad = 0;
			$this->response($cantidad);
			return;
		}
		$this->response($query->num_rows());
    }
}
