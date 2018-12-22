<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(APPPATH.'/libraries/REST_Controller.php');
use Restserver\Libraries\REST_Controller;

class Piso extends REST_Controller {

	public function index(){
		echo "Hola Mundo";
	}

	public function pisos_get($module_id){
		$this->load->database();
		$query = $this->db->query("SELECT floor_id FROM aulalist WHERE module_id='".$module_id."'");
			$this->response($query->result());
	}
}