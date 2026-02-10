<?php
/**
* ================= doc ====================
* FILENAME     : ProcessMataAnggaran.php
* @package     : ProcessMataAnggaran
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-03-27
* @Modified    : 2015-03-27
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/finansi_mata_anggaran/business/MataAnggaran.class.php';

class ProcessMataAnggaran
{
   # internal variables
   private $mObj;
   protected $mData = array();

   private $urlInput;
   private $urlEdit;
   private $urlList;
   # Constructor
   function __construct ($connectionNumber = 0)
   {
      $this->mObj    = new MataAnggaran($connectionNumber);
      $queryString   = $this->mObj->_getQueryString();
      $queryReturn   = preg_replace('/(data_id=[\d+])/', '', $queryString);
      $queryReturn   = preg_replace('/(search=[\d+])/', '', $queryReturn);
      $queryReturn   = preg_replace('/\&[\&]/', '&', $queryReturn);
      $this->urlList    = Dispatcher::Instance()->GetUrl(
         'finansi_mata_anggaran',
         'MataAnggaran',
         'view',
         'html'
      ).'&search=1&'.$queryReturn;
      $this->urlInput   = Dispatcher::Instance()->GetUrl(
         'finansi_mata_anggaran',
         'AddMataAnggaran',
         'view',
         'html'
      ).'&'.$queryString;
      $this->urlEdit    = Dispatcher::Instance()->GetUrl(
         'finansi_mata_anggaran',
         'EditMataAnggaran',
         'view',
         'html'
      ).'&'.$queryString;
   }

   private function setData()
   {
      $requestData   = array();
      if($this->mObj->method == 'post'){
         $requestData['id']      = $this->mObj->_POST['data_id'];
         $requestData['bas_id']  = $this->mObj->_POST['bas_id'];
         $requestData['bas']     = $this->mObj->_POST['bas'];
         $requestData['coa_id']  = $this->mObj->_POST['coa_id'];
         $requestData['tipe']    = $this->mObj->_POST['bas_tipe'];
         $requestData['kode']    = $this->mObj->_POST['kode'];
         $requestData['nama']    = $this->mObj->_POST['nama'];
         $requestData['status']  = $this->mObj->_POST['status'];
         $requestData['nilai_default'] = $this->mObj->_POST['nilai_default'];
      }

      $this->mData   = $requestData;
   }

   public function getData()
   {
      $this->setData();

      return $this->mData;
   }

   private function checkData()
   {
      $requestData      = $this->mData;
      if(empty($requestData)){
         $err[]   = 'Tidak ada data '.GTFWConfiguration::GetValue('language', 'mak');
      }

      if($requestData['bas_id'] == ''){
         $err[]   = 'Pilih '.GTFWConfiguration::GetValue('language', 'rkakl_pagu_bas');
      }

      if($requestData['kode'] == ''){
         $err[]   = 'Isikan '.GTFWConfiguration::GetValue('language', 'kode');
      }

      if($requestData['nama'] == ''){
         $err[]   = 'Isikan '.GTFWConfiguration::GetValue('language', 'nama');
      }

      $checkData  = $this->mObj->doCheckMataAnggaran($requestData['bas_id'], $requestData['kode'], $requestData['id']);

      if($checkData === false){
         $err[]   = GTFWConfiguration::GetValue('language', 'mak'). ' Dengan kode '.$requestData['kode'].' Sudah terdaftar dalam sistem';
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

   public function Save()
   {
      $this->setData();
      $check      = $this->checkData();
      if($check['result'] === false){
         $return['data']      = $this->mObj->_POST;
         $return['style']     = 'notebox-warning';
         $return['url']       = $this->urlInput;
         $return['message']   = $check['message'];
      }else{
         $process       = $this->mObj->doSaveData($this->mData);
         // $process = false;
         if($process === true){
            $return['data']      = NULL;
            $return['style']     = 'notebox-done';
            $return['url']       = $this->urlList;
            $return['message']   = 'Proses penyimpanan data berhasil';
         }else{
            $return['data']      = $this->mObj->_POST;
            $return['style']     = 'notebox-warning';
            $return['url']       = $this->urlInput;
            $return['message']   = 'Proses penyimpanan data gagal';
         }
      }

      return $return;
   }

   public function Update()
   {
      $this->setData();
      $check      = $this->checkData();
      if($check['result'] === false){
         $return['data']      = $this->mObj->_POST;
         $return['style']     = 'notebox-warning';
         $return['url']       = $this->urlEdit;
         $return['message']   = $check['message'];
      }else{
         $process       = $this->mObj->doUpdateData($this->mData);
         if($process === true){
            $return['data']      = NULL;
            $return['style']     = 'notebox-done';
            $return['url']       = $this->urlList;
            $return['message']   = 'Proses Update data berhasil';
         }else{
            $return['data']      = $this->mObj->_POST;
            $return['style']     = 'notebox-warning';
            $return['url']       = $this->urlEdit;
            $return['message']   = 'Proses Update data gagal';
         }
      }

      return $return;
   }

   public function Delete()
   {
      $return['url']    = $this->urlList;
      $return['data']   = NULL;
      if($this->mObj->method == 'post'){
         $idDelete      = $this->mObj->_POST['idDelete'];
         $count         = 0;
         $success       = 0;
         $failed        = 0;
         foreach ($idDelete as $id) {
            $delete     = $this->mObj->doDeleteData($id);
            if($delete === true){
               $success+=1;
            }else{
               $failed+=1;
            }
            $count+=1;
         }

         if((int)$count === 0){
            $return['message']   = 'Tidak ada data yang akan di hapus';
            $return['style']     = 'notebox-warning';
         }else if((int)$success === (int)$count){
            $return['message']   = 'Semua data berhasil dihapus';
            $return['style']     = 'notebox-done';
         }else if((int)$failed === 0){
            $return['message']   = 'Proses penghapusan data gagal';
            $return['style']     = 'notebox-warning';
         }else{
            $return['message']   = '<strong>'.$success.' Data</strong> berhasil di hapus. <strong>'.$failed.' Data</strong> Gagal di hapus';
            $return['style']     = 'notebox-info';
         }
      }else{
         $return['message']   = 'You don\'t have permission to access this page';
         $return['style']     = 'notebox-info';
      }

      return $return;
   }
}
?>