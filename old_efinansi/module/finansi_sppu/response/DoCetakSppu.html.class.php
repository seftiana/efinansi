<?php

require_once GTFWConfiguration::GetValue('application','docroot').
'module/finansi_sppu/business/Sppu.class.php';
require_once GTFWConfiguration::GetValue('application','docroot').
'module/finansi_sppu/response/ProcessSppu.proc.class.php';

class DoCetakSppu extends HtmlResponse 
{

   public function TemplateModule() {}
   
   public function ProcessRequest() 
   {
      $Obj = new ProcessSppu();
      $urlRedirect = $Obj->Add();
      $this->RedirectTo($urlRedirect);
      return NULL;
   }

   public function ParseTemplate($data = NULL) {}
}

?>