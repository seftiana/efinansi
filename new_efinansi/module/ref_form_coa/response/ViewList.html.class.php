<?php
require_once GTFWConfiguration::GetValue('application', 'docroot').
   'module/'.Dispatcher::Instance()->mModule.'/business/FormCOA.class.php';

class ViewList extends HtmlResponse
{
	function TemplateModule ()
   {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
         'module/'.Dispatcher::Instance()->mModule.'/template');
		$this->SetTemplateFile('view_list.html');
	}
	
	function ProcessRequest ()
   {
		$Obj = new FormCOA;
      
      // inisialisasi messaging
		$msg = Messenger::Instance()->Receive(__FILE__);
      $this->Data = $msg[0][0];
		$this->Pesan = $msg[1][1];
		$this->css = $msg[1][2];
      // ---------
      
      // inisialisasi filter
      $filter = array();
      
      if (isset($_POST['btncari'])) $filter = $_POST->AsArray();
      elseif (isset($_GET['page']) && is_array($this->Data))
      {
         $filter = $this->Data;
         if ((string) $_GET['page'] == '')
            $_GET['page'] = $filter['page'];
         $filter['page'] = $_GET['page']->Integer()->Raw();
      }
      
      Messenger::Instance()->Send(Dispatcher::Instance()->mModule, Dispatcher::Instance()->mSubModule, Dispatcher::Instance()->mAction, Dispatcher::Instance()->mType, array($filter), Messenger::UntilFetched);
      $return['filter'] = $filter;
      // ---------
      
      // inisialisasi data
      $itemViewed = 20;
      if (isset($_GET['page'])) $currPage = (float) $_GET['page']->Raw();
      if (!isset($currPage) OR $currPage < 1) $currPage = 1;
      $startRec = ($currPage - 1) * $itemViewed;
      
      $return['dataGrid'] = $Obj->GetFormListBySearch($filter, $startRec, $itemViewed);
      $totalData = $Obj->GetSearchCount();
      
      $url = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, Dispatcher::Instance()->mSubModule, Dispatcher::Instance()->mAction, Dispatcher::Instance()->mType);
      Messenger::Instance()->SendToComponent('paging', 'paging', 'view', 'html', 'paging_top', array($itemViewed, $totalData, $url, $currPage), Messenger::CurrentRequest);
      $return['start'] = $startRec+1;
      // ---------
      
      // Generate URL
      $return['url']['search'] = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule,Dispatcher::Instance()->mSubModule,Dispatcher::Instance()->mAction,Dispatcher::Instance()->mType);
      $return['url']['add'] = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule,'input','view','html');
      $return['url']['detail'] = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule,'detail','view','html');
      // ---------
      
      return $return;
	}
	
	function ParseTemplate ($data = NULL)
   {
      extract ($data);
      
      // render message box
      if($this->Pesan)
      {
         $msg = '';
         if (count($this->Pesan) > 1) foreach ($this->Pesan as $value)
            $msg .= "\t$value<br/>\n";
         else $msg .= $this->Pesan[0];
         
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $msg);
			$this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
		}
      // ---------
      
      // Render variabel
      $this->mrTemplate->AddVars('content', $url, 'URL_');
      $this->mrTemplate->AddVars('content', $filter, '');
      // ---------
      
      if (empty($dataGrid)) $this->mrTemplate->AddVar('dataGrid','IS_DATA_EMPTY','YES');
      else foreach ($dataGrid as $key=>$value)
      {
         $value['nomor'] = $key + $start;
         $value['is_data_empty'] = 'NO';
         $value['class_name'] = ($key%2) ? 'table-common-even' : '';
         
         $value['url_edit'] = $url['add'] . '&id=' . $value['id'];
         $value['url_detail'] = $url['detail'] . '&id=' . $value['id'];
         
         $this->mrTemplate->AddVars('dataGrid',$value);
         $this->mrTemplate->parseTemplate('dataGrid','a');
      }
	}
}
?>
