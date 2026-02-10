<?php
require_once GTFWConfiguration::GetValue('application', 'docroot').
   'module/'.Dispatcher::Instance()->mModule.'/response/ProcessInput.proc.class.php';

class DoDelete extends HtmlResponse
{
   function TemplateModule ()
   {
   }
   
   function ProcessRequest ()
   {
      $Obj = new ProcessInput;
      $url['redo'] = $url['canceled'] = $url['success'] = $url['failed'] =
         Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, 'list', 'view', 'html').'&page=';
      
      $param = $Obj->Delete();
      if (!isset($param['status']))
         $param['status'] = 'canceled';
      $url = $url[$param['status']];
      
      if (isset($param['message']))
      {
         if ($param['status'] == 'redo') Messenger::Instance()->Send(Dispatcher::Instance()->mModule, 'input', 'view', 'html', $param['message'], Messenger::NextRequest);
         else Messenger::Instance()->Send(Dispatcher::Instance()->mModule, 'list', 'view', 'html', $param['message'], Messenger::NextRequest);
      }
      
      $this->RedirectTo($url);
      return NULL;
   }

   function ParseTemplate ($data = NULL)
   {
   }
}
?>