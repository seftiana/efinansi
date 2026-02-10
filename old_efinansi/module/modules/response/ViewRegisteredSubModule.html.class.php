<?php
/**
* ================= doc ====================
* FILENAME     : ViewRegisteredSubModule.class.php
* @package     : ViewRegisteredSubModule
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2014-11-13
* @Modified    : 2014-11-13
* @Analysts    : Nobody
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/modules/business/Module.class.php';

class ViewRegisteredSubModule extends HtmlResponse
{
   public function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/modules/template/');
      $this->SetTemplateFile('view_registered_sub_module.html');
   }

   public function ProcessRequest(){
      $mObj          = new Module();
      $queryString   = $mObj->_getQueryString();
      $appId         = Dispatcher::Instance()->Decrypt($mObj->_GET['app_id']);
      $module_name   = Dispatcher::Instance()->Decrypt($mObj->_GET['module_name']);
      $dataModule    = $mObj->GetModuleDetail($appId, $module_name);
      $query         = $mObj->GetQueryModule($module_name);

      $return['query_string']    = $queryString;
      $return['module']          = $dataModule['data_module'];
      $return['registered_module']     = $dataModule['data_detail']['sub_module']['register'];
      $return['conflict_module']       = $dataModule['data_detail']['sub_module']['conflict'];
      $return['unregistered_module']   = $dataModule['data_detail']['sub_module']['unregister'];
      $return['query']                 = implode('<br /><br />', $query);
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
      $code       = '<!-- patTemplate:phpHighlight --> '.$data['query'].'<!-- /patTemplate:phpHighlight -->';
      $this->mrTemplate->AddVar('code', 'CODE', $data['query']);
   }
}
?>