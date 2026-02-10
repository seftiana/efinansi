<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
    'module/komponen/business/Komponen.class.php';

class ProsessKomponen {
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
		$this->Obj = new Komponen();
		$this->_POST = $_POST->AsArray();
		$this->decId = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
		$this->encId = Dispatcher::Instance()->Encrypt($this->decId);
        $nama = Dispatcher::Instance()->Decrypt($_REQUEST['nama']);
        
        if(isset($_REQUEST['page'])){
            $page       = (string) $_REQUEST['page']->StripHtmlTags()->SqlString()->Raw();
        }
        
        $page = ($page =='') ? '1' : $page;
        
		$this->pageView = Dispatcher::Instance()->GetUrl(
		  'komponen', 
		  'Komponen', 
		  'view', 
		  'html') . '&search='.Dispatcher::Instance()->Encrypt(1).'&nama='.Dispatcher::Instance()->Encrypt($nama).'&page='.$page;
		$this->pageInput = Dispatcher::Instance()->GetUrl(
		  'komponen', 
		  'inputKomponen', 
		  'view', 
		  'html').'&search='.Dispatcher::Instance()->Encrypt(1).'&nama='.Dispatcher::Instance()->Encrypt($nama).'&page='.$page;
        
	}

	function Check() {
		if (isset($_POST['btnsimpan'])) {
			if(trim($this->_POST['nama_komponen']) == "") {
				return "emptyNamaKomponen";
			} elseif(trim($this->_POST['nama_satuan']) == "") {
				return "emptyNamaSatuan";
			} elseif(trim($this->_POST['id_sumber_dana']=="" ) && trim($this->_POST['nama_sumber_dana']=="" )){
				return "emptySumberDana";
			} else return true;
		}
		return false;
	}

	function Add() {
		$cek = $this->Check();
		if($cek === true) {
			if($this->_POST['formula'] != "") {
				$f = strtolower($this->_POST['formula']);
				if(preg_match("/a-z/", $f) == 0) {
					eval("\$hasilformula = $f;");
				} else $hasilformula = 0;
			} else $hasilformula =0;

			if($this->_POST['id_coa'] == "") {
				$this->_POST['id_coa'] = NULL;
			}
			if($this->_POST['id_mak'] == "") {
				$this->_POST['id_mak'] = NULL;
			}
			//if($this->_POST['id_sumber_dana'] == "") {
			//	$this->_POST['id_sumber_dana'] = NULL;
			//}
			$add = $this->Obj->InsertKomponen(
                                    $this->_POST['nama_komponen'],
                                    $this->_POST['nama_satuan'],
                                    $this->_POST['deskripsi'],
                                    $this->_POST['formula'], 
                                    $this->_POST['id_coa'], 
                                    $this->_POST['harga_satuan'], 
                                    $this->_POST['coa_is_kas'], 
                                    $this->_POST['id_mak'], 
                                    $this->_POST['id_sumber_dana'], 
                                    $this->_POST['biaya1'], 
                                    $this->_POST['biaya2'], 
                                    $hasilformula,
                                    $this->_POST['kode_aset'],
                                    $this->_POST['pengadaan']);
			if ($add === true) {
				Messenger::Instance()->Send('komponen', 'Komponen', 'view', 'html', 
                        array($this->_POST,'Penambahan data Berhasil Dilakukan ', $this->cssDone),
                        Messenger::NextRequest);
			} else {
				Messenger::Instance()->Send('komponen', 'Komponen', 'view', 'html', 
                        array($this->_POST,'Gagal Menambah Data ', $this->cssFail),
                        Messenger::NextRequest);
			}
		} elseif($cek == "emptyNamaKomponen") {
			Messenger::Instance()->Send('komponen', 'inputKomponen', 'view', 'html', 
                        array($this->_POST,'Nama Komponen wajib diisi', $this->cssFail),
                        Messenger::NextRequest);
			return $this->pageInput;
		} elseif($cek == "emptyNamaSatuan") {
         Messenger::Instance()->Send('komponen', 'inputKomponen', 'view', 'html', 
                        array($this->_POST,'Nama Satuan wajib diisi', $this->cssFail),
                        Messenger::NextRequest);
         return $this->pageInput;   
         
		} elseif($cek == "emptySumberDana") {
         Messenger::Instance()->Send('komponen', 'inputKomponen', 'view', 'html', 
                        array($this->_POST,'Sumber Dana wajib diisi', $this->cssFail),
                        Messenger::NextRequest);
         return $this->pageInput;   
		}
		
		return $this->pageView;
	}

	function Update() {
		$cek = $this->Check();
		if($cek === true) {
			if($this->_POST['formula'] != "") {
				$f = strtolower($this->_POST['formula']);
				if(preg_match("/a-z/", $f) == 0) {
					eval("\$hasilformula = $f;");
				} else $hasilformula = 0;
			} else $hasilformula = 0;
			
			if($this->_POST['id_coa'] == "") {
				$this->_POST['id_coa'] = NULL;
			}
			if($this->_POST['id_mak'] == "") {
				$this->_POST['id_mak'] = NULL;
			}
		//	if($this->_POST['id_sumber_dana'] == "") {
		//		$this->_POST['id_sumber_dana'] = NULL;
		//	}
			$update = $this->Obj->UpdateKomponen(
                                    $this->_POST['nama_komponen'],
                                    $this->_POST['nama_satuan'],
                                    $this->_POST['deskripsi'],
                                    $this->_POST['formula'], 
                                    $this->_POST['id_coa'], 
                                    $this->_POST['harga_satuan'], 
                                    $this->_POST['coa_is_kas'], 
                                    $this->_POST['id_mak'], 
                                    $this->_POST['id_sumber_dana'], 
                                    $this->_POST['biaya1'], 
                                    $this->_POST['biaya2'], 
                                    $hasilformula,
                                    $this->_POST['kode_aset'],
                                    $this->_POST['pengadaan'], 
                                    $this->decId);
			if ($update === true) {
				Messenger::Instance()->Send('komponen', 'Komponen', 'view', 'html', 
                            array($this->_POST,'Perubahan Data Berhasil Dilakukan ', $this->cssDone),
                            Messenger::NextRequest);
			} else {
				Messenger::Instance()->Send('komponen', 'Komponen', 'view', 'html', 
                            array($this->_POST,'Perubahan Data Gagal Dilakukan ', $this->cssFail),
                            Messenger::NextRequest);
			}
		} elseif($cek == "emptyNamaSatuan") {
			 Messenger::Instance()->Send('komponen', 'inputKomponen', 'view', 'html', 
                            array($this->_POST,'Nama Satuan wajib diisi', $this->cssFail),
                            Messenger::NextRequest);
			return $this->pageInput . "&dataId=" . $this->encId;
		}  elseif($cek == "emptyNamaKomponen") {
			Messenger::Instance()->Send('komponen', 'inputKomponen', 'view', 'html', 
                            array($this->_POST,'Nama Komponen wajib diisi', $this->cssFail),
                            Messenger::NextRequest);
			return $this->pageInput . "&dataId=" . $this->encId;
		} elseif($cek == "emptySumberDana") {
			Messenger::Instance()->Send('komponen', 'inputKomponen', 'view', 'html', 
                        array($this->_POST,'Sumber Dana wajib diisi', $this->cssFail),
                        Messenger::NextRequest);
			return $this->pageInput . "&dataId=" . $this->encId;
		}
		return $this->pageView;
	}

	function Delete() {
		$arrId = $this->_POST['idDelete'];
		$deleteArrData = $this->Obj->DeleteKomponen($arrId);
		if($deleteArrData === true) {
			Messenger::Instance()->Send('komponen', 'Komponen', 'view', 'html', 
                        array($this->_POST,'Penghapusan Data Berhasil Dilakukan', $this->cssDone),
                        Messenger::NextRequest);
		} else {
			//jika masuk disini, berarti PASTI ada salah satu atau lebih data yang gagal dihapus
			for($i=0;$i<sizeof($arrId);$i++) {
				$deleteData = false;
				$deleteData = $this->Obj->DeleteKomponen($arrId[$i]);
				if($deleteData === true) $sukses += 1;
				else $gagal += 1;
			}
			Messenger::Instance()->Send('komponen', 'Komponen', 'view', 'html', 
                    array($this->_POST, $gagal . ' Data Tidak Dapat Dihapus.', $this->cssFail),
                    Messenger::NextRequest);
		}
		return $this->pageView;
	}

}