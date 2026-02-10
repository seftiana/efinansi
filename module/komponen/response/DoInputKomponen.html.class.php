<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/komponen/response/Komponen.proc.class.php';

class DoInputKomponen extends HtmlResponse {

   function TemplateModule() {
   }
   
   function ProcessRequest() {
      
      $Obj = new ProsessKomponen();
		if ($_GET['dataId']!="")
         $urlRedirect = $Obj->Update();
		else $urlRedirect = $Obj->Add(); 
            
      $this->RedirectTo($urlRedirect) ;      
      
      return NULL;
    }

   function ParseTemplate($data = NULL) {
      
   }
}
?>
