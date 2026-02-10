<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/user/response/AddUser.proc.class.php';

class DoAddUser extends JsonResponse {

   function TemplateModule() {
   }
   
   function ProcessRequest() {

      $userObj = new ProsessAddUser();
      
      $urlRedirect = $userObj->AddUser();
      
      return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');       
    }

   function ParseTemplate($data = NULL) {
   }
}
?>
