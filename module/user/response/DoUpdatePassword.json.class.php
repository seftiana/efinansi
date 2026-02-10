<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/user/response/UpdatePassword.class.php';

class DoUpdatePassword extends JsonResponse {

   function TemplateModule() {
   }
   
   function ProcessRequest() {      
      $userObj = new ProcessUpdatePassword();
      
      $urlRedirect = $userObj->UpdatePassword();
      
      return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")') ;
               
      return NULL;
   }

   function ParseTemplate($data = NULL) {
   }
}
?>
