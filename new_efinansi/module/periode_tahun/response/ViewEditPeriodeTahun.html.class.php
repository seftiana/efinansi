<?php
/**
* ================= doc ====================
* FILENAME     : ViewEditPeriodeTahun.html.class.php
* @package     : ViewEditPeriodeTahun
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-02-09
* @Modified    : 2015-02-09
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/periode_tahun/business/PeriodeTahun.class.php';

class ViewEditPeriodeTahun extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/periode_tahun/template/');
      $this->SetTemplateFile('view_edit_periode_tahun.html');
   }

   function ProcessRequest(){
      $mObj          = new PeriodeTahun();
      $queryString   = $mObj->_getQueryString();
      $messenger     = Messenger::Instance()->Receive(__FILE__);
      $dataId        = Dispatcher::Instance()->Decrypt($mObj->_GET['data_id']);
      $requestData   = array();
      $message       = $style = $messengerData = NULL;
      $dataRenstra   = $mObj->ChangeKeyName($mObj->getRenstra(true));
      $minYear       = date('Y', strtotime($dataRenstra[0]['tanggal_awal']));
      $maxYear       = date('Y', strtotime($dataRenstra[0]['tanggal_akhir']));

      $dataDetail    = $mObj->ChangeKeyName($mObj->getDataDetail($dataId));
      $requestData['renstra_awal']     = date('Y-m-d', strtotime($dataRenstra[0]['tanggal_awal']));
      $requestData['renstra_akhir']    = date('Y-m-d', strtotime($dataRenstra[0]['tanggal_akhir']));
      $requestData['id']               = $dataDetail['id'];
      $requestData['renstra_id']       = $dataDetail['renstra_id'];
      $requestData['renstra_nama']     = $dataDetail['renstra_nama'];
      $requestData['nama']             = $dataDetail['nama'];
      $requestData['status_aktif']     = strtoupper($dataDetail['status_aktif']);
      $requestData['checked_aktif']    = (strtoupper($dataDetail['status_aktif']) == 'Y') ? 'checked' : '';
      $requestData['checked_open']     = (strtoupper($dataDetail['status_open']) == 'Y') ? 'checked' : '';
      $requestData['start_date']       = date('Y-m-d', strtotime($dataDetail['tgl_awal']));
      $requestData['end_date']         = date('Y-m-d', strtotime($dataDetail['tgl_akhir']));

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

   function ParseTemplate($data = null){
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
         'UpdatePeriodeTahun',
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

      if($requestData['status_aktif'] == 'Y'){
         $this->mrTemplate->AddVar('status_aktif', 'AKTIF', 'YES');
      }else{
         $this->mrTemplate->AddVar('status_aktif', 'AKTIF', 'NO');
      }
   }
}
?>