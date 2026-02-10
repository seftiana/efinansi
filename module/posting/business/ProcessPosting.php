<?php
/**
* ================= doc ====================
* FILENAME     : ProcessPosting.php
* @package     : ProcessPosting
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-02-25
* @Modified    : 2015-02-25
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/posting/business/AppPosting.class.php';

class ProcessPosting
{
   # internal variables
   private $mObj;
   # Constructor
   function __construct ()
   {
      $this->mObj       = new AppPosting();
   }

   public function doPosting()
   {
      $urlRedirect      = Dispatcher::Instance()->GetUrl(
         'posting',
         'Posting',
         'view',
         'html'
      );
      $checkCoaLabaRugi = $this->mObj->getCoaLabaRugi();
      $return['data']   = null;
      $return['url']    = $urlRedirect;
      if(!$this->mObj->method == 'post'){
         $return['message']   = 'You don\'t have permission to access this page';
         $return['style']     = 'notebox-warning';
      }elseif($checkCoaLabaRugi === false){
         $return['message']   = 'Coa laba rugi belum di set';
         $return['style']     = 'notebox-warning';
      }else{
         $tanggalPosting_day  = (int)$this->mObj->_POST['tanggal_posting_day'];
         $tanggalPosting_mon  = (int)$this->mObj->_POST['tanggal_posting_mon'];
         $tanggalPosting_year = (int)$this->mObj->_POST['tanggal_posting_year'];
         $tanggal_posting     = date('Y-m-d', mktime(0,0,0, $tanggalPosting_mon, $tanggalPosting_day, $tanggalPosting_year));
         $last_posting        = date('Y-m-d', strtotime($this->mObj->_POST['last_posting']));
         $check_date          = checkdate($tanggalPosting_mon, $tanggalPosting_day, $tanggalPosting_year);

         $subAkun = $this->mObj->_POST['sub_account'];

         if($check_date === false){
            $return['message']   = 'Definisikan tanggal posting dengan benar';
            $return['style']     = 'notebox-warning';
         }else{
            $process             = $this->mObj->doPosting($tanggal_posting, $subAkun);
            if($process === true){
               $return['message']   = 'Proses posting berhasil';
               $return['style']     = 'notebox-done';
            }else{
               $return['message']   = 'Gagal melakukan proses posting';
               $return['style']     = 'notebox-warning';
            }
         }
      }

      return $return;
   }
}
?>