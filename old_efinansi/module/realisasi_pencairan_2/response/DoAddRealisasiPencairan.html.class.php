<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/realisasi_pencairan_2/response/ProcessRealisasiPencairan.proc.class.php';

class DoAddRealisasiPencairan extends HtmlResponse
{
   function ProcessRequest()
   {
      $Obj           = new ProcessRealisasiPencairan();
      $urlRedirect   = $Obj->Add();
      $this->RedirectTo($urlRedirect) ;
      return NULL;
   }
}
?>
