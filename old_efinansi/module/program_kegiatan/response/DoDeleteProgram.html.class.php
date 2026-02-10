<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/program_kegiatan/response/ProcessProgram.proc.class.php';

class DoDeleteProgram extends HtmlResponse {

   function TemplateModule() {
   }
   
   function ProcessRequest() {
      $Obj = new ProcessProgram();
      
      $urlRedirect = $Obj->Delete();     
            
      $this->RedirectTo($urlRedirect) ;      
      
      return NULL;      
   }

   function ParseTemplate($data = NULL) {
      
   }
}
?>
