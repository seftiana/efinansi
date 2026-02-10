<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/program_kegiatan/response/ProcessSubKegiatan.proc.class.php';

class DoDeleteSubKegiatan extends HtmlResponse {

   function TemplateModule() {
   }
   
   function ProcessRequest() {
      $Obj = new ProcessSubKegiatan();
      
      $urlRedirect = $Obj->Delete();     
            
      $this->RedirectTo($urlRedirect) ;      
      
      return NULL;      
   }

   function ParseTemplate($data = NULL) {
      
   }
}
?>
