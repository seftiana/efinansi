<?php
require_once GTFWConfiguration::GetValue('application','docroot').
'module/periode_tahun/business/ProcessPeriodeTahun.php';

class DoAddPeriodeTahun extends HtmlResponse
{
   function TemplateModule() {
      return null;
   }

   function ProcessRequest() {
      $mObj          = new PeriodeTahun();
      $mProcess      = new ProcessPeriodeTahun();
      $process       = $mProcess->Save();
      $urlRedirect   = $process['url'];
      $module        = $mObj->getModule($urlRedirect);
      $subModule     = $mObj->getSubModule($urlRedirect);
      $action        = $mObj->getAction($urlRedirect);
      $type          = $mObj->getType($urlRedirect);

      Messenger::Instance()->Send(
         $module,
         $subModule,
         $action,
         $type,
         array(
            $process['data'],
            $process['message'],
            $process['style']
         ),
         Messenger::NextRequest
      );
      $this->RedirectTo($urlRedirect) ;

      return NULL;
   }

   function ParseTemplate($data = NULL) {
   }
}
?>