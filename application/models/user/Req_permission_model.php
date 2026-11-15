<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Req_permission_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

	public function get_data() {
        $query = $this->db->query("SELECT * FROM tx_request_izin ORDER BY request_izin_id DESC")->result_array();
        return $query;
    }

    public function get_karyawan($id) {
        $query = $this->db->query("SELECT a.*, b.pegawai_id as pid FROM m_pegawai a LEFT JOIN tx_request_izin_pegawai b ON a.pegawai_id=b.pegawai_id AND b.request_izin_id='$id' WHERE a.is_status='y' AND a.is_del='n' ORDER BY id_pegawai ASC")->result_array();
        return $query;
    }

    public function add_proses($cekimgpdf,$upload) {

        if ($cekimgpdf=='') { 
            $filex = ''; 
        }else{ 
            $filex = $upload['path'].$upload['file']['file_name']; 
        }

        if ($this->input->post('tgl2')=='') {
            $tglakh = $this->input->post('tgl1');
        }else{
            $tglakh = $this->input->post('tgl2');
        }

        $tgl1 = strtotime($this->input->post('tgl1')); 
        $tgl2 = strtotime($tglakh); 
        $jarak = $tgl2 - $tgl1;
        $jumlahhari = $jarak / 60 / 60 / 24;

        $data = [
            'tipe_request'          => $this->input->post('kat'),
            'tanggal_request'       => $this->input->post('tgl1'),
            'tanggal_request_end'   => $tglakh,
            'r_jam_masuk'           => $this->input->post('jmasuk'),
            'r_jam_keluar'          => $this->input->post('jkeluar'),
            'catatan_awal'          => $this->input->post('catatanl'),
            'jumlah_cuti'           => $jumlahhari+1,
            'file_dokumen'          => $filex,
            'is_status'             => 1, // 0 pending, 1 acc, 2 tolak
            'created_at'            => date('Y-m-d H:i:s')
        ];
        $res = $this->db->insert('tx_request_izin', $data);
        $idnya = $this->db->insert_id();

        $idp = $this->input->post('idp');
        if ($idp!='') {
            foreach ($idp as $key => $value) {
                if($value!=""){
                    $data = [
                        'request_izin_id'   => $idnya,
                        'pegawai_id'        => $value
                    ];
                    $this->db->insert('tx_request_izin_pegawai', $data);
                }
            }
        }
        return $res;
    }

    public function edit_proses($id,$cekimgpdf,$upload,$oldimg) {

        if ($cekimgpdf=='') { 
            $filex = $oldimg; 
        }else{ 
            $filex = $upload['path'].$upload['file']['file_name']; 
        }

        if ($this->input->post('tgl2')=='') {
            $tglakh = $this->input->post('tgl1');
        }else{
            $tglakh = $this->input->post('tgl2');
        }

        $tgl1 = strtotime($this->input->post('tgl1')); 
        $tgl2 = strtotime($tglakh); 
        $jarak = $tgl2 - $tgl1;
        $jumlahhari = $jarak / 60 / 60 / 24;

        $this->db->set([
            'tipe_request'          => $this->input->post('kat'),
            'tanggal_request'       => $this->input->post('tgl1'),
            'tanggal_request_end'   => $tglakh,
            'r_jam_masuk'           => $this->input->post('jmasuk'),
            'r_jam_keluar'          => $this->input->post('jkeluar'),
            'catatan_awal'          => $this->input->post('catatanl'),
            'is_status'             => $this->input->post('status'),
            'jumlah_cuti'           => $jumlahhari+1,
            'file_dokumen'          => $filex
        ]);
        $this->db->where('request_izin_id', $id);
        $res = $this->db->update('tx_request_izin');


        $buff = $this->db->query("SELECT * FROM tx_request_izin_pegawai WHERE request_izin_id='$id'")->result_array();
        $this->db->delete('tx_request_izin_pegawai', ['request_izin_id' => $id]);
        $idp = $this->input->post('idp');
        if ($idp!='') {
            foreach ($idp as $key => $value) {

                if($value!=''){
                    $data = [
                        'request_izin_id'   => $id,
                        'pegawai_id'        => $value
                    ];
                    $this->db->insert('tx_request_izin_pegawai', $data);
                }
            }
        }

        foreach ($buff as $row) {
            $resbuff = $this->db->query("SELECT * FROM tx_request_izin_pegawai WHERE request_izin_id='$id' AND pegawai_id='$row[pegawai_id]'")->num_rows();

            if ($resbuff==0) {
                $this->db->delete('tx_absensi', ['pegawai_id' => $row['pegawai_id'], 'is_request' => $id]);
            }
        }
        
        return $res;
    }

}