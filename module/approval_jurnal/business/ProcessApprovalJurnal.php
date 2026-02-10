<?php
/**
* ================= doc ====================
* FILENAME     : ProcessApprovalJurnal.php
* @package     : ProcessApprovalJurnal
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-02-23
* @Modified    : 2015-02-23
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/approval_jurnal/business/ApprovalJurnal.class.php';

class ProcessApprovalJurnal
{
   # internal variables
   private $mObj;
   protected $mData = array();
   public $pageReturn;
   # Constructor
   function __construct ()
   {
      $this->mObj       = new ApprovalJurnal();
      $queryString      = $this->mObj->_getQueryString();
      $this->pageReturn = Dispatcher::Instance()->GetUrl(
         'approval_jurnal',
         'ApprovalJurnal',
         'view',
         'html'
      ).'&search=1&'.$queryString;
   }

   private function setData()
   {
      $requestData      = array();
      if($this->mObj->method == 'post'){
         $pembukuanId   = $this->mObj->_POST['id'];
         $tmpJurnal     = $this->mObj->_POST['jurnal'];

         if($pembukuanId && !empty($pembukuanId)){
            if(!empty($pembukuanId)){
               foreach ($pembukuanId as $id) {
                  $requestData[$id]['id']                = $id;
                  $requestData[$id]['status_kas']        = NULL;
                  $requestData[$id]['bentuk_transaksi']  = NULL;
                  if($tmpJurnal && !empty($tmpJurnal)){
                     $requestData[$id]['status_kas']        = $tmpJurnal[$id]['status_kas'];
                     $requestData[$id]['bentuk_transaksi']  = $tmpJurnal[$id]['bentuk_transaksi'];
                  }
               }
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

   public function Approve()
   {
      $requestData         = $this->getData();
      $return['url']       = $this->pageReturn;
      $return['data']      = $this->mObj->_POST;
      if(empty($requestData)){
         $return['message']   = 'Pilih minimal 1 data jurnal yang akan di approve';
         $return['style']     = 'notebox-warning';
      }else{
         $process             = $this->mObj->doApproveJurnal($requestData);
         if($process === true){
            $return['message']   = 'Approval jurnal berhasil';
            $return['style']     = 'notebox-done';
         }else{
            $return['message']   = 'Approval jurnal gagal';
            $return['style']     = 'notebox-done';
         }
      }

      return $return;
   }

   public function UnApprove()
   {
      $requestData         = $this->getData();
      $return['url']       = $this->pageReturn;
      $return['data']      = $this->mObj->_POST;
      if(empty($requestData)){
         $return['message']   = 'Pilih minimal 1 data jurnal yang akan di approve';
         $return['style']     = 'notebox-warning';
      }else{
         $process             = $this->mObj->doUnApproveJurnal($requestData);
         if($process === true){
            $return['message']   = 'Pembalik Approval jurnal berhasil';
            $return['style']     = 'notebox-done';
         }else{
            $return['message']   = 'Pembalik Approval jurnal gagal';
            $return['style']     = 'notebox-done';
         }
      }

      return $return;
   }
}
?>