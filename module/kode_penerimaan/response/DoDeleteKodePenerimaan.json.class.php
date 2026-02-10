<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/kode_penerimaan/response/ProcessKodePenerimaan.proc.class.php';

class DoDeleteKodePenerimaan extends JsonResponse {

   function TemplateModule() {
   }
   
   function ProcessRequest() {
      $Obj = new ProcessKodePenerimaan();
      
      $urlRedirect = $Obj->Delete();
      
      return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');      
   }

   function ParseTemplate($data = NULL) {
      
   }
}
?>