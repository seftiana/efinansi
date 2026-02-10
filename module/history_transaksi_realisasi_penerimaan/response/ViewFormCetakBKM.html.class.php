<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/history_transaksi_realisasi_penerimaan/business/AppTransaksi.class.php';
require_once GTFWConfiguration::GetValue('application','docroot').
'main/function/terbilang.php';

class ViewFormCetakBKM extends HtmlResponse {
   var $Data;
   var $Pesan;

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') .
      'module/history_transaksi_realisasi_penerimaan/template');
      $this->SetTemplateFile('view_form_cetak_bkm.html');
   }

   function ProcessRequest() {
      $messenger  = Messenger::Instance()->Receive(__FILE__);
      $Obj        = new AppTransaksi();
      $idDec      = Dispatcher::Instance()->Decrypt($Obj->_GET['dataId']);
      $setDate             = $Obj->setDate();
      $minYear             = (int)$setDate['min_year'];
      $maxYear             = (int)$setDate['max_year'];
      $getdate             = getdate();
      $currDay             = (int)$getdate['mday'];
      $currMon             = (int)$getdate['mon'];
      $currYear            = (int)$getdate['year'];
      $dataList            = $Obj->getTransaksiDetail($idDec);
      $dataInvoice         = $Obj->getInvoiceTransaksi($idDec);
      $queryString         = $Obj->_getQueryString();
      $requestData         = array();
      $arr_pejabat_pembantu_rektor  = $Obj->GetJabatanNama('PR');
      $arr_pejabat_bendahara        = $Obj->GetJabatanNama('BENDAHARA');
      $requestData['tanggal']    = date('Y-m-d', mktime(0,0,0, $currMon, $currDay, $currYear));
      $requestData['penyetor']   = 'BANK BUKOPIN';
      $requestData['pembuat_kwitansi'] = Security::Instance()->mAuthentication->GetCurrentUser()->GetRealName();

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
      $return['data_list']       = $dataList;
      $return['query_string']    = $queryString;
      $return['request_data']    = $requestData;
      return $return;
   }

   function ParseTemplate($data = NULL) {
      $mNumber       = new Number();
      $dataList      = $data['data_list'];
      $queryString   = $data['query_string'];
      $requestData   = $data['request_data'];
      $dataList['keterangan'] = ($dataList['keterangan'] == '') ? '-' : $dataList['keterangan'];
      $dataList['terbilang']  = $mNumber->Terbilang($dataList['nominal'], 3).' Rupiah';
      if($dataList['nominal'] < 0){
         $dataList['nominal_label'] = '('.number_format(abs($dataList['nominal']), 2, ',','.').')';
      }else{
         $dataList['nominal_label'] = number_format($dataList['nominal'], 2, ',','.');
      }

      $urlReturn     = Dispatcher::Instance()->GetUrl(
         'history_transaksi_realisasi_penerimaan',
         'HTRealisasiPenerimaan',
         'view',
         'html'
      ).'&search=1&'.$queryString;
      $urlAction     = Dispatcher::Instance()->GetUrl(
         'history_transaksi_realisasi_penerimaan',
         'CetakBkm',
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