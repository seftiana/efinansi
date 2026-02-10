<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/program_kegiatan/response/ProcessKomponenKegiatan.proc.class.php';

class DoDeleteKomponenKegiatan extends HtmlResponse {

   function TemplateModule() {
   }
   
   function ProcessRequest() {

      $Obj = new ProcessKomponenKegiatan();   
      
      $urlRedirect = $Obj->Delete();     
            
      $this->RedirectTo($urlRedirect) ;      
      
      return NULL;
    }

   function ParseTemplate($data = NULL) {
      
   }
}
?>
