<?php

/**
* @module program
* @author Rendi Kasigi,
* @mail to rkasigi@gmail.com
* @copyright 2008&copy;Gamatechno
*/
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/periode_tahun/business/PeriodeTahun.class.php';

class ViewInputPeriodeTahun extends HtmlResponse
{
   public function TemplateModule()
   {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') .
         'module/periode_tahun/template');
      $this->SetTemplateFile('input_periode_tahun.html');
   }

   public function ProcessRequest()
   {
      $mObj          = new PeriodeTahun();
      $queryString   = $mObj->_getQueryString();
      $messenger     = Messenger::Instance()->Receive(__FILE__);
      $requestData   = array();
      $message       = $style = $messengerData = NULL;
      $dataRenstra   = $mObj->ChangeKeyName($mObj->getRenstra(true));
      $minYear       = date('Y', strtotime($dataRenstra[0]['tanggal_awal']));
      $maxYear       = date('Y', strtotime($dataRenstra[0]['tanggal_akhir']));
      $requestData['renstra_id']    = $dataRenstra[0]['id'];
      $requestData['renstra_nama']  = $dataRenstra[0]['name'];
      $requestData['renstra_awal']  = date('Y-m-d', strtotime($dataRenstra[0]['tanggal_awal']));
      $requestData['renstra_akhir'] = date('Y-m-d', strtotime($dataRenstra[0]['tanggal_akhir']));
      $requestData['start_date']    = date('Y-m-d', mktime(0,0,0, 1, 1, $minYear));
      $requestData['end_date']      = date('Y-m-t', mktime(0,0,0, 12, 1, $minYear));

      if($messenger){
         $messengerData       = $messenger[0][0];
         $message             = $messenger[0][1];
         $style               = $messenger[0][2];
         $startDate_day       = (int)$messengerData['tanggal_awal_day'];
         $startDate_mon       = (int)$messengerData['tanggal_awal_mon'];
         $startDate_year      = (int)$messengerData['tanggal_awal_year'];
         $endDate_day         = (int)$messengerData['tanggal_akhir_day'];
         $endDate_mon         = (int)$messengerData['tanggal_akhir_mon'];
         $endDate_year        = (int)$messengerData['tanggal_akhir_year'];

         $requestData['id']               = $messengerData['data_id'];
         $requestData['renstra_id']       = $messengerData['renstra_id'];
         $requestData['nama']             = $messengerData['nama'];
         $requestData['checked_aktif']    = (strtoupper($messengerData['status_aktif']) == 'Y') ? 'checked' : '';
         $requestData['checked_open']     = (strtoupper($messengerData['status_open']) == 'Y') ? 'checked' : '';
         $requestData['start_date']       = date('Y-m-d', mktime(0,0,0, $startDate_mon, $startDate_day, $startDate_year));
         $requestData['end_date']         = date('Y-m-d', mktime(0,0,0, $endDate_mon, $endDate_day, $endDate_year));
      }

      # GTFW Tanggal
      Messenger::Instance()->SendToComponent(
         'tanggal',
         'Tanggal',
         'view',
         'html',
         'tanggal_awal',
         array(
            $requestData['start_date'],
            $minYear,
            $maxYear,
            false,
            false,
            false
         ),
         Messenger::CurrentRequest
      );

      Messenger::Instance()->SendToComponent(
         'tanggal',
         'Tanggal',
         'view',
         'html',
         'tanggal_akhir',
         array(
            $requestData['end_date'],
            $minYear,
            $maxYear,
            false,
            false,
            false
         ),
         Messenger::CurrentRequest
      );

      $return['query_string']    = $queryString;
      $return['message']         = $message;
      $return['style']           = $style;
      $return['request_data']    = $requestData;
      return $return;
   }

   public function ParseTemplate($data = NULL)
   {
      $requestData      = $data['request_data'];
      $queryString      = $data['query_string'];
      $message          = $data['message'];
      $style            = $data['style'];
      $urlReturn        = Dispatcher::Instance()->GetUrl(
         'periode_tahun',
         'PeriodeTahun',
         'view',
         'html'
      ).'&search=1&'.$queryString;

      $urlAction        = Dispatcher::Instance()->GetUrl(
         'periode_tahun',
         'AddPeriodeTahun',
         'do',
         'json'
      ).'&'.$queryString;

      $this->mrTemplate->AddVar('content', 'URL_RETURN', $urlReturn);
      $this->mrTemplate->AddVar('content', 'URL_ACTION', $urlAction);
      $this->mrTemplate->AddVars('content', $requestData);

      if($message){
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $message);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $style);
      }
   }
}
?>