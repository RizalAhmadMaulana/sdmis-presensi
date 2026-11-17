<?php

defined('BASEPATH') OR exit('No direct script access allowed');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Rest_get extends CI_Controller {

    function __construct() {
        parent::__construct();
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*';
        header('Access-Control-Allow-Origin: ' . $origin);
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Origin, Content-Type, Authorization, Accept, X-Requested-With, x-xsrf-token, X-CSRF-Token, token");
        header("Content-Type: application/json; charset=utf-8");

        if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
        $this->load->model('user/api/get_model', 'get');

        require APPPATH.'libraries/phpmailer/src/Exception.php';
        require APPPATH.'libraries/phpmailer/src/PHPMailer.php';
        require APPPATH.'libraries/phpmailer/src/SMTP.php';
    }

    function index() {
        $config = array(
            'name'      => 'Carvellonic',
            'website'   => 'https://carvellonic.com'
        );
        echo json_encode($config);
    }

    function company_data() {
        $postjson = json_decode(file_get_contents('php://input'), true);
        $data = $this->get->company_data($postjson);
        echo $data;
    }

    function karyawan_data() {
        $postjson = json_decode(file_get_contents('php://input'), true);
        $data = $this->get->karyawan_data($postjson);
        echo $data;
    }

    function attendance_data() {
        $postjson = json_decode(file_get_contents('php://input'), true);
        $data = $this->get->attendance_data($postjson);
        echo $data;
    }

    function location_office() {
        $postjson = json_decode(file_get_contents('php://input'), true);
        $data = $this->get->location_office($postjson);
        echo $data;
    }

    function req_data() {
        $postjson = json_decode(file_get_contents('php://input'), true);
        $data = $this->get->req_data($postjson);
        echo $data;
    } 

    function req_data_id() {
        $postjson = json_decode(file_get_contents('php://input'), true);
        $data = $this->get->req_data_id($postjson);
        echo $data;
    } 

    function lembur_data() {
        $postjson = json_decode(file_get_contents('php://input'), true);
        $data = $this->get->lembur_data($postjson);
        echo $data;
    }   

    //Masukan function selanjutnya disini
}
?>