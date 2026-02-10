<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/user/response/AddGroup.class.php';

class DoAddGroup extends HtmlResponse {

   function TemplateModule() {
   }
   
   function ProcessRequest() {      
      
         $groupObj = new ProcessAddGroup();
         
         $urlRedirect = $groupObj->AddGroup();
         
         $this->RedirectTo($urlRedirect) ;      
   }

   function ParseTemplate($data = NULL) {
      
   }
}
?>
