<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/realisasi_pencairan_2/business/RealisasiPencairan.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ProcessRealisasiPencairan
{
   protected $msg;
   protected $data;
   protected $_POST;
   protected $method;
   protected $mData        = array();
   protected $moduleName   = 'realisasi_pencairan_2';
   protected $inputModule  = 'inputRealisasiPencairan';
   protected $homeModule   = 'realisasiPencairan';

   public $urlReturn;
   public $urlHome;

   public $cssDone         = 'notebox-done';
   public $cssFail         = 'notebox-warning';

   public $RealisasiPencairan;
   public $UserUnitKerja;
   public $queryString     = '';
   public $maxFileSize;
   public $fileTypeAllowed;
   public $typeAccepted;
   public $uploadDir;

   function __construct()
   {
      //constructor
      $this->UserUnitKerja          = new UserUnitKerja;
      $this->RealisasiPencairan     = new RealisasiPencairan;
      $this->method                 = $_SERVER['REQUEST_METHOD'];
      $this->queryString            = $this->RealisasiPencairan->_getQueryString();

      $this->maxFileSize            = 8 * 1024 * 1024;
      $this->fileTypeAllowed        = array(
         "application/x-gzip",
         "application/x-rar",
         "application/zip",
         "application/rtf",
         "application/msword",
         "application/wps-office.doc",
         "application/wps-office.docx",
         "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
         "application/vnd.ms-excel",
         "application/wps-office.xls",
         "application/wps-office.xlsx",
         "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
         "application/pdf",
         "image/jpeg",
         "image/pjpeg",
         "image/jpeg",
         "image/pjpeg",
         "image/png"
      );
      $this->typeAccepted  = array('doc', 'docx', 'xls', 'xlsx', 'pdf', 'rar', 'zip', 'rtf', 'jpg', 'jpeg', 'png');
      $this->uploadDir     = realpath(GTFWConfiguration::GetValue('application', 'docroot') . "document/fpa/");

      $this->urlReturn              =  Dispatcher::Instance()->GetUrl(
         'realisasi_pencairan_2',
         'inputRealisasiPencairan',
         'view',
         'html'
      ).'&'.$this->queryString;

      $this->urlHome      = Dispatcher::Instance()->GetUrl(
         'realisasi_pencairan_2',
         'realisasiPencairan',
         'view',
         'html'
      ).'&search=1&'.$this->queryString;

      if(is_object($_POST)){
         $this->_POST    = $_POST->AsArray();
      }else{
         $this->_POST    = $_POST;
      }
      if(isset($_POST['data'])){
         if(is_object($_POST['data'])){
            $this->data = $_POST['data']->AsArray();
         }else{
            $this->data = $_POST['data'];
         }
      }
   }

   public function SetData()
   {
      if(strtolower($this->method) === 'post'){
         if(isset($this->_POST['btnsimpan'])){
            $tanggalDay             = (int)$this->RealisasiPencairan->_POST['tanggal_day'];
            $tanggalMon             = (int)$this->RealisasiPencairan->_POST['tanggal_mon'];
            $tanggalYear            = (int)$this->RealisasiPencairan->_POST['tanggal_year'];

            $this->mData['id']      = $this->RealisasiPencairan->_POST['data']['id'];
            $this->mData['action']  = $this->RealisasiPencairan->_POST['data']['action'];
            $this->mData['kegiatanUnitId']   = $this->RealisasiPencairan->_POST['data']['kegiatanunit_id'];
            $this->mData['kegiatanDetailId'] = $this->RealisasiPencairan->_POST['data']['kegiatandetail_id'];
            $this->mData['taId']             = $this->RealisasiPencairan->_POST['data']['ta_id'];
            $this->mData['taNama']           = $this->RealisasiPencairan->_POST['data']['ta_nama'];
            $this->mData['unitNama']         = $this->RealisasiPencairan->_POST['data']['unit_nama'];
            $this->mData['unitId']           = $this->RealisasiPencairan->_POST['data']['unit_id'];
            $this->mData['programId']        = $this->RealisasiPencairan->_POST['data']['program_id'];
            $this->mData['programNama']      = $this->RealisasiPencairan->_POST['data']['program_nama_hidden'];
            $this->mData['kegiatanId']       = $this->RealisasiPencairan->_POST['data']['kegiatan_id'];
            $this->mData['kegiatanNama']     = $this->RealisasiPencairan->_POST['data']['kegiatan_nama_hidden'];
            $this->mData['subKegiatanId']    = $this->RealisasiPencairan->_POST['data']['subkegiatan_id'];
            $this->mData['subKegiatanNama']  = $this->RealisasiPencairan->_POST['data']['subkegiatan_nama'];
            $this->mData['keterangan']       = trim($this->RealisasiPencairan->_POST['data']['keterangan']);
            $this->mData['nomorPengajuan']   = trim($this->RealisasiPencairan->_POST['data']['nomor_pengajuan']);
            $this->mData['totalAnggaran']    = trim($this->RealisasiPencairan->_POST['data']['total_anggaran']);
            $this->mData['nominalRealisasi'] = trim($this->RealisasiPencairan->_POST['data']['realisasi_nominal']);
            $this->mData['realisasiPencairan']  = $this->RealisasiPencairan->_POST['data']['realisasi_pencairan'];
            $this->mData['nominal']          = $this->RealisasiPencairan->_POST['data']['nominal'];
            $this->mData['tanggal']          = date('Y-m-d', mktime(0,0,0, $tanggalMon, $tanggalDay, $tanggalYear));
            $this->mData['tanggal_old']      = date('Y-m-d', strtotime($this->RealisasiPencairan->_POST['data']['tanggal_old']));
            $this->mData['checkDate']        = checkdate($tanggalMon, $tanggalDay, $tanggalYear);
            $this->mData['komponen']         = array();

            $this->mData['file_name']        = $_FILES['fpa_file']['name'];
            $this->mData['file_tmp']         = $_FILES['fpa_file']['tmp_name'];
            $this->mData['file_size']        = $_FILES['fpa_file']['size'];
            $this->mData['file_type']        = $_FILES['fpa_file']['type'];

            if(!empty($this->RealisasiPencairan->_POST['KOMP'])){
               $index            = 0;
               foreach ($this->RealisasiPencairan->_POST['KOMP'] as $komponen) {
                  $this->mData['komponen'][$index]['id']      = $komponen['rp_id'];
                  $this->mData['komponen'][$index]['kode']    = $komponen['kodeKomponen'];
                  $this->mData['komponen'][$index]['nama']    = $komponen['namaKomponen'];
                  $this->mData['komponen'][$index]['makId']   = $komponen['makId'];
                  $this->mData['komponen'][$index]['makKode'] = $komponen['makKode'];
                  $this->mData['komponen'][$index]['deskripsi'] = $komponen['deskripsi'];
                  $this->mData['komponen'][$index]['nominal'] = $komponen['nominal_budget'];
                  $this->mData['komponen'][$index]['nominal_available'] = $komponen['nominal_available'];
                  $this->mData['komponen'][$index]['nominal_sisa']      = $komponen['nominal'];
                  $index++;
               }
            }
         }
      }
   }

   public function Check()
   {
      if(strtolower($this->method) === 'post'){
         if($this->_POST['btnsimpan']){
            $this->SetData();
            $dataList      = $this->mData;
            if($dataList['taId'] == ''){
               $err[]      = 'Definisikan Tahun Periode';
            }
            if($dataList['unitId'] == ''){
               $err[]      = 'Definisikan Unit Kerja';
            }
            if($dataList['subKegiatanId'] == ''){
               $err[]      = 'Definisikan Program, Sub Program dan Kegiatan';
            }
            if($dataList['checkDate'] === false){
               $err[]      = 'Definisikan tanggal pengajuan dengan benar';
            }
            
            //if($dataList['nomorPengajuan'] == ''){
            //   $err[]      = 'Isikan nominal pengajuan';
            //}
            
             if($dataList['keterangan'] == ''){
               $err[]   = 'Isikan keterangan';
            }
            if(empty($dataList['komponen'])){
               $err[]   = 'Definisikan Komponen index';
            }
            if($dataList['nominal'] == 0){
               $err[]   = 'Isikan Nominal Pengajuan';
            }

            if(empty($dataList['komponen'])){
               $err[]   = 'Tidak ada data detail belanja yang akan di simpan';
            }
            
            $danaSisa = $this->RealisasiPencairan->GetDanaSisaFpa($dataList['id'] , $dataList['kegiatanDetailId']);
           
            if($dataList['nominal'] > $danaSisa){
               $err[]   = 'Realisasi Melebihi Dana Sisa. Dana sisa Rp.'.number_format($danaSisa,0,',','.');
            }

            // Attachment
            if(!empty($dataList['file_tmp']) || $dataList['file_tmp'] != ""){
               if($dataList['file_size'] > 0 && $dataList['file_size'] > $this->maxFileSize){
                  $err[]   = 'Ukuran file yang Anda upload terlalu besar. Max file upload 10MB';
               }
         
               if(!in_array($dataList['file_type'], $this->fileTypeAllowed)){
                  $err[]   = 'File yang bisa di upload adalah : '. implode(',', $this->typeAccepted);
               }
         
               if(!is_writable($this->uploadDir)){
                  $err[]   = 'Cek directory tujuan upload. Pastikan directory tujuan upload writable dan readable';
               }
               // end - Attachment
            }

            if(isset($err)){
               $result['return']    = false;
               $result['message']   = $err[0];
            }else{
               $result['return']    = true;
               $result['message']   = NULL;
            }

            return (array)$result;
         }else{
            return false;
         }
      }else{
         return false;
      }
   }

   function Add()
   {
      $check         = $this->Check();
      if($check === false){
         return $this->urlHome;
      }

      if($check['return'] === true) {
         $process       = $this->RealisasiPencairan->DoAddRealisasiPencairan($this->mData);
         
         if($process === true) {
            $getMaxId   = $this->RealisasiPencairan->GetMaxId();
            $dataId     = $getMaxId != NULL ? $getMaxId['max_id'] : 0;

            if(!empty($this->mData['file_tmp']) || $this->mData['file_tmp'] != ""){
               // Upload File
               foreach (glob($this->uploadDir.'/FPA_'.$dataId.'_*') as $filename) {
                  unlink($filename);
               }
               $timestamp  = date('YmdHis', time());
               $ext        = pathinfo($this->mData['file_name'], PATHINFO_EXTENSION);
               $newFile    = 'FPA_' . $dataId . '_' . $timestamp .'.' . $ext;

               if(move_uploaded_file($this->mData['file_tmp'], $this->uploadDir.'/'.$newFile)) {
                  $processUpload    = $this->RealisasiPencairan->DoUpdateFile($newFile, $dataId);
               } else {
                  $processUpload    = false;
               }
               // end - Upload File
            }else{
               $processUpload = true;
            }
         }

         // $process    = false;
         if($process === true && $processUpload === true) {
            Messenger::Instance()->Send(
               'realisasi_pencairan_2',
               'realisasiPencairan',
               'view',
               'html',
               array(
                  NULL,
                  'Penambahan data berhasil dilakukan',
                  $this->cssDone
               ),
               Messenger::NextRequest
            );

            return $this->urlHome;
         } elseif($process === true && $processUpload === false) {
            Messenger::Instance()->Send(
               'realisasi_pencairan_2',
               'realisasiPencairan',
               'view',
               'html',
               array(
                  NULL,
                  'Penambahan data berhasil dilakukan<br/> Proses upload file gagal dilakukan',
                  $this->cssDone
               ),
               Messenger::NextRequest
            );

            return $this->urlHome;
         } else {
            Messenger::Instance()->Send(
               'realisasi_pencairan_2',
               'inputRealisasiPencairan',
               'view',
               'html',
               array(
                  $this->RealisasiPencairan->_POST,
                  'Proses penambahan data gagal',
                  $this->cssFail
               ),
               Messenger::NextRequest
            );
            return $this->urlReturn;
         }

      }else{
         Messenger::Instance()->Send(
            'realisasi_pencairan_2',
            'inputRealisasiPencairan',
            'view',
            'html',
            array(
               $this->RealisasiPencairan->_POST,
               $check['message'],
               $this->cssFail
            ),
            Messenger::NextRequest
         );

         return $this->urlReturn;
      }
   }

   function Delete ()
   {
      if(isset($this->RealisasiPencairan->_POST['idDelete'])) {
         $process          = $this->RealisasiPencairan->DoDeleteRealisasiPencairan($this->RealisasiPencairan->_POST['idDelete']);
         
         // foreach (glob($this->uploadDir.'/FPA_'.$this->RealisasiPencairan->_POST['idDelete'].'_*') as $filename) {
         //    unlink($filename);
         // }

         if($process === true) {
            Messenger::Instance()->Send(
               'realisasi_pencairan_2',
               'realisasiPencairan',
               'view',
               'html',
               array(
                  NULL,
                  'Penghapusan data berhasil dilakukan',
                  $this->cssDone
               ),
               Messenger::NextRequest
            );
         } else {
            Messenger::Instance()->Send(
               'realisasi_pencairan_2',
               'realisasiPencairan',
               'view',
               'html',
               array(
                  NULL,
                  'Proses penghapusan data gagal',
                  $this->cssFail
               ),
               Messenger::NextRequest
            );
         }
      }

      return $this->urlHome;
   }

   function Update()
   {
      $check         = $this->Check();
      if($check === false){
         return $this->urlHome;
      }

      if($check['return'] === true){
         $process       = $this->RealisasiPencairan->DoUpdateRealisasiPencairan($this->mData);
         
         if($process === true) {
            $dataId     = $this->mData['id'];
            
            if(!empty($this->mData['file_tmp']) || $this->mData['file_tmp'] != ""){
               // Upload File
               foreach (glob($this->uploadDir.'/FPA_'.$dataId.'_*') as $filename) {
                  unlink($filename);
               }
               $timestamp  = date('YmdHis', time());
               $ext        = pathinfo($this->mData['file_name'], PATHINFO_EXTENSION);
               $newFile    = 'FPA_' . $dataId . '_' . $timestamp .'.' . $ext;

               if(move_uploaded_file($this->mData['file_tmp'], $this->uploadDir.'/'.$newFile)) {
                  $processUpload    = $this->RealisasiPencairan->DoUpdateFile($newFile, $dataId);
               } else {
                  $processUpload    = false;
               }
            }else{
               $processUpload = true;
            }
            // end - Upload File
         }

         if($process === true && $processUpload === true) {
            Messenger::Instance()->Send(
               'realisasi_pencairan_2',
               'realisasiPencairan',
               'view',
               'html',
               array(
                  NULL,
                  'Perubahan data berhasil dilakukan',
                  $this->cssDone
               ),
               Messenger::NextRequest
            );

            return $this->urlHome;

         } elseif($process === true && $processUpload === false) {
            Messenger::Instance()->Send(
               'realisasi_pencairan_2',
               'realisasiPencairan',
               'view',
               'html',
               array(
                  NULL,
                  'Perubahan data berhasil dilakukan<br/> Proses upload file gagal',
                  $this->cssDone
               ),
               Messenger::NextRequest
            );

            return $this->urlHome;
         } else {
            Messenger::Instance()->Send(
               'realisasi_pencairan_2',
               'inputRealisasiPencairan',
               'view',
               'html',
               array(
                  $this->RealisasiPencairan->_POST,
                  'Proses perubahan data gagal',
                  $this->cssFail
               ),
               Messenger::NextRequest
            );
            return $this->urlReturn;
         }

      }else{
         Messenger::Instance()->Send(
            'realisasi_pencairan_2',
            'inputRealisasiPencairan',
            'view',
            'html',
            array(
               $this->RealisasiPencairan->_POST,
               $check['message'],
               $this->cssFail
            ),
            Messenger::NextRequest
         );

         return $this->urlReturn;
      }
   }
}
?>