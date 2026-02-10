<?php
require_once GTFWConfiguration::GetValue('application', 'docroot').
   'module/'.Dispatcher::Instance()->mModule.'/business/FormCOA.class.php';

class ViewInput extends HtmlResponse
{
   function TemplateModule ()
   {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
         'module/'.Dispatcher::Instance()->mModule.'/template');
      $this->SetTemplateFile('view_input.html');
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
      $data = array();
      if (is_array($this->Data)) $data = $this->Data;
      elseif (isset($_GET['id']))
      {
         $detail = $Obj->GetFormDetail((float)$_GET['id']->Raw());
         if ($detail !== false)
         {
            $COA = $Obj->GetFormKomponenCOA((float)$_GET['id']->Raw());
            foreach ($COA as $value) $detail['COA'][$value['formCoaCoaId']] = $value;
            $Signer = $Obj->GetFormKomponenSigner((float)$_GET['id']->Raw());
            
           	if(!empty($Signer)){
            	foreach ($Signer as $value) $detail['Signer'][$value['formsignUserId']] = $value;
            }
			$data = $detail;
         }
         else unset($_GET['id']);
      }
      
      $return['data'] = $data;
      unset ($return['data']['COA']);
      unset ($return['data']['Signer']);
      $return['COA'] = $data['COA'];
      $return['Signer'] = $data['Signer'];
      // ---------
      
      // Generate ComboBox
      $ComboSignerGroup = $Obj->GetComboSignerGroup();
      $return['combo']['signer_group'] = $ComboSignerGroup;
      Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 'ComboSignerGroup',
         array('ComboSignerGroup', $ComboSignerGroup, '', false, 'id="ComboSignerGroup"'), Messenger::CurrentRequest);
      // ---------
      
      // Generate URL
      $return['url']['list'] = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, 'list', 'view', 'html');
      $return['url']['action'] = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, 'input', 'do', 'html');
      if (isset($_GET['id'])) $return['url']['action'] .= '&id='.$_GET['id'];
      $return['url']['popup_coa'] = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, 'popupCOA', 'view', 'html');
      $return['url']['popup_user'] = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, 'popupUser', 'view', 'html');
      // ---------
      
      if (!isset($_GET['id']))
      {
         $this->RedirectTo($return['url']['list']);
         return null;
      }
      else return $return;
   }
   
	function ParseTemplate ($data = NULL)
   {
      if ($data == null) return;
      
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
      
      // render link and other data
      $this->mrTemplate->AddVars('content', $data['url'], 'URL_');
      // ---------
      
      // render form default value
      if (isset($_GET['id'])) $data['data']['mode'] = "Ubah";
      else $data['data']['mode'] = "Tambah";
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
            $value['formCoaCoaId'] = $key;
            $value['class_name'] = ($i%2) ? 'table-common-even' : '';
            if ($value['formCoaDK'] == 'D') list($value['checked_d'],$value['checked_k']) = array('checked','');
            elseif ($value['formCoaDK'] == 'K') list($value['checked_d'],$value['checked_k']) = array('','checked');
            
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
            $value['formsignUserId'] = $key;
            $value['class_name'] = ($i%2) ? 'table-common-even' : '';
            
            // Render Combo Signer Group
            $this->mrTemplate->clearTemplate('combo_signer_group');
            foreach ($data['combo']['signer_group'] as $item)
            {
               if ($item['id'] == $value['formsignSignGroupId'])
                  $item['selected'] = ' selected';
               else $item['selected'] = '';
               
               $this->mrTemplate->AddVars('combo_signer_group', $item, '');
               $this->mrTemplate->parseTemplate('combo_signer_group', 'a');
            }
            // ---------
            
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