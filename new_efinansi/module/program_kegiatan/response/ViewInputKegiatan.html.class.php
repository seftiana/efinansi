<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/program_kegiatan/response/ProcessKegiatan.proc.class.php';

class ViewInputKegiatan extends HtmlResponse {

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') .
         'module/program_kegiatan/template');
      $this->SetTemplateFile('input_kegiatan.html');
   }
   
   function ProcessRequest() {

	   $subprogramObj = new Kegiatan();
	   
	    if (isset($_GET['idTahun']))
      {
         if($_GET['idTahun']!='')
		 {
		 $tahunId=Dispatcher::Instance()->Decrypt($_GET['idTahun']);
		 }else
		 {$tahunId='';}
      }
		
		$arrJenisKegiatan 	= $subprogramObj->GetJenisKegiatan();
		$arrProgram 		= $subprogramObj->GetProgram($tahunId);
	 	

	  if(isset($_GET['grp'])) { //action pengeditan
	    $grp=Dispatcher::Instance()->Decrypt($_GET['grp']);	         				
		$return['grp']=$grp;
		

        $data = $subprogramObj->GetDataById($grp);		 		
		if(sizeof($data) > 0 ) {
			$return['kegiatan']=$data[0];
			$return['kegiatan']['id'] = Dispatcher::Instance()->
			Encrypt($return['kegiatan']['id']);
			$return['grp']= Dispatcher::Instance()->Encrypt($return['kegiatan']['id']);
	    }			
	  }    
	   
	   $processKegiatan= new ProcessKegiatan();
	$tmp=$processKegiatan->parsingUrl(__FILE__);	
		
		if(isset($tmp['data'])) {
		  $return['kegiatan']=$tmp['data']['kegiatan'];	
		  if(trim($tmp['data']['kegiatan']['id']) != '')
             $return['grp'] = Dispatcher::Instance()->Encrypt($return['kegiatan']['id']);
		}
		
		if(isset($_GET['idTahun']))
			$return['kegiatan']['thn_id'] = $_GET['idTahun'];
		
		$return['tahun_anggaran'] = 
		$subprogramObj->GetTahunAnggaranById($return['kegiatan']['thn_id']);
		$return['kegiatan']['thn_id'] =  
		$return['tahun_anggaran']['id'];
		$return['kegiatan']['thn_name'] =  
		$return['tahun_anggaran']['name'];
		
	if(!empty($_GET['jenisId'])) 
	$return['kegiatan']['jenisId'] = Dispatcher::Instance()->Decrypt($_GET['jenisId']);
	
	Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 
				'data[kegiatan][jenisId]',array('data[kegiatan][jenisId]', 
				$arrJenisKegiatan, $return['kegiatan']['jenisId'], '', 
				' style="width:100px;" onchange="tampilkanKodeSelanjutnya()"'), 
		 		Messenger::CurrentRequest);
		 		
	//empty($return['kegiatan']['program'])?:$return['kegiatan']['program'];
    if(!$return['kegiatan']['program']) 
	$return['kegiatan']['program'] = Dispatcher::Instance()->Decrypt($_GET['programId']);
	Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 
				'data[kegiatan][program]',array('data[kegiatan][program]', 
				$arrProgram, $return['kegiatan']['program'], '', ' style="width:400px;" '), 
	Messenger::CurrentRequest);

    if(trim($_GET['programId']) != "") 
         $return['kode_selanjutnya'] = $subprogramObj->GetKodeSelanjutnya
		 							   ($return['kegiatan']['program'],$_GET['jenisId']);
		
	if(isset($tmp['msg']))
		$return['msg']=$tmp['msg'];             
	  
		return $return;
   }

   function ParseTemplate($data = NULL) {     
   	
      if (isset ($data['msg'])) {	     
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $data['msg']['message']);
      }
      
      
      if (isset($data['grp'])) {
         $url="updateKegiatan";
         $tambah="Ubah";         
      } else {	
         $url="addKegiatan";
         $tambah="Tambah";	  
         
      }
	  //debug($data);
	  
		$this->mrTemplate->AddVar('content', 'TAHUN_NAME', $data['kegiatan']['thn_name']);
		$this->mrTemplate->AddVar('content', 'DATA_KEGIATAN_TAHUN', $data['kegiatan']['thn_id']);
		$this->mrTemplate->AddVar('content', 'DATA_KEGIATAN_PROGRAM_ID', $data['kegiatan']['program_id']);
		$this->mrTemplate->AddVar('content', 'DATA_KEGIATAN_PROGRAM_NAMA', $data['kegiatan']['program_nama']);	  
		$this->mrTemplate->AddVar('content', 'DATA_KEGIATAN_ID', $data['kegiatan']['id'] );	  
		$this->mrTemplate->AddVar('content', 'DATA_KEGIATAN_KODE', $data['kegiatan']['kode'] );
		$this->mrTemplate->AddVar('content', 'DATA_KEGIATAN_NAMA', $data['kegiatan']['nama']);
		$this->mrTemplate->AddVar('content', 'DATA_KODE_LABEL', $data['kegiatan']['kode_label']);
		$this->mrTemplate->AddVar('content', 'DATA_RKAKL_OUTPUT_ID', $data['kegiatan']['rkakl_output_id']);
		$this->mrTemplate->AddVar('content', 'DATA_RKAKL_OUTPUT_NAMA', $data['kegiatan']['rkakl_output_nama']);
    	  	  
      if($data['kode_selanjutnya'])	
         $this->mrTemplate->AddVar('content', 'DATA_KODE_SELANJUTNYA', "(kode selanjutnya : " . $data['kode_selanjutnya']['nomor'] . ")");	  
      $this->mrTemplate->AddVar('content', 'URL_ACTION', Dispatcher::Instance()->GetUrl('program_kegiatan', $url, 'do', 'html'));
		$this->mrTemplate->AddVar('content', 'POPUP_PROGRAM', Dispatcher::Instance()->GetUrl('program_kegiatan', 'program', 'popup', 'html'));
		$this->mrTemplate->AddVar('content', 'URL_POPUP_RKAKL_OUTPUT', 
								Dispatcher::Instance()->GetUrl(
														'program_kegiatan', 
														'popupRkaklOutput', 
														'view', 
														'html'));
		
	  
//      $this->mrTemplate->AddVar('content', 'URL_VIEW', Dispatcher::Instance()->GetUrl('program', 'program', 'view', 'html') );
   }
}
?>