<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once(APPPATH.'/libraries/REST_Controller.php');
use Restserver\libraries\REST_Controller;

class Personal extends REST_Controller
{
	
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
	public function personal_get($correo){
		$query = $this->db->query("SELECT nombre,a_paterno,a_materno FROM personal WHERE correo='".$correo."'");
			$this->response($query->result());
	}
}