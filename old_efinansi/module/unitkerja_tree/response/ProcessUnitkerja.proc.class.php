<?php

require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
         'module/unitkerja_tree/business/AppUnitkerja.class.php';
         
class ProcessUnitkerja 
{
	protected $_POST;
	protected $Obj;
	protected $pageView;
	protected $pageInput;
    protected $pageListCari;
    protected $p;
	//css hanya dipake di view
	protected $cssDone = "notebox-done";
	protected $cssFail = "notebox-warning";

	protected $return;
	protected $decId;
	protected $encId;

	public function __construct() 
    {
		$this->Obj = new AppUnitkerja();
		$this->_POST = $_POST->AsArray();
		$this->decId = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
		$this->encId = Dispatcher::Instance()->Encrypt($this->decId);
		$this->decJenis = Dispatcher::Instance()->Decrypt($_GET['jenis']);
		$this->encJenis = Dispatcher::Instance()->Encrypt($this->decJenis);
        $parentId = Dispatcher::Instance()->Decrypt($_GET['parentUnitId']);
        $this->p = Dispatcher::Instance()->Decrypt($_GET['p']);
        if($parentId != ''){
            $url_parent_id = '&parentUnitId='.Dispatcher::Instance()->Encrypt($parentId);
        } 
        if($this->p != ''){
            $url_p = '&p='.Dispatcher::Instance()->Encrypt($this->p);
        }
        if($this->decJenis == 'unit') {
            $this->satker = '';   
        } else {
            $this->satker = $this->_POST['satker'];
        }
        $this->pageListCari = Dispatcher::Instance()->GetUrl('unitkerja_tree', 'unitkerjaCari', 'view', 'html');
		$this->pageView = Dispatcher::Instance()->GetUrl(
                                                            'unitkerja_tree', 
                                                            'unitkerja', 
                                                            'view', 
                                                            'html');
		$this->pageInput = Dispatcher::Instance()->GetUrl(
                                                            'unitkerja_tree', 
                                                            'inputUnitkerja', 
                                                            'view', 
                                                            'html') . 
                                                            '&jenis=' . 
                                                            $this->encJenis . 
                                                            $url_parent_id.
                                                            $url_p;
	}

	public function Check() 
    {
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

	public function Add() 
    {
		$cek = $this->Check();
		if($cek === true) {
         
			$addUnitkerja = $this->Obj->DoAddUnitkerja(
                                                    $this->_POST['unitkerja_pimpinan'], 
                                                    $this->_POST['unitkerja_kode'], 
                                                    $this->_POST['unitkerja_nama'], 
                                                    $this->_POST['tipeunit'], 
                                                    $this->_POST['statusunit'],
                                                    $this->satker);
		
			$addData = $addUnitkerja;
		
			if (($addData['dbResult'] === true)  && ($addData['serviceResult'] =='dataSend')){
				$message = 'Penambahan data Berhasil Dilakukan. Data Teririm Ke Service';
                $status ='done';
			} elseif (($addData['dbResult'] === true)){
			    $message = 'Penambahan data Berhasil Dilakukan';
                $status ='done';
			} elseif(($addData['dbResult'] === false)  && ($addData['serviceResult']=='urlNotSet')) {
				$message ='Gagal Melakukan Tambah Data. Service Tidak ditemukan.';
                $status = 'fail';
			} elseif(($addData['dbResult'] === false) && ($addData['serviceResult'] =='dataNotSend')) {
				$message ='Gagal Melakukan Tambah Data. Data Tidak Terkirim Ke Service.';
                $status = 'fail';
			} else {
			    $message = 'Gagal Melakukan Tambah Data. ';
                $status = 'fail';
			}		
						
		} elseif($cek == "empty") {
		    $this->ShowMessage('inputUnitkerja','Lengkapi Isian Data','fail');			
			return $this->pageInput;
		}
        
        if($this->p == 'list'){
            $this->ShowMessage('UnitkerjaCari',$message,$status);
            return $this->pageListCari;    
        } else {
            $this->ShowMessage('Unitkerja',$message,$status);
            return $this->pageView;
        }
		
	}

	public function Update() 
    {
		$cek = $this->Check();
		if($cek === true) {
		   
			$updateUnitkerja = $this->Obj->DoUpdateUnitkerja(
                                                 $this->_POST['unitkerja_pimpinan'], 
                                                 $this->_POST['unitkerja_kode'], 
                                                 $this->_POST['unitkerja_nama'], 
                                                 $this->_POST['tipeunit'],
                                                 $this->_POST['statusunit'], 
                                                 $this->satker, 
                                                 $this->decId);
			
			$updateData = $updateUnitkerja;			
			//print_r($updateData);
			if (($updateData['dbResult'] === true)  && ($updateData['serviceResult'] =='dataSend')) {
				$message ='Perubahan Data Berhasil Dilakukan. Data Terkirim Ke Service';
                $status = 'done';
			} elseif(($updateData['dbResult'] === true)) {
			   $message ='Perubahan Data Berhasil Dilakukan';
               $status = 'done';
			} elseif(($updateData['dbResult'] === false) && ($updateData['serviceResult']=='urlNotSet')) {
				$message ='Gagal Melakukan Update Data. Service Tidak ditemukan.';
                $status = 'fail';
			}elseif(($updateData['dbResult'] === false) && ($updateData['serviceResult']=='dataNotSend')) {
				$message ='Gagal Melakukan Update Data. Data Tidak Terkirim Ke Service.';
                $status = 'fail';
			} else {
				$message ='Perubahan Data Gagal Dilakukan. ';
                $status = 'fail';
			}
		} elseif($cek == "empty") {
		      $this->ShowMessage('inputUnitkerja','Lengkapi Isian Data','fail');
              return $this->pageInput . "&dataId=" . $this->encId;
		}
        
       if($this->p == 'list'){
            $this->ShowMessage('UnitkerjaCari',$message,$status);
            return $this->pageListCari;    
        } else {
            $this->ShowMessage('Unitkerja',$message,$status);
            return $this->pageView;
        }
	}

	public function Delete() 
    {
		$arrId = $this->_POST['idDelete'];
				
		$deleteArrData = $this->Obj->DoDeleteUnitkerjaByArrayId($arrId);
		
		$deleteData = $deleteArrData;
		
		//if($deleteData === true) {
            //$this->ShowMessage('UnitkerjaCari','Penghapusan Data Berhasil Dilakukan');
		
		if (($deleteData['dbResult'] === true)  && ($deleteData['serviceResult'] =='dataSend')) {
			$message ='Berhasil Melakukan Hapus Data. Permintaan Terkirim Ke Service';
            $status = 'done';
		} elseif(($deleteData['dbResult'] === true)) {
			$message ='Berhasil Melakukan Hapus Data';
            $status = 'done';
		} elseif(($deleteData['dbResult'] === false) && ($deleteData['serviceResult']=='urlNotSet')) {
			$message ='Gagal Melakukan Hapus Data. Service Tidak ditemukan.';
            $status = 'fail';
		}elseif(($deleteData['dbResult'] === false) && ($deleteData['serviceResult']=='dataNotSend')) {
			$message ='Gagal Melakukan Hapus Data. Permintaan Tidak Terkirim Ke Service.';
            $status = 'fail';			            
		} else {			
            $message ='Gagal Melakukan Hapus Data.';
            $status = 'fail';			
		}
		
		$this->ShowMessage('UnitkerjaCari',$message,$status);
		
        return $this->pageListCari;
	}
    
    protected function ShowMessage($subModule,$msg,$tipe='done')
    {
        if($tipe == 'done'){
            $status = $this->cssDone;
        }
        if($tipe == 'fail'){
            $status = $this->cssFail;
        }
        Messenger::Instance()->Send('unitkerja_tree', 
                                    $subModule, 
                                    'view', 
                                    'html', 
                                    array(
                                         $this->_POST,
                                         $msg, 
                                         $status),
                                    Messenger::NextRequest);
    }    
   
}

?>