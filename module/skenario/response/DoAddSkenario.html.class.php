<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/skenario/response/ProcSkenario.proc.class.php';

class DoAddSkenario extends HtmlResponse {

   function TemplateModule() {
   }
   
   function ProcessRequest() {

      $Obj = new ProcSkenario();   
      
      $urlRedirect = $Obj->Add();     
            
      $this->RedirectTo($urlRedirect) ;      
      
      return NULL;
    }

   function ParseTemplate($data = NULL) {
      
   }
}
?>
