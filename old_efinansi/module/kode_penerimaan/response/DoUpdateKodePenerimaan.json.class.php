<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/kode_penerimaan/response/ProcessKodePenerimaan.proc.class.php';

class DoUpdateKodePenerimaan extends JsonResponse {

   function TemplateModule() {
   }
   
   function ProcessRequest() {
      $Obj = new ProcessKodePenerimaan();
      
      $urlRedirect = $Obj->Update();
      
      return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');
   }

   function ParseTemplate($data = NULL) {
   }
}
?>