<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .'module/daftar_pinjaman/business/AppPinjaman.class.php';

class ProcessPinjaman{
	var $_POST;
	var $Obj;
	var $pageView;
	var $pageInput;
	var $pageImport;
	var $cssDone = "notebox-done";
	var $cssFail = "notebox-warning";

	var $return;
	var $decId;
	var $encId;

	function __construct() {   
		$this->Obj = new AppPinjaman();
      
		$this->_POST = $_POST->AsArray();
		$this->decId = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
		$this->encId = Dispatcher::Instance()->Encrypt($this->decId);
		$this->pageView = Dispatcher::Instance()->GetUrl('daftar_pinjaman', 'pinjaman', 'view', 'html');
		$this->pageInput = Dispatcher::Instance()->GetUrl('daftar_pinjaman', 'InputPinjaman', 'view', 'html');
	}
	
	function Check() {
		if (isset($_POST['btnsimpan'])) {
			$dataPinjaman=$this->Obj->GetDataPinjamanById($this->_POST['pinjaman_kode']);
			if((trim($this->_POST['pinjaman_kode']) == "") or (trim($this->_POST['pinjaman_nama']) == "")
				or (trim($this->_POST['pinjaman_jumlah']) == "") or (trim($this->_POST['pinjaman_angsuran']))==""){
				return "empty";
			}elseif((empty($this->decId)) and($dataPinjaman[0]['pinjaman_kode']===$this->_POST['pinjaman_kode'])){
				return "use";
			}elseif((!empty($this->decId)) and ($dataPinjaman[0]['pinjaman_kode']!=$this->decId) and ($dataPinjaman[0]['pinjaman_kode']===$this->_POST['pinjaman_kode'])){           
				return "use";           
			}else{
				return true;
			}
		}
		return false;
	}

	function Add(){
		$cek=$this->Check();
		//var_dump($this->_POST);exit;
		if($cek===true){
			$this->Obj->StartTrans();
			$addPinjaman=$this->Obj->DoAddPinjaman($this->_POST['pinjaman_kode'],strtoupper($this->_POST['pinjaman_nama']),$this->_POST['pinjaman_jumlah'],$this->_POST['pinjaman_angsuran']);
			$this->Obj->EndTrans($addPinjaman);     
         
			if($addPinjaman===true){
				Messenger::Instance()->Send('daftar_pinjaman', 'pinjaman', 'view', 'html', array($this->_POST,'Penambahan data Berhasil Dilakukan', $this->cssDone),Messenger::NextRequest);
			}else{
				Messenger::Instance()->Send('daftar_pinjaman', 'pinjaman', 'view', 'html', array($this->_POST,'Penambahan Data Gagal Dilakukan', $this->cssFail),Messenger::NextRequest);
			}
		}elseif($cek=="use"){
			Messenger::Instance()->Send('daftar_pinjaman', 'InputPinjaman', 'view', 'html', array($this->_POST,'Kode Pinjaman Sudah Digunakan'),Messenger::NextRequest);
			return $this->pageInput . "&dataId=" . $this->encId;
		}elseif($cek=="empty"){
			Messenger::Instance()->Send('daftar_pinjaman', 'InputPinjaman', 'view', 'html', array($this->_POST,'Lengkapi Isian Data'),Messenger::NextRequest);
			return $this->pageInput;
		}
		return $this->pageView;
	}   
 
	function Update(){
		$cek=$this->Check();
		if($cek===true){      
			$this->Obj->StartTrans();
			$updatePinjaman=$this->Obj->DoUpdatePinjaman($this->_POST['pinjaman_kode'],strtoupper($this->_POST['pinjaman_nama']),$this->_POST['pinjaman_jumlah'],$this->_POST['pinjaman_angsuran']);
			$this->Obj->EndTrans($updatePinjaman);
         
			if($updatePinjaman===true){
				Messenger::Instance()->Send('daftar_pinjaman', 'pinjaman', 'view', 'html', array($this->_POST,'Perubahan data Berhasil Dilakukan', $this->cssDone),Messenger::NextRequest);
			}else{
				Messenger::Instance()->Send('daftar_pinjaman', 'pinjaman', 'view', 'html', array($this->_POST,'Perubahan Data Gagal Dilakukan', $this->cssFail),Messenger::NextRequest);
			}
		}elseif($cek=="use"){
			Messenger::Instance()->Send('daftar_pinjaman', 'InputPinjaman', 'view', 'html', array($this->_POST,'Kode Pinjaman Sudah Digunakan'),Messenger::NextRequest);
			return $this->pageInput . "&dataId=" . $this->encId;
		}elseif($cek=="empty"){
			Messenger::Instance()->Send('daftar_pinjaman', 'InputPinjaman', 'view', 'html', array($this->_POST,'Lengkapi Isian Data'),Messenger::NextRequest);
			return $this->pageInput . "&dataId=" . $this->encId;
		}
		return $this->pageView;
	}

	function Delete(){
		$arrId = $this->_POST['idDelete'];
		
		$this->Obj->StartTrans();	 
		$deleteDataById=$this->Obj->DoDeletePinjamanById($arrId); 
		$this->Obj->EndTrans($deleteDataById);
	 
		if($deleteDataById==false){
			if(is_array($arrId)==false)
			   $arr[0]=$arrId;
			else
			   $arr=$arrId;
			$this->Obj->StartTrans();		
			$deleteArrData = $this->Obj->DoDeletePinjamanByArrayId($arr);
			$this->Obj->EndTrans($deleteArrData);
		}
		if($deleteArrData === true) {
			Messenger::Instance()->Send('daftar_pinjaman', 'pinjaman', 'view', 'html', array($this->_POST,'Penghapusan Data Berhasil Dilakukan', $this->cssDone),Messenger::NextRequest);
		}elseif($deleteDataById===true){
			Messenger::Instance()->Send('daftar_pinjaman', 'pinjaman', 'view', 'html', array($this->_POST,'Penghapusan Data Berhasil Dilakukan', $this->cssDone),Messenger::NextRequest);
		}else{
			//jika masuk disini, berarti PASTI ada salah satu atau lebih data yang gagal dihapus
			for($i=0;$i<sizeof($arr);$i++) {		
				$deleteData = false;		   
				$this->Obj->StartTrans();
				$deleteData = $this->Obj->DoDeletePinjamanById($arr[$i]);
				$this->Obj->EndTrans($deleteData);
			   
				if($deleteData === true) $sukses += 1;
				else $gagal += 1;
			}
			Messenger::Instance()->Send('daftar_pinjaman', 'pinjaman', 'view', 'html', array($this->_POST, $gagal . ' Data Tidak Dapat Dihapus.', $this->cssFail),Messenger::NextRequest);
		}
		return $this->pageView;

	}
}
?>
