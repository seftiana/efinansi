<?php

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
    'module/jumlah_kelas_per_unit/business/JumlahKelasPerUnit.class.php';

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
    'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ProcessJumlahKelasPerUnit
{

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

	function __construct() 
	{
		$this->Obj = new JumlahKelasPerUnit;
		$this->_POST = $_POST->AsArray();
		$this->decId = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
		$this->encId = Dispatcher::Instance()->Encrypt($this->decId);
		$this->pageView = Dispatcher::Instance()->GetUrl('jumlah_kelas_per_unit', 'JumlahKelasPerUnit', 'view', 'html');
		$this->pageInput = Dispatcher::Instance()->GetUrl('jumlah_kelas_per_unit', 'inputJumlahKelasPerUnit', 'view', 'html');
		$userUnitKerja = new UserUnitKerja();
		$userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
		$this->Role = $userUnitKerja->GetRoleUser($userId);
	}

	function Check() {
		//print_r($this->_POST);
		if(!$this->_POST['unitkerja'] || trim($this->_POST['unitkerja']=='-')) 
		$this->_POST['unitkerja'] = $this->_POST['satker'];
		if (isset($_POST['btnsimpan'])) {/*
			    if($this->_POST['unitkerja'] == '') {
					return "empty";			
				} elseif(trim($this->_POST['jumlah_kelas'])==""  || trim($this->_POST['jumlah_kelas'])== 0 ) {
					return "empty";				
				} else {*/
				    if(($this->_POST['unitkerja'] !== $this->_POST['unitkerja_old']) || 
                       ($this->_POST['tahun_anggaran_old'] !== $this->_POST['tahun_anggaran'])) {
                            $cekRowData = $this->Obj->GetCountRowData($this->_POST['tahun_anggaran'], $this->_POST['unitkerja']);               
                            if($cekRowData > 0) {
                                return "exist";             
                            } else {
                                return true;
                            } 
                    } else {
                        return true;
                    }
              // }
		}
		return false;
	}

	function Add() {
		$cek = $this->Check();
		if($cek === true) {
			$addJumlahKelasPerUnit = $this->Obj->DoAddJumlahKelasPerUnit(
			     $this->_POST['tahun_anggaran'], 
			     $this->_POST['unitkerja'], 
			     $this->_POST['jumlah_kelas'],
			     $this->_POST['prodi_kelas_gasal'],
			     $this->_POST['prodi_kelas_genap'],
			     $this->_POST['sgasal'],
			     $this->_POST['sgenap']
            );
			$cekRowData = $this->Obj->GetCountRowData($this->_POST['tahun_anggaran'], $this->_POST['unitkerja']);
			if ($addJumlahKelasPerUnit === true) {
				Messenger::Instance()->Send('jumlah_kelas_per_unit', 'JumlahKelasPerUnit', 'view', 'html', 
				array($this->_POST,'Penambahan data Berhasil Dilakukan', 
				$this->cssDone),Messenger::NextRequest);
			} else {
				Messenger::Instance()->Send('jumlah_kelas_per_unit', 'JumlahKelasPerUnit', 'view', 'html', 
				array($this->_POST,'Gagal Menambah Data', $this->cssFail),Messenger::NextRequest);
			}
		} elseif($cek == "empty") {
			Messenger::Instance()->Send('jumlah_kelas_per_unit', 'inputJumlahKelasPerUnit', 'view', 'html', 
			array($this->_POST,'Lengkapi Isian Data'),Messenger::NextRequest);
			return $this->pageInput;
		} elseif($cek == "exist") {
            Messenger::Instance()->Send('jumlah_kelas_per_unit', 'inputJumlahKelasPerUnit', 'view', 'html', 
            array($this->_POST,'Data sudah ada'),Messenger::NextRequest);
            return $this->pageInput;
        }
		return $this->pageView;
	}

	function Update() {
		$cek = $this->Check();
        
		if($cek === true) {			
			$addJumlahKelasPerUnit = $this->Obj->DoUpdateJumlahKelasPerUnit(
			     $this->_POST['tahun_anggaran'], 
                 $this->_POST['unitkerja'], 
                 $this->_POST['jumlah_kelas'],
                 $this->_POST['prodi_kelas_gasal'],
                 $this->_POST['prodi_kelas_genap'],
                 $this->_POST['sgasal'],
                 $this->_POST['sgenap'],
                 $this->_POST['jumlah_kelas_id'] 
            );
            
			if ($addJumlahKelasPerUnit === true) {
				Messenger::Instance()->Send('jumlah_kelas_per_unit', 'JumlahKelasPerUnit', 'view', 'html', 
				array($this->_POST,'Perubahan Data Berhasil Dilakukan', $this->cssDone),Messenger::NextRequest);
			} else {
				Messenger::Instance()->Send('jumlah_kelas_per_unit', 'JumlahKelasPerUnit', 'view', 'html', 
				array($this->_POST,'Perubahan Data Gagal Dilakukan', $this->cssFail),Messenger::NextRequest);
			}
		} elseif($cek == "empty") {
			Messenger::Instance()->Send('jumlah_kelas_per_unit', 'inputJumlahKelasPerUnit', 'view', 'html', 
			array($this->_POST,'Lengkapi Isian Data'),Messenger::NextRequest);
			return $this->pageInput . "&dataId=" . $this->encId;
		}elseif($cek == "exist") {
            Messenger::Instance()->Send('jumlah_kelas_per_unit', 'inputJumlahKelasPerUnit', 'view', 'html', 
            array($this->_POST,'Data sudah ada'),Messenger::NextRequest);
            return $this->pageInput;
        }
		return $this->pageView;
	}

	function Delete() {
		$arrId = $this->_POST['idDelete'];
		//print_r($this->_POST);
		$deleteArrData = $this->Obj->DoDeleteJumlahKelasPerUnitByArrayId($arrId);
		if($deleteArrData === true) {
			Messenger::Instance()->Send('jumlah_kelas_per_unit', 'JumlahKelasPerUnit', 'view', 'html', 
			array($this->_POST,'Penghapusan Data Berhasil Dilakukan', $this->cssDone),Messenger::NextRequest);
		} else {
			//jika masuk disini, berarti PASTI ada salah satu atau lebih data yang gagal dihapus
			for($i=0;$i<sizeof($arrId);$i++) {
				$deleteData = false;
				$deleteData = $this->Obj->DoDeleteJumlahKelasPerUnitById($arrId[$i]);
				if($deleteData === true) $sukses += 1;
				else $gagal += 1;
			}
			Messenger::Instance()->Send('jumlah_kelas_per_unit', 'JumlahKelasPerUnit', 'view', 'html', 
			array($this->_POST, $gagal . ' Data Tidak Dapat Dihapus.', $this->cssFail),Messenger::NextRequest);
		}
		return $this->pageView;
	}
	

}
?>