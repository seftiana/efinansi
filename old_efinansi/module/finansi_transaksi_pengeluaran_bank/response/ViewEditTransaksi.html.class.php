<?php
/**
* ================= doc ====================
* FILENAME     : ViewEditJurnalPengeluaran.html.class.php
* @package     : ViewEditJurnalPengeluaran
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-04-14
* @Modified    : 2015-04-14
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'main/function/date.php';

require_once GTFWConfiguration::GetValue('application','docroot').
'module/finansi_transaksi_pengeluaran_bank/business/TransaksiPengeluaranBank.class.php';

class ViewEditTransaksi extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/finansi_transaksi_pengeluaran_bank/template/');
      $this->SetTemplateFile('view_edit_transaksi.html');
   }

   function ProcessRequest(){
      $mObj       = new TransaksiPengeluaranBank();
      $messenger  = Messenger::Instance()->Receive(__FILE__);
      $message          = $style = $messengerData  = NULL;
      $tahunPencatatan = $mObj->getTahunPencatatan();
        $minYear = $tahunPencatatan['min_year'];
        $maxYear = $tahunPencatatan['max_year'];
      $autoApprove      = $mObj->getApplicationSetting('JURNAL_AUTO_APPROVE');
      $subAkunPatern    = $mObj->getPaternSubAccount();
      $patern           = $subAkunPatern['patern'];
      $regex            = $subAkunPatern['regex'];
      $queryString      = $mObj->_getQueryString();
      $queryString      = ($queryString == '' OR $queryString === NULL) ? '' : '&'.$queryString;
      $queryReturn      = ($queryString == '' OR $queryString === NULL) ? '' : '&search=1'.$queryString;
      $arrStatus           = array(
         array('id'=>'Y','name'=>'Ya'),
         array('id'=>'T','name'=>'Tidak')
      );
      $arrBentukTransaksi  = $mObj->GetBentukTransaksi();
      $jurnalDebet         = array();
      $jurnalKredit        = array();
      $requestData         = array();
      $index               = 0;
      $requestData['sub_akun_patern']  = $patern;
      $referensiId         = Dispatcher::Instance()->Decrypt($mObj->_GET['data_id']);
      $pembukuanId         = Dispatcher::Instance()->Decrypt($mObj->_GET['pr_id']);
      $dataTransaksiBank   = $mObj->getTransaksiBankById($referensiId, $pembukuanId);
      $dataJurnal          = $mObj->getDataJurnalSubAkun($referensiId, $pembukuanId);
      $requestData['id']               = $dataTransaksiBank['id'];
      $requestData['referensi_nama']   = $dataTransaksiBank['nomor_referensi'];
      $requestData['referensi_no']     = $dataTransaksiBank['nomor_referensi'];
      $requestData['referensi_id']     = $dataTransaksiBank['sppu_id'];
      $requestData['nama_penyetor']    = $dataTransaksiBank['nama_penyetor'];
      $requestData['nama_penerima']    = $dataTransaksiBank['nama_penerima'];
      $requestData['tanggal']          = $dataTransaksiBank['tanggal'];
      $requestData['tanggal_ymd']          = $dataTransaksiBank['tanggal'];
      $requestData['tanggal_transaksi_bank'] = IndonesianDate($dataTransaksiBank['tanggal'],'YYYY-MM-DD');
      $requestData['nominal']          = $dataTransaksiBank['nominal'];
      $requestData['keterangan']       = $dataTransaksiBank['keterangan'];
      $requestData['pembukuan_id']     = $dataTransaksiBank['pembukuan_id'];

      $getCoaInTransaksi   = $mObj->getCoaKodeInTransaksi($requestData['referensi_id']);
      
      if($requestData['referensi_id'] === '0') {
          $requestData['is_ref']       = 'T';
          $requestData['is_readonly']  = '';
      } else {
          $requestData['is_ref']       = 'Y';
          $requestData['is_readonly']  = ''; // readonly
      }
      // generate data jurnal
      $debetIndex          = 0;
      $kreditIndex         = 0;
      foreach ($dataJurnal as $jurnal) {
         if(strtoupper($jurnal['status']) == 'D'){
            $jurnalDebet[$debetIndex]['id']     = $jurnal['id'];
            $jurnalDebet[$debetIndex]['kode']   = $jurnal['kode'];
            $jurnalDebet[$debetIndex]['nama']   = $jurnal['nama'];
            $jurnalDebet[$debetIndex]['sub_akun']     = $jurnal['sub_account'];
            $jurnalDebet[$debetIndex]['referensi']    = $jurnal['referensi'];
            $jurnalDebet[$debetIndex]['keterangan']   = $jurnal['keterangan'];
            $jurnalDebet[$debetIndex]['nominal']      = $jurnal['nominal'];
            $jurnalDebet[$debetIndex]['is_coa_trans'] = (in_array($jurnal['kode'], $getCoaInTransaksi) ? 1 : 0);
            $debetIndex+=1;
         }

         if(strtoupper($jurnal['status']) == 'K'){
            $jurnalKredit[$kreditIndex]['id']   = $jurnal['id'];
            $jurnalKredit[$kreditIndex]['kode'] = $jurnal['kode'];
            $jurnalKredit[$kreditIndex]['nama'] = $jurnal['nama'];
            $jurnalKredit[$kreditIndex]['sub_akun']   = $jurnal['sub_account'];
            $jurnalKredit[$kreditIndex]['referensi']  = $jurnal['referensi'];
            $jurnalKredit[$kreditIndex]['keterangan'] = $jurnal['keterangan'];
            $jurnalKredit[$kreditIndex]['nominal']    = $jurnal['nominal'];
            $kreditIndex+=1;
         }
      }

      if($messenger){
         $messengerData    = $messenger[0][0];
         $message          = $messenger[0][1];
         $style            = $messenger[0][2];

            $tanggalDay = (int) $messengerData['referensi_tanggal_day'];
            $tanggalMon = (int) $messengerData['referensi_tanggal_mon'];
            $tanggalYear = (int) $messengerData['referensi_tanggal_year'];
            $requestData['tanggal'] = date('Y-m-d', mktime(0, 0, 0, $tanggalMon, $tanggalDay, $tanggalYear));

         $requestData['id']               = $messengerData['data_id'];
         $requestData['trans_tt_id']      = $messengerData['trans_tt_id'];
         $requestData['pembukuan_id']     = $messengerData['pembukuan_referensi_id'];
         $requestData['referensi_id']     = $messengerData['referensi_id'];
         $requestData['referensi_nama']   = $messengerData['referensi_nama'];
         $requestData['referensi_no']   = $messengerData['referensi_no'];
         $requestData['nominal']          = $messengerData['nominal'];
         $requestData['keterangan']       = $messengerData['keterangan'];
         $requestData['is_ref']           = $messengerData['is_ref'];
         $requestData['is_readonly']      = $messengerData['is_readonly'];
                 
         if($messengerData['debet'] && !empty($messengerData['debet'])){
            foreach ($messengerData['debet'] as $debet) {
               $jurnalDebet[$index]['id']    = $debet['id'];
               $jurnalDebet[$index]['kode']  = $debet['kode'];
               $jurnalDebet[$index]['nama']  = $debet['nama'];
               $jurnalDebet[$index]['sub_akun']    = $debet['subaccount'];
               $jurnalDebet[$index]['referensi']   = $debet['nomor_referensi'];
               $jurnalDebet[$index]['keterangan']  = $debet['keterangan'];
               $jurnalDebet[$index]['nominal']     = $debet['nominal'];
               $jurnalDebet[$index]['is_coa_trans'] = $debet['is_coa_trans'];
               $index++;
            }
         }
         unset($index);
         $index      = 0;
         if($messengerData['kredit'] && !empty($messengerData['kredit'])){
            foreach ($messengerData['kredit'] as $kredit) {
               $jurnalKredit[$index]['id']    = $kredit['id'];
               $jurnalKredit[$index]['kode']  = $kredit['kode'];
               $jurnalKredit[$index]['nama']  = $kredit['nama'];
               $jurnalKredit[$index]['sub_akun']    = $kredit['subaccount'];
               $jurnalKredit[$index]['referensi']   = $kredit['nomor_referensi'];
               $jurnalKredit[$index]['keterangan']  = $kredit['keterangan'];
               $jurnalKredit[$index]['nominal']     = $kredit['nominal'];
               $index++;
            }
         }
      }

        # GTFW Tanggal
        Messenger::Instance()->SendToComponent(
                'tanggal', 'Tanggal', 'view', 'html', 'referensi_tanggal', array(
            $requestData['tanggal'],
            $minYear,
            $maxYear,
            false,
            false,
            false
                ), Messenger::CurrentRequest
        );
      # Combobox
      Messenger::Instance()->SendToComponent(
         'combobox',
         'Combobox',
         'view',
         'html',
         'status',
         array(
            'status_kas',
            $arrStatus,
            $requestData['status'],
            false,
            'id="cmb_status_kas"'
         ),
         Messenger::CurrentRequest
      );

      # Combobox
      Messenger::Instance()->SendToComponent(
         'combobox',
         'Combobox',
         'view',
         'html',
         'bentuk_transaksi',
         array(
            'bentuk_transaksi',
            $arrBentukTransaksi,
            $requestData['bentuk_transaksi'],
            false,
            'id="cmb_bentuk_transaksi"'
         ),
         Messenger::CurrentRequest
      );

      $return['query_string']    = $queryString;
      $return['query_return']    = $queryReturn;
      $return['request_data']    = $requestData;
      $return['request_data']    = $requestData;
      $return['message']         = $message;
      $return['style']           = $style;
      $return['auto_approve']    = $autoApprove;
      $return['akun_debet']['data']    = json_encode($jurnalDebet);
      $return['akun_kredit']['data']   = json_encode($jurnalKredit);
      return $return;
   }

   function ParseTemplate($data = null){
      $autoApprove      = $data['auto_approve'];
      $queryString      = $data['query_string'];
      $queryReturn      = $data['query_return'];
      $requestData      = $data['request_data'];
      $dataAkunDebet    = $data['akun_debet'];
      $dataAkunKredit   = $data['akun_kredit'];
      $message          = $data['message'];
      $style            = $data['style'];
      $urlReferensiTransaksi     = Dispatcher::Instance()->GetUrl(
         'finansi_transaksi_pengeluaran_bank',
         'PopupReferensiKomponen',
         'view',
         'html'
      );

      $urlPopupCoa      = Dispatcher::Instance()->GetUrl(
         'finansi_transaksi_pengeluaran_bank',
         'ReferensiCoa',
         'view',
         'html'
      );

      $urlAction        = Dispatcher::Instance()->GetUrl(
         'finansi_transaksi_pengeluaran_bank',
         'UpdateTransaksi',
         'do',
         'json'
      ). $queryString;

      $urlList          = Dispatcher::Instance()->GetUrl(
         'finansi_transaksi_pengeluaran_bank',
         'TransaksiPengeluaranBank',
         'view',
         'html'
      ).$queryReturn;
      
      /**
      if($requestData['trans_tt_id'] == '2'){
          $requestData['status_display'] ='style="display:block;"';
      } else {
          $requestData['status_display'] ='style="display:none;"';
      }
      */
      $requestData['status_display'] ='style="display:block;"';
      $this->mrTemplate->AddVar('content', 'URL_RETURN', $urlList);
      $this->mrTemplate->AddVar('content', 'POPUP_REFERENSI_TRANSAKSI', $urlReferensiTransaksi);
      $this->mrTemplate->AddVar('content', 'POPUP_COA', $urlPopupCoa);
      $this->mrTemplate->AddVar('content', 'URL_ACTION', $urlAction);
      $this->mrTemplate->AddVars('content', $requestData);
      $this->mrTemplate->AddVar('is_sppu','TANGGAL_TRANSAKSI_BANK', $requestData['tanggal_transaksi_bank']);
      $this->mrTemplate->AddVars('content', $dataAkunDebet, 'AKUN_DEBET_');
      $this->mrTemplate->AddVars('content', $dataAkunKredit, 'AKUN_KREDIT_');

      if($autoApprove !== NULL AND (bool)$autoApprove === TRUE){
         $this->mrTemplate->SetAttribute('approval', 'visibility', 'visible');
         $this->mrTemplate->SetAttribute('auto_approval', 'visibility', 'visible');
      }else{
         $this->mrTemplate->SetAttribute('approval', 'visibility', 'hidden');
      }

      if($message){
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $message);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $style);
      }

      if($requestData['referensi_id'] === '0') {
          $this->mrTemplate->AddVar('is_sppu', 'IS_SPPU', 'NO');
          $this->mrTemplate->SetAttribute('is_sppu_status', 'visibility', 'visible');
      } else {
          $this->mrTemplate->AddVar('is_sppu', 'IS_SPPU', 'YES');
          $this->mrTemplate->SetAttribute('is_sppu_status', 'visibility', 'hidden');
      }      
   }
}
?>