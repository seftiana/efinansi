<?php
require_once GTFWConfiguration::GetValue('application', 'docroot').
   'module/'.Dispatcher::Instance()->mModule.'/business/FormCOA.class.php';

class ViewPopupUser extends HtmlResponse
{

   function TemplateModule ()
   {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
         'module/'.Dispatcher::Instance()->mModule.'/template');
      $this->SetTemplateFile('view_popup_user.html');
   }
   
   function ProcessRequest ()
   {
      $Obj = new FormCOA;
      
      // inisialisasi messaging
		$msg = Messenger::Instance()->Receive(__FILE__);
      $this->Data = $msg[0][0];
		$this->Pesan = $msg[0][1];
		$this->css = $msg[0][2];
      // ---------
      
      // inisialisasi filter
      $filter = array();
      
      if (isset($_POST['btncari'])) $filter = $_POST->AsArray();
      elseif (isset($_GET['page']) && is_array($this->Data)) $filter = $this->Data;
      
      if (!empty($filter)) Messenger::Instance()->Send(Dispatcher::Instance()->mModule, Dispatcher::Instance()->mSubModule, Dispatcher::Instance()->mAction, Dispatcher::Instance()->mType, array($filter), Messenger::NextRequest);
      $return['filter'] = $filter;
      // ---------
      
      // Inisialisasi komponen paging
      $itemViewed = 10;
      if (isset($_GET['page'])) $currPage = $_GET['page']->Integer()->Raw();
      if (!isset($currPage) OR $currPage < 1) $currPage = 1;
      $startRec = ($currPage - 1) * $itemViewed;
      
      $return['dataGrid'] = $Obj->GetUserListBySearch($filter, $startRec, $itemViewed);
      $totalData = $Obj->GetSearchCount();
      
      $url = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, Dispatcher::Instance()->mSubModule, Dispatcher::Instance()->mAction, Dispatcher::Instance()->mType);
      Messenger::Instance()->SendToComponent('paging', 'paging', 'view', 'html', 'paging_top', array($itemViewed, $totalData, $url, $currPage, 'popup-subcontent'), Messenger::CurrentRequest);
      $return['start'] = $startRec+1;
      // ---------
      
      // Generate URL
      $return['url']['search'] = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, Dispatcher::Instance()->mSubModule, Dispatcher::Instance()->mAction, Dispatcher::Instance()->mType);
      // ---------
      
      return $return;
   }

   function ParseTemplate ($data = NULL)
   {  
      // Render URL, Filter dan variabel
	   $this->mrTemplate->AddVars('content', $data['url'], 'URL_');
	   $this->mrTemplate->AddVars('content', $data['filter']);
	   $this->mrTemplate->AddVar('content', 'FORM_NAME', $data['form']);
      if (empty($data['dataGrid'])) return $this->mrTemplate->AddVar('data_item', 'DATA_EMPTY', 'YES');
      else $this->mrTemplate->AddVar('data_item', 'DATA_EMPTY', 'NO');
      // ---------
      
      // Render DataGrid
      $i = $data['start'];
      foreach ($data['dataGrid'] as $item)
      {
         $item['number'] = $i++;
         $item['class_name'] = ($item % 2) ? '' : 'table-common-even';
         
         $this->mrTemplate->AddVars('item', $item);
         $this->mrTemplate->parseTemplate('item', 'a');
      }
      // ---------
   }
}
?>