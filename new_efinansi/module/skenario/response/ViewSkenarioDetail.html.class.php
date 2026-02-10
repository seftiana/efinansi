<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/skenario/response/ProcSkenario.proc.class.php';

class ViewSkenarioDetail extends HtmlResponse {
   
   protected $data;
   protected $proc;
   
   function ViewSkenarioDetail() {
      $this->proc = new ProcSkenario;
	  //$this->data = $this->proc->getPOST();  	  
   }
   
   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/skenario/template');
      $this->SetTemplateFile('view_skenariodetail.html');
   } 
   
   
   function ProcessRequest() {	
    
    if(isset($_GET['grp'])) {
	   if(is_object($_GET['grp']))
	      $grp = $_GET['grp']->mrVariable;
	   else 
	      $grp = $_GET['grp'];
	   
	   $skenario['id'] = Dispatcher::Instance()->Decrypt($grp);
	   $skenario['grp'] = $grp;	   
	   
    } elseif(isset($_GET['cari'])) { //sebenernya ini adalah hasil parsingan dari componen delete kalo batal cuma bisa mengrimkan id lewat variable id
	   if(is_object($_GET['cari']))
	      $grp = $_GET['cari']->mrVariable;
	   else 
	      $grp = $_GET['cari'];
	   
	   $skenario['id'] = Dispatcher::Instance()->Decrypt($grp);
	   $skenario['grp'] = $grp;	   
    } else {
	   echo "gagal";
	   exit;
	}
	
	$this->data = $this->proc->db->GetDataDetail($skenario['id']); 
		
	 //start menghandle pesan yang diparsing	    
		$tmp=$this->proc->parsingUrl(__FILE__);		 
		if(isset($tmp['msg'])) {		   
		   $return['msg']=$tmp['msg'];  
 	   }		  
		//end handle 
	$return['skenario'] = $skenario;
	return $return;
   }
   
   function ParseTemplate($data = NULL) {   	  	  
	  $this->mrTemplate->AddVar('content', 'URL_LIST_SKENARIO', Dispatcher::Instance()->GetUrl($this->proc->moduleName, 'skenario', 'view', 'html') );      
	  $this->mrTemplate->AddVar('content', 'URL_TAMBAH_AKUN', Dispatcher::Instance()->GetUrl($this->proc->moduleName, $this->proc->moduleInput, 'view', 'html').'&grp='.$data['skenario']['grp'] );      
      
	  	  
	  if(isset($this->data) && !empty($this->data)) {          
		  $this->mrTemplate->AddVar('content', 'NAMA_SKENARIO', $this->data[0]['skenario_nama'] );      
		  $this->mrTemplate->AddVar('bool-table', 'HAVE_DATA', 'YES');	
          $totaldebet=0;
          $totalkredit=0;
		  
	      foreach($this->data as $data){
		     if(trim($data['skenariodetail_kredit'])=='') {
			    $totaldebet += $data['skenariodetail_prosen'];
			    $data['skenariodetail_debet_prosentase'] = $data['skenariodetail_prosen'].' %';
				$data['skenariodetail_kredit_prosentase'] = '';
				$skenariodetail_nama = $data['skenariodetail_debet'];
			 } else {
			    $totalkredit += $data['skenariodetail_prosen'];
			    $data['skenariodetail_debet_prosentase'] = '';
				$data['skenariodetail_kredit_prosentase'] = $data['skenariodetail_prosen'].' %';
				$skenariodetail_nama = $data['skenariodetail_kredit'];
			 }
			 //=============start dipake componenet confirm delete ===============================	     
		     $idDelete = Dispatcher::Instance()->Encrypt($data['skenariodetail_id'].'*'.$data['skenario_id']);		 
		     $kirimVar = Dispatcher::Instance()->Encrypt($data['skenario_id']);
	         $urlAccept = $this->proc->moduleName.'|deleteSkenarioDetail|do|html-cari-'.$kirimVar;
             $urlReturn = $this->proc->moduleName.'|skenarioDetail|view|html-cari-'.$kirimVar;
	         $label = 'Delete Skenario Detail';
	         $dataName = $skenariodetail_nama;				 
             $data['url_delete'] = Dispatcher::Instance()->GetUrl('confirm', 'confirmDelete', 'do', 'html').'&urlDelete='. $urlAccept.'&urlReturn='.$urlReturn.'&id='.$idDelete.'&label='.$label.'&dataName='.$dataName;               			   
			 //=============end  dipake componenet confirm delete ===============================
			 
			 
		     $this->mrTemplate->AddVars('data_item', $data, 'DATA_');
             $this->mrTemplate->parseTemplate('data_item', 'a');	  	  
		  }	  
		  $this->mrTemplate->AddVar('bool-table', 'TOTAL_DEBET', $totaldebet);	
		  $this->mrTemplate->AddVar('bool-table', 'TOTAL_KREDIT', $totalkredit);	
		  
	  } 
	     
	  if ($totaldebet != 100 OR $totalkredit != 100)
         $data['msg'] = array('action'=>'err','message'=>'Kredit atau Debet tidak benar! Tekan [Tambah Akun] untuk membenarkan.');
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
      
		
	}

}
?>
