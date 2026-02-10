<?php
/**
* ================= doc ====================
* FILENAME     : ViewEditTransaksiSpj.html.class.php
* @package     : ViewEditTransaksiSpj
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

class ViewEditTransaksiSpj extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/finansi_transaksi_spj_history/template/');
      $this->SetTemplateFile('view_edit_transaksi_spj.html');
   }

   function ProcessRequest(){
      $messenger     = Messenger::Instance()->Receive(__FILE__);
      $mObj          = new HistoryTransaksiSpj();
      $queryString   = $mObj->_getQueryString();
      $mUnitObj      = new UserUnitKerja();
      $userId        = $mObj->getUserId();
      $transId       = Dispatcher::Instance()->Decrypt($mObj->_GET['trans_id']);
      /**
       * Get Data Transaksi
       */
      $dataTransaksi    = $mObj->getTransaksiDetail($transId);
      $dataInvoice      = $mObj->getInvoiceTransaksi($transId);
      $dataFiles        = $mObj->getFilesTransaksi($transId);

      $messengerData       = $message = $style = NULL;
      $setDate             = $mObj->setDate();
      $arrTipeTransaksi    = $mObj->getTipeTransaksi();
      $arrJenisTransaksi   = $mObj->getJenisTransaksi();
      $maxUploadedFiles    = $mObj->returnBytes(ini_get('upload_max_filesize'));
      $minYear             = (int)$setDate['min_year'];
      $maxYear             = (int)$setDate['max_year'];
      $getdate             = getdate();
      $currDay             = (int)$getdate['mday'];
      $currMon             = (int)$getdate['mon'];
      $currYear            = (int)$getdate['year'];
      $unitKerja           = $mUnitObj->GetUnitKerjaRefUser($userId);
      $requestData         = array();
      $invoices            = (array)$dataInvoice;
      $attachment          = array();
      /**
       * Parse Data transaksi
       */
      $requestData['jenis_transaksi']  = 5;
      $requestData['tipe_transaksi']   = 6;
      $requestData['id']               = $dataTransaksi['id'];
      $requestData['unit_id']          = $dataTransaksi['unit_id'];
      $requestData['unit_nama']        = $dataTransaksi['unit_nama'];
      $requestData['jenis_transaksi']  = $dataTransaksi['transaksi_jenis_id'];
      $requestData['tipe_transaksi']   = $dataTransaksi['transaksi_tipe_id'];
      $requestData['realisasi_id']     = $dataTransaksi['realisasi_id'];
      $requestData['kegiatan_id']      = $dataTransaksi['kegiatan_id'];
      $requestData['akun_id']          = $dataTransaksi['kegiatan_id'];
      $requestData['akun_nama']        = $dataTransaksi['akun_nama'];
      $requestData['tanggal']          = date('Y-m-d', strtotime($dataTransaksi['tanggal_entri']));
      $requestData['due_date']         = date('Y-m-d', strtotime($dataTransaksi['due_date']));
      $requestData['nominal_approve']     = $dataTransaksi['nominal_approve'];
      $requestData['nominal_realisasi']   = $dataTransaksi['nominal_pencairan'];
      $requestData['nominal']             = $dataTransaksi['nominal'];
      $requestData['uraian']              = $dataTransaksi['keterangan'];
      $requestData['penanggung_jawab']    = $dataTransaksi['penanggung_jawab'];

      if(!empty($dataFiles)){
         $idx     = 0;
         foreach ($dataFiles as $file) {
            // jika file tidak ada abaikan
            if($file['location'] === NULL){
               continue;
            }
            $attachment[$idx]['name']  = $file['name'];
            $attachment[$idx]['size']  = $file['size'];
            $attachment[$idx]['path']  = $file['path'];
            $idx++;
         }
      }

      if($messenger){
         $messengerData = $messenger[0][0];
         $message       = $messenger[0][1];
         $style         = $messenger[0][2];
         $tanggalDay    = (int)$messengerData['tanggal_day'];
         $tanggalMon    = (int)$messengerData['tanggal_mon'];
         $tanggalYear   = (int)$messengerData['tanggal_year'];
         $dueDateDay    = (int)$messengerData['due_date_day'];
         $dueDateMon    = (int)$messengerData['due_date_mon'];
         $dueDateYear   = (int)$messengerData['due_date_year'];

         $requestData['id']            = $messengerData['data_id'];
         $requestData['unit_id']       = $messengerData['unit_id'];
         $requestData['unit_nama']     = $messengerData['unit_nama'];
         $requestData['jenis_transaksi']  = $messengerData['jenis_transaksi'];
         $requestData['tipe_transaksi']   = $messengerData['tipe_transaksi'];
         $requestData['realisasi_id']     = $messengerData['realisasi_id'];
         $requestData['kegiatan_id']      = $messengerData['kegiatan_id'];
         $requestData['akun_id']          = $messengerData['akun_id'];
         $requestData['akun_nama']        = $messengerData['akun_nama'];
         $requestData['tanggal']          = date('Y-m-d', mktime(0,0,0, $tanggalMon, $tanggalDay, $tanggalYear));
         $requestData['due_date']         = date('Y-m-d', mktime(0,0,0, $dueDateMon, $dueDateDay, $dueDateYear));
         $requestData['nominal_approve']     = $messengerData['nominal_approve'];
         $requestData['nominal_realisasi']   = $messengerData['nominal_realisasi'];
         $requestData['nominal']             = $messengerData['nominal'];
         $requestData['uraian']              = $messengerData['uraian'];
         $requestData['penanggung_jawab']    = $messengerData['penanggung_jawab'];

         if($messengerData['invoices'] && !empty($messengerData['invoices'])){
            $index      = 0;
            foreach ($messengerData['invoices'] as $inv) {
               $invoices[$index]['nomor']    = $inv['nomor'];
               $index++;
            }
         }

         if($messengerData['attachment'] && !empty($messengerData['attachment'])){
            unset($index);
            $index   = 0;
            foreach ($messengerData['attachment'] as $files) {
               $attachment[$index]  = $files;
               $index++;
            }
         }
      }
      # Combobox
      Messenger::Instance()->SendToComponent(
         'combobox',
         'Combobox',
         'view',
         'html',
         'jenis_transaksi',
         array(
            'jenis_transaksi',
            $arrJenisTransaksi,
            $requestData['jenis_transaksi'],
            false,
            'id="cmb_jenis_transaksi" '
         ),
         Messenger::CurrentRequest
      );

      # Combobox
      Messenger::Instance()->SendToComponent(
         'combobox',
         'Combobox',
         'view',
         'html',
         'tipe_transaksi',
         array(
            'tipe_transaksi',
            $arrTipeTransaksi,
            $requestData['tipe_transaksi'],
            false,
            'id="cmb_tipe_transaksi"'
         ),
         Messenger::CurrentRequest
      );

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
         'tanggal',
         'Tanggal',
         'view',
         'html',
         'due_date',
         array(
            $requestData['due_date'],
            $minYear,
            $maxYear,
            false,
            false,
            false
         ),
         Messenger::CurrentRequest
      );
      $return['max_upload_filesize']   = $maxUploadedFiles;
      $return['unit_kerja']            = $unitKerja;
      $return['request_data']          = $requestData;
      $return['invoices']['data']      = json_encode((array)$invoices);
      $return['attachment']['data']    = json_encode((array)$attachment);
      $return['message']               = $message;
      $return['style']                 = $style;
      return $return;
   }

   function ParseTemplate($data = null){
      $requestData         = $data['request_data'];
      $unitKerja           = $data['unit_kerja'];
      $maxUploadedFiles    = $data['max_upload_filesize'];
      $invoices            = $data['invoices'];
      $attachment          = $data['attachment'];
      $message             = $data['message'];
      $style               = $data['style'];
      $urlUploadFile       = Dispatcher::Instance()->GetUrl(
         'finansi_transaksi_spj_history',
         'UploadFiles',
         'do',
         'json'
      );

      $urlDeleteFile    = Dispatcher::Instance()->GetUrl(
         'finansi_transaksi_spj_history',
         'DeleteFile',
         'do',
         'json'
      );

      $urlPopupUnitKerja   = Dispatcher::Instance()->GetUrl(
         'finansi_transaksi_spj_history',
         'PopupUnitKerja',
         'view',
         'html'
      );

      $urlPopupRealisasi   = Dispatcher::Instance()->GetUrl(
         'finansi_transaksi_spj_history',
         'PopupRealisasi',
         'view',
         'html'
      );

      $urlAction           = Dispatcher::Instance()->GetUrl(
         'finansi_transaksi_spj_history',
         'UpdateTransaksi',
         'do',
         'json'
      );

      $this->mrTemplate->AddVars('content', $invoices, 'INVOICE_');
      $this->mrTemplate->AddVars('content', $attachment, 'ATTACHMENT_');
      $this->mrTemplate->AddVars('content', $requestData);
      $this->mrTemplate->AddVar('content', 'URL_UPLOAD', $urlUploadFile);
      $this->mrTemplate->AddVar('content', 'MAX_UPLOAD_FILESIZE', (int)$maxUploadedFiles);
      $this->mrTemplate->AddVar('content', 'URL_DELETE_FILE', $urlDeleteFile);
      $this->mrTemplate->AddVar('content', 'URL_POPUP_REALISASI', $urlPopupRealisasi);
      $this->mrTemplate->AddVar('content', 'URL_ACTION', $urlAction);
      $this->mrTemplate->AddVar('data_unit', 'LEVEL', strtoupper($unitKerja['status']));
      $this->mrTemplate->AddVar('data_unit', 'URL_POPUP_UNIT', $urlPopupUnitKerja);
      $this->mrTemplate->AddVar('data_unit', 'UNIT_ID', $requestData['unit_id']);
      $this->mrTemplate->AddVar('data_unit', 'UNIT_NAMA', $requestData['unit_nama']);

      if($message){
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $message);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $style);
      }
   }
}
?>