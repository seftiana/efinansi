<?php

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
        'module/kelompok_laporan/business/AppKlpLaporan.class.php';
        
class ProcessKlpLaporan {

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
		$this->Obj = new AppKelpLaporan();
		$this->_POST = $_POST->AsArray();
		$this->decId = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
		$this->encId = Dispatcher::Instance()->Encrypt($this->decId);
		$this->pageView = Dispatcher::Instance()->GetUrl('kelompok_laporan', 'KlpLaporan', 'view', 'html');
		$this->pageInput = Dispatcher::Instance()->GetUrl('kelompok_laporan', 'inputKlpLaporan', 'view', 'html');
	}

	function Check() {
		if (isset($_POST['btnsimpan'])) {
			if((trim($this->_POST['klp_lap']) == "") || trim($this->_POST['no_urutan']) == "" ){
				return "empty";
			}
             
            if(!ctype_digit($this->_POST['no_urutan'])){
               return "is_number";
            }
            
            return true;
		} elseif(isset($_POST['btnsimpan'])) {
      }
		return false;
	}

	function Add() {
		$cek = $this->Check();
		if($cek === true) {
			$addKlpLap = $this->Obj->DoAddData(
                                                    $this->_POST['klp_lap'], 
                                                    $this->_POST['is_tambah'], 
                                                    empty($this->_POST['bentuk_transaksi']) ? 
                                                            $this->_POST['jns_lap']:$this->_POST['bentuk_transaksi'],
                                                    $this->_POST['no_urutan']);
			if ($addKlpLap === true) {
				Messenger::Instance()->Send(
                                                'kelompok_laporan', 
                                                'KlpLaporan', 
                                                'view', 
                                                'html', 
                                                array(
                                                        $this->_POST,
                                                        'Penambahan data Berhasil Dilakukan', 
                                                        $this->cssDone),
                                                Messenger::NextRequest);
			} else {
				Messenger::Instance()->Send(
                                                'kelompok_laporan', 
                                                'KlpLaporan', 
                                                'view', 
                                                'html', 
                                                array(
                                                        $this->_POST,
                                                        'Gagal Menambah Data', 
                                                        $this->cssFail),
                                                Messenger::NextRequest);
			}
		} elseif($cek == "empty") {
			Messenger::Instance()->Send(
                                            'kelompok_laporan', 
                                            'inputKlpLaporan', 
                                            'view', 
                                            'html', 
                                            array(
                                                    $this->_POST,
                                                    'Lengkapi Isian Data'),
                                            Messenger::NextRequest);
			return $this->pageInput;
		} elseif($cek == "is_number") {
			Messenger::Instance()->Send(
                                            'kelompok_laporan', 
                                            'inputKlpLaporan', 
                                            'view', 
                                            'html', 
                                            array(
                                                    $this->_POST,
                                                    'No Urutan harus angka'),
                                            Messenger::NextRequest);
			return $this->pageInput;
		}
		return $this->pageView;
	}

	function Update() {
		$cek = $this->Check();
		if($cek === true) {
			$updateKlpLap = $this->Obj->DoUpdateData(
                                                        $this->_POST['klp_lap'], 
                                                        $this->_POST['is_tambah'], 
                                                        empty($this->_POST['bentuk_transaksi']) ? 
                                                              $this->_POST['jns_lap']:$this->_POST['bentuk_transaksi'],
                                                        $this->_POST['no_urutan'], 
                                                        $this->decId);
			if ($updateKlpLap === true) {
				Messenger::Instance()->Send(
                                                'kelompok_laporan', 
                                                'KlpLaporan', 
                                                'view', 
                                                'html', 
                                                array(
                                                        $this->_POST,
                                                        'Perubahan Data Berhasil Dilakukan', 
                                                        $this->cssDone),
                                                Messenger::NextRequest);
			} else {
				Messenger::Instance()->Send(
                                                'kelompok_laporan', 
                                                'KlpLaporan', 
                                                'view', 
                                                'html', 
                                                array(
                                                        $this->_POST,
                                                        'Perubahan Data Gagal Dilakukan', 
                                                        $this->cssFail),
                                                Messenger::NextRequest);
			}
		} elseif($cek == "empty") {
			Messenger::Instance()->Send(
                                            'kelompok_laporan', 
                                            'inputKlpLaporan', 
                                            'view', 
                                            'html', 
                                            array(
                                                    $this->_POST,
                                                    'Lengkapi Isian Data'),
                                            Messenger::NextRequest);
                                            
			return $this->pageInput . "&dataId=" . $this->encId;
		}  elseif($cek == "is_number") {
			Messenger::Instance()->Send(
                                            'kelompok_laporan', 
                                            'inputKlpLaporan', 
                                            'view', 
                                            'html', 
                                            array(
                                                    $this->_POST,
                                                    'No Urutan harus angka'),
                                            Messenger::NextRequest);
			return $this->pageInput . "&dataId=" . $this->encId;
		}
		return $this->pageView;
	}

	function Delete() {
		$arrId = $this->_POST['idDelete'];
		//print_r($this->_POST);
		$deleteArrData = $this->Obj->DoDeleteDataByArrayId($arrId);
		if($deleteArrData === true) {
			Messenger::Instance()->Send(
                                        'kelompok_laporan', 
                                        'KlpLaporan', 
                                        'view', 
                                        'html', 
                                        array(
                                                $this->_POST,
                                                'Penghapusan Data Berhasil Dilakukan', 
                                                $this->cssDone),
                                        Messenger::NextRequest);
		} else {
			//jika masuk disini, berarti PASTI ada salah satu atau lebih data yang gagal dihapus
			for($i=0;$i<sizeof($arrId);$i++) {
				$deleteData = false;
				$deleteData = $this->Obj->DoDeleteDataById($arrId[$i]);
				if($deleteData === true) $sukses += 1;
				else {
					$gagal += 1;
					$sebab = $this->Obj->GetError();
				}
			}
			Messenger::Instance()->Send(
                                        'kelompok_laporan', 
                                        'KlpLaporan', 
                                        'view', 
                                        'html', 
                                        array(
                                                $this->_POST, 
                                                $gagal . ' Data Tidak Dapat Dihapus<br />' . $sebab, 
                                        $this->cssFail),Messenger::NextRequest);
		}
		return $this->pageView;
	}
}
?>
