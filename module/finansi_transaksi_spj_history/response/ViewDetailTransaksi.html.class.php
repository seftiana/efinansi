<?php
/**
* ================= doc ====================
* FILENAME     : ViewDetailTransaksi.html.class.php
* @package     : ViewDetailTransaksi
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-03-23
* @Modified    : 2015-03-23
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/finansi_transaksi_spj_history/business/HistoryTransaksiSpj.class.php';

class ViewDetailTransaksi extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/finansi_transaksi_spj_history/template/');
      $this->SetTemplateFile('view_detail_transaksi.html');
   }

   function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }

   function ProcessRequest(){
      $messenger  = Messenger::Instance()->Receive(__FILE__);
      $mObj       = new HistoryTransaksiSpj();
      $transId    = Dispatcher::Instance()->Decrypt($mObj->_GET['trans_id']);
      $message    = $style = NULL;
      $dataList   = $mObj->getTransaksiDetail($transId);
      $dataInvoice      = $mObj->getInvoiceTransaksi($transId);
      $dataFiles        = $mObj->getFilesTransaksi($transId);

      if($messenger){
         $message       = $messenger[0][1];
         $style         = $messenger[0][2];
      }

      $return['data_list']    = $dataList;
      $return['invoices']     = $dataInvoice;
      $return['files']        = $dataFiles;
      $return['message']      = $message;
      $return['style']        = $style;
      return $return;
   }

   function ParseTemplate($data = null){
      $message       = $data['message'];
      $style         = $data['style'];
      $dataTransaksi = $data['data_list'];
      $dataInvoices  = $data['invoices'];
      $dataFiles     = $data['files'];
      $urlAdd        = Dispatcher::Instance()->GetUrl(
         'finansi_transaksi_spj',
         'addTransaksiSpj',
         'view',
         'html'
      );
      $urlHistory    = Dispatcher::Instance()->GetUrl(
         'finansi_transaksi_spj_history',
         'HistoryTransaksiSpj',
         'view',
         'html'
      );
      $dataTransaksi['nominal_approve']   = number_format($dataTransaksi['nominal_approve'], 2, ',','.');
      $dataTransaksi['nominal_pencairan'] = number_format($dataTransaksi['nominal_pencairan'], 2, ',','.');
      $dataTransaksi['nominal']           = number_format($dataTransaksi['nominal'], 2, ',','.');

      $this->mrTemplate->AddVars('content', $dataTransaksi);
      $this->mrTemplate->AddVar('content', 'URL_ADD', $urlAdd);
      $this->mrTemplate->AddVar('content', 'URL_LIST', $urlHistory);
      if(empty($dataInvoices)){
         $this->mrTemplate->AddVar('data_invoices', 'DATA_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('data_invoices', 'DATA_EMPTY', 'NO');
         foreach($dataInvoices as $invoice){
            $this->mrTemplate->AddVars('invoice_list', $invoice);
            $this->mrTemplate->parseTemplate('invoice_list', 'a');
         }
      }

      if(empty($dataFiles)){
         $this->mrTemplate->AddVar('data_files', 'DATA_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('data_files', 'DATA_EMPTY', 'NO');
         foreach ($dataFiles as $file) {
            $this->mrTemplate->clearTemplate('download');
            if($file['download'] === true){
               $file['location']    = $file['location'];
               $file['class_name']  = 'download';
               $this->mrTemplate->AddVar('download', 'EXISTS', 'YES');
            }else{
               $file['location']    = 'javascript:void(0);';
               $file['class_name']  = 'not-exists';
               $this->mrTemplate->AddVar('download', 'EXISTS', 'NO');
            }

            $this->mrTemplate->AddVars('download', $file);
            $this->mrTemplate->AddVars('file_list', $file);
            $this->mrTemplate->parseTemplate('file_list', 'a');
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