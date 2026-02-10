<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/approval_jurnal/response/ProcApprovalJurnal.proc.class.php';

class ViewInputApprovalJurnal extends HtmlResponse {
   
   protected $data;
   protected $proc;
   
   function ViewInputApprovalJurnal() {
      $this->proc = new ProcApprovalJurnal;
	  $this->data = $this->proc->getPOST();
	  
	  	  
   }
   
   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/approval_jurnal/template');
      $this->SetTemplateFile('view_input_approval_jurnal.html');
   } 
   
   
   function ProcessRequest() {	
    $this->data['action']='add';
	
	
    if(isset($_GET['grp'])) {
	   if(is_object($_GET['grp']))
	      $grp = $_GET['grp']->mrVariable;
	   else 
	      $grp = $_GET['grp'];
	   
	   $jurnal['id'] = Dispatcher::Instance()->Decrypt($grp);
	   $jurnal['grp'] = $grp;	   
	   
	   $datadetail = $this->proc->db->GetDataById($jurnal['id']);   
	   
	   //debug($datadetail);
	   
	   
	   
    } elseif(isset($_GET['cari'])) { //sebenernya ini adalah hasil parsingan dari componen delete kalo batal cuma bisa mengrimkan id lewat variable id
	   if(is_object($_GET['cari']))
	      $grp = $_GET['cari']->mrVariable;
	   else 
	      $grp = $_GET['cari'];
	   
	   $jurnal['id'] = Dispatcher::Instance()->Decrypt($grp);
	   $jurnal['grp'] = $grp;	   
	   
	   $datadetail = $this->proc->db->GetDataById($jurnal['id']);   
    }
	
	
	//parsing mana data kredit mana data debet untuk ditampilkan di view
	if(isset($datadetail)){
	   $this->data['referensi_id'] = $datadetail[0]['referensi_id'];
	   $this->data['referensi_nama'] = $datadetail[0]['referensi_nama'];
	   $this->data['referensi_nilai'] = $datadetail[0]['referensi_nilai'];
	   $this->data['referensi_tanggal'] = $datadetail[0]['referensi_tanggal'];
	   $this->data['pembukuan_referensi_id'] = $datadetail[0]['pembukuan_referensi_id'];
	   
	   foreach($datadetail as $val){
	      
		  //ini adalah data kredit
		  if($val['detail_status']=='K') {
		     $this->data['kredit']['coa_id'] = $val['coa_id'];
			 $this->data['kredit']['nilai'] = $val['detail_nilai'];
			 $this->data['kredit']['detail_id'] = $val['detail_id'];
		  } else {
		     $akun['id'] = $val['coa_id'];
			 $akun['detail_id'] = $val['detail_id'];
			 $akun['kode'] = $val['coa_kode'];
			 $akun['nama'] = $val['coa_nama'];
			 $akun['keterangan'] = $val['detail_keterangan'];
			 $akun['nilai'] = $val['detail_nilai'];
			 $this->data['debet']['tambah'][] = $akun;
			 unset($akun);
		  }
	      
	   }	   
	   $this->data['action']='update';
	}
	
	  
	
	  $arr_coa = $this->proc->db->GetComboCoa('kredit');
	  $coaid = isset($this->data['kredit']['coa_id']) ? $this->data['kredit']['coa_id'] : '';
      
	  Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 'data[kredit][coa_id]', 
	     array('data[kredit][coa_id]', $arr_coa, $coaid, 'kosong', ' style="width:200px;" '), 
		 Messenger::CurrentRequest);
	  
	
	 //start menghandle pesan yang diparsing	    
		$tmp=$this->proc->parsingUrl(__FILE__);		 
		if(isset($tmp['msg'])) {		   
		   $this->data = $tmp['data'];
		   $return['msg']=$tmp['msg'];  
 	   }		  
     //end handle 
      //echo "<pre>";
     // print_r($this->data);
     // echo "</pre>";
	 
	 
	 if(isset($dataGrid)){
	    $this->data['kredit']['datalist'] = $dataGrid['kredit'];
		$this->data['debet']['datalist'] = $dataGrid['debet'];
	 }
	 
		
	return $return;
   }
   
   function ParseTemplate($data = NULL) {  
      		
      $action = ($this->data['action'] == 'add') ? $this->proc->moduleAdd : $this->proc->moduleUpdate; 
		
	  $this->mrTemplate->AddVar('content', 'URL_ACTION', Dispatcher::Instance()->GetUrl($this->proc->moduleName, $action, 'do', 'html') );      
	  $this->mrTemplate->AddVar('content', 'URL_LIST_JURNAL_PENERIMAAN', Dispatcher::Instance()->GetUrl($this->proc->moduleName, 'jurnalPengeluaran', 'view', 'html') );      
      $this->mrTemplate->AddVar('content', 'POPUP_COA', Dispatcher::Instance()->GetUrl($this->proc->moduleName, 'coa', 'popup', 'html') );	  	  
	  $this->mrTemplate->AddVar('content', 'POPUP_REFERENSI', Dispatcher::Instance()->GetUrl($this->proc->moduleName, 'referensiTransaksi', 'popup', 'html') );	  	  
	  
	  $this->mrTemplate->AddVar('content', 'REFERENSI_ID', $this->data['referensi_id'] );	  	  
	  $this->mrTemplate->AddVar('content', 'REFERENSI_NAMA', $this->data['referensi_nama'] );	  	  
	  $this->mrTemplate->AddVar('content', 'KREDIT_DETAIL_ID', $this->data['kredit']['detail_id'] );	  	  	  
	  $this->mrTemplate->AddVar('content', 'KREDIT_NILAI', $this->data['kredit']['nilai'] );	  	  	  
	  $this->mrTemplate->AddVar('content', 'KREDIT_NILAI_VIEW', 'Rp '.number_format($this->data['kredit']['nilai'], 2 , ',' ,'.') );	  	  
	  $this->mrTemplate->AddVar('content', 'REFERENSI_TANGGAL', $this->data['referensi_tanggal'] );	  	  
	  $this->mrTemplate->AddVar('content', 'PEMBUKUAN_REFERENSI_ID', $this->data['pembukuan_referensi_id'] );	  	  
	  
	  
	  
	  
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
	  if(isset($this->data['debet']['datalist']) && !empty($this->data['kredit']['datalist'])) {
          $this->mrTemplate->AddVar('bool-table-debet', 'HAVE_DATA', 'YES');	
          $no=1;		  
	      foreach($this->data['debet']['datalist'] as $data){
		     $data['nomor'] = $no;
			 $no++;
			 /*
			 //=============start dipake componenet confirm delete ===============================	     
		     $idDelete = Dispatcher::Instance()->Encrypt($data['skenariodetail_id'].'*'.$data['skenario_id']);		 
		     $kirimVar = Dispatcher::Instance()->Encrypt($data['skenario_id']);
	         $urlAccept = $this->proc->moduleName.'|deleteSkenarioDetail|do|html-cari-'.$kirimVar;
             $urlReturn = $this->proc->moduleName.'|inputSkenario|view|html-cari-'.$kirimVar;
	         $label = 'Delete Jurnal Pengeluaran';
	         $dataName = $data['nama'];
             $data['url_delete'] = Dispatcher::Instance()->GetUrl('confirm', 'confirmDelete', 'do', 'html').'&urlDelete='. $urlAccept.'&urlReturn='.$urlReturn.'&id='.$idDelete.'&label='.$label.'&dataName='.$dataName;               			   
			 //=============end  dipake componenet confirm delete ===============================
			 */
		     $this->mrTemplate->AddVars('table-debet', $data, 'DEBET_');
             $this->mrTemplate->parseTemplate('table-debet', 'a');	  	  
		  }	  
		  $viewsimpan = true;
	  } 
	     
	  #merupakan data yang belum dimasukan dalam data base, karena terjadi error pada sesuatu.
	  if(isset($this->data['debet']['tambah']) && !empty($this->data['debet']['tambah'])) {	
          $this->mrTemplate->AddVar('bool-table-debet-tambah', 'HAVE_DATA', 'YES');		  
	      foreach($this->data['debet']['tambah'] as $data){
		     $data['tr_id'] = 'tr-debet-'.$data['id'].rand(1, 20);
		     $data['url_delete'] ='<a class="dest_subcontent-element" href="javascript:void(0)" onclick="javascript:hapusAkun(\''.$data['tr_id'].'\')" title="Membatalkan Menambah Akun debet">Batal</a>';		
			 $data['tr_id'] = 'id="'.$data['tr_id'].'"';
		     $this->mrTemplate->AddVars('table-debet-tambah', $data, 'DEBET_');
             $this->mrTemplate->parseTemplate('table-debet-tambah', 'a');	  	  
		  }
		  $viewsimpan = true;
	  } 
	  
	  /* ====================end parsing data debet */
	  
	  
	  if(!$viewsimpan) {
	     $this->mrTemplate->AddVar('content', 'DIV_BTNSIMPAN_STYLE', 'style="display:none"');
		 $this->mrTemplate->AddVar('content', 'TABLE_DEBET_STYLE', 'style="display:none"');
		 $this->mrTemplate->AddVar('content', 'TABLE_KREDIT_STYLE', 'style="display:none"');
	  }
	
		
	}

}
?>
