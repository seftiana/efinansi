<?php
/**
* ================= doc ====================
* FILENAME     : ViewAddDipa.html.class.php
* @package     : ViewAddDipa
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2014-12-08
* @Modified    : 2014-12-08
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/finansi_dipa/business/FinansiDipa.class.php';

class ViewAddDipa extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/finansi_dipa/template/');
      $this->SetTemplateFile('view_add_dipa.html');
   }

   function ProcessRequest(){
      $message = $style = $messengerData = null;
      $mObj             = new FinansiDipa();
      $messenger        = Messenger::Instance()->Receive(__FILE__);
      $query_string     = $mObj->_getQueryString();
      $tahun_awal       = date('Y',time())-5;
      $tahun_akhir      = date('Y', time())+5;
      $get_date         = getdate();
      $cDay             = (int)$get_date['mday'];
      $cMon             = (int)$get_date['mon'];
      $cYear            = (int)$get_date['year'];
      $request_data     = array();
      if($messenger){
         $message       = $messenger[0][1];
         $style         = $messenger[0][2];
         $messengerData = $messenger[0][0];
         $cDay          = (int)$messengerData['tanggal_day'];
         $cMon          = (int)$messengerData['tanggal_mon'];
         $cYear         = (int)$messengerData['tanggal_year'];
         $request_data['kode']      = $messengerData['kode'];
         $request_data['id']        = $messengerData['data_id'];
         $request_data['nominal']   = $messengerData['nominal'];
         $request_data['checked']   = strtoupper($messengerData['status']) == 'Y' ? 'checked="true"' : '';
      }

      # GTFW Tanggal
      $current_date     = date('Y-m-d', mktime(0,0,0, $cMon, $cDay, $cYear));
      Messenger::Instance()->SendToComponent(
         'tanggal',
         'Tanggal',
         'view',
         'html',
         'tanggal',
         array(
            $current_date,
            $tahun_awal,
            $tahun_akhir,
            false,
            false,
            false
         ),
         Messenger::CurrentRequest
      );
      $return     = compact('query_string', 'message', 'style', 'request_data');
      return $return;
   }

   function ParseTemplate($data = null){
      $message       = $data['message'];
      $style         = $data['style'];
      $query_string  = $data['query_string'];
      $request_data  = $data['request_data'];
      $urlReturn     = Dispatcher::Instance()->GetUrl(
         'finansi_dipa',
         'FinansiDipa',
         'view',
         'html'
      ).'&search=1&'.$query_string;

      $urlAction     = Dispatcher::Instance()->GetUrl(
         'finansi_dipa',
         'SaveDipa',
         'do',
         'html'
      ).'&'.$query_string;

      $this->mrTemplate->AddVar('content', 'URL_RETURN', $urlReturn);
      $this->mrTemplate->AddVar('content', 'URL_ACTION', $urlAction);
      $this->mrTemplate->AddVars('content', $request_data);

      if(!is_null($message)){
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $message);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $style);
      }
   }
}
?>