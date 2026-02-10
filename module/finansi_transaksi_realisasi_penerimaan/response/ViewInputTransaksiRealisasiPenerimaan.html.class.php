<?php
/**
* ================= doc ====================
* FILENAME     : ViewInputTransaksiRealisasiPenerimaan.html.class.php
* @package     : ViewInputTransaksiRealisasiPenerimaan
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-03-04
* @Modified    : 2015-03-04
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/user_unit_kerja/business/UserUnitKerja.class.php';
require_once GTFWConfiguration::GetValue('application','docroot').
'module/finansi_transaksi_realisasi_penerimaan/business/TransaksiPenerimaan.class.php';

class ViewInputTransaksiRealisasiPenerimaan extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/finansi_transaksi_realisasi_penerimaan/template/');
      $this->SetTemplateFile('view_input_transaksi_realisasi_penerimaan.html');
   }

   function ProcessRequest(){
      $messenger        = Messenger::Instance()->Receive(__FILE__);
      $mObj             = new TransaksiPenerimaan();
      $mUnitObj         = new UserUnitKerja();
      $userId           = $mObj->getUserId();
      $unitKerja        = $mUnitObj->GetUnitKerjaRefUser($userId);
      $periodeTahun     = $mObj->getPeriodeTahun();
      $maxUploadedFiles    = $mObj->returnBytes(ini_get('upload_max_filesize'));
      $arrJenisTransaksi   = $mObj->GetComboJenisTransaksi();
      $arrTipeTransaksi    = $mObj->GetComboTipeTransaksi(array(
         'TA',
         'PSA'
      ));
      $message             = $style = $messengerData = NULL;
      $fileAttach          = array();
      $invoices            = array();
      $skenarioJurnal      = array();
      $minYear             = date('Y', strtotime($periodeTahun[0]['tanggal_awal']));
      $maxYear             = date('Y', strtotime($periodeTahun[0]['tanggal_akhir']));
      $getdate             = getdate();
      $currMon             = (int)$getdate['mon'];
      $currDay             = (int)$getdate['mday'];
      $requestData         = array();
      $requestData['unit_id']    = $unitKerja['id'];
      $requestData['unit_nama']  = $unitKerja['nama'];
      $requestData['tanggal']    = date('Y-m-d', mktime(0,0,0, $currMon, $currDay, $minYear));
      $requestData['due_date']   = date('Y-m-d', mktime(0,0,0, $currMon, $currDay+7, $minYear));
      $requestData['jenis_transaksi_id']  = 5;
      $requestData['tipe_transaksi_id']   = 1;
      $requestData['penanggung_jawab']    = Security::Instance()->mAuthentication->GetCurrentUser()->GetRealName();
      $requestData['skenario']            = 'manual';
      $requestData['skenario_label']      = 'manual';

      if($messenger){
         $messengerData    = $messenger[0][0];
         $message          = $messenger[0][1];
         $style            = $messenger[0][2];

         $tanggalDay       = (int)$messengerData['tanggal_transaksi_day'];
         $tanggalMon       = (int)$messengerData['tanggal_transaksi_mon'];
         $tanggalYear      = (int)$messengerData['tanggal_transaksi_year'];
         $dueDateDay       = (int)$messengerData['due_date_day'];
         $dueDateMon       = (int)$messengerData['due_date_mon'];
         $dueDateYear      = (int)$messengerData['due_date_year'];

         $requestData['id']      = $messengerData['data_id'];
         $requestData['unit_id'] = $messengerData['unit_id'];
         $requestData['unit_nama']  = $messengerData['unit_nama'];
         $requestData['map_id']     = $messengerData['map_id'];
         $requestData['map_nama']   = $messengerData['map_nama'];
         $requestData['jenis_transaksi_id']  = $messengerData['jenis_transaksi'];
         $requestData['tipe_transaksi_id']   = $messengerData['tipe_transaksi'];
         $requestData['no_invoice']          = $messengerData['no_invoice'];
         $requestData['nominal_approve']     = $messengerData['nominal_approve'];
         $requestData['nominal_realisasi']   = $messengerData['nominal_realisasi'];
         $requestData['nominal']             = $messengerData['nominal'];
         $requestData['keterangan']          = $messengerData['catatan_transaksi'];
         $requestData['penanggung_jawab']    = $messengerData['penanggung_jawab'];
         $requestData['skenario']            = $messengerData['skenario'];
         $requestData['skenario_label']      = $messengerData['skenario_label'];
         $requestData['tanggal']    = date('Y-m-d', mktime(0,0,0, $tanggalMon, $tanggalDay, $tanggalYear));
         $requestData['due_date']   = date('Y-m-d', mktime(0,0,0, $dueDateMon, $dueDateDay, $dueDateYear));

         if($messengerData['invoices'] && !empty($messengerData['invoices'])){
            $index      = 0;
            foreach ($messengerData['invoices'] as $inv) {
               $invoices[$index]['nomor']    = $inv['nomor'];
               $index++;
            }
         }

         if($messengerData['attachment'] && !empty($messengerData['attachment'])){
            $index      = 0;
            foreach ($messengerData['attachment'] AS $file) {
               $fileAttach[$index]['path']      = $file['path'];
               $fileAttach[$index]['name']      = $file['name'];
               $fileAttach[$index]['size']      = $file['size'];

               $index++;
            }
         }

         if($messengerData['skenario_jurnal'] && !empty($messengerData['skenario_jurnal'])){
            $index      = 0;
            foreach ($messengerData['skenario_jurnal'] as $sk) {
               $skenarioJurnal[$index]['id']    = $sk['id'];
               $skenarioJurnal[$index]['name']  = $sk['nama'];
               $index++;
            }
         }
      }

      # GTFW Tanggal
      Messenger::Instance()->SendToComponent(
         'tanggal',
         'Tanggal',
         'view',
         'html',
         'tanggal_transaksi',
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

      # Jenis Transaksi
      Messenger::Instance()->SendToComponent(
         'combobox',
         'Combobox',
         'view',
         'html',
         'jenis_transaksi',
         array(
            'jenis_transaksi',
            $arrJenisTransaksi,
            $requestData['jenis_transaksi_id'],
            false,
            ' style="width:200px;" id="cmb_jenis_transaksi" onchange="showHideMAK(this)"'
         ), Messenger::CurrentRequest
      );

      Messenger::Instance()->SendToComponent(
         'combobox',
         'Combobox',
         'view',
         'html',
         'tipe_transaksi',
         array(
            'tipe_transaksi',
            $arrTipeTransaksi,
            $requestData['tipe_transaksi_id'],
            false,
            'style="width:200px;"  id="tipe_transaksi"'
         ), Messenger::CurrentRequest
      );

      $return['unit_kerja']            = $unitKerja;
      $return['max_upload_filesize']   = $maxUploadedFiles;
      $return['style']                 = $style;
      $return['message']               = $message;
      $return['request_data']          = $requestData;
      $return['files']['data']         = json_encode($fileAttach);
      $return['invoices']['data']      = json_encode($invoices);
      $return['skenario']['jurnal']    = json_encode($skenarioJurnal);
      return $return;
   }

   function ParseTemplate($data = null){
      $maxUploadedFiles    = $data['max_upload_filesize'];
      $attachment          = $data['files'];
      $invoices            = $data['invoices'];
      $skenarioJurnal      = $data['skenario'];
      $message             = $data['message'];
      $style               = $data['style'];
      $requestData         = $data['request_data'];
      $unitKerja           = $data['unit_kerja'];
      $urlUploadFile    = Dispatcher::Instance()->GetUrl(
         'finansi_transaksi_realisasi_penerimaan',
         'UploadFiles',
         'do',
         'json'
      );

      $urlDeleteFile    = Dispatcher::Instance()->GetUrl(
         'finansi_transaksi_realisasi_penerimaan',
         'DeleteFile',
         'do',
         'json'
      );

      $urlPopupUnit     = Dispatcher::Instance()->GetUrl(
         'finansi_transaksi_realisasi_penerimaan',
         'PopupUnitKerja',
         'view',
         'html'
      );

      $urlRencanaPenerimaan   = Dispatcher::Instance()->GetUrl(
         'finansi_transaksi_realisasi_penerimaan',
         'PopupRencanaPenerimaan',
         'view',
         'html'
      );

      $urlSkenarioJurnal      = Dispatcher::Instance()->GetUrl(
         'finansi_transaksi_realisasi_penerimaan',
         'PopupSkenarioJurnal',
         'view',
         'html'
      );

      $urlAction              = Dispatcher::Instance()->GetUrl(
         'finansi_transaksi_realisasi_penerimaan',
         'SaveTransaksi',
         'do',
         'json'
      );

      $this->mrTemplate->AddVar('data_unit', 'STATUS', strtoupper($unitKerja['status']));
      $this->mrTemplate->AddVar('data_unit', 'URL_POPUP_UNITKERJA', $urlPopupUnit);
      $this->mrTemplate->AddVar('data_unit', 'UNIT_ID', $requestData['unit_id']);
      $this->mrTemplate->AddVar('data_unit', 'UNIT_NAMA', $requestData['unit_nama']);
      $this->mrTemplate->AddVar('content', 'URL_UPLOAD', $urlUploadFile);
      $this->mrTemplate->AddVar('content', 'MAX_UPLOAD_FILESIZE', (int)$maxUploadedFiles);
      $this->mrTemplate->AddVar('content', 'URL_DELETE_FILE', $urlDeleteFile);
      $this->mrTemplate->AddVar('content', 'URL_ACTION', $urlAction);
      $this->mrTemplate->AddVars('content', $requestData);
      $this->mrTemplate->AddVars('content', $attachment, 'ATTACHMENT_');
      $this->mrTemplate->AddVars('content', $invoices, 'INVOICES_');
      $this->mrTemplate->AddVars('content', $skenarioJurnal, 'SKENARIO_');
      $this->mrTemplate->AddVar('content', 'URL_POPUP_RENCANA_PENERIMAAN', $urlRencanaPenerimaan);
      $this->mrTemplate->AddVar('content', 'URL_POPUP_SKENARIO_JURNAL', $urlSkenarioJurnal);
      $this->mrTemplate->AddVar('content', 'URL_ACTION', $urlAction);

      if($message){
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $message);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $style);
      }
   }
}
?>