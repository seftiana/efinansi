<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/adjustment_pengeluaran/business/AppInputAdjustmentPengeluaran.class.php';
class ProcessInputAdjustmentPengeluaran {

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
		$this->Obj = new AppInputAdjustmentPengeluaran();
		$this->_POST = $_POST->AsArray();
		$this->decId = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
		$this->encId = Dispatcher::Instance()->Encrypt($this->decId);
		$this->pageView = Dispatcher::Instance()->GetUrl('adjustment_pengeluaran', 'inputAdjustmentPengeluaran', 'view', 'html');
	}

	function Check() {
		if (isset($_POST['btnSimpan'])) {
         if(empty($this->_POST['checkbox_id'])) {
            return "empty";
         }
			return true;
		}
		return false;
	}


	function Input() {
		$cek = $this->Check();
		if($cek === true) {
			//cek history sudah ada ato belum
			$cekhistory = $this->Obj->GetHistoryIsExist($this->_POST['checkbox_id']);
			if ($cekhistory == 0){
			     $addhistory = $this->Obj->DoInputHistoryPengeluaran($this->_POST['checkbox_id']);
			     $updateDetilApproval = $this->Obj->DoUpdateDetilApproval($this->_POST['formula'],$this->_POST['nominal'], $this->_POST['satuan'], $this->_POST['checkbox_id']);
      }else{
           $updateDetilApproval = $this->Obj->DoUpdateDetilApproval($this->_POST['formula'],$this->_POST['nominal'], $this->_POST['satuan'], $this->_POST['checkbox_id']);   
      }
			if ($updateDetilApproval === true) {
				Messenger::Instance()->Send('adjustment_pengeluaran', 'inputAdjustmentPengeluaran', 'view', 'html', array($this->_POST,'Adjustment Pengeluaran Berhasil Dilakukan', $this->cssDone),Messenger::NextRequest);
			} else {
				Messenger::Instance()->Send('adjustment_pengeluaran', 'inputAdjustmentPengeluaran', 'view', 'html', array($this->_POST,'Adjustment Pengeluaran', $this->cssFail),Messenger::NextRequest);
			}
		} elseif($cek === "empty") {
			Messenger::Instance()->Send('adjustment_pengeluaran', 'inputAdjustmentPengeluaran', 'view', 'html', array($this->_POST,'Pilih Minimal Salah Satu Komponen', $this->cssFail),Messenger::NextRequest);
      }
		return $this->pageView . "&dataId=" . $this->encId;
	}
}
?>
