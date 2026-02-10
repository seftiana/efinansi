<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/angsuran_detil/response/ProcessRencanaPengeluaran.proc.class.php';

class DoUpdateRencanaPengeluaran extends HtmlResponse {
   function ProcessRequest() {
      $Obj           = new ProcessRencanaPengeluaran();
      $urlRedirect   = $Obj->Update();
      $this->RedirectTo($urlRedirect) ;
      return NULL;
   }
}
?>
