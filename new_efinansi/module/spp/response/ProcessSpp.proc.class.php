<?php
require_once GTFWConfiguration::GetValue('application','docroot').
'module/spp/business/spp.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ProcessSpp{
	public $_POST;
	public $obj;
	protected $userunitkerja;
	public $cssDone	= "notebox-done";
	public $cssFail	= "notebox-warning";
	
	public $pageInput;
	public $pageView;
	
	public $decId;
	public $encId;
	
	function __construct(){
		$this->_POST	= $_POST->AsArray();
		$this->obj		= new Spp();
		$this->userunitkerja	= new UserUnitKerja();
		
		$this->pageInput	= Dispatcher::Instance()->GetUrl('spp','AddSpp','view','html');
		$this->pageView		= Dispatcher::Instance()->GetUrl('spp','ListSpp','view','html');
		
		$this->decId		= Dispatcher::Instance()->Decrypt($_GET['id']);
		$this->encId		= Dispatcher::Instance()->Encrypt($this->decId);
	}
	
	function check(){
		$keperluan		= trim($this->_POST['keperluan']);
		$jenis_belanja	= trim($this->_POST['jenis_belanja']);
		$nama			= trim($this->_POST['nama']);
		$sifat_bayar	= $this->_POST['sifat_bayar'];
		$jenis_bayar	= $this->_POST['jenis_bayar'];
		if(isset($this->_POST['btnsimpan'])){
			if($keperluan == '' OR $jenis_belanja == '' OR $nama == '' 
				OR $sifat_bayar == '' OR $jenis_bayar == ''){
				return 'emptyData';
			}else{
				return true;
			}
		}else{
			return $this->pageView;
		}
	}
	
	function Add(){
		$keperluan		= trim($this->_POST['keperluan']);
		$jenis_belanja	= trim($this->_POST['jenis_belanja']);
		$nama			= trim($this->_POST['nama']);
		$alamat			= trim($this->_POST['alamat']);
		$rekening		= trim($this->_POST['rekening']);
		$no_spk			= trim($this->_POST['no_spk']);
		$nilai_spk		= trim($this->_POST['nilai_spk']);
		$dana			= $this->_POST['dana'];
		$userId 		= trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
		$id_pengeluaran	= $this->_POST['id_pengeluaran'];
		$sifat_bayar	= $this->_POST['sifat_bayar'];
		$jenis_bayar	= $this->_POST['jenis_bayar'];
		$nomor			= $this->obj->GetLastNumb();
		// $ta_id			= $this->_POST['ta_id'];
		
		$check			= $this->check();
		if($check === true){
			$add	= $this->obj->InputSpp($nomor,$sifat_bayar,$jenis_bayar,
			$keperluan,$jenis_belanja,$nama,$alamat,$rekening,$nilai_spk,$dana,$userId);
			$last_id	= $this->obj->GetLastId();
			if($add){
				if($this->obj->InsertSppDetail($last_id,$id_pengeluaran,$dana,$userId)){
					//proses penyimpanan data berhasil di jalankan
					Messenger::Instance()->Send('spp', 'ListSpp', 'view', 'html', 
					array('Penyimpanan data berhasil di laksanakan', $this->cssDone),
					Messenger::NextRequest);
					return $this->pageView;  
				}else{
					// gagal melakukan penyimpanan data
					$this->obj->DeleteSpp($last_id);
					Messenger::Instance()->Send('spp', 'AddSpp', 'view', 'html', 
					array($this->_POST,'Penyimpanan Data gagal dilaksanakan', $this->cssFail),
					Messenger::NextRequest);
					return $this->pageInput;  
				}
			}else{
				// gagal menyimpan data ke dalam database
				$this->obj->DeleteSpp($last_id);
				Messenger::Instance()->Send('spp', 'AddSpp', 'view', 'html', 
				array($this->_POST,'Penyimpanan Data gagal dilaksanakan', $this->cssFail),
				Messenger::NextRequest);
				return $this->pageInput;  	
			}
		}else if($check == 'emptyData'){
			// data isian tidak lengkap
			$this->obj->DeleteSpp($last_id);
			Messenger::Instance()->Send('spp', 'AddSpp', 'view', 'html', 
			array($this->_POST,'Data kurang lengkap', $this->cssFail),
			Messenger::NextRequest);
			return $this->pageInput;  
		}else{
			return $this->pageView;
		}
		
	}
}
?>