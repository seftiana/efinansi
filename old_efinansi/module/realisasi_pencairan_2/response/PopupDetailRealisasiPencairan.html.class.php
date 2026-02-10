<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/realisasi_pencairan_2/response/ProcessRealisasiPencairan.proc.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'main/function/date.php';
class PopupDetailRealisasiPencairan extends HtmlResponse {
   
   protected $data;
   protected $proc;
   
   function PopupDetailRealisasiPencairan() {
     $this->proc = new ProcessRealisasiPencairan();   
   }

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') . 'module/realisasi_pencairan_2/template');
      $this->SetTemplateFile('popup_detail_realisasi_pencairan.html');
   }
   
   function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }
   
   function ProcessRequest() {  
      if(isset($_GET['grp'])) { //action pengeditan inih	   
         if(is_object($_GET['grp'])) 
            $grp = $_GET['grp']->mrVariable;
         else
            $grp = $_GET['grp'];
         
         $grp = Dispatcher::Instance()->Decrypt($grp);	   
         $data = $this->proc->RealisasiPencairan->GetDataById($grp);
         $dataKomponen  = $this->proc->RealisasiPencairan->GetKomponenAnggaranPengajuanRealisasi($grp);
         
         if($data) 
            $this->data =  $data;	
         
         $this->data['action']='edit';
         $tanggal_selected = $this->data['tanggal'];
      
      }	 
	 
	 $startYear = $this->proc->RealisasiPencairan->GetMinTahun();	 
	 $endYear = $this->proc->RealisasiPencairan->GetMaxTahun();	 
	 if(!isset($tanggal_selected))
	    $tanggal_selected = date("Y-m-d");
		
	 Messenger::Instance()->SendToComponent('tanggal', 'Tanggal', 'view', 'html', 'tanggal',
	    array($tanggal_selected,$startYear,$endYear), Messenger::CurrentRequest); 
	 
	 
	 //ta_sekarang : mendapatkan data tahun anggaran sekarang kalo belom terset
	 if(!isset($this->data['ta_id'])) {
	    $ta_sekarang = $this->proc->RealisasiPencairan->GetDataTahunAnggaranSekarang();	 		
		$this->data['ta_id'] = $ta_sekarang['id'];
		$this->data['ta_nama'] = $ta_sekarang['nama'];    
		
     }
		 
	
      $ret['komponen']     = $dataKomponen;
      return $ret;
   }

   function ParseTemplate($data = NULL) {        
	  //print_r($this->data);
	  
      $dataKomponen  = $data['komponen'];
      
	  $this->mrTemplate->AddVar('content', 'DATA_ID', $this->data['id']);
	  
	  $this->mrTemplate->AddVar('content', 'DATA_TA_ID', $this->data['ta_id']);
      $this->mrTemplate->AddVar('content', 'DATA_TA_NAMA', $this->data['ta_label']);
	  
	  $this->mrTemplate->AddVar('content', 'DATA_UNIT_ID', $this->data['unit_id']);
	  $this->mrTemplate->AddVar('content', 'DATA_UNIT_NAMA', $this->data['unit_nama']);
	  
	 // $this->mrTemplate->AddVar('content', 'DATA_SUBUNIT_ID', $this->data['subunit_id']);
	  //$this->mrTemplate->AddVar('content', 'DATA_SUBUNIT_NAMA', $this->data['subunit_nama']);
	  
	  $this->mrTemplate->AddVar('content', 'DATA_PROGRAM_ID', $this->data['program_id']);
	  $this->mrTemplate->AddVar('content', 'DATA_PROGRAM_NAMA', $this->data['program_nama']);
	  
	  $this->mrTemplate->AddVar('content', 'DATA_KEGIATAN_ID', $this->data['kegiatan_id']);
	  $this->mrTemplate->AddVar('content', 'DATA_KEGIATAN_NAMA', $this->data['kegiatan_nama']);
	  
	  $this->mrTemplate->AddVar('content', 'DATA_SUBKEGIATAN_ID', $this->data['subkegiatan_id']);
	  $this->mrTemplate->AddVar('content', 'DATA_SUBKEGIATAN_NAMA', $this->data['subkegiatan_nama']);
	  
	  $this->mrTemplate->AddVar('content', 'DATA_KETERANGAN', $this->data['keterangan']);
	  $this->mrTemplate->AddVar('content', 'DATA_NOMOR_PENGAJUAN', $this->data['nomor_pengajuan']);
      
      $this->mrTemplate->AddVar('content', 'DATA_USER_APPROVAL', $this->data['user_approval']);
	  
	  $this->mrTemplate->AddVar('content', 'DATA_NOMINAL', number_format($this->data['nominal'], 0, ',', '.'));  
	  $this->mrTemplate->AddVar('content', 'DATA_TANGGAL', $this->proc->RealisasiPencairan->_dateToIndo($this->data['tanggal']));  
	  	  
	  $this->mrTemplate->AddVar('content', 'LABEL_ACTION', $label_action);	  


      if(empty($dataKomponen)){
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         foreach ($dataKomponen as $komponen) {
            if($komponen['nominalBudget'] < 0){
               $komponen['nominalBudget']   = number_format(abs($komponen['nominalBudget']), 2, ',','.');
            }else{
               $komponen['nominalBudget']   = number_format($komponen['nominalBudget'], 2, ',','.');
            }

            if($komponen['nominalApprove'] < 0){
               $komponen['nominalApprove']  = number_format(abs($komponen['nominalApprove']), 2, ',','.');
            }else{
               $komponen['nominalApprove']  = number_format($komponen['nominalApprove'], 2, ',','.');
            }
            $this->mrTemplate->AddVars('data_list', $komponen);
            $this->mrTemplate->parseTemplate('data_list', 'a');
         }
      }

   }
}
?>
