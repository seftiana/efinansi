<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/jurnal_penerimaan/response/ProcJurnalPenerimaan.proc.class.php';

class DoAddJurnalPenerimaan extends HtmlResponse {

   function TemplateModule() {
   }
   
   function ProcessRequest() {

      $Obj = new ProcJurnalPenerimaan();   
      
      $urlRedirect = $Obj->Add();     
            
      $this->RedirectTo($urlRedirect) ;      
      
      return NULL;
    }

   function ParseTemplate($data = NULL) {
      
   }
}
?>
