<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/rencana_penerimaan/business/ProcessRencanaPenerimaan.proc.class.php';

class DoAddRencanaPenerimaan extends HtmlResponse {

   function TemplateModule() {
      return NULL;
   }

   function ProcessRequest() {
      $mObj          = new AppRencanaPenerimaan();
      $mProcess      = new ProcessRencanaPenerimaan();
      $process       = $mProcess->Add();
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

      $this->RedirectTo($urlRedirect);
      return NULL;
    }
   function ParseTemplate($data = NULL) {
   }
}
?>
