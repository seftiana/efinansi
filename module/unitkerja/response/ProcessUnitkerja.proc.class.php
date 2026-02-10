<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/unitkerja/business/AppUnitkerja.class.php';
class ProcessUnitkerja {

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
		$this->Obj = new AppUnitkerja();
		$this->_POST = $_POST->AsArray();
		$this->decId = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
		$this->encId = Dispatcher::Instance()->Encrypt($this->decId);
		$this->decJenis = Dispatcher::Instance()->Decrypt($_GET['jenis']);
		$this->encJenis = Dispatcher::Instance()->Encrypt($this->decJenis);
      if($this->decJenis == 'unit') $this->satker = '';
      else $this->satker = $this->_POST['satker'];
		$this->pageView = Dispatcher::Instance()->GetUrl('unitkerja', 'unitkerja', 'view', 'html');
		$this->pageInput = Dispatcher::Instance()->GetUrl('unitkerja', 'inputUnitkerja', 'view', 'html') . '&jenis=' . $this->encJenis;
	}

	function Check() {
		if (isset($_POST['btnsimpan'])) {
			if(trim($this->_POST['satker']) == "" && $this->decJenis == 'subunit') {
				return "empty";
			} elseif(trim($this->_POST['unitkerja_kode']) == "") {
				return "empty";
			} elseif(trim($this->_POST['unitkerja_nama']) == "") {
				return "empty";
			} elseif(trim($this->_POST['tipeunit']) == "") {
				return "empty";
			} elseif(trim($this->_POST['unitkerja_pimpinan']) == "") {
				return "empty";
			} else {
				return true;
			}
		}
		return false;
	}

	function Add() {
		$cek = $this->Check();
		if($cek === true) {
         
         

         $this->Obj->StartTrans();
         
			$addUnitkerja = $this->Obj->DoAddUnitkerja(
                                                    $this->_POST['unitkerja_pimpinan'], 
                                                    $this->_POST['unitkerja_kode'], 
                                                    $this->_POST['unitkerja_nama'], 
                                                    $this->_POST['tipeunit'], 
                                                    $this->_POST['statusunit'],
                                                    $this->satker);
			
			if(GTFWConfiguration::GetValue( 'application', 'aset')){
				$Obj2 = new AppUnitkerja(1);
				$Obj2->StartTrans();
				$addUnitkerja2 = $Obj2->DoAddUnitkerja(
                                                $this->_POST['unitkerja_pimpinan'], 
                                                $this->_POST['unitkerja_kode'], 
                                                $this->_POST['unitkerja_nama'], 
                                                $this->_POST['tipeunit'], 
                                                $this->_POST['statusunit'],
                                                $this->satker);
				$addData = $addUnitkerja && $addUnitkerja2 && $Obj2->addConn;
				$Obj2->EndTrans($addData);
			}else
				$addData = $addUnitkerja;
			
			$this->Obj->EndTrans($addData);
         
			
			
         
         
			if ($addData === true) {
				Messenger::Instance()->Send('unitkerja', 'unitkerja', 'view', 'html', array($this->_POST,'Penambahan data Berhasil Dilakukan', $this->cssDone),Messenger::NextRequest);
			} else {
			   
				Messenger::Instance()->Send('unitkerja', 'unitkerja', 'view', 'html', array($this->_POST,'Gagal Menambah Data', $this->cssFail),Messenger::NextRequest);
			}
		} elseif($cek == "empty") {
			Messenger::Instance()->Send('unitkerja', 'inputUnitkerja', 'view', 'html', array($this->_POST,'Lengkapi Isian Data'),Messenger::NextRequest);
			return $this->pageInput;
		}
		return $this->pageView;
	}

	function Update() {
		$cek = $this->Check();
		if($cek === true) {
		   $this->Obj->StartTrans();
			$updateUnitkerja = $this->Obj->DoUpdateUnitkerja(
                                                $this->_POST['unitkerja_pimpinan'], 
                                                $this->_POST['unitkerja_kode'], 
                                                $this->_POST['unitkerja_nama'], 
                                                $this->_POST['tipeunit'],
                                                 $this->_POST['statusunit'], 
                                                 $this->satker, 
                                                 $this->decId);
			if(GTFWConfiguration::GetValue( 'application', 'aset')){
				$Obj2 = new AppUnitkerja(1);
				$Obj2->StartTrans();
			   $updateUnitkerja2 = $Obj2->DoUpdateUnitkerja($this->_POST['unitkerja_pimpinan'], $this->_POST['unitkerja_kode'], $this->_POST['unitkerja_nama'], $this->_POST['tipeunit'], $this->_POST['statusunit'], $this->satker, $this->decId);
			   
        		$updateData = $updateUnitkerja && $updateUnitkerja2 && $Obj2->addConn;
        		$Obj2->EndTrans($updateData);
			}else
				$updateData = $updateUnitkerja;
			
			$this->Obj->EndTrans($updateData);
         
			   
			if ($updateData === true) {
			   
			   
				Messenger::Instance()->Send('unitkerja', 'unitkerja', 'view', 'html', array($this->_POST,'Perubahan Data Berhasil Dilakukan', $this->cssDone),Messenger::NextRequest);
			} else {
				Messenger::Instance()->Send('unitkerja', 'unitkerja', 'view', 'html', array($this->_POST,'Perubahan Data Gagal Dilakukan', $this->cssFail),Messenger::NextRequest);
			}
		} elseif($cek == "empty") {
			Messenger::Instance()->Send('unitkerja', 'inputUnitkerja', 'view', 'html', array($this->_POST,'Lengkapi Isian Data'),Messenger::NextRequest);
			return $this->pageInput . "&dataId=" . $this->encId;
		}
		return $this->pageView;
	}

	function Delete() {
		$arrId = $this->_POST['idDelete'];
		//print_r($this->_POST);
		$this->Obj->StartTrans();
		$deleteArrData = $this->Obj->DoDeleteUnitkerjaByArrayId($arrId);
		
		if(GTFWConfiguration::GetValue( 'application', 'aset')){
			$Obj2 = new AppUnitkerja(1);
			$deleteArrData2 = $Obj2->DoDeleteUnitkerjaByArrayId($arrId);
			
			$deleteData = $deleteArrData && $deleteArrData2 && $Obj2->addConn;
			$Obj2->EndTrans($deleteData);
		}
		else
			$deleteData = $deleteArrData;
			
      $this->Obj->EndTrans($deleteData);

		
		if($deleteData === true) {
		   
			Messenger::Instance()->Send('unitkerja', 'unitkerja', 'view', 'html', array($this->_POST,'Penghapusan Data Berhasil Dilakukan', $this->cssDone),Messenger::NextRequest);
		} else {
			//jika masuk disini, berarti PASTI ada salah satu atau lebih data yang gagal dihapus
			for($i=0;$i<sizeof($arrId);$i++) {
				$deleteData = false;
				$deleteData = $this->Obj->DoDeleteUnitkerjaById($arrId[$i]);
				if($deleteData === true) 
				{$sukses += 1;
               
               $return = $Obj->DoDeleteUnitkerjaById($arrId[$i]);
            }
				else {
					$gagal += 1;
					$sebab = $this->Obj->GetError();
				}
			}
			Messenger::Instance()->Send('unitkerja', 'unitkerja', 'view', 'html', array($this->_POST, $gagal . ' Data Tidak Dapat Dihapus.<br />' . $sebab, $this->cssFail),Messenger::NextRequest);
		}
		return $this->pageView;
	}
}
?>
