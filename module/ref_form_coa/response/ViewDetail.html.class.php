<?php
require_once GTFWConfiguration::GetValue('application', 'docroot').
   'module/'.Dispatcher::Instance()->mModule.'/business/FormCOA.class.php';

class ViewDetail extends HtmlResponse
{
   function TemplateModule ()
   {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
         'module/'.Dispatcher::Instance()->mModule.'/template');
      $this->SetTemplateFile('view_detail.html');
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
      
      // inisialisasi default value
      $detail = $Obj->GetFormDetail((float)$_GET['id']->Raw());
      if ($detail !== false)
      {
         $COA = $Obj->GetFormKomponenCOA((float)$_GET['id']->Raw());
         $Signer = $Obj->GetFormKomponenSigner((float)$_GET['id']->Raw());
         $data = $detail;
      }
      
      $return['data'] = $data;
      $return['COA'] = $COA;
      $return['Signer'] = $Signer;
      // ---------
      
      // Generate URL
      $return['url']['back'] = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, 'list', 'view', 'html').'&page=';
      // ---------
      
      return $return;
   }
   
	function ParseTemplate($data = NULL)
   {
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
      
      // render data and link
      $this->mrTemplate->AddVars('content', $data['url'], 'URL_');
      $this->mrTemplate->AddVars('content', $data['data'], '');
      // ---------
      
      // render list COA
      if (!empty($data['COA']))
      {
			$this->mrTemplate->SetAttribute('list_coa', 'visibility', 'visible');
			$this->mrTemplate->AddVar('content', 'COA_EMPTY', 'none');
         
         $i = 0;
         foreach ($data['COA'] as $key => $value)
         {
            $value['nomor'] = $i + 1;
            $value['class_name'] = ($i%2) ? 'table-common-even' : '';
            if ($value['formCoaDK'] == 'D') list($value['debet'],$value['kredit']) = array('Debet','');
            elseif ($value['formCoaDK'] == 'K') list($value['debet'],$value['kredit']) = array('','Kredit');
            
            $this->mrTemplate->AddVars('list_coa', $value, '');
            $this->mrTemplate->parseTemplate('list_coa', 'a');
            $i++;
         }
      }
      // ---------
      
      // render list Signer
      /**
      if (!empty($data['Signer']))
      {
			$this->mrTemplate->SetAttribute('list_signer', 'visibility', 'visible');
			$this->mrTemplate->AddVar('content', 'SIGNER_EMPTY', 'none');
         
         $i = 0;
         foreach ($data['Signer'] as $key => $value)
         {
            $value['nomor'] = $i + 1;
            $value['class_name'] = ($i%2) ? 'table-common-even' : '';
            
            $this->mrTemplate->AddVars('list_signer', $value, '');
            $this->mrTemplate->parseTemplate('list_signer', 'a');
            $i++;
         }
      }
      // ---------
      */
   }
}
?>