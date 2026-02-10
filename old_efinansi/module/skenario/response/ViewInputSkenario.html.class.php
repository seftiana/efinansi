<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/skenario/response/ProcSkenario.proc.class.php';

class ViewInputSkenario extends HtmlResponse {
   
   protected $data;
   protected $proc;
   
   function ViewInputSkenario() {
      $this->proc = new ProcSkenario;
	  $this->data = $this->proc->getPOST();
	  
	  	  
   }
   
   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/skenario/template');
      $this->SetTemplateFile('view_skenario_input.html');
   } 
   
   
   function ProcessRequest() {
    $this->data['action']='add';
	
    if(isset($_GET['grp'])) {
	   if(is_object($_GET['grp']))
	      $grp = $_GET['grp']->mrVariable;
	   else 
	      $grp = $_GET['grp'];
	   
	   $skenario['id'] = Dispatcher::Instance()->Decrypt($grp);
	   $skenario['grp'] = $grp;	   
	   
	   $datadetail = $this->proc->db->GetDataDetail($skenario['id']);   
	   
	   
    } elseif(isset($_GET['cari'])) { //sebenernya ini adalah hasil parsingan dari componen delete kalo batal cuma bisa mengrimkan id lewat variable id
	   if(is_object($_GET['cari']))
	      $grp = $_GET['cari']->mrVariable;
	   else 
	      $grp = $_GET['cari'];
	   
	   $skenario['id'] = Dispatcher::Instance()->Decrypt($grp);
	   $skenario['grp'] = $grp;	   
	   
	   $datadetail = $this->proc->db->GetDataDetail($skenario['id']);   
    }
	
	
	//parsing mana data debet mana data kredit
	if(isset($datadetail)){
	   foreach($datadetail as $val){
	      $akun['skenariodetail_id'] = $val['skenariodetail_id'];
		  $akun['skenario_id'] = $val['skenario_id'];
		  $akun['prosentase'] = $val['skenariodetail_prosen'];
		  $akun['debet_id'] = $val['debet_id'];
		  $akun['kredit_id'] = $val['kredit_id'];
		  
		  
	      if(empty($val['skenariodetail_kredit'])) { // ini data debet		     
			 $akun['nama'] = $val['skenariodetail_debet'];
			 $akun['kode'] = $val['skenariodetail_debet_kode'];			 
		     $dataGrid['debet'][] = $akun;
		  } else { // ini data kredit
		     $akun['nama'] = $val['skenariodetail_kredit'];
			 $akun['kode'] = $val['skenariodetail_kredit_kode'];			 
		     $dataGrid['kredit'][] = $akun;
		  }
	   }
	   $this->data['skenario']['nama'] = $datadetail[0]['skenario_nama'];
	   $this->data['skenario']['id'] = $datadetail[0]['skenario_id'];
	   unset($akun);
	   $this->data['action']='update';
	}
	
	 //start menghandle pesan yang diparsing	    
		$tmp=$this->proc->parsingUrl(__FILE__);		 
		if(isset($tmp['msg'])) {		   
		   $this->data = $tmp['data'];
		   $return['msg']=$tmp['msg'];
         $dataGrid['debet'] = array();
         if (is_array($this->data['debet']['tambah'])) foreach ($this->data['debet']['tambah'] AS $key=>$value)
         {
            $dataGrid['debet'][$key]['debet_id'] = $value['id'];
            $dataGrid['debet'][$key]['kode'] = $value['kode'];
            $dataGrid['debet'][$key]['nama'] = $value['nama'];
            $dataGrid['debet'][$key]['prosentase'] = $value['prosentase'];
         }
         $dataGrid['kredit'] = array();
         if (is_array($this->data['kredit']['tambah'])) foreach ($this->data['kredit']['tambah'] AS $key=>$value)
         {
            $dataGrid['kredit'][$key]['kredit_id'] = $value['id'];
            $dataGrid['kredit'][$key]['kode'] = $value['kode'];
            $dataGrid['kredit'][$key]['nama'] = $value['nama'];
            $dataGrid['kredit'][$key]['prosentase'] = $value['prosentase'];
         }
 	   }		  
     //end handle 
	 if(isset($dataGrid)){
	    $this->data['debet']['datalist'] = $dataGrid['debet'];
		$this->data['kredit']['datalist'] = $dataGrid['kredit'];
	 }
	 
		//print_r($this->data);
	return $return;
   }
   
   function ParseTemplate($data = NULL) {   	  
	  if($this->data['action']=='add')
	     $mode = 'Tambah';
	  else
	     $mode = 'Ubah';
	  
	  $this->mrTemplate->AddVar('content', 'MODE', $mode);      
	  $this->mrTemplate->AddVar('content', 'URL_ACTION', Dispatcher::Instance()->GetUrl($this->proc->moduleName, $this->proc->moduleAdd, 'do', 'html') );      
	  $this->mrTemplate->AddVar('content', 'URL_LIST_SKENARIO', Dispatcher::Instance()->GetUrl($this->proc->moduleName, 'skenario', 'view', 'html') );      
     $this->mrTemplate->AddVar('content', 'POPUP_COA', Dispatcher::Instance()->GetUrl($this->proc->moduleName, 'coa', 'popup', 'html') );	  	  
	  
	  $this->mrTemplate->AddVar('content', 'SKENARIO_ID', $this->data['skenario']['id'] );	  	  
	  $this->mrTemplate->AddVar('content', 'SKENARIO_NAMA', $this->data['skenario']['nama'] );	  	  
	  
	  
	  
	  $this->mrTemplate->AddVar('content', 'DATA_ACTION', $this->data['action']);	  	  
	  
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
      $viewsimpan = false;	  
	  /* ===================start parsing data debet */
	  #data sudah ada dalam database
	  $stu=sizeof($this->data['debet']['datalist']);
	  if(isset($this->data['debet']['datalist']) && !empty($this->data['debet']['datalist'])) {
          $this->mrTemplate->AddVar('bool-table-debet', 'HAVE_DATA', 'YES');
          $no=1;		  
	      foreach($this->data['debet']['datalist'] as $data){
		     $data['js_id'] = 'tr-debet-'.$data['id'].rand(1, 20);
		     $data['url_delete'] ='<a class="dest_subcontent-element" href="javascript:void(0)" onclick="javascript:hapusAkun(\''.$data['tr_id'].'\')" title="Membatalkan Menambah Akun debet">Batal</a>';		
			 $data['tr_id'] = 'id="'.$data['js_id'].'"';
		     $data['nomor'] = $no;
			 $no++;
			 
			 //=============start dipake componenet confirm delete ===============================	     
		     $idDelete = Dispatcher::Instance()->Encrypt($data['skenariodetail_id'].'*'.$data['skenario_id']);		 
		     $kirimVar = Dispatcher::Instance()->Encrypt($data['skenario_id']);
	         $urlAccept = $this->proc->moduleName.'|deleteSkenarioDetail|do|html-cari-'.$kirimVar;
             $urlReturn = $this->proc->moduleName.'|inputSkenario|view|html-cari-'.$kirimVar;
	         $label = 'Delete Skenario Detail';
	         $dataName = $data['nama'];
             $data['url_delete'] = Dispatcher::Instance()->GetUrl('confirm', 'confirmDelete', 'do', 'html').'&urlDelete='. $urlAccept.'&urlReturn='.$urlReturn.'&id='.$idDelete.'&label='.$label.'&dataName='.$dataName;               			   
			 //=============end  dipake componenet confirm delete ===============================
			 
		     $this->mrTemplate->AddVars('table-debet', $data, 'DEBET_');
             $this->mrTemplate->parseTemplate('table-debet', 'a');	  	  
		  }	  
		  $viewsimpan = true;
	  } 
	     
	  #merupakan data yang belum dimasukan dalam data base, karena terjadi error pada sesuatu.
	   $dwa=sizeof($this->data['debet']['tambah']);
	   if(isset($this->data['debet']['tambah']) && !empty($this->data['debet']['tambah']) and $stu!=$dwa){
	  
          $this->mrTemplate->AddVar('bool-table-debet-tambah', 'HAVE_DATA', 'YES');		  
	      foreach($this->data['debet']['tambah'] as $data){ 
		     $data['js_id'] = 'tr-debet-'.$data['id'].rand(1, 20);
		     $data['url_delete'] ='<a class="dest_subcontent-element" href="javascript:void(0)" onclick="javascript:hapusAkun(\''.$data['tr_id'].'\')" title="Membatalkan Menambah Akun debet">Batal</a>';		
			 $data['tr_id'] = 'id="'.$data['js_id'].'"';
			if(empty($data['prosentase'])){
		     $this->mrTemplate->AddVars('table-debet-tambah', $data, 'DEBET_');
             $this->mrTemplate->parseTemplate('table-debet-tambah', 'a');	
			 }  	  
		  }
		  $viewsimpan = true;
		  
	  } 
	  
	  /* ====================end parsing data debet */
	  
	   /* ===================start parsing data kredit */
	  #data sudah ada dalam database
	  $satu=sizeof($this->data['kredit']['datalist']);
	  if(isset($this->data['kredit']['datalist']) && !empty($this->data['kredit']['datalist'])) {
          $this->mrTemplate->AddVar('bool-table-kredit', 'HAVE_DATA', 'YES');	
          $no=1;		  
	      foreach($this->data['kredit']['datalist'] as $data){
		     $data['js_id'] = 'tr-kredit-'.$data['id'].rand(1, 20);
		     $data['url_delete'] ='<a class="dest_subcontent-element" href="javascript:void(0)" onclick="javascript:hapusAkun(\''.$data['tr_id'].'\')" title="Membatalkan Menambah Akun debet">Batal</a>';		
			 $data['tr_id'] = 'id="'.$data['js_id'].'"';
		     $data['nomor'] = $no;
			 $no++;
			 
			 //=============start dipake componenet confirm delete ===============================	     
		     $idDelete = Dispatcher::Instance()->Encrypt($data['skenariodetail_id'].'*'.$data['skenario_id']);		 
		     $kirimVar = Dispatcher::Instance()->Encrypt($data['skenario_id']);
	         $urlAccept = $this->proc->moduleName.'|deleteSkenarioDetail|do|html-cari-'.$kirimVar;
             $urlReturn = $this->proc->moduleName.'|inputSkenario|view|html-cari-'.$kirimVar;
	         $label = 'Delete Skenario Detail';
	         $dataName = $data['nama'];
             $data['url_delete'] = Dispatcher::Instance()->GetUrl('confirm', 'confirmDelete', 'do', 'html').'&urlDelete='. $urlAccept.'&urlReturn='.$urlReturn.'&id='.$idDelete.'&label='.$label.'&dataName='.$dataName;               			   
			 //=============end  dipake componenet confirm delete ===============================
			 
			
		     $this->mrTemplate->AddVars('table-kredit', $data, 'KREDIT_');
             $this->mrTemplate->parseTemplate('table-kredit', 'a');	  	  
		  }	  
		  $viewsimpan = true;
	  } 
	     
		// print_r($this->data['kredit']['tambah']['tanda'][0]);
	  #merupakan data yang belum dimasukan dalam data base, karena terjadi error pada sesuatu.
	   $dua=sizeof($this->data['kredit']['tambah']);
	  if(isset($this->data['kredit']['tambah']) && !empty($this->data['kredit']['tambah']) and $satu!=$dua){
	 
	 	
          $this->mrTemplate->AddVar('bool-table-kredit-tambah', 'HAVE_DATA', 'YES');		  
	      foreach($this->data['kredit']['tambah'] as $data){
		     $data['js_id'] = 'tr-kredit-'.$data['id'].rand(1, 20);
		     $data['url_delete'] ='<a class="dest_subcontent-element" href="javascript:void(0)" onclick="javascript:hapusAkun(\''.$data['tr_id'].'\')" title="Membatalkan Menambah Akun kredit">Batal</a>';		
			 $data['tr_id'] = 'id="'.$data['js_id'].'"';
			 
			 
			// for($q=0; $q<=$satu - 1 ; $q++){$cek=$this->data['kredit']['datalist'][$q]['kode'];}
			
			 //if(empty($data['prosentase']) or $data['kode']!=$cek){
			 if(empty($data['prosentase'])){
			 //print_r($data);
		     $this->mrTemplate->AddVars('table-kredit-tambah', $data, 'KREDIT_');
             $this->mrTemplate->parseTemplate('table-kredit-tambah', 'a');	
			}
		  }
		  $viewsimpan = true;
		  
	  } 
	  
	  /* ====================end parsing data kredit */
	  
	  
	  if(!$viewsimpan) {
	     $this->mrTemplate->AddVar('content', 'DIV_BTNSIMPAN_STYLE', 'style="display:none"');
		 $this->mrTemplate->AddVar('content', 'TABLE_KREDIT_STYLE', 'style="display:none"');
		 $this->mrTemplate->AddVar('content', 'TABLE_DEBET_STYLE', 'style="display:none"');
	  }
	
		
	}

}
?>
