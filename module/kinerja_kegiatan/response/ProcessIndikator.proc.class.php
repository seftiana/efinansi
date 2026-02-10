<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/kinerja_kegiatan/business/KinerjaKegiatan.class.php';

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
	$this->Obj 		= new KinerjaKegiatan();
	$this->_POST 	= $_POST->AsArray();
	$this->decId 	= Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
	$this->encId 	= Dispatcher::Instance()->Encrypt($this->decId);
	$this->pageView = Dispatcher::Instance()->GetUrl('kinerja_kegiatan', 'indikatorKegiatan', 'view', 'html');
	$this->pageInput 	= Dispatcher::Instance()->
						  GetUrl('kinerja_kegiatan', 'inputIndikatorKegiatan', 'view', 'html');
}

function Check(){
	if (isset($_POST['btnsimpan'])){
		if(trim($this->_POST['kode']) == ""){
			return "emptyKode";
		}elseif(trim($this->_POST['nama']) == ""){
			return "emptyNama";
		}
/* sementara ini di hidden karena untuk real dilapangan sulit untuk me mapping dengan program yang ada di rkakl*/
/* 		elseif(trim($this->_POST['id_kegiatan']) == ""){
			return "emptyKegiatan";
		} */
		else return true;
	}
	return false;
}

function Add(){
	$cek = $this->Check();
	if($cek === true){
		$add = $this->Obj->AddKinerjaKegiatan($this->_POST['kode'],$this->_POST['nama'],
			   $this->_POST['id_kegiatan']);
		if ($add === true){
			Messenger::Instance()->Send('kinerja_kegiatan', 'indikatorKegiatan', 'view', 'html',
			array($this->_POST,'Penambahan data Berhasil Dilakukan', $this->cssDone),
			Messenger::NextRequest);
		}else{
			Messenger::Instance()->Send('kinerja_kegiatan', 'indikatorKegiatan', 'view', 'html', 
			array($this->_POST,'Gagal Menambah Data', $this->cssFail),
			Messenger::NextRequest);
		}
	}elseif($cek == "emptyNama"){
		Messenger::Instance()->Send('kinerja_kegiatan', 'inputIndikatorKegiatan', 'view', 'html', 
		array($this->_POST,'Nama wajib diisi'),
		Messenger::NextRequest);
		return $this->pageInput;
	}elseif($cek == "emptyKode"){
        Messenger::Instance()->Send('kinerja_kegiatan', 'inputIndikatorKegiatan', 'view', 'html', 
		array($this->_POST,'Kode wajib diisi'),
		Messenger::NextRequest);
        return $this->pageInput;
	}
	/* sementara ini di hidden karena untuk real dilapangan sulit untuk me mapping dengan program yang ada di rkakl*/
	/* elseif($cek == "emptyKegiatan"){
        Messenger::Instance()->Send('kinerja_kegiatan', 'inputIndikatorKegiatan', 'view', 'html', 
		array($this->_POST,'Pilih Kode Kegiatan yang sudah ada di popup'),
		Messenger::NextRequest);
        return $this->pageInput;   
    } */
	return $this->pageView;
}

function Update() {
	$cek = $this->Check();
	if($cek === true) {
		$update 	= $this->Obj->UpdateKinerjaKegiatan($this->_POST['kode'],
					  $this->_POST['nama'],$this->_POST['id_kegiatan'], $this->decId);
		if ($update === true){
			Messenger::Instance()->
			Send('kinerja_kegiatan', 'indikatorKegiatan', 'view', 'html', 
			array($this->_POST,'Perubahan Data Berhasil Dilakukan', 
			$this->cssDone),Messenger::NextRequest);
		}else{
			Messenger::Instance()->
			Send('kinerja_kegiatan', 'indikatorKegiatan', 'view', 'html', 
			array($this->_POST,'Perubahan Data Gagal Dilakukan', 
			$this->cssFail),Messenger::NextRequest);
		}
	}elseif($cek == "emptyKode"){
		Messenger::Instance()->
		Send('kinerja_kegiatan', 'inputIndikatorKegiatan', 'view', 'html', 
		array($this->_POST,'Kode wajib diisi'),Messenger::NextRequest);
		return $this->pageInput . "&dataId=" . $this->encId;
	}elseif($cek == "emptyNama"){
        Messenger::Instance()->
		Send('kinerja_kegiatan', 'inputIndikatorKegiatan', 'view', 'html', 
		array($this->_POST,'Nama wajib diisi'),Messenger::NextRequest);
        return $this->pageInput . "&dataId=" . $this->encId;
    }
	/* sementara ini di hidden karena untuk real dilapangan sulit untuk me mapping dengan program yang ada di rkakl*/
	/* elseif($cek == "emptyKegiatan"){
        Messenger::Instance()->Send('kinerja_kegiatan', 'inputIndikatorKegiatan', 'view', 'html', 
		array($this->_POST,'Pilih Kode Kegiatan yang sudah ada di popup'),
		Messenger::NextRequest);
        return $this->pageInput;
    } */
	return $this->pageView;
}

function Delete() {
	$arrId = $this->_POST['idDelete'];
	$deleteArrData = $this->Obj->DeleteKinerjaKegiatanByArrayId($arrId);
	if($deleteArrData === true){
		Messenger::Instance()->Send('kinerja_kegiatan', 'indikatorKegiatan', 'view', 'html',
		array($this->_POST,'Penghapusan Data Berhasil Dilakukan', 
		$this->cssDone),Messenger::NextRequest);
	}else{
		//jika masuk disini, berarti PASTI ada salah satu atau lebih data yang gagal dihapus
		for($i=0;$i<sizeof($arrId);$i++) {
		$deleteData = false;
		$deleteData = $this->Obj->DeleteKinerjaKegiatanById($arrId[$i]);
		if($deleteData === true) $sukses += 1;
		else $gagal += 1;
	}
		Messenger::Instance()->Send('kinerja_kegiatan', 'indikatorKegiatan', 'view', 'html', 
		array($this->_POST, $gagal . ' Data Tidak Dapat Dihapus.', 
		$this->cssFail),Messenger::NextRequest);
	}
	return $this->pageView;
}
}
?>
