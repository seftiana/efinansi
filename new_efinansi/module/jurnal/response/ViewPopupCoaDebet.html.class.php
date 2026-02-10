<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot').
   'module/jurnal_penerimaan/business/AppPopupCoaDebet.class.php';

class ViewPopupCoaDebet extends HtmlResponse
{
   function TemplateBase()
   {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }
   
   function TemplateModule()
   {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
         'module/jurnal_penerimaan/template');
      $this->SetTemplateFile('popup_coa_debet.html');
   }
   
   function ProcessRequest()
   {
      $Obj = new Popup;
      $_POST = $_POST->AsArray();
      
      #Dispatcher::Instance()->Decrypt($_GET['cari']->Raw())
      if(!empty($_POST['key']))
         $key = $_POST['key'];
      elseif(!empty($_GET['key']))
         $key = Dispatcher::Instance()->Decrypt($_GET['key']->Raw());
      else
         $key = '';
      
      // inisialisasi komponen paging
		$itemViewed = 10;
		$currPage = 1;
		$startRec = 0 ;
		if(isset($_GET['page'])) {
			$currPage = $_GET['page']->Integer()->Raw();
			if ($currPage < 1) $currPage = 1;
			$startRec = ($currPage-1) * $itemViewed;
		}
      $totalData = $Obj->GetCount($key);
      $data = $Obj->GetPupUpCoaDebet($key, $startRec, $itemViewed);
      $url = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, Dispatcher::Instance()->mSubModule, Dispatcher::Instance()->mAction, Dispatcher::Instance()->mType . '&key=' . Dispatcher::Instance()->Encrypt($key));
	  $dest = "popup-subcontent";
		Messenger::Instance()->SendToComponent('paging', 'paging', 'view', 'html', 'paging_top', array($itemViewed,$totalData, $url, $currPage, $dest), Messenger::CurrentRequest);
		$return['start'] = $startRec+1;
      // ---------
		
      $return['coa'] = $data;   
		// ---------
      
      // Inisialisasi link URL
      $return['url']['search'] = Dispatcher::Instance()->GetUrl('jurnal_penerimaan','popupCoaDebet','view','html');
      // ---------
      
      return $return;
   }
   
	function ParseTemplate($data = NULL)
   {
      #print_r($data);
      $this->mrTemplate->AddVar('content', 'URL_SEARCH', Dispatcher::Instance()->GetUrl('jurnal_penerimaan', 'popupCoaDebet', 'view', 'html'));
      
		// Render data list
		if (count($data['coa']) === 0) {
			return $this->mrTemplate->AddVar('data', 'DATA_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('data', 'DATA_EMPTY', 'NO');
         
         $no = $data['start'];
         for($i=0; $i<sizeof($data['coa']); $i++) {
            $data['coa'][$i]['no']=$no;            
            $no++;
            if($no % 2 != 0)
               $data['coa'][$i]['class_name'] = 'table-common-even';
            else
               $data['coa'][$i]['class_name'] = '';
            $this->mrTemplate->AddVars('data_item', $data['coa'][$i], 'DATA_');
            $this->mrTemplate->parseTemplate('data_item', 'a');         
         }
		}
   }
}
?>