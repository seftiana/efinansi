<?php
/**
* @module rencana_pengeluaran
* @author Rendi Kasigi,
* @mail to rkasigi@gmail.com
* @copyright 2008&copy;Gamatechno
*/

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
'module/program_kegiatan/business/KomponenKegiatan.class.php';

class PopupKomponen extends HtmlResponse {
   
   protected $data;
   protected $search;   
   
   protected $KomponenKegiatan;
   
   function PopupKomponen () { //constructor
      $this->KomponenKegiatan = new KomponenKegiatan;
   }

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/program_kegiatan/template');
      $this->SetTemplateFile('popup_komponen.html');
   }
   
    function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }
   
   function ProcessRequest() {  
      	   
	  if(isset($_POST['data'])) {
	     if(is_object($_POST['data'])) 
	        $this->data=$_POST['data']->AsArray();			
		 else 
		    $this->data = $_POST['data'];				   
	  } elseif(isset($_GET['kode'])){
         $this->data['komponen_kode']=$_GET['kode'];
         $this->data['komponen_nama']=$_GET['nama'];
         $this->data['kegref_id'] = $_GET['grp']->mrVariable;	
     } else {
         $this->data['komponen_kode']='';
         $this->data['komponen_nama']='';
         $this->data['kegref_id'] = $_GET['grp']->mrVariable;	 
      }	  
	  
      $itemViewed = 20;
      $currPage = 1;
      $startRec = 0 ;
      if(isset($_GET['page'])) {
         $currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();  
         $startRec =($currPage-1) * $itemViewed;
      }
	  
	  $dataGrid  = $this->KomponenKegiatan->GetKomponen($startRec,$itemViewed,$this->data['komponen_kode'],$this->data['komponen_nama'],$this->data['kegref_id']);
	  $totalData = $this->KomponenKegiatan->GetKomponenCount($this->data['komponen_kode'],$this->data['komponen_nama'],$this->data['kegref_id']);
	  
	 
	  //$dataProgram = $ProgramObj->GetDataProgram($startRec,$itemViewed, $this->data['program'],$is_cari);
	
      $url = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, Dispatcher::Instance()->mSubModule, Dispatcher::Instance()->mAction, Dispatcher::Instance()->mType
            . '&kode=' . Dispatcher::Instance()->Encrypt($this->data['komponen_kode'])
            . '&nama=' . Dispatcher::Instance()->Encrypt($this->data['komponen_nama'])
            . '&cari=' . Dispatcher::Instance()->Encrypt(1));
             
	  
     Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top', array($itemViewed,$totalData, $url, $currPage, 'popup-subcontent'), Messenger::CurrentRequest);

      $return['dataGrid'] = $dataGrid;
      $return['start'] = $startRec+1;
   
	return $return;
   }
   
   function ParseTemplate($data = NULL) {      
	  
      $this->mrTemplate->AddVar('content', 'SEARCH_KOMPONEN_KODE', $this->data['komponen_kode']);	  
      $this->mrTemplate->AddVar('content', 'SEARCH_KOMPONEN_NAMA', $this->data['komponen_nama']);	  	  
	  $this->mrTemplate->AddVar('content', 'SEARCH_KEGREF_ID', $this->data['kegref_id']);	  	  
	  
      $this->mrTemplate->AddVar('content', 'URL_SEARCH', Dispatcher::Instance()->GetUrl('program_kegiatan', 'komponen', 'popup', 'html') );      
	  
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
         $this->mrTemplate->AddVar('data_grid', 'IS_EMPTY', 'YES');
      } else {
	  
	     
         $this->mrTemplate->AddVar('data_grid', 'IS_EMPTY', 'NO');
         $dataGrid = $data['dataGrid'];
		 $i=0;
		 $no=$data['start'];
         for ($i=0; $i<sizeof($dataGrid); $i++) {              			   
			   $dataGrid[$i]['no']=$no;
			   $dataGrid[$i]['unitkerja_nama']=trim($dataGrid[$i]['unitkerja_nama']);
			   $dataGrid[$i]['link_komponen']	= str_replace("'","\'",$dataGrid[$i]['komponen_nama']);
			   $no++;
               if ($no % 2 != 0) {
                  $dataGrid[$i]['class_name'] = 'table-common-even';
               } else {
                  $dataGrid[$i]['class_name'] = '';
               }			  			                  
			   //$idEnc = Dispatcher::Instance()->Encrypt($dataGrid[$i]['program_id']);
               $dataGrid[$i]['harga']=$dataGrid[$i]['harga_satuan'];			   
              	$dataGrid[$i]['komponen_kode']=number_format($dataGrid[$i]['komponen_kode'], 2, '.', '.');
              	$dataGrid[$i]['harga_satuan']=number_format($dataGrid[$i]['harga_satuan'], 2, ',', '.');
              
				
               $this->mrTemplate->AddVars('data_item', $dataGrid[$i], 'DATA_');
               $this->mrTemplate->parseTemplate('data_item', 'a'); 			   
		}
	}
	
   } 
}
?>
