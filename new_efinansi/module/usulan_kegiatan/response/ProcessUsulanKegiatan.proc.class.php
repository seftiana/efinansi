<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/usulan_kegiatan/business/AppUsulanKegiatan.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/user_unit_kerja/business/UserUnitKerja.class.php';
class ProcessUsulanKegiatan {

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
		$this->Obj = new AppUsulanKegiatan();
		$this->_POST = $_POST->AsArray();
		$this->decId = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
		$this->encId = Dispatcher::Instance()->Encrypt($this->decId);
		$this->pageView = Dispatcher::Instance()->GetUrl('usulan_kegiatan', 'usulanKegiatan', 'view', 'html');
		$this->pageInput = Dispatcher::Instance()->GetUrl('usulan_kegiatan', 'inputUsulanKegiatan', 'view', 'html');
		$userUnitKerja = new UserUnitKerja();
		$userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
		$this->Role = $userUnitKerja->GetRoleUser($userId);
	}

	function Check() {
		if(!$this->_POST['unitkerja'] || trim($this->_POST['unitkerja']=='-')) $this->_POST['unitkerja'] = $this->_POST['satker'];
		if (isset($_POST['btnsimpan'])) {
			if($this->Role['role_name'] == "Administrator") {
				//tahun anggaran dari $_POST dan $_GET, unitkerja dan sub-unit dari $_POST dan $_GET
				if(!$this->_POST['tahun_anggaran'] || !$this->_POST['unitkerja'] || !$this->_POST['program'] || trim($this->_POST['latar_belakang'])=="") {
					return "empty";
				} else {
					return true;
				}
			} elseif($this->Role['role_name'] == "OperatorUnit") {
				//tahun anggaran dan unitkerja dari database, subunit dari $_POST dan $_GET
				if(!$this->_POST['unitkerja'] || !$this->_POST['program'] || trim($this->_POST['latar_belakang'])=="") {
					return "empty";
				} else {
					return true;
				}
			} else {
				//tahun anggaran, unitkerja, dan subunit dari database
				if(!$this->_POST['program'] || trim($this->_POST['latar_belakang'])=="") {
					return "empty";
				} else {
					return true;
				}
			}
		}
		return false;
	}

	function Add() {
		$cek = $this->Check();
		if($cek === true) {
		    if($this->Obj->CheckKegiatan($this->_POST['unitkerja'],$this->_POST['program'],$this->_POST['tahun_anggaran']) <> 0){
		        Messenger::Instance()->Send('usulan_kegiatan', 'inputUsulanKegiatan', 'view', 'html', 
		        array(
		        $this->_POST,
		        'Anda tidak di ijinkan untuk memilih program yang sudah terdaftar dalam satu unit dan pada tahun anggaran yang sama', 
		        $this->cssFail),Messenger::NextRequest);
			    return $this->pageInput;
		    }else{
			    $addUsulanKegiatan = $this->Obj->DoAddUsulanKegiatan(
			        $this->_POST['tahun_anggaran'], $this->_POST['unitkerja'], 
			        $this->_POST['program'], $this->_POST['latar_belakang'], 
			        $this->_POST['indikator'], $this->_POST['baseline'], $this->_POST['final'], 
			        $this->_POST['satker_pimpinan'], 
			        $this->_POST['unitkerja_pimpinan'], 
			        $this->_POST['nama_pic']);
			    if ($addUsulanKegiatan === true) {
				    Messenger::Instance()->Send('usulan_kegiatan', 'usulanKegiatan', 'view', 'html', 
				    array(
				        $this->_POST,
				        'Penambahan data Berhasil Dilakukan', 
				        $this->cssDone),Messenger::NextRequest);
			    } else {
				    Messenger::Instance()->Send('usulan_kegiatan', 'usulanKegiatan', 'view', 'html', 
				    array(
				        $this->_POST,
				        'Gagal Menambah Data', 
				        $this->cssFail),Messenger::NextRequest);
			    }
			}
		} elseif($cek == "empty") {
			Messenger::Instance()->Send('usulan_kegiatan', 'inputUsulanKegiatan', 'view', 'html', 
			array(
			    $this->_POST,
			    'Lengkapi Isian Data', 
			    $this->cssFail),Messenger::NextRequest);
			return $this->pageInput;
		}
		return $this->pageView;
	}

	function Update() {
		$cek = $this->Check();
		if($cek === true) {
			//$updateUsulanKegiatan = $this->Obj->DoUpdateUsulanKegiatan($this->_POST['usulan_kegiatan_nama'], $this->decId);
			$updateUsulanKegiatan = $this->Obj->DoUpdateUsulanKegiatan($this->_POST['tahun_anggaran'], $this->_POST['unitkerja'], $this->_POST['program'], $this->_POST['latar_belakang'], $this->_POST['indikator'], $this->_POST['baseline'], $this->_POST['final'], $this->_POST['satker_pimpinan'], $this->_POST['unitkerja_pimpinan'], $this->_POST['nama_pic'], $this->_POST['kegiatan_id']);
			if ($updateUsulanKegiatan === true) {
				Messenger::Instance()->Send('usulan_kegiatan', 'usulanKegiatan', 'view', 'html', array($this->_POST,'Perubahan Data Berhasil Dilakukan', $this->cssDone),Messenger::NextRequest);
			} else {
				Messenger::Instance()->Send('usulan_kegiatan', 'usulanKegiatan', 'view', 'html', array($this->_POST,'Perubahan Data Gagal Dilakukan', $this->cssFail),Messenger::NextRequest);
			}
		} elseif($cek == "empty") {
			Messenger::Instance()->Send('usulan_kegiatan', 'inputUsulanKegiatan', 'view', 'html', array($this->_POST,'Lengkapi Isian Data',$this->cssFail),Messenger::NextRequest);
			return $this->pageInput . "&dataId=" . $this->encId;
		}
		return $this->pageView;
	}

	function Delete() {
		$arrId = $this->_POST['idDelete'];
		//print_r($this->_POST);
		$deleteArrData = $this->Obj->DoDeleteUsulanKegiatanByArrayId($arrId);
		if($deleteArrData === true) {
			Messenger::Instance()->Send('usulan_kegiatan', 'usulanKegiatan', 'view', 'html', array($this->_POST,'Penghapusan Data Berhasil Dilakukan', $this->cssDone),Messenger::NextRequest);
		} else {
			//jika masuk disini, berarti PASTI ada salah satu atau lebih data yang gagal dihapus
			for($i=0;$i<sizeof($arrId);$i++) {
				$deleteData = false;
				$deleteData = $this->Obj->DoDeleteUsulanKegiatanById($arrId[$i]);
				if($deleteData === true) $sukses += 1;
				else $gagal += 1;
			}
			Messenger::Instance()->Send('usulan_kegiatan', 'usulanKegiatan', 'view', 'html', array($this->_POST, $gagal . ' Data Tidak Dapat Dihapus.', $this->cssFail),Messenger::NextRequest);
		}
		return $this->pageView;
	}
}
?>
