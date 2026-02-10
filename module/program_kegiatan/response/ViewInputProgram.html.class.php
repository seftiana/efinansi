<?php

/**
* @module program
* @author Rendi Kasigi,
* @mail to rkasigi@gmail.com
* @copyright 2008&copy;Gamatechno
*/

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/program_kegiatan/response/ProcessProgram.proc.class.php';

class ViewInputProgram extends HtmlResponse {

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') .
         'module/program_kegiatan/template');
      $this->SetTemplateFile('input_program.html');
   }
   
   function ProcessRequest() {      
	  $ta_id_selected=''; //inisialisasi tahun anggaran terpilih
	  
	  
	  if(isset($_GET['grp'])) { //action pengeditan
	    $return['grp']=Dispatcher::Instance()->Decrypt($_GET['grp']);		 
		 $programObj = new Program();
       $data = $programObj->GetDataProgramById($return['grp']);
		 
		if(sizeof($data) > 0 ) {
	     $return['dataProgram']=$data[0];		 
	    }	
	  }
	   
      
	   $processProgram= new ProcessProgram();
		
		$tmp=$processProgram->parsingUrl(__FILE__);
      
      
		
		if(isset($tmp['data'])) { //hasil redirect dari halaman ini juga
		  $return['dataProgram']=$tmp['data']['program'];		  		  
		  $ta_id_selected = $tmp['data']['program']['ta_id'];
		  
		  if(trim($tmp['data']['program']['id']) != '') //klo kondisi edit :p
		     $return['grp']=$tmp['data']['program']['id'];		    
		}

		$programObj = new Program();
		if(isset($_GET['idTahun']))
			$ta_id_selected = $_GET['idTahun'];
		/**
		 * ganti maping ke rkakl kode kegiatan 
		$arrRkakl = $programObj->GetKodeRkakl();
		Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 'data[program][jenisId]', array('data[program][jenisId]', $arrRkakl, $data[0]['rkakl_id'], '', ' style="width:200px;" '), Messenger::CurrentRequest);
        */ 

		$return['tahun_anggaran'] = $programObj->GetTahunAnggaranById($ta_id_selected);
		$return['kode_selanjutnya'] = $programObj->GetKodeSelanjutnya($ta_id_selected);
		

		if(isset($tmp['msg']))
		  $return['msg']=$tmp['msg'];             
	  
      return $return;
   }

   function ParseTemplate($data = NULL) {
   
		
   
      if (isset ($data['msg'])) {	     
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $data['msg']['message']);
      }
      
      
      if (!isset($data['grp'])) {	 
         $url="addProgram";
         $tambah="Tambah";
      } else {	    
         $url="updateProgram";
         $tambah="Ubah";
      }
		
		$this->mrTemplate->AddVar('content', 'TAHUN_ID', Dispatcher::Instance()->Decrypt($data['tahun_anggaran']['id']));
		$this->mrTemplate->AddVar('content', 'TAHUN_NAME', $data['tahun_anggaran']['name']);
        $this->mrTemplate->AddVar('content', 'DATA_PROGRAM_ID', Dispatcher::Instance()->Decrypt($data['grp']));
	  	$this->mrTemplate->AddVar('content', 'DATA_PROGRAM_NOMOR', $data['dataProgram']['nomor']);
	  	$this->mrTemplate->AddVar('content', 'DATA_PROGRAM_NAMA', $data['dataProgram']['nama']);
	  	$this->mrTemplate->AddVar('content', 'DATA_PROGRAM_LABEL', $data['dataProgram']['label']);
	  	$this->mrTemplate->AddVar('content', 'DATA_PROGRAM_SASARAN', $data['dataProgram']['sasaran']);
	  	$this->mrTemplate->AddVar('content', 'DATA_PROGRAM_SASARAN_ID', $data['dataProgram']['sasaran_id']);
	  	$this->mrTemplate->AddVar('content', 'DATA_PROGRAM_INDIKATOR', $data['dataProgram']['indikator']);
	  	$this->mrTemplate->AddVar('content', 'DATA_PROGRAM_STRATEGI', $data['dataProgram']['strategi']);
	  	$this->mrTemplate->AddVar('content', 'DATA_PROGRAM_KEBIJAKAN', $data['dataProgram']['kebijakan']);	 	  
	  	$this->mrTemplate->AddVar('content', 'DATA_KODE_SELANJUTNYA', $data['kode_selanjutnya']['nomor']);
	  	$this->mrTemplate->AddVar('content', 'DATA_RKAKL_KEGIATAN_ID', $data['dataProgram']['rkakl_kegiatan_id']);
	  	$this->mrTemplate->AddVar('content', 'DATA_RKAKL_KEGIATAN_NAMA', $data['dataProgram']['rkakl_kegiatan_nama']);
		$this->mrTemplate->AddVar('content', 'URL_ACTION', Dispatcher::Instance()->GetUrl('program_kegiatan', $url, 'do', 'html'));
		$this->mrTemplate->AddVar('content', 'URL_POPUP_SASARAN', 
								Dispatcher::Instance()->GetUrl(
															'program_kegiatan', 
															'PopupSasaran', 
															'view', 
															'html'));
		$this->mrTemplate->AddVar('content', 'URL_POPUP_RKAKL_KEGIATAN', 
								Dispatcher::Instance()->GetUrl(	
															'program_kegiatan', 
															'popupRkaklKegiatan', 
															'view', 
															'html'));
   }
}
?>