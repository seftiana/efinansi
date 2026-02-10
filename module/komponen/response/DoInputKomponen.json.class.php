<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/komponen/response/Komponen.proc.class.php';

class DoInputKomponen extends JsonResponse {

   function TemplateModule() {
   }
   
   function ProcessRequest() {

      $Obj = new ProsessKomponen();
		if ($_GET['dataId']!="")
         $urlRedirect = $Obj->Update();
		else $urlRedirect = $Obj->Add();
      
      return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');       
    }

   function ParseTemplate($data = NULL) {
   }
}
?>
