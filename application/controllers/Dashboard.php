<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Dashboard extends CI_Controller {
    public function __construct() {
        parent::__construct();
        is_logged_in();
        $this->load->library('form_validation');
        $this->load->model('user/menu_model', 'menu');
        $this->load->model('master_model', 'master');
    }

    public function index() {
        $data['htmlpagejs'] = 'dashboard';
        $data['nmenu'] = 'Dashboard';
        $data['title'] = 'Dashboard';
        $data['auth'] = authUser();

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidemenu', $data);
        $this->load->view('templates/sidenav', $data);
        $this->load->view('module/dashboard/index', $data);
        $this->load->view('templates/footer', $data);
        $this->load->view('templates/fscript-html-end', $data);
    }

}
