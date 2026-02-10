<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/kelompok_laporan/response/ProcessDetilKlpLaporan.proc.class.php';

class DoAddDetilKlpLaporan extends JsonResponse {

   function TemplateModule() {
   }
   
   function ProcessRequest() {
      $Obj = new ProcessDetilKlpLaporan();
      $urlRedirect = $Obj->Add();
      return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');
    }

   function ParseTemplate($data = NULL) {
   }
}
?>
