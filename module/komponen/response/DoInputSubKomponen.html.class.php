<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/komponen/response/SubKomponen.proc.class.php';

class DoInputSubKomponen extends HtmlResponse {

   function TemplateModule() {
   }
   
   function ProcessRequest() {
      
      $objSubKomponen = new ProsessSubKomponen();
      //set post
      $objSubKomponen->SetPost($_POST);
      
      $urlRedirect = $objSubKomponen->InputSubKomponen();     
            
      $this->RedirectTo($urlRedirect) ;      
      
      return NULL;
    }

   function ParseTemplate($data = NULL) {
      
   }
}
?>
