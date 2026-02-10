<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/uraian_belanja/response/UraianBelanja.proc.class.php';

class DoInputUraianBelanja extends JsonResponse {

   function TemplateModule() {
   }
   
   function ProcessRequest() {

      $obj = new ProsessUraianBelanja();
      
      $urlRedirect = $obj->InputUraianBelanja();
      
      return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');       
    }

   function ParseTemplate($data = NULL) {
   }
}
?>
