<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(APPPATH.'/libraries/REST_Controller.php');
use Restserver\Libraries\REST_Controller;

class Material extends REST_Controller {
	public function __construct()
	{
		header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
		header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding");
		header("Access-Control-Allow-Origins: *");

		parent::__construct();
		$this->load->database();
    }

    public function nuevomaterial_post(){
			$folio = $this->post('folio');
			$reporteProyecto = $this->post('reporteProyecto');
			$solicita = $this->post('solicita');
			$material = $this->post('matrial');
			$catalogo = $this->post('catalogo');
			$unidad = $this->post('unidad');
			$cantidadSolicitada = $this->post('cantidadSolicitada');
			$cantidadRecibida = $this->post('cantidadRecibida');
			$pendiente = $this->post('pendiente');
			$fecha = $this->post('fecha');
			$diasSurtido = $this->post('diasSurtido');
			$estatus = $this->post('estatus');
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
		//PREPARAN LOS DATOS A INSERTAR
		$datos = array('folio' => $folio,
										'reporte_proyecto' => $reporteProyecto,
										'solicita' => $solicita,
										'material' => $material,
										'catalogo' => $catalogo,
										'unidad' => $unidad,
										'cantidad_solicitada' => $cantidadSolicitada,
										'cantidad_recibida' => $cantidadRecibida,
										'pendiente' => $pendiente,
										'fecha' => $fecha,
										'dias_surtido' => $diasSurtido,
										'estatus' => $estatus,
										'observaciones' => $observaciones
									 );
		$this->db->insert('materialList',$datos);
		//SE ENVIA LA RESPUESTA
		$respuesta = array('error' => FALSE,
							'mensaje' => 'Se ha realizado el reporte correctamente');

		$this->response($respuesta);  
	}
	public function material_get(){
		$query = $this->db->query('SELECT * FROM materialList');
    $this->response($query->result());
	}
	public function materialsel_get($indice){
		$query = $this->db->query('SELECT * FROM materialList WHERE indice = '.$indice);
    $this->response($query->result());
	}
	public function materialselfolio_get($folio){
		$query = $this->db->query('SELECT * FROM materialList WHERE indice = '.$folio);
    $this->response($query->result());
	}
	public function materialselmod_post(){
		$indice = $this->post('indice');
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
		//PREPARAN LOS DATOS A INSERTAR
			$reporteProyecto = $this->post('reporteProyecto');
			$solicita = $this->post('solicita');
			$material = $this->post('matrial');
			$catalogo = $this->post('catalogo');
			$unidad = $this->post('unidad');
			$cantidadSolicitada = $this->post('cantidadSolicitada');
			$cantidadRecibida = $this->post('cantidadRecibida');
			$pendiente = $this->post('pendiente');
			$fecha = $this->post('fecha');
			$diasSurtido = $this->post('diasSurtido');
			$estatus = $this->post('estatus');
			$observaciones = $this->post('observaciones');

			$condiciones = array('reporte_proyecto' => $reporteProyecto,
										'solicita' => $solicita,
										'material' => $material,
										'catalogo' => $catalogo,
										'unidad' => $unidad,
										'cantidad_solicitada' => $cantidadSolicitada,
										'cantidad_recibida' => $cantidadRecibida,
										'pendiente' => $pendiente,
										'fecha' => $fecha,
										'dias_surtido' => $diasSurtido,
										'estatus' => $estatus,
										'observaciones' => $observaciones
									 );
			$this->db->where('indice',$indice);
			$resultado = $this->db->update('materialList',$condiciones);
			$respuesta = array('error' => FALSE,
													'mensaje' => 'Reporte Actualizado Correctamente');
			$this->response($respuesta);
	}
}