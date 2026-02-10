<?php
/**
* ================= doc ====================
* FILENAME     : ViewRegisterModule.html.class.php
* @package     : ViewRegisterModule
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2014-11-12
* @Modified    : 2014-11-12
* @Analysts    : Nobody
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/gtfw_modules/business/Module.class.php';

class ViewRegisterModule extends HtmlResponse
{
   public function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/'.Dispatcher::Instance()->mModule.'/template/');
      $this->SetTemplateFile('view_register_module.html');
   }

   public function ProcessRequest(){
      $mObj          = new Module();
      $messenger     = Messenger::Instance()->Receive(__FILE__);
      $queryString   = $mObj->_getQueryString();
      $appId         = Dispatcher::Instance()->Decrypt($mObj->_GET['app_id']);
      $module_name   = Dispatcher::Instance()->Decrypt($mObj->_GET['module_name']);
      $dataModule    = $mObj->GetModuleDetail($appId, $module_name);
      $dataModule['data_module']['app_id']      = $appId;

      if($messenger){
         $messengerMsg     = $messenger[0][1];
         $messengerStyle   = $messenger[0][2];
      }

      $return['query_string']    = $queryString;
      $return['message']         = $messengerMsg;
      $return['style']           = $messengerStyle;
      $return['module']          = $dataModule['data_module'];
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
      $urlActionRegister   = Dispatcher::Instance()->GetUrl(
         'gtfw_modules',
         'registerModule',
         'do',
         'json'
      ).'&'.$queryString;

      $dataModule          = $data['module'];
      $registerdModule     = $data['registered_module'];
      $conflictModule      = $data['conflict_module'];
      $unregisteredModule  = $data['unregistered_module'];

      $this->mrTemplate->AddVar('content', 'URL_RETURN', $urlReturn);
      $this->mrTemplate->AddVar('content', 'URL_ACTION', $urlActionRegister);
      $this->mrTemplate->AddVars('content', $dataModule);

      if($message){
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $message);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $style);
      }
      if(empty($unregisteredModule)){
         $this->mrTemplate->AddVar('sub_module_unregister', 'DATA_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('sub_module_unregister', 'DATA_EMPTY', 'NO');
         $i       = 0;
         foreach ($unregisteredModule as $uMod) {
            $stringId               = $dataModule['name'].$uMod['name'].$uMod['type'].$uMod['action'];
            $uMod['class_name']     = ($i % 2 <> 0) ? 'table-common-even' : '';
            $uMod['module_name']    = $dataModule['name'];
            $uMod['label']          = preg_replace('/(?<=\w)([A-Z])/', '_\1', $uMod['name']);
            $uMod['id']             = $i+1;
            $uMod['app_id']         = $dataModule['app_id'];
            $this->mrTemplate->AddVars('list_unregister_sub_module', $uMod);
            $this->mrTemplate->parseTemplate('list_unregister_sub_module', 'a');
            $i++;
         }
      }
   }
}
?>