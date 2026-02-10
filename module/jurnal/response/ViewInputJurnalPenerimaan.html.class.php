<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/jurnal_penerimaan/response/ProcJurnalPenerimaan.proc.class.php';

class ViewInputJurnalPenerimaan extends HtmlResponse {
   
   protected $data;
   protected $proc;
   
   function ViewInputJurnalPenerimaan() {
      $this->proc = new ProcJurnalPenerimaan;
	  $this->data = $this->proc->getPOST();
	  
	  	  
   }
   
   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/jurnal_penerimaan/template');
      $this->SetTemplateFile('view_input_jurnal_penerimaan.html');
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
	   
	   $return['disable'] = 'disabled="true"';
	   
    } elseif(isset($_GET['cari'])) { //sebenernya ini adalah hasil parsingan dari componen delete kalo batal cuma bisa mengrimkan id lewat variable id
	   if(is_object($_GET['cari']))
	      $grp = $_GET['cari']->mrVariable;
	   else 
	      $grp = $_GET['cari'];
	   
	   $jurnal['id'] = Dispatcher::Instance()->Decrypt($grp);
	   $jurnal['grp'] = $grp;	   
	   
	   $datadetail = $this->proc->db->GetDataById($jurnal['id']);  
      
    }	
	
	//parsing mana data debet mana data kredit untuk ditampilkan di view
	if(isset($datadetail)){
	   $this->data['referensi_id'] = $datadetail[0]['referensi_id'];
	   $this->data['referensi_nama'] = $datadetail[0]['referensi_nama'];
	   $this->data['referensi_nilai'] = $datadetail[0]['referensi_nilai'];
	   $this->data['referensi_tanggal'] = $datadetail[0]['referensi_tanggal'];
	   $this->data['pembukuan_referensi_id'] = $datadetail[0]['pembukuan_referensi_id'];
	   
	   foreach($datadetail as $val){
	      
		  //ini adalah data debet
		  if($val['detail_status']=='D') {
            $this->data['debet']['coa_id'] = $val['coa_id'];
			   $this->data['debet']['nilai'] = $val['detail_nilai'];
			   $this->data['debet']['detail_id'] = $val['detail_id'];
            $this->data['debet']['coa_nama'] = $val['coa_nama'];  
		  } else {
            $akun['id'] = $val['coa_id'];
            $akun['detail_id'] = $val['detail_id'];
            $akun['kode'] = $val['coa_kode'];
            $akun['nama'] = $val['coa_nama'];
            $akun['keterangan'] = $val['detail_keterangan'];
            $akun['nilai'] = $val['detail_nilai'];
            $this->data['kredit']['datalist'][] = $akun;
            unset($akun);
		  }
	      
	   }	   
	   $this->data['action']='update';
	}
	
	  
	
	  $arr_coa = $this->proc->db->GetComboCoa('debet');
	  $coaid = isset($this->data['debet']['coa_id']) ? $this->data['debet']['coa_id'] : '';
      
	  Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 'data[debet][coa_id]', 
	     array('data[debet][coa_id]', $arr_coa, $coaid, 'kosong', ' style="width:150px;" '), 
		 Messenger::CurrentRequest);
	  
	
	 //start menghandle pesan yang diparsing	    
		$tmp=$this->proc->parsingUrl(__FILE__);		 
		if(isset($tmp['msg'])) {		   
		   $this->data = $tmp['data'];
		   $return['msg']=$tmp['msg'];  
 	   }		  
     //end handle 
	 
	 
	 if(isset($dataGrid)){
      $this->data['debet']['datalist'] = $dataGrid['debet'];
		$this->data['kredit']['datalist'] = $dataGrid['kredit'];
	 }
	 
	 
	 
		
	return $return;
   }
   
   function ParseTemplate($data = NULL) {
      if ($this->data['action'] == 'add')
      {
         $action = $this->proc->moduleAdd;
         $this->mrTemplate->AddVar('content', 'MODE', 'Tambah');
      }
      else
      {
         $action = $this->proc->moduleUpdate;
         $this->mrTemplate->AddVar('content', 'MODE', 'Ubah');
      }
      $this->mrTemplate->AddVar('content', 'URL_ACTION', Dispatcher::Instance()->GetUrl($this->proc->moduleName, $action, 'do', 'html') );      
	   $this->mrTemplate->AddVar('content', 'URL_LIST_JURNAL_PENERIMAAN', Dispatcher::Instance()->GetUrl($this->proc->moduleName, 'jurnalPenerimaan', 'view', 'html') );      
      $this->mrTemplate->AddVar('content', 'POPUP_COA', Dispatcher::Instance()->GetUrl($this->proc->moduleName, 'coa', 'popup', 'html') );
      $this->mrTemplate->AddVar('content', 'POPUP_COA_DEBET', Dispatcher::Instance()->GetUrl($this->proc->moduleName, 'PopupCoaDebet', 'view', 'html') );
	   $this->mrTemplate->AddVar('content', 'POPUP_REFERENSI', Dispatcher::Instance()->GetUrl($this->proc->moduleName, 'referensiTransaksi', 'popup', 'html') );	  	  
	  
	  $this->mrTemplate->AddVar('content', 'REFERENSI_ID', $this->data['referensi_id'] );	  	  
	  $this->mrTemplate->AddVar('content', 'REFERENSI_NAMA', $this->data['referensi_nama'] );	  	  
	  $this->mrTemplate->AddVar('content', 'REFERENSI_KETERANGAN', ($this->data['referensi_keterangan']) ? $this->data['referensi_keterangan'] :$this->data['kredit']['datalist'][0]['keterangan']);	  	  
	  $this->mrTemplate->AddVar('content', 'DEBET_DETAIL_ID', $this->data['debet']['detail_id'] );	  	  	  
	  $this->mrTemplate->AddVar('content', 'DEBET_NILAI', $this->data['debet']['nilai'] );	  	  	  
	  $this->mrTemplate->AddVar('content', 'DEBET_NILAI_VIEW', number_format($this->data['debet']['nilai'], 2 , ',' ,'.') );	  	  
	  $this->mrTemplate->AddVar('content', 'REFERENSI_TANGGAL', $this->data['referensi_tanggal'] );	  	  
	  $this->mrTemplate->AddVar('content', 'PEMBUKUAN_REFERENSI_ID', $this->data['pembukuan_referensi_id'] );
     $this->mrTemplate->AddVar('content', 'COA_DEBET_NAMA', $this->data['debet']['coa_nama'] ); 
	  $this->mrTemplate->AddVar('content', 'COA_DEBET_ID', $this->data['debet']['coa_id'] );
	  $this->mrTemplate->AddVar('content', 'DISABLE', $data['disable']);
	  #print_r($this->data);
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
        
      #print_r($this->data['debet']);
	  //parsing data yang akan didelete
	  if(isset($this->data['deleted']['id'])){
	    $this->mrTemplate->AddVar('deleted_value', 'HAVE_DATA', 'YES');
		 foreach($this->data['deleted']['id'] as $val){		
             $tmp['id'] = $val;
		     $this->mrTemplate->AddVars('deleted_item', $tmp, 'DELETED_');
             $this->mrTemplate->parseTemplate('deleted_item', 'a');	  	  
		  }	  
	  
	  }
	  
	   /* ===================start parsing data kredit */
	  #data sudah ada dalam database
	  if(isset($this->data['kredit']['datalist'])) {
          $this->mrTemplate->AddVar('bool-table-kredit', 'HAVE_DATA', 'YES');	
          $no=1;		  
	      foreach($this->data['kredit']['datalist'] as $data){
		     $data['nomor'] = $no;
			 $no++;			 
		     $this->mrTemplate->AddVars('table-kredit', $data, 'KREDIT_');
             $this->mrTemplate->parseTemplate('table-kredit', 'a');	  	  
		  }	  		  
	  } 
	     
	  #merupakan data yang belum dimasukan dalam data base, karena terjadi error pada sesuatu.
	  if(isset($this->data['kredit']['tambah']) && !empty($this->data['kredit']['tambah'])) {	
          $this->mrTemplate->AddVar('bool-table-kredit-tambah', 'HAVE_DATA', 'YES');		  
	      foreach($this->data['kredit']['tambah'] as $data){
		     $data['tr_id'] = 'tr-kredit-'.$data['id'].rand(1, 20);
		     $data['url_delete'] ='<a class="dest_subcontent-element" href="javascript:void(0)" onclick="javascript:hapusAkun(\''.$data['tr_id'].'\')" title="Membatalkan Menambah Akun kredit">Batal</a>';		
			 $data['tr_id'] = 'id="'.$data['tr_id'].'"';
		     $this->mrTemplate->AddVars('table-kredit-tambah', $data, 'KREDIT_');
             $this->mrTemplate->parseTemplate('table-kredit-tambah', 'a');	  	  
		  }		  
	  } 
	  
	  /* ====================end parsing data kredit */  
		
	}

}
?>
