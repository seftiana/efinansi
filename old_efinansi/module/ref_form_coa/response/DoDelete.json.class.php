<?php
require_once GTFWConfiguration::GetValue('application', 'docroot').
   'module/'.Dispatcher::Instance()->mModule.'/response/ProcessInput.proc.class.php';

class DoDelete extends JsonResponse
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
      
      return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$url.'&ascomponent=1")');  
   }

   function ParseTemplate ($data = NULL)
   {
   }
}
?>