<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/pembalik_approval_pencairan/business/AppPembalikApprovalPencairan.class.php';
class ProcessPembalikApprovalPencairan {

   var $_POST;
   var $Obj;
   var $pageView;
   var $pageInput;
   //css hanya dipake di view
   var $cssDone = "notebox-done";
   var $cssFail = "notebox-warning";

   var $return;
   var $decId;
   var $encId;

   function __construct() {
      $this->Obj = new AppPembalikApprovalPencairan();
      $this->_POST = $_POST->AsArray();
      $this->decId = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
      $this->encId = Dispatcher::Instance()->Encrypt($this->decId);
      $this->pageView = Dispatcher::Instance()->GetUrl('pembalik_approval_pencairan', 'pembalikApprovalPencairan', 'view', 'html');
      $this->pageInput = Dispatcher::Instance()->GetUrl('pembalik_approval_pencairan', 'inputPembalikApprovalPencairan', 'view', 'html');
   }

   function Check() {
      if (isset($_POST['btnsimpan'])) {
         if($this->_POST['status_approval'] == "BELUM") {
            return "BELUM_DIAPPROVE";
         }
         return true;
      }
      return false;
   }

   function Update() {
      $cek = $this->Check();
      if($cek === true) {
         $userId = $this->Obj->getUserId();
         $update = $this->Obj->DoUpdate($userId, $this->decId);
         if ($update === true) {
            Messenger::Instance()->Send('pembalik_approval_pencairan', 'pembalikApprovalPencairan', 'view', 'html', array($this->_POST,'Proses Berhasil', $this->cssDone),Messenger::NextRequest);
         } else {
            Messenger::Instance()->Send('pembalik_approval_pencairan', 'pembalikApprovalPencairan', 'view', 'html', array($this->_POST,'Proses Gagal', $this->cssFail),Messenger::NextRequest);
         }
      } elseif($cek == "BELUM_DIAPPROVE") {
         Messenger::Instance()->Send('pembalik_approval_pencairan', 'pembalikApprovalPencairan', 'view', 'html', array($this->_POST,'Pencairan belum diapprove, tidak perlu dibalik', $this->cssFail),Messenger::NextRequest);
      }
      return $this->pageView;
   }
}
?>
