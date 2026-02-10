<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/komponen/response/SubKomponen.proc.class.php';

class DoDeleteSubKomponen extends HtmlResponse {

   function TemplateModule() {
   }
   
   function ProcessRequest() {
      $objSubKomponen = new ProsessSubKomponen();
      //set post
      $objSubKomponen->SetPost($_POST);
      
      $urlRedirect = $objSubKomponen->DeleteSubKomponen();     
            
      $this->RedirectTo($urlRedirect) ;      
      
      return NULL;      
   }

   function ParseTemplate($data = NULL) {
      
   }
}
?>
