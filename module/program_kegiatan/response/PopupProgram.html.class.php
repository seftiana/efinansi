<?php
/**
* @module program
* @author Rendi Kasigi,
* @mail to rkasigi@gmail.com
* @copyright 2008&copy;Gamatechno
*/

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/program_kegiatan/business/Program.class.php';

class PopupProgram extends HtmlResponse {
   
   protected $data;
   protected $search;   

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/program_kegiatan/template');
      $this->SetTemplateFile('popup_program.html');
   }
   
    function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }
   
   
   
   function ProcessRequest() {
      $ProgramObj = new Program();
	  
	  $ta_id_selected=''; //inisialisasi tahun anggaran terpilih
	  $is_cari=true;
	  
	  
	   
	  if(isset($_POST['data'])) {
	     if(is_object($_POST['data'])) {
	        $this->data=$_POST['data']->AsArray();
			$flag_renstra = (boolean) $_POST['data']['flag']['renstra']->AsArray();
		 } else {
		    $this->data = $_POST['data'];
			$flag_renstra = (boolean) $_POST['data']['flag']['renstra'];
		 }	 
		 
		 if($this->data['program']['renstra_id']=='all') {
		    $return['msg']['message']='Maaf, Renstra tidak boleh berada pada pilihan semua';
			$return['msg']['action']='err';	
            $is_cari = false;			
		 } 
		 
		 $ta_id_selected=$this->data['program']['ta_id'];
		 if($flag_renstra)
		   $is_cari = false;
         else		   
		   $return['search']= $this->data;		 
		   
	  }
	  
	  //############################ start combo box#################################3
	  	 
      
	  $arr_ta = $ProgramObj->GetDataTahunAnggaran($ta_id_selected);
	  Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 'data[program][ta_id]', 
	     array('data[program][ta_id]', $arr_ta, $ta_id_selected, 'kosong', ' style="width:150px;" '), 
		 Messenger::CurrentRequest);
      //############################ end combo box#################################3
	  
	  if(!isset($this->data['program']['ta_id']) || $this->data['program']['ta_id']=='')
	      $this->data['program']['ta_id']=$ta_id_selected;
	  
	      
	 	
	  $totalData = $ProgramObj->GetCountDataProgram($this->data['program'],$is_cari);
	  
      $itemViewed = 20;
      $currPage = 1;
      $startRec = 0 ;
      if(isset($_GET['page'])) {
         $currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();  
         $startRec =($currPage-1) * $itemViewed;
      }
	  
	  $dataProgram = $ProgramObj->GetDataProgram($startRec,$itemViewed, $this->data['program'],$is_cari);
	
      $url = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, 
               Dispatcher::Instance()->mSubModule, 
               Dispatcher::Instance()->mAction, 
               Dispatcher::Instance()->mType. 
               '&search=' . Dispatcher::Instance()->Encrypt($search['program']['nama'])
               );
		$dest = "popup-subcontent";	   
      Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top', 
         array($itemViewed,$totalData, $url, $currPage, $dest), 
         Messenger::CurrentRequest);       
            
      $return['dataProgram'] = $dataProgram;
      $return['start'] = $startRec+1;
   
	return $return;
   }
   
   function ParseTemplate($data = NULL) {      
	  
      $cari=$data['search']['program']['nama'];
      $this->mrTemplate->AddVar('content', 'SEARCH_PROGRAM_NAMA', $data['search']['program']['nama']);
	  $this->mrTemplate->AddVar('content', 'SEARCH_PROGRAM_KODE', $data['search']['program']['kode']);
      $this->mrTemplate->AddVar('content', 'URL_SEARCH', Dispatcher::Instance()->GetUrl('kegiatan', 'program', 'popup', 'html') );      
	  
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
	  
	  
	  
      if (empty($data['dataProgram'])) {	     
         $this->mrTemplate->AddVar('data_grid', 'IS_EMPTY', 'YES');
      } else {
	  
	     
         $this->mrTemplate->AddVar('data_grid', 'IS_EMPTY', 'NO');
         $dataProgram = $data['dataProgram'];
		 $i=0;
		 $no=$data['start'];
         for ($i=0; $i<sizeof($dataProgram); $i++) {              			   
			   $dataProgram[$i]['no']=$no;
			   $no++;
               if ($no % 2 != 0) {
                  $dataProgram[$i]['class_name'] = 'table-common-even';
               } else {
                  $dataProgram[$i]['class_name'] = '';
               }
			   //dipake componenet confirm delete
			   $urlAccept = 'program_kegiatan|deleteProgram|do|html-cari-'.$cari;
               $urlReturn = 'program_kegiatan|program|view|html-cari-'.$cari;
			   $label = 'program';
               
			   $idEnc = Dispatcher::Instance()->Encrypt($dataProgram[$i]['program_id']);
			   
               $dataProgram[$i]['url_edit'] = Dispatcher::Instance()->GetUrl('program_kegiatan', 'inputProgram', 'view', 'html') . 
                  '&grp=' . $idEnc.'&search='.$cari;
               
				$dataName = $dataProgram[$i]['program_nama'];
				$dataProgram[$i]['url_delete'] = Dispatcher::Instance()->GetUrl('confirm', 'confirmDelete', 'do', 'html').'&urlDelete='. $urlAccept.'&urlReturn='.$urlReturn.'&id='.$idEnc.'&label='.$label.'&dataName='.$dataName;               
				  
               $this->mrTemplate->AddVars('data_item', $dataProgram[$i], 'DATA_');
               $this->mrTemplate->parseTemplate('data_item', 'a'); 			   
		}
	}
	
   } 
}
?>
