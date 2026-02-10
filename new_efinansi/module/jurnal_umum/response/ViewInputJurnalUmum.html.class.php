<?php
require_once GTFWConfiguration::GetValue('application','docroot').
'module/jurnal_umum/business/JurnalUmum.class.php';

class ViewInputJurnalUmum extends HtmlResponse
{
   public function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/jurnal_umum/template');
      $this->SetTemplateFile('view_input_jurnal_umum.html');
   }

   public function ProcessRequest() {
      $mObj             = new JurnalUmum();
      $autoApprove      = $mObj->getApplicationSetting('JURNAL_AUTO_APPROVE');
      $messenger        = Messenger::Instance()->Receive(__FILE__);
      $message          = $style = $messengerData  = NULL;
      $queryString      = $mObj->_getQueryString();
      $queryString      = ($queryString == '' OR $queryString === NULL) ? '' : '&'.$queryString;
      $queryReturn      = ($queryString == '' OR $queryString === NULL) ? '' : '&search=1'.$queryString;

      $tahunPencatatan  = $mObj->getTahunPencatatan();
      $minYear          = $tahunPencatatan['min_year'];
      $maxYear          = $tahunPencatatan['max_year'];
      $subAkunPatern    = $mObj->getPaternSubAccount();
      $patern           = $subAkunPatern['patern'];
      $regex            = $subAkunPatern['regex'];
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
      $requestData['tanggal']          = date('Y-m-d', time());

      if($messenger){
         $messengerData    = $messenger[0][0];
         $message          = $messenger[0][1];
         $style            = $messenger[0][2];

         $tanggalDay       = (int)$messengerData['referensi_tanggal_day'];
         $tanggalMon       = (int)$messengerData['referensi_tanggal_mon'];
         $tanggalYear      = (int)$messengerData['referensi_tanggal_year'];
         $requestData['tanggal']    = date('Y-m-d', mktime(0,0,0, $tanggalMon, $tanggalDay, $tanggalYear));
         $requestData['status']     = $messengerData['status_kas'];
         $requestData['bentuk_transaksi'] = $messengerData['bentuk_transaksi'];
         $requestData['keterangan']       = $messengerData['keterangan'];
         if($messengerData['debet'] && !empty($messengerData['debet'])){
            foreach ($messengerData['debet'] as $debet) {
               $jurnalDebet[$index]['id']    = $debet['id'];
               $jurnalDebet[$index]['kode']  = $debet['kode'];
               $jurnalDebet[$index]['nama']  = $debet['nama'];
               $jurnalDebet[$index]['sub_akun']    = $debet['subaccount'];
               $jurnalDebet[$index]['referensi']   = $debet['nomor_referensi'];
               $jurnalDebet[$index]['keterangan']  = $debet['keterangan'];
               $jurnalDebet[$index]['nominal']     = $debet['nominal'];
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
         'tanggal',
         'Tanggal',
         'view',
         'html',
         'referensi_tanggal',
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
      $return['request_data']    = $requestData;
      $return['message']         = $message;
      $return['style']           = $style;
      $return['auto_approve']    = $autoApprove;
      $return['query_return']    = $queryReturn;
      $return['akun_debet']['data']    = json_encode($jurnalDebet);
      $return['akun_kredit']['data']   = json_encode($jurnalKredit);
      return $return;
   }

   public function ParseTemplate($data = NULL) {
      $autoApprove      = $data['auto_approve'];
      $requestData      = $data['request_data'];
      $queryString      = $data['query_string'];
      $queryReturn      = $data['query_return'];
      $dataAkunDebet    = $data['akun_debet'];
      $dataAkunKredit   = $data['akun_kredit'];
      $message          = $data['message'];
      $style            = $data['style'];
      $urlAction        = Dispatcher::Instance()->GetUrl(
         'jurnal_umum',
         'AddJurnalUmum',
         'do',
         'json'
      ). $queryString;

      $urlReturn        = Dispatcher::Instance()->GetUrl(
         'jurnal_umum',
         'JurnalUmum',
         'view',
         'html'
      ).$queryReturn;

      $urlPopupCoa      = Dispatcher::Instance()->GetUrl(
         'jurnal_umum',
         'coa',
         'popup',
         'html'
      );

      $this->mrTemplate->AddVar('content', 'URL_ACTION', $urlAction);
      $this->mrTemplate->AddVar('content', 'URL_RETURN', $urlReturn);
      $this->mrTemplate->AddVars('content', $requestData);
      $this->mrTemplate->AddVar('content', 'POPUP_COA', $urlPopupCoa);
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