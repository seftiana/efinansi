<?php

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/jurnal_penyesuaian/response/ProcJurnalPenyesuaian.proc.class.php';

class DoAddJurnalPenyesuaian extends JsonResponse {

   function TemplateModule() {
   }
   
   function ProcessRequest() {

      $Obj = new ProcJurnalPenyesuaian();   
      
      $urlRedirect = $Obj->Add();
      
      return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');       
    }

   function ParseTemplate($data = NULL) {
   }
}
?>