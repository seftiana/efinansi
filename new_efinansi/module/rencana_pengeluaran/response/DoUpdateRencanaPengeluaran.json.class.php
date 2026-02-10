<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/rencana_pengeluaran/response/ProcessRencanaPengeluaran.proc.class.php';

class DoUpdateRencanaPengeluaran extends JsonResponse {
   function ProcessRequest() {
      $Obj           = new ProcessRencanaPengeluaran();
      $urlRedirect   = $Obj->Update();

      return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');
   }
}
?>