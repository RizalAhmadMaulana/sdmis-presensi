<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Data_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

	public function get_data() {
        $has_face_table = $this->db->query("SHOW TABLES LIKE 'm_pegawai_face'")->num_rows();
        if ($has_face_table > 0) {
            $query = $this->db->query("SELECT a.*, b.face_id, b.foto_sample, b.is_verified as face_verified, b.updated_at as face_updated_at 
            	FROM m_pegawai a
            	LEFT JOIN m_pegawai_face b ON a.pegawai_id=b.pegawai_id
            	WHERE a.is_del='n'")->result_array();
        } else {
            $query = $this->db->query("SELECT * FROM m_pegawai
            	WHERE is_del='n'")->result_array();
        }
        return $query;
    }

    public function add_proses() {
        $data = [
            'id_pegawai'          => $this->input->post('idkar'),
            'nama_pegawai'  	  => $this->input->post('nama'),
            'email_pegawai'  	  => $this->input->post('email'),
            'nomor_pegawai'       => $this->input->post('nom'),
            'jenis_kelamin'       => $this->input->post('jeniskelamin'),
            'tanggal_mulai_kerja' => $this->input->post('tglmulai'),
            'password_pegawai'    => password_hash($this->input->post('password'), PASSWORD_DEFAULT),
            'foto_pegawai'        => 'assets/uploaded/users/default-logo.png',
            'is_status'  		  => $this->input->post('status'),
            'created_at'  		  => date('Y-m-d H:i:s')
        ];
        $res = $this->db->insert('m_pegawai', $data);
        return $res;
    }

    public function edit_proses($id) {
        $pega = authKaryawan($id);
        $pass = $this->input->post('password');
        if ($pass=='') {
            $passnya = $pega['password_pegawai'];
        }else{
            $passnya = password_hash($this->input->post('password'), PASSWORD_DEFAULT);
        }

        $this->db->set([
            'id_pegawai'          => $this->input->post('idkar'),
            'nama_pegawai'        => $this->input->post('nama'),
            'email_pegawai'       => $this->input->post('email'),
            'nomor_pegawai'       => $this->input->post('nom'),
            'jenis_kelamin'       => $this->input->post('jeniskelamin'),
            'tanggal_mulai_kerja' => $this->input->post('tglmulai'),
            'is_status'           => $this->input->post('status'),
            'password_pegawai'    => $passnya
        ]);
        $this->db->where('pegawai_id', $id);
        $res = $this->db->update('m_pegawai');
        return $res;
    }

}