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
		$correo = $this->post('correo');
		//OBTENER ID Y NOMBRE A PARTIR DEL CORREO DEL USUARIO DADO
		//VALIDAR A SU VEZ EL CORREO SEA VALIDO
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

		//SE PREPARAN LOS DATOS A INSERTAR
		$ubicacionServicio = "MODULO: ".$this->post('modulo')." PISO: ".$this->post('piso')." AULA: ".$this->post('aula')."";
		$datos = array('recibe' => $this->post('recibe'),
					   'nombre' => $nombre,
					   'a_paterno' => $aPaterno,
					   'a_materno' => $aMaterno,
					   'telefono' => $this->post('telefono'),
					   'area_solicitante' => $this->post('area'),
					   'ubicacion_servicio' => $ubicacionServicio,
					   'anotacion_extra' => $this->post('anotacionExtra'),
					   'descripcion_servicio' => $this->post('option'),
					   'descripcion_problema' => $this->post('descripcionProblema'),
					   'idUsuario' => $id
					);
		$this->db->insert('reporteManten',$datos);
		//OBTENER EL ULTIMO FOLIO REGISTRADO
		$ultimoFolio = $this->db->insert_id();
		$this->db->reset_query();

		//PREPARAN LOS DATOS PARA INSERTAR EN LA TABLA STATUSREPORTE
		$idStatus = "1";
		$datosEstatusReporte = array('id' => null,
									 'idUsuario' => $id,
									 'idStatus' => $idStatus,
									 'folio' => $ultimoFolio);
		$this->db->insert('statusReporte',$datosEstatusReporte);

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
		$this->db->insert('reporteManten',$datos);
		//OBTENER EL ULTIMO FOLIO REGISTRADO
		$ultimoFolio = $this->db->insert_id();
		$this->response($ultimoFolio);
		$this->db->reset_query();
		//PREPARAN LOS DATOS PARA INSERTAR EN LA TABLA STATUSREPORTE
		$datosEstatusReporte = array('idUsuario' => $id,
									 'idStatus' => '1',
									 'folio' => $ultimoFolio);
		$this->db->insert('statusReporte',$datosEstatusReporte);

		//SE ENVIA LA RESPUESTA
		$respuesta = array('error' => FALSE,
							'mensaje' => 'Se ha realizado el reporte correctamente',
							'folio' => $ultimoFolio);

		$this->response($respuesta);
	}
	public function nuevos_get(){
		$query = $this->db->query('SELECT * FROM statusReporte WHERE idStatus = 1');
		$cantidad;
		if(!$query){
			$cantidad = 0;
			$this->response($cantidad);
			return;
		}
		$this->response($query->num_rows());
	}
	public function atender_get(){
		$query = $this->db->query('SELECT * FROM statusReporte WHERE idStatus = 2');
		$cantidad;
		if(!$query){
			$cantidad = 0;
			$this->response($cantidad);
			return;
		}
		$this->response($query->num_rows());
	}
	public function finalizado_get(){
		$query = $this->db->query('SELECT * FROM statusReporte WHERE idStatus = 3');
		$cantidad;
		if(!$query){
			$cantidad = 0;
			$this->response($cantidad);
			return;
		}
		$cant = $query->num_rows();
		$this->response($cant);
	}
	public function cancelados_get(){
		$query = $this->db->query('SELECT * FROM statusReporte WHERE idStatus = 4');
		$cantidad;
		if(!$query){
			$cantidad = 0;
			$this->response($cantidad);
			return;
		}
		$this->response($query->num_rows());
	}
	public function reportenpp_get($aPaterno,$aMaterno,$nombre,$folio){
		$this->db->select('*');
		$this->db->where('a_paterno',$aPaterno)->or_where('a_materno',$aMaterno)->or_where('nombre',$nombre)->or_where('folio',$folio);
		$query = $this->db->get('reporteManten')->result();
		if(empty($query)){
			$respuesta = array('error' => TRUE,
								'mensaje' => 'No hay resultados');
			$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
			return;
		}
		$this->response($query);
	}
	public function reporteindpp_get($folio){
		$this->db->select('*');
		$this->db->where('folio',$folio);
		$query = $this->db->get('reporteManten')->result();
		if(empty($query)){
			$respuesta = array('error' => TRUE,
								'mensaje' => 'No hay resultados');
			$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
			return;
		}
		$this->response($query);
	}
	public function modreporte_post(){
		$token = $this->post('token');
		$folio = $this->post('folio');
		$fechaRecepcion = $this->post('fecha-recepcion');
		$fechaAsignacion = $this->post('fecha-asignacion');
		$fechaReparacion = $this->post('fecha-reparacion');
		if(empty($fechaRecepcion)){
			$fechaRecepcion = null;
		}
		if(empty($fechaAsignacion)){
			$fechaAsignacion = null;
		}
		if(empty($fechaReparacion)){
			$fechaReparacion = null;
		}
		
		
		$this->db->select('idStatus');
		$this->db->where('folio',$folio);
		$query = $this->db->get('statusReporte')->result();
		$id = $query;
		 if($id == '4'){
			$respuesta = array('error' => TRUE,
			'mensaje' => 'Ya fue cancelado, no se puede modificar');
			$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
			return;
		 }

		if (empty($token)) {
			$respuesta = array('error' => TRUE,
								'mensaje' => 'No Autorizado.');
			$this->response($respuesta,REST_Controller::HTTP_UNAUTHORIZED);
			return;
		}
		if (!empty($fechaRecepcion) && empty($fechaAsignacion) && !empty($fechaReparacion)) {
			$respuesta = array('error' => TRUE,
								'mensaje' => 'Debe haber fecha de Asignación antes.');
			$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
			return;
		}
		if (empty($fechaRecepcion) && empty($fechaAsignacion) && empty($fechaReparacion)) {
			$respuesta = array('error' => FALSE,
								'mensaje' => 'No se realizó ningun cambio.' );
			$this->response($respuesta);
			return;
		}
		if (empty($fechaRecepcion) && !empty($fechaAsignacion)) {
			$respuesta = array('error' => TRUE,
								'mensaje' => 'Debe existir fecha de recepción antes de asignar una.' );
			$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
			return;
		}
		if (empty($fechaRecepcion) && !empty($fechaReparacion)) {
			$respuesta = array('error' => TRUE,
								'mensaje' => 'Debe haber fecha de recepcion antes de asignar.' );
			$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
			return;
		}
		if (empty($fechaAsignacion) && !empty($fechaReparacion)) {
			$respuesta = array('error' => TRUE,
								'mensaje' => 'Debe haber fecha de asignacion antes de asignar.' );
			$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
			return;
		}
		//ESTATUS SOLICITUD 1
		if(!empty($fechaRecepcion) && empty($fechaAsignacion) && empty($fechaReparacion)){
			$condiciones = array('fecha_recepcion' => $fechaRecepcion,
			'fecha_asignacion' => $fechaAsignacion,
			'fecha_reparacion' => $fechaReparacion);
			$this->db->where('folio',$folio);
			$resultado = $this->db->update('reporteManten',$condiciones);
			$this->db->reset_query();
			//RESETEO LA OBSERVACION		
			$condiciones = array('observacion_status' => null);
			$this->db->where('folio',$folio);
			$resultado = $this->db->update('statusReporte',$condiciones);
			$respuesta = array('error' => FALSE,
			'mensaje' => 'Reporte Actualizado Correctamente');
			$this->response($respuesta);
			return;
		}
		//ESTATUS ASIGNADO 2
		if(!empty($fechaRecepcion) && !empty($fechaAsignacion) && empty($fechaReparacion)){
			$condiciones = array('fecha_recepcion' => $fechaRecepcion,
			'fecha_asignacion' => $fechaAsignacion,
			'fecha_reparacion' => $fechaReparacion);
			$this->db->where('folio',$folio);
			$resultado = $this->db->update('reporteManten',$condiciones);
			$this->db->reset_query();
			$condiciones = array('idStatus' => '2');
			$this->db->where('folio',$folio);
			$resultado = $this->db->update('statusReporte',$condiciones);
			$this->db->reset_query();
			//RESETEO LA OBSERVACION		
			$condiciones = array('observacion_status' => null);
			$this->db->where('folio',$folio);
			$resultado = $this->db->update('statusReporte',$condiciones);
			$respuesta = array('error' => FALSE,
			'mensaje' => 'Reporte Actualizado Correctamente');
			$this->response($respuesta);
			return;
		}
		//ESTATUS 3 FINALIZADO
		if(!empty($fechaRecepcion) && !empty($fechaAsignacion) && !empty($fechaReparacion)){
			$condiciones = array('fecha_recepcion' => $fechaRecepcion,
			'fecha_asignacion' => $fechaAsignacion,
			'fecha_reparacion' => $fechaReparacion);
			$this->db->where('folio',$folio);
			$resultado = $this->db->update('reporteManten',$condiciones);
			$this->db->reset_query();
			$condiciones = array('idStatus' => '3');
			$this->db->where('folio',$folio);
			$resultado = $this->db->update('statusReporte',$condiciones);
			$this->db->reset_query();
			//RESETEO LA OBSERVACION		
			$condiciones = array('observacion_status' => null);
			$this->db->where('folio',$folio);
			$resultado = $this->db->update('statusReporte',$condiciones);
			$respuesta = array('error' => FALSE,
			'mensaje' => 'Reporte Actualizado Correctamente');
			$this->response($respuesta);
			return;
		}
	}

	public function cancelar_post(){
		$token = $this->post('token');
		$folio = $this->post('folio');
		//$this->db->select('idStatus');
		//$this->db->where('folio',$folio);
		$query = $this->db->query("SELECT idStatus FROM statusReporte WHERE folio = {$folio}");
		$row = $query->row();
		$result = $row->idStatus;
		 if($result == '4'){
			$respuesta = array('error' => TRUE,
			'mensaje' => 'Ya fue cancelado');
			$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
			return;
		 }
		$condiciones = array('idStatus' => '4');
		$this->db->where('folio',$folio);
		$resultado = $this->db->update('statusReporte',$condiciones);
		$respuesta = array('error' => FALSE,
						   'mensaje' => 'Se ha Cancelado correctamente el reporte');
		$this->response($respuesta);
	}

	public function reportenuevos_get($token){
		if (empty($token)) {
			$respuesta = array('error' => TRUE,
								'mensaje' => 'No Autorizado.');
			$this->response($respuesta,REST_Controller::HTTP_UNAUTHORIZED);
			return;
		}
		$query = $this->db->query('SELECT * FROM statusReporte WHERE idStatus = 1');
		$this->response($query->result());
	}
	public function reporteasignados_get($token){
		if(empty($token)){
			$respuesta = array('error' => TRUE,
								'mensaje' => 'No Autorizado');
			$this->response($respuesta,REST_CONTROLLER::HTTP_UNAUTHORIZED);
			return;			
		}
		$query = $this->db->query('SELECT * FROM statusReporte WHERE idStatus = 2');
		$this->response($query->result());
	}
	public function reportefinalizados_get($token){
		if(empty($token)){
			$respuesta = array('error' => TRUE,
								'mensaje' => 'No Autorizado');
			$this->response($respuesta,REST_CONTROLLER::HTTP_UNAUTHORIZED);
			return;			
		}
		$query = $this->db->query('SELECT * FROM statusReporte WHERE idStatus = 3');
		$this->response($query->result());
	}
	public function reportecancelados_get($token){
		if(empty($token)){
			$respuesta = array('error' => TRUE,
								'mensaje' => 'No Autorizado');
			$this->response($respuesta,REST_CONTROLLER::HTTP_UNAUTHORIZED);
			return;			
		}
		$query = $this->db->query('SELECT * FROM statusReporte WHERE idStatus = 4');
		$this->response($query->result());
	}
	public function genobservacion_post(){
		$token = $this->post('token');
		$folio = $this->post('folio');
		$idUsuario = $this->post('idUsuario');
		$observacion = $this->post('observacion');
		if($token === "" || $idUsuario === ""){
			$respuesta = array('error' => TRUE,
								'mensaje' => 'No Autorizado');
			$this->response($respuesta,REST_Controller::HTTP_UNAUTHORIZED);
			return;
		}
		$condiciones = array('observacion_status' => $observacion);
		$this->db->where('folio',$folio);
		$resultado = $this->db->update('statusReporte',$condiciones);
		$respuesta = array('error' => FALSE,
						   'mensaje' => 'Se ha agregado correctamente la observación al reporte');
		$this->response($respuesta);
	}
	public function personal_get(){
		$query = $this->db->query('SELECT id,nombre,a_paterno,a_materno FROM personal WHERE status = 3');
		$this->response($query->result());
	}
	public function asignarencargado_post(){
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
			$this->response($respuesta,REST_Controller::HTTP_UNAUTHORIZED);
			return;
		}
		$this->db->reset_query();
		//VALIDA QUE NO SEA EL MISMO ENCARGADO
		$this->db->select('idPersonal');
		$this->db->where('folioReporte',$folio);
		$query = $this->db->get('encargado')->result();
		$idPersonalq = $query;

		if($idPersonalq == $idPersonal){
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
		$folioq  = $query;
		if($folioq == 4){
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
						   'mensaje' => 'Se ha asignado correctamente el encargado');
		$this->response($respuesta);
	}
	public function getreporteencargado_get(){
		$query = $this->db->query('SELECT personal.nombre,personal.a_paterno,personal.a_materno, encargado.folioReporte, encargado.id FROM personal INNER JOIN encargado ON personal.id=encargado.idPersonal');
		$this->response($query->result());
	}
	public function grafico_get(){
		$solicitud;
		$asignados;
		$finalizados;
		$cancelados;
		$this->db->where('idStatus','1');
		$this->db->from('statusReporte');
		$query = $this->db->count_all_results();
		$solicitud = $query;
		$this->db->reset_query();
		$this->db->where('idStatus','2');
		$this->db->from('statusReporte');
		$query = $this->db->count_all_results();
		$asignados = $query;
		$this->db->reset_query();
		$this->db->where('idStatus','3');
		$this->db->from('statusReporte');
		$query = $this->db->count_all_results();
		$finalizados = $query;
		$this->db->reset_query();
		$this->db->where('idStatus','4');
		$this->db->from('statusReporte');
		$query = $this->db->count_all_results();
		$cancelados = $query;
		$this->db->reset_query();
		$respuesta = array(array('Reporte'  => 'Solicitud',
								 'Cantidad' => $solicitud),
								array('Reporte' => 'Asignados',
									  'Cantidad' => $asignados),
								array('Reporte' => 'Finalizados',
									  'Cantidad' => $finalizados),
								array('Reporte' => 'Cancelados',
									  'Cantidad' => $cancelados));
		$this->response($respuesta);
	}
	public function getemail_get($folio){
		//OBTIENE EL ID DEL USUARIO A PARTIR DEL FOLIO REGISTRADO
		$this->db->select('idUsuario');
		$this->db->where('folio',$folio);
		$query = $this->db->get('reporteManten')->result();
		$id = $query;
		$this->db->reset_query();
		//OBTENER EL CORREO A PARTIR DEL ID
		$this->db->select('correo');
		$this->db->where('id',$id);
		$query = $this->db->get('usuario')->result();
		
		$this->response($query);
	}
}
