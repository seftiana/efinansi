<?php
/**
* ================= doc ====================
* FILENAME     : ViewAddJurnalPengeluaran.html.class.php
* @package     : ViewAddJurnalPengeluaran
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-04-13
* @Modified    : 2015-04-13
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/finansi_jurnal_pengeluaran/business/JurnalPengeluaran.class.php';

class ViewAddJurnalPengeluaran extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/finansi_jurnal_pengeluaran/template/');
      $this->SetTemplateFile('view_add_jurnal_pengeluaran.html');
   }

   function ProcessRequest(){
      $mObj       = new JurnalPengeluaran();
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
      $subAccount       = empty($data['sub_account']) || is_null($data['sub_account']) ? array() :$data['sub_account'];
      $message          = $data['message'];
      $style            = $data['style'];
      $urlReferensiTransaksi     = Dispatcher::Instance()->GetUrl(
         'finansi_jurnal_pengeluaran',
         'ReferensiTransaksi',
         'view',
         'html'
      );

      $urlPopupCoa      = Dispatcher::Instance()->GetUrl(
         'finansi_jurnal_pengeluaran',
         'ReferensiCoa',
         'view',
         'html'
      );

      $urlAction        = Dispatcher::Instance()->GetUrl(
         'finansi_jurnal_pengeluaran',
         'SaveJurnalPengeluaran',
         'do',
         'json'
      ). $queryString;

      $urlList          = Dispatcher::Instance()->GetUrl(
         'finansi_jurnal_pengeluaran',
         'JurnalPengeluaran',
         'view',
         'html'
      ).$queryReturn;
/*
      if($requestData['trans_tt_id'] == '2'){
          $requestData['status_display'] ='style="display:block;"';
      } else {
          $requestData['status_display'] ='style="display:none;"';
      }
 * 
 */
      $requestData['status_display'] ='style="display:block;"';
      $this->mrTemplate->AddVar('content', 'URL_RETURN', $urlList);
      $this->mrTemplate->AddVar('content', 'POPUP_REFERENSI_TRANSAKSI', $urlReferensiTransaksi);
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