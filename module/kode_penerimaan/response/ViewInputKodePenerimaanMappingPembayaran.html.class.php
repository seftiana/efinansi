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
			$return['grp']=$grp;
		
			$data = $this->ePembayaran->KodePenerimaanMappingPembayaran->GetDataJenisPembayaranId($grp);
			$data_penerimaan = $this->proc->KodePenerimaan->GetDataByIdJnsPembayaran($grp);
			// echo'<pre>';print_r($data);die;
			$data[0]['jenis_pembayaran_id'] = $data[0]['id'];
			$data[0]['jenis_pembayaran_kode'] = $data[0]['kode'];
			$data[0]['jenis_pembayaran_nama'] = $data[0]['nama'];
			$data[0]['id_penerimaan'] = $data_penerimaan[0]['kodeterimaId'];
			$data[0]['kode_penerimaan'] = $data_penerimaan[0]['kodeterimaKode'];
			$data[0]['nama_penerimaan'] = $data_penerimaan[0]['kodeterimaNama'];

		 
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

      $url="updateKodePenerimaanMappingPembayaran";
      $tambah="Ubah";         
    
      $this->mrTemplate->AddVar('content', 'DATA_KODEPENERIMAAN_ID', $data['kodepenerimaan']['id_penerimaan'] );	  
	  $this->mrTemplate->AddVar('content', 'DATA_KODEPENERIMAAN_KODE', $data['kodepenerimaan']['kode_penerimaan'] );
	  $this->mrTemplate->AddVar('content', 'DATA_KODEPENERIMAAN_NAMA', $data['kodepenerimaan']['nama_penerimaan']);
	  
	  $this->mrTemplate->AddVar('content', 'DATA_JENIS_PEMBAYARAN_ID', $data['kodepenerimaan']['jenis_pembayaran_id']);
	  $this->mrTemplate->AddVar('content', 'DATA_JENIS_PEMBAYARAN_NAMA', $data['kodepenerimaan']['jenis_pembayaran_nama']);
	  $this->mrTemplate->AddVar('content', 'DATA_JENIS_PEMBAYARAN_KODE', $data['kodepenerimaan']['jenis_pembayaran_kode']);
	  
	  $this->mrTemplate->AddVar('content', 'URL_POPUP_PEMBAYARAN',Dispatcher::Instance()->GetUrl('kode_penerimaan', 'popupJenisPembayaran', 'view', 'html'));

	  	
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