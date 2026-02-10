<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/realisasi_pencairan/response/ProcessRealisasiPencairan.proc.class.php';

class ViewInputRealisasiPencairan extends HtmlResponse {
   
   protected $data;
   protected $proc;
   
   function ViewInputRealisasiPencairan() {
     $this->proc = new ProcessRealisasiPencairan();   
   }

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') .
         'module/realisasi_pencairan/template');
      $this->SetTemplateFile('input_realisasi_pencairan.html');
   }
   
   function ProcessRequest() {  
	  $userid=Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
     $role = $this->proc->UserUnitKerja->GetRoleUser($userid);   
	  $unitkerja= $this->proc->UserUnitKerja->GetSatkerUnitKerjaUserDua($userid); 
	  $this->data['unit_id']=$unitkerja['unit_kerja_id'];
	  $this->data['unit_nama']=$unitkerja['unit_kerja_nama'];
     $this->data['action']   ='add';
     if(isset($_POST['data'])) {
	    if(is_object($_POST))
		   $this->data = $_POST['data']->AsArray();
		else
		   $this->data = $_POST['data'];		
		
	 } elseif(isset($_GET['grp'])) { //action pengeditan inih	   
	   if(is_object($_GET['grp'])) 
	     $grp = $_GET['grp']->mrVariable;
	   else
	     $grp = $_GET['grp'];
	   
	   $grp = Dispatcher::Instance()->Decrypt($grp);	   
	   $data = $this->proc->RealisasiPencairan->GetDataById($grp);
	   
	   if($data) 
	      $this->data =  $data;	
	   /*	  
	   $this->data['kegiatandetail_id'] = Dispatcher::Instance()->Encrypt($this->data['kegiatandetail_id']);
	   $this->data['kegiatanunit_id'] = Dispatcher::Instance()->Encrypt($this->data['kegiatanunit_id']);
	   $this->data['unit_id'] = Dispatcher::Instance()->Encrypt($this->data['unit_id']);
	   $this->data['subunit_id'] = Dispatcher::Instance()->Encrypt($this->data['subunit_id']);
	   $this->data['program_id'] = Dispatcher::Instance()->Encrypt($this->data['program_id']);
	   $this->data['kegiatan_id'] = Dispatcher::Instance()->Encrypt($this->data['kegiatan_id']);
	   $this->data['subkegiatan_id'] = Dispatcher::Instance()->Encrypt($this->data['subkegiatan_id']);
	   $this->data['id'] = Dispatcher::Instance()->Encrypt($this->data['id']);
	   */
       
       $this->data['action']='edit';
	   $tanggal_selected = $this->data['tanggal'];
	   	   
	 }
	 $redir=$this->proc->parsingUrl(__FILE__);
	  
	  if(isset($redir['data'])) { //merupakan bentuk redirect dari halaman sebelumnya
	     $this->data = $redir['data'];		 
		 $ret['msg'] = $redir['msg'];
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
		 
	
	  $ret['unitkerja'] = $unitkerja;
		$ret['role_name'] = $role['role_name'];
	    
      return $ret;
   }

   function ParseTemplate($data = NULL) {  
      
   
      if (isset ($data['msg'])) {	     
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $data['msg']['message']);
		 if($data['msg']['action']=='msg') 
		   $class='notebox-done';
		 else
		   $class = 'notebox-warning';
		 
		 $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $data['msg']['message']);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $class);
      }
      
      
      if ($this->data['action']=='edit') {
         $url="updateRealisasiPencairan";
         $tambah="Ubah";         
		 $label_action = 'Update';
      } else {	
         $url="addRealisasiPencairan";
         $tambah="Tambah";	  
		 $label_action = 'Tambah';
         
      }
	  $this->mrTemplate->AddVar('content', 'POPUP_UNIT_KERJA', Dispatcher::Instance()->GetUrl('realisasi_pencairan', 'unitKerja', 'popup', 'html'));
	  $this->mrTemplate->AddVar('content', 'POPUP_SUBUNIT_KERJA', Dispatcher::Instance()->GetUrl('realisasi_pencairan', 'subUnitKerja', 'popup', 'html'));
	  $this->mrTemplate->AddVar('content', 'POPUP_PROGRAM', Dispatcher::Instance()->GetUrl('realisasi_pencairan', 'program', 'popup', 'html').'&tahun_anggaran='.Dispatcher::Instance()->Encrypt($this->data['ta_id']).'&tahun_anggaran_label='.Dispatcher::Instance()->Encrypt($this->data['ta_nama']));
	  $this->mrTemplate->AddVar('content', 'POPUP_KEGIATAN', Dispatcher::Instance()->GetUrl('realisasi_pencairan', 'kegiatan', 'popup', 'html'));
	  $this->mrTemplate->AddVar('content', 'POPUP_SUBKEGIATAN', Dispatcher::Instance()->GetUrl('realisasi_pencairan', 'subKegiatan', 'popup', 'html'));
	  
	  //print_r($this->data);
     //print_r($data);
	  if($data['role_name'] == "Administrator") {
	     $this->mrTemplate->AddVar('sub_unit', 'IS_UNIT_KERJA', 'ADMINISTRATOR');	
      }elseif($data['unitkerja']['is_unit_kerja']) {
	     $this->mrTemplate->AddVar('sub_unit', 'IS_UNIT_KERJA', 'YES');		 
	  } else {
	     $this->mrTemplate->AddVar('sub_unit', 'IS_UNIT_KERJA', 'NO');
		 $this->mrTemplate->AddVar('sub_unit', 'DATA_SUBUNIT_NAMA', $this->data['unit_nama']);
	  }
	  $this->mrTemplate->AddVar('content', 'DATA_ID', $this->data['id']);
	  $this->mrTemplate->AddVar('content', 'DATA_KEGIATANUNIT_ID', $this->data['kegiatanunit_id']);
	  $this->mrTemplate->AddVar('content', 'DATA_KEGIATANDETAIL_ID', $this->data['kegiatandetail_id']);
	  
	  $this->mrTemplate->AddVar('content', 'DATA_TA_ID', $this->data['ta_id']);
      $this->mrTemplate->AddVar('content', 'DATA_TA_NAMA', $this->data['ta_nama']);
	  
	  $this->mrTemplate->AddVar('sub_unit', 'DATA_UNIT_ID', $this->data['unit_id']);
	  $this->mrTemplate->AddVar('sub_unit', 'DATA_UNIT_NAMA', $this->data['unit_nama']);
	  
	  $this->mrTemplate->AddVar('sub_unit', 'DATA_SUBUNIT_ID', $this->data['subunit_id']);
	  $this->mrTemplate->AddVar('sub_unit', 'DATA_SUBUNIT_NAMA', $this->data['subunit_nama']);
	  
	  $this->mrTemplate->AddVar('content', 'DATA_PROGRAM_ID', $this->data['program_id']);
	  $this->mrTemplate->AddVar('content', 'DATA_PROGRAM_NAMA', $this->data['program_nama']);
	  
	  $this->mrTemplate->AddVar('content', 'DATA_KEGIATAN_ID', $this->data['kegiatan_id']);
	  $this->mrTemplate->AddVar('content', 'DATA_KEGIATAN_NAMA', $this->data['kegiatan_nama']);
	  
	  $this->mrTemplate->AddVar('content', 'DATA_SUBKEGIATAN_ID', $this->data['subkegiatan_id']);
	  $this->mrTemplate->AddVar('content', 'DATA_SUBKEGIATAN_NAMA', $this->data['subkegiatan_nama']);
	  
	  $this->mrTemplate->AddVar('content', 'DATA_KETERANGAN', $this->data['keterangan']);
	  $this->mrTemplate->AddVar('content', 'DATA_NOMOR_PENGAJUAN', $this->data['nomor_pengajuan']);
	  
	  $this->mrTemplate->AddVar('content', 'DATA_NOMINAL', $this->data['nominal']);  
	  
	  $this->mrTemplate->AddVar('content', 'DATA_TOTAL_ANGGARAN', $this->data['total_anggaran']);  
	  $this->mrTemplate->AddVar('content', 'DATA_REALISASI_NOMINAL', $this->data['realisasi_nominal']);  
	  $this->mrTemplate->AddVar('content', 'DATA_REALISASI_PENCAIRAN', $this->data['realisasi_pencairan']);  
	  
	  
	  $this->mrTemplate->AddVar('content', 'DATA_ACTION', $this->data['action']);
	  $this->mrTemplate->AddVar('content', 'URL_ACTION', Dispatcher::Instance()->GetUrl('realisasi_pencairan', $url, 'do', 'html'));
	  $this->mrTemplate->AddVar('content', 'LABEL_ACTION', $label_action);

      if(is_numeric($this->data['nominal']))
	     $this->mrTemplate->AddVar('content', 'DATA_TERBILANG', $this->proc->terbilang($this->data['nominal']));   
	 

   }
}
?>
