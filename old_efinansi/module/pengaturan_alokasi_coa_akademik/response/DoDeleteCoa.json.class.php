<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
 'module/pengaturan_alokasi_coa_akademik/response/ProcessAlokasiCoaAkademik.proc.class.php';

class DoDeleteCoa extends JsonResponse
{

   function ProcessRequest() {
      $Obj           = new ProcessAlokasiCoaAkademik();
      $urlRedirect   = $Obj->Delete();

      return array(
         'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")'
      );
   }
}
?>
