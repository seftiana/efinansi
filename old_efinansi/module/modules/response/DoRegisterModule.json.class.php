<?php
/**
* ================= doc ====================
* FILENAME     : DoRegisterModule.json.class.php
* @package     : DoRegisterModule
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

class DoRegisterModule extends JsonResponse
{
   public function ProcessRequest()
   {
      $mObj          = new Module();
      $queryString   = $mObj->_getQueryString();
      $urlReturn     = Dispatcher::Instance()->GetUrl(
         'modules',
         'registerModule',
         'view',
         'html'
      ).'&'.$queryString;

      $requestData   = $mObj->_POST['register'];
      $moduleId      = $mObj->_POST['id'];
      $dataModule    = array();
      if(is_null($moduleId)){
         $message    = 'Pilih sub module yang akan di register';
         $style      = 'notebox-warning';
      }else{
         for ($i=0; $i < count($moduleId); $i++) {
            $dataModule[$i]      = $requestData[$moduleId[$i]];
         }

         $process    = $mObj->DoRegisterModule($dataModule);

         if($process === true){
            $message = 'Success register module';
            $style   = 'notebox-done';
         }else{
            $message = 'Failed register module';
            $style   = 'notebox-warning';
         }
      }

      Messenger::Instance()->Send(
         'modules',
         'registerModule',
         'view',
         'html',
         array(
            NULL,
            $message,
            $style
         ),
         Messenger::NextRequest
      );

      return array(
         'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlReturn.'&ascomponent=1")'
      );
   }
}
?>