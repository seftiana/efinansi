<?php

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
	'module/kode_penerimaan/business/KodePenerimaan.class.php';

class ProcessKodePenerimaan 
{
   
	protected $msg;
	protected $data;
	protected $moduleName='kode_penerimaan';
	protected $moduleHome='kodePenerimaan';
   
	public $KodePenerimaan;
   
	public function ProcessKodePenerimaan()
	{
		if(isset($_POST['data']))
			if(is_object($_POST['data']))	  
				$this->data=$_POST['data']->AsArray();		 
			else
				$this->data=$_POST['data'];		 
	  
		$this->KodePenerimaan = new KodePenerimaan();
		 
    }
   
    public function Add() 
    {
	   
	   if(isset($_POST['btnsimpan'])){
	      //kalo yang diklik tombol simpan
		  if($this->validation('Penambahan')){
		  	
		    $addData = $this->KodePenerimaan->DoAdd($this->data['kodepenerimaan']);
		    /**
            if($add === true) {
            	$last_id = $this->KodePenerimaan->GetLastKodePenerimaanId();
            	
            	if($this->data['kodepenerimaan']['coaid']){
					$add_coa_map = $this->KodePenerimaan->DoAddCoa(
								$this->data['kodepenerimaan']['coaid'],$last_id);
				}
			   $this->msg='Penambahan data berhasil dilakukan';
			   
			   $urlRedirect=$this->generateUrl('msg');
            } else {
			   $this->msg='Penambahan data gagal dilakukan '.$add;
			   $urlRedirect=$this->generateUrl('err');
            }
            */
            
			if (($addData['dbResult'] === true)  && ($addData['serviceResult'] =='dataSend')){
				$this->msg = 'Penambahan data Berhasil Dilakukan. Data Teririm Ke Service';
                $urlRedirect=$this->generateUrl('msg');
			} elseif (($addData['dbResult'] === true)){
			    $this->msg = 'Penambahan data Berhasil Dilakukan';
                $urlRedirect=$this->generateUrl('msg');
			} elseif(($addData['dbResult'] === false)  && ($updateData['serviceResult']=='urlNotSet')) {
				$this->msg = 'Gagal Melakukan Tambah Data. Service Tidak ditemukan.';
                $urlRedirect=$this->generateUrl('err');
			} elseif(($addData['dbResult'] === false) && ($addData['serviceResult'] =='dataNotSend')) {
				$this->msg = 'Gagal Melakukan Tambah Data. Data Tidak Terkirim Ke Service.';
                $urlRedirect=$this->generateUrl('err');
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
	
	public function Delete() 
	{
	    //if(isset($_GET['grp'])) {
		if(isset($_POST['idDelete'])) {
		   $grp=Dispatcher::Instance()->Decrypt($_GET['grp']);		   
		   $grp=$_POST['idDelete'];		   
		   $deleteData = $this->KodePenerimaan->DoDelete($grp);
		   /**
		   if($del) {
   				//cek kodepenerimaan di tabel relasi finansi_coa_map
				//jika ada maka delete
				if($this->KodePenerimaan->GetCoaMap($grp)){
						$this->KodePenerimaan->DoDeleteCoaMap($grp);
				}
				
		     	$this->msg='Penghapusan data berhasil dilakukan';
		     	$urlRedirect = $this->generateUrl('msg',true);
		     
		   } else {
		     $this->msg = 'Penghapusan data gagal dilakukan';
		     $urlRedirect = $this->generateUrl('err',true);
		   }
		   */
			if (($deleteData['dbResult'] === true)  && ($deleteData['serviceResult'] =='dataSend')) {
				$this->msg = 'Berhasil Melakukan Hapus Data. Permintaan Terkirim Ke Service';
				$urlRedirect = $this->generateUrl('msg',true);
			} elseif(($deleteData['dbResult'] === true)) {
				$this->msg = 'Berhasil Melakukan Hapus Data';
				$urlRedirect = $this->generateUrl('msg',true);
			} elseif(($deleteData['dbResult'] === false) && ($deleteData['serviceResult']=='urlNotSet')) {
				$this->msg = 'Gagal Melakukan Hapus Data. Service Tidak ditemukan.';
				$urlRedirect = $this->generateUrl('err',true);
			}elseif(($deleteData['dbResult'] === false) && ($deleteData['serviceResult']=='dataNotSend')) {
				$this->msg = 'Gagal Melakukan Hapus Data. Permintaan Tidak Terkirim Ke Service.';
				$urlRedirect = $this->generateUrl('err',true);
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
	
	public function Update()
	{
	  
	   if(isset($_POST['btnsimpan'])){
         if($this->validation('Perubahan')) {
		   
		   $updateData = $this->KodePenerimaan->DoUpdate($this->data['kodepenerimaan']);
		   /*
			if($update === true) {
				//cek kodepenerimaan di tabel relasi finansi_coa_map
				//jika ada maka update
				if($this->KodePenerimaan->GetCoaMap($this->data['kodepenerimaan']['id'])){
					$this->KodePenerimaan->DoUpdateCoaMap($this->data['kodepenerimaan']);	
				} else {
					//jika tidak ada maka tambah data
					if($this->data['kodepenerimaan']['coaid'] && $this->data['kodepenerimaan']['id']){
					$this->KodePenerimaan->DoAddCoa(
						$this->data['kodepenerimaan']['coaid'],
						$this->data['kodepenerimaan']['id']
						);
					}
				}
				
			  $this->msg='Perubahan data berhasil dilakukan'; 		   
		      $urlRedirect = $this->generateUrl('msg');		  
			} else {
			  $this->msg='Perubahan data gagal dilakukan silahkan ulangi lagi '.$update; 		   
		      $urlRedirect = $this->generateUrl('err');		  
			}	   
			*/
			if (($updateData['dbResult'] === true)  && ($updateData['serviceResult'] =='dataSend')) {
				$this->msg = 'Perubahan Data Berhasil Dilakukan. Data Terkirim Ke Service';
                $urlRedirect = $this->generateUrl('msg');	
			} elseif(($updateData['dbResult'] === true)) {
			    $this->msg = 'Perubahan Data Berhasil Dilakukan';
                $urlRedirect = $this->generateUrl('msg');	
			} elseif(($updateData['dbResult'] === false) && ($updateData['serviceResult']=='urlNotSet')) {
				$this->msg = 'Gagal Melakukan Update Data. Service Tidak ditemukan.';
                $urlRedirect = $this->generateUrl('err');		
			}elseif(($updateData['dbResult'] === false) && ($updateData['serviceResult']=='dataNotSend')) {
				$this->msg = 'Gagal Melakukan Update Data. Data Tidak Terkirim Ke Service.';
                $urlRedirect = $this->generateUrl('err');		
			} else {
				$this->msg = 'Perubahan Data Gagal Dilakukan. ';
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
	
	public function validation($action) 
	{
	   
	    $this->msg='';
	    if(!isset($_POST['data'])) { //kalo gak ada data yang di POST apa yang mau di  validasi
		   $this->msg=$action.' data gagal dilakukan ';
		   return false;
		}				
	  
	    if(isset($this->$data['kodepenerimaan']['id']) && (trim($this->data['kodepenerimaan']['id']) != ''))
	      $this->data['kodepenerimaan']['id']= Dispatcher::Instance()->Decrypt($this->data['kodepenerimaan']['id']);
	      
	   
	    if(!isset($this->data['kodepenerimaan']['kode']) || trim($this->data['kodepenerimaan']['kode']) == '')
	      $this->msg.='Kode Tidak Boleh Kosong <br />';
	    
		
			    
		if(!isset($this->data['kodepenerimaan']['nama']) || trim($this->data['kodepenerimaan']['nama']) == '')
	      $this->msg.='Nama Tidak Boleh Kosong <br />';
	    /*  
		if(!isset($this->data['kodepenerimaan']['mak_nama']) || trim($this->data['kodepenerimaan']['mak_nama']) == '')
	      $this->msg.='MAP Tidak Boleh Kosong';
		*/	
			
			
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
		else $submodule='inputKodePenerimaan';	
		
		
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