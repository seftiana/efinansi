<?php
/*
   @ClassName : DoUploadFile
   @Copyright : PT Gamatechno Indonesia
   @Analyzed By : Nanang Ruswianto <nanang@gamatechno.com>
   @Author By : Dyan Galih <galih@gamatechno.com>
   @Version : 0.1
   @StartDate : 2010-01-06
   @LastUpdate : 2010-01-06
   @Description :
*/

require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/rencana_pengeluaran/response/Upload.proc.class.php';

class DoUploadFile extends HtmlResponse
{
   function ProcessRequest()
   {
      $objUpload     = new UploadRAB();
      $urlRedirect   = $objUpload->upload();
      $this->RedirectTo($urlRedirect) ;

      return NULL;
   }
}
?>
