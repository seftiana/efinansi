<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/jurnal_pengeluaran/response/ProcJurnalPengeluaran.proc.class.php';

class DoDeleteJurnalPengeluaran extends HtmlResponse {

   function TemplateModule() {
   }
   
   function ProcessRequest() {

      $Obj = new ProcJurnalPengeluaran();   
      
      $urlRedirect = $Obj->Delete();     
            
      $this->RedirectTo($urlRedirect) ;      
      
      return NULL;
    }

   function ParseTemplate($data = NULL) {
      
   }
}
?>
