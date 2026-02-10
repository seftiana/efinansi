<?php
require_once GTFWConfiguration::GetValue('application','docroot').
'module/posting/business/ProcessPosting.php';

class DoPostingOld extends HtmlResponse {

   function TemplateModule() { return null; }

   function ProcessRequest() {
      $mProcess      = new ProcessPosting();
      $mObj          = new AppPosting();
      $process       = $mProcess->doPostingOld();
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
            NULL,
            $process['message'],
            $process['style']
         ),
         Messenger::NextRequest
      );
      $this->RedirectTo($urlRedirect) ;
      return NULL;
   }

   function ParseTemplate($data = NULL) { }
}
?>
