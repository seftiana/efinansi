<?php
#doc
#    classname:    DoSaveData
#    scope:        PUBLIC
#
#/doc
require_once GTFWConfiguration::GetValue('application','docroot').
'module/rencana_kinerja_tahunan_kegiatan/business/ProcessRencanaKinerjaTahunankegiatan.php';

class DoSaveData extends JsonResponse
{
   function ProcessRequest()
   {
      $mProcess   = new ProcessRencanaKinerjaTahunankegiatan();
      $mObj       = new RencanaKinerjaTahunan();
      $process    = $mProcess->Save();
      $url        = $process['url'];
      $message    = $process['message'];
      $style      = $process['style'];
      $data       = $process['data'];

      $module     = $mObj->getModule($url);
      $subModule  = $mObj->getSubModule($url);
      $action     = $mObj->getAction($url);
      $type       = $mObj->getType($url);

      Messenger::Instance()->Send(
         $module,
         $subModule,
         $action,
         $type,
         array(
            $mObj->_POST,
            $message,
            $style
         ),
         Messenger::NextRequest
      );

      return array(
         'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$url.'&ascomponent=1")'
      );
   }
}
?>
