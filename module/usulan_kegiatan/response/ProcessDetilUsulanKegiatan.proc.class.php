<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/usulan_kegiatan/business/AppDetilUsulanKegiatan.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/user_unit_kerja/business/UserUnitKerja.class.php';
class ProcessDetilUsulanKegiatan {

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
		$this->Obj = new AppDetilUsulanKegiatan();
		$this->_POST = $_POST->AsArray();
		$this->decId = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
		$this->encId = Dispatcher::Instance()->Encrypt($this->decId);
		$this->decKegiatanId = Dispatcher::Instance()->Decrypt($_REQUEST['kegiatanId']);
		$this->encKegiatanId = Dispatcher::Instance()->Encrypt($this->decKegiatanId);
		$this->pageView = Dispatcher::Instance()->GetUrl('usulan_kegiatan', 'detilUsulanKegiatan', 'view', 'html') . '&kegiatanId=' . $this->encKegiatanId;
		$this->pageInput = Dispatcher::Instance()->GetUrl('usulan_kegiatan', 'inputDetilUsulanKegiatan', 'view', 'html') . '&kegiatanId=' . $this->encKegiatanId;
		$userUnitKerja = new UserUnitKerja();
		$userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
		$this->Role = $userUnitKerja->GetRoleUser($userId);
	}

	function Check() {
		//print_r($this->_POST);
		//if(!$this->_POST['unitkerja'] || trim($this->_POST['unitkerja']=='-')) $this->_POST['unitkerja'] = $this->_POST['satker'];
		if (isset($_POST['btnsimpan'])) {
			if(!$this->_POST['subprogram'] || 
			!$this->_POST['kegiatanref'] || 
			!$this->_POST['prioritas']) {
				return "empty";
            /*
			} elseif(!checkdate($this->_POST['waktu_pelaksanaan_mulai_mon'], "01", $this->_POST['waktu_pelaksanaan_mulai_year'])) {
            return "invalid_date_mulai";
			} elseif(!checkdate($this->_POST['waktu_pelaksanaan_selesai_mon'], "01", $this->_POST['waktu_pelaksanaan_selesai_year'])) {
            return "invalid_date_selesai";
            */
			} else {
            if(!checkdate($this->_POST['waktu_pelaksanaan_mulai_mon'], "01", $this->_POST['waktu_pelaksanaan_mulai_year'])) {
               $this->waktu_mulai='';
            } else {
               $this->waktu_mulai = $this->_POST['waktu_pelaksanaan_mulai_year'] . "-" . $this->_POST['waktu_pelaksanaan_mulai_mon'] . "-01";
            }
            if(!checkdate($this->_POST['waktu_pelaksanaan_selesai_mon'], "01", $this->_POST['waktu_pelaksanaan_selesai_year'])) {
               $this->waktu_selesai='';
            } else {
               $this->waktu_selesai = $this->_POST['waktu_pelaksanaan_selesai_year'] . "-" . $this->_POST['waktu_pelaksanaan_selesai_mon'] . "-01";
            }
				return true;
			}
		}
		return false;
	}

	function Add() {
		$cek = $this->Check();
		if($cek === true) {
			$addDetilUsulanKegiatan = $this->Obj->DoAddDetilUsulanKegiatan(
			                          $this->decKegiatanId, $this->_POST['kegiatanref'], 
			                          $this->_POST['deskripsi'], 
			                          $this->_POST['catatan'], 
			                          $this->_POST['output'], 
			                          $this->waktu_mulai, 
			                          $this->waktu_selesai,
			                          $this->_POST['prioritas'],
			                          $this->_POST['mastuk'], 
			                          $this->_POST['mastk'], 
			                          $this->_POST['keltuk'], 
			                          $this->_POST['keltk'], 
			                          $this->_POST['ikk'], 
			                          $this->_POST['iku'],
			                          $this->_POST['output_rkakl'],
									  $this->_POST['tupoksi_id']);
			if ($addDetilUsulanKegiatan === true) {
				Messenger::Instance()->Send('usulan_kegiatan', 'detilUsulanKegiatan', 'view', 'html', 
				array($this->_POST,'Penambahan data Berhasil Dilakukan', $this->cssDone),Messenger::NextRequest);
				return $this->pageView;
			} else {
				Messenger::Instance()->Send('usulan_kegiatan', 'inputDetilUsulanKegiatan', 'view', 'html', 
				array($this->_POST,'Gagal Menambah Data', $this->cssFail),Messenger::NextRequest);
				return $this->pageInput;
			}

		} elseif($cek == "empty") {
			Messenger::Instance()->Send('usulan_kegiatan', 'inputDetilUsulanKegiatan', 'view', 'html', 
			array($this->_POST,'Lengkapi Isian Data'),Messenger::NextRequest);
			return $this->pageInput;
		}else{
		    return $this->pageView;
		}
		return $this->pageView;
	}
	function Update() {
		$cek = $this->Check();
      //print_r($this->_POST);
		if($cek === true) {
			$updateDetilUsulanKegiatan = $this->Obj->DoUpdateDetilUsulanKegiatan(
												$this->decKegiatanId, 
												$this->_POST['kegiatanref'], 
												$this->_POST['deskripsi'], 
												$this->_POST['catatan'], 
												$this->_POST['output'], 
												$this->waktu_mulai, 
												$this->waktu_selesai, 
												$this->_POST['prioritas'],																				 $this->_POST['mastuk'], 
												$this->_POST['mastk'], 
												$this->_POST['keltuk'], 
												$this->_POST['keltk'], 
												$this->_POST['ikk'], 
												$this->_POST['iku'], 
												$this->_POST['output_rkakl'],
												$this->_POST['tupoksi_id'],
												$this->decId);
			if ($updateDetilUsulanKegiatan === true) {
				Messenger::Instance()->Send('usulan_kegiatan', 'detilUsulanKegiatan', 'view', 'html', array($this->_POST,'Perubahan data Berhasil Dilakukan', $this->cssDone),Messenger::NextRequest);
			} else {
				Messenger::Instance()->Send('usulan_kegiatan', 'detilUsulanKegiatan', 'view', 'html', array($this->_POST,'Gagal Merubah Data', $this->cssFail),Messenger::NextRequest);
			}
		} elseif($cek == "empty") {
			Messenger::Instance()->Send('usulan_kegiatan', 'inputDetilUsulanKegiatan', 'view', 'html', array($this->_POST,'Lengkapi Isian Data'),Messenger::NextRequest);
			return $this->pageInput . "&dataId=" . $this->encId;
         /*
		} elseif($cek == "invalid_date_mulai") {
			Messenger::Instance()->Send('usulan_kegiatan', 'inputDetilUsulanKegiatan', 'view', 'html', array($this->_POST,'Waktu Pelaksanaan Mulai Tidak Valid'),Messenger::NextRequest);
			return $this->pageInput;
		} elseif($cek == "invalid_date_selesai") {
			Messenger::Instance()->Send('usulan_kegiatan', 'inputDetilUsulanKegiatan', 'view', 'html', array($this->_POST,'Waktu Pelaksanaan Selesai Tidak Valid'),Messenger::NextRequest);
			return $this->pageInput;
         */
		}
		return $this->pageView;
	}

	function Delete() {
		$arrId = $this->_POST['idDelete'];
		//print_r($this->_POST);
		$deleteArrData = $this->Obj->DoDeleteDetilUsulanKegiatanByArrayId($arrId);
		if($deleteArrData === true) {
			Messenger::Instance()->Send('usulan_kegiatan', 'detilUsulanKegiatan', 'view', 'html', array($this->_POST,'Penghapusan Data Berhasil Dilakukan', $this->cssDone),Messenger::NextRequest);
		} else {
			//jika masuk disini, berarti PASTI ada salah satu atau lebih data yang gagal dihapus
			for($i=0;$i<sizeof($arrId);$i++) {
				$deleteData = false;
				$deleteData = $this->Obj->DoDeleteDetilUsulanKegiatanById($arrId[$i]);
				if($deleteData === true) $sukses += 1;
				else $gagal += 1;
			}
			Messenger::Instance()->Send('usulan_kegiatan', 'detilUsulanKegiatan', 'view', 'html', array($this->_POST, $gagal . ' Data Tidak Dapat Dihapus.', $this->cssFail),Messenger::NextRequest);
		}
		return $this->pageView;
	}
}
?>
