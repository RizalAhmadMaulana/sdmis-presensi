<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Post_model extends CI_Model {

    protected $today;
    protected $todayy;
    protected $time;
    public function __construct() {
        parent::__construct();
        $this->today = date('Y-m-d');
        $this->todayy = date('Y/m/d');
        $this->time = date('H:i');
    }

    public function signin($postjson) {

        if (!isset($postjson)) exit();

        $c_data = $this->db->query("SELECT * FROM m_pegawai WHERE email_pegawai='$postjson[email]'");

        if ($c_data->num_rows() > 0) {
            $result = $c_data->row_array();
            if (password_verify($postjson['password'], $result['password_pegawai'])) {
                if($result['is_status']=='y'){
                    return json_encode(array('status'=>true, 'result'=>$result, 'msg'=>'Login berhasil.'));
                }else if($result['is_active']=='n'){
                    return json_encode(array('status'=>false, 'msg'=>'Akun ini tidak aktif, hubungi kontak support untuk informasi lebih lanjut.'));
                }else{
                    return json_encode(array('status'=>false, 'msg'=>'Login gagal, status akun null.'));
                }
            }else{
                return json_encode(array('status'=>false, 'msg'=>'Email atau password tidak sesuai.'));
            }
        }else{
            return json_encode(array('status'=>false, 'msg'=>'Login gagal, akun tidak terdaftar.'));
        }
    }

    public function post_attendance($postjson) {

        if (!isset($postjson)) exit();

        $data = array();
        $datakar = authKaryawan($postjson['id']);

        if ($datakar) {

            $tgl = date('Y-m-d');
            $check_tgl = $this->db->query("SELECT * FROM tx_tanggal WHERE tanggal='$tgl'")->num_rows();
            if ($check_tgl==0) {
                $datatgl = [
                    'tanggal'       => $tgl
                ];
                $this->db->insert('tx_tanggal', $datatgl);
            }            

            $query = "SELECT a.pegawai_id as pid, a.nama_pegawai, b.*, c.mulai_berlaku_tanggal, c.dari_hari_ke, c.pola_kerja_id FROM m_pegawai a
                LEFT JOIN tx_absensi b ON a.pegawai_id=b.pegawai_id AND b.tanggal_absen='$this->today'
                LEFT JOIN m_pegawai_pola c ON a.pegawai_id=c.pegawai_id AND c.is_selected='y'
                WHERE a.pegawai_id='$postjson[id]' AND a.is_del='n'";

            $result = $this->db->query($query)->result_array();

            foreach ($result as $row) {

                if (isset($row['mulai_berlaku_tanggal'])) {
                    if ($this->today >= $row['mulai_berlaku_tanggal']) {

                        $tgl1 = strtotime($row['mulai_berlaku_tanggal']); 
                        $tgl2 = strtotime($this->today); 

                        $jarak = $tgl2 - $tgl1;
                        $hari = $jarak / 60 / 60 / 24;
                        $checkJumlahPola = checkJumlahPola($row['pola_kerja_id'],$hari+($row['dari_hari_ke']));
                        $q = $this->db->query("SELECT a.*, b.toleransi_terlambat FROM m_pola_kerja_det a 
                            JOIN m_pola_kerja b ON a.pola_kerja_id=b.pola_kerja_id 
                            WHERE a.pola_kerja_id='$row[pola_kerja_id]' AND a.is_day='$checkJumlahPola'")->row_array();

                        if (isset($q['is_work']) && $q['is_work']=='n') { $st = 'l'; }else{ $st = 'ts'; }

                        $q2 = $this->db->query("SELECT * FROM tx_absensi WHERE tanggal_absen='$this->today' AND pegawai_id='$row[pid]'")->num_rows();
                        if ($q2==0) {
                            $datains = [
                                'tanggal_absen'       => $this->today,
                                'pegawai_id'          => $row['pid'],
                                'j_masuk'             => $q['jam_masuk'],
                                'j_pulang'            => $q['jam_pulang'],
                                'j_toleransi'         => $q['toleransi_terlambat'],
                                'is_status'           => $st
                            ];
                            $this->db->insert('tx_absensi', $datains);
                        }
                    }
                }
            }

            $result = $this->db->query($query)->row_array();

            $lembur = $this->db->query("SELECT a.lembur_id, 
            a.tanggal_lembur, a.masuk_lembur, a.keluar_lembur, 
            a.is_status, a.created_at, b.* FROM tx_lembur a JOIN tx_lembur_pegawai b ON a.lembur_id=b.lembur_id
            WHERE b.pegawai_id='$postjson[id]'
            AND a.tanggal_lembur='$this->today' ORDER BY a.lembur_id DESC
            ")->row_array();

            $statusizinrequesttxt = '';
            $txt_lblabsen = '';
            $unlockabsen = 'y';
            $txtizx = '';
            $statusizinrequest = $this->db->query("SELECT a.tipe_request, a.is_status FROM tx_request_izin a JOIN tx_request_izin_pegawai b ON a.request_izin_id=b.request_izin_id WHERE b.pegawai_id='$postjson[id]' AND a.tanggal_request<='$this->today' AND a.tanggal_request_end>='$this->today'")->row_array();

            if($statusizinrequest){
                if($statusizinrequest['is_status']==1){
                    $unlockabsen = 'n';
                    if($statusizinrequest['tipe_request']=='csh'){
                        $statusizinrequesttxt = 'Absen Dikunci Status = Cuti Setengah Hari ';
                    }else if($statusizinrequest['tipe_request']=='i'){
                        $statusizinrequesttxt = 'Absen Dikunci Status = Izin Cuti '.$txtizx;
                    }else if($statusizinrequest['tipe_request']=='s'){
                        $statusizinrequesttxt = 'Absen Dikunci Status = Izin Sakit '.$txtizx;
                    }else if($statusizinrequest['tipe_request']=='ct'){
                        $statusizinrequesttxt = 'Absen Dikunci Status = Cuti Tahunan '.$txtizx;
                    }else if($statusizinrequest['tipe_request']=='tl'){
                        $unlockabsen = 'y';
                    }
                }else if($statusizinrequest['is_status']==0 || $statusizinrequest['is_status']==2){
                    if ($statusizinrequest['is_status']==0) {
                        $txtizx = 'masih menunggu persetujuan.';
                    }else{
                        $txtizx = 'telah ditolak.';
                    }
                    if($statusizinrequest['tipe_request']=='csh'){
                        $statusizinrequesttxt = 'Request Cuti Setengah Hari '.$txtizx;
                    }else if($statusizinrequest['tipe_request']=='i'){
                        $statusizinrequesttxt = 'Request Izin Cuti '.$txtizx;
                    }else if($statusizinrequest['tipe_request']=='s'){
                        $statusizinrequesttxt = 'Request Izin Sakit '.$txtizx;
                    }else if($statusizinrequest['tipe_request']=='ct'){
                        $statusizinrequesttxt = 'Request Cuti Tahunan '.$txtizx;
                    }else if($statusizinrequest['tipe_request']=='tl'){
                        $statusizinrequesttxt = 'Request Tugas Luar '.$txtizx;
                    }
                }
            }

            if ($result) {

                $res2 = $this->db->query("SELECT a.tipe_request FROM tx_request_izin a JOIN tx_request_izin_pegawai b ON a.request_izin_id=b.request_izin_id WHERE b.pegawai_id='$postjson[id]' AND a.tanggal_request<='$this->today' AND a.tanggal_request_end>='$this->today' AND a.is_status=1")->row_array();

                if(isset($res2['tipe_request'])) $result['is_status'] = $res2['tipe_request'];

                if($result['is_status']=='' || $result['is_status']=='ts'){
                  $txt_swork = 'Hari Kerja';
                }else if($result['is_status']=='th'){
                  $txt_swork = 'Tidak Hadir';
                }else if($result['is_status']=='l'){
                  $txt_swork = 'Libur Hari Ini';
                  $txt_lblabsen = 'Status kamu sedang '.$txt_swork.', tapi jika kamu masuk kerja silahkan melakukan absen.';
                }else if($result['is_status']=='hhk'){
                  $txt_swork = 'Masuk Kerja';
                }else if($result['is_status']=='hbhk'){
                  $txt_swork = 'Masuk Dihari Kerja';
                }else if($result['is_status']=='s'){
                  $txt_swork = 'Sakit';
                  $txt_lblabsen = 'Status kamu sedang '.$txt_swork.', tapi jika kamu masuk kerja silahkan melakukan absen.';
                }else if($result['is_status']=='i'){
                  $txt_swork = 'Izin';
                  $txt_lblabsen = 'Status kamu sedang '.$txt_swork.', tapi jika kamu masuk kerja silahkan melakukan absen.';
                }else if($result['is_status']=='c'){
                  $txt_swork = 'Cuti';
                  $txt_lblabsen = 'Status kamu sedang '.$txt_swork.', tapi jika kamu masuk kerja silahkan melakukan absen.';
                }else if($result['is_status']=='cb'){
                  $txt_swork = 'Cuti Bersama';
                  $txt_lblabsen = 'Status kamu sedang '.$txt_swork.', tapi jika kamu masuk kerja silahkan melakukan absen.';
                }else if($result['is_status']=='ct'){
                  $txt_swork = 'Cuti Tahunan';
                  $txt_lblabsen = 'Status kamu sedang '.$txt_swork.', tapi jika kamu masuk kerja silahkan melakukan absen.';
                }else if($result['is_status']=='csh'){
                  $txt_swork = 'Cuti Setengah Hari';
                  $txt_lblabsen = 'Status kamu sedang '.$txt_swork.', tapi jika kamu masuk kerja silahkan melakukan absen.';
                }else if($result['is_status']=='tl'){
                  $txt_swork = 'Tugas Luar';
                }else{
                  $txt_swork = '';
                }

                $datashift = $this->db->query("SELECT * FROM tx_shift WHERE pegawai_id='$postjson[id]' AND tanggal_shift='$this->today'")->row_array();

                if (!$datashift) {
                    $datashift = '';
                }

                $txt_pending = '';
                if($result['is_pending']=='y'){
                    $txt_pending = 'Status absen menunggu persetujuan, kamu melakukan absen diluar jangkauan atau radius lokasi yang ditetapkan, tapi tenang kamu masih bisa melakukan absen hingga selesai.';
                }

                $txt_accmasuk = '';
                if($result['acc_masuk']=='t'){
                    $txt_accmasuk = 'Absen masuk ditolak oleh HRD atau admin, silahkan hubungi pihak terkait untuk informasi lebih lanjut.';
                }

                $data = array(
                    'nama_pegawai'          => $result['nama_pegawai'],
                    'tanggal_ini_display'   => $this->todayy,
                    'tanggal_absen'         => $result['tanggal_absen'],
                    'is_status'             => $result['is_status'],
                    'txt_swork'             => $txt_swork,
                    'jam_masuk'             => $result['jam_masuk'],
                    'jam_istirahat'         => $result['jam_istirahat'],
                    'jam_sistirahat'        => $result['jam_sistirahat'],
                    'jam_keluar'            => $result['jam_keluar'],
                    'catatan_masuk'         => $result['catatan_masuk'],
                    'catatan_keluar'        => $result['catatan_keluar'],
                    'acc_masuk'             => $result['acc_masuk'],
                    'acc_keluar'            => $result['acc_keluar'],
                    'is_pending'            => $result['is_pending'],
                    'txt_pending'           => $txt_pending,
                    'lembur'                => $lembur,
                    'txt_accmasuk'          => $txt_accmasuk,
                    'statusizinrequesttxt'  => $statusizinrequesttxt,
                    'txt_lblabsen'          => $txt_lblabsen,
                    'unlockabsen'           => $unlockabsen,
                    'datashift'             => $datashift
                );
            }

            return json_encode(array('status'=>true, 'result'=>$data));

        }else{
            return json_encode(array('status'=>false, 'msg'=>'Proses dihentikan, id unknown, silahkan logout dan login kembali.'));
        }
    }

    public function attendance($postjson) {

        if (!isset($postjson)) exit();

        $msg = '';
        $res = false;
        if ($postjson['status']=='in') {
            $field = 'jam_masuk'; 
            $msg = 'Absen masuk berhasil pada jam '.$this->time;
        }else if ($postjson['status']=='out') {
            $field = 'jam_keluar'; 
            $msg = 'Absen keluar berhasil.';
        }else if ($postjson['status']=='iin') {
            $field = 'jam_istirahat'; 
            $msg = 'Absen istirahat berhasil, waktu istirahat telah dimulai.';
        }else if ($postjson['status']=='oout') {
            $field = 'jam_sistirahat'; 
            $msg = 'Absen selesai istirahat berhasil, waktu nya kembali bekerja.';
        }else{
            return json_encode(array('status'=>false, 'msg'=>'Proses dihentikan, status unknown.'));
            exit();
        }

        $datakar = authKaryawan($postjson['id']);

        if ($datakar) {

            $qlokasi = $this->db->query("SELECT * FROM m_pegawai_lokasi a
                JOIN m_lokasi b ON a.lokasi_id=b.lokasi_id
                WHERE a.pegawai_id='$postjson[id]'")->row_array();

            $lat_point = '';
            $lng_point = '';
            if($qlokasi){
                $lat_point = $qlokasi['garis_lintang'];
                $lng_point = $qlokasi['garis_bujur'];
            }else {
                $qlokasi = $this->db->query("SELECT * FROM m_lokasi")->row_array();
                if($qlokasi){
                    $lat_point = $qlokasi['garis_lintang'];
                    $lng_point = $qlokasi['garis_bujur'];
                }
            }

            $randname = date('Ymdhis').$postjson['id'];

            $q = $this->db->query("SELECT * FROM tx_absensi WHERE pegawai_id='$postjson[id]' AND tanggal_absen='$this->today'");
            $result = $q->row_array();

            if(!isset($result['is_status'])) $result['is_status'] = '';
            if(!isset($result['jam_masuk'])) $result['jam_masuk'] = '';
            if(!isset($result['jam_keluar'])) $result['jam_keluar'] = '';
            if(!isset($result['jam_istirahat'])) $result['jam_istirahat'] = '';
            if(!isset($result['jam_sistirahat'])) $result['jam_sistirahat'] = '';
            
            if ($this->time > $result['jam_masuk'] && $result['jam_masuk']=='' && $result['is_status']=='th') {
                return json_encode(array('status'=>false, 'msg'=>'Proses dihentikan, action telah ditutup.'));
                exit();
            }

            if ($postjson['status']=='in' && $result['jam_masuk']!='') {
                return json_encode(array('status'=>false, 'msg'=>'Proses dihentikan, absen masuk telah dilakukan pada jam '.$result['jam_masuk']));
            }

            if ($postjson['status']=='out' && $result['jam_keluar']!='') {
                return json_encode(array('status'=>false, 'msg'=>'Proses dihentikan, absen keluar telah dilakukan pada jam '.$result['jam_keluar']));
            }

            if ($postjson['status']=='iin' && $result['jam_istirahat']!='') {
                return json_encode(array('status'=>false, 'msg'=>'Proses dihentikan, waktu mulai istirahat telah dilakukan.'));
            }

            if ($postjson['status']=='oout' && $result['jam_sistirahat']!='') {
                return json_encode(array('status'=>false, 'msg'=>'Proses dihentikan, waktu selesai istirahat telah dilakukan.'));
            }

            if ($q->num_rows()==0) {
                if (($postjson['status']=='out' || $postjson['status']=='oout' || $postjson['status']=='iin') && $result['jam_masuk']=='') {
                    return json_encode(array('status'=>false, 'msg'=>'Proses dihentikan, absen masuk belum dilakukan.'));
                    exit();
                }else{

                    if ($postjson['poinradius']=='y') {
                        $ispending = 'n';
                    }else{
                        $ispending = 'y';
                    }

                    if ($postjson['image_path']!='') {
                        $ext = 'jpeg';
                        $imgstring = $postjson['image_path'];
                        $imgstring = trim(str_replace('data:image/'.$ext.';base64,', "", $imgstring));
                        $imgstring = str_replace(' ', '+', $imgstring);
                        $data = base64_decode($imgstring);
                        $directory = "assets/uploaded/users/";
                        $absenfoto  = $directory."absen_masuk_".$postjson['id']."_".$randname.".jpg";  
                        file_put_contents($absenfoto, $data);
                    }else{
                        $absenfoto = '';
                    }

                    $data = [
                        'tanggal_absen'       => $this->today,
                        'pegawai_id'          => $postjson['id'],
                        'is_status'           => 'hhk',
                        'foto_absen_masuk'    => $absenfoto,
                        'latitude_masuk'      => $postjson['latitude'],
                        'longitude_masuk'     => $postjson['longitude'],
                        'point_latitude'      => $lat_point,
                        'point_longitude'     => $lng_point,
                        'catatan_masuk'       => $postjson['catatan'],
                        'is_point_masuk'      => $postjson['poinradius'],
                        'is_pending'          => $ispending,
                        $field   => $this->time
                    ];
                    if (isset($postjson['face_similarity'])) {
                        $data['face_similarity_score'] = $postjson['face_similarity'];
                    }
                    if (isset($postjson['face_status'])) {
                        $data['face_status'] = $postjson['face_status'];
                    }
                    $res = $this->db->insert('tx_absensi', $data);
                }
            }else{
                if (($postjson['status']=='out' || $postjson['status']=='oout' || $postjson['status']=='iin') && $result['jam_masuk']=='') {
                    return json_encode(array('status'=>false, 'msg'=>'Proses dihentikan, absen masuk belum dilakukan.'));
                    exit();
                }else{

                    if ($result['is_status']=='ts' || $result['is_status']=='th') {
                        $status_ab = 'hhk';
                    }else if ($result['is_status']=='l') {
                        $status_ab = 'hbhk';
                    }else if ($result['is_status']=='s' || $result['is_status']=='i' || $result['is_status']=='c' || $result['is_status']=='cb' || $result['is_status']=='ct') {
                        $status_ab = 'hhk';
                    }else{
                        $status_ab = $result['is_status'];
                    }

                    if ($postjson['status']=='out' && $result['jam_istirahat']=='') {
                        $this->db->set([
                            'jam_istirahat'     => '12:00',
                            'jam_sistirahat'    => '13:00',
                            'is_status'         => $status_ab,
                            $field              => $this->time
                        ]);   
                    }else if ($postjson['status']=='out' && $result['jam_sistirahat']=='') {
                        $this->db->set([
                            'jam_sistirahat'    => '13:00',
                            'is_status'         => $status_ab,
                            $field              => $this->time
                        ]);
                    }else if ($postjson['status']=='oout' && $result['jam_istirahat']=='') {
                        if ($this->time>'13:30') {
                            $this->time = '13:30';
                        }
                        $this->db->set([
                            'jam_istirahat'     => '12:00',
                            'is_status'         => $status_ab,
                            $field              => $this->time
                        ]);
                    }else{
                        if ($postjson['status']=='oout' && $this->time>'13:30') {
                            $this->time = '13:30';
                        }
                        $this->db->set([
                            'point_latitude'    => $lat_point,
                            'point_longitude'   => $lng_point,
                            'is_status'         => $status_ab,
                            $field              => $this->time
                        ]);
                    }
                    $this->db->where('absen_id', $result['absen_id']);
                    $res = $this->db->update('tx_absensi');

                    if ($postjson['status']=='in') {

                        if ($postjson['poinradius']=='y') {
                            $ispending = 'n';
                        }else{
                            $ispending = 'y';
                        }

                        if ($postjson['image_path']!='') {
                            $ext = 'jpeg';
                            $imgstring = $postjson['image_path'];
                            $imgstring = trim(str_replace('data:image/'.$ext.';base64,', "", $imgstring));
                            $imgstring = str_replace(' ', '+', $imgstring);
                            $data = base64_decode($imgstring);
                            $directory = "assets/uploaded/users/";
                            $absenfoto  = $directory."absen_masuk_".$postjson['id']."_".$randname.".jpg";  
                            file_put_contents($absenfoto, $data);
                        }else{
                            $absenfoto = '';
                        }

                        $set_in = [
                            'foto_absen_masuk'    => $absenfoto,
                            'latitude_masuk'      => $postjson['latitude'],
                            'longitude_masuk'     => $postjson['longitude'],
                            'catatan_masuk'       => $postjson['catatan'],
                            'is_point_masuk'      => $postjson['poinradius'],
                            'is_pending'          => $ispending,
                            $field                => $this->time
                        ];
                        if (isset($postjson['face_similarity'])) {
                            $set_in['face_similarity_score'] = $postjson['face_similarity'];
                        }
                        if (isset($postjson['face_status'])) {
                            $set_in['face_status'] = $postjson['face_status'];
                        }
                        $this->db->set($set_in);
                        $this->db->where('absen_id', $result['absen_id']);
                        $res = $this->db->update('tx_absensi');

                    }else if ($postjson['status']=='out') {

                        if ($postjson['image_path']!='') {
                            $ext = 'jpeg';
                            $imgstring = $postjson['image_path'];
                            $imgstring = trim(str_replace('data:image/'.$ext.';base64,', "", $imgstring));
                            $imgstring = str_replace(' ', '+', $imgstring);
                            $data = base64_decode($imgstring);
                            $directory = "assets/uploaded/users/";
                            $absenfoto  = $directory."absen_keluar_".$postjson['id']."_".$randname.".jpg";  
                            file_put_contents($absenfoto, $data);
                        }else{
                            $absenfoto = '';
                        }

                        $set_out = [
                            'foto_absen_keluar'    => $absenfoto,
                            'latitude_keluar'      => $postjson['latitude'],
                            'longitude_keluar'     => $postjson['longitude'],
                            'catatan_keluar'       => $postjson['catatan'],
                            'is_point_keluar'      => $postjson['poinradius'],
                            'acc_keluar'           => $postjson['poinradius'],
                            $field                 => $this->time
                        ];
                        if (isset($postjson['face_similarity'])) {
                            $set_out['face_similarity_score'] = $postjson['face_similarity'];
                        }
                        if (isset($postjson['face_status'])) {
                            $set_out['face_status'] = $postjson['face_status'];
                        }
                        $this->db->set($set_out);
                        $this->db->where('absen_id', $result['absen_id']);
                        $res = $this->db->update('tx_absensi');
                    }
                }
            }

            return json_encode(array('status'=>true, 'msg'=>$msg));

        }else{
            return json_encode(array('status'=>false, 'msg'=>'Proses dihentikan, id unknown, silahkan logout dan login kembali.'));
        }

    }

    public function attendance_lembur($postjson) {

        if (!isset($postjson)) exit();
        $msg = '';
        if ($postjson['status']=='overtime' || $postjson['status']=='sovertime') {

            $randname = date('Ymdhis').$postjson['id'];

            if ($postjson['image_path']!='') {
                $ext = 'jpeg';
                $imgstring = $postjson['image_path'];
                $imgstring = trim(str_replace('data:image/'.$ext.';base64,', "", $imgstring));
                $imgstring = str_replace(' ', '+', $imgstring);
                $data = base64_decode($imgstring);
                $directory = "assets/uploaded/users/";
                $absenfoto  = $directory."lembur_".$postjson['id']."_".$randname.".jpg";  
                file_put_contents($absenfoto, $data);
            }else{
                $absenfoto = '';
            }

            if ($postjson['status']=='overtime') {

                $qlokasi = $this->db->query("SELECT * FROM m_pegawai_lokasi a
                JOIN m_lokasi b ON a.lokasi_id=b.lokasi_id
                WHERE a.pegawai_id='$postjson[id]'")->row_array();

                $lat_point = '';
                $lng_point = '';
                if($qlokasi){
                    $lat_point = $qlokasi['garis_lintang'];
                    $lng_point = $qlokasi['garis_bujur'];
                }else {
                    $qlokasi = $this->db->query("SELECT * FROM m_lokasi")->row_array();
                    if($qlokasi){
                        $lat_point = $qlokasi['garis_lintang'];
                        $lng_point = $qlokasi['garis_bujur'];
                    }
                }

                $this->db->set([
                    'absen_masuk'         => $this->time,
                    'foto_absen_masuk'    => $absenfoto,
                    'latitude_masuk'      => $postjson['latitude'],
                    'longitude_masuk'     => $postjson['longitude'],
                    'point_latitude'      => $lat_point,
                    'point_longitude'     => $lng_point
                ]);
                $msg = 'Absen lembur berhasil.';
            }else{
                $this->db->set([
                    'absen_keluar'         => $this->time,
                    'foto_absen_keluar'    => $absenfoto,
                    'latitude_keluar'      => $postjson['latitude'],
                    'longitude_keluar'     => $postjson['longitude'],
                    'catatan_hasil_lembur' => $postjson['catatan']
                ]);
                $msg = 'Absen lembur keluar berhasil.';
            }
            $this->db->where('pegawai_id', $postjson['id']);
            $this->db->where('tanggal_lembur', $this->today);
            $res = $this->db->update('tx_lembur_pegawai');
            if ($res==true) {
                return json_encode(array('status'=>true, 'msg'=>$msg));
            }else{
                return json_encode(array('status'=>false, 'msg'=>'Proses dihentikan, absen lembur gagal.'));
            }
        }else{
            return json_encode(array('status'=>false, 'msg'=>'Unknown in/out.'));
        }

    }

    public function add_lembur($postjson) {

        if (!isset($postjson)) exit();

        $c_data = $this->db->query("SELECT * FROM m_pegawai WHERE pegawai_id='$postjson[id]'")->num_rows();

        if ($c_data > 0) {

            $c_data = $this->db->query("SELECT * FROM tx_lembur_pegawai WHERE pegawai_id='$postjson[id]' AND tanggal_lembur='$postjson[tanggal]'")->num_rows();
            if ($c_data==0) {
                $datains = [
                    'tanggal_lembur'      => $postjson['tanggal'],
                    'masuk_lembur'        => $postjson['jmulai'],
                    'keluar_lembur'       => $postjson['jakhir'],
                    'is_status'           => 0,
                    'created_at'          => date('Y-m-d H:i:s')
                ];
                $res = $this->db->insert('tx_lembur', $datains);
                $idnya = $this->db->insert_id();

                $datainss = [
                    'lembur_id'             => $idnya,
                    'pegawai_id'            => $postjson['id'],
                    'tanggal_lembur'        => $postjson['tanggal'],
                    'catatan_lembur'        => $postjson['catatan']
                ];
                $res = $this->db->insert('tx_lembur_pegawai', $datainss);

                if($res==true){
                    return json_encode(array('status'=>true, 'msg'=>'Request lembur telah dikirim.'));
                }else{
                    return json_encode(array('status'=>false, 'msg'=>'Proses dihentikan, request lembur gagal.'));
                }
            }else{
                return json_encode(array('status'=>false, 'msg'=>'Proses dihentikan, request lembur pada tanggal ini sudah ada.'));
            }
        }else{
            return json_encode(array('status'=>false, 'msg'=>'Proses dihentikan, ID unknown silahkan logout dan login kembali..'));
        }
    }

    public function add_izin($postjson) {

        if (!isset($postjson)) exit();

        $c_data = $this->db->query("SELECT * FROM m_pegawai WHERE pegawai_id='$postjson[id]'")->num_rows();

        if ($c_data > 0) {

            $tanggal = date("Y-m-d", strtotime($postjson['tanggal']));
            $totanggal = date("Y-m-d", strtotime($postjson['totanggal']));

            if ($postjson['totanggal']=='') {
                $totanggal = $tanggal;
            }else{
                $totanggal = $totanggal;
            }
            
            if($tanggal<$this->today){
                return json_encode(array('status'=>false, 'msg'=>'Proses dihentikan, minimal tanggal yang di isi adalah tanggal hari ini.'));
                exit();
            }

            if($tanggal>$totanggal){
                return json_encode(array('status'=>false, 'msg'=>'Proses dihentikan, tanggal berakhir tidak boleh lebih rendah dari tanggal mulai.'));
                exit();
            }

            $c_data = $this->db->query("SELECT * FROM tx_request_izin a JOIN tx_request_izin_pegawai b ON a.request_izin_id=b.request_izin_id WHERE b.pegawai_id='$postjson[id]' AND a.tanggal_request<='$tanggal' AND a.tanggal_request_end>='$tanggal'")->num_rows();
            if ($c_data==0) {

                $randname = date('Ymdhis').$postjson['id'];

                if ($postjson['totanggal']=='') {
                    $tglakh = $tanggal;
                }else{
                    $tglakh = $totanggal;
                }

                if ($tglakh<$tanggal) {
                    $tglakh = $tanggal;
                }

                $tgl1 = strtotime($tanggal); 
                $tgl2 = strtotime($tglakh); 
                $jarak = $tgl2 - $tgl1;
                $jumlahhari = $jarak / 60 / 60 / 24;

                if ($postjson['doc_file']!='') {
                    $ext = 'jpeg';
                    $imgstring = $postjson['doc_file'];
                    $imgstring = trim(str_replace('data:image/'.$ext.';base64,', "", $imgstring));
                    $imgstring = str_replace(' ', '+', $imgstring);
                    $data = base64_decode($imgstring);
                    $directory = "assets/uploaded/users/";
                    $filedoc  = $directory."files_".$postjson['id']."_".$randname.".jpg";  
                    file_put_contents($filedoc, $data);
                }else{
                    $filedoc = '';
                }

                $data = [
                    'tipe_request'          => $postjson['kategori'],
                    'tanggal_request'       => $tanggal,
                    'tanggal_request_end'   => $tglakh,
                    'r_jam_masuk'           => $postjson['jmulai'],
                    'r_jam_keluar'          => $postjson['jakhir'],
                    'catatan_awal'          => $postjson['catatan'],
                    'jumlah_cuti'           => $jumlahhari+1,
                    'file_dokumen'          => $filedoc,
                    'is_status'             => 0, // 0 pending, 1 acc, 2 tolak
                    'created_at'            => date('Y-m-d H:i:s')
                ];
                $res = $this->db->insert('tx_request_izin', $data);
                $idnya = $this->db->insert_id();

                $data = [
                    'request_izin_id'   => $idnya,
                    'pegawai_id'        => $postjson['id']
                ];
                $res = $this->db->insert('tx_request_izin_pegawai', $data);

                if($res==true){
                    return json_encode(array('status'=>true, 'msg'=>'Request izin telah dikirim.'));
                }else{
                    return json_encode(array('status'=>false, 'msg'=>'Proses dihentikan, request gagal.'));
                }
            }else{
                return json_encode(array('status'=>false, 'msg'=>'Proses dihentikan, request diantara tanggal ini sudah ada.'));
            }
        }else{
            return json_encode(array('status'=>false, 'msg'=>'Proses dihentikan, ID unknown silahkan logout dan login kembali..'));
        }
    }

    public function edit_izin($postjson) {

        if (!isset($postjson)) exit();

        $c_data = $this->db->query("SELECT * FROM m_pegawai WHERE pegawai_id='$postjson[id]'")->num_rows();

        if ($c_data > 0) {

            $tanggal = date("Y-m-d", strtotime($postjson['tanggal']));
            $totanggal = date("Y-m-d", strtotime($postjson['totanggal']));

            if ($postjson['totanggal']=='') {
                $totanggal = $tanggal;
            }else{
                $totanggal = $totanggal;
            }
            
            if($tanggal<$this->today){
                return json_encode(array('status'=>false, 'msg'=>'Proses dihentikan, minimal tanggal yang di isi adalah tanggal hari ini.'));
                exit();
            }

            if($tanggal>$totanggal){
                return json_encode(array('status'=>false, 'msg'=>'Proses dihentikan, tanggal berakhir tidak boleh lebih rendah dari tanggal mulai.'));
                exit();
            }

            $c_data = $this->db->query("SELECT * FROM tx_request_izin a JOIN tx_request_izin_pegawai b ON a.request_izin_id=b.request_izin_id WHERE b.pegawai_id='$postjson[id]' AND a.tanggal_request<='$tanggal' AND a.tanggal_request_end>='$tanggal' AND a.request_izin_id!='$postjson[idizin]'")->num_rows();
            if ($c_data==0) {

                if ($postjson['totanggal']=='') {
                    $tglakh = $tanggal;
                }else{
                    $tglakh = $totanggal;
                }

                if ($tglakh<$tanggal) {
                    $tglakh = $tanggal;
                }

                $tgl1 = strtotime($tanggal); 
                $tgl2 = strtotime($tglakh); 
                $jarak = $tgl2 - $tgl1;
                $jumlahhari = $jarak / 60 / 60 / 24;

                $cc_data = $this->db->query("SELECT file_dokumen FROM tx_request_izin WHERE request_izin_id='$postjson[idizin]'")->row_array();

                if ($postjson['doc_file']!='') {
                    $ext = 'jpeg';
                    $imgstring = $postjson['doc_file'];
                    $imgstring = trim(str_replace('data:image/'.$ext.';base64,', "", $imgstring));
                    $imgstring = str_replace(' ', '+', $imgstring);
                    $data = base64_decode($imgstring);
                    $directory = "assets/uploaded/users/";
                    $filedoc  = $directory."files_".$postjson['id']."_".$randname.".jpg";  
                    file_put_contents($absenfoto, $data);
                    if($cc_data['file_dokumen']!=''){
                        if (file_exists(FCPATH.$cc_data['file_dokumen'])){ unlink(FCPATH.$cc_data['file_dokumen']); }
                    }

                }else{
                    $filedoc = $cc_data['file_dokumen'];
                }

                $this->db->set([
                    'tipe_request'          => $postjson['kategori'],
                    'tanggal_request'       => $tanggal,
                    'tanggal_request_end'   => $tglakh,
                    'r_jam_masuk'           => $postjson['jmulai'],
                    'r_jam_keluar'          => $postjson['jakhir'],
                    'catatan_awal'          => $postjson['catatan'],
                    'jumlah_cuti'           => $jumlahhari+1,
                    'file_dokumen'          => $filedoc,
                    'is_status'             => 0 // 0 pending, 1 acc, 2 tolak
                ]);
                $this->db->where('request_izin_id', $postjson['idizin']);
                $res = $this->db->update('tx_request_izin');

                if($res==true){
                    return json_encode(array('status'=>true, 'msg'=>'Request izin berhasil diperbarui.'));
                }else{
                    return json_encode(array('status'=>false, 'msg'=>'Proses dihentikan, edit request gagal.'));
                }
            }else{
                return json_encode(array('status'=>false, 'msg'=>'Proses dihentikan, request pada diantara tanggal ini sudah ada.'));
            }
        }else{
            return json_encode(array('status'=>false, 'msg'=>'Proses dihentikan, ID unknown silahkan logout dan login kembali..'));
        }
    }

    public function del_izin($postjson) {

        if (!isset($postjson)) exit();

        $c_data = $this->db->query("SELECT * FROM tx_request_izin a JOIN tx_request_izin_pegawai b ON a.request_izin_id=b.request_izin_id WHERE a.request_izin_id='$postjson[idizin]' AND b.pegawai_id='$postjson[id]'")->row_array();

        if ($c_data) {
            $res = $this->db->delete('tx_request_izin', ['request_izin_id' => $postjson['idizin']]);
            if($res==true){
                if($c_data['file_dokumen']!=''){
                    if (file_exists(FCPATH.$c_data['file_dokumen'])){ unlink(FCPATH.$c_data['file_dokumen']); }
                }
                return json_encode(array('status'=>true, 'msg'=>'Data berhasil dihapus.'));
            }else{
                return json_encode(array('status'=>false, 'msg'=>'Proses dihentikan, data gagal dihapus.'));
            }
        }else{
            return json_encode(array('status'=>false, 'msg'=>'Proses dihentikan, data tidak ditemukan.'));
        }
    }

    public function del_lembur($postjson) {

        if (!isset($postjson)) exit();

        $c_data = $this->db->query("SELECT * FROM tx_lembur a JOIN tx_lembur_pegawai b ON a.lembur_id=b.lembur_id WHERE a.lembur_id='$postjson[idizin]' AND b.pegawai_id='$postjson[id]'")->row_array();

        if ($c_data) {
            $res = $this->db->delete('tx_lembur_pegawai', ['lembur_id' => $postjson['idizin']]);
            
            $count = $this->db->query("SELECT * FROM tx_lembur_pegawai WHERE lembur_id='$postjson[idizin]'")->num_rows();
            if($count==0){
                $res = $this->db->delete('tx_lembur', ['lembur_id' => $postjson['idizin']]);
            }

            if($res==true){
                return json_encode(array('status'=>true, 'msg'=>'Data berhasil dihapus.'));
            }else{
                return json_encode(array('status'=>false, 'msg'=>'Proses dihentikan, data gagal dihapus.'));
            }
        }else{
            return json_encode(array('status'=>false, 'msg'=>'Proses dihentikan, data tidak ditemukan.'));
        }
    }

    public function reset_absen($postjson) {

        if (!isset($postjson)) exit();

        $c_data = $this->db->query("SELECT * FROM m_pegawai WHERE pegawai_id='$postjson[id]'")->num_rows();
        $c_absen = $this->db->query("SELECT * FROM tx_absensi WHERE pegawai_id='$postjson[id]' AND tanggal_absen='$this->today'")->row_array();

        if ($c_data > 0) {
            $res = $this->db->delete('tx_absensi', ['pegawai_id' => $postjson['id'], 'tanggal_absen' => $this->today]);
            if($res==true){
                if($c_absen['foto_absen_masuk']!=''){
                    if (file_exists(FCPATH.$c_absen['foto_absen_masuk'])){ unlink(FCPATH.$c_absen['foto_absen_masuk']); }
                }
                if($c_absen['foto_absen_keluar']!=''){
                    if (file_exists(FCPATH.$c_absen['foto_absen_keluar'])){ unlink(FCPATH.$c_absen['foto_absen_keluar']); }
                }
                return json_encode(array('status'=>true, 'msg'=>'Data berhasil dihapus.'));
            }else{
                return json_encode(array('status'=>false, 'msg'=>'Proses dihentikan, data gagal dihapus.'));
            }
        }else{
            return json_encode(array('status'=>false, 'msg'=>'Proses dihentikan, ID unknown silahkan logout dan login kembali..'));
        }
    }

    public function face_register($postjson) {
        if (!isset($postjson)) exit();

        if (empty($postjson['id']) || empty($postjson['descriptor_front'])) {
            return json_encode(array('status' => false, 'msg' => 'Data biometrik tidak lengkap. Pastikan scan wajah menghadap depan telah selesai.'));
        }

        $datakar = authKaryawan($postjson['id']);
        if (!$datakar) {
            return json_encode(array('status' => false, 'msg' => 'ID Pegawai tidak ditemukan. Silakan login kembali.'));
        }

        // Simpan foto sampel wajah jika dikirimkan (Base64 JPEG/PNG)
        $foto_sample = '';
        $sample_input = !empty($postjson['image_sample']) ? $postjson['image_sample'] : (!empty($postjson['foto_sample']) ? $postjson['foto_sample'] : '');
        if (!empty($sample_input)) {
            $imgstring = $sample_input;
            $imgstring = preg_replace('#^data:image/\w+;base64,#i', '', $imgstring);
            $imgstring = str_replace(' ', '+', $imgstring);
            $data_img = base64_decode($imgstring);

            $dir = "assets/uploaded/faces/";
            if (!is_dir(FCPATH . $dir)) {
                @mkdir(FCPATH . $dir, 0777, true);
                @file_put_contents(FCPATH . $dir . 'index.html', '<!DOCTYPE html><html><head><title>403 Forbidden</title></head><body><p>Directory access is forbidden.</p></body></html>');
            }

            $filename = "face_" . $postjson['id'] . "_" . date('YmdHis') . ".jpg";
            $filepath = $dir . $filename;
            if (@file_put_contents(FCPATH . $filepath, $data_img)) {
                $foto_sample = $filepath;
            }
        }

        // Format deskriptor (string JSON)
        $desc_front = is_array($postjson['descriptor_front']) ? json_encode($postjson['descriptor_front']) : $postjson['descriptor_front'];
        $desc_right = !empty($postjson['descriptor_right']) ? (is_array($postjson['descriptor_right']) ? json_encode($postjson['descriptor_right']) : $postjson['descriptor_right']) : null;
        $desc_left  = !empty($postjson['descriptor_left'])  ? (is_array($postjson['descriptor_left'])  ? json_encode($postjson['descriptor_left'])  : $postjson['descriptor_left'])  : null;
        $desc_up    = !empty($postjson['descriptor_up'])    ? (is_array($postjson['descriptor_up'])    ? json_encode($postjson['descriptor_up'])    : $postjson['descriptor_up'])    : null;
        $desc_down  = !empty($postjson['descriptor_down'])  ? (is_array($postjson['descriptor_down'])  ? json_encode($postjson['descriptor_down'])  : $postjson['descriptor_down'])  : null;

        // Cek apakah data wajah sudah pernah didaftarkan
        $q_face = $this->db->get_where('m_pegawai_face', ['pegawai_id' => $postjson['id']]);
        if ($q_face->num_rows() > 0) {
            $existing = $q_face->row_array();
            $update_data = [
                'descriptor_front' => $desc_front,
                'descriptor_right' => $desc_right,
                'descriptor_left'  => $desc_left,
                'descriptor_up'    => $desc_up,
                'descriptor_down'  => $desc_down,
                'is_verified'      => 'n', // Setiap update scan baru, wajib diverifikasi admin kembali
                'updated_at'       => date('Y-m-d H:i:s')
            ];
            if (!empty($foto_sample)) {
                $update_data['foto_sample'] = $foto_sample;
                if (!empty($existing['foto_sample']) && file_exists(FCPATH . $existing['foto_sample'])) {
                    @unlink(FCPATH . $existing['foto_sample']);
                }
            }
            $this->db->where('pegawai_id', $postjson['id']);
            $this->db->update('m_pegawai_face', $update_data);
        } else {
            $insert_data = [
                'pegawai_id'       => $postjson['id'],
                'descriptor_front' => $desc_front,
                'descriptor_right' => $desc_right,
                'descriptor_left'  => $desc_left,
                'descriptor_up'    => $desc_up,
                'descriptor_down'  => $desc_down,
                'foto_sample'      => $foto_sample,
                'is_verified'      => 'n',
                'created_at'       => date('Y-m-d H:i:s')
            ];
            $this->db->insert('m_pegawai_face', $insert_data);
        }

        // Tandai di m_pegawai
        $this->db->where('pegawai_id', $postjson['id']);
        $this->db->update('m_pegawai', ['is_face_registered' => 'y']);

        return json_encode([
            'status' => true,
            'msg' => 'Perekaman wajah biometrik berhasil disimpan! Menunggu verifikasi dan persetujuan dari Admin/HRD sekolah.'
        ]);
    }

    public function face_get_descriptors($postjson) {
        if (!isset($postjson)) exit();

        if (empty($postjson['id'])) {
            return json_encode(['status' => false, 'msg' => 'ID Pegawai tidak boleh kosong.']);
        }

        $q_face = $this->db->get_where('m_pegawai_face', ['pegawai_id' => $postjson['id']]);
        if ($q_face->num_rows() == 0) {
            return json_encode([
                'status' => false,
                'is_registered' => false,
                'is_verified' => false,
                'msg' => 'Wajah belum pernah didaftarkan. Silakan lakukan perekaman wajah biometrik terlebih dahulu.'
            ]);
        }

        $row = $q_face->row_array();
        if ($row['is_verified'] != 'y') {
            return json_encode([
                'status' => false,
                'is_registered' => true,
                'is_verified' => false,
                'msg' => 'Wajah Anda sudah didaftarkan tetapi masih MENUNGGU PERSETUJUAN dari Admin sekolah.'
            ]);
        }

        $base = base_url();
        return json_encode([
            'status' => true,
            'is_registered' => true,
            'is_verified' => true,
            'result' => [
                'descriptor_front' => json_decode($row['descriptor_front']),
                'descriptor_right' => !empty($row['descriptor_right']) ? json_decode($row['descriptor_right']) : null,
                'descriptor_left'  => !empty($row['descriptor_left'])  ? json_decode($row['descriptor_left'])  : null,
                'descriptor_up'    => !empty($row['descriptor_up'])    ? json_decode($row['descriptor_up'])    : null,
                'descriptor_down'  => !empty($row['descriptor_down'])  ? json_decode($row['descriptor_down'])  : null,
                'foto_sample'      => !empty($row['foto_sample']) ? $base . $row['foto_sample'] : ''
            ]
        ]);
    }

    public function face_status($postjson) {
        if (!isset($postjson)) exit();

        if (empty($postjson['id'])) {
            return json_encode(['status' => false, 'msg' => 'ID Pegawai tidak boleh kosong.']);
        }

        $q_face = $this->db->get_where('m_pegawai_face', ['pegawai_id' => $postjson['id']]);
        if ($q_face->num_rows() == 0) {
            return json_encode([
                'status' => true,
                'is_registered' => false,
                'is_verified' => false,
                'foto_sample' => '',
                'msg' => 'Belum rekam wajah.'
            ]);
        }

        $row = $q_face->row_array();
        $base = base_url();
        return json_encode([
            'status' => true,
            'is_registered' => true,
            'is_verified' => ($row['is_verified'] == 'y'),
            'foto_sample' => !empty($row['foto_sample']) ? $base . $row['foto_sample'] : '',
            'msg' => ($row['is_verified'] == 'y') ? 'Wajah aktif & terverifikasi.' : 'Menunggu verifikasi admin.'
        ]);
    }

}