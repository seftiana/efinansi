<?php
/**
* ================= doc ====================
* FILENAME     : PembalikApprovalPencairan.php
* @package     : PembalikApprovalPencairan
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-04-09
* @Modified    : 2015-04-09
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/pembalik_approval_pencairan/business/AppPembalikApprovalPencairan.class.php';

class PembalikApprovalPencairan
{
   # internal variables
   private $mObj;
   private $urlReturn;
   private $urlInput;
   # Constructor
   function __construct ($connectionNumber = 0)
   {
      $this->mObj       = new AppPembalikApprovalPencairan();
      $queryString      = $this->mObj->_getQueryString();
      $queryRequest     = preg_replace('/(data_id=[\d]+)/', '', $queryString);
      $queryRequest     = preg_replace('/(search=[\d]+)/', '', $queryRequest);
      $queryRequest     = preg_replace('/[\&]$/', '', $queryRequest);
      $queryRequest     = preg_replace('/\&[\&]+/', '&', $queryRequest);
      $this->urlReturn  = Dispatcher::Instance()->GetUrl(
         'pembalik_approval_pencairan',
         'PembalikApprovalPencairan',
         'view',
         'html'
      ).'&search=1&'.$queryRequest;

      $this->urlInput   = Dispatcher::Instance()->GetUrl(
         'pembalik_approval_pencairan',
         'inputPembalikApprovalPencairan',
         'view',
         'html'
      ).'&'.$queryString;
   }

   public function UnApprove()
   {
      $process       = $this->mObj->doUnproveRealisasi($this->mObj->_POST['dataId']);
      if($process === true){
         $return['url']       = $this->urlReturn;
         $return['style']     = 'notebox-done';
         $return['data']      = NULL;
         $return['message']   = 'Proses Berhasil';
      }else{
         $return['url']       = $this->urlInput;
         $return['style']     = 'notebox-warning';
         $return['data']      = $this->mObj->_POST;
         $return['message']   = 'Proses Gagal';
      }

      return $return;
   }
}
?>