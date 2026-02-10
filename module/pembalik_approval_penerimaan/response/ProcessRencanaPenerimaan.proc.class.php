<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/pembalik_approval_penerimaan/business/AppRencanaPenerimaan.class.php';
class ProcessRencanaPenerimaan {

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

		$this->Obj = new AppRencanaPenerimaan();
		$this->_POST = $_POST->AsArray();
		$this->decId = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
		$this->encId = Dispatcher::Instance()->Encrypt($this->decId);
		$this->pageView = Dispatcher::Instance()->GetUrl('pembalik_approval_penerimaan', 'rencanaPenerimaan', 'view', 'html');
		$this->pageInput = Dispatcher::Instance()->GetUrl('pembalik_approval_penerimaan', 'InputRencanaPenerimaan', 'view', 'html') . '&tahun_anggaran=' . $this->_POST['tahun_anggaran'] . '&unitkerja=' . $this->_POST['unitkerja'];
		$this->perbulan = $this->_POST['jmlpenerimaan'];

	}

	function Update() {
		if($this->_POST['btnbalik']){
			Messenger::Instance()->Send('pembalik_approval_penerimaan', 'rencanaPenerimaan', 'view', 'html', Messenger::NextRequest);
			return $this->pageView;
		}

			$updateRencanaPenerimaan = $this->Obj->DoUpdateRencanaPenerimaan($this->_POST['approval'], $this->_POST['dataId']);

			if($updateRencanaPenerimaan === true) {
				Messenger::Instance()->Send('pembalik_approval_penerimaan', 'rencanaPenerimaan', 'view', 'html', array($this->_POST,'Proses Berhasil', $this->cssDone),Messenger::NextRequest);
			} else {
				Messenger::Instance()->Send('pembalik_approval_penerimaan', 'rencanaPenerimaan', 'view', 'html', array($this->_POST,'Proses Gagal', $this->cssFail),Messenger::NextRequest);
			}
			return $this->pageView;
	}
}
?>
