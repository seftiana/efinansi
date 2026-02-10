<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/komponen/response/Komponen.proc.class.php';

class DoDeleteKomponen extends HtmlResponse {

   function TemplateModule() {
   }
   
   function ProcessRequest() {
      $objKomponen = new ProsessKomponen();
      //set post
      
      $urlRedirect = $objKomponen->Delete();     
            
      $this->RedirectTo($urlRedirect) ;      
      
      return NULL;      
   }

   function ParseTemplate($data = NULL) {
      
   }
}
?>
