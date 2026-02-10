<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/jurnal_pengeluaran/response/ProcJurnalPengeluaran.proc.class.php';

class DoDeleteJurnalPengeluaran extends JsonResponse {

   function TemplateModule() {
   }
   
   function ProcessRequest() {

      $Obj = new ProcJurnalPengeluaran();   
      
      $urlRedirect = $Obj->Delete();
      
      return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');       
    }

   function ParseTemplate($data = NULL) {
   }
}
?>
