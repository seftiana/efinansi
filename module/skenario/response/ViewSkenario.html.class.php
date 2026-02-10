<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/skenario/response/ProcSkenario.proc.class.php';

class ViewSkenario extends HtmlResponse {
   
   protected $proc;
   protected $data;
   
   function ViewSkenario(){
      $this->proc = new ProcSkenario;
	  $this->data = $this->proc->getPOST();
   }
   
   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/skenario/template');
      $this->SetTemplateFile('view_skenario.html');
   } 
   
   
   function ProcessRequest() { 
		
				
		$itemViewed = 20;
		$currPage = 1;
		$startRec = 0 ;
		if(isset($_GET['page'])) {
			$currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();  
			$startRec =($currPage-1) * $itemViewed;
		}
		$this->data = $this->proc->db->GetData($startRec,$itemViewed);
		$totalData = $this->proc->db->GetCount();
		
	  
	 


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
       

      $return['start'] = $startRec+1;
	  $return['search'] = $search;	 
      
	return $return;
   }
   
   function ParseTemplate($data = NULL) {   	  
	  $this->mrTemplate->AddVar('content', 'URL_ADD', Dispatcher::Instance()->GetUrl($this->proc->moduleName, $this->proc->moduleInput, 'view', 'html') );	  	  
	  
	  
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
	  
	  	  	  
      if (empty($this->data)) {
         $this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'YES');
      } else {
         $this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'NO');
         $dataGrid = $this->data;
		 $nomor=$data['start'];	 
		 
         for ($i=0; $i<sizeof($dataGrid);$i++) {
		    $idEnc =  Dispatcher::Instance()->Encrypt($dataGrid[$i]['id']);
			$dataGrid[$i]['nomor']= $nomor;
			$nomor++;
			
            if($i%2 == 0)		 
			   $dataGrid[$i]['class_name']= 'table-common-even';
			else
			   $dataGrid[$i]['class_name']= '';
			
			$dataGrid[$i]['url_detail']= Dispatcher::Instance()->GetUrl($this->proc->moduleName, 'skenarioDetail', 'view', 'html').'&grp=' .$idEnc; 
			$dataGrid[$i]['url_edit']= Dispatcher::Instance()->GetUrl($this->proc->moduleName, 'inputSkenario', 'view', 'html').'&grp=' .$idEnc; 
		    
			//=============start dipake componenet confirm delete ===============================	     
		     $idDelete = Dispatcher::Instance()->Encrypt($dataGrid[$i]['id']);		     
	         $urlAccept = $this->proc->moduleName.'|'.$this->proc->moduleDelete.'|do|html-cari-';
             $urlReturn = $this->proc->moduleName.'|'.$this->proc->moduleHome.'|view|html-cari-';
	         $label = 'Delete Skenario';
	         $dataName = $dataGrid[$i]['nama'];
             $dataGrid[$i]['url_delete'] = Dispatcher::Instance()->GetUrl('confirm', 'confirmDelete', 'do', 'html').'&urlDelete='. $urlAccept.'&urlReturn='.$urlReturn.'&id='.$idDelete.'&label='.$label.'&dataName='.$dataName;               			   
			 //=============end  dipake componenet confirm delete ===============================
			 
			
			$this->mrTemplate->AddVars('data_item', $dataGrid[$i], 'DATA_');
            $this->mrTemplate->parseTemplate('data_item', 'a');
		}
	 }
		
 }
	

}
?>
