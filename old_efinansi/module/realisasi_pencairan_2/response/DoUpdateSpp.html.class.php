<?php
/**
* Module : realisasi_pencairan_2
* FileInclude : ProcessSpp.proc.class.php
* Class : DoUpdateSpp
* Extends : HtmlResponse
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/realisasi_pencairan_2/response/ProcessSpp.proc.class.php';

class DoUpdateSpp extends HtmlResponse
{   
   function ProcessRequest(){
      # code ...
      $obj            = new ProcessSpp();
      $url_redirect   = $obj->Update();

      $this->RedirectTo($url_redirect);

      return null;
   }
}
?>
