<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
	'module/kode_penerimaan/response/ProcessKodePenerimaan.proc.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/kode_penerimaan/response/ProcessKodePenerimaanMappingPembayaran.proc.class.php';

class ViewInputKodePenerimaanMappingPembayaran extends HtmlResponse {
   
   protected $proc;

   function ViewInputKodePenerimaanMappingPembayaran(){
      $this->proc = new ProcessKodePenerimaan();
      $this->ePembayaran = new ProcessKodePenerimaanMappingPembayaran();
   }
   
   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') .
         'module/kode_penerimaan/template');
      $this->SetTemplateFile('input_kode_penerimaan_mapping_pembayaran.html');
   }
   
   function ProcessRequest() {
	  if(isset($_GET['grp'])) { //action pengeditan
			$grp=Dispatcher::Instance()->Decrypt($_GET['grp']);	         				
			$coa=Dispatcher::Instance()->Decrypt($_GET['coa']);	         				
			$return['grp']=$grp;
		
			$data = $this->ePembayaran->KodePenerimaanMappingPembayaran->GetDataJenisPembayaranId($grp);
			$data_coa = $this->proc->KodePenerimaan->GetCoaMapByIdPembayaran($coa);

			$data[0]['id_penerimaan'] = $data[0]['jenisBiayaId'];
			$data[0]['kode_penerimaan'] = $data[0]['kode'];
			$data[0]['nama_penerimaan'] = $data[0]['nama'];
			$data[0]['prodi_id'] = $data[0]['mpcoaProdiId'];
			$data[0]['prodi_kode'] = $data[0]['prodiKodeProdi'];
			$data[0]['prodi_nama'] = $data[0]['prodi'];
			$data[0]['coa_id'] = $data_coa[0]['coaId'];
			$data[0]['coa_kode'] = $data_coa[0]['coaKodeAkun'];
			$data[0]['coa_nama'] = $data_coa[0]['coaNamaAkun'];

		 
      if(sizeof($data) > 0 ) {
	      $return['kodepenerimaan']=$data[0];
		   $return['kodepenerimaan']['id'] = Dispatcher::Instance()->Encrypt($return['kodepenerimaan']['id']);
		   $return['grp']= Dispatcher::Instance()->Encrypt($return['kodepenerimaan']['id']);
	    }			
	  }    
	        
		$tmp=$this->proc->parsingUrl(__FILE__);	
		
		if(isset($tmp['data'])) {
		  $return['kodepenerimaan']=$tmp['data']['kodepenerimaan'];	
		  if(trim($tmp['data']['kodepenerimaan']['id']) != '')
             $return['grp'] = Dispatcher::Instance()->Encrypt($return['kodepenerimaan']['id']);
		}
		 
		if(isset($tmp['msg'])){
		  $return['msg']=$tmp['msg'];   
		}	
      return $return;
   }

   function ParseTemplate($data = NULL) { 
	 //debug($data);
      if (isset ($data['msg'])) {	     
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $data['msg']['message']);
      }
	
		if (isset($data['grp'])) {
         $url="updateKodePenerimaanMappingPembayaran";
         $tambah="Ubah";         
		} else {	
         $url="addKodePenerimaanMappingPembayaran";
         $tambah="Tambah";	   
		}        
    
      $this->mrTemplate->AddVar('content', 'JUDUL', $tambah );	  
      $this->mrTemplate->AddVar('content', 'DATA_ID', $data['kodepenerimaan']['id'] );	  
      $this->mrTemplate->AddVar('content', 'DATA_KODEPENERIMAAN_ID', $data['kodepenerimaan']['id_penerimaan'] );	  
	  $this->mrTemplate->AddVar('content', 'DATA_KODEPENERIMAAN_KODE', $data['kodepenerimaan']['kode_penerimaan'] );
	  $this->mrTemplate->AddVar('content', 'DATA_KODEPENERIMAAN_NAMA', $data['kodepenerimaan']['nama_penerimaan']);
	  $this->mrTemplate->AddVar('content', 'DATA_PRODI_ID', $data['kodepenerimaan']['prodi_id'] );	  
	  $this->mrTemplate->AddVar('content', 'DATA_PRODI_KODE', $data['kodepenerimaan']['prodi_kode'] );
	  $this->mrTemplate->AddVar('content', 'DATA_PRODI_NAMA', $data['kodepenerimaan']['prodi_nama']);
	  
	  $this->mrTemplate->AddVar('content', 'DATA_ID_COA', $data['kodepenerimaan']['coa_id'] );	  
	  $this->mrTemplate->AddVar('content', 'DATA_KODE_COA', $data['kodepenerimaan']['coa_kode'].' '.$data['kodepenerimaan']['coa_nama']);
	  
	  
	  $this->mrTemplate->AddVar('content', 'URL_POPUP_PEMBAYARAN',Dispatcher::Instance()->GetUrl('kode_penerimaan', 'popupJenisPembayaran', 'view', 'html'));
	  $this->mrTemplate->AddVar('content', 'URL_POPUP_PRODI',Dispatcher::Instance()->GetUrl('kode_penerimaan', 'popupProdi', 'view', 'html'));
	  $this->mrTemplate->AddVar('content', 'URL_POPUP_COA',Dispatcher::Instance()->GetUrl('kode_penerimaan', 'coa', 'popup', 'html'));

	  	
     if ($data['kodepenerimaan']['tipe']=='header'){
         $this->mrTemplate->AddVar('content', 'IS_HEADER', 'checked="checked"');	 
     }else{
         $this->mrTemplate->AddVar('content', 'NO_HEADER', 'checked="checked"');
     } 
     
     if ($data['kodepenerimaan']['aktif']=='T'){
         $this->mrTemplate->AddVar('content', 'AKTIF_TIDAK', 'checked="checked"');	 
     }else{
         $this->mrTemplate->AddVar('content', 'AKTIF_YA', 'checked="checked"');
     } 
     
     $this->mrTemplate->AddVar('content', 'URL_ACTION', Dispatcher::Instance()->GetUrl('kode_penerimaan', $url, 'do', 'html'));	  
     $this->mrTemplate->AddVar('content', 'URL_BACK', Dispatcher::Instance()->GetUrl('kode_penerimaan', 'kodepenerimaanMappingPembayaran', 'view', 'html'));	  
	  

   }
}
?>