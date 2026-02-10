<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
 'module/pengaturan_alokasi_coa_akademik/response/ProcessAlokasiCoaAkademik.proc.class.php';

class DoAddCoa extends JsonResponse {

   function TemplateModule() {
   }
   
   function ProcessRequest() {
      $Obj = new ProcessAlokasiCoaAkademik();
      $urlRedirect = $Obj->Add();
      return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');
    }

   function ParseTemplate($data = NULL) {
   }
}
?>
