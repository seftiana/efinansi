<?php

require_once GTFWConfiguration::GetValue('application', 'docroot'). 	'module/kode_penerimaan/business/KodePenerimaanMappingPembayaran.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot'). 'module/kode_penerimaan/business/KodePenerimaan.class.php';

class ProcessKodePenerimaanMappingPembayaran 
{
   
	protected $msg;
	protected $data;
	protected $moduleName='kode_penerimaan';
	protected $moduleHome='KodePenerimaanMappingPembayaran';
	protected $moduleEfinansi='kodePenerimaan';
   
	public $KodePenerimaanMappingPembayaran;
	public $KodePenerimaan;
   
	public function ProcessKodePenerimaanMappingPembayaran()
	{
		if(isset($_POST['data']))
			if(is_object($_POST['data']))	  
				$this->data=$_POST['data']->AsArray();		 
			else
				$this->data=$_POST['data'];		 
	  
		$this->KodePenerimaanMappingPembayaran = new KodePenerimaanMappingPembayaran();
		$this->KodePenerimaan = new KodePenerimaan();
		 
    }
	
	public function Add() 
    {
		// echo'<pre>';print_r($this->data['kodepenerimaan']);die;
		if(isset($_POST['btnsimpan'])){
			if($this->validation('Penambahan')){	
				$addData = $this->KodePenerimaanMappingPembayaran->DoAdd($this->data['kodepenerimaan']);
            
				if (($addData['dbResult'] === true)){
					$this->msg = 'Penambahan data Berhasil Dilakukan';
					$urlRedirect=$this->generateUrl('msg');
				} else {
					$this->msg = 'Gagal Melakukan Tambah Data.';
					$urlRedirect=$this->generateUrl('err');
				}	
			            
			} else {		    			
				$urlRedirect = $this->generateUrl('err');
			}
		  
		} else {
	      //kalo yang ditekan tombol balik
		  $urlRedirect = Dispatcher::Instance()->GetUrl($this->moduleName, $this->moduleHome, 'view', 'html') ;
		}
		return $urlRedirect;
	}
	
	public function Update()
	{
		// echo'<pre>';print_r($this->data['kodepenerimaan']);die;
		if(isset($_POST['btnsimpan'])){
			if($this->validation('Perubahan')) {
				// $updateData = $this->KodePenerimaan->DoUpdateMapping($this->data['kodepenerimaan']);
				$updateDataBiaya = $this->KodePenerimaanMappingPembayaran->DoUpdateCoaMapBiayaPembayaran($this->data['kodepenerimaan']);
				// if(($updateData['dbResult'] === true) and ($updateDataBiaya['dbResult'] === true) ) {
				if(($updateDataBiaya['dbResult'] === true) ) {
					$this->msg = 'Perubahan Data Berhasil Dilakukan';
					$urlRedirect = $this->generateUrl('msg');
				} else {
					$this->msg = 'Perubahan Data Gagal Dilakukan.';
					$urlRedirect = $this->generateUrl('err');		
				}
						
			} else {		    		   
				$urlRedirect = $this->generateUrl('err');
			}		  
		} else {
			//kalo yang ditekan tombol balik
			$urlRedirect = Dispatcher::Instance()->GetUrl($this->moduleName, $this->moduleHome, 'view', 'html') ;
		}
		return $urlRedirect;
	}
	
	public function Delete() 
	{
	    // echo'<pre>';print_r($_POST);die;
		if(isset($_POST['idDelete'])) {
		   $grp=Dispatcher::Instance()->Decrypt($_GET['grp']);		   
		   $grp=$_POST['idDelete'];		   
		   $deleteData = $this->KodePenerimaanMappingPembayaran->DoDelete($grp);
		  
			if(($deleteData['dbResult'] === true)) {
				$this->msg = 'Berhasil Melakukan Hapus Data';
				$urlRedirect = $this->generateUrl('msg',true);
			} else {			
				$this->msg = 'Gagal Melakukan Hapus Data.';
				$urlRedirect = $this->generateUrl('err',true);
			}		   
		    
		} else {
		   $this->msg = 'Penghapusan data gagal dilakukan';
		   $urlRedirect = $this->generateUrl('err',true);
		}
		return $urlRedirect;
	}
	
	public function validation($action) 
	{
	   
	    $this->msg='';
	    if(!isset($_POST['data'])) { //kalo gak ada data yang di POST apa yang mau di  validasi
		   $this->msg=$action.' data gagal dilakukan ';
		   return false;
		}				
	  
		// jenis biaya
	    if(isset($this->$data['kodepenerimaan']['penerimaan_id']) && (trim($this->data['kodepenerimaan']['penerimaan_id']) != ''))
	      $this->data['kodepenerimaan']['penerimaan_id']= Dispatcher::Instance()->Decrypt($this->data['kodepenerimaan']['penerimaan_id']);
	      
	    if(!isset($this->data['kodepenerimaan']['penerimaan_kode']) || trim($this->data['kodepenerimaan']['penerimaan_kode']) == '')
	      $this->msg.='Kode Pembayaran Tidak Boleh Kosong <br />';
			    
		
		// program studi
	    if(isset($this->$data['kodepenerimaan']['prodi_id']) && (trim($this->data['kodepenerimaan']['prodi_id']) != ''))
	      $this->data['kodepenerimaan']['prodi_id']= Dispatcher::Instance()->Decrypt($this->data['kodepenerimaan']['prodi_id']);
			    
		if(!isset($this->data['kodepenerimaan']['prodi_nama']) || trim($this->data['kodepenerimaan']['prodi_nama']) == '')
	      $this->msg.='Nama Prodi Tidak Boleh Kosong <br />';
	  
		// coa akun
	    if(isset($this->$data['kodepenerimaan']['coaid']) && (trim($this->data['kodepenerimaan']['coaid']) != ''))
	      $this->data['kodepenerimaan']['coaid']= Dispatcher::Instance()->Decrypt($this->data['kodepenerimaan']['coaid']);
	  
	    if(!isset($this->data['kodepenerimaan']['kode_coa']) || trim($this->data['kodepenerimaan']['kode_coa']) == '')
	      $this->msg.='Kode COA Tidak Boleh Kosong <br />';
			
			
		if($this->msg=='')
   		   return true;
		else 
		   return false; 			   
	}
	
	
	public function generateUrl($type,$isHome=false)
	{
	    //parameter isHome ditujukan bahwa url diredirect ke home module apapun bentuk pesannya
	    if(isset($_GET['grp']))
	      $grp='&grp='.Dispatcher::Instance()->Encrypt($this->data['periodetahun']['id']);
	    else
		  $grp='';
		
		if($type=='msg' || $isHome ) $submodule=$this->moduleHome;
		else $submodule='inputKodePenerimaanMappingPembayaran';	
		
		
		Messenger::Instance()->Send($this->moduleName, $submodule, 'view', 'html', array($this->data,$type,$this->msg),Messenger::NextRequest);				
		$urlRedirect = Dispatcher::Instance()->GetUrl($this->moduleName, $submodule, 'view', 'html').$grp;
		return $urlRedirect;
	}	
	
	public function parsingUrl($file) 
	{
	    $msg = Messenger::Instance()->Receive($file);	
		
		if(!empty($msg)) {		
		   $tmp['data']=$msg[0][0];
		   $tmp['msg']['action']=$msg[0][1];
		   $tmp['msg']['message']=$msg[0][2];
		   return $tmp;
		} else {
		  return array();
		}	    
	}
}

?>