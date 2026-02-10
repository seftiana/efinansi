<?php
/**
* @module rencana_pengeluaran
* @author Rendi Kasigi,
* @mail to rkasigi@gmail.com
* @copyright 2008&copy;Gamatechno
*/

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/adjustment_pengeluaran/business/AdjustmentPengeluaran.class.php';


class PopupUnitKerja extends HtmlResponse {
   
   protected $data;
   protected $search;   
   
   protected $RekapUnitKerja;
  
   
   
   function PopupUnitKerja () { //constructor
      $this->RekapUnitKerja = new AdjustmentPengeluaran;	  
   }

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/adjustment_pengeluaran/template');
      $this->SetTemplateFile('popup_unit_kerja.html');
   }
   
    function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }
   
   
   
   function ProcessRequest() {   

	  $this->data['nama'] = '';	 	  
	   
	  if(isset($_POST['data'])) {
	     if(is_object($_POST['data'])) 
	        $this->data=$_POST['data']->AsArray();			
		 else 
		    $this->data = $_POST['data'];
			
         $search_nav = '&nama='.$this->data['nama'];		   
	  } elseif(isset($_GET['nama'])) {
	     if(is_object($_GET['nama'])) 
	        $this->data=$_GET['nama']->mrVariable;			
		 else 
		    $this->data = $_GET['nama'];
	     $search_nav = '&nama='.$this->data['nama'];		   
      }	else
         $search_nav='';	  
	 
	   
      $itemViewed = 20;
      $currPage = 1;
      $startRec = 0 ;
      if(isset($_GET['page'])) {
         $currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();  
         $startRec =($currPage-1) * $itemViewed;
      }
	  
	  $dataGrid  = $this->RekapUnitKerja->GetUnitKerja($startRec,$itemViewed,$this->data['nama']);
	  $totalData = $this->RekapUnitKerja->GetCountUnitKerja($this->data['nama']);
	  
	 
	  //$dataProgram = $ProgramObj->GetDataProgram($startRec,$itemViewed, $this->data['program'],$is_cari);
	
      $url = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, 
               Dispatcher::Instance()->mSubModule, 
               Dispatcher::Instance()->mAction, 
               Dispatcher::Instance()->mType).$search_nav;
	  $dest = "popup-subcontent";
      Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top', 
         array($itemViewed,$totalData, $url, $currPage, $dest), 
         Messenger::CurrentRequest);       
            
      $return['dataGrid'] = $dataGrid;
      $return['start'] = $startRec+1;
   
	return $return;
   }
   
   function ParseTemplate($data = NULL) {      
	  
      
      $this->mrTemplate->AddVar('content', 'SEARCH_NAMA', $this->data['nama']);	  
      $this->mrTemplate->AddVar('content', 'URL_SEARCH', Dispatcher::Instance()->GetUrl('adjustment_pengeluaran', 'unitKerja', 'popup', 'html') );      
	  
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
		 
		 //menampilakn semua unit
		 //$x['unitkerja_id']='all';
		 //$x['unitkerja_kode']='Semua Unit Kerja';
		 //$x['unitkerja_nama']='Semua Unit Kerja';
		 
		 //$this->mrTemplate->AddVars('data_item', $x, 'DATA_');
         //$this->mrTemplate->parseTemplate('data_item', 'a');
		 
         $dataGrid = $data['dataGrid'];
		 $i=0;
		 $no=$data['start'];
         for ($i=0; $i<sizeof($dataGrid); $i++) {              			   
			   $dataGrid[$i]['no']=$no;
			   $dataGrid[$i]['unitkerja_nama']=trim
			   ($dataGrid[$i]['unitkerja_nama']);
			   $dataGrid[$i]['link'] = str_replace("'","\'",$dataGrid[$i]['unitkerja_nama']);
			   $no++;
               if ($no % 2 != 0) {
                  $dataGrid[$i]['class_name'] = 'table-common-even';
               } else {
                  $dataGrid[$i]['class_name'] = '';
               }
               if($this->RekapUnitKerja->GetTotalSubUnitKerja($dataGrid[$i]['unitkerja_id']) > 0) {
					$dataGrid[$i]['class_name_parent'] = 'table-common-even1';
				}	  			                  
			   //$idEnc = Dispatcher::Instance()->Encrypt($dataGrid[$i]['program_id']);			   
              			  
               $this->mrTemplate->AddVars('data_item', $dataGrid[$i], 'DATA_');
               $this->mrTemplate->parseTemplate('data_item', 'a'); 			   
		}
	}
	
   } 
}
?>
