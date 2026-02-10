<?php
require_once GTFWConfiguration::GetValue('application', 'docroot').
   'module/'.Dispatcher::Instance()->mModule.'/business/BKK.class.php';

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
      $Obj = new BKK;
      
      // inisialisasi messaging
      $msg = Messenger::Instance()->Receive(__FILE__);
      $this->Data = $msg[0][0];
      $this->Pesan = $msg[0][1];
      $this->css = $msg[0][2];
      // ---------
      
      // inisialisasi default value
      $data = array();
      if (is_array($this->Data)) $data = $this->Data;
      if (isset($_GET['idParent']))
      {
         $tmp = $Obj->GetTransaksiDetail($_GET['idParent']->Raw());
         if (is_array($tmp))
         {
            $tmp += $data;
            $data = $tmp;
         }
         else unset($tmp);
      }
      
      if (!isset($data['transTanggal']))
      {
         $data['transTanggal'] = date('Y-m-d');
         $data['transDueDate'] = date('Y-m-d', strtotime('+30 days'));
         $userInfo = $Obj->GetUserInfo(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
         $data['transPenanggungJawabNama'] = $userInfo['RealName'];
         $data['transUnitkerjaId'] = $userInfo['unitkerjaId'];
      }
      
      $return['data'] = $data;
      unset ($return['data']['PR_List']);
      unset ($return['data']['Signer']);
      $return['PR_List'] = $data['PR_List'];
      $return['COA'] = $data['COA'];
      $return['Signer'] = $data['Signer'];
      // ---------
      
      // Generate ComboBox
      $ComboSignerGroup = $Obj->GetComboSignerGroup();
      $return['combo']['signer_group'] = $ComboSignerGroup;
      Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 'ComboSignerGroup',
         array('ComboSignerGroup', $ComboSignerGroup, '', 'false', 'id="ComboSignerGroup"'), Messenger::CurrentRequest);
      $tahun_awal = date('Y') - 2;
      $tahun_akhir = date('Y') + 2;
      Messenger::Instance()->SendToComponent('tanggal', 'tanggal', 'view', 'html', 'transTanggal', array($data['transTanggal'], $tahun_awal, $tahun_akhir), Messenger::CurrentRequest);
      Messenger::Instance()->SendToComponent('tanggal', 'tanggal', 'view', 'html', 'transDueDate', array($data['transDueDate'], $tahun_awal, $tahun_akhir), Messenger::CurrentRequest);
      // ---------
      
      // Generate URL
      $return['url']['self'] = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, Dispatcher::Instance()->mSubModule, Dispatcher::Instance()->mAction, Dispatcher::Instance()->mType);
      $return['url']['list'] = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, 'list', 'view', 'html');
      $return['url']['action'] = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, 'input', 'do', 'html');
      if (isset($_GET['id'])) $return['url']['action'] .= '&id='.$_GET['id'];
      $return['url']['popup_coa'] = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, 'popupCOA', 'view', 'html');
      $return['url']['popup_user'] = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, 'popupUser', 'view', 'html');
      $return['url']['popup_transaksi'] = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, 'popupTransaksi', 'view', 'html');
      $return['url']['trans_ref'] = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, 'getNextTransRef', 'do', 'json');
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
      
      // render link and other data
      $this->mrTemplate->AddVars('content', $data['url'], 'URL_');
      // ---------
      
      // render form default value
      if (isset($_GET['id'])) $data['data']['mode'] = "Ubah";
      else $data['data']['mode'] = "Tambah";
      $this->mrTemplate->AddVars('content', $data['data'], '');
      // ---------
      
      // render list PR
      if (!empty($data['PR_List']))
      {
         $this->mrTemplate->SetAttribute('list_item_pr', 'visibility', 'visible');
         $this->mrTemplate->AddVar('content', 'PR_LIST_EMPTY', 'none');
         
         $i = 0;
         foreach ($data['PR_List'] as $key => $value)
         {
            $value['prDetId'] = $key;
            $value['class_name'] = ($i%2) ? 'table-common-even' : '';
            $value['prDetExtCostLabel'] = number_format($value['prDetExtCost'], 2, ',', '.');
            
            $this->mrTemplate->AddVars('list_item_pr', $value, '');
            $this->mrTemplate->parseTemplate('list_item_pr', 'a');
            $i++;
         }
      }
      // ---------
      
      // render list COA
      if (!empty($data['COA']))
      {
         $this->mrTemplate->SetAttribute('list_coa', 'visibility', 'visible');
         $this->mrTemplate->AddVar('content', 'COA_EMPTY', 'none');
         
         $i = 0;
         $total_debet = $total_kredit = 0;
         foreach ($data['COA'] as $key => $value)
         {
            $value['coaId'] = $key;
            $value['class_name'] = ($i%2) ? 'table-common-even' : '';
            if ($value['typeRekening'] == 'D') $total_debet += $value['nominal'];
            elseif ($value['typeRekening'] == 'K') $total_kredit += $value['nominal'];
            if (empty($value['subAccount'])) $value['subAccount'] = '0-00-000-0-0000';
            
            $this->mrTemplate->AddVars('list_coa', $value, '');
            $this->mrTemplate->parseTemplate('list_coa', 'a');
            $i++;
         }
         
         $this->mrTemplate->AddVar('content', 'TOTAL_DEBET', $total_debet);
         $this->mrTemplate->AddVar('content', 'TOTAL_KREDIT', $total_kredit);
      }
      else $this->mrTemplate->AddVar('content', 'SHOW_TOTAL', 'none');
      // ---------
      
      // render list Signer
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
   }
}
?>