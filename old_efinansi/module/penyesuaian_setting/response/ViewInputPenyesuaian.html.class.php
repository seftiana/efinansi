<?php
/**
* @package ViewInputPenyesuaian
* @copyright Copyright (c) PT Gamatechno Indonesia
* @Analyzed By Dyan Galih <galih@gamatechno.com>
* @author Dyan Galih <galih@gamatechno.com>
* @version 0.1
* @startDate 2011-09-11
* @lastUpdate 2011-09-11
* @description View Input Penyesuaian
*/

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/penyesuaian_setting/business/PenyesuaianSetting.class.php';

class ViewInputPenyesuaian extends HtmlResponse {

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue( 'application', 'docroot').'module/penyesuaian_setting/template');
      $this->SetTemplateFile('view_input_penyesuaian_setting.html');
   }

   function ProcessRequest() {
      $msg = Messenger::Instance()->Receive(__FILE__);
      if(!empty($msg)){
         $return['msg']['css'] = $msg[0][2];
         $return['msg']['message'] = $msg[0][1];
      }

      $obj = new PenyesuaianSetting;

      $id = Dispatcher::Instance()->Decrypt($_GET['id']);
      if(!empty($id)){
         $return['data'] = $obj->GetDataById($id);
         $return['datadb']['COA'] = $obj->GetDataDetilByMstId($id);
      }


      $return['jsonCOA'] = json_encode($return['datadb']['COA']);
      $return['id'] = $id;
      return $return;
   }

   function ParseTemplate($data = NULL) {

      if(!empty($data['data']))
         $this->mrTemplate->AddVars('content', $data['data'], '');

      if(!empty($data['msg'])){
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $data['msg']['message']);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $data['msg']['css']);
      }

      $this->mrTemplate->AddVar('content', 'URL_POPUP_COA', Dispatcher::Instance()->GetUrl('penyesuaian_setting', 'popupCOA', 'view', 'html'));

      if($data['id']=='')
         $this->mrTemplate->AddVar('content', 'URL_ACTION', Dispatcher::Instance()->GetUrl('penyesuaian_setting', 'addPenyesuaian', 'do', 'json'));
      else
         $this->mrTemplate->AddVar('content', 'URL_ACTION', Dispatcher::Instance()->GetUrl('penyesuaian_setting', 'updatePenyesuaian', 'do', 'json').'&id='.Dispatcher::Instance()->Encrypt($data['id']));

      if(!empty($data['jsonCOA']))
         $this->mrTemplate->AddVar('content', 'JSON_COA', 'var jsonCOA = '.$data['jsonCOA']);
   }
}
?>