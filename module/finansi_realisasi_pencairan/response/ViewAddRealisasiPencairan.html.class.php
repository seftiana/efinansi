<?php
/**
* ================= doc ====================
* FILENAME     : ViewAddRealisasiPencairan.html.class.php
* @package     : ViewAddRealisasiPencairan
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-04-01
* @Modified    : 2015-04-01
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/finansi_realisasi_pencairan/business/RealisasiPencairan.class.php';

class ViewAddRealisasiPencairan extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/finansi_realisasi_pencairan/template/');
      $this->SetTemplateFile('view_add_realisasi_pencairan.html');
   }

   function ProcessRequest(){
      $messenger  = Messenger::Instance()->Receive(__FILE__);
      $mObj       = new RealisasiPencairan();
      $mUnitObj   = new UserUnitKerja();
      $userId     = $mObj->getUserId();
      $unitKerja  = $mUnitObj->GetUnitKerjaRefUser($userId);
      $messengerData       = $message = $style = NULL;
      $setDate             = $mObj->setDate();
      $arrJenisTransaksi   = $mObj->getJenisTransaksi();
      $arrTypeTransaksi    = $mObj->getTypeTransaksi();
      $maxUploadedFiles    = $mObj->returnBytes(ini_get('upload_max_filesize'));
      $minYear             = (int)$setDate['min_year'];
      $maxYear             = (int)$setDate['max_year'];
      $getdate             = getdate();
      $currDay             = (int)$getdate['mday'];
      $currMon             = (int)$getdate['mon'];
      $currYear            = (int)$getdate['year'];
      $mNumber             = new GenerateNumber();
      $requestData         = array();
      $fileAttach          = array();
      $invoices            = array();
      $skenarioJurnal      = array();
      $requestData['unit_id']    = $unitKerja['id'];
      $requestData['unit_nama']  = $unitKerja['nama'];
      $requestData['tanggal']    = date('Y-m-d', mktime(0,0,0, $currMon, $currDay,$currYear));
      $requestData['due_date']   = date('Y-m-d', mktime(0,0,0, $currMon, $currDay,$currYear));
      $requestData['jenis_transaksi_id']  = 5;
      $requestData['tipe_transaksi_id']   = 4;
      $requestData['penanggung_jawab']    = Security::Instance()->mAuthentication->GetCurrentUser()->GetRealName();
      $requestData['penerima']            = '';
      $requestData['skenario']            = 'manual';
      $requestData['skenario_label']      = 'manual';

      if($messenger){
         $messengerData    = $messenger[0][0];
         $message          = $messenger[0][1];
         $style            = $messenger[0][2];

         if(!is_null($messengerData)){
            $tanggalDay       = (int)$messengerData['tanggal_day'];
            $tanggalMon       = (int)$messengerData['tanggal_mon'];
            $tanggalYear      = (int)$messengerData['tanggal_year'];
            $dueDateDay       = (int)$messengerData['due_date_day'];
            $dueDateMon       = (int)$messengerData['due_date_mon'];
            $dueDateYear      = (int)$messengerData['due_date_year'];

            $requestData['id']                  = $messengerData['data_id'];
            $requestData['unit_id']             = $messengerData['unit_id'];
            $requestData['unit_nama']           = $messengerData['unit_nama'];
            $requestData['jenis_transaksi_id']  = $messengerData['jenis_transaksi'];
            $requestData['tipe_transaksi_id']   = $messengerData['tipe_transaksi'];
            $requestData['realisasi_id']        = $messengerData['realisasi_id'];
            $requestData['kegiatan_id']         = $messengerData['kegiatan_id'];
            $requestData['akun_id']             = $messengerData['akun_id'];
            $requestData['akun_nama']           = $messengerData['akun_nama'];
            $requestData['tanggal']             = date('Y-m-d', mktime(0,0,0, $tanggalMon, $tanggalDay, $tanggalYear));
            $requestData['due_date']            = date('Y-m-d', mktime(0,0,0, $dueDateMon, $dueDateDay, $dueDateYear));
            $requestData['no_invoice']          = $messengerData['no_invoice'];
            $requestData['nominal_approve']     = $messengerData['nominal_approve'];
            $requestData['nominal_realisasi']   = $messengerData['nominal_realisasi'];
            $requestData['nominal']             = $messengerData['nominal'];
            $requestData['keterangan']          = $messengerData['uraian'];
            $requestData['penanggung_jawab']    = $messengerData['penanggung_jawab'];
            $requestData['penerima']            = $messengerData['penerima'];
            $requestData['skenario']            = $messengerData['skenario'];
            $requestData['skenario_label']      = $messengerData['skenario_label'];

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

         if(!empty($messengerData['KOMP'])){
             $index = 0;
            foreach ($messengerData['KOMP'] as $komp) {
               $dataKomponen[$index]['pd_id']           = $komp['pd_id'];
               $dataKomponen[$index]['p_id']            = $komp['p_id'];
               $dataKomponen[$index]['kegdet_id']       = $komp['kegdet_id'];
               $dataKomponen[$index]['komp_kode']       = $komp['komp_kode'];
               $dataKomponen[$index]['komp_nama']       = $komp['komp_nama'];
               $dataKomponen[$index]['coa_kode']        = $komp['coa_kode'];
               $dataKomponen[$index]['deskripsi']       = $komp['deskripsi'];
               $dataKomponen[$index]['nominal']         = $komp['nominal'];
               $index++;
            }
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
            $requestData['jenis_transaksi_id'],
            false,
            'id="cmb_jenis_transaksi"'
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
            $arrTypeTransaksi,
            $requestData['tipe_transaksi_id'],
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

      $return['unit_kerja']            = $unitKerja;
      $return['max_upload_filesize']   = $maxUploadedFiles;
      $return['style']                 = $style;
      $return['message']               = $message;
      $return['request_data']          = $requestData;
      $return['data_komponen']['data'] = json_encode($dataKomponen);
      $return['files']['data']         = json_encode($fileAttach);
      $return['invoices']['data']      = json_encode($invoices);
      $return['skenario']['jurnal']    = json_encode($skenarioJurnal);
      return $return;
   }

   function ParseTemplate($data = null){
      $message             = $data['message'];
      $style               = $data['style'];
      $requestData         = $data['request_data'];
      $unitKerja           = $data['unit_kerja'];
      $maxUploadedFiles    = $data['max_upload_filesize'];
      $attachment          = $data['files'];
      $invoices            = $data['invoices'];
      $skenarioJurnal      = $data['skenario'];
      $dataKomponen        = $data['data_komponen'];
      $urlPopupUnit        = Dispatcher::Instance()->GetUrl(
         'finansi_realisasi_pencairan',
         'PopupUnitKerja',
         'view',
         'html'
      );

      $urlUploadFile    = Dispatcher::Instance()->GetUrl(
         'finansi_realisasi_pencairan',
         'UploadFiles',
         'do',
         'json'
      );

      $urlDeleteFile    = Dispatcher::Instance()->GetUrl(
         'finansi_realisasi_pencairan',
         'DeleteFile',
         'do',
         'json'
      );

      $urlPopupRealisasi   = Dispatcher::Instance()->GetUrl(
         'finansi_realisasi_pencairan',
         'PopupRealisasi',
         'view',
         'html'
      );

      $urlSkenarioJurnal      = Dispatcher::Instance()->GetUrl(
         'finansi_realisasi_pencairan',
         'PopupSkenarioJurnal',
         'view',
         'html'
      );

      $urlAction              = Dispatcher::Instance()->GetUrl(
         'finansi_realisasi_pencairan',
         'SaveTransaksi',
         'do',
         'json'
      );

      $this->mrTemplate->AddVar('content', 'URL_ACTION', $urlAction);
      $this->mrTemplate->AddVar('content', 'URL_UPLOAD', $urlUploadFile);
      $this->mrTemplate->AddVar('content', 'MAX_UPLOAD_FILESIZE', (int)$maxUploadedFiles);
      $this->mrTemplate->AddVar('content', 'URL_DELETE_FILE', $urlDeleteFile);
      $this->mrTemplate->AddVar('content', 'MAX_UPLOAD_FILESIZE', (int)$maxUploadedFiles);
      $this->mrTemplate->AddVar('content', 'URL_POPUP_REALISASI', $urlPopupRealisasi);
      $this->mrTemplate->AddVar('data_unit', 'LEVEL', strtoupper($unitKerja['status']));
      $this->mrTemplate->AddVar('data_unit', 'URL_POPUP_UNITKERJA', $urlPopupUnit);
      $this->mrTemplate->AddVar('data_unit', 'UNIT_ID', $requestData['unit_id']);
      $this->mrTemplate->AddVar('data_unit', 'UNIT_NAMA', $requestData['unit_nama']);
      $this->mrTemplate->AddVar('content', 'URL_POPUP_SKENARIO_JURNAL', $urlSkenarioJurnal);
      $this->mrTemplate->AddVars('content', $attachment, 'ATTACHMENT_');
      $this->mrTemplate->AddVars('content', $invoices, 'INVOICES_');
      $this->mrTemplate->AddVars('content', $skenarioJurnal, 'SKENARIO_');
      $this->mrTemplate->AddVars('content', $dataKomponen,'KOMP_');
      $this->mrTemplate->AddVars('content', $requestData);

      if($message){
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $message);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $style);
      }
   }
}
?>