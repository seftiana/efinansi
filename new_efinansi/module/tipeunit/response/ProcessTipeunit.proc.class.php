<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/tipeunit/business/AppTipeunit.class.php';
class ProcessTipeunit {

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
		$this->Obj = new AppTipeunit();
		$this->_POST = $_POST->AsArray();
		$this->decId = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
		$this->encId = Dispatcher::Instance()->Encrypt($this->decId);
		$this->pageView = Dispatcher::Instance()->GetUrl('tipeunit', 'tipeunit', 'view', 'html');
		$this->pageInput = Dispatcher::Instance()->GetUrl('tipeunit', 'inputTipeunit', 'view', 'html');
	}

	function Check() {
		if (isset($_POST['btnsimpan'])) {
			if(trim($this->_POST['tipeunit_nama']) == "") {
				return "empty";
			} else {
				return true;
			}
		}
		return false;
	}

	function Add() {
		$cek = $this->Check();
		if($cek === true) {
			$cekTipeunit = $this->Obj->CekDataTipeunit(trim(strtolower($this->_POST['tipeunit_nama'])));
			if($cekTipeunit['nama'] == trim(strtolower($this->_POST['tipeunit_nama'])) && $cekTipeunit['id'] != "") {
				Messenger::Instance()->Send('tipeunit', 'tipeunit', 'view', 'html', array($this->_POST,'Gagal Menambah Data, Tipe Unit Sudah Ada', $this->cssFail),Messenger::NextRequest);
			} else {
				$addTipeunit = $this->Obj->DoAddTipeunit($this->_POST['tipeunit_nama']);
				if ($addTipeunit === true) {
					Messenger::Instance()->Send('tipeunit', 'tipeunit', 'view', 'html', array($this->_POST,'Penambahan data Berhasil Dilakukan', $this->cssDone),Messenger::NextRequest);
				} else {
					Messenger::Instance()->Send('tipeunit', 'tipeunit', 'view', 'html', array($this->_POST,'Gagal Menambah Data', $this->cssFail),Messenger::NextRequest);
				}
			}
		} elseif($cek == "empty") {
			Messenger::Instance()->Send('tipeunit', 'inputTipeunit', 'view', 'html', array($this->_POST,'Lengkapi Isian Data'),Messenger::NextRequest);
			return $this->pageInput;
		}
		return $this->pageView;
	}

	function Update() {
		$cek = $this->Check();
		if($cek === true) {
			$cekTipeunit = $this->Obj->CekDataTipeunit(trim(strtolower($this->_POST['tipeunit_nama'])));
			if($cekTipeunit['nama'] == trim(strtolower($this->_POST['tipeunit_nama'])) && $cekTipeunit['id'] != "") {
				Messenger::Instance()->Send('tipeunit', 'tipeunit', 'view', 'html', array($this->_POST,'Perubahan Data Gagal Dilakukan, Tipe Unit Sama Dengan Yang Sudah Ada', $this->cssFail),Messenger::NextRequest);
			} else {
				$updateTipeunit = $this->Obj->DoUpdateTipeunit($this->_POST['tipeunit_nama'], $this->decId);
				if ($updateTipeunit === true) {
					Messenger::Instance()->Send('tipeunit', 'tipeunit', 'view', 'html', array($this->_POST,'Perubahan Data Berhasil Dilakukan', $this->cssDone),Messenger::NextRequest);
				} else {
					Messenger::Instance()->Send('tipeunit', 'tipeunit', 'view', 'html', array($this->_POST,'Perubahan Data Gagal Dilakukan', $this->cssFail),Messenger::NextRequest);
				}
			}
		} elseif($cek == "empty") {
			Messenger::Instance()->Send('tipeunit', 'inputTipeunit', 'view', 'html', array($this->_POST,'Lengkapi Isian Data'),Messenger::NextRequest);
			return $this->pageInput . "&dataId=" . $this->encId;
		}
		return $this->pageView;
	}

	function Delete() {
		$arrId = $this->_POST['idDelete'];
		//print_r($this->_POST);
		$deleteArrData = $this->Obj->DoDeleteTipeunitByArrayId($arrId);
		if($deleteArrData === true) {
			Messenger::Instance()->Send('tipeunit', 'tipeunit', 'view', 'html', array($this->_POST,'Penghapusan Data Berhasil Dilakukan', $this->cssDone),Messenger::NextRequest);
		} else {
			//jika masuk disini, berarti PASTI ada salah satu atau lebih data yang gagal dihapus
			for($i=0;$i<sizeof($arrId);$i++) {
				$deleteData = false;
				$deleteData = $this->Obj->DoDeleteTipeunitById($arrId[$i]);
				if($deleteData === true) $sukses += 1;
				else {
					$gagal += 1;
					$sebab = $this->Obj->GetError();
				}
			}
			Messenger::Instance()->Send('tipeunit', 'tipeunit', 'view', 'html', array($this->_POST, $gagal . ' Data Tidak Dapat Dihapus.<br />' . $sebab, $this->cssFail),Messenger::NextRequest);
		}
		return $this->pageView;
	}
}
?>
