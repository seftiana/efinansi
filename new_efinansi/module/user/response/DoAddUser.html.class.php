<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/user/response/AddUser.proc.class.php';

class DoAddUser extends HtmlResponse {

   function TemplateModule() {
   }
   
   function ProcessRequest() {

      $userObj = new ProsessAddUser();
      
      $urlRedirect = $userObj->AddUser();     
            
      $this->RedirectTo($urlRedirect) ;      
      
      return NULL;
    }

   function ParseTemplate($data = NULL) {
      
   }
}
?>
