<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/angsuran_detil/response/ProcessRencanaPengeluaran.proc.class.php';

class DoAddRencanaPengeluaran extends JsonResponse
{
   function ProcessRequest()
   {
      $Obj           = new ProcessRencanaPengeluaran();
      $urlRedirect   = $Obj->Add();
      return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');
   }
}
?>
