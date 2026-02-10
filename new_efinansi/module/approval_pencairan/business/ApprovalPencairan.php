<?php
/**
* ================= doc ====================
* FILENAME     : ApprovalPencairan.php
* @package     : ApprovalPencairan
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-03-31
* @Modified    : 2015-03-31
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/approval_pencairan/business/AppApprovalPencairan.class.php';

class ApprovalPencairan
{
   # internal variables
   private $mObj;
   private $urlList;
   private $urlInput;
   protected $mData  = array();
  
   # Constructor
   function __construct ($connectionNumber = 0)
   {
      $this->mObj       = new AppApprovalPencairan();
      $queryString      = $this->mObj->_getQueryString();
      $queryRequest     = preg_replace('/(data_id=[\d]+)/', '', $queryString);
      $queryRequest     = preg_replace('/(search=[\d]+)/', '', $queryString);
      $queryRequest     = preg_replace('/\&[\&]+/', '&', $queryString);

      $this->urlList    = Dispatcher::Instance()->GetUrl(
         'approval_pencairan',
         'ApprovalPencairan',
         'view',
         'html'
      ).'&search=1&'.$queryRequest;
      $this->urlInput   = Dispatcher::Instance()->GetUrl(
         'approval_pencairan',
         'InputApprovalPencairan',
         'view',
         'html'
      ).'&'.$queryString;
   }

   private function setData()
   {
      $requestData      = array();
      if($this->mObj->method == 'post'){
         $requestData['id']         = $this->mObj->_POST['dataId'];
         $requestData['status']     = $this->mObj->_POST['status'];
         $requestData['nominal']    = $this->mObj->_POST['nominal_approve'];
         $requestData['nominal_approve']  = 0;
         $requestData['komponen']   = array();
         if($this->mObj->_POST['KOMP'] && !empty($this->mObj->_POST['KOMP'])){
            $index      = 0;
            foreach ($this->mObj->_POST['KOMP'] as $komponen) {
               $requestData['komponen'][$index]['id']    = $komponen['id'];
               $requestData['komponen'][$index]['pr_id'] = $komponen['pr_id'];
               $requestData['komponen'][$index]['nominal_usulan']    = $komponen['nominal_usulan'];
               $requestData['komponen'][$index]['nominal_approve']   = $komponen['nominal_approve'];
               if(strtoupper($this->mObj->_POST['status']) == 'YA'){
                  $requestData['nominal_approve']  += $komponen['nominal_approve'];
               }
               $index++;
            }
         }
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
      if($this->mObj->method == 'post'){
         $requestData   = $this->mData;
         if(empty($requestData)){
            $err[]   = 'Tidak ada data yang akan diproses';
         }

         if(empty($requestData['komponen'])){
            $err[]   = 'Tidak ada data '.GTFWConfiguration::GetValue('language', 'komponen');
         }

         if(strtoupper($requestData['status']) == 'YA'){
            foreach ($requestData['komponen'] as $komponen) {
               if($komponen['nominal_approve'] > $komponen['nominal_usulan']){
                  $err[]   = 'Nominal yang disetujui untuk '.GTFWConfiguration::GetValue('language', 'komponen').' dengan '.GTFWConfiguration::GetValue('language', 'kode').' melebihi nominal usulan '.GTFWConfiguration::GetValue('language', 'rp').'.'.number_format($komponen['nominal_usulan'], 2, ',','.');
               }
            }
            if($requestData['nominal'] <> $requestData['nominal_approve']){
               $err[]   = 'Total nominal yang disetujui tidak sesuai';
            }

            if($requestData['nominal'] <= 0){
               $err[]   = 'Total nominal yang disetujui tidak sesuai';
            }
         } elseif($requestData['status'] == '' || empty($requestData['status'])){
         	$err[]   = 'Status Approval Belum Dipilih';
         }
      }else{
         $err[]   = 'You don\'t have permission to access this page';
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

   public function doApproval()
   {
      $this->setData();
      $check      = $this->checkData();
      if($check['result'] === false){
         $return['url']       = $this->urlInput;
         $return['message']   = $check['message'];
         $return['data']      = $this->mObj->_POST;
         $return['style']     = 'notebox-warning';
      }else{
         $process    = $this->mObj->doApproval($this->mData);
         if($process === true){
            $return['url']       = $this->urlList;
            $return['message']   = 'Proses berhasil';
            $return['data']      = NULL;
            $return['style']     = 'notebox-done';
         }else{
            $return['url']       = $this->urlInput;
            $return['message']   = 'Proses gagal';
            $return['data']      = $this->mObj->_POST;
            $return['style']     = 'notebox-warning';
         }
      }

      return (array)$return;
   }
}
?>