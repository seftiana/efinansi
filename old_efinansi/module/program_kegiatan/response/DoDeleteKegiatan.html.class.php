<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/program_kegiatan/response/ProcessKegiatan.proc.class.php';

class DoDeleteKegiatan extends HtmlResponse {

   function TemplateModule() {
   }
   
   function ProcessRequest() {
      $Obj = new ProcessKegiatan();
      
      $urlRedirect = $Obj->Delete();     
            
      $this->RedirectTo($urlRedirect) ;      
      
      return NULL;      
   }

   function ParseTemplate($data = NULL) {
      
   }
}
?>
