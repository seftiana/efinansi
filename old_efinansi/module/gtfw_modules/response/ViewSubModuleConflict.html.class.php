<?php
/**
* ================= doc ====================
* FILENAME     : ViewSubModuleConflict.html.class.php
* @package     : ViewSubModuleConflict
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2014-11-12
* @Modified    : 2014-11-12
* @Analysts    : No Body
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/gtfw_modules/business/Module.class.php';

class ViewSubModuleConflict extends HtmlResponse
{
   public function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/'.Dispatcher::Instance()->mModule.'/template/');
      $this->SetTemplateFile('view_sub_module_conflict.html');
   }

   public function ProcessRequest(){
      $messenger     = Messenger::Instance()->Receive(__FILE__);
      $mObj          = new Module();
      $queryString   = $mObj->_getQueryString();
      $appId         = Dispatcher::Instance()->Decrypt($mObj->_GET['app_id']);
      $module_name   = Dispatcher::Instance()->Decrypt($mObj->_GET['module_name']);
      $dataModule    = $mObj->GetModuleDetail($appId, $module_name);

      if($messenger){
         $messengerMsg     = $messenger[0][1];
         $messengerStyle   = $messenger[0][2];
      }
      $return['query_string']    = $queryString;
      $return['module']          = $dataModule['data_module'];
      $return['message']         = $messengerMsg;
      $return['style']           = $messengerStyle;
      $return['registered_module']     = $dataModule['data_detail']['sub_module']['register'];
      $return['conflict_module']       = $dataModule['data_detail']['sub_module']['conflict'];
      $return['unregistered_module']   = $dataModule['data_detail']['sub_module']['unregister'];
      return $return;
   }

   public function ParseTemplate($data = null){
      $message          = $data['message'];
      $style            = $data['style'];
      $queryString      = $data['query_string'];
      $urlReturn        = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'gtfwModule',
         'view',
         'html'
      ).'&search=1&'.$queryString;
      $urlAction        = Dispatcher::Instance()->GetUrl(
         'gtfw_modules',
         'fixConflict',
         'do',
         'json'
      ).'&'.$queryString;

      $dataModule          = $data['module'];
      $registerdModule     = $data['registered_module'];
      $conflictModule      = $data['conflict_module'];
      $unregisteredModule  = $data['unregistered_module'];

      $this->mrTemplate->AddVar('content', 'URL_RETURN', $urlReturn);
      $this->mrTemplate->AddVar('content', 'URL_ACTION', $urlAction);
      $this->mrTemplate->AddVars('content', $dataModule);

      if($message){
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $message);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $style);
      }

      if(empty($conflictModule)){
         $this->mrTemplate->AddVar('sub_module_conflict', 'DATA_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('sub_module_conflict', 'DATA_EMPTY', 'NO');
         foreach ($conflictModule as $cMod) {
            $this->mrTemplate->AddVars('list_conflict_sub_module', $cMod);
            $this->mrTemplate->parseTemplate('list_conflict_sub_module', 'a');
         }
      }
   }
}
?>