<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/program_kegiatan/response/ProcessKomponenKegiatan.proc.class.php';

class DoDeleteKomponenKegiatan extends JsonResponse {

   function TemplateModule() {
   }
   
   function ProcessRequest() {

      $Obj = new ProcessKomponenKegiatan();   
      
      $urlRedirect = $Obj->Delete();
      
      return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');       
    }

   function ParseTemplate($data = NULL) {
   }
}
?>
