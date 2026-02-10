<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/user/business/AppGroup.class.php';

class DoDeleteGroup extends JsonResponse {

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') .
         'module/user/template');
      $this->SetTemplateFile('view_group.html');
   }
   
   function ProcessRequest() {
      $idDec = Dispatcher::Instance()->Decrypt($_REQUEST['idDelete']);
      $groupObj = new AppGroup();
      $deleteData = $groupObj->DoDeleteGroup($idDec);
       if ($deleteData == true) {
         $additionalUrl = "delete|";
      } else {
         $additionalUrl = "delete|fail";
      }
      return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element",
               " '.Dispatcher::Instance()->GetUrl('user', 'group', 'view', 'html') . 
               '&err=' . Dispatcher::Instance()->Encrypt($additionalUrl).'&ascomponent=1")');
   }

   function ParseTemplate($data = NULL) {
      
   }
}
?>
