<?php
/**
* ================= doc ====================
* FILENAME     : ViewBuktiTransaksi.html.class.php
* @package     : ViewBuktiTransaksi
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-05-19
* @Modified    : 2015-05-19
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/finansi_transaksi_penerimaan_kas/business/TransaksiPenerimaanKas.class.php';
require_once GTFWConfiguration::GetValue('application','docroot').
'main/function/terbilang.php';

class ViewBuktiTransaksi extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/finansi_transaksi_penerimaan_kas/template/');
      $this->SetTemplateFile('view_bukti_transaksi.html');
   }

   function ProcessRequest(){
      $messenger  = Messenger::Instance()->Receive(__FILE__);
      $mObj       = new TransaksiPenerimaanKas();
      $mNumber    = new Number();
      $message          = $style = $data = NULL;
      $query_string     = $mObj->_getQueryString();
      $query_string     = preg_replace('/(transaksi_id=[\d+])/', '', $query_string);
      $query_string     = preg_replace('/\&[\&]+/', '&', $query_string);
      $query_string     = preg_replace('/^[\&]/', '', $query_string);
      $query_return     = (!empty($query_string)) ? '&search=1&'.$query_string : '';
      $transaksi_id     = Dispatcher::Instance()->Decrypt($mObj->_GET['transaksi_id']);
      $data_transaksi   = $mObj->getTransaksiDetil($transaksi_id);
      $data_transaksi['terbilang']  = $mNumber->Terbilang($data_transaksi['nominal'], 3);
      $transaksi_detail = $mObj->getListTransaksiDetail($transaksi_id);

      if($messenger){
         $message       = $messenger[0][1];
         $style         = $messenger[0][2];
      }
      return compact('query_string', 'query_return', 'data_transaksi', 'transaksi_detail', 'transaksi_id', 'message', 'style');
   }

   function ParseTemplate($data = null){
      extract($data);
      $url_add       = Dispatcher::Instance()->GetUrl(
         'finansi_transaksi_penerimaan_kas',
         'Transaksi',
         'view',
         'html'
      ).'&'.$query_string;

      $url_list      = Dispatcher::Instance()->GetUrl(
         'finansi_transaksi_penerimaan_kas',
         'TransaksiPenerimaanKas',
         'view',
         'html'
      ).$query_return;
      $url_export    = Dispatcher::Instance()->GetUrl(
         'finansi_transaksi_penerimaan_kas',
         'BuktiTransaksi',
         'view',
         'xlsx'
      ).'&'.$query_string.'&transaksi_id='.$transaksi_id;

      $url_edit      = Dispatcher::Instance()->GetUrl(
         'finansi_transaksi_penerimaan_kas',
         'EditTransaksi',
         'view',
         'html'
      ).'&'.$query_string.'&transaksi_id='.$transaksi_id;

      $this->mrTemplate->AddVars('content', compact('url_add', 'url_list', 'url_export', 'url_edit'));
      $this->mrTemplate->AddVars('content', $data_transaksi);
      if(empty($transaksi_detail)){
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
         $this->mrTemplate->setAttribute('total_komponen', 'visibility', 'hidden');
      }else{
         $this->mrTemplate->setAttribute('total_komponen', 'visibility', 'visible');
         $this->mrTemplate->AddVar('total_komponen', 'nominal', number_format($data_transaksi['nominal'], 2, ',', '.'));
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         foreach ($transaksi_detail as $item) {
            $item['nominal']  = number_format($item['nominal'], 2, ',','.');
            $this->mrTemplate->AddVars('data_list', $item);
            $this->mrTemplate->parseTemplate('data_list', 'a');
         }
      }

      if($message){
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $message);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $style);
      }
   }
}
?>