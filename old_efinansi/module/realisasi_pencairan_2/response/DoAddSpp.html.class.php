<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot').
'module/realisasi_pencairan_2/response/ProcessSpp.proc.class.php';

class DoAddSpp extends HtmlResponse
{
   function ProcessRequest(){
      $obj           = new ProcessSpp();
      $url_redirect  = $obj->Add();
      $this->RedirectTo($url_redirect);
      return NULL;
   }
}
?>