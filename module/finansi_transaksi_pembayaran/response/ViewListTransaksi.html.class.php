<?php
/**
* ================= doc ====================
* FILENAME     : ViewListTransaksi.html.class.php
* @package     : ViewListTransaksi
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-04-23
* @Modified    : 2015-04-23
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/finansi_transaksi_pembayaran/business/TransaksiPembayaran.class.php';

class ViewListTransaksi extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/finansi_transaksi_pembayaran/template/');
      $this->SetTemplateFile('view_list_transaksi.html');
   }

   function ProcessRequest(){
      $mObj       = new TransaksiPembayaran();
      $getRange   = $mObj->getRangeYear();
      $minYear    = $getRange['min_year']-5;
      $maxYear    = $getRange['max_year'];
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
      $requestData['referensi']     = '';

      if(isset($mObj->_POST['btnSearch'])){
         $startDate_day    = (int)$mObj->_POST['start_date_day'];
         $startDate_mon    = (int)$mObj->_POST['start_date_mon'];
         $startDate_year   = (int)$mObj->_POST['start_date_year'];
         $endDate_day      = (int)$mObj->_POST['end_date_day'];
         $endDate_mon      = (int)$mObj->_POST['end_date_mon'];
         $endDate_year     = (int)$mObj->_POST['end_date_year'];
         $requestData['referensi']     = $mObj->_POST['referensi'];
         $requestData['tanggal_awal']  = date('Y-m-d', mktime(0,0,0, $startDate_mon, $startDate_day, $startDate_year));
         $requestData['tanggal_akhir'] = date('Y-m-d', mktime(0,0,0, $endDate_mon, $endDate_day, $endDate_year));
      }elseif(isset($mObj->_GET['search'])){
         $requestData['referensi']     = Dispatcher::Instance()->Decrypt($mObj->_GET['referensi']);
         $requestData['tanggal_awal']  = date('Y-m-d', strtotime(Dispatcher::Instance()->Decrypt($mObj->_GET['tanggal_awal'])));
         $requestData['tanggal_akhir'] = date('Y-m-d', strtotime(Dispatcher::Instance()->Decrypt($mObj->_GET['tanggal_akhir'])));
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

      $offset         = 0;
      $limit          = 20;
      $page           = 0;
      $total_data     = total_data;
      if(isset($_GET['page'])){
         $page    = (string) $_GET['page']->StripHtmlTags()->SqlString()->Raw();
         $offset  = ($page - 1) * $limit;
      }
      #paging url
      $url    = Dispatcher::Instance()->GetUrl(
        Dispatcher::Instance()->mModule,
        Dispatcher::Instance()->mSubModule,
        Dispatcher::Instance()->mAction,
        Dispatcher::Instance()->mType
      ).'&search='.Dispatcher::Instance()->Encrypt(1).'&'.$queryString;

      $destination_id   = "subcontent-element";
      $dataList         = $mObj->getDataTransaksi($offset, $limit, (array)$requestData);
      $total_data       = $mObj->Count();

      #send data to pagging component
      Messenger::Instance()->SendToComponent(
         'paging',
         'Paging',
         'view',
         'html',
         'paging_top',
         array(
            $limit,
            $total_data,
            $url,
            $page,
            $destination_id
         ),
         Messenger::CurrentRequest
      );

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

      $return['data_list']    = $dataList;
      $return['request_data'] = $requestData;
      $return['query_string'] = $queryString;
      $return['start']        = $offset+1;
      return $return;
   }

   function ParseTemplate($data = null){
      $requestData   = $data['request_data'];
      $dataList      = $data['data_list'];
      $queryString   = $data['query_string'];
      $start         = $data['start'];
      $urlSearch     = Dispatcher::Instance()->GetUrl(
         'finansi_transaksi_pembayaran',
         'ListTransaksi',
         'view',
         'html'
      );

      $urlAdd        = Dispatcher::Instance()->GetUrl(
         'finansi_transaksi_pembayaran',
         'TransaksiPembayaran',
         'view',
         'html'
      );

      $urlBankreceive   = Dispatcher::Instance()->GetUrl(
         'finansi_transaksi_pembayaran',
         'BankReceive',
         'view',
         'xlsx'
      );

      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);
      $this->mrTemplate->AddVar('content', 'URL_ADD', $urlAdd);
      $this->mrTemplate->AddVars('content', $requestData);
      if(empty($dataList)){
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         foreach ($dataList as $list) {
            $list['nomor']       = $start;
            $list['class_name']  = ($start % 2 <> 0) ? 'table-common-even' : '';
            if($list['nominal'] < 0){
               $list['nominal']  = '('.number_format(abs($list['nominal']), 2, ',','.').')';
            }else{
               $list['nominal']  = number_format($list['nominal'], 2, ',','.');
            }
            $list['url_bank_receive']  = $urlBankreceive.'&data_id='.Dispatcher::Instance()->Encrypt($list['id']);
            $this->mrTemplate->AddVars('data_list', $list);
            $this->mrTemplate->parseTemplate('data_list', 'a');
            $start++;
         }
      }
   }
}
?>