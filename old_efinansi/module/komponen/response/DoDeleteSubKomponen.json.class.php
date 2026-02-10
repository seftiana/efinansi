<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/komponen/response/SubKomponen.proc.class.php';

class DoDeleteSubKomponen extends JsonResponse {

   function TemplateModule() {
   }
   
   function ProcessRequest() {
      $objSubKomponen = new ProsessSubKomponen();
      //set post
      $objSubKomponen->SetPost($_POST);
      
      $urlRedirect = $objSubKomponen->DeleteSubKomponen();
      
      return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');      
   }

   function ParseTemplate($data = NULL) {
      
   }
}
?>