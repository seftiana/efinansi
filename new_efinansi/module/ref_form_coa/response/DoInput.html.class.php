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
      $url['redo'] = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, 'input', 'view', 'html');
      if (isset($_GET['id'])) $url['redo'] .= '&id=' . $_GET['id'];
      $url['canceled'] = $url['success'] = $url['failed'] = 
         Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, 'list', 'view', 'html');
      foreach (array_keys($url) as $key)
         if ($key == 'redo') continue;
         else if ($key != 'success' OR isset($_GET['id'])) 
            $url[$key] .= '&id=' . $_GET['id'] . '&page=';
      
      if (isset($_GET['id'])) $param = $Obj->Edit();
      else $param = $Obj->Add();
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