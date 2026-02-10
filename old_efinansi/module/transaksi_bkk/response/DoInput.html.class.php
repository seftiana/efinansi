<?php
require_once GTFWConfiguration::GetValue('application', 'docroot').
   'module/'.Dispatcher::Instance()->mModule.'/response/ProcessInput.proc.class.php';

class DoInput extends HtmlResponse
{
   function TemplateModule ()
   {
   }
   
   function ProcessRequest ()
   {
      $Obj = new ProcessInput;
      $url['redo'] = $url['canceled'] = $url['success'] = $url['failed'] =
         Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, 'input', 'view', 'html');
      
      if (isset($_GET['id'])) $param = $Obj->Edit();
      else $param = $Obj->Add();
      if (!isset($param['status']))
         $param['status'] = 'canceled';
      $url = $url[$param['status']];
      
      if (isset($param['message'])) Messenger::Instance()->Send(Dispatcher::Instance()->mModule, 'input', 'view', 'html', $param['message'], Messenger::NextRequest);
      
      $this->RedirectTo($url);
      return NULL;
   }

   function ParseTemplate ($data = NULL)
   {
   }
}
?>