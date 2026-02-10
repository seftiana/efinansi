<?php
/**
* ================= doc ====================
* FILENAME     : ProcessRencanaKinerjaTahunankegiatan.php
* @package     : ProcessRencanaKinerjaTahunankegiatan
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-02-06
* @Modified    : 2015-02-06
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/pegawai_lat/business/RencanaKinerjaTahunan.class.php';

class ProcessRencanaKinerjaTahunankegiatan
{
   # internal variables
   protected $mData = array();
   private $mObj;
   public $urlAdd;
   public $urlEdit;
   public $urlList;

   public $cssDone   = 'notebox-done';
   public $cssFail   = 'notebox-warning';
   # Constructor
   function __construct ()
   {
      $this->mObj       = new RencanaKinerjaTahunan();
      $queryString      = $this->mObj->_getQueryString();
      $this->urlAdd     = Dispatcher::Instance()->GetUrl(
         'pegawai_lat',
         'InputKinerjaTahunan',
         'view',
         'html'
      ).'&'.$queryString;
      $this->urlEdit    = Dispatcher::Instance()->GetUrl(
         'pegawai_lat',
         'EditKinerjaTahunan',
         'view',
         'html'
      ).'&'.$queryString;
      $this->urlList    = Dispatcher::Instance()->GetUrl(
         'pegawai_lat',
         'Angsuran',
         'view',
         'html'
      ).'&search=1&'.$queryString;
   }

   private function setData()
   {
      $requestData      = array();
      if($this->mObj->method == 'post'){
         $startDateMon           = (int)$this->mObj->_POST['start_date_mon'];
         $startDateYear          = (int)$this->mObj->_POST['start_date_year'];
         $endDateMon             = (int)$this->mObj->_POST['end_date_mon'];
         $endDateYear            = (int)$this->mObj->_POST['end_date_year'];

         $requestData['id']            = $this->mObj->_POST['data_id'];
         $requestData['keg_id']        = $this->mObj->_POST['kegiatan_id'];
         $requestData['ta_id']         = $this->mObj->_POST['tahun_anggaran'];
         $requestData['unit_id']       = $this->mObj->_POST['unit_id'];
         $requestData['unit_nama']     = $this->mObj->_POST['unit_nama'];
         $requestData['program_id']    = $this->mObj->_POST['program'];
         $requestData['program_nama']  = $this->mObj->_POST['program_nama'];
         $requestData['kegiatan_id']   = $this->mObj->_POST['kegiatan'];
         $requestData['kegiatan_nama'] = $this->mObj->_POST['kegiatan_nama'];
         $requestData['sub_kegiatan_id']     = $this->mObj->_POST['sub_kegiatan'];
         $requestData['sub_kegiatan_nama']   = $this->mObj->_POST['sub_kegiatan_nama'];
         $requestData['latar_belakang']      = $this->mObj->_POST['latar_belakang'];
         $requestData['indikator']           = $this->mObj->_POST['indikator'];
         $requestData['baseline']            = $this->mObj->_POST['baseline'];
         $requestData['final']               = $this->mObj->_POST['final'];
         $requestData['ikk_id']              = $this->mObj->_POST['ikk_id'];
         $requestData['ikk_nama']            = $this->mObj->_POST['ikk'];
         $requestData['iku_id']              = $this->mObj->_POST['iku_id'];
         $requestData['iku_nama']            = $this->mObj->_POST['iku'];
         $requestData['tupoksi_id']          = $this->mObj->_POST['tupoksi_id'];
         $requestData['tupoksi_nama']        = $this->mObj->_POST['tupoksi'];
         $requestData['deskripsi']           = $this->mObj->_POST['deskripsi'];
         $requestData['catatan']             = $this->mObj->_POST['catatan'];
         $requestData['prioritas']           = $this->mObj->_POST['prioritas'];
         $requestData['start_date']          = date('Y-m-d', mktime(0,0,0, $startDateMon, 1, $startDateYear));
         $requestData['end_date']            = date('Y-m-t', mktime(0,0,0, $endDateMon, 1, $endDateYear));
         $requestData['mastuk']              = $this->mObj->_POST['mastuk'];
         $requestData['mastk']               = $this->mObj->_POST['mastk'];
         $requestData['keltuk']              = $this->mObj->_POST['keltuk'];
         $requestData['keltk']               = $this->mObj->_POST['keltk'];
         $requestData['nama_pic']            = $this->mObj->_POST['nama_pic'];
      }

      $this->mData      = $requestData;
   }

   public function getData()
   {
      $this->setData();
      return (array)$this->mData;
   }

   private function checkData()
   {
      if($this->mObj->method == 'post'){
         $requestData         = $this->mData;
         if(empty($requestData)){
            $err[]      = 'Tidak ada data yang akan di proses';
         }

         if($requestData['ta_id'] == ''){
            $err[]      = 'Silahkan pilih tahun anggaran';
         }

         if($requestData['unit_id'] == ''){
            $err[]      = 'Definisikan unit kerja';
         }

         if($requestData['program_id'] == ''){
            $err[]      = 'Definisikan '.GTFWConfiguration::GetValue('language', 'program');
         }

         if($requestData['kegiatan_id'] == ''){
            $err[]      = 'Definisikan '.GTFWConfiguration::GetValue('language', 'kegiatan');
         }

         if($requestData['sub_kegiatan_id'] == ''){
            $err[]      = 'Definisikan '.GTFWConfiguration::GetValue('language', 'sub_kegiatan');
         }

         // if($requestData['latar_belakang'] == ''){
            // $err[]      = 'Isikan latar belakang';
         // }

         // if($requestData['prioritas'] == ''){
            // $err[]      = 'Pilih prioritas';
         // }

         if(isset($err)){
            $return['result']    = false;
            $return['message']   = $err[0];
         }else{
            $return['result']    = true;
            $return['message']   = NULL;
         }
      }else{
         $return['result']    = false;
         $return['message']   = 'Not allowed';
      }

      return $return;
   }

   public function Save()
   {
      $this->setData();
      $check               = $this->checkData();
      if($check['result'] === false){
         $return['url']       = $this->urlAdd;
         $return['message']   = $check['message'];
         $return['data']      = $this->mObj->_POST;
         $return['style']     = $this->cssFail;
      }else{
         $taAktif    = $this->mObj->GetTahunAnggaranInput();
         foreach ($taAktif as $ta) {
            $taBuka  = $ta['tabuka'];
            $taTutup = $ta['tatutup'];
         }

         $taAwal  = $this->mObj->_dateToIndo(date('Y-m-d', strtotime($taBuka)));
         $taAkhir = $this->mObj->_dateToIndo(date('Y-m-d', strtotime($taTutup)));

         if($this->mData['start_date'] >= $taBuka && $this->mData['end_date'] <= $taTutup && $this->mData['start_date'] < $this->mData['end_date']){
            $process             = $this->mObj->doSaveKegiatan($this->mData);
            if($process === true){
               $return['url']       = $this->urlList;
               $return['message']   = 'Proses penyimpanan data berhasil';
               $return['data']      = NULL;
               $return['style']     = $this->cssDone;
            }else{
               $return['url']       = $this->urlAdd;
               $return['message']   = 'Proses penyimpanan data gagal';
               $return['data']      = $this->mObj->_POST;
               $return['style']     = $this->cssFail;
            }
            }else{
               $return['url']       = $this->urlAdd;
               $return['message']   = 'Bulan Anggaran tidak berada pada periode aktif '.$taAwal.' - '.$taAkhir;
               $return['data']      = $this->mObj->_POST;
               $return['style']     = $this->cssFail;
         }
      }

      return (array)$return;
   }

   public function Update()
   {
      $this->setData();
      $check               = $this->checkData();
      if($check['result'] === false){
         $return['url']       = $this->urlEdit;
         $return['message']   = $check['message'];
         $return['data']      = $this->mObj->_POST;
         $return['style']     = $this->cssFail;
      }else{
         $taAktif    = $this->mObj->GetTahunAnggaranInput();
         foreach ($taAktif as $ta) {
            $taBuka  = $ta['tabuka'];
            $taTutup = $ta['tatutup'];
         }

         $taAwal  = $this->mObj->_dateToIndo(date('Y-m-d', strtotime($taBuka)));
         $taAkhir = $this->mObj->_dateToIndo(date('Y-m-d', strtotime($taTutup)));

         if($this->mData['start_date'] >= $taBuka && $this->mData['end_date'] <= $taTutup && $this->mData['start_date'] < $this->mData['end_date']){
         $process             = $this->mObj->doUpdateKegiatan($this->mData);
         if($process === true){
            $return['url']       = $this->urlList;
            $return['message']   = 'Proses Update data berhasil';
            $return['data']      = NULL;
            $return['style']     = $this->cssDone;
         }else{
            $return['url']       = $this->urlEdit;
            $return['message']   = 'Proses Update data gagal';
            $return['data']      = $this->mObj->_POST;
            $return['style']     = $this->cssFail;
         }
            }else{
               $return['url']       = $this->urlAdd;
               $return['message']   = 'Bulan Anggaran tidak berada pada periode aktif '.$taAwal.' - '.$taAkhir;
               $return['data']      = $this->mObj->_POST;
               $return['style']     = $this->cssFail;
         }
      }

      return (array)$return;
   }

   public function Delete()
   {
      $return['url']       = $this->urlList;
      $return['data']      = NULL;
      if($this->mObj->method == 'post'){
         $idDelete         = $this->mObj->_POST['idDelete'];
         $sukses           = 0;
         $failed           = 0;
         for ($i=0; $i < count($idDelete); $i++) {
            list($id, $kegiatan_id)    = explode('|', $idDelete[$i]);
            $process       = $this->mObj->doDeleteKegiatan($id, $kegiatan_id);

            if($process === true){
               $sukses+=1;
            }else{
               $failed+=1;
            }
         }

         if((int)$sukses === 0){
            $message    = 'Penghapusan Data gagal Dilakukan';
         }elseif((int)$failed === 0){
            $message    = 'Penghapusan Data Berhasil Dilakukan';
         }else{
            $message    = $sukses .' Data berhasil dihapus. '.$failed. ' Gagal di hapus';
         }

         $return['message']   = $message;
         $return['style']     = ((int)$sukses === 0) ? $this->cssFail : $this->cssDone;
      }else{
         $return['message']   = 'Not allowed';
         $return['style']     = $this->cssFail;
      }

      return (array)$return;
   }
}
?>