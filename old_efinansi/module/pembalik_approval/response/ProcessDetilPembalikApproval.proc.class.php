<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/pembalik_approval/business/AppDetilPembalikApproval.class.php';
class ProcessDetilPembalikApproval {

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
		$this->Obj = new AppDetilPembalikApproval();
		$this->_POST = $_POST->AsArray();
		$this->decId = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
		$this->encId = Dispatcher::Instance()->Encrypt($this->decId);
		$this->decJenisKegiatan = Dispatcher::Instance()->Decrypt($_GET['jenis_kegiatan']);
		$this->encJenisKegiatan = Dispatcher::Instance()->Encrypt($this->decJenisKegiatan);
		$this->pageView = Dispatcher::Instance()->GetUrl('pembalik_approval', 'detilPembalikApproval', 'view', 'html') . '&jenis_kegiatan=' . $this->encJenisKegiatan;
		//$this->pageInput = Dispatcher::Instance()->GetUrl('approval', 'inputDetilPembalikApproval', 'view', 'html');
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

	function Update() {
		$cek = $this->Check();
		if($cek === true) {
			//update nominal dan jumlah
			$updateDetilPembalikApproval = $this->Obj->DoUpdateDetilPembalikApproval($this->_POST['checkbox_id']);
			if ($updateDetilPembalikApproval === true) {

			/*insert ke table kegiatan_detail_status, untuk mencatat log approval*/
            $this->Obj->DoInsertKegdetStatus($this->_POST['checkbox_id'], $this->decId);
            /*
            cek semua data status approval
            */
            $status = $this->Obj->GetStatusPembalikApproval($this->decId);
            //echo $status;
            if(sizeof($status) == 1) {
               //update tabel kegiatan detil
               $this->Obj->DoUpdateStatusPembalikApprovalKegiatanDetil($this->decId);
            }

				Messenger::Instance()->Send('pembalik_approval', 'detilPembalikApproval', 'view', 'html', array($this->_POST,'Proses berhasil', $this->cssDone),Messenger::NextRequest);
			} else {
				Messenger::Instance()->Send('pembalik_approval', 'detilPembalikApproval', 'view', 'html', array($this->_POST,'Proses Gagal', $this->cssFail),Messenger::NextRequest);
			}
		} elseif($cek === "empty") {
			Messenger::Instance()->Send('pembalik_approval', 'detilPembalikApproval', 'view', 'html', array($this->_POST,'Pilih Minimal Salah Satu Komponen', $this->cssFail),Messenger::NextRequest);
      }
		return $this->pageView . "&dataId=" . $this->encId;
	}
}
?>
