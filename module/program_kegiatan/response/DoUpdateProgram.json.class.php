<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/program_kegiatan/response/ProcessProgram.proc.class.php';

class DoUpdateProgram extends JsonResponse {

   function TemplateModule() {
   }
   
   function ProcessRequest() {
      $Obj = new ProcessProgram();
      
      $urlRedirect = $Obj->Update();
      
      return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');
   }

   function ParseTemplate($data = NULL) {
   }
}
?>
