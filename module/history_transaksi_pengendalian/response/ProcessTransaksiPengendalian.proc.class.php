<?php
#require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/transaksi_pengendalian/business/AppTransaksi.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/transaksi_pengendalian/business/AppTransaksiPengendalianAsper.class.php';
class ProcessTransaksiPengendalian {

   var $_POST;
   var $Obj;
   var $pageView;
   var $pageInput;
   //css hanya dipake di view
   var $cssDone = "notebox-done";
   var $cssFail = "notebox-warning";
   var $cssAlert = "notebox-alert";

   var $return;
   var $decId;
   var $encId;

   function __construct() {
      #$this->Obj = new AppTransaksiAset();
      $this->ObjAsper = new AppTransaksiPengendalianAsper();
      $this->_POST = $_POST->AsArray();
      $this->decId = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
      $this->encId = Dispatcher::Instance()->Encrypt($this->decId);
      $this->pageView = Dispatcher::Instance()->GetUrl('transaksi_pengendalian', 'TransaksiPengendalian', 'view', 'html');
      #$this->pageDetil = Dispatcher::Instance()->GetUrl('transaksi_pengendalian', 'TransaksiPengendalian', 'view', 'html');
   }
   
   function Add() {
      $_POST = $_POST->AsArray();
      #print_r($_POST);
      if(!empty($_POST['btnbalik'])) {
         $urlRedirect = Dispatcher::Instance()->GetUrl('transaksi_pengendalian', 'TransaksiPengendalian', 'view', 'html');
      }else{
         Messenger::Instance()->Send('transaksi_pengendalian', 'TransaksiPengendalian', 'view', 'html', array($this->_POST,'Penambahan Data Transaksi Pengendalian Berhasil', $this->cssDone),Messenger::NextRequest);
         $urlRedirect = Dispatcher::Instance()->GetUrl('transaksi_pengendalian', 'TransaksiPengendalian', 'view', 'html');
      }
      return $urlRedirect;
   }
   
}

?>