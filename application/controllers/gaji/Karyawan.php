<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Controller Karyawan
 * Modul Penggajian SD Mentari Islamic School (SDMIS)
 * Mengelola Master Profil Gaji Pokok, Status GTY, Tunjangan Jabatan, dan Rekening Bank Pegawai
 */
class Karyawan extends CI_Controller {

    public function __construct() {
        parent::__construct();
        is_logged_in();
        $this->load->library('form_validation');
        $this->load->model('payroll_model', 'payroll');
        $this->load->model('user/menu_model', 'menu');
    }

    /**
     * Halaman Utama Master Gaji & Rekening 21 Guru / Karyawan
     */
    public function index() {
        cek_menu_access();
        $data['htmlpagejs'] = 'none';
        $data['nmenu']      = 'Penggajian';
        $data['nmenusub']   = 'Master Gaji & Rekening';
        $data['title']      = 'Master Gaji & Rekening';
        $data['namalabel']  = $data['title'];
        $data['auth']       = authUser();

        // Ambil daftar seluruh pegawai dengan profil gaji
        $list_karyawan = $this->payroll->get_list_pegawai_gaji();
        $data['karyawan'] = $list_karyawan;

        // Hitung statistik ringkas untuk KPI Card
        $total_pegawai = count($list_karyawan);
        $total_gty = 0;
        $total_kontrak_ekstra = 0;
        $total_rekening_ready = 0;

        foreach ($list_karyawan as $k) {
            if ($k['status_kepegawaian'] === 'gty') {
                $total_gty++;
            } else {
                $total_kontrak_ekstra++;
            }
            if (!empty($k['nomor_rekening'])) {
                $total_rekening_ready++;
            }
        }

        $data['stats'] = [
            'total_pegawai'        => $total_pegawai,
            'total_gty'            => $total_gty,
            'total_kontrak_ekstra' => $total_kontrak_ekstra,
            'total_rekening_ready' => $total_rekening_ready
        ];

        $data['setting_global'] = $this->payroll->get_setting_global();

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidemenu', $data);
        $this->load->view('templates/sidenav', $data);
        $this->load->view('module/gaji/karyawan/index', $data);
        $this->load->view('templates/footer', $data);
        $this->load->view('templates/fscript-html-end', $data);
    }

    /**
     * Endpoint AJAX untuk Mengambil Data Profil Gaji Satu Pegawai
     */
    public function get_detail_ajax($pegawai_id = null) {
        if (!$pegawai_id) {
            echo json_encode(['status' => 'error', 'message' => 'ID Pegawai tidak valid.']);
            return;
        }

        $detail = $this->payroll->get_pegawai_gaji_by_id((int)$pegawai_id);
        if ($detail) {
            echo json_encode(['status' => 'success', 'data' => $detail]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Data pegawai tidak ditemukan.']);
        }
    }

    /**
     * Proses Simpan / Update Profil Gaji & Rekening Pegawai
     */
    public function update_setting() {
        cek_menu_access();

        $pegawai_id = (int)$this->input->post('pegawai_id', true);
        if (!$pegawai_id) {
            if ($this->input->is_ajax_request()) {
                echo json_encode(['status' => 'error', 'message' => 'ID Pegawai tidak ditemukan.']);
                return;
            }
            redirect('gaji/karyawan');
            return;
        }

        // Helper pembersih angka desimal/rupiah
        $clean_num = function($val) {
            $cleaned = preg_replace('/[^0-9.]/', '', str_replace(',', '.', $val));
            return is_numeric($cleaned) ? (float)$cleaned : 0.00;
        };

        $status_kepegawaian      = trim($this->input->post('status_kepegawaian', true));
        $is_wali_kelas           = $this->input->post('is_wali_kelas', true) === 'y' ? 'y' : 'n';
        $nama_jabatan_struktural = trim($this->input->post('nama_jabatan_struktural', true));
        $nominal_tunj_struktural = $clean_num($this->input->post('nominal_tunj_struktural', true));
        $gaji_pokok_nominal      = $clean_num($this->input->post('gaji_pokok_nominal', true));
        $tunj_khusus_tetap       = $clean_num($this->input->post('tunj_khusus_tetap', true));
        $nama_bank               = trim($this->input->post('nama_bank', true));
        $nomor_rekening          = trim($this->input->post('nomor_rekening', true));
        $nama_pemilik_rekening   = trim($this->input->post('nama_pemilik_rekening', true));
        $cabang_bank             = trim($this->input->post('cabang_bank', true));
        $customer_reff           = trim($this->input->post('customer_reff', true));

        $valid_status = ['gty', 'kontrak', 'guru_ekstra', 'karyawan'];
        if (!in_array($status_kepegawaian, $valid_status)) {
            $status_kepegawaian = 'gty';
        }

        $data_save = [
            'status_kepegawaian'      => $status_kepegawaian,
            'is_wali_kelas'           => $is_wali_kelas,
            'nama_jabatan_struktural' => !empty($nama_jabatan_struktural) ? $nama_jabatan_struktural : null,
            'nominal_tunj_struktural' => $nominal_tunj_struktural,
            'gaji_pokok_nominal'      => $gaji_pokok_nominal,
            'tunj_khusus_tetap'       => $tunj_khusus_tetap,
            'nama_bank'               => !empty($nama_bank) ? $nama_bank : 'BRI',
            'nomor_rekening'          => !empty($nomor_rekening) ? $nomor_rekening : null,
            'nama_pemilik_rekening'   => !empty($nama_pemilik_rekening) ? $nama_pemilik_rekening : null,
            'cabang_bank'             => !empty($cabang_bank) ? $cabang_bank : 'Klaten',
            'customer_reff'           => !empty($customer_reff) ? $customer_reff : null,
            'updated_at'              => date('Y-m-d H:i:s')
        ];

        $res = $this->payroll->save_pegawai_gaji($pegawai_id, $data_save);

        if ($this->input->is_ajax_request()) {
            if ($res) {
                echo json_encode(['status' => 'success', 'message' => 'Profil gaji & rekening pegawai berhasil disimpan!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan profil gaji ke database.']);
            }
            return;
        }

        if ($res) {
            $this->session->set_flashdata('message', '<div class="alert alert-success alert-dismissible p-3 mb-4" role="alert"><i class="ti ti-check me-2"></i> Profil gaji dan rekening bank pegawai berhasil diperbarui!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
        } else {
            $this->session->set_flashdata('message', '<div class="alert alert-danger alert-dismissible p-3 mb-4" role="alert"><i class="ti ti-x me-2"></i> Terjadi kesalahan saat menyimpan data pegawai.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
        }

        redirect('gaji/karyawan');
    }
}
