<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/pagu_anggaran_unit/business/PaguAnggaranUnit.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/user_unit_kerja/business/UserUnitKerja.class.php';
class ProcessPaguAnggaranUnit {

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
		$this->Obj = new PaguAnggaranUnit();
		$this->_POST = $_POST->AsArray();
		$this->decId = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
		$this->encId = Dispatcher::Instance()->Encrypt($this->decId);
		$this->pageView = Dispatcher::Instance()->GetUrl('pagu_anggaran_unit', 'paguAnggaranUnit', 'view', 'html');
		$this->pageInput = Dispatcher::Instance()->GetUrl('pagu_anggaran_unit', 'inputPaguAnggaranUnit', 'view', 'html');
		$this->pageCopy = Dispatcher::Instance()->GetUrl('pagu_anggaran_unit', 'copyPaguAnggaranUnit', 'view', 'html');
		$userUnitKerja = new UserUnitKerja();
		$userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
		$this->Role = $userUnitKerja->GetRoleUser($userId);
	}

	function Check() {
		//print_r($this->_POST);
		if(!$this->_POST['unitkerja'] || trim($this->_POST['unitkerja']=='-')) 
		$this->_POST['unitkerja'] = $this->_POST['satker'];
		if (isset($_POST['btnsimpan'])) {
			if($this->Role['role_name'] == "Administrator") {
				//tahun anggaran dari $_POST dan $_GET, unitkerja dan sub-unit dari $_POST dan $_GET
				if(!$this->_POST['tahun_anggaran'] || !$this->_POST['unitkerja']  || 
				trim($this->_POST['nominal_pagu'])== "" || trim($this->_POST['nominal_pagu'])== 0 || !$this->_POST['bas'] || !$this->_POST['sumber_dana'] || !$this->_POST['unitkerja']) {
					return "empty";
				/* }elseif(!$this->_POST['tahun_anggaran'] || !$this->_POST['unitkerja']  || 
				trim($this->_POST['pagu_tersedia'])=="") {
					return "empty_pagu"; */
				} else {
					return true;
				}
			} elseif($this->Role['role_name'] == "OperatorUnit") {
				//tahun anggaran dan unitkerja dari database, subunit dari $_POST dan $_GET
				if(!$this->_POST['unitkerja'] || trim($this->_POST['nominal_pagu'])==""  || trim($this->_POST['nominal_pagu'])== 0 || !$this->_POST['sumber_dana']  || !$this->_POST['bas'] || !$this->_POST['unitkerja']) {
					return "empty";
				} else {
					return true;
				}
			} else {
				//tahun anggaran, unitkerja, dan subunit dari database
				if(trim($this->_POST['nominal_pagu'])==""  || trim($this->_POST['nominal_pagu'])== 0 || !$this->_POST['sumber_dana']  || !$this->_POST['bas']) {
					return "empty";
				//}elseif(trim($this->_POST['pagu_tersedia'])=="") {
				//	return "empty_pagu";
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
			$addPaguAnggaranUnit = $this->Obj->DoAddPaguAnggaranUnit($this->_POST['tahun_anggaran'], 
			$this->_POST['unitkerja'], $this->_POST['bas'], $this->_POST['nominal_pagu'], 
			$this->_POST['sumber_dana'], $this->_POST['nominal_pagu']);
			if ($addPaguAnggaranUnit === true) {
				Messenger::Instance()->Send('pagu_anggaran_unit', 'paguAnggaranUnit', 'view', 'html', 
				array($this->_POST,'Penambahan data Berhasil Dilakukan', 
				$this->cssDone),Messenger::NextRequest);
			} else {
				Messenger::Instance()->Send('pagu_anggaran_unit', 'paguAnggaranUnit', 'view', 'html', 
				array($this->_POST,'Gagal Menambah Data', $this->cssFail),Messenger::NextRequest);
			}
		} elseif($cek == "empty") {
			Messenger::Instance()->Send('pagu_anggaran_unit', 'inputPaguAnggaranUnit', 'view', 'html', 
			array($this->_POST,'Lengkapi Isian Data'),Messenger::NextRequest);
			return $this->pageInput;
		}
		return $this->pageView;
	}

	function Update() {
		$cek = $this->Check();
		if($cek === true) {
			//$updatePaguAnggaranUnit = $this->Obj->DoUpdatePaguAnggaranUnit($this->_POST['pagu_anggaran_unit_nama'], $this->decId);
			$updatePaguAnggaranUnit = $this->Obj->DoUpdatePaguAnggaranUnit($this->_POST['tahun_anggaran'], 
			$this->_POST['unitkerja'], $this->_POST['bas'], $this->_POST['nominal_pagu'], 
			$this->_POST['sumber_dana'],$this->_POST['pagu_tersedia'], $this->_POST['pagu_id']);
			if ($updatePaguAnggaranUnit === true) {
				Messenger::Instance()->Send('pagu_anggaran_unit', 'paguAnggaranUnit', 'view', 'html', 
				array($this->_POST,'Perubahan Data Berhasil Dilakukan', $this->cssDone),Messenger::NextRequest);
			} else {
				Messenger::Instance()->Send('pagu_anggaran_unit', 'paguAnggaranUnit', 'view', 'html', 
				array($this->_POST,'Perubahan Data Gagal Dilakukan'.mysql_error(), $this->cssFail),Messenger::NextRequest);
			}
		} elseif($cek == "empty") {
			Messenger::Instance()->Send('pagu_anggaran_unit', 'inputPaguAnggaranUnit', 'view', 'html', 
			array($this->_POST,'Lengkapi Isian Data'),Messenger::NextRequest);
			return $this->pageInput . "&dataId=" . $this->encId;
		}
		return $this->pageView;
	}

	function Delete() {
		$arrId = $this->_POST['idDelete'];
		//print_r($this->_POST);
		$deleteArrData = $this->Obj->DoDeletePaguAnggaranUnitByArrayId($arrId);
		if($deleteArrData === true) {
			Messenger::Instance()->Send('pagu_anggaran_unit', 'paguAnggaranUnit', 'view', 'html', 
			array($this->_POST,'Penghapusan Data Berhasil Dilakukan', $this->cssDone),Messenger::NextRequest);
		} else {
			//jika masuk disini, berarti PASTI ada salah satu atau lebih data yang gagal dihapus
			for($i=0;$i<sizeof($arrId);$i++) {
				$deleteData = false;
				$deleteData = $this->Obj->DoDeletePaguAnggaranUnitById($arrId[$i]);
				if($deleteData === true) $sukses += 1;
				else $gagal += 1;
			}
			Messenger::Instance()->Send('pagu_anggaran_unit', 'paguAnggaranUnit', 'view', 'html', 
			array($this->_POST, $gagal . ' Data Tidak Dapat Dihapus.', $this->cssFail),Messenger::NextRequest);
		}
		return $this->pageView;
	}
	
	function Copy() {
	  if (isset($_POST['btnsimpan'])) {
      if($this->_POST['unitkerja'] && 
                $this->_POST['tahun_anggaran_tujuan'] && 
                    $this->_POST['tahun_anggaran_asal']) {
         if ($this->_POST['perubahan_pagu'] == 'Naik'){ 
            $copyPaguAnggaranUnit = $this->Obj->DoCopyPaguAnggaranUnitNaik($this->_POST['tahun_anggaran_tujuan'], 
            $this->_POST['persen_perubahan'], $this->_POST['tahun_anggaran_asal'], $this->_POST['unitkerja']);
         }else{
            $copyPaguAnggaranUnit = $this->Obj->DoCopyPaguAnggaranUnitTurun($this->_POST['tahun_anggaran_tujuan'],
             $this->_POST['persen_perubahan'], $this->_POST['tahun_anggaran_asal'], 
             $this->_POST['unitkerja']);
         }
			if ($copyPaguAnggaranUnit === true) {
				Messenger::Instance()->Send('pagu_anggaran_unit', 'paguAnggaranUnit', 'view', 'html', 
				array($this->_POST,'Proses Salin Pagu Anggaran Berhasil Dilakukan', 
				$this->cssDone),Messenger::NextRequest);
			} else {
				Messenger::Instance()->Send('pagu_anggaran_unit', 'paguAnggaranUnit', 'view', 'html', 
				array($this->_POST,'Proses Salin Pagu Anggaran Gagal', 
				$this->cssFail),Messenger::NextRequest);
			}
		} else{
			Messenger::Instance()->Send('pagu_anggaran_unit', 'copyPaguAnggaranUnit', 'view', 'html', 
			array($this->_POST,'Lengkapi Isian Data '),Messenger::NextRequest);
			return $this->pageCopy;
		}
	  }
		return $this->pageView;
	}
	

}
?>
