<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/user/response/AddGroup.class.php';

class DoAddGroup extends JsonResponse {

   function TemplateModule() {
   }
   
   function ProcessRequest() {    
         $groupObj = new ProcessAddGroup();
         
         $urlRedirect = $groupObj->AddGroup();
         
         return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")') ;               
   }

   function ParseTemplate($data = NULL) {     
   }
}
?>
