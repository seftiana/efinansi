<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot').
'module/realisasi_pencairan_2/business/spp.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') .
'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ProcessSpp{
   private $mObj;
   private $queryString    = '';
   protected $requestData  = array();
   public $cssDone         = "notebox-done";
   public $cssFail         = "notebox-warning";

   public $pageInput;
   public $pageView;

   public $decId;
   public $encId;

   function __construct(){
      $this->mObj          = new Spp();
      $this->queryString   = $this->mObj->_getQueryString();
      $this->pageInput     = Dispatcher::Instance()->GetUrl(
         'realisasi_pencairan_2',
         'AddSpp',
         'view',
         'html'
      );
      $this->pageView      = Dispatcher::Instance()->GetUrl(
         'realisasi_pencairan_2',
         'RealisasiPencairan',
         'view',
         'html'
      );

      $this->decId      = Dispatcher::Instance()->Decrypt($_GET['id']);
      $this->encId      = Dispatcher::Instance()->Encrypt($this->decId);
   }

   private function SetData()
   {
      $data             = $this->mObj->_POST;
      $requestData      = array();
      if(strtoupper($this->mObj->method) == 'POST'){
         $spkTanggalDay       = (int)$data['spk_tanggal_day'];
         $spkTanggalMon       = (int)$data['spk_tanggal_mon'];
         $spkTanggalYear      = (int)$data['spk_tanggal_year'];

         $requestData['id']               = $data['data_id'];
         $requestData['realisasi_id']     = $data['realisasi_id'];
         $requestData['ta_id']            = $data['ta_id'];
         $requestData['unit_id']          = $data['unit_id'];
         $requestData['sifat_pembayaran'] = $data['sifat_pembayaran'];
         $requestData['jenis_pembayaran'] = $data['jenis_pembayaran'];
         $requestData['keperluan']        = $data['keperluan'];
         $requestData['jenis_belanja']    = $data['jenis_belanja'];
         $requestData['nama']             = trim($data['nama']);
         $requestData['alamat']           = trim($data['alamat']);
         $requestData['rekening']         = trim($data['rekening']);
         $requestData['npwp']             = trim($data['npwp']);
         $requestData['spk_nomor']        = trim($data['nomor_spk']);
         $requestData['spk_nominal']      = $data['nominal_spk'];
         $requestData['spk_tanggal']      = date('Y-m-d', mktime(0,0,0, $spkTanggalMon, $spkTanggalDay, $spkTanggalYear));
         $requestData['check_date']       = checkdate($spkTanggalMon, $spkTanggalDay, $spkTanggalYear);
         $requestData['nominal']          = 0;
         $requestData['detail']           = array();
         if(!empty($data['data_spp'])){
            $index            = 0;
            foreach ($data['data_spp'] as $list) {
               $requestData['nominal']    += $list['nominal'];
               $requestData['detail'][$index]['id']      = $list['realisasi_id'];
               $requestData['detail'][$index]['nominal'] = $list['nominal'];
               $index++;
            }
         }
      }

      $this->requestData      = (array)$requestData;
   }

   public function GetData()
   {
      return $this->requestData;
   }

   private function CheckData()
   {
      $this->SetData();
      $requestData      = $this->GetData();

      if(empty($requestData)){
         $err[]         = GTFWConfiguration::GetValue('language', 'data_kosong');
      }
      if($requestData['sifat_pembayaran'] == ''){
         $err[]         = 'Pilih '.GTFWConfiguration::GetValue('language', 'sifat_pembayaran');
      }
      if($requestData['jenis_pembayaran'] == ''){
         $err[]         = 'Pilih '.GTFWConfiguration::GetValue('language', 'jenis_pembayaran');
      }
      if($requestData['keperluan'] == ''){
         $err[]         = 'Isikan keperluan/kepentingan pembuatan SPP';
      }
      if($requestData['jenis_belanja'] == ''){
         $err[]         = 'Isikan jenis belanja';
      }
      if($requestData['nama'] == ''){
         $err[]         = 'Isikan Nama SPP';
      }
      if(empty($requestData['detail'])){
         $err[]         = GTFWConfiguration::GetValue('language', 'data_kosong');
      }
      if($requestData['nominal'] <= 0){
         $err[]         = GTFWConfiguration::GetValue('language', 'data_kosong');
      }

      if(isset($err)){
         $return['message']      = $err[0];
         $return['return']       = false;
      }else{
         $return['message']      = null;
         $return['return']       = true;
      }

      return $return;
   }

   function check(){
      $keperluan     = trim($this->_POST['keperluan']);
      $jenis_belanja = trim($this->_POST['jenis_belanja']);
      $nama       = trim($this->_POST['nama']);
      $sifat_bayar   = $this->_POST['sifat_bayar'];
      $jenis_bayar   = $this->_POST['jenis_bayar'];
      if(isset($this->_POST['btnsimpan'])){
         if($keperluan == '' OR $jenis_belanja == '' OR $nama == ''
            OR $sifat_bayar == '' OR $jenis_bayar == ''){
            return 'emptyData';
         }else{
            return true;
         }
      }else{
         return $this->pageView;
      }
   }

   public function Add(){
      $this->SetData();
      $requestData   = $this->GetData();
      $checkData     = $this->CheckData();
      $urlInput      = Dispatcher::Instance()->GetUrl(
         'realisasi_pencairan_2',
         'AddSpp',
         'view',
         'html'
      ).'&'.$this->queryString;

      $urlReturn     = Dispatcher::Instance()->GetUrl(
         'realisasi_pencairan_2',
         'RealisasiPencairan',
         'view',
         'html'
      ).'&'.$this->queryString;

      if($checkData['return'] === false){
         Messenger::Instance()->Send(
            'realisasi_pencairan_2',
            'AddSpp',
            'view',
            'html',
            array(
               $this->mObj->_POST,
               $checkData['message'],
               'notebox-warning'
            ),
            Messenger::NextRequest
         );

         return $urlInput;
      }else{
         // proses insert data spp
         $process       = $this->mObj->DoInsertDataSpp($requestData);
         if($process === true){
            Messenger::Instance()->Send(
               'realisasi_pencairan_2',
               'RealisasiPencairan',
               'view',
               'html',
               array(
                  $this->mObj->_POST,
                  'Proses penyimpanan data berhasil',
                  'notebox-done'
               ),
               Messenger::NextRequest
            );

            return $urlReturn;
         }else{
            Messenger::Instance()->Send(
               'realisasi_pencairan_2',
               'AddSpp',
               'view',
               'html',
               array(
                  $this->mObj->_POST,
                  'Proses penyimpanan data gagal',
                  'notebox-warning'
               ),
               Messenger::NextRequest
            );

            return $urlInput;
         }
      }
   }

   public function Update(){
      $this->SetData();
      $requestData   = $this->GetData();
      $checkData     = $this->CheckData();
      $urlInput      = Dispatcher::Instance()->GetUrl(
         'realisasi_pencairan_2',
         'EditSpp',
         'view',
         'html'
      ).'&'.$this->queryString;

      $urlReturn     = Dispatcher::Instance()->GetUrl(
         'realisasi_pencairan_2',
         'RealisasiPencairan',
         'view',
         'html'
      ).'&'.$this->queryString;
      if($checkData['return'] === false){
         Messenger::Instance()->Send(
            'realisasi_pencairan_2',
            'EditSpp',
            'view',
            'html',
            array(
               $this->mObj->_POST,
               $checkData['message'],
               'notebox-warning'
            ),
            Messenger::NextRequest
         );

         return $urlInput;
      }else{
         // proses update data spp
         $process       = $this->mObj->DoUpdateDataSpp($requestData);
         if($process === true){
            Messenger::Instance()->Send(
               'realisasi_pencairan_2',
               'RealisasiPencairan',
               'view',
               'html',
               array(
                  $this->mObj->_POST,
                  'Proses update data berhasil',
                  'notebox-done'
               ),
               Messenger::NextRequest
            );

            return $urlReturn;
         }else{
            Messenger::Instance()->Send(
               'realisasi_pencairan_2',
               'EditSpp',
               'view',
               'html',
               array(
                  $this->mObj->_POST,
                  'Proses update data gagal',
                  'notebox-warning'
               ),
               Messenger::NextRequest
            );

            return $urlInput;
         }
      }
   }
}
?>
