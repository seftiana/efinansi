<?php
/**
* ================= doc ====================
* FILENAME     : ViewTransaksiPembayaran.html.class.php
* @package     : ViewTransaksiPembayaran
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-04-22
* @Modified    : 2015-04-22
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/finansi_transaksi_pembayaran/business/TransaksiPembayaran.class.php';

class ViewTransaksiPembayaran extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/finansi_transaksi_pembayaran/template/');
      $this->SetTemplateFile('view_transaksi_pembayaran.html');
   }

   function ProcessRequest(){
      $messenger  = Messenger::Instance()->Receive(__FILE__);
      $mObj       = new TransaksiPembayaran();
      $getRange   = $mObj->getRangeYear();
      $getPeriodePembayaran = $mObj->getDataPeriodePembayaran();
      
      $minYear    = $getRange['min_year']-5;
      $maxYear    = $getRange['max_year'];
      $message    = $style = $messengerData = NULL;
      $dataList   = array();
      $requestData   = array();
      $queryString   = '';
      $arrType       = array(
         array(
            'id' => 'piutang',
            'name' => 'Piutang'
         ),
         array(
            'id' => 'pengakuan',
            'name' => 'Pengakuan'
         )
      );
      $requestData['tanggal_awal']  = date('Y-m-d', strtotime($getRange['tanggal_awal']));
      $requestData['tanggal_akhir'] = date('Y-m-d', strtotime($getRange['tanggal_akhir']));
      $requestData['show']          = false;
      $requestData['type']          = 'piutang';

      if(isset($mObj->_POST['btnSearch'])){
         $startDate_day    = (int)$mObj->_POST['start_date_day'];
         $startDate_mon    = (int)$mObj->_POST['start_date_mon'];
         $startDate_year   = (int)$mObj->_POST['start_date_year'];
         $endDate_day      = (int)$mObj->_POST['end_date_day'];
         $endDate_mon      = (int)$mObj->_POST['end_date_mon'];
         $endDate_year     = (int)$mObj->_POST['end_date_year'];
         $requestData['periodeId'] = $mObj->_POST['periode_bayar'];
         $requestData['show']          = true;
         $requestData['type']          = $mObj->_POST['type'];
         $requestData['tanggal_awal']  = date('Y-m-d', mktime(0,0,0, $startDate_mon, $startDate_day, $startDate_year));
         $requestData['tanggal_akhir'] = date('Y-m-d', mktime(0,0,0, $endDate_mon, $endDate_day, $endDate_year));
      }elseif(isset($mObj->_GET['search'])){
         $requestData['periodeId'] = Dispatcher::Instance()->Decrypt($mObj->_GET['periode_bayar']);
         $requestData['type']          = Dispatcher::Instance()->Decrypt($mObj->_GET['type']);
         $requestData['show']          = true;
         $requestData['tanggal_awal']  = date('Y-m-d', strtotime(Dispatcher::Instance()->Decrypt($mObj->_GET['tanggal_awal'])));
         $requestData['tanggal_akhir'] = date('Y-m-d', strtotime(Dispatcher::Instance()->Decrypt($mObj->_GET['tanggal_akhir'])));
      }

      if($requestData['show'] === true){
         $mData            = $mObj->getDataPiutangPembayaran($requestData);
         $dataList         = $mData['data_list'];
      }
      if(method_exists(Dispatcher::Instance(), 'getQueryString')){
         # @param array
         $queryString   = Dispatcher::instance()->getQueryString($requestData);
      }else{
         $query         = array();
         foreach ($requestData as $key => $value) {
            $query[$key]   = Dispatcher::Instance()->Encrypt($value);
         }
         $queryString      = urldecode(http_build_query($query));
      }

      # GTFW Tanggal
      Messenger::Instance()->SendToComponent(
         'tanggal',
         'Tanggal',
         'view',
         'html',
         'start_date',
         array(
            $requestData['tanggal_awal'],
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
         'end_date',
         array(
            $requestData['tanggal_akhir'],
            $minYear,
            $maxYear,
            false,
            false,
            false
         ),
         Messenger::CurrentRequest
      );

      # Combobox
      Messenger::Instance()->SendToComponent(
         'combobox',
         'Combobox',
         'view',
         'html',
         'periode_bayar',
         array(
            'periode_bayar',
            $getPeriodePembayaran['data_list'],
            $requestData['periodeId'],
            false,
            'id="cmb_periode_bayar" '
         ),
         Messenger::CurrentRequest
      );
      
      # Combobox
      Messenger::Instance()->SendToComponent(
         'combobox',
         'Combobox',
         'view',
         'html',
         'type',
         array(
            'type',
            $arrType,
            $requestData['type'],
            false,
            'id="cmb_tipe" style="width:100px"'
         ),
         Messenger::CurrentRequest
      );

      if($messenger){
         $message    = $messenger[0][1];
         $style      = $messenger[0][2];
      }

      $return['message']      = $message;
      $return['style']        = $style;
      $return['data_list']    = $dataList;
      $return['request_data'] = $requestData;
      $return['query_string'] = $queryString;
      return $return;
   }

   function ParseTemplate($data = null){
      $message       = $data['message'];
      $style         = $data['style'];
      $dataList      = $data['data_list'];
      $requestData   = $data['request_data'];
      $queryString   = $data['query_string'];
      $totalData     = 0;
      $urlSearch     = Dispatcher::Instance()->GetUrl(
         'finansi_transaksi_pembayaran',
         'TransaksiPembayaran',
         'view',
         'html'
      );

      $urlAction     = Dispatcher::Instance()->GetUrl(
         'finansi_transaksi_pembayaran',
         'SaveTransaksi',
         'do',
         'json'
      ).'&'.$queryString;

      $urlListTransaksi    = Dispatcher::Instance()->GetUrl(
         'finansi_transaksi_pembayaran',
         'ListTransaksi',
         'view',
         'html'
      );

      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);
      $this->mrTemplate->AddVar('content', 'URL_ACTION', $urlAction);
      $this->mrTemplate->AddVar('content', 'URL_LIST', $urlListTransaksi);
      $this->mrTemplate->AddVars('content', $requestData);
      if(empty($dataList)){
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         $index      = 0;
         foreach ($dataList as $list) {
            $list['index'] = $index;
            $list['type']  = $requestData['type'];
            if($list['nominal'] < 0){
               $list['nominal_label']  = '('.number_format(abs($list['nominal']), 2, ',','.');
            }else{
               $list['nominal_label']  = number_format($list['nominal'], 2, ',','.');
            }
            
            if($list['potongan'] < 0){
               $list['potongan_label']  = '('.number_format(abs($list['potongan']), 2, ',','.');
            }else{
               $list['potongan_label']  = number_format($list['potongan'], 2, ',','.');
            }
            
            $this->mrTemplate->AddVars('data_list', $list);
            $this->mrTemplate->parseTemplate('data_list', 'a');
            $totalData+=1;
            $index++;
         }
      }

      if((int)$totalData > 0){
         $this->mrTemplate->SetAttribute('button', 'visibility', 'visible');
      }

      if($message){
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $message);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $style);
      }
   }
}
?>