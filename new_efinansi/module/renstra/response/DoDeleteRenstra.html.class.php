<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/renstra/business/ProcessRenstra.php';

class DoDeleteRenstra extends HtmlResponse
{

   function ProcessRequest() {
      $Obj           = new ProcessRenstra();
      $process       = $Obj->Delete();

      Messenger::Instance()->Send(
         'renstra',
         'Renstra',
         'view',
         'html',
         array(
            NULL,
            $process['message'],
            $process['style']
         ),
         Messenger::NextRequest
      );

      $this->RedirectTo($process['url']) ;

      return NULL;
   }
}
?>