<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
 'module/pengaturan_alokasi_coa_akademik/response/ProcessAlokasiCoaAkademik.proc.class.php';

class DoAddCoa extends HtmlResponse 
{

   function TemplateModule() {}
   
   function ProcessRequest() 
   {
      $Obj = new ProcessAlokasiCoaAkademik();
      $urlRedirect = $Obj->Add();
      $this->RedirectTo($urlRedirect);
      return NULL;
    }
   
   function ParseTemplate($data = NULL) {}
}

?>