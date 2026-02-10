<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
'module/kinerja_utama/business/KinerjaUtama.class.php';

class ProcessIndikator {

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
	$this->Obj 		= new KinerjaUtama();
	$this->_POST 	= $_POST->AsArray();
	$this->decId 	= Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
	$this->encId 	= Dispatcher::Instance()->Encrypt($this->decId);
	$this->pageView = Dispatcher::Instance()->
					  GetUrl('kinerja_utama', 'indikatorUtama', 'view', 'html');
	$this->pageInput = Dispatcher::Instance()->
					   GetUrl('kinerja_utama', 'inputIndikatorUtama', 'view', 'html');
}

function Check() {
	if(isset($_POST['btnsimpan'])){
		if(trim($this->_POST['kode']) == ""){
			return "emptyKode";
		}elseif(trim($this->_POST['nama']) == ""){
			return "emptyNama";
		}
		/* sementara ini di hidden karena untuk real dilapangan sulit untuk me mapping dengan program yang ada di rkakl*/
		/* elseif(trim($this->_POST['id_program']) == ""){
			return "emptyProgram";
		} */
		else{
			return true;	
		}
	}
	return false;
}

function Add() {
	$cek = $this->Check();
	if($cek === true) {
		$add = $this->Obj->AddKinerjaUtama
		($this->_POST['kode'],$this->_POST['nama'],$this->_POST['id_program']);
					
		if($add === true) {
			Messenger::Instance()->
			Send('kinerja_utama', 'indikatorUtama', 'view', 'html', 
			array($this->_POST,'Penambahan data Berhasil Dilakukan', 
			$this->cssDone),Messenger::NextRequest);
		}else {
			Messenger::Instance()->
			Send('kinerja_utama', 'indikatorUtama', 'view', 'html', 
			array($this->_POST,'Gagal Menambah Data', 
			$this->cssFail),Messenger::NextRequest);
		}
	}elseif($cek == "emptyNama") {
		Messenger::Instance()->
		Send('kinerja_utama', 'inputIndikatorUtama', 'view', 'html', 
		array($this->_POST,'Nama wajib diisi'),Messenger::NextRequest);
		return $this->pageInput;
	}elseif($cek == "emptyKode") {
		Messenger::Instance()->
		Send('kinerja_utama', 'inputIndikatorUtama', 'view', 'html', 
		array($this->_POST,'Kode wajib diisi'),Messenger::NextRequest);
		return $this->pageInput;
	}
	/* sementara ini di hidden karena untuk real dilapangan sulit untuk me mapping dengan program yang ada di rkakl*/
	/* elseif($cek == "emptyProgram") {
		Messenger::Instance()->
		Send('kinerja_utama', 'inputIndikatorUtama', 'view', 'html', 
		array($this->_POST,'Pilih Program'),Messenger::NextRequest);
		return $this->pageInput;   
	} */
	return $this->pageView;
}

	function Update() {
		$cek = $this->Check();
		if($cek === true) {
			//$update = $this->Obj->UpdateKinerjaUtama(
			//$this->_POST['kode'],$this->_POST['nama'],$this->_POST['id_program'], $this->decId);
			//if ($update === true) {
			//	Messenger::Instance()->
			//	Send('kinerja_utama', 'indikatorUtama', 'view', 'html', 
			//	array($this->_POST,'Perubahan Data Berhasil Dilakukan', 
			//	$this->cssDone),Messenger::NextRequest);
			//} else {
			//	Messenger::Instance()->
			//	Send('kinerja_utama', 'indikatorUtama', 'view', 'html', 
			//	array($this->_POST,'Perubahan Data Gagal Dilakukan', 
			//	$this->cssFail),Messenger::NextRequest);
			//}
			$update	= $this->Obj->UpdateKinerjaUtama
					  ($this->_POST['kode'],$this->_POST['nama'],$this->_POST['id_program'], 
					  $this->decId);
			if($update):
				Messenger::Instance()->
				Send('kinerja_utama','indikatorUtama','view','html',
				array($this->_POST,'Perubahan Data berhasil di lakukan',
				$this->cssDone),Messenger::NextRequest);
			else:
				Messenger::Instance()->
				Send('kinerja_utama','indikatorUtama','view','html',
				array($this->_POST,'Perubahan Data Gagal di lakukan',
				$this->cssFail),Messenger::NextRequest);
			endif;
		//Messenger::Instance()->
		//Send('kinerja_utama', 'inputIndikatorUtama', 'view', 'html', 
		//array($this->_POST,'Semua Form Terisi penuh'),Messenger::NextRequest);
		//return $this->pageInput;
		
	}elseif($cek == "emptyNama") {
		Messenger::Instance()->
		Send('kinerja_utama', 'inputIndikatorUtama', 'view', 'html', 
		array($this->_POST,'Nama wajib diisi'),Messenger::NextRequest);
		return $this->pageInput;
	}elseif($cek == "emptyKode") {
		Messenger::Instance()->
		Send('kinerja_utama', 'inputIndikatorUtama', 'view', 'html', 
		array($this->_POST,'Kode wajib diisi'),Messenger::NextRequest);
		return $this->pageInput;
	}
	/* sementara ini di hidden karena untuk real dilapangan sulit untuk me mapping dengan program yang ada di rkakl*/
	/* elseif($cek == "emptyProgram") {
		Messenger::Instance()->
		Send('kinerja_utama', 'inputIndikatorUtama', 'view', 'html', 
		array($this->_POST,'Pilih Program'),Messenger::NextRequest);
		return $this->pageInput;   
	} */
		return $this->pageView;
	}

	function Delete() {
		$arrId = $this->_POST['idDelete'];
		$deleteArrData = $this->Obj->DeleteKinerjaUtamaByArrayId($arrId);
		if($deleteArrData === true) {
			Messenger::Instance()->Send('kinerja_utama', 'indikatorUtama', 'view', 'html', array($this->_POST,'Penghapusan Data Berhasil Dilakukan', $this->cssDone),Messenger::NextRequest);
		} else {
			//jika masuk disini, berarti PASTI ada salah satu atau lebih data yang gagal dihapus
			for($i=0;$i<sizeof($arrId);$i++) {
				$deleteData = false;
				$deleteData = $this->Obj->DeleteKinerjaUtamaById($arrId[$i]);
				if($deleteData === true) $sukses += 1;
				else $gagal += 1;
			}
			Messenger::Instance()->Send('kinerja_utama', 'indikatorUtama', 'view', 'html', array($this->_POST, $gagal . ' Data Tidak Dapat Dihapus.', $this->cssFail),Messenger::NextRequest);
		}
		return $this->pageView;
	}
}
?>
