<?php
/**
* ================= doc ====================
* FILENAME     : ViewGtfwModule.html.class.php
* @package     : ViewGtfwModule
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2014-11-12
* @Modified    : 2014-11-12
* @Analysts    : Nobody
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/modules/business/Module.class.php';

class ViewGtfwModule extends HtmlResponse
{
   public function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/'.Dispatcher::Instance()->mModule.'/template/');
      $this->SetTemplateFile('view_gtfw_module.html');
   }

   public function ProcessRequest(){
      $mObj          = new Module();
      $arrAppId      = $mObj->GetGtfwApplication();
      $queryString   = '';
      $requestData   = array();
      $arrStatus     = array(array(
         'id' => 'unregister',
         'name' => 'UN-REGISTER'
      ), array(
         'id' => 'conflict',
         'name' => 'CONFLICT'
      ));
      $requestData['app_id']  = GTFWConfiguration::GetValue('application', 'application_id');
      $requestData['status']  = 'all';
      if(isset($mObj->_POST['btnSearch'])){
         $requestData['app_id']     = $mObj->_POST['application'];
         $requestData['status']     = $mObj->_POST['status'];
      }elseif(isset($mObj->_GET['search'])){
         $requestData['app_id']     = Dispatcher::Instance()->Decrypt($mObj->_GET['app_id']);
         $requestData['status']     = Dispatcher::Instance()->Decrypt($mObj->_GET['status']);
      }

      if(method_exists(Dispatcher::Instance(), 'getQueryString')){
         $queryString      = Dispatcher::Instance()->getQueryString($requestData);
      }else{
         $query            = array();
         foreach ($requestData as $key => $value) {
            $query[$key]   = Dispatcher::Instance()->Encrypt($value);
         }
         $queryString      = urldecode(http_build_query($query));
      }

      $dirModule     = $mObj->GetDir($requestData['app_id']);

      # Combobox
      Messenger::Instance()->SendToComponent(
         'combobox',
         'Combobox',
         'view',
         'html',
         'application',
         array(
            'application',
            $arrAppId,
            $requestData['app_id'],
            false,
            'id="cmb_application"'
         ),
         Messenger::CurrentRequest
      );

      # Combobox
      Messenger::Instance()->SendToComponent(
         'combobox',
         'Combobox',
         'view',
         'html',
         'status',
         array(
            'status',
            $arrStatus,
            $requestData['status'],
            true,
            'id="cmb_status"'
         ),
         Messenger::CurrentRequest
      );

      $return['dir_module']      = $dirModule;
      $return['request_data']    = $requestData;
      $return['query_string']    = $queryString;
      return $return;
   }

   public function ParseTemplate($data = null){
      $module        = $data['dir_module'];
      $requestData   = $data['request_data'];
      $queryString   = $data['query_string'];
      $urlSearch     = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         Dispatcher::Instance()->mSubModule,
         Dispatcher::Instance()->mAction,
         Dispatcher::Instance()->mType
      );
      $urlGetConflict   = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'ModuleConflict',
         'view',
         'html'
      ).'&'.$queryString;
      $urlSubModuleConflict   = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'SubModuleConflict',
         'view',
         'html'
      ).'&'.$queryString;
      $urlRegisterModule      = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'RegisterModule',
         'view',
         'html'
      ).'&'.$queryString;

      $urlDetail              = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'DetailModule',
         'view',
         'html'
      ).'&'.$queryString;

      $urlRegisteredSubModule = Dispatcher::Instance()->GetUrl(
         'modules',
         'registeredSubModule',
         'view',
         'html'
      ).'&'.$queryString;

      $this->mrTemplate->AddVar('content', 'URL_GET_CONFLICT', $urlGetConflict);
      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);
      if(empty($module)){
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
         echo 'empty';
      }else{
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         $index      = 0;
         $nomor      = 1;
         foreach ($module as $mod) {
            if(strtolower($requestData['status']) == 'conflict' && $mod['conflict'] == 0){
               continue;
            }

            if(strtolower($requestData['status']) == 'unregister' && $mod['unregister'] == 0){
               continue;
            }

            $mod['number']       = $nomor;
            $mod['class_name']   = ($nomor % 2 <> 0) ? 'table-common-even' : '';
            $mod['url_conflict'] = $urlSubModuleConflict.'&module_name='.$mod['name'];
            $mod['url_register'] = $urlRegisterModule.'&module_name='.$mod['name'];
            $mod['url_detail']   = $urlDetail.'&module_name='.$mod['name'];
            $mod['url_registered_submodule']   = $urlRegisteredSubModule.'&module_name='.$mod['name'];
            $this->mrTemplate->AddVars('data_list', $mod);
            $this->mrTemplate->parseTemplate('data_list', 'a');
            $nomor+=1;
            $index+=1;
         }

         if((int)$index === 0){
            $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
         }
      }
   }
}
?>