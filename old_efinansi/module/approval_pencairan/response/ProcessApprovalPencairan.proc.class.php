<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/approval_pencairan/business/AppApprovalPencairan.class.php';
class ProcessApprovalPencairan {

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
		$this->Obj = new AppApprovalPencairan();
		$this->_POST = $_POST->AsArray();
		$this->decId = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
		$this->encId = Dispatcher::Instance()->Encrypt($this->decId);
		$this->pageView = Dispatcher::Instance()->GetUrl('approval_pencairan', 'approvalPencairan', 'view', 'html');
		$this->pageInput = Dispatcher::Instance()->GetUrl('approval_pencairan', 'inputApprovalPencairan', 'view', 'html');
	}

	function Check() {
		if (isset($_POST['btnsimpan'])) {
			if($this->_POST['status'] == "Ya" && trim($this->_POST['nominal_approve']) == "") {
			   return "empty";
			} elseif($this->_POST['status'] == "Tidak") {
            	$this->_POST['nominal_approve'] = 0;
				return true;
			} elseif($this->_POST['status'] == '' || empty($this->_POST['status'])) {            	
				return 'status_empty';
			} elseif($this->CheckNominal() == false) {
            return "lebih_besar";
         } else {
				return true;
         }
		}
		return false;
	}

   function CheckNominal() {
      $data = $this->Obj->GetNominal($this->decId);
      if($this->_POST['nominal_approve'] > $data['nominal']) {
         return false;
      }
      return true;
   }

	function Update() {
		$cek = $this->Check();
		if($cek === true) {
         $userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
			$update = $this->Obj->DoUpdate($this->_POST['status'], $this->_POST['nominal_approve'], $userId, $this->decId);
			if ($update === true) {
				Messenger::Instance()->Send('approval_pencairan', 'approvalPencairan', 'view', 'html', array($this->_POST,'proses berhasil', $this->cssDone),Messenger::NextRequest);
			} else {
				Messenger::Instance()->Send('approval_pencairan', 'approvalPencairan', 'view', 'html', array($this->_POST,'Proses gagal', $this->cssFail),Messenger::NextRequest);
			}
		} elseif($cek == "empty") {
			Messenger::Instance()->Send('approval_pencairan', 'inputApprovalPencairan', 'view', 'html', array($this->_POST,'Lengkapi Isian Data'),Messenger::NextRequest);
			return $this->pageInput . "&dataId=" . $this->encId;
		} elseif($cek == "status_empty") {
			Messenger::Instance()->Send('approval_pencairan', 'inputApprovalPencairan', 'view', 'html', array($this->_POST,'Status Approval Belum Dipilih'),Messenger::NextRequest);
			return $this->pageInput . "&dataId=" . $this->encId;
		} elseif($cek == "lebih_besar") {
			Messenger::Instance()->Send('approval_pencairan', 'inputApprovalPencairan', 'view', 'html', array($this->_POST,'Nominal yang disetujui tidak boleh lebih besar dari yang diusulkan'),Messenger::NextRequest);
			return $this->pageInput . "&dataId=" . $this->encId;
		}
		return $this->pageView;
	}

}
?>
