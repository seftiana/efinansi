<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/rencana_pengeluaran/response/ProcessRencanaPengeluaran.proc.class.php';

class DoDeleteKomponen extends HtmlResponse {

   function TemplateModule() {
   }
   
   function ProcessRequest() {

      $Obj = new ProcessRencanaPengeluaran();   
      
      $urlRedirect = $Obj->Delete();     
            
      $this->RedirectTo($urlRedirect) ;      
      
      return NULL;
    }

   function ParseTemplate($data = NULL) {
      
   }
}
?>
