<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/pph_ref_gpp/response/PphGpp.proc.class.php';

class DoDeletePphGpp extends JsonResponse {

   function TemplateModule() {
   }
   
   function ProcessRequest() {
      $objPphGpp = new ProsessPphGpp();
      //set post
      $objPphGpp->SetPost($_POST);
      $urlRedirect = $objPphGpp->DeletePphGpp();
      
      return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');      
   }

   function ParseTemplate($data = NULL) {
      
   }
}
?>