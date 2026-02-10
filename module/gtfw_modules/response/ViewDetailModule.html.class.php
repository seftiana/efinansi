<?php
/**
* ================= doc ====================
* FILENAME     : ViewDetailModule.html.class.php
* @package     : ViewDetailModule
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

class ViewDetailModule extends HtmlResponse
{
   public function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/'.Dispatcher::Instance()->mModule.'/template/');
      $this->SetTemplateFile('view_detail_module.html');
   }

   public function ProcessRequest(){
      $mObj          = new Module();
      $queryString   = $mObj->_getQueryString();
      $appId         = Dispatcher::Instance()->Decrypt($mObj->_GET['app_id']);
      $module_name   = Dispatcher::Instance()->Decrypt($mObj->_GET['module_name']);
      $dataModule    = $mObj->GetModuleDetail($appId, $module_name);

      $return['query_string']    = $queryString;
      $return['module']          = $dataModule['data_module'];
      $return['registered_module']     = $dataModule['data_detail']['sub_module']['register'];
      $return['conflict_module']       = $dataModule['data_detail']['sub_module']['conflict'];
      $return['unregistered_module']   = $dataModule['data_detail']['sub_module']['unregister'];
      return $return;
   }

   public function ParseTemplate($data = null){
      $queryString      = $data['query_string'];
      $urlReturn        = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'gtfwModule',
         'view',
         'html'
      ).'&search=1&'.$queryString;
      $dataModule          = $data['module'];
      $registerdModule     = $data['registered_module'];
      $conflictModule      = $data['conflict_module'];
      $unregisteredModule  = $data['unregistered_module'];

      $this->mrTemplate->AddVar('content', 'URL_RETURN', $urlReturn);
      $this->mrTemplate->AddVars('content', $dataModule);

      if(empty($registerdModule)){
         $this->mrTemplate->AddVar('sub_module_registered', 'DATA_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('sub_module_registered', 'DATA_EMPTY', 'NO');
         $i = 0;
         foreach ($registerdModule as $register) {
            $register['class_name']    = ($i % 2 <> 0) ? 'table-common-even' : '';
            $this->mrTemplate->AddVars('list_registered_module', $register);
            $this->mrTemplate->parseTemplate('list_registered_module', 'a');
            $i++;
         }
      }

      if(empty($unregisteredModule)){
         $this->mrTemplate->AddVar('sub_module_unregister', 'DATA_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('sub_module_unregister', 'DATA_EMPTY', 'NO');
         $i       = 0;
         foreach ($unregisteredModule as $uMod) {
            $uMod['class_name']     = ($i % 2 <> 0) ? 'table-common-even' : '';
            $this->mrTemplate->AddVars('list_unregister_sub_module', $uMod);
            $this->mrTemplate->parseTemplate('list_unregister_sub_module', 'a');
            $i++;
         }
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