<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/rencana_pengeluaran/response/ProcessRencanaPengeluaran.proc.class.php';

class DoUpdateRencanaPengeluaran extends HtmlResponse {
   function ProcessRequest() {
      $Obj           = new ProcessRencanaPengeluaran();
      $urlRedirect   = $Obj->Update();
      $this->RedirectTo($urlRedirect) ;
      return NULL;
   }
}
?>
