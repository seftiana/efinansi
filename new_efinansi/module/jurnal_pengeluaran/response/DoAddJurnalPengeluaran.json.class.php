<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/jurnal_pengeluaran/response/ProcJurnalPengeluaran.proc.class.php';

class DoAddJurnalPengeluaran extends JsonResponse {
   function ProcessRequest() {
      $Obj           = new ProcJurnalPengeluaran();
      $urlRedirect   = $Obj->Add();
      return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');
   }
}
?>
