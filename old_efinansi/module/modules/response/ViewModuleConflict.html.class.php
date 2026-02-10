<?php
/**
* ================= doc ====================
* FILENAME     : ViewModuleConflict.html.class.php
* @package     : ViewModuleConflict
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2014-11-12
* @Modified    : 2014-11-12
* @Analysts    : No Body
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/modules/business/Module.class.php';

class ViewModuleConflict extends HtmlResponse
{
   public function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/'.Dispatcher::Instance()->mModule.'/template/');
      $this->SetTemplateFile('view_module_conflict.html');
   }

   public function ProcessRequest(){
      $mObj          = new Module();
      $queryString   = $mObj->_getQueryString();
      $appId         = Dispatcher::Instance()->Decrypt($mObj->_GET['app_id']);
      $appId         = ($appId == '') ? GTFWConfiguration::GetValue('application', 'application_id') : $appId;

      if(isset($mObj->_POST['btnExecute'])){
         $mObj->DoCleanConflictedModule($appId);
      }

      $modules       = $mObj->GetConflictedModule($appId);
      sort($modules);
      $return['modules']         = $modules;
      $return['query_string']    = $queryString;
      return $return;
   }

   public function ParseTemplate($data = null){
      $dataModule    = $data['modules'];
      $queryString   = $data['query_string'];
      $urlReturn     = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'GtfwModule',
         'view',
         'html'
      ).'&search=1&'.$queryString;

      $urlAction     = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         Dispatcher::Instance()->mSubModule,
         Dispatcher::Instance()->mAction,
         Dispatcher::Instance()->mType
      ).'&'.$queryString;

      $this->mrTemplate->AddVar('content', 'URL_ACTION', $urlAction);
      $this->mrTemplate->AddVar('content', 'URL_RETURN', $urlReturn);

      if(empty($dataModule)){
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
         $this->mrTemplate->SetAttribute('btn_execute', 'visibility', 'hidden');
      }else{
         $this->mrTemplate->SetAttribute('btn_execute', 'visibility', 'visible');
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         $index      = 0;
         $moduleId   = array();
         foreach ($dataModule as $module) {
            $moduleId[$index]          = $module['module_id'];
            $module['class_name']      = ($index % 2 <> 0) ? 'table-common-even' : '';
            $module['description']     = (is_null($module['description']) OR $module['description'] == '') ? '-' : $module['description'];
            $this->mrTemplate->AddVars('data_list', $module);
            $this->mrTemplate->parseTemplate('data_list', 'a');
            $index++;
         }
      }

   }
}
?>