<?php
/**
* ================= doc ====================
* FILENAME     : ViewEditDipa.html.class.php
* @package     : ViewEditDipa
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2014-12-10
* @Modified    : 2014-12-10
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/finansi_dipa/business/FinansiDipa.class.php';

class ViewEditDipa extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/finansi_dipa/template/');
      $this->SetTemplateFile('view_edit_dipa.html');
   }

   function ProcessRequest(){
      $message = $style = $messengerData = NULL;
      $mObj          = new FinansiDipa();
      $query_string  = $mObj->_getQueryString();
      // $query_string  = preg_replace('/\&data_id=[0-9]/', '', $query_string);
      $messenger     = Messenger::Instance()->Receive(__FILE__);
      $data_id       = Dispatcher::Instance()->Decrypt($mObj->_GET['data_id']);
      $request_data  = array();
      $dataList      = $mObj->GetDataDetail($data_id);
      $request_data['id']        = $dataList['id'];
      $request_data['kode']      = $dataList['kode'];
      $request_data['nominal']   = $dataList['nominal'];
      $request_data['status']    = strtoupper($dataList['status']);
      $request_data['status_label'] = $dataList['status_label'];
      $cDay                = (int)date('d', strtotime($dataList['tanggal']));
      $cMon                = (int)date('m', strtotime($dataList['tanggal']));
      $cYear               = (int)date('Y', strtotime($dataList['tanggal']));

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
      }

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

      $return        = compact('request_data', 'message', 'style', 'query_string');
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
      ).'&search=1&'.preg_replace('/\&data_id=[0-9]/', '', $query_string);

      $urlAction     = Dispatcher::Instance()->GetUrl(
         'finansi_dipa',
         'UpdateDipa',
         'do',
         'html'
      ).'&'.$query_string;

      $this->mrTemplate->AddVar('content', 'URL_RETURN', $urlReturn);
      $this->mrTemplate->AddVar('content', 'URL_ACTION', $urlAction);
      $this->mrTemplate->AddVars('content', $request_data);
      $this->mrTemplate->clearTemplate('status_aktif');
      $this->mrTemplate->AddVar('status_aktif', 'STATUS', strtoupper($request_data['status_label']));

      if(!is_null($message)){
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $message);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $style);
      }
   }
}
?>