<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/referensi_mak/business/ReferensiMak.class.php';

class ProcessReferensiMak {

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
		$this->Obj = new ReferensiMak();
		$this->_POST = $_POST->AsArray();
		$this->decId = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
		$this->encId = Dispatcher::Instance()->Encrypt($this->decId);
		$this->pageView = Dispatcher::Instance()->GetUrl('referensi_mak', 'ReferensiMak', 'view', 'html');
		$this->pageInput = Dispatcher::Instance()->GetUrl('referensi_mak', 'inputReferensiMak', 'view', 'html');
	}

	function Check() {
		if (isset($_POST['btnsimpan'])) {
			if(trim($this->_POST['kode']) == "") {
				return "emptyKode";
			} elseif(trim($this->_POST['nama']) == "") {
				return "emptyNama";
			} elseif(trim($this->_POST['id_pagubas']) == "") {
				return "emptyIdPagubas";
			}elseif(strcmp(substr($this->_POST['kode_pagubas'],0,2),substr($this->_POST['kode'],0,2)) != 0){
			    return "codeNotMatch";
			}	else {
			    return true;
			}
		}
		return false;
	}
    
    public function _checkMakKode($basKode,$makKode)
    {
        $bas    = $basKode;
        
    }
    
	function Add() {
		$cek        = $this->Check();
		$kode_coa   = $this->_POST['kode_coa'];
		$id_coa     = $this->_POST['id_coa'];
		$nama_coa   = $this->_POST['nama_coa'];
		$mak_kode   = $this->_POST['kode'];
		$mak_nama   = $this->_POST['nama'];
		$kode_bas   = $this->_POST['kode_pagubas'];
		$id_bas     = $this->_POST['id_pagubas'];
		$status     = $this->_POST['status_aktif'];
		$nilai      = $this->_POST['nilai_default'];
		$bas_tipe   = $this->_POST['bas_tipe'];
		$userId     = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
		
		if($cek === true) {
			$add = $this->Obj->InsertMakIntoPaguBas($mak_kode,$id_bas,$nilai,$status,$mak_nama);
			
			if ($add === true) {
				$last_id = $this->Obj->GetLastPaguBasId();
				if($this->_POST['id_coa']){
					$this->Obj->InsertIntoCoaMak($id_coa,$last_id);
				}
				if($bas_tipe){
				    $this->Obj->InsertBasTipe($bas_tipe,$last_id);
				}
				Messenger::Instance()->Send('referensi_mak', 'ReferensiMak', 'view', 'html', 
					array($this->_POST,'Penambahan data Berhasil Dilakukan'.$mak_kode, $this->cssDone),
					Messenger::NextRequest);
			} else {
				Messenger::Instance()->Send('referensi_mak', 'ReferensiMak', 'view', 'html', 
					array($this->_POST,'Gagal Menambah Data', $this->cssFail),
					Messenger::NextRequest);
			}
		} elseif($cek == "emptyNama") {
				Messenger::Instance()->Send('referensi_mak', 'inputReferensiMak', 'view', 'html', 
					array($this->_POST,'Nama wajib diisi', $this->cssFail),
					Messenger::NextRequest);
				return $this->pageInput;
		} elseif($cek == "emptyKode") {
         	Messenger::Instance()->Send('referensi_mak', 'inputReferensiMak', 'view', 'html', 
				array($this->_POST,'Kode wajib diisi', $this->cssFail),
				Messenger::NextRequest);
         	return $this->pageInput;   
        }elseif($cek == "emptyIdPagubas") {
         	Messenger::Instance()->Send('referensi_mak', 'inputReferensiMak', 'view', 'html', 
	 			array($this->_POST,'Kode Pagu Bas wajib diisi', $this->cssFail),
			 	Messenger::NextRequest);
         	return $this->pageInput;   
		}elseif($cek == "codeNotMatch") {
         	Messenger::Instance()->Send('referensi_mak', 'inputReferensiMak', 'view', 'html', 
	 			array($this->_POST,'2 Digit awal kode MAK harus sama dengan Kode BAS yang Anda pilih', $this->cssFail),
			 	Messenger::NextRequest);
         	return $this->pageInput;
		}
		return $this->pageView;
	}

	function Update() {
		$cek = $this->Check();
		$kode_coa   = $this->_POST['kode_coa'];
		$id_coa     = $this->_POST['id_coa'];
		$nama_coa   = $this->_POST['nama_coa'];
		$mak_kode   = $this->_POST['kode'];
		$mak_nama   = $this->_POST['nama'];
		$kode_bas   = $this->_POST['kode_pagubas'];
		$id_bas     = $this->_POST['id_pagubas'];
		$status     = $this->_POST['status_aktif'];
		$nilai      = $this->_POST['nilai_default'];
		$bas_tipe   = $this->_POST['bas_tipe'];
		$userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
		if($cek === true) {
		    $update = $this->Obj->UpdateMakByBasId(
		                                          $mak_kode,
		                                          $id_bas,
		                                          $nilai,
		                                          $status,
		                                          $mak_nama,
		                                          $this->decId,
		                                          $id_coa,
		                                          $bas_tipe);
			if ($update === true) {
				Messenger::Instance()->Send('referensi_mak', 'ReferensiMak', 'view', 'html', 
					array($this->_POST,'Perubahan Data Berhasil Dilakukan', $this->cssDone),
					Messenger::NextRequest);
			} else {
				Messenger::Instance()->Send('referensi_mak', 'ReferensiMak', 'view', 'html', 
				array($this->_POST,'Perubahan Data Gagal Dilakukan', $this->cssFail),
				Messenger::NextRequest);
			}
		} elseif($cek == "emptyKode") {
				Messenger::Instance()->Send('referensi_mak', 'inputReferensiMak', 'view', 'html', 
					array($this->_POST,'Kode wajib diisi'),
					Messenger::NextRequest);
				return $this->pageInput . "&dataId=" . $this->encId;
		}  elseif($cek == "emptyNama") {
         		Messenger::Instance()->Send('referensi_mak', 'inputReferensiMak', 'view', 'html', 
				 	array($this->_POST,'Nama wajib diisi'),
					 Messenger::NextRequest);
         		return $this->pageInput . "&dataId=" . $this->encId;
      	}elseif($cek == "emptyIdPagubas") {
         	Messenger::Instance()->Send('referensi_mak', 'inputReferensiMak', 'view', 'html', 
			 	array($this->_POST,'Kode Pagu Bas wajib diisi'),
	 			Messenger::NextRequest);
         	return $this->pageInput;   
		}elseif($cek == "codeNotMatch") {
         	Messenger::Instance()->Send('referensi_mak', 'inputReferensiMak', 'view', 'html', 
	 			array($this->_POST,'2 Digit awal kode MAK harus sama dengan Kode BAS yang Anda pilih', $this->cssFail),
			 	Messenger::NextRequest);
         	return $this->pageInput;
		}
		return $this->pageView;
	}

	function Delete() {
		$arrId      = $this->_POST['idDelete'];
		$nameArr    = $this->_POST['nameDelete'];
		$count      = count($arrId);
		
		for ($i = 0; $i < $count; $i++)
		{
		    $delete = $this->Obj->DeleteMakPaguBasByBasId($arrId[$i]);
		    
		    if($delete){
		        $sukses[$i] = $nameArr[$i];
		    }else{
		        $gagal[$i]  = $nameArr[$i];
		    }
		}
		
		$pesan  = 'Data MAK dengan Kode <br />';
		$pesan  .= implode('<br />', $sukses);
		$pesan  .= '<br />Berhasil di hapus';
		
		if(isset($gagal)){
		    $style  = $this->cssFail;
		    $pesan  .= '<br />Data MAK dengan Kode <br />';
		    $pesan  .= implode('<br />',$gagal);
		    $pesan  .= '<br />Gagal di hapus';
		}else{
		    $style  = $this->cssDone;
		}
		Messenger::Instance()->Send('referensi_mak', 'ReferensiMak', 'view', 'html', 
		array($this->_POST,$pesan, $style),
		Messenger::NextRequest);
		
		return $this->pageView;
	}
}
?>