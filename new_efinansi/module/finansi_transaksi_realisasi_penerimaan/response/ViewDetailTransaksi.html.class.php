<?php
/**
* ================= doc ====================
* FILENAME     : ViewDetailTransaksi.html.class.php
* @package     : ViewDetailTransaksi
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-03-10
* @Modified    : 2015-03-10
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/finansi_transaksi_realisasi_penerimaan/business/TransaksiPenerimaan.class.php';

class ViewDetailTransaksi extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/finansi_transaksi_realisasi_penerimaan/template/');
      $this->SetTemplateFile('view_detail_transaksi.html');
   }

   function ProcessRequest(){
      $messenger  = Messenger::Instance()->Receive(__FILE__);
      $mObj       = new TransaksiPenerimaan();
      $dataId     = Dispatcher::Instance()->Decrypt($mObj->_GET['trans_id']);
      $message    = $style = NULL;
      $dataTransaksi    = $mObj->getDataTransaksiDetail($dataId);
      $dataInvoice      = $mObj->getInvoiceTransaksi($dataId);
      $dataFiles        = $mObj->getFilesTransaksi($dataId);

      if($messenger){
         $message       = $messenger[0][1];
         $style         = $messenger[0][2];
      }
      $return['message']   = $message;
      $return['style']     = $style;
      $return['transaksi'] = $dataTransaksi;
      $return['invoices']  = $dataInvoice;
      $return['files']     = $dataFiles;
      return $return;
   }

   function ParseTemplate($data = null){
      $message       = $data['message'];
      $style         = $data['style'];
      $urlAdd        = Dispatcher::Instance()->GetUrl(
         'finansi_transaksi_realisasi_penerimaan',
         'InputTransaksiRealisasiPenerimaan',
         'view',
         'html'
      );
      $urlHistory    = Dispatcher::Instance()->GetUrl(
         'history_transaksi_realisasi_penerimaan',
         'HTRealisasiPenerimaan',
         'view',
         'html'
      );
      $dataTransaksi    = $data['transaksi'];
      $dataInvoices     = $data['invoices'];
      $dataFiles        = $data['files'];
      $dataTransaksi['nominal_approve']   = number_format($dataTransaksi['nominal_approve'], 2, ',','.');
      $dataTransaksi['nominal_realisasi'] = number_format($dataTransaksi['nominal_realisasi'], 2, ',','.');
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