<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
	'module/kode_penerimaan/response/ProcessKodePenerimaan.proc.class.php';

class ViewInputKodePenerimaan extends HtmlResponse {
   
   protected $proc;

   function ViewInputKodePenerimaan(){
      $this->proc = new ProcessKodePenerimaan();
   }
   
   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') .
         'module/kode_penerimaan/template');
      $this->SetTemplateFile('input_kode_penerimaan.html');
   }
   
   function ProcessRequest() {
	  if(isset($_GET['grp'])) { //action pengeditan
	      $grp=Dispatcher::Instance()->Decrypt($_GET['grp']);	         				
		   $return['grp']=$grp;
		
         $data = $this->proc->KodePenerimaan->GetDataById($grp);		 		
		 
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
      $popupMak      = Dispatcher::Instance()->GetUrl(
         'kode_penerimaan',
         'PopupMak',
         'view',
         'html'
      );
      
      $this->mrTemplate->AddVar('content', 'POPUP_MAK', $popupMak);
      if (isset ($data['msg'])) {	     
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $data['msg']['message']);
      }
      
      
      if (isset($data['grp'])) {
         $url="updateKodePenerimaan";
         $tambah="Ubah";         
      } else {	
         $url="addKodePenerimaan";
         $tambah="Tambah";	  
         
      }
	  
	  //COA
	  //popup COA diambil dari modul komponen 
      $url_popup_coa = Dispatcher::Instance()->GetUrl('kode_penerimaan', 'coa', 'popup', 'html');
	   $this->mrTemplate->AddVar("content", "URL_POPUP_COA", $url_popup_coa);
	   $this->mrTemplate->AddVar('content', 'DATA_ID_COA', 
	  					empty($dataK[0]['coaId'])?$data['Data']['id_coa']:$dataK[0]['coaId']);
	   $this->mrTemplate->AddVar('content', 'DATA_NAMA_COA', 
	  					empty($dataK[0]['coaNamaAkun'])?$data['Data']['nama_coa']:$dataK[0]['coaNamaAkun']);
	  //end popup coa
	  
	  $this->mrTemplate->AddVar('content', 'URL_POPUP_KODE_RKAKL', 
			Dispatcher::Instance()->GetUrl('kode_penerimaan', 'popupKodeRkakl', 'view', 'html'));
	  $this->mrTemplate->AddVar('content', 'URL_POPUP_SD', 
			Dispatcher::Instance()->GetUrl('kode_penerimaan', 'popupSumberDana', 'view', 'html'));	  
	  $this->mrTemplate->AddVar('content', 'URL_POPUP_KP_HEADER', 
			Dispatcher::Instance()->GetUrl('kode_penerimaan', 'popupKodePenerimaanHeader', 'view', 'html'));	  
      $this->mrTemplate->AddVar('content', 'DATA_KODEPENERIMAAN_ID', $data['kodepenerimaan']['id'] );	  
	  $this->mrTemplate->AddVar('content', 'DATA_KODEPENERIMAAN_KODE', $data['kodepenerimaan']['kode'] );
	  $this->mrTemplate->AddVar('content', 'DATA_KODEPENERIMAAN_NAMA', $data['kodepenerimaan']['nama']);
	  $this->mrTemplate->AddVar('content', 'DATA_KODE_RKAKL', $data['kodepenerimaan']['kode_rkakl']);	 
	  $this->mrTemplate->AddVar('content', 'DATA_KODE_RKAKL_NAMA', $data['kodepenerimaan']['kode_rkakl_nama']);
	  
	  $this->mrTemplate->AddVar('content', 'DATA_ID_COA', $data['kodepenerimaan']['coaid']);
	  $this->mrTemplate->AddVar('content', 'DATA_NAMA_COA', $data['kodepenerimaan']['nama_coa']);
	  $this->mrTemplate->AddVar('content', 'DATA_KODE_COA', $data['kodepenerimaan']['kode_coa']);
	  
	  $this->mrTemplate->AddVar('content', 'DATA_SD_ID', $data['kodepenerimaan']['sd_id']);
	  $this->mrTemplate->AddVar('content', 'DATA_SD_NAMA', $data['kodepenerimaan']['sd_nama']);
	  
	  $this->mrTemplate->AddVar('content', 'DATA_MAK_ID', $data['kodepenerimaan']['mak_id']);
	  $this->mrTemplate->AddVar('content', 'DATA_MAK_KODE', $data['kodepenerimaan']['mak_nama']);
	  
	  $this->mrTemplate->AddVar('content', 'DATA_PARENT_ID', $data['kodepenerimaan']['parent_id']);
	  $this->mrTemplate->AddVar('content', 'DATA_PARENT_NAMA', $data['kodepenerimaan']['parent_nama']);
	  	
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
	  

   }
}
?>