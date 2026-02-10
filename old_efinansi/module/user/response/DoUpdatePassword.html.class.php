<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/user/response/UpdatePassword.class.php';

class DoUpdatePassword extends HtmlResponse {

   function TemplateModule() {
   }
   
   function ProcessRequest() {      
      $userObj = new ProcessUpdatePassword();
      
      $urlRedirect = $userObj->UpdatePassword();
            
      $this->RedirectTo($urlRedirect);
      
      return NULL;
   }

   function ParseTemplate($data = NULL) {
   }
}
?>
