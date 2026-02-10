<?php

require_once GTFWConfiguration::GetValue('application', 'docroot'). 	'module/kode_penerimaan/business/KodePenerimaanMappingPembayaran.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot'). 	'module/kode_penerimaan/business/KodePenerimaan.class.php';

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
	
	public function Update()
	{
		// echo'<pre>';print_r($this->data['kodepenerimaan']);die;
		if(isset($_POST['btnsimpan'])){
			if($this->validation('Perubahan')) {
				$updateData = $this->KodePenerimaan->DoUpdateMapping($this->data['kodepenerimaan']);
				$updateDataBiaya = $this->KodePenerimaanMappingPembayaran->DoUpdateCoaMapBiayaPembayaran($this->data['kodepenerimaan']);
				if(($updateData['dbResult'] === true) and ($updateDataBiaya) ) {
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
	
	public function validation($action) 
	{
	   
	    $this->msg='';
	    if(!isset($_POST['data'])) { //kalo gak ada data yang di POST apa yang mau di  validasi
		   $this->msg=$action.' data gagal dilakukan ';
		   return false;
		}				
	  
	    if(isset($this->$data['kodepenerimaan']['penerimaan_id']) && (trim($this->data['kodepenerimaan']['penerimaan_id']) != ''))
	      $this->data['kodepenerimaan']['penerimaan_id']= Dispatcher::Instance()->Decrypt($this->data['kodepenerimaan']['penerimaan_id']);
	      
	   
	    if(!isset($this->data['kodepenerimaan']['penerimaan_kode']) || trim($this->data['kodepenerimaan']['penerimaan_kode']) == '')
	      $this->msg.='Mapping Kode Penerimaan Tidak Boleh Kosong <br />';
	    
		
			    
		if(!isset($this->data['kodepenerimaan']['nama']) || trim($this->data['kodepenerimaan']['nama']) == '')
	      $this->msg.='Mapping Nama Penerimaan Tidak Boleh Kosong <br />';

			
			
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