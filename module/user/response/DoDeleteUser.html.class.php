<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/user/business/AppUser.class.php';

class DoDeleteUser extends HtmlResponse {

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
      $this->RedirectTo(Dispatcher::Instance()->GetUrl('user', 'user', 'view', 'html') . 
         '&err=' . Dispatcher::Instance()->Encrypt($additionalUrl));
   }

   function ParseTemplate($data = NULL) {
      
   }
}
?>
