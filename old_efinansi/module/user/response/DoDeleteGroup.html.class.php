<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/user/business/AppGroup.class.php';

class DoDeleteGroup extends HtmlResponse {

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') .
         'module/user/template');
      $this->SetTemplateFile('view_group.html');
   }
   
   function ProcessRequest() {
      $idDec = Dispatcher::Instance()->Decrypt($_REQUEST['id']);
      $groupObj = new AppGroup();
      $deleteData = $groupObj->DoDeleteGroup($idDec);
       if ($deleteData == true) {
         $additionalUrl = "delete|";
      } else {
         $additionalUrl = "delete|fail";
      }
      $this->RedirectTo(Dispatcher::Instance()->GetUrl('user', 'group', 'view', 'html') . 
         '&err=' . Dispatcher::Instance()->Encrypt($additionalUrl));
      return NULL;
   }

   function ParseTemplate($data = NULL) {
      
   }
}
?>
