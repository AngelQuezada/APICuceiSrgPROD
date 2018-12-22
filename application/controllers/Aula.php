<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(APPPATH.'/libraries/REST_Controller.php');
use Restserver\Libraries\REST_Controller;

class Aula extends REST_Controller {

	public function index(){
		echo "Hola Mundo";
	}

	public function aulas_get($module_id,$floor_id){
		$this->load->database();
		$query = $this->db->query("SELECT aula_name FROM aulalist WHERE module_id='".$module_id."' AND floor_id='".$floor_id."'");
		
			$this->response($query->result());
	}
}