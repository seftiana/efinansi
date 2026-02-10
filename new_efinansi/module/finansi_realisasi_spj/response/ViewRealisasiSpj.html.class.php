<?php
/**
* ================= doc ====================
* FILENAME     : ViewRealisasiSpj.html.class.php
* @package     : ViewRealisasiSpj
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-03-24
* @Modified    : 2015-03-24
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/

class ViewRealisasiSpj extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/finansi_realisasi_spj/template/');
      $this->SetTemplateFile('view_realisasi_spj.html');
   }

   function ProcessRequest(){

      return $return;
   }

   function ParseTemplate($data = null){
      $urlSearch     = Dispatcher::Instance()->GetUrl(
         'finansi_realisasi_spj',
         'realisasiSpj',
         'view',
         'html'
      );

      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);
   }
}
?>