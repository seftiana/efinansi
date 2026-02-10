<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
			'module/adjustment_pengeluaran/business/AdjustmentPengeluaran.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
			'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewAdjustmentPengeluaran extends HtmlResponse {
   
   protected $RekapUnitKerja;
   protected $data;
   
   function ViewAdjustmentPengeluaran() {
     $this->RekapUnitKerja = new AdjustmentPengeluaran();   
   }
   
   
   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') . 
	  		'module/adjustment_pengeluaran/template');
      $this->SetTemplateFile('view_adjustment_pengeluaran.html');
   }
   
   function ProcessRequest() {  
     $objUserUnitKerja = new UserUnitKerja;
	 $userid=Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
	 $unitkerja= $objUserUnitKerja->GetUnitKerjaUser($userid); 
	 
	 if(isset($_POST['btnTampilkan'])) { //pasti dari form pencarian :p	  
	     if(is_object($_POST['data']))
		    $this->data = $_POST['data']->AsArray();
		 else
		    $this->data = $_POST['data'];   
      
	 //print_r($this->data);
     $ta_id_selected = $this->data['ta_id'];
		 
		 if(!isset($this->data['unit_id'])) {
		    $this->data['unit_id']=$unitkerja['unit_kerja_id'];
	        $this->data['unit_nama']=$unitkerja['unit_kerja_nama'];
		 }
		 
		 $search_nav =  '&data[ta_id]='.$this->data['ta_id'].
		 				'&data[unit_id]='.$this->data['unit_id'].
						 '&data[unit_nama]='.$this->data['unit_nama'];
						 
      } elseif(isset($_GET['data'])) {	
         if(is_object($_GET['data']))
		    $this->data = $_GET['data']->AsArray();
		 else
		    $this->data = $_GET['data'];   
         
        $ta_id_selected = $this->data['ta_id'];
		 
		 if(!isset($this->data['unit_id'])) {
		    $this->data['unit_id']=$unitkerja['unit_kerja_id'];
	        $this->data['unit_nama']=$unitkerja['unit_kerja_nama'];
		 }
		 
		 $search_nav =  '&data[kodenama]='.$this->data['kodenama'].
		      			'&data[ta_id]='.$this->data['ta_id'].
	                    '&data[unit_id]='.$this->data['unit_id'].
						'&data[unit_nama]='.$this->data['unit_nama'];
						      
	  } else {
		 $this->data['unit_id']= $unitkerja['unit_kerja_id'];
		 $this->data['unit_nama']= $unitkerja['unit_kerja_nama'];
		 $search_nav='';
	  }
	  
	  
   
     //############################ start combo box tahun anggaran #################################3     		
	  $arr_ta = $this->RekapUnitKerja->GetDataTahunAnggaran($ta_id_selected,$ta_nama_selected);
	  Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 'data[ta_id]', 
	     array('data[ta_id]', $arr_ta, $ta_id_selected, 'kosong', ' style="width:150px;" id="ta_id" '), 
		 Messenger::CurrentRequest);
	  
	    //############################ end combo box#################################3
     
     if(!isset($this->data['ta_id'])) {
	    $this->data['ta_id']= $ta_id_selected;
		$this->data['ta_nama']= $ta_nama_selected;
	 }
	 
	  /*echo "<br/>"; echo "<br/>";
	  print_r($this->data);
	  */
	  
	  $itemViewed = 20;
      $currPage = 1;
      $startRec = 0 ;
	  
      if(isset($_GET['page'])) {
         $currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();  
         $startRec =($currPage-1) * $itemViewed;
      }
	 $return['data'] = $this->RekapUnitKerja->GetData($startRec,$itemViewed,$this->data) ;	   
	 $totalData =  $this->RekapUnitKerja->GetCount();
	 
	 #echo sizeof($return['data']);
	 for($i=0;$i<sizeof($return['data']);$i++):
		$return['data_realisasi'] = $this->RekapUnitKerja->GetCountDataRealisasiPencairan($return['data'][$i]['kegiatandetail_id']);
	 endfor;
	 
	 $url = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, 
               Dispatcher::Instance()->mSubModule, 
               Dispatcher::Instance()->mAction, 
               Dispatcher::Instance()->mType
               ).$search_nav;			   
			   
      Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top', 
         array($itemViewed,$totalData, $url, $currPage), 
         Messenger::CurrentRequest);
	 
	$return['unitkerja'] = $unitkerja;
    $return['startRec'] =$startRec;
	$return['itemViewed'] = $itemViewed;
	$return['totalSubUnitKerja'] = $this->RekapUnitKerja->GetTotalSubUnitKerja($unitkerja['unit_kerja_id']);
	//debug($return['resume']);
	
	return $return;
   }
   
   function ParseTemplate($data = NULL) {   
     //debug($this->data);
	$this->mrTemplate->AddVar('content', 'KODENAMA', $this->data['kodenama']); 	 
    $this->mrTemplate->AddVar('sub_unit', 'SEARCH_UNIT_ID', $this->data['unit_id']);   
    $this->mrTemplate->AddVar('sub_unit', 'SEARCH_UNIT_NAMA', $this->data['unit_nama']);   
    $this->mrTemplate->AddVar('content', 'SEARCH_TA_NAMA', $this->data['ta_nama']); 	 
    $this->mrTemplate->AddVar('sub_unit', 'POPUP_UNIT', Dispatcher::Instance()->GetUrl('adjustment_pengeluaran', 'UnitKerja', 'popup', 'html'));   
    
    $this->mrTemplate->AddVar('content', 'SEARCH_PROGRAM_NAMA', $this->data['program_nama']); 
    $this->mrTemplate->AddVar('content', 'SEARCH_PROGRAM_ID', $this->data['program_id']);
    $this->mrTemplate->AddVar('content', 'SEARCH_SUBPROGRAM_NAMA', $this->data['subprogram_nama']); 
    $this->mrTemplate->AddVar('content', 'SEARCH_SUBPROGRAM_ID', $this->data['subprogram_id']); 
    $this->mrTemplate->AddVar('content', 'SEARCH_KEGIATANREF_NAMA', $this->data['kegiatanref_nama']); 
    $this->mrTemplate->AddVar('content', 'SEARCH_KEGIATANREF_ID', $this->data['kegiatanref_id']);  
    $this->mrTemplate->AddVar('content', 'URL_SEARCH', Dispatcher::Instance()->GetUrl('adjustment_pengeluaran', 'AdjustmentPengeluaran', 'view', 'html') );
    $this->mrTemplate->AddVar('content', 'URL_RESET', Dispatcher::Instance()->GetUrl('adjustment_pengeluaran', 'AdjustmentPengeluaran', 'view', 'html') );
    $this->mrTemplate->AddVar('content', 'URL_POPUP_PROGRAM', Dispatcher::Instance()->GetUrl('adjustment_pengeluaran', 'Program', 'popup', 'html'));
    $this->mrTemplate->AddVar('content', 'URL_POPUP_SUBPROGRAM', Dispatcher::Instance()->GetUrl('adjustment_pengeluaran', 'SubProgram', 'popup', 'html'));
    $this->mrTemplate->AddVar('content', 'URL_POPUP_KEGIATANREF', Dispatcher::Instance()->GetUrl('adjustment_pengeluaran', 'KegiatanRef', 'popup', 'html'));
     
     $nomor_program =''; //inisialisasi nomor program
	 $nomor_kegiatan =''; //inisialisasi nomor kegiatan
	 
	 //if($data['unitkerja']['is_unit_kerja']) {
 	if($data['totalSubUnitKerja'] > 0) {
	     $this->mrTemplate->AddVar('sub_unit', 'IS_UNIT_KERJA', 'YES');		 
	  } else {
	     $this->mrTemplate->AddVar('sub_unit', 'IS_UNIT_KERJA', 'NO');
		 $this->mrTemplate->AddVar('sub_unit', 'DATA_SUBUNIT_NAMA', $this->data['unit_nama']);
	  }
	 
	 $i=0;
	 if (empty($data['data'])) {
         $this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'YES');
		 $this->mrTemplate->AddVar('resume', 'RESUME_EMPTY', 'YES');
      } else {
         $this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'NO');
		 $this->mrTemplate->AddVar('resume', 'RESUME_EMPTY', 'NO');
         $dataGrid = $data['data'];		
		 $i=0;
		 $index=0; // inisialisasi data yang akan dikirim
		 $no=1;
		 
		 $program_nomor=''; //inisialisasi program
		 $kegiatan_nomor=''; //inisialisasi kegiatan
		 $unit_nama=''; //inisialisasi nama unit
		 $index_program=''; //inisialisasi index program yang aktif saat ini
		 $index_kegiatan=''; //inisialisasi index kegiatan yang aktif saat ini
		 
		 $dataList = array();
		 //debug($dataGrid);
		 
		 
         //parsing  tampilan dan membuat menjadi array bertingkat yang ditempatkan pada $dataList
         for ($i=0; $i<sizeof($dataGrid);) {   
		      			   
			   //===========kondisi kalo berupa data sub_kegiatan ===========================
			   if(($program_nomor == $dataGrid[$i]['kodeProg']) && ($kegiatan_nomor == $dataGrid[$i]['kodeKegiatan']) && ($unit_nama == $dataGrid[$i]['unitName'])) { 			     
				 
				 
				 $dataKirim[$index]['class_name']='';			 
				 $dataKirim[$index]['nama_unit'] = '';
				 $dataKirim[$index]['deskripsi'] ='<br /><br />(<i>'.($dataGrid[$i]['deskripsi']=='' ? '' : $dataGrid[$i]['deskripsi']).')</i>';
				 $dataKirim[$index]['kode_kegiatan'] = $dataGrid[$i]['kodeSubKegiatan'];
				 $dataKirim[$index]['nama_kegiatan'] = $dataGrid[$i]['namaSubKegiatan'];
				 $dataKirim[$index]['nominal_usulan'] = $dataGrid[$i]['nominalUsulan'];
				 $dataKirim[$index]['nominal_setujui'] = $dataGrid[$i]['nominalSetuju'];
				 
         $var = $dataGrid[$i]['kegiatandetail_id'];
				 $url_manipulasi = Dispatcher::Instance()->GetUrl('adjustment_pengeluaran', 'inputAdjustmentPengeluaran', 'view', 'html');
         $url_manipulasi.= '&dataId=' . $var;
		 if($data['data_realisasi'] <> 0):
			$url_manipulasi = Dispatcher::Instance()->Encrypt($url_manipulasi);
			$dataKirim[$index]['url_aksi'] = '<a class="xhr dest_subcontent-element" href="' . $url_manipulasi . '" onclick="" title="Sesuaikan Pengeluaran" tabindex="2"><img src="images/button-edit.gif" alt="Sesuaikan Pengeluaran"></a>';
		 else:
			$dataKirim[$index]['url_aksi'] = '-';
		 endif;
				 //$dataKirim[$index]['url_aksi'] = '<a class="xhr dest_subcontent-element" href="' . $url_manipulasi . '" onclick="" title="Sesuaikan Pengeluaran" tabindex="2"><img src="images/button-edit.gif" alt="Sesuaikan Pengeluaran"></a>';
									 
				 
				 //$dataKirim[$index] = $dataGrid[$i];				 
				 $dataList[$index_program]['data'][$index_kegiatan]['data'][$index]=$dataKirim[$index];
				 
			     $i++;
			   
			     //klo informasi program 	====================	
			   } elseif(($program_nomor != $dataGrid[$i]['kodeProg']) && ($unit_nama == $dataGrid[$i]['unitName'])) { 
                 
				 $dataKirim[$index]['class_name']='table-common-even1';	             	 		 
				 $dataKirim[$index]['nama_unit'] = '';
				 $dataKirim[$index]['kode_kegiatan'] = '<b>'.$dataGrid[$i]['kodeProg'].'</b>';
				 $dataKirim[$index]['nama_kegiatan'] = '<b>'.$dataGrid[$i]['namaProgram'].'</b>';
				 $dataKirim[$index]['nominal_usulan'] = '';
				 $dataKirim[$index]['nominal_setujui'] = '';
				 $dataKirim[$index]['unitName'] = $dataGrid[$i]['unitName'];
				 $dataKirim[$index]['deskripsi'] ='';
				 
				 $program_nomor = $dataGrid[$i]['kodeProg'];
				 $index_program = $index;				 
				 $dataList[$index_program]=$dataKirim[$index];                 
			     	 
			   } elseif(
			      (($program_nomor != $dataGrid[$i]['kodeProg']) && ($unit_nama != $dataGrid[$i]['unitName'])) ||
				  (($program_nomor == $dataGrid[$i]['kodeProg']) && ($unit_nama != $dataGrid[$i]['unitName']))			  
			   ) { //klo informasi program 	====================	
                 $dataKirim[$index]['class_name']='table-common-even1';	             	 		 
				 $dataKirim[$index]['nama_unit'] = '<b>'.$dataGrid[$i]['unitName'].'</b>';
				 $dataKirim[$index]['kode_kegiatan'] = '<b>'.$dataGrid[$i]['kodeProg'].'</b>';
				 $dataKirim[$index]['nama_kegiatan'] = '<b>'.$dataGrid[$i]['namaProgram'].'</b>';
				 $dataKirim[$index]['nominal_usulan'] = '';
				 $dataKirim[$index]['nominal_setujui'] = '';
				 $dataKirim[$index]['unitName'] = $dataGrid[$i]['unitName'];
				 $dataKirim[$index]['deskripsi'] ='';
				 $program_nomor = $dataGrid[$i]['kodeProg'];
				 $unit_nama = $dataGrid[$i]['unitName'];
				 $index_program = $index;				 
				 $dataList[$index_program]=$dataKirim[$index];              
			     	 
			   } elseif($kegiatan_nomor != $dataGrid[$i]['kodeKegiatan']) { //klo informasi kegiatan  =============================			 
			     
				 $dataKirim[$index]['class_name']='table-common-even2';	             	 		 
				 $dataKirim[$index]['nama_unit'] = '';
				 $dataKirim[$index]['kode_kegiatan'] = '<i>'.$dataGrid[$i]['kodeKegiatan'].'</i>';
				 $dataKirim[$index]['nama_kegiatan'] = '<i>'.$dataGrid[$i]['namaKegiatan'].'</i>';
				 $dataKirim[$index]['nominal_usulan'] = '';
				 $dataKirim[$index]['nominal_setujui'] = '';
				 $dataKirim[$index]['unitName'] = $dataGrid[$i]['unitName'];
				 $dataKirim[$index]['deskripsi'] ='';
				 $kegiatan_nomor = $dataGrid[$i]['kodeKegiatan'];				 				 
				 $index_kegiatan = $index;
				 $dataList[$index_program]['data'][$index_kegiatan]=$dataKirim[$index];	                 					 
			   } 
			   
			   $index++;
		} //end for dataGrid
		
			
		for($j=0;$j < sizeof($dataKirim);$j++){
			if($dataKirim[$j]['nominal_usulan']):
		   $dataKirim[$j]['nominal_usulan'] =number_format($dataKirim[$j]['nominal_usulan'], 2, ',', '.');
		   endif;
		   if($dataKirim[$j]['nominal_setujui']):
		   $dataKirim[$j]['nominal_setujui'] =number_format($dataKirim[$j]['nominal_setujui'], 2, ',', '.');
		   endif;
		   $this->mrTemplate->AddVars('data_item', $dataKirim[$j], 'DATA_');
           $this->mrTemplate->parseTemplate('data_item', 'a');			   
		    }
     }
   }
  
}
?>
