<?php
require_once GTFWConfiguration::GetValue('application','docroot').
'module/approval_jurnal/business/ProcessApprovalJurnal.php';

class DoAddApprovalJurnal extends HtmlResponse {

   function TemplateModule() {
   }

   function ProcessRequest() {
      $mObj          = new ApprovalJurnal();
      $mProcess      = new ProcessApprovalJurnal();
      $process       = $mProcess->Approve();
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