<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(APPPATH.'/libraries/REST_Controller.php');
use Restserver\Libraries\REST_Controller;

class Modulo extends REST_Controller {

	public function index(){
		echo "Hola Mundo";
	}

	public function modulos_get(){
		$this->load->database();
		$query = $this->db->query("SELECT id,module_name FROM modulelist");
			$this->response($query->result());
	}
}