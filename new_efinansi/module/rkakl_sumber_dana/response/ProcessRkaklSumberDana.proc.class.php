<?php

/**
 * Class ProcessRkaklSumberDana
 * untuk menangani proses manipulasi data
 * @copyright 2011 gamatechno
 */
 
 /**
  * sertakan file class untuk menangani proses bisnis
  */
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
 	'module/rkakl_sumber_dana/business/RkaklSumberDana.class.php';

/**
 * Class ProcessRkaklSumberDana
 * untuk menangani proses requst manipulasi data ( simpan,hapus,update )
 */
class ProcessRkaklSumberDana {

	/**
	 * Variable 
	 */
	 
	protected $_POST;
	protected $Obj;
	protected $pageView;
	protected $pageInput;
	//css hanya dipake di view
	protected $cssDone = "notebox-done";
	protected $cssFail = "notebox-warning";

	//protected $return;
	protected $decId;
	protected $encId;
	
	 
	public function __construct() 
	{
		$this->Obj = new RkaklSumberDana();
		$this->_POST = $_POST->AsArray();
		$this->decId = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
		$this->encId = Dispatcher::Instance()->Encrypt($this->decId);
		$this->pageView = Dispatcher::Instance()->GetUrl('rkakl_sumber_dana', 'RkaklSumberDana',
		                      'view', 'html');
		$this->pageInput = Dispatcher::Instance()->GetUrl('rkakl_sumber_dana',
		               'inputRkaklSumberDana', 'view', 'html');
	}

	/**
	 * function Check
	 * untuk melakukan validasi input data, input dalam kondisi kosong atau tidak
	 */
	private function Check() 
	{
		if (isset($_POST['btnsimpan'])) {
			if(trim($this->_POST['sumber_dana_nama']) == "") {
				return "emptyNama";
			} else return true;
		}
		return false;
	}
	
	/**
	 * function Add()
	 * untuk melkukan proses simpan data
	 */
	public function Add() 
	{
		$cek = $this->Check();
		if($cek === true) {
			$add = $this->Obj->AddRkaklSumberDana($this->_POST['sumber_dana_nama'],
			                   $this->_POST['is_aktif']);
			if ($add === true) {
					Messenger::Instance()->Send('rkakl_sumber_dana', 'RkaklSumberDana', 'view', 'html', 
					array($this->_POST,'Penambahan data Berhasil Dilakukan', $this->cssDone),
					Messenger::NextRequest);
			} else {
					Messenger::Instance()->Send('rkakl_sumber_dana', 'RkaklSumberDana', 'view', 'html',
				 	array($this->_POST,'Gagal Menambah Data', $this->cssFail),
				 	Messenger::NextRequest);
			}
		} elseif($cek == "emptyNama") {
			Messenger::Instance()->Send('rkakl_sumber_dana', 'inputRkaklSumberDana', 'view', 'html',
		 	array($this->_POST,'Sumber dana wajib diisi'),Messenger::NextRequest);
			return $this->pageInput;
      	}
		return $this->pageView;
	}

	/**
	 * function Update()
	 * untuk melakukan proses update data
	 */
	public function Update() 
	{
		$cek = $this->Check();
		if($cek === true) {
			$update = $this->Obj->UpdateRkaklSumberDana($this->_POST['sumber_dana_nama'],
					$this->_POST['is_aktif'], $this->decId);
			if ($update === true) {
				Messenger::Instance()->Send('rkakl_sumber_dana', 'RkaklSumberDana', 'view', 'html', 
				array($this->_POST,'Perubahan Data Berhasil Dilakukan', $this->cssDone),
				Messenger::NextRequest);
			} else {
				Messenger::Instance()->Send('rkakl_sumber_dana', 'RkaklSumberDana', 'view', 'html', 
				array($this->_POST,'Perubahan Data Gagal Dilakukan', $this->cssFail),
				Messenger::NextRequest);
			}
		} elseif($cek == "emptyNama") {
         	Messenger::Instance()->Send('rkakl_sumber_dana', 'inputRkaklSumberDana', 'view', 'html', 
	 		array($this->_POST,'Sumber dana wajib diisi'),Messenger::NextRequest);
         	return $this->pageInput . "&dataId=" . $this->encId;
      	}
		return $this->pageView;
	}

	/**
	 * function Delete
	 * untuk melakukan proses hapus data
	 */
	public function Delete() 
	{
		$arrId = $this->_POST['idDelete'];
		$deleteArrData = $this->Obj->DeleteRkaklSumberDanaByArrayId($arrId);
		if($deleteArrData === true) {
			Messenger::Instance()->Send('rkakl_sumber_dana', 'RkaklSumberDana', 'view', 'html', 
			array($this->_POST,'Penghapusan Data Berhasil Dilakukan', $this->cssDone),
			Messenger::NextRequest);
		} else {
			/**
			 * jikalau ada data yang tidak bisa dihapus maka akan menjalankan
			 * kode dibawah ini ( hapus data satu persatu dengan menggunakan for )
			 */
			for($i=0;$i<sizeof($arrId);$i++) {
				$deleteData = false;
				$deleteData = $this->Obj->DeleteRkaklSumberDanaById($arrId[$i]);
				if($deleteData === true) $sukses += 1;
				else $gagal += 1;
			}
			Messenger::Instance()->Send('rkakl_sumber_dana', 'RkaklSumberDana', 'view', 'html', 
			array($this->_POST, $gagal . ' Data Tidak Dapat Dihapus.', $this->cssFail),
			Messenger::NextRequest);
		}
		return $this->pageView;
	}
}