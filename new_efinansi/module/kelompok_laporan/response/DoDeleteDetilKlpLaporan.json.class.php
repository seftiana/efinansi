<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/kelompok_laporan/response/ProcessDetilKlpLaporan.proc.class.php';

class DoDeleteDetilKlpLaporan extends JsonResponse
{

   function ProcessRequest() {
      $Obj           = new ProcessDetilKlpLaporan();
      $urlRedirect   = $Obj->Delete();

      return array(
         'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")'
      );
   }
}
?>
