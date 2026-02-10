<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/satuan_komponen/response/SatuanKomponen.proc.class.php';

class DoDeleteSatuanKomponen extends HtmlResponse {

   function TemplateModule() {
   }
   
   function ProcessRequest() {
      $objSatuanKomponen = new ProsessSatuanKomponen();
      //set post
      $objSatuanKomponen->SetPost($_POST);
      
      $urlRedirect = $objSatuanKomponen->DeleteSatuanKomponen();     
            
      $this->RedirectTo($urlRedirect) ;      
      
      return NULL;      
   }

   function ParseTemplate($data = NULL) {
      
   }
}
?>
