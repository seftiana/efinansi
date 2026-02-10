<?php
/**
* ================= doc ====================
* FILENAME     : ProcessPeriodeTahun.php
* @package     : ProcessPeriodeTahun
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-02-09
* @Modified    : 2015-02-09
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/periode_tahun/business/PeriodeTahun.class.php';

class ProcessPeriodeTahun
{
   # internal variables
   private $mObj;
   protected $mUserId;
   protected $mData  = null;
   public $urlList;
   public $urlAdd;
   public $urlEdit;

   public $cssDone      = 'notebox-done';
   public $cssFail      = 'notebox-warning';
   # Constructor
   function __construct ()
   {
      $this->mObj       = new PeriodeTahun();
      $queryString      = $this->mObj->_getQueryString();
      $this->urlList    = Dispatcher::Instance()->GetUrl(
         'periode_tahun',
         'PeriodeTahun',
         'view',
         'html'
      ).'&search=1&'.$queryString;
      $this->urlAdd     = Dispatcher::Instance()->GetUrl(
         'periode_tahun',
         'InputPeriodeTahun',
         'view',
         'html'
      ).'&'.$queryString;
      $this->urlEdit    = Dispatcher::Instance()->GetUrl(
         'periode_tahun',
         'EditPeriodeTahun',
         'view',
         'html'
      ).'&'.$queryString;
   }

   private function setData()
   {
      $requestData      = array();
      if($this->mObj->method == 'post'){
         $startDate_day       = (int)$this->mObj->_POST['tanggal_awal_day'];
         $startDate_mon       = (int)$this->mObj->_POST['tanggal_awal_mon'];
         $startDate_year      = (int)$this->mObj->_POST['tanggal_awal_year'];
         $endDate_day         = (int)$this->mObj->_POST['tanggal_akhir_day'];
         $endDate_mon         = (int)$this->mObj->_POST['tanggal_akhir_mon'];
         $endDate_year        = (int)$this->mObj->_POST['tanggal_akhir_year'];

         $requestData['id']               = $this->mObj->_POST['data_id'];
         $requestData['renstra_id']       = $this->mObj->_POST['renstra_id'];
         $requestData['nama']             = trim($this->mObj->_POST['nama']);
         $requestData['status_aktif']     = strtoupper($this->mObj->_POST['status_aktif']);
         $requestData['status_open']      = strtoupper($this->mObj->_POST['status_open']);
         $requestData['start_date']       = date('Y-m-d', mktime(0,0,0, $startDate_mon, $startDate_day, $startDate_year));
         $requestData['end_date']         = date('Y-m-d', mktime(0,0,0, $endDate_mon, $endDate_day, $endDate_year));
         $requestData['check_start_date'] = checkdate($startDate_mon, $startDate_day, $startDate_year);
         $requestData['check_end_date']   = checkdate($endDate_mon, $endDate_day, $endDate_year);
      }

      $this->mData      = (array)$requestData;
   }

   public function getData()
   {
      $this->setData();
      return (array)$this->mData;
   }

   private function checkData()
   {
      $requestData         = $this->mData;

      if(empty($requestData)){
         $return['result']    = false;
         $return['message']   = 'Tidak ada data yang akan diproses';

         return $return;
      }else{
         $checkPeriodeTahun   = $this->mObj->doCheckData(
            $requestData['renstra_id'],
            $requestData['nama'],
            $requestData['id']
         );
         if($requestData['renstra_id'] == ''){
            $err[]      = 'Definisikan renstra.';
         }
         if($requestData['nama'] == ''){
            $err[]      = 'Isikan nama periode tahun';
         }
         if($requestData['check_start_date'] === false){
            $err[]      = 'Definsikan tanggal dengan benar';
         }
         if($requestData['check_end_date'] === false){
            $err[]      = 'Definsikan tanggal dengan benar';
         }
         if($checkPeriodeTahun === false){
            $err[]      = 'Data periode tahun dengan nama '.$requestData['nama'].' Sudah terdaftar di dalam sistem';
         }

         if(isset($err)){
            $return['message']   = $err[0];
            $return['result']    = false;
         }else{
            $return['message']   = NULL;
            $return['result']    = true;
         }

         return $return;
      }
   }

   public function Save()
   {
      $this->setData();
      $checkData           = $this->checkData();
      if($checkData['result'] === false){
         $return['url']       = $this->urlAdd;
         $return['message']   = $checkData['message'];
         $return['data']      = $this->mObj->_POST;
         $return['style']     = $this->cssFail;
      }else{
         $process             = $this->mObj->doSaveData($this->mData);
         if($process === true){
            $return['url']       = $this->urlList;
            $return['message']   = 'Proses Penyimpanan data berhasil';
            $return['data']      = NULL;
            $return['style']     = $this->cssDone;
         }else{
            $return['url']       = $this->urlAdd;
            $return['message']   = 'Proses Penyimpanan data gagal';
            $return['data']      = $this->mObj->_POST;
            $return['style']     = $this->cssFail;
         }
      }

      return $return;
   }

   public function Update()
   {
      $this->setData();
      $checkData           = $this->checkData();
      if($checkData['result'] === false){
         $return['url']       = $this->urlEdit;
         $return['message']   = $checkData['message'];
         $return['data']      = $this->mObj->_POST;
         $return['style']     = $this->cssFail;
      }else{
         $process             = $this->mObj->doUpdateData($this->mData);
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
      }

      return $return;
   }

   public function Delete()
   {
      $return['url']       = $this->urlList;
      $return['data']      = NULL;

      if($this->mObj->method != 'post'){
         $return['message']   = 'You don\'t have permission';
         $return['style']     = $this->cssFail;
      }else{
         $idDelete         = $this->mObj->_POST['idDelete'];
         $process          = $this->mObj->doDeleteData($idDelete);
         if($process === true){
            $return['message']   = 'Proses penghapusan data berhasil';
            $return['style']     = $this->cssDone;
         }else{
            $return['message']   = 'Proses penghapusan data gagal';
            $return['style']     = $this->cssFail;
         }
      }

      return $return;
   }

   public function SetAktif()
   {
      $return['url']       = $this->urlList;
      $return['data']      = NULL;

      if($this->mObj->method != 'post'){
         $return['message']   = 'You don\'t have permission';
         $return['style']     = $this->cssFail;
      }else{
         $id               = $this->mObj->_POST['id'];
         $process          = $this->mObj->doSetActive($id);
         if($process === true){
            $return['message']   = 'Proses set aktif data berhasil';
            $return['style']     = $this->cssDone;
         }else{
            $return['message']   = 'Proses set aktif data gagal';
            $return['style']     = $this->cssFail;
         }
      }

      return $return;
   }
}
?>