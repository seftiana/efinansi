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
'module/finansi_transaksi_pengeluaran_bank/business/TransaksiPengeluaranBank.class.php';

class ViewTransaksi extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/finansi_transaksi_pengeluaran_bank/template/');
      $this->SetTemplateFile('view_transaksi.html');
   }

   function ProcessRequest(){
      $mObj             = new TransaksiPengeluaranBank();
      $messenger        = Messenger::Instance()->Receive(__FILE__);
      $message          = $style = $messengerData  = NULL;
      //$tahunPencatatan = $mObj->getTahunPencatatan();   
      $tahunAnggaranYear = $mObj->getTahunAnggaranYear();
      //$minYear = $tahunPencatatan['min_year'];
      ///$maxYear = $tahunPencatatan['max_year'];      
      $minYear         = $tahunAnggaranYear['tahun_awal'];
      $maxYear         = $tahunAnggaranYear['tahun_khir'];
      
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
      $requestData['tanggal'] = date('Y-m-d', time());
      $requestData['tanggal_ymd'] = date('Y-m-d', time());
      $requestData['is_ref'] = 'Y';
      $requestData['is_display_bpkb']   = 'display:none';
      $requestData['is_display_no_ref'] = '';
      $requestData['is_readonly']       = '';
      $requestData['is_tgl_disabled']   = 'disabled';

      if($messenger){
         $messengerData    = $messenger[0][0];
         $message          = $messenger[0][1];
         $style            = $messenger[0][2];
         
            $requestData['tanggal_ymd']   = date('Y-m-d',  strtotime($messengerData['tanggal_ymd']));

            if($requestData['is_ref'] == 'Y') {
               $requestData['tanggal']    = date('Y-m-d', strtotime($messengerData['tanggal_ymd']));
            } else {
                $tanggalDay = (int) $messengerData['referensi_tanggal_day'];
                $tanggalMon = (int) $messengerData['referensi_tanggal_mon'];
                $tanggalYear = (int) $messengerData['referensi_tanggal_year'];
                $requestData['tanggal'] = date('Y-m-d', mktime(0, 0, 0, $tanggalMon, $tanggalDay, $tanggalYear));
            }
            

            $requestData['id']               = $messengerData['data_id'];
            $requestData['trans_tt_id']      = $messengerData['trans_tt_id'];
            $requestData['referensi_id']     = $messengerData['referensi_id'];
            $requestData['referensi_nama']   = $messengerData['referensi_nama'];
            $requestData['referensi_no']   = $messengerData['referensi_no'];
            $requestData['nominal']          = $messengerData['nominal'];
            $requestData['keterangan']       = $messengerData['keterangan'];
            $requestData['bpkb']             = trim($messengerData['bpkb']);
            $requestData['is_ref']           = $messengerData['is_ref'];
            $requestData['is_display_bpkb']  = $messengerData['is_display_bpkb'];
            $requestData['is_display_no_ref']  = $messengerData['is_display_no_ref'];
            $requestData['is_readonly']       = $messengerData['is_readonly'];
            $requestData['is_tgl_disabled']   = $messengerData['is_tgl_disabled'];
            $requestData['nama_penyetor']    = trim($messengerData['nama_penyetor']);//diterima dari
            $requestData['nama_penerima']    = trim($messengerData['nama_penerima']);// tujuan

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
        # GTFW Tanggal
        Messenger::Instance()->SendToComponent(
                'tanggal', 'Tanggal', 'view', 'html', 'referensi_tanggal', array(
            $requestData['tanggal'],
            $minYear,
            $maxYear,
            false,
            $requestData['is_tgl_disabled'],
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
         'SaveTransaksi',
         'do',
         'json'
      ). $queryString;

      $urlList          = Dispatcher::Instance()->GetUrl(
         'finansi_transaksi_pengeluaran_bank',
         'TransaksiPengeluaranBank',
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
        if($requestData['is_ref'] ==='Y') {
            $requestData['ref_ya'] = 'checked="checked"';
            $requestData['ref_tidak'] = '';
            $requestData['is_display_bpkb']   = 'display:none';
            $requestData['is_display_no_ref'] = '';
            $requestData['is_readonly']   = ''; // readonly
        } else {
            $requestData['ref_ya'] = '';
            $requestData['ref_tidak'] = 'checked="checked"';
            $requestData['is_display_bpkb']   = '';
            $requestData['is_display_no_ref'] = 'display:none';
            $requestData['is_readonly']   = '';
        }
        
      $requestData['status_display'] ='style="display:block;"';
      $this->mrTemplate->AddVar('content', 'URL_RETURN', $urlList);
      $this->mrTemplate->AddVar('content', 'POPUP_REFERENSI_TRANSAKSI', $urlReferensiTransaksi);
      $this->mrTemplate->AddVar('content', 'POPUP_COA', $urlPopupCoa);
      $this->mrTemplate->AddVar('content', 'URL_ACTION', $urlAction);
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