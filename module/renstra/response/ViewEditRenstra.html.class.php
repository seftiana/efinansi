<?php
/**
* ================= doc ====================
* FILENAME     : ViewEditRenstra.html.class.php
* @package     : ViewEditRenstra
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2014-12-16
* @Modified    : 2014-12-16
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/renstra/business/Renstra.class.php';

class ViewEditRenstra extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/renstra/template/');
      $this->SetTemplateFile('view_edit_renstra.html');
   }

   function ProcessRequest(){
      $message    = $style = $messengerData = NULL;
      $messenger        = Messenger::Instance()->Receive(__FILE__);
      $mObj             = new Renstra();
      $query_string     = $mObj->_getQueryString();
      $setDate          = $mObj->GetTanggalRenstra();
      $dataId           = $mObj->_GET['data_id'];
      $dataList               = array();
      $dataList['startDate']  = date('Y-m-d', mktime(0,0,0, 1,1, $setDate['startDate']));
      $dataList['endDate']    = date('Y-m-d', mktime(0,0,0, 12,31, $setDate['endDate']));
      $dataList['nama']       = date('Y', strtotime($setDate['startDate'])).'-'.date('Y', strtotime($setDate['endDate']));
      $startYear              = $setDate['startYear']-5;
      $endYear                = $setDate['endYear']+5;

      $dataRenstra               = $mObj->ChangeKeyName($mObj->GetDataDetail($dataId));
      $dataList['id']            = $dataRenstra['id'];
      $dataList['startDate']     = date('Y-m-d', strtotime($dataRenstra['tanggal_awal']));
      $dataList['endDate']       = date('Y-m-d', strtotime($dataRenstra['tanggal_akhir']));
      $dataList['nama']          = $dataRenstra['nama'];
      $dataList['pimpinan']      = trim($dataRenstra['pimpinan']);
      $dataList['visi']          = trim($dataRenstra['visi']);
      $dataList['misi']          = trim($dataRenstra['misi']);
      $dataList['tujuan_umum']   = trim($dataRenstra['tujuan_umum']);
      $dataList['tujuan_khusus'] = trim($dataRenstra['tujuan_khusus']);
      $dataList['catatan']       = trim($dataRenstra['catatan']);
      $dataList['sasaran']       = trim($dataRenstra['sasaran']);
      $dataList['strategi']      = trim($dataRenstra['strategi']);
      $dataList['kebijakan']     = trim($dataRenstra['kebijakan']);
      $dataList['status']        = $dataRenstra['status'];

      if($messenger){
         $messengerData       = $messenger[0][0];
         $message             = $messenger[0][1];
         $style               = $messenger[0][2];

         $startDate_day    = (int)$messengerData['tanggal_awal_day'];
         $startDate_mon    = (int)$messengerData['tanggal_awal_mon'];
         $startDate_year   = (int)$messengerData['tanggal_awal_year'];
         $endDate_day      = (int)$messengerData['tanggal_akhir_day'];
         $endDate_mon      = (int)$messengerData['tanggal_akhir_mon'];
         $endDate_year     = (int)$messengerData['tanggal_akhir_year'];

         $dataList['id']            = $messengerData['data_id'];
         $dataList['startDate']     = date('Y-m-d', mktime(0,0,0, $startDate_mon, $startDate_day, $startDate_year));
         $dataList['endDate']       = date('Y-m-d', mktime(0,0,0, $endDate_mon, $endDate_day, $endDate_year));
         $dataList['nama']          = $messengerData['nama'];
         $dataList['pimpinan']      = trim($messengerData['pimpinan']);
         $dataList['visi']          = trim($messengerData['visi']);
         $dataList['misi']          = trim($messengerData['misi']);
         $dataList['tujuan_umum']   = trim($messengerData['tujuan_umum']);
         $dataList['tujuan_khusus'] = trim($messengerData['tujuan_khusus']);
         $dataList['catatan']       = trim($messengerData['catatan']);
         $dataList['sasaran']       = trim($messengerData['sasaran']);
         $dataList['strategi']      = trim($messengerData['strategi']);
         $dataList['kebijakan']     = trim($messengerData['kebijakan']);
      }


      # GTFW Tanggal
      Messenger::Instance()->SendToComponent(
         'tanggal',
         'Tanggal',
         'view',
         'html',
         'tanggal_awal',
         array(
            $dataList['startDate'],
            $startYear,
            $endYear,
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
            $dataList['endDate'],
            $startYear,
            $endYear,
            false,
            false,
            false
         ),
         Messenger::CurrentRequest
      );

      $return['query_string']    = $query_string;
      $return['data_list']       = $dataList;
      $return['message']         = $message;
      $return['style']           = $style;
      return $return;
   }

   function ParseTemplate($data = null){
      $query_string        = $data['query_string'];
      $dataList            = $data['data_list'];
      $message             = $data['message'];
      $style               = $data['style'];
      $status              = (strtoupper($dataList['status']) == 'Y') ? 'ACTIVE' : 'NOT_ACTIVE';
      $urlReturn           = Dispatcher::Instance()->GetUrl(
         'renstra',
         'Renstra',
         'view',
         'html'
      ).'&search=1&'.$query_string;

      $urlAction           = Dispatcher::Instance()->GetUrl(
         'renstra',
         'UpdateRenstra',
         'do',
         'json'
      ).'&'.$query_string;

      if($message){
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $message);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $style);
      }

      $this->mrTemplate->AddVar('content', 'URL_RETURN', $urlReturn);
      $this->mrTemplate->AddVar('content', 'URL_ACTION', $urlAction);
      $this->mrTemplate->AddVars('content', $dataList);
      $this->mrTemplate->AddVar('status_aktif', 'STATUS', $status);
   }
}
?>