<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
'module/pagu_anggaran_unit_per_mak/response/ProcessPaguAnggaranUnitPerMak.proc.class.php';

class DoUpdatePaguAnggaranUnitPerMak extends HtmlResponse 
{
   function ProcessRequest() 
   {
      $usulan_kegiatanObj = new ProcessPaguAnggaranUnitPerMak();
      $urlRedirect = $usulan_kegiatanObj->Update();
      $this->RedirectTo($urlRedirect);
      return NULL;
   }
}
?>