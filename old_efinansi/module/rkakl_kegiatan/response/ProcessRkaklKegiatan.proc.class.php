<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/rkakl_kegiatan/business/RkaklKegiatan.class.php';

class ProcessRkaklKegiatan {

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
		$this->Obj = new RkaklKegiatan();
		$this->_POST = $_POST->AsArray();
		$this->decId = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
		$this->encId = Dispatcher::Instance()->Encrypt($this->decId);
		$this->pageView = Dispatcher::Instance()->GetUrl('rkakl_kegiatan', 'RkaklKegiatan', 'view', 'html');
		$this->pageInput = Dispatcher::Instance()->GetUrl('rkakl_kegiatan', 'inputRkaklKegiatan', 'view', 'html');
	}

	function Check() {
		if (isset($_POST['btnsimpan'])) {
			if(trim($this->_POST['kode']) == "") {
				return "emptyKode";
			}elseif(trim($this->_POST['nama']) == "") {
				return "emptyNama";
			}else return true;
			foreach($_POST AS $field=>$val):
				echo $field." = ".$val."<br />";
			endforeach;
		}
		return false;
	}

	function Add() {
		$cek = $this->Check();
		if($cek === true) {
			$add = $this->Obj->AddRkaklKegiatan($this->_POST['kode'],
					$this->_POST['nama'],$this->_POST['program']);
			if ($add === true) {
				Messenger::Instance()->Send('rkakl_kegiatan', 'RkaklKegiatan', 'view', 'html',
				 array($this->_POST,'Penambahan data Berhasil Dilakukan', 
				 $this->cssDone),Messenger::NextRequest);
			} else {
				Messenger::Instance()->Send('rkakl_kegiatan', 'RkaklKegiatan', 'view', 'html',
				 array($this->_POST,'Gagal Menambah Data', 
				 $this->cssFail),Messenger::NextRequest);
			}
		} elseif($cek == "emptyNama") {
			Messenger::Instance()->Send('rkakl_kegiatan', 'inputRkaklKegiatan', 'view', 'html',
			 array($this->_POST,'Nama wajib diisi'),
			 Messenger::NextRequest);
			return $this->pageInput;
		}elseif($cek == "emptyKode") {
         Messenger::Instance()->Send('rkakl_kegiatan', 'inputRkaklKegiatan', 'view', 'html',
		  array($this->_POST,'Kode wajib diisi'),Messenger::NextRequest);
         return $this->pageInput;   
      }
		return $this->pageView;
	}

	function Update() {
		$cek = $this->Check();
		if($cek === true) {
			$update = $this->Obj->UpdateRkaklKegiatan($this->_POST['kode'],
			$this->_POST['nama'],$this->_POST['program'], $this->decId);
			if ($update === true) {
				Messenger::Instance()->Send('rkakl_kegiatan', 'RkaklKegiatan', 'view', 'html',
				 array($this->_POST,'Perubahan Data Berhasil Dilakukan', 
				$this->cssDone),Messenger::NextRequest);
			} else {
				Messenger::Instance()->Send('rkakl_kegiatan', 'RkaklKegiatan', 'view', 'html',
				 array($this->_POST,'Perubahan Data Gagal Dilakukan', 
				 $this->cssFail),Messenger::NextRequest);
			}
		} elseif($cek == "emptyKode") {
			Messenger::Instance()->Send('rkakl_kegiatan', 'inputRkaklKegiatan', 'view', 'html',
			 array($this->_POST,'Kode wajib diisi'),Messenger::NextRequest);
			return $this->pageInput . "&dataId=" . $this->encId;
		}  elseif($cek == "emptyNama") {
         Messenger::Instance()->Send('rkakl_kegiatan', 'inputRkaklKegiatan', 'view', 'html', 
		 array($this->_POST,'Nama wajib diisi'),Messenger::NextRequest);
         return $this->pageInput . "&dataId=" . $this->encId;
      }
		return $this->pageView;
	}

	function Delete() {
		$arrId = $this->_POST['idDelete'];
		$deleteArrData = $this->Obj->DeleteRkaklKegiatanByArrayId($arrId);
		if($deleteArrData === true) {
			Messenger::Instance()->Send('rkakl_kegiatan', 'RkaklKegiatan', 'view', 'html',
			 array($this->_POST,'Penghapusan Data Berhasil Dilakukan', 
			 $this->cssDone),Messenger::NextRequest);
		} else {
			//jika masuk disini, berarti PASTI ada salah satu atau lebih data yang gagal dihapus
			for($i=0;$i<sizeof($arrId);$i++) {
				$deleteData = false;
				$deleteData = $this->Obj->DeleteRkaklKegiatanById($arrId[$i]);
				if($deleteData === true) $sukses += 1;
				else $gagal += 1;
			}
			Messenger::Instance()->Send('rkakl_kegiatan', 'RkaklKegiatan', 'view', 'html', array($this->_POST, $gagal . ' Data Tidak Dapat Dihapus.', $this->cssFail),Messenger::NextRequest);
		}
		return $this->pageView;
	}
}
?>
