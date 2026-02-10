<?php
/*
   @ClassName : DoUploadFile
   @Copyright : PT Gamatechno Indonesia
   @Analyzed By : Nanang Ruswianto <nanang@gamatechno.com>
   @Author By : Dyan Galih <galih@gamatechno.com>
   @Version : 0.1
   @StartDate : 2010-01-06
   @LastUpdate : 2014-06-30
   @modified by : Eko Susilo
   @Description :
*/

require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/rencana_pengeluaran/response/Upload.proc.class.php';

class DoUploadFile extends JsonResponse
{
   function ProcessRequest()
   {

      $objUpload     = new UploadRAB(false);

      $urlRedirect   = $objUpload->Upload();

      $return        = array(
         'exec' => 'popupClose();GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")'
      );
      return $return;
   }
}
?>
