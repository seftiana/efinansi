<?php
/**
* ================= doc ====================
* FILENAME     : DoFixConflict.json.class.php
* @package     : DoFixConflict
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2014-11-13
* @Modified    : 2014-11-13
* @Analysts    : Nobody
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/gtfw_modules/business/Module.class.php';

class DoFixConflict extends JsonResponse
{
   public function ProcessRequest()
   {
      $mObj          = new Module();
      $queryString   = $mObj->_getQueryString();
      $urlReturn     = Dispatcher::Instance()->GetUrl(
         'gtfw_modules',
         'subModuleConflict',
         'view',
         'html'
      ).'&'.$queryString;
      $module        = $mObj->_POST['module'];
      if(is_null($module) OR empty($module)){
         $message    = 'Pilih salah satu sub module';
         $style      = 'notebox-warning';
      }else{
         $process       = $mObj->DoFixSubModuleConflict($module);
         if($process === true){
            $message    = 'Clean conflict success';
            $style      = 'notebox-done';
         }else{
            $message    = 'Error cleaning';
            $style      = 'notebox-warning';
         }
      }

      Messenger::Instance()->Send(
         'gtfw_modules',
         'subModuleConflict',
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