<?php
/**
* ================= doc ====================
* FILENAME     : ViewInputJurnalPenerimaanPembayaranMhs.html.class.php
* @package     : ViewInputJurnalPenerimaanPembayaranMhs
* scope        : PUBLIC
* @Author      : Cecep SP
* @Created     : 2026-01-26
* @Modified    : 2026-01-26
* @Analysts    : Cecep SP
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/jurnal_penerimaan_pembayaran/business/JurnalPenerimaanPembayaranMhs.class.php';

class ViewInputJurnalPenerimaanPembayaranMhs extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/jurnal_penerimaan_pembayaran/template/');
      $this->SetTemplateFile('view_input_jurnal_penerimaan_pembayaran_mhs.html');
   }

   function ProcessRequest(){
      $mObj       = new JurnalPenerimaanPembayaranMhs;
      $messenger  = Messenger::Instance()->Receive(__FILE__);
      $message          = $style = $messengerData  = NULL;
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

      if($messenger){
         $messengerData    = $messenger[0][0];
         $message          = $messenger[0][1];
         $style            = $messenger[0][2];

         $requestData['id']               = $messengerData['data_id'];
         $requestData['trans_tt_id']      = $messengerData['trans_tt_id'];
         $requestData['referensi_id']     = $messengerData['referensi_id'];
         $requestData['referensi_nama']   = $messengerData['referensi_nama'];
         $requestData['nominal']          = $messengerData['nominal'];
         $requestData['keterangan']       = $messengerData['keterangan'];
         $requestData['is_institute']     = $messengerData['is_institute'];

         if($messengerData['debet'] && !empty($messengerData['debet'])){
            foreach ($messengerData['debet'] as $debet) {
               $jurnalDebet[$index]['id']    = $debet['id'];
               $jurnalDebet[$index]['kode']  = $debet['kode'];
               $jurnalDebet[$index]['nama']  = $debet['nama'];
               $jurnalDebet[$index]['sub_akun']    = $debet['subaccount'];
               $jurnalDebet[$index]['referensi']   = $debet['nomor_referensi'];
               $jurnalDebet[$index]['keterangan']  = $debet['keterangan'];
               $jurnalDebet[$index]['nominal']     = $debet['nominal'];
               $jurnalDebet[$index]['is_coa_trans']     = $debet['is_coa_trans'];
               $index++;
            }
         }
      //   print_r($jurnalDebet);
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
               $jurnalKredit[$index]['is_coa_trans']= $kredit['is_coa_trans'];
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
      $return['sub_account']     = $mObj->getSubAccountCombo();
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
      $subAccount       = empty($data['sub_account']) || is_null($data['sub_account']) ? array() :$data['sub_account'];
      $urlReferensiTransaksi     = Dispatcher::Instance()->GetUrl(
         'jurnal_penerimaan_pembayaran',
         'ReferensiTransaksi',
         'Popup',
         'html'
      );

      $urlPopupCoa      = Dispatcher::Instance()->GetUrl(
         'jurnal_penerimaan_pembayaran',
         'Coa',
         'Popup',
         'html'
      );
	  
	  $urlReferensiPembayaranMahasiswa     = Dispatcher::Instance()->GetUrl(
         'jurnal_penerimaan_pembayaran',
         'ReferensiPembayaranMahasiswa',
         'Popup',
         'html'
      );

      $urlAction        = Dispatcher::Instance()->GetUrl(
         'jurnal_penerimaan_pembayaran',
         'AddJurnalPenerimaan',
         'do',
         'json'
      ). $queryString;

      $urlList          = Dispatcher::Instance()->GetUrl(
         'jurnal_penerimaan_pembayaran',
         'JurnalPenerimaanPembayaranMhs',
         'view',
         'html'
      ).$queryReturn;
      
      $requestData['status_display'] ='style="display:block;"';
      $this->mrTemplate->AddVar('content', 'URL_RETURN', $urlList);
      $this->mrTemplate->AddVar('content', 'POPUP_REFERENSI_TRANSAKSI', $urlReferensiTransaksi);
      $this->mrTemplate->AddVar('content', 'POPUP_REFERENSI_PEMBAYARAN_MAHASISWA', $urlReferensiPembayaranMahasiswa);
      $this->mrTemplate->AddVar('content', 'POPUP_COA', $urlPopupCoa);
      $this->mrTemplate->AddVar('content', 'URL_ACTION', $urlAction);
      $this->mrTemplate->AddVar('content', 'JSON_SUBACCOUNT', json_encode($subAccount));
      $this->mrTemplate->AddVars('content', $requestData);
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
   }
}
?>