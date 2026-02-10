<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/approval/business/AppDetilApproval.class.php';
class ProcessDetilApproval {

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
		$this->Obj = new AppDetilApproval();
		$this->_POST = $_POST->AsArray();
		$this->decId = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
		$this->encId = Dispatcher::Instance()->Encrypt($this->decId);
		$this->decJenisKegiatan = Dispatcher::Instance()->Decrypt($_GET['jenis_kegiatan']);
		$this->encJenisKegiatan = Dispatcher::Instance()->Encrypt($this->decJenisKegiatan);
		$this->pageView = Dispatcher::Instance()->GetUrl('approval', 'detilApproval', 'view', 'html') . '&jenis_kegiatan=' . $this->encJenisKegiatan;
		//$this->pageInput = Dispatcher::Instance()->GetUrl('approval', 'inputDetilApproval', 'view', 'html');
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

/*
	function Add() {
		//$cek = $this->Check();
		//if($cek === true) {
		$addDetilApproval = $this->Obj->DoAddDetilApproval($this->decId);
		if ($addDetilApproval === true) {
			Messenger::Instance()->Send('approval', 'approval', 'view', 'html', array($this->_POST,'Proses Berhasil', $this->cssDone),Messenger::NextRequest);
		} else {
			Messenger::Instance()->Send('approval', 'approval', 'view', 'html', array($this->_POST,'Proses Gagal', $this->cssFail),Messenger::NextRequest);
		}
		return $this->pageView;
	}
*/

	function Update() {
		$cek = $this->Check();
		if($cek === true) {
			//update nominal dan jumlah
			for($i=0;$i<sizeof($this->_POST['checkbox_id']);$i++) {
				if(isset($this->_POST['formula'][$this->_POST['checkbox_id'][$i]])){
					if($this->_POST['formula'][$this->_POST['checkbox_id'][$i]] == 0){
						$pembagi = 1;
					} else {
						$pembagi = $this->_POST['formula'][$this->_POST['checkbox_id'][$i]];
					}
				} else {
					$pembagi = 1;
				}

				$this->_POST['nominal'][$this->_POST['checkbox_id'][$i]] = $this->_POST['nominal'][$this->_POST['checkbox_id'][$i]] / $pembagi;
			}
			$updateDetilApproval = $this->Obj->DoUpdateDetilApproval($this->_POST['jumlah'], $this->_POST['nominal'], $this->_POST['satuan'], $this->_POST['keterangan'], $this->_POST['status_approval'], $this->_POST['checkbox_id']);
			//var_dump($updateDetilApproval);exit;
         if ($updateDetilApproval === true) {
            /*
            cek semua data status approval
            */
            $status = $this->Obj->GetStatusApproval($this->decId);
            if(!array_key_exists('Belum', $status)) {
               $this->Obj->DoUpdateStatusApprovalKegiatanDetil((array_key_exists('Ya', $status)) ? 'Ya': 'Tidak', $this->decId);
            }
				Messenger::Instance()->Send('approval', 'detilApproval', 'view', 'html', array($this->_POST,'Proses berhasil', $this->cssDone,'done'),Messenger::NextRequest);
			} else {
				Messenger::Instance()->Send('approval', 'detilApproval', 'view', 'html', array($this->_POST,'Proses Gagal', $this->cssFail),Messenger::NextRequest);
			}
			/*update status approval
			//$updateStatusApproval = $this->Obj->DoUpdateStatusApproval($this->_POST['status_approval'], $this->_POST['checkbox_id']);

			if ($updateDetilApproval === true && $updateStatusApproval === true) {
				Messenger::Instance()->Send('approval', 'detilApproval', 'view', 'html', array($this->_POST,'Proses Berhasil', $this->cssDone),Messenger::NextRequest);
			} elseif($updateDetilApproval === true || $updateStatusApproval === true) {
				Messenger::Instance()->Send('approval', 'detilApproval', 'view', 'html', array($this->_POST,'Proses gagal', $this->cssDone),Messenger::NextRequest);
			} else {
				Messenger::Instance()->Send('approval', 'detilApproval', 'view', 'html', array($this->_POST,'Proses Gagal', $this->cssFail),Messenger::NextRequest);
			}
         */
		} elseif($cek === "empty") {
			Messenger::Instance()->Send('approval', 'detilApproval', 'view', 'html', array($this->_POST,'Pilih Minimal Salah Satu Komponen', $this->cssFail),Messenger::NextRequest);
      }
		return $this->pageView . "&dataId=" . $this->encId;
	}


}
?>
