<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/kelompok_laporan/response/ProcessDetilKlpLaporan.proc.class.php';

class DoAddDetilKlpLaporan extends HtmlResponse {

   function TemplateModule() {
   }
   
   function ProcessRequest() {
      $Obj = new ProcessDetilKlpLaporan();
      $urlRedirect = $Obj->Add();
      $this->RedirectTo($urlRedirect);
      return NULL;
    }
   function ParseTemplate($data = NULL) { 
   }
}
?>