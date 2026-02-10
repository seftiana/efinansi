<?php
/**
* @package ViewEarlyWarning
* @copyright Copyright (c) PT Gamatechno Indonesia
* @author Dyan Galih <galih@gamatechno.com>
* @version 0.1
* @startDate 2010-09-27
* @lastUpdate 2010-09-27
* @description View Early Warning
*/

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/early_warning/business/EarlyWarning.class.php';

class ViewEarlyWarning extends HtmlResponse {

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue( 'application', 'docroot').'module/early_warning/template');
      $this->SetTemplateFile('view_early_warning.html');
   }

   function ProcessRequest() {
      $objEarlyWarning = new EarlyWarning();
      $return = $objEarlyWarning->GetEarlyWarning();
      $message = unserialize($_SESSION['messenger_queue']);
#      print_r($message);
#      foreach ($message as $key => $value)
#      {
#         # code...
#         print_r($message[$key]);
#      }
      return $return;
   }

   function ParseTemplate($data = NULL) {
      $urlEarlyWarning = Dispatcher::Instance()->GetUrl('early_warning', 'EarlyWarning', 'view', 'html').'&ascomponent=1';
      $this->mrTemplate->AddVar('content', 'URL_EARLY_WARNING', $urlEarlyWarning);

      if(!empty($data)){
         $this->mrTemplate->SetAttribute('early_warning','visibility', 'visibile');

         for ($i = 0; $i < count($data); $i++)
         {
            $this->mrTemplate->AddVar('early_warning', 'TEXT', $data[$i]['query_desc']);
            $this->mrTemplate->AddVar('early_warning', 'TOTAL', $data[$i]['total']);
            $this->mrTemplate->parseTemplate('early_warning', 'a');
         }
      }else{
         $this->mrTemplate->SetAttribute('empty_warning','visibility', 'visibile');
         $this->mrTemplate->AddVar('empty_warning', 'TEXT', 'No List');
         $this->mrTemplate->parseTemplate('empty_warning', 'a');
      }
   }
}
?>