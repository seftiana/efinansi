<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/satuan_komponen/response/SatuanKomponen.proc.class.php';

class DoInputSatuanKomponen extends JsonResponse {

   function TemplateModule() {
   }
   
   function ProcessRequest() {

      $objSatuanKomponen = new ProsessSatuanKomponen();
      //set post
      $objSatuanKomponen->SetPost($_POST);
      
      $urlRedirect = $objSatuanKomponen->InputSatuanKomponen(); 
      
      return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');       
    }

   function ParseTemplate($data = NULL) {
   }
}
?>
