<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(APPPATH.'/libraries/REST_Controller.php');
use Restserver\Libraries\REST_Controller;

class Aula extends REST_Controller {
	public function __construct()
	{
		header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
		header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding");
		header("Access-Control-Allow-Origins: *");

		parent::__construct();
		$this->load->database();
	}

	public function index(){
	}

	public function aulas_get($module_id,$floor_id){
		$query = $this->db->query("SELECT aula_name FROM aulaList WHERE module_id='".$module_id."' AND floor_id='".$floor_id."'");
		
			$this->response($query->result());
	}
}