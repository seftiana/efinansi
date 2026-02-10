<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
'module/pagu_anggaran_unit_per_mak/response/ProcessPaguAnggaranUnitPerMak.proc.class.php';

class DoAddPaguAnggaranUnitPerMak extends JsonResponse 
{
   function ProcessRequest() 
   {
      $usulan_kegiatanObj     = new ProcessPaguAnggaranUnitPerMak();
      $urlRedirect            = $usulan_kegiatanObj->Add();
      return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');
   }
}
?>