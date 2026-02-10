<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/komponen/response/Komponen.proc.class.php';

class DoDeleteKomponen extends JsonResponse {

   function TemplateModule() {
   }
   
   function ProcessRequest() {
      $objKomponen = new ProsessKomponen();
      //set post
      
      $urlRedirect = $objKomponen->Delete();
      
      return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');      
   }

   function ParseTemplate($data = NULL) {
      
   }
}
?>