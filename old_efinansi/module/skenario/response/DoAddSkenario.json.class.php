<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/skenario/response/ProcSkenario.proc.class.php';

class DoAddSkenario extends JsonResponse {

   function TemplateModule() {
   }
   
   function ProcessRequest() {

      $Obj = new ProcSkenario();   
      
      $urlRedirect = $Obj->Add();
      
      return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');       
    }

   function ParseTemplate($data = NULL) {
   }
}
?>
