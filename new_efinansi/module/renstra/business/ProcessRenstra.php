<?php
/**
* ================= doc ====================
* FILENAME     : ProcessRenstra.php
* @package     : ProcessRenstra
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2014-12-16
* @Modified    : 2014-12-16
* @Analysts    : Dyah Fajar
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/renstra/business/Renstra.class.php';

class ProcessRenstra
{
   # internal variables
   private $mObj;
   protected $mData  = array();
   private $method;
   public $urlList;
   public $urlInput;
   public $urlEdit;

   public $cssDone      = 'notebox-done';
   public $cssWarning   = 'notebox-warning';
   # Constructor
   function __construct ()
   {
      $this->mObj          = new Renstra();
      $this->method        = strtolower($_SERVER['REQUEST_METHOD']);
      $queryString         = $this->mObj->_getQueryString();
      $this->urlList       = Dispatcher::Instance()->GetUrl(
         'renstra',
         'Renstra',
         'view',
         'html'
      ).'&search=1&'.$queryString;
      $this->urlInput      = Dispatcher::Instance()->GetUrl(
         'renstra',
         'InputRenstra',
         'view',
         'html'
      ).'&'.$queryString;
      $this->urlEdit       = Dispatcher::Instance()->GetUrl(
         'renstra',
         'EditRenstra',
         'view',
         'html'
      ).'&'.$queryString;
   }

   private function setData()
   {
      $requestData      = array();
      if($this->method == 'post'){
         $startDate_day    = (int)$this->mObj->_POST['tanggal_awal_day'];
         $startDate_mon    = (int)$this->mObj->_POST['tanggal_awal_mon'];
         $startDate_year   = (int)$this->mObj->_POST['tanggal_awal_year'];
         $endDate_day      = (int)$this->mObj->_POST['tanggal_akhir_day'];
         $endDate_mon      = (int)$this->mObj->_POST['tanggal_akhir_mon'];
         $endDate_year     = (int)$this->mObj->_POST['tanggal_akhir_year'];

         $requestData['id']      = $this->mObj->_POST['data_id'];
         $requestData['nama']    = $this->mObj->_POST['nama'];
         $requestData['tanggal_awal']  = date('Y-m-d', mktime(0,0,0, $startDate_mon, $startDate_day, $startDate_year));
         $requestData['tanggal_akhir'] = date('Y-m-d', mktime(0,0,0, $endDate_mon, $endDate_day, $endDate_year));
         $requestData['pimpinan']      = trim($this->mObj->_POST['pimpinan']);
         $requestData['visi']          = trim($this->mObj->_POST['visi']);
         $requestData['misi']          = trim($this->mObj->_POST['misi']);
         $requestData['tujuan_umum']   = trim($this->mObj->_POST['tujuan_umum']);
         $requestData['tujuan_khusus'] = trim($this->mObj->_POST['tujuan_khusus']);
         $requestData['catatan']       = trim($this->mObj->_POST['catatan']);
         $requestData['sasaran']       = trim($this->mObj->_POST['sasaran']);
         $requestData['strategi']      = trim($this->mObj->_POST['strategi']);
         $requestData['kebijakan']     = trim($this->mObj->_POST['kebijakan']);
         $requestData['status']        = trim($this->mObj->_POST['status']);
         $requestData['check_start_date'] = checkdate($startDate_mon, $startDate_day, $startDate_year);
         $requestData['check_end_date']   = checkdate($endDate_mon, $endDate_day, $endDate_year);
      }

      $this->mData      = (array)$requestData;
   }

   public function getData()
   {
      if(empty($this->mData)){
         $this->setData();
      }

      return (array)$this->mData;
   }

   public function Save()
   {
      $this->setData();
      $requestData         = $this->mData;

      if($this->method == 'post'){
         if($requestData['nama'] == ''){
            $err[]            = 'Isikan nama '.GTFWConfiguration::GetValue('language', 'renstra');
         }

         if($requestData['check_start_date'] == false){
            $err[]            = 'Format tanggal tidak sesuai dd-mm-YYYY';
         }

         if($requestData['check_end_date'] == false){
            $err[]            = 'Format tanggal tidak sesuai dd-mm-YYYY';
         }
         $checkExists         = $this->mObj->CheckRenstra($requestData['nama']);
         if($checkExists === false){
            $err[]            = 'Data '.GTFWConfiguration::GetValue('language', 'renstra').' dengan nama '.$requestData['nama'].' Sudah tersimpan di dalam database';
         }

         if(isset($err)){
            $result['message']   = $err[0];
            $result['url']       = $this->urlInput;
            $result['style']     = $this->cssWarning;
            $result['redirect']  = false;
            $result['data']      = $this->mObj->_POST;
         }else{
            $process             = $this->mObj->DoAddRenstra($requestData);
            if($process === true){
               $urlList             = preg_replace('/\&end_year=[a-zA-Z0-9_-]+/', '&end_year='.date('Y', strtotime($requestData['tanggal_akhir'])), $this->urlList);
               $result['message']   = 'Proses penyimpanan data berhasil';
               $result['url']       = $urlList;
               $result['style']     = $this->cssDone;
               $result['redirect']  = true;
               $result['data']      = null;
            }else{
               $result['message']   = 'Proses penyimpanan data gagal';
               $result['url']       = $this->urlInput;
               $result['style']     = $this->cssWarning;
               $result['redirect']  = false;
               $result['data']      = $this->mObj->_POST;
            }
         }

         return (array)$result;
      }else{
         $result['message']   = 'Not allowed direct access';
         $result['url']       = $this->urlList;
         $result['style']     = $this->cssWarning;
         $result['redirect']  = true;
         $result['data']      = NULL;
      }

      return (array)$result;
   }

   public function Update()
   {
      $this->setData();
      $requestData         = $this->mData;

      if($this->method == 'post'){
         if($requestData['nama'] == ''){
            $err[]            = 'Isikan nama '.GTFWConfiguration::GetValue('language', 'renstra');
         }

         if($requestData['check_start_date'] == false){
            $err[]            = 'Format tanggal tidak sesuai dd-mm-YYYY';
         }

         if($requestData['check_end_date'] == false){
            $err[]            = 'Format tanggal tidak sesuai dd-mm-YYYY';
         }
         $checkExists         = $this->mObj->CheckRenstra($requestData['nama'], $requestData['id']);
         if($checkExists === false){
            $err[]            = 'Data '.GTFWConfiguration::GetValue('language', 'renstra').' dengan nama '.$requestData['nama'].' Sudah tersimpan di dalam database';
         }

         if(isset($err)){
            $result['message']   = $err[0];
            $result['url']       = $this->urlInput;
            $result['style']     = $this->cssWarning;
            $result['redirect']  = false;
            $result['data']      = $this->mObj->_POST;
         }else{
            $process             = $this->mObj->DoUpdateRenstra($requestData);
            if($process === true){
               $result['message']   = 'Proses update data berhasil';
               $result['url']       = $this->urlList;
               $result['style']     = $this->cssDone;
               $result['redirect']  = true;
               $result['data']      = null;
            }else{
               $result['message']   = 'Proses update data gagal';
               $result['url']       = $this->urlInput;
               $result['style']     = $this->cssWarning;
               $result['redirect']  = false;
               $result['data']      = $this->mObj->_POST;
            }
         }

         return (array)$result;
      }else{
         $result['message']   = 'Not allowed direct access';
         $result['url']       = $this->urlList;
         $result['style']     = $this->cssWarning;
         $result['redirect']  = true;
         $result['data']      = NULL;
      }

      return (array)$result;
   }

   public function Delete()
   {
      if($this->method == 'post'){
         $idDelete            = $this->mObj->_POST['idDelete'];
         if(!$idDelete){
            $result['message']   = 'Tidak ada data yang akan dihapus';
            $result['url']       = $this->urlList;
            $result['style']     = $this->cssWarning;
            $result['redirect']  = true;
            $result['data']      = NULL;
         }else{
            $process             = $this->mObj->DoDelete($idDelete);
            if($process === true){
               $result['message']   = 'Proses penghapusan data berhasil';
               $result['url']       = $this->urlList;
               $result['style']     = $this->cssDone;
               $result['redirect']  = true;
               $result['data']      = NULL;
            }else{
               $result['message']   = 'Proses penghapusan data gagal';
               $result['url']       = $this->urlList;
               $result['style']     = $this->cssDone;
               $result['redirect']  = true;
               $result['data']      = NULL;
            }
         }
      }else{
         $result['message']   = 'Not allowed direct access';
         $result['url']       = $this->urlList;
         $result['style']     = $this->cssWarning;
         $result['redirect']  = true;
         $result['data']      = NULL;
      }

      return (array)$result;
   }

   public function setActive()
   {
      if($this->method == 'post'){
         $dataId              = $this->mObj->_POST['id'];

         if(!$dataId){
            $result['message']   = 'Tidak ada data yang akan di set aktif';
            $result['url']       = $this->urlList;
            $result['style']     = $this->cssWarning;
            $result['redirect']  = true;
            $result['data']      = NULL;
         }else{
            $process             = $this->mObj->DoSetAktif($dataId);
            if($process === true){
               $result['message']   = 'Proses set aktif data berhasil';
               $result['url']       = $this->urlList;
               $result['style']     = $this->cssDone;
               $result['redirect']  = true;
               $result['data']      = NULL;
            }else{
               $result['message']   = 'Proses set aktif data gagal';
               $result['url']       = $this->urlList;
               $result['style']     = $this->cssDone;
               $result['redirect']  = true;
               $result['data']      = NULL;
            }
         }
      }else{
         $result['message']   = 'Not allowed direct access';
         $result['url']       = $this->urlList;
         $result['style']     = $this->cssWarning;
         $result['redirect']  = true;
         $result['data']      = NULL;
      }

      return (array)$result;
   }
}
?>