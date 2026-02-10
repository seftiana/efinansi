<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/user/business/AppUser.class.php';

class DoDeleteUser extends JsonResponse {

   function TemplateModule() {
   }
   
   function ProcessRequest() {
      
      $idDec = Dispatcher::Instance()->Decrypt($_REQUEST['idDelete']);
      $userObj = new AppUser();
      $deleteData = $userObj->DoDeleteUser($idDec);
       if ($deleteData == true) {
         $additionalUrl = "delete|";
      } else {
         $additionalUrl = "delete|fail";
      }
      return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element",
               " '.Dispatcher::Instance()->GetUrl('user', 'user', 'view', 'html') . 
               '&err=' . Dispatcher::Instance()->Encrypt($additionalUrl).'&ascomponent=1")') ;
   }

   function ParseTemplate($data = NULL) {
      
   }
}
?>
