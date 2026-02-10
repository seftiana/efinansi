<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/realisasi_pencairan/response/ProcessRealisasiPencairan.proc.class.php';

class DoDeleteRealisasiPencairan extends HtmlResponse {

   function TemplateModule() {
   }
   
   function ProcessRequest() {

      $Obj = new ProcessRealisasiPencairan();   
      
      $urlRedirect = $Obj->Delete();     
            
      $this->RedirectTo($urlRedirect) ;      
      
      return NULL;
    }

   function ParseTemplate($data = NULL) {
      
   }
}
?>
