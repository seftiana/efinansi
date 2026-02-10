<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/jurnal_penerimaan/response/ProcJurnalPenerimaan.proc.class.php';

class DoUpdateJurnalPenerimaan extends JsonResponse {

   function TemplateModule() {
   }
   
   function ProcessRequest() {

      $Obj = new ProcJurnalPenerimaan();   
      
      $urlRedirect = $Obj->Update();
      
      return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');       
    }

   function ParseTemplate($data = NULL) {
   }
}
?>
