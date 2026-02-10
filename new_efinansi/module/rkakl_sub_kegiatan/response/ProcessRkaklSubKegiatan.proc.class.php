<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/rkakl_sub_kegiatan/business/RkaklSubKegiatan.class.php';

class ProcessRkaklSubKegiatan {

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
		$this->Obj = new RkaklSubKegiatan();
		$this->_POST = $_POST->AsArray();
		if(isset($_GET['dataId'])) {
			$this->decId = Dispatcher::Instance()->Decrypt($_GET['dataId']);
			$this->encId = Dispatcher::Instance()->Encrypt($this->decId);
		}

		$this->pageView = Dispatcher::Instance()->GetUrl('rkakl_sub_kegiatan', 'RkaklSubKegiatan', 'view', 'html');
		$this->pageInput = Dispatcher::Instance()->GetUrl('rkakl_sub_kegiatan', 'inputRkaklSubKegiatan', 'view', 'html');
	}

	function Check() {
		if (isset($_POST['btnsimpan'])) {
			if(trim($this->_POST['kode']) == "") {
				return "emptyKode";
			} elseif(trim($this->_POST['nama']) == "") {
				return "emptyNama";
			} else return true;
		}
		return false;
	}

	function Add() {
		$cek = $this->Check();
		if($cek === true) {
			$add = $this->Obj->AddRkaklSubKegiatan($this->_POST['kode'],$this->_POST['nama']);
			if ($add === true) {
				Messenger::Instance()->Send('rkakl_sub_kegiatan', 'RkaklSubKegiatan', 'view', 'html', array($this->_POST,'Penambahan data Berhasil Dilakukan', $this->cssDone),Messenger::NextRequest);
			} else {
				Messenger::Instance()->Send('rkakl_sub_kegiatan', 'RkaklSubKegiatan', 'view', 'html', array($this->_POST,'Gagal Menambah Data', $this->cssFail),Messenger::NextRequest);
			}
		} elseif($cek == "emptyNama") {
			Messenger::Instance()->Send('rkakl_sub_kegiatan', 'inputRkaklSubKegiatan', 'view', 'html', array($this->_POST,'Nama wajib diisi'),Messenger::NextRequest);
			return $this->pageInput;
		} elseif($cek == "emptyKode") {
         Messenger::Instance()->Send('rkakl_sub_kegiatan', 'inputRkaklSubKegiatan', 'view', 'html', array($this->_POST,'Kode wajib diisi'),Messenger::NextRequest);
         return $this->pageInput;   
      }
		return $this->pageView;
	}

	function Update() {
		$cek = $this->Check();
		if($cek === true) {
			$update = $this->Obj->UpdateRkaklSubKegiatan($this->_POST['kode'],$this->_POST['nama'], $this->decId);
			if ($update === true) {
				Messenger::Instance()->Send('rkakl_sub_kegiatan', 'RkaklSubKegiatan', 'view', 'html', array($this->_POST,'Perubahan Data Berhasil Dilakukan', $this->cssDone),Messenger::NextRequest);
			} else {
				Messenger::Instance()->Send('rkakl_sub_kegiatan', 'RkaklSubKegiatan', 'view', 'html', array($this->_POST,'Perubahan Data Gagal Dilakukan', $this->cssFail),Messenger::NextRequest);
			}
		} elseif($cek == "emptyKode") {
			Messenger::Instance()->Send('rkakl_sub_kegiatan', 'inputRkaklSubKegiatan', 'view', 'html', array($this->_POST,'Kode wajib diisi'),Messenger::NextRequest);
			return $this->pageInput . "&dataId=" . $this->encId;
		}  elseif($cek == "emptyNama") {
         Messenger::Instance()->Send('rkakl_sub_kegiatan', 'inputRkaklSubKegiatan', 'view', 'html', array($this->_POST,'Nama wajib diisi'),Messenger::NextRequest);
         return $this->pageInput . "&dataId=" . $this->encId;
      }
		return $this->pageView;
	}

	function Delete() {
		$arrId = $this->_POST['idDelete'];
		$deleteArrData = $this->Obj->DeleteRkaklSubKegiatanByArrayId($arrId);
		if($deleteArrData === true) {
			Messenger::Instance()->Send('rkakl_sub_kegiatan', 'RkaklSubKegiatan', 'view', 'html', array($this->_POST,'Penghapusan Data Berhasil Dilakukan', $this->cssDone),Messenger::NextRequest);
		} else {
			//jika masuk disini, berarti PASTI ada salah satu atau lebih data yang gagal dihapus
			for($i=0;$i<sizeof($arrId);$i++) {
				$deleteData = false;
				$deleteData = $this->Obj->DeleteRkaklSubKegiatanById($arrId[$i]);
				if($deleteData === true) $sukses += 1;
				else $gagal += 1;
			}
			Messenger::Instance()->Send('rkakl_sub_kegiatan', 'RkaklSubKegiatan', 'view', 'html', array($this->_POST, $gagal . ' Data Tidak Dapat Dihapus.', $this->cssFail),Messenger::NextRequest);
		}
		return $this->pageView;
	}
}
?>
