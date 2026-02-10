<?php
/**
* @module angsuran_detil
* @author Rendi Kasigi,
* @mail to rkasigi@gmail.com
* @copyright 2008&copy;Gamatechno
*/

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/angsuran_detil/response/ProcessRencanaPengeluaran.proc.class.php';

class PopupCoa extends HtmlResponse {
   
   protected $data;
   protected $search;   
   
   protected $proc;
   
   function PopupCoa () { //constructor
      $this->proc = new ProcessRencanaPengeluaran;
   }

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/angsuran_detil/template');
      $this->SetTemplateFile('popup_coa.html');
   }
   
    function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }
   
   
   
   function ProcessRequest() {  
      $userid=Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
	  $unitkerja= $this->proc->UserUnitKerja->GetUnitKerjaUser($userid); 
	  
	  $this->data['nama'] = '';  
	  
	   
	  if(isset($_POST['data'])) {
	     if(is_object($_POST['data'])) 
	        $this->data=$_POST['data']->AsArray();			
		 else 
		    $this->data = $_POST['data'];			 
		   
	  }     
	 
	   
      $itemViewed = 20;
      $currPage = 1;
      $startRec = 0 ;
      if(isset($_GET['page'])) {
         $currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();  
         $startRec =($currPage-1) * $itemViewed;
      }
	  
	  $dataGrid  = $this->proc->RencanaPengeluaran->GetCoa($startRec,$itemViewed,$unitkerja['unit_kerja_id'],'%'.$this->data['nama'].'%');
	  $totalData = $this->proc->RencanaPengeluaran->GetCountCoa($unitkerja['unit_kerja_id'],'%'.$this->data['nama'].'%');
	  
	 
	 
	  //$dataProgram = $ProgramObj->GetDataProgram($startRec,$itemViewed, $this->data['program'],$is_cari);
	
      $url = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, 
               Dispatcher::Instance()->mSubModule, 
               Dispatcher::Instance()->mAction, 
               Dispatcher::Instance()->mType);
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
      $this->mrTemplate->AddVar('content', 'URL_SEARCH', Dispatcher::Instance()->GetUrl('angsuran_detil', 'coa', 'popup', 'html') );      
	  
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
			   $no++;
               if ($no % 2 != 0) {
                  $dataGrid[$i]['class_name'] = 'table-common-even';
               } else {
                  $dataGrid[$i]['class_name'] = '';
               }			  			                  
			   //$idEnc = Dispatcher::Instance()->Encrypt($dataGrid[$i]['program_id']);			   
              			  
               $this->mrTemplate->AddVars('data_item', $dataGrid[$i], 'DATA_');
               $this->mrTemplate->parseTemplate('data_item', 'a'); 			   
		}
	}
	
   } 
}
?>
