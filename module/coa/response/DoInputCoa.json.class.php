<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/coa/response/Coa.proc.class.php';

class DoInputCoa extends JsonResponse {

   function TemplateModule() {
   }
   
   function ProcessRequest() {

      $objCoa = new ProsessCoa();
      //set post
      $objCoa->SetPost($_POST);
      
      $urlRedirect = $objCoa->InputCoa(); 
      
      return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');       
    }

   function ParseTemplate($data = NULL) {
   }
}
?>
