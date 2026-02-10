<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/realisasi_pencairan/response/ProcessRealisasiPencairan.proc.class.php';

class ViewRealisasiPencairan extends HtmlResponse {
   
   protected $proc;
   protected $data;
   
   function ViewRealisasiPencairan() {
     $this->proc = new ProcessRealisasiPencairan();   
   }
   
   
   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/realisasi_pencairan/template');
      $this->SetTemplateFile('view_realisasi_pencairan.html');
   }
   
   function ProcessRequest() {  
      $ta_id_selected =''; //inisialisasi
	  $userid=Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
	  //$unitkerja= $this->proc->UserUnitKerja->GetUnitKerjaUser($userid);       
	  $unitkerja= $this->proc->UserUnitKerja->GetSatkerUnitKerjaUserDua($userid);     
     $role = $this->proc->UserUnitKerja->GetRoleUser($userid);   
	  $this->data['unit_id']=$unitkerja['unit_kerja_id'];
	  $this->data['unit_nama']=$unitkerja['unit_kerja_nama'];
	  	 
	 	  
	  if(isset($_POST['btnTampilkan'])) { //pasti dari form pencarian :p	  
	     if(is_object($_POST['data']))
		    $this->data = $_POST['data']->AsArray();
		 else
		    $this->data = $_POST['data'];   
         
         $ta_id_selected = $this->data['ta_id'];
		 if(!isset($this->data['unit_id'])) {
		    $this->data['unit_id']=$unitkerja['unit_kerja_id'];
	        $this->data['unit_nama']=$unitkerja['unit_kerja_nama'];
		 }
      }
	 //############################ start combo box#################################3   
  		
	  $arr_ta = $this->proc->RealisasiPencairan->GetDataTahunAnggaran($ta_id_selected);
	  Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 'data[ta_id]', 
	     array('data[ta_id]', $arr_ta, $ta_id_selected, 'kosong', ' style="width:150px;" '), 
		 Messenger::CurrentRequest);
	  
	  $arr_program = $this->proc->RealisasiPencairan->GetDataProgram();
	  Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 'data[program_id]', 
	     array('data[program_id]', $arr_program, $this->data['program_id'], true, ' style="width:150px;" '), 
		 Messenger::CurrentRequest);
	  
	  $arr_jenis_kegiatan = $this->proc->RealisasiPencairan->GetDataJenisKegiatan();
	  Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 'data[jenis_kegiatan]', 
	     array('data[jenis_kegiatan]', $arr_jenis_kegiatan, $this->data['jenis_kegiatan'], true, ' style="width:150px;" '), 
		 Messenger::CurrentRequest);
	  
	  
      //############################ end combo box#################################3
	  
	  if($this->data['ta_id'] == '')
	     $this->data['ta_id'] = $ta_id_selected;
	  
	  
	        
      $itemViewed = 20;
      $currPage = 1;
      $startRec = 0 ;
	  
      if(isset($_GET['page'])) {
         $currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();  
         $startRec =($currPage-1) * $itemViewed;
      }
	  
	  
	  
	  $totalData = $this->proc->RealisasiPencairan->GetCount($this->data);
	  $dataList = $this->proc->RealisasiPencairan->GetData($startRec,$itemViewed, $this->data);
	  
	
      $url = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, 
               Dispatcher::Instance()->mSubModule, 
               Dispatcher::Instance()->mAction, 
               Dispatcher::Instance()->mType
               );
			   
      Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top', 
         array($itemViewed,$totalData, $url, $currPage), 
         Messenger::CurrentRequest); 

     
        //start menghandle pesan yang diparsing
	    
		$tmp=$this->proc->parsingUrl(__FILE__);		 
		if(isset($tmp['msg']))
		  $return['msg']=$tmp['msg'];  
		//end handle
       
      $return['data'] = $dataList;
      $return['start'] = $startRec+1;
	  $return['search'] = $search;	 
	  $return['unitkerja'] = $unitkerja;
		$return['role_name'] = $role['role_name'];
	  
	  //debug($this->data);
      
	return $return;
   }
   
   function ParseTemplate($data = NULL) {   
      
	  $this->mrTemplate->AddVar('content', 'URL_SEARCH', Dispatcher::Instance()->GetUrl('realisasi_pencairan', 'realisasiPencairan', 'view', 'html') );
      $this->mrTemplate->AddVar('content', 'URL_ADD', Dispatcher::Instance()->GetUrl('realisasi_pencairan', 'inputRealisasiPencairan', 'view', 'html') );	  	  
	  $this->mrTemplate->AddVar('content', 'POPUP_UNIT_KERJA', Dispatcher::Instance()->GetUrl('realisasi_pencairan', 'unitKerja', 'popup', 'html').'&pop=home');
	  $this->mrTemplate->AddVar('content', 'POPUP_SUBUNIT_KERJA', Dispatcher::Instance()->GetUrl('realisasi_pencairan', 'subUnitKerja', 'popup', 'html').'&pop=home');
	  $this->mrTemplate->AddVar('content', 'POPUP_DETAIL', Dispatcher::Instance()->GetUrl('realisasi_pencairan', 'detailRealisasiPencairan', 'popup', 'html'));
	  
	  $this->mrTemplate->AddVar('sub_unit', 'SEARCH_UNIT_NAMA', $this->data['unit_nama']);
	  $this->mrTemplate->AddVar('sub_unit', 'SEARCH_UNIT_ID', $this->data['unit_id']);
	  $this->mrTemplate->AddVar('sub_unit', 'SEARCH_SUBUNIT_ID', $this->data['subunit_id']);
	  $this->mrTemplate->AddVar('sub_unit', 'SEARCH_SUBUNIT_NAMA', $this->data['subunit_nama']);
	  
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
	  
	  if($data['role_name'] == "Administrator") {
	     $this->mrTemplate->AddVar('sub_unit', 'IS_UNIT_KERJA', 'ADMINISTRATOR');	
      }elseif($data['unitkerja']['is_unit_kerja']) {
	     $this->mrTemplate->AddVar('sub_unit', 'IS_UNIT_KERJA', 'YES');		 
	  } else {
	     $this->mrTemplate->AddVar('sub_unit', 'IS_UNIT_KERJA', 'NO');
		 $this->mrTemplate->AddVar('sub_unit', 'DATA_SUBUNIT_NAMA', $this->data['unit_nama']);
	  }
	  	  
      if (empty($data['data'])) {
         $this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'YES');
      } else {
         $this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'NO');
         $dataGrid = $data['data'];		
		 $i=0;
		 $index=0; // inisialisasi data yang akan dikirim
		 $no=1;
		 
		 $program_id=''; //inisialisasi program
		 $kegiatan_id=''; //inisialisasi kegiatan
		 $index_program=''; //inisialisasi index program yang aktif saat ini
		 $index_kegiatan=''; //inisialisasi index kegiatan yang aktif saat ini
		 
		 $dataList = array();
		 
		 
         //parsing  tampilan dan membuat menjadi array bertingkat yang ditempatkan pada $dataList
         for ($i=0; $i<sizeof($dataGrid);) {   		      
			   
			   //===========kondisi kalo berupa data sub_kegiatan ===========================
			   if(($program_id == $dataGrid[$i]['program_id']) && ($kegiatan_id == $dataGrid[$i]['kegiatan_id'])) { 
			     $idEnc = Dispatcher::Instance()->Encrypt($dataGrid[$i]['id']);
			     					
				
				
				 $dataKirim[$index]['class_name']='';				 				 
				 $dataKirim[$index]['tanggal']=$dataGrid[$i]['pr_tanggal'];
				 $dataKirim[$index]['kegiatan_kode']=$dataGrid[$i]['subkegiatan_kode'];
				 $dataKirim[$index]['kegiatan_nama']=$dataGrid[$i]['subkegiatan_nama'];		 
				 $dataKirim[$index]['nomor']=$no;
				 $dataKirim[$index]['nominal_usulan'] = $dataGrid[$i]['pr_nominal_usulan'];		 
				 $dataKirim[$index]['nominal_setuju'] = $dataGrid[$i]['pr_nominal_setuju'];	
                 
				 //kalo udah di approve gak usah ditampilin edit sama deletenya
                 if($dataGrid[$i]['pr_is_approve'] != 'Ya') {                 	 			 
				    $url_edit = Dispatcher::Instance()->GetUrl('realisasi_pencairan', 'inputRealisasiPencairan', 'view', 'html').'&grp='.$idEnc;
				    $dataKirim[$index]['url_edit']   = '<a class="xhr dest_subcontent-element"  href="'.$url_edit.'" title="Edit Data" ><img src="images/button-edit.gif" alt="Edit"></a>';
				 
				    //action delete
	                $urlAccept = 'realisasi_pencairan|deleteRealisasiPencairan|do|html-cari-';
                    $urlReturn = 'realisasi_pencairan|realisasiPencairan|view|html-cari-';
	                $label = 'Realisasi Pencairan';
	                $dataName = 'Sub Kegiatan : '.$dataGrid[$i]['subkegiatan_nama'];				 
                    $url_delete = Dispatcher::Instance()->GetUrl('confirm', 'confirmDelete', 'do', 'html').'&urlDelete='. $urlAccept.'&urlReturn='.$urlReturn.'&id='.$idEnc.'&label='.$label.'&dataName='.$dataName;               			   
	     
				 
                    $dataKirim[$index]['url_delete'] = '<a class="xhr dest_subcontent-element" href="'.$url_delete.'"><img src="images/button-delete.gif" alt="Delete"></a>';				    
			     }
				 
				 $dataKirim[$index]['url_detail'] = '<a class="dest_subcontent-element" href="javascript:void(0)" onclick="popupDetail('.$idEnc.')" title="Detil"><img src="images/button-bukubuka.gif" alt="Detil"></a>';				
						 
				 $dataList[$index_program]['data'][$index_kegiatan]['data'][$index]=$dataKirim[$index];
				 $no++;
			     $i++;
			   } elseif($program_id != $dataGrid[$i]['program_id']) { //klo informasi program 	====================	
                 $program_id = $dataGrid[$i]['program_id'];
				 $dataKirim[$index]['class_name']='table-common-even1';				 				 
				 $dataKirim[$index]['tanggal']='';
				 $dataKirim[$index]['kegiatan_kode']='<b>'.$dataGrid[$i]['program_kode'].'</b>';
				 $dataKirim[$index]['kegiatan_nama']='<b>'.$dataGrid[$i]['program_nama'].'</b>';				 
				 $dataKirim[$index]['nomor']='';	
				 $dataKirim[$index]['nominal_usulan'] = '';				 
				 $dataKirim[$index]['nominal_setuju'] = '';				 
				 $dataKirim[$index]['url_aksi']='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				 $index_program = $index;				 
				 $dataList[$index_program]=$dataKirim[$index];                 
			     	 
			   } elseif($kegiatan_id != $dataGrid[$i]['kegiatan_id']) { //klo informasi kegiatan  =============================
			     
				 $kegiatan_id = $dataGrid[$i]['kegiatan_id'];
				 $dataKirim[$index]['class_name']='table-common-even2';				 				 
				 $dataKirim[$index]['tanggal']='';
				 $dataKirim[$index]['kegiatan_kode']='<b>'.$dataGrid[$i]['kegiatan_kode'].'</b>';
				 $dataKirim[$index]['kegiatan_nama']='<b>'.$dataGrid[$i]['kegiatan_nama'].'</b>';				 
				 $dataKirim[$index]['nomor']='';
				 $dataKirim[$index]['nominal_usulan'] = '';				 
				 $dataKirim[$index]['nominal_setuju'] = '';				 
				 $dataKirim[$index]['url_aksi']='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';	                 
				 $index_kegiatan = $index;
				 $dataList[$index_program]['data'][$index_kegiatan]=$dataKirim[$index];	                 					 
			   }				 
			   
			   $index++;
		} //end for dataGrid
		
		//proses SUM penghitungan nilai pada program dan kegiatan
		foreach($dataList as $keyprogram => $program) { 
		   foreach($program['data'] as $keykegiatan => $kegiatan){
		      foreach($kegiatan['data'] as $key => $data){
			     $dataKirim[$keykegiatan]['nominal_usulan'] += $dataKirim[$key]['nominal_usulan'];			  
				 $dataKirim[$keykegiatan]['nominal_setuju'] += $dataKirim[$key]['nominal_setuju'];
			  }
			  $dataKirim[$keyprogram]['nominal_usulan'] +=  $dataKirim[$keykegiatan]['nominal_usulan'];
			  $dataKirim[$keyprogram]['nominal_setuju'] +=  $dataKirim[$keykegiatan]['nominal_setuju'];
		   }		   
		}
		//debug($dataKirim);
		
		//data sudah siap ditampilkan... kirim ke pat-template
		for($j=0;$j < sizeof($dataKirim);$j++) {
		   $dataKirim[$j]['nominal_usulan'] =number_format($dataKirim[$j]['nominal_usulan'], 2, ',', '.');
		   $dataKirim[$j]['nominal_setuju'] =number_format($dataKirim[$j]['nominal_setuju'], 2, ',', '.');
		   $this->mrTemplate->AddVars('data_item', $dataKirim[$j], 'DATA_');
           $this->mrTemplate->parseTemplate('data_item', 'a');			   
		}
		
		
	}
	
   }
   
   
  
}
?>
