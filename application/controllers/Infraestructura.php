<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(APPPATH.'/libraries/REST_Controller.php');
use Restserver\Libraries\REST_Controller;

class Infraestructura extends REST_Controller {
	public function __construct()
	{
		header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
		header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding");
		header("Access-Control-Allow-Origins: *");
		parent::__construct();
		$this->load->database();
	}
	public function index(){}
	public function imodulos_get() {
		$this->db->select('id, module_name');
		$query = $this->db->get('moduleList');
		$this->response($query->result());
    }
    public function ipisos_get($module_id) {
		$this->db->select('floor_id');
		$this->db->distinct();
		$this->db->where('module_id', $module_id);
		$query = $this->db->get('aulaList');
		$this->response($query->result());
    }
    public function iaulas_get($module_id,$floor_id) {
		$this->db->select('aula_name');
		$this->db->where('module_id', $module_id);
		$this->db->where('floor_id', $floor_id);
		$query = $this->db->get('aulaList');
		$this->response($query->result());
	}
}