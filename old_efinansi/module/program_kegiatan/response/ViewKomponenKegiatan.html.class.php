<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/program_kegiatan/response/ProcessKomponenKegiatan.proc.class.php';

class ViewKomponenKegiatan extends HtmlResponse {
   
   protected $proc;
   protected $SubKegiatan;
   protected $KomponenKegiatan;
   protected $data;
   protected $msg;
   
   function ViewKomponenKegiatan() {
     if(isset($_POST['data']))
         if(is_object($_POST['data']))	  
	        $this->data=$_POST['data']->AsArray();		 
		 else
		    $this->data=$_POST['data'];
			
     $this->proc = new ProcessKomponenKegiatan();
	 $this->SubKegiatan = new SubKegiatan();
	 $this->KomponenKegiatan = new KomponenKegiatan();   
   }
   
   function CleanVar(){
     $this->data['komponen_id']='';
	 $this->data['komponen_nama']='';
	 $this->data['komponen_nominal']='';
	 $this->data['aksi']='add';
	 /**
	 $dataUnit = $this->SubKegiatan->GetUnitKerjaRef();
	 if(is_array($dataUnit)){
		foreach($dataUnit as $key => $value){
			$this->data['unitkerjaid'][$value['unitkerjaId']]='';
		}
	  }	
	  */ 
   }
   
   
   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/program_kegiatan/template');
      $this->SetTemplateFile('view_komponen_kegiatan.html');
   }
   
   function ProcessRequest() {  
     //loading pertama kali inilah bentuknya :D //tapi ada kemungkinan hasil pendeletan
     if(isset($_GET['grp'])){ 
	   $grp = $_GET['grp']->mrVariable;	   
	   $this->data['kegref_id'] = $grp;
	   $this->data['aksi']='add';   
	   
	   //start menghandle pesan yang diparsing	  //apakah ini hasi dari parsing delete?  
		$tmp=$this->proc->parsingUrl(__FILE__);		 
		if(isset($tmp['msg']))
		  $return['msg']=$tmp['msg'];  
		//end handle
       	   
	 
	 //terjadi aksi simpan atau update	 
	 } elseif(isset($_GET['grpx'])) {
	   $grpx = $_GET['grpx']->mrVariable;
	   $grpx = Dispatcher::Instance()->Decrypt($grpx);
	   $grpx= explode('|',$grpx);   
	   
	   $this->data= $this->proc->KomponenKegiatan->GetDataById($grpx[0],$grpx[1]);
	   $this->data['aksi']='edit';
	   $this->data['kegref_id']=$grpx[0];
	   
     } elseif(isset($_POST['proses'])) {
	   $grp = $this->data['kegref_id']; 
	   if($this->data['aksi']=='add')
	      $ok=$this->proc->Add();
	   elseif($this->data['aksi']=='edit')
	      $ok=$this->proc->Update();	   
	   
	   if($ok)
	      $this->CleanVar();
     
	 //pembatalan dari delete tpi kudu difilter lagi
     } elseif(isset($_GET['cari'])) { 
	   $cari = $_GET['cari']->mrVariable;	 
	   $cari = explode("$",$cari);
	   if(count($cari) > 1) { //nah ini berarti bener2 cancel :D soale kadang2 berasal 
	      $this->data['kegref_id']=$cari[1];
		  $this->data['aksi']='add';	   
	   }
     }  
	 
	 if(!isset($this->data['detail']))
	    $this->data['detail'] = $this->proc->KomponenKegiatan->GetDataDetail($this->data['kegref_id']);
	 
	 if(!$return['msg'])
	    $return['msg'] = $this->proc->msg;
	 //paging
	        
      $itemViewed = 20;
      $currPage = 1;
      $startRec = 0 ;
	  
      if(isset($_GET['page'])) {
         $currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();  
         $startRec =($currPage-1) * $itemViewed;
      }
	  
	  
	  
	  $totalData = $this->proc->KomponenKegiatan->GetCount($this->data['kegref_id']);
	  $dataGrid = $this->proc->KomponenKegiatan->GetData($startRec,$itemViewed, $this->data['kegref_id']);
	  //debug($dataList);
	  
	
      $url = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, 
               Dispatcher::Instance()->mSubModule, 
               Dispatcher::Instance()->mAction, 
               Dispatcher::Instance()->mType
               ).
               '&grp='.$grp;
			   
      Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top', 
         array($itemViewed,$totalData, $url, $currPage), 
         Messenger::CurrentRequest); 

           
      $return['dataGrid'] = $dataGrid;
      $return['start'] = $startRec+1;
	  $return['aksi'] = $aksi;
	  
	  //unit kerja ref
	  ///$this->data['unit_kerja_ref']= $this->SubKegiatan->GetUnitKerjaRef();
	  //debug($this->data);
      
	return $return;
   }
   
    /**
    * fungsi ListUnitKerjaRef
    * @param $kompId = id dari kegiatan ref ID (kegrefid)
    * @todo menampilkan list unit kerja ref
    */
  	function ListUnitKerjaRef($kompid)
   {
        $dataUnit = $this->KomponenKegiatan->GetListUnitKerja($kompid);
		if(count($dataUnit) > 0 ){
		  $str ='';
   			foreach($dataUnit as $key => $value){
   			  
				//jika unitkerjaId berada pada tabel finansi_pa_komponen_unit_kerja maka 
				//centang cekbox
                
				//if($this->data['unitkerjaid'][$value['unitkerja_id']]){
				//    $cek = 'checked="checked"';
					//$this->mrTemplate->AddVar('unit_kerja_ref', 'UK_CHECKED', 'checked="checked"');	
				//}else{
					if($value['total'] > 0 && !isset($this->data['checkbox'])) {
					   $cek = 'checked="checked"';
						//$this->mrTemplate->AddVar('unit_kerja_ref', 'UK_CHECKED','checked="checked"');
					}else { 
					   $cek = '';
						//$this->mrTemplate->AddVar('unit_kerja_ref', 'UK_CHECKED', '');
					}
				//}		
				
				if($value['unitkerja_parent'] == 0){
				    $str .= '<li style="padding:2px 0 2px 0;">'.
                            '<input type="checkbox" name="data[unitkerjaid]['.
                            $value['unitkerja_id'].']" class="CheckBoxFW_parent" value="'.
                            $value['unitkerja_id'].'" '.$cek.' />&nbsp;'.$value['unitkerja_nama'].'</li>';
				    /**
				    $this->mrTemplate->addVar('unit_kerja_ref', 'CLASS_NAME', 'YES');
					$this->mrTemplate->addVar('unit_kerja_ref', 'UK_PARENT', 'YES');
					$this->mrTemplate->AddVars('unit_kerja_ref', $value, 'UK_');
   					$this->mrTemplate->parseTemplate('unit_kerja_ref', 'a');
                    */
   				} else {
                    $str .='<ul style="list-style: none; margin:0; padding:3px 0 0 20px;"><li>'.	
                           '<input type="checkbox" name="data[unitkerjaid]['.
                           $value['unitkerja_id'].']" class="CheckBoxFW_child" value="'.
                           $value['unitkerja_id'].'" '.$cek.'/>&nbsp;'.
                           $value['unitkerja_nama'].'</li></ul>';   				 
   				     /**
  					$this->mrTemplate->addVar('unit_kerja_ref', 'UK_PARENT', 'NO');
   						$this->mrTemplate->AddVars('unit_kerja_ref',$value, 'UK_');
   						$this->mrTemplate->parseTemplate('unit_kerja_ref', 'a');
                        */
   				} 
                  $this->mrTemplate->addVar('content', 'LIST_UNIT_KERJA', $str);               
	       }
      }
		  
   }
   
   
   function ParseTemplate($data = NULL) {   
      	
	//add list unit kerja ref
		$this->ListUnitKerjaRef($this->data['komponen_id']);
	//end unit kerja ref
	  $this->mrTemplate->AddVar('content', 'DETAIL_TAHUNPERIODE', $this->data['detail']['tahunperiode']);
	  $this->mrTemplate->AddVar('content', 'DETAIL_PROGRAM', $this->data['detail']['program']);
	  $this->mrTemplate->AddVar('content', 'DETAIL_KEGIATAN', $this->data['detail']['kegiatan']);
	  $this->mrTemplate->AddVar('content', 'DETAIL_SUBKEGIATAN', $this->data['detail']['subkegiatan']);
	  
	  $this->mrTemplate->AddVar('content', 'DATA_KOMPONEN_ID', $this->data['komponen_id']);
	  $this->mrTemplate->AddVar('content', 'DATA_KOMPONEN_NAMA', $this->data['komponen_nama']);
	  $this->mrTemplate->AddVar('content', 'DATA_KOMPONEN_NOMINAL', $this->data['komponen_nominal']);
	  
	  $this->mrTemplate->AddVar('content', 'DATA_KEGREF_ID', $this->data['kegref_id']);
	  
	  if($this->data['aksi'] == 'add') {
	     $this->mrTemplate->AddVar('content', 'LABEL_AKSI', 'Tambah ');
		 $this->mrTemplate->AddVar('content', 'BUTTON_AKSI', 'Simpan');
		
		 $url_aksi = 'addKomponenKegiatan';
	  } else {
	     $this->mrTemplate->AddVar('content', 'LABEL_AKSI', 'Edit ');
		 $this->mrTemplate->AddVar('content', 'BUTTON_AKSI', 'Update');
		
		 $url_aksi = 'updateKomponenKegiatan';
	  }
	  
	  $this->mrTemplate->AddVar('content', 'AKSI', $this->data['aksi']);
	    
	  $this->mrTemplate->AddVar('content', 'URL_RETURN', Dispatcher::Instance()->GetUrl('program_kegiatan', 'programKegiatan', 'view', 'html'));
	  $this->mrTemplate->AddVar('content', 'URL_BATAL', Dispatcher::Instance()->GetUrl('program_kegiatan', 'komponenKegiatan', 'view', 'html').'&grp='.$this->data['kegref_id']);	  
      $this->mrTemplate->AddVar('content', 'URL_ACTION', Dispatcher::Instance()->GetUrl('program_kegiatan', 'komponenKegiatan', 'view', 'html'));	  
	  $this->mrTemplate->AddVar('content', 'POPUP_KOMPONEN', Dispatcher::Instance()->GetUrl('program_kegiatan', 'komponen', 'popup', 'html').'&grp='.$this->data['kegref_id']);
	  
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
	  	  
      if (empty($data['dataGrid'])) {
         $this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'YES');
      } else {
         $this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'NO');
         $dataGrid = $data['dataGrid'];		
		 $i=0;
		 $index=0; // inisialisasi data yang akan dikirim		 
		 //debug($dataGrid);
		
		
		for($j=0;$j < sizeof($dataGrid);$j++) {		
		   $dataGrid[$j]['nomor'] = $j+$data['start'];
		   
		   $no = $j+$data['start'];
				$dataGrid[$j]['nomor'] = $no;
				if ($no % 2 != 0) $dataGrid[$j]['class_name'] = 'table-common-even';
				else $dataGrid[$j]['class_name'] = '';
		   
		   $url = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, 
                                                 Dispatcher::Instance()->mSubModule, 
                                                 Dispatcher::Instance()->mAction, 
                                                 Dispatcher::Instance()->mType
                                                ); 
		   $dataGrid[$j]['url_edit'] = $url
		   //.'&grp='.Dispatcher::Instance()->Encrypt($this->data['kegref_id'])
		   .'&grpx='.Dispatcher::Instance()->Encrypt($this->data['kegref_id'].'|'.$dataGrid[$j]['komponen_id']);
		   
		    //mulai bikin tombol delete
			//$additional=$this->data['detail']['tahunperiode'].'$'.$this->data['detail']['program'].'$'.$this->data['detail']['kegiatan'].'$'.$this->data['detail']['subkegiatan'];
		    $urlAccept = 'program_kegiatan|deleteKomponenKegiatan|do|html-cari-'.$cari;
			$urlReturn = 'program_kegiatan|komponenKegiatan|view|html-cari-'.$cari.'$'.$this->data['kegref_id'];
			
			$label = Dispatcher::Instance()->Encrypt('Manajemen Komponen Kegiatan');
			$idEnc = Dispatcher::Instance()->Encrypt($dataGrid[$j]['kegref_id'].','.$dataGrid[$j]['komponen_id']);
			$dataName = Dispatcher::Instance()->Encrypt($dataGrid[$j]['komponen_nama']);
			$message = '';//'Penghapusan Data ini akan menghapus semua kegiatan dan sub kegiatan dibawahnya';
					
			$urlDelete = Dispatcher::Instance()->GetUrl('confirm', 'confirmDelete', 'do', 'html').'&urlDelete='. $urlAccept.'&urlReturn='.$urlReturn.'&id='.$idEnc.'&label='.$label.'&dataName='.$dataName.'&message='.$message;		
			$dataGrid[$j]['url_delete']=$urlDelete;
			 //selesai bikin tombol delete
			$dataGrid[$j]['komponen_nominal']=number_format($dataGrid[$j]['komponen_nominal'], 0, ',', '.');
           
		   
			
		   $this->mrTemplate->AddVars('data_item', $dataGrid[$j], 'DATA_');
           $this->mrTemplate->parseTemplate('data_item', 'a');			   
		   
		}
		//debug($dataGrid);
		
		
	}
	
   }
   
   
  
}
?>