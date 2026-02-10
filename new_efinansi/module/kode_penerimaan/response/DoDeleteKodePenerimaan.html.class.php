<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/kode_penerimaan/response/ProcessKodePenerimaan.proc.class.php';

class DoDeleteKodePenerimaan extends HtmlResponse {

   function TemplateModule() {
   }
   
   function ProcessRequest() {
      $Obj = new ProcessKodePenerimaan();
      
      $urlRedirect = $Obj->Delete();     
            
      $this->RedirectTo($urlRedirect) ;      
      
      return NULL;      
   }

   function ParseTemplate($data = NULL) {
      
   }
}
?>