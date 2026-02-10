<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/jurnal_penyesuaian/response/ProcJurnalPenyesuaian.proc.class.php';

class DoAddJurnalPenyesuaian extends HtmlResponse {

   function TemplateModule() {
   }
   
   function ProcessRequest() {

      $Obj = new ProcJurnalPenyesuaian();
      
      $urlRedirect = $Obj->Add();     
            
      $this->RedirectTo($urlRedirect) ;      
      
      return NULL;
    }

   function ParseTemplate($data = NULL) {
      
   }
}
?>