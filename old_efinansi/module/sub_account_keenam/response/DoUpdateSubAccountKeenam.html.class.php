<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/sub_account_keenam/response/ProcessSubAccountKeenam.proc.class.php';

class DoUpdateSubAccountKeenam extends HtmlResponse {

   public function TemplateModule() {}

   public function ProcessRequest() {
      $mObj          = new SubAccountKeenam();
      $mProcess      = new ProcessSubAccountKeenam();
      $process       = $mProcess->Update();
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
   public function ParseTemplate($data = NULL) {}
}
?>