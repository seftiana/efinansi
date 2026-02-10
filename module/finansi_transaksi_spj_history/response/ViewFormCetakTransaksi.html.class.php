<?php
/**
* ================= doc ====================
* FILENAME     : ViewFormCetakTransaksi.html.class.php
* @package     : ViewFormCetakTransaksi
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-04-27
* @Modified    : 2015-04-27
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/finansi_transaksi_spj_history/business/HistoryTransaksiSpj.class.php';
require_once GTFWConfiguration::GetValue('application','docroot').
'main/function/terbilang.php';

class ViewFormCetakTransaksi extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/finansi_transaksi_spj_history/template/');
      $this->SetTemplateFile('view_form_cetak_transaksi.html');
   }

   function ProcessRequest(){
      $messenger  = Messenger::Instance()->Receive(__FILE__);
      $mObj       = new HistoryTransaksiSpj();
      $transId    = Dispatcher::Instance()->Decrypt($mObj->_GET['trans_id']);
      $message    = $style = NULL;
      $setDate             = $mObj->setDate();
      $minYear             = (int)$setDate['min_year'];
      $maxYear             = (int)$setDate['max_year'];
      $getdate             = getdate();
      $currDay             = (int)$getdate['mday'];
      $currMon             = (int)$getdate['mon'];
      $currYear            = (int)$getdate['year'];
      $dataList            = $mObj->getTransaksiDetail($transId);
      $dataInvoice         = $mObj->getInvoiceTransaksi($transId);
      $queryString         = $mObj->_getQueryString();
      $requestData         = array();
      $arr_pejabat_pembantu_rektor  = $mObj->GetJabatanNama('PR');
      $arr_pejabat_bendahara        = $mObj->GetJabatanNama('BENDAHARA');

      $requestData['tanggal']    = date('Y-m-d', mktime(0,0,0, $currMon, $currDay, $currYear));
      $requestData['penyetor']   = 'Pengguna Anggaran '.GTFWConfiguration::GetValue('organization', 'company_name');
      $requestData['pembuat_kwitansi'] = Security::Instance()->mAuthentication->GetCurrentUser()->GetRealName();

      if($messenger){
         $message       = $messenger[0][1];
         $style         = $messenger[0][2];
      }

      # GTFW Tanggal
      Messenger::Instance()->SendToComponent(
         'tanggal',
         'Tanggal',
         'view',
         'html',
         'tanggal',
         array(
            $requestData['tanggal'],
            $minYear,
            $maxYear,
            false,
            false,
            false
         ),
         Messenger::CurrentRequest
      );

      Messenger::Instance()->SendToComponent(
         'combobox',
         'Combobox',
         'view',
         'html',
         'pejabat_pembantu_rektor',
         array(
            'pejabat_pembantu_rektor',
            $arr_pejabat_pembantu_rektor,
            '',
            '-',
            ' id="pejabat_pembantu_rektor" style="width:200px;" '
         ), Messenger::CurrentRequest);

      Messenger::Instance()->SendToComponent(
         'combobox',
         'Combobox',
         'view',
         'html',
         'pejabat_bendahara',
         array(
            'pejabat_bendahara',
            $arr_pejabat_bendahara,
            '',
            '-',
            ' id="pejabat_bendahara" style="width:200px;" '
         ), Messenger::CurrentRequest);
      $return['data_list']    = $dataList;
      $return['invoices']     = $dataInvoice;
      $return['message']      = $message;
      $return['style']        = $style;
      $return['query_string'] = $queryString;
      $return['request_data'] = $requestData;
      return $return;
   }

   function ParseTemplate($data = null){
      $mNumber       = new Number();
      $dataList      = $data['data_list'];
      $requestData   = $data['request_data'];
      $dataList['keterangan'] = ($dataList['keterangan'] == '') ? '-' : $dataList['keterangan'];
      $dataList['terbilang']  = $mNumber->Terbilang($dataList['nominal'], 3).' Rupiah';
      if($dataList['nominal'] < 0){
         $dataList['nominal_label'] = '('.number_format(abs($dataList['nominal']), 2, ',','.').')';
      }else{
         $dataList['nominal_label'] = number_format($dataList['nominal'], 2, ',','.');
      }
      $dataInvoices  = $data['invoices'];
      $queryString   = $data['query_string'];
      $urlReturn     = Dispatcher::Instance()->GetUrl(
         'finansi_transaksi_spj_history',
         'HistoryTransaksiSpj',
         'view',
         'html'
      ).'&search=1&'.$queryString;
      $urlAction     = Dispatcher::Instance()->GetUrl(
         'finansi_transaksi_spj_history',
         'CetakTransaksi',
         'do',
         'xlsx'
      ).'&'.$queryString;

      $this->mrTemplate->AddVars('content', $dataList);
      $this->mrTemplate->AddVars('content', $requestData);
      $this->mrTemplate->AddVar('content', 'URL_RETURN', $urlReturn);
      $this->mrTemplate->AddVar('content', 'URL_ACTION', $urlAction);
   }
}
?>