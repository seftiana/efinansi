<?php
/**
* ================= doc ====================
* FILENAME     : ProcessFinansiDipa.php
* @package     : ProcessFinansiDipa
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2014-12-10
* @Modified    : 2014-12-10
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/finansi_dipa/business/FinansiDipa.class.php';

class ProcessFinansiDipa
{
   public $urlList;
   public $urlAdd;
   public $urlUpdate;
   public $cssDone      = 'notebox-done';
   public $cssFail      = 'notebox-warning';
   public $queryString  = '';
   private $mObj;
   protected $method;
   protected $request   = array();
   function __construct ()
   {
      $this->mObj          = new FinansiDipa();
      $this->queryString   = $this->mObj->_getQueryString();
      $this->method        = strtolower($_SERVER['REQUEST_METHOD']);
      $this->urlList       = Dispatcher::Instance()->GetUrl(
         'finansi_dipa',
         'FinansiDipa',
         'view',
         'html'
      ).'&search=1&'.$this->queryString;
      $this->urlAdd        = Dispatcher::Instance()->GetUrl(
         'finansi_dipa',
         'AddDipa',
         'view',
         'html'
      ).'&'.$this->queryString;
      $this->urlUpdate     = Dispatcher::Instance()->GetUrl(
         'finansi_dipa',
         'EditDipa',
         'view',
         'html'
      ).'&'.$this->queryString;
   }

   private function setData()
   {
      $requestData      = array();

      if($this->method == 'post'){
         $mDay          = (int)$this->mObj->_POST['tanggal_day'];
         $mMon          = (int)$this->mObj->_POST['tanggal_mon'];
         $mYear         = (int)$this->mObj->_POST['tanggal_year'];
         $requestData['id']      = $this->mObj->_POST['data_id'];
         $requestData['kode']    = trim($this->mObj->_POST['kode']);
         $requestData['tanggal'] = date('Y-m-d', mktime(0,0,0, $mMon, $mDay, $mYear));
         $requestData['nominal'] = $this->mObj->_POST['nominal'];
         $requestData['status']  = strtoupper($this->mObj->_POST['status']);
      }

      $this->request    = $requestData;
   }

   public function getData()
   {
      if(empty($this->request)){
         $this->setData();
      }

      return $this->request;
   }

   public function Save()
   {
      if($this->method == 'post'){
         $this->setData();
         $requestData         = $this->request;
         // check input data
         if(empty($requestData)){
            $err[]      = 'Tidak ada data yang akan disimpan';
         }
         if($requestData['kode'] == ''){
            $err[]      = 'Isikan Nomor DIPA';
         }
         if($requestData['nominal'] <= 0){
            $err[]      = 'Isikan nominal DIPA yang sesuai';
         }
         // check duplicate data
         $duplicate     = $this->mObj->CheckDuplicate($requestData['kode']);
         if($duplicate === true){
            $err[]      = 'DIPA dengan nomor "'.$requestData['kode'].'" Sudah terdaftar di dalam sistem';
         }

         if(isset($err)){
            $return['message']   = $err[0];
            $return['style']     = $this->cssFail;
            $return['url']       = $this->urlAdd;
            $return['data']      = $this->mObj->_POST;
            $return['return']    = false;
         }else{
            $process             = $this->mObj->DoSaveDipa($requestData);
            if($process === true){
               $return['message']   = 'Proses penyimpanan data berhasil';
               $return['style']     = $this->cssDone;
               $return['url']       = $this->urlList;
               $return['data']      = null;
               $return['return']    = true;
            }else{
               $return['message']   = 'Proses penyimpanan data gagal';
               $return['style']     = $this->cssFail;
               $return['url']       = $this->urlAdd;
               $return['data']      = $this->mObj->_POST;
               $return['return']    = false;
            }
         }
         return (array)$return;
      }else{
         $return['message']   = 'Direct access not allowed on this page';
         $return['style']     = $this->cssFail;
         $return['url']       = $this->urlList;
         $return['data']      = NULL;
         $return['return']    = true;
         return (array)$return;
      }
   }

   public function Delete()
   {
      if($this->method == 'post'){
         $idDelete      = $this->mObj->_POST['idDelete'];
         $nameDelete    = $this->mObj->_POST['nameDelete'];
         if(empty($idDelete)){
            $return['message']   = 'Tidak ada data yang akan dihapus';
            $return['style']     = $this->cssFail;
            $return['url']       = $this->urlList;
            $return['data']      = NULL;
            $return['return']    = true;
         }else{
            for ($i=0; $i < count($idDelete); $i++) {
               $process          = $this->mObj->DoDeleteDipa($idDelete[$i]);
               if($process === false){
                  $err[]         = '- Data dipa "'.$nameDelete[$idDelete[$i]].'" tidak bisa dihapus. Cek status dipa';
               }
            }

            if(isset($err)){
               $return['message']   = implode('<br />', $err);
               $return['style']     = $this->cssFail;
               $return['url']       = $this->urlList;
               $return['data']      = NULL;
               $return['return']    = true;
            }else{
               $return['message']   = 'Data Dipa berhasil di hapus';
               $return['style']     = $this->cssDone;
               $return['url']       = $this->urlList;
               $return['data']      = NULL;
               $return['return']    = true;
            }
         }
         return (array)$return;
      }else{
         $return['message']   = 'Direct access not allowed on this page';
         $return['style']     = $this->cssFail;
         $return['url']       = $this->urlList;
         $return['data']      = NULL;
         $return['return']    = true;
         return (array)$return;
      }
   }

   public function Update()
   {
      if($this->method == 'post'){
         $this->setData();
         $requestData         = $this->request;
         // check input data
         if(empty($requestData)){
            $err[]      = 'Tidak ada data yang akan disimpan';
         }
         if($requestData['kode'] == ''){
            $err[]      = 'Isikan Nomor DIPA';
         }
         if($requestData['nominal'] <= 0){
            $err[]      = 'Isikan nominal DIPA yang sesuai';
         }
         // check duplicate data
         $duplicate     = $this->mObj->CheckDuplicate($requestData['kode'], $requestData['id']);
         if($duplicate === true){
            $err[]      = 'DIPA dengan nomor "'.$requestData['kode'].'" Sudah terdaftar di dalam sistem';
         }

         if(isset($err)){
            $return['message']   = $err[0];
            $return['style']     = $this->cssFail;
            $return['url']       = $this->urlUpdate;
            $return['data']      = $this->mObj->_POST;
            $return['return']    = false;
         }else{
            $process             = $this->mObj->DoUpdateDipa($requestData);
            if($process === true){
               $return['message']   = 'Proses update data berhasil';
               $return['style']     = $this->cssDone;
               $return['url']       = $this->urlList;
               $return['data']      = null;
               $return['return']    = true;
            }else{
               $return['message']   = 'Proses update data gagal';
               $return['style']     = $this->cssFail;
               $return['url']       = $this->urlUpdate;
               $return['data']      = $this->mObj->_POST;
               $return['return']    = false;
            }
         }
         return (array)$return;
      }else{
         $return['message']   = 'Direct access not allowed on this page';
         $return['style']     = $this->cssFail;
         $return['url']       = $this->urlList;
         $return['data']      = NULL;
         $return['return']    = true;
         return (array)$return;
      }
   }

   public function DoChangeStatus()
   {
      if($this->method == 'post'){
         $id         = $this->mObj->_POST['id'];
         $process    = $this->mObj->DoSetAktifDipa($id);

         if($process === true){
            $return['message']   = 'Proses set aktif dipa berhasil';
            $return['style']     = $this->cssDone;
            $return['url']       = $this->urlList;
            $return['data']      = $this->mObj->_POST;
            $return['return']    = true;
         }else{
            $return['message']   = 'Gagal melakukan set aktif dipa';
            $return['style']     = $this->cssFail;
            $return['url']       = $this->urlList;
            $return['data']      = $this->mObj->_POST;
            $return['return']    = true;
         }
         return (array)$return;
      }else{
         $return['message']   = 'Direct access not allowed on this page';
         $return['style']     = $this->cssFail;
         $return['url']       = $this->urlList;
         $return['data']      = NULL;
         $return['return']    = true;
         return (array)$return;
      }
   }
}
?>