<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/program_kegiatan/response/ProcessSubKegiatan.proc.class.php';

class DoDeleteSubKegiatan extends JsonResponse {

   function TemplateModule() {
   }
   
   function ProcessRequest() {
      $Obj = new ProcessSubKegiatan();
      
      $urlRedirect = $Obj->Delete();
      
      return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');      
   }

   function ParseTemplate($data = NULL) {
      
   }
}
?>