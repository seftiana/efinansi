<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/rencana_penerimaan/business/AppRencanaPenerimaan.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewInputRencanaPenerimaan extends HtmlResponse
{
   function TemplateModule()
   {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') .
         'module/rencana_penerimaan/template');
      $this->SetTemplateFile('input_rencana_penerimaan.html');
   }

   function ProcessRequest()
   {
      $messenger     = Messenger::Instance()->Receive(__FILE__);
      $message       = $style = $messengerData = NULL;
      $mObj          = new AppRencanaPenerimaan();
      $userId        = $mObj->getUserId();
      $mUnitObj      = new UserUnitKerja();
      $unitKerja     = $mUnitObj->GetUnitKerjaRefUser($userId);
      $queryString   = $mObj->_getQueryString();
      $requestData   = array();
      $arrPeriodeTahun  = $mObj->getPeriodeTahunAktifOpen();
      $periodeTahun     = $mObj->getPeriodeTahun(array('active' => true));
      $months           = range(1, 12);
      $indonesianMonths = $mObj->indonesianMonth;
      $dataRincianPenerimaan  = array();
      $index            = 0;
      $rincianReq       = array();

      $requestData['ta_id']      = $periodeTahun[0]['id'];
      $requestData['unit_id']    = $unitKerja['id'];
      $requestData['unit_nama']  = $unitKerja['nama'];
      $requestData['pagu']       = 100;
      $requestData['btn_toogle_state']   = 'show';

      if($messenger){
         $message       = $messenger[0][1];
         $style         = $messenger[0][2];
         $messengerData = $messenger[0][0];

         $requestData['id']                  = $messengerData['data_id'];
         $requestData['ta_id']               = $messengerData['tahun_anggaran'];
         $requestData['unit_id']             = $messengerData['unit_id'];
         $requestData['unit_nama']           = $messengerData['unit_nama'];
         $requestData['alokasi_pusat_id']    = $messengerData['alokasi_pusat_id'];
         $requestData['alokasi_pusat']       = $messengerData['alokasi_pusat'];
         $requestData['alokasi_unit_id']     = $messengerData['alokasi_unit_id'];
         $requestData['alokasi_unit']        = $messengerData['alokasi_unit'];
         $requestData['kodepenerimaan_id']   = $messengerData['kodepenerimaan_id'];
         $requestData['kodepenerimaan_kode'] = $messengerData['kodepenerimaan_kode'];
         $requestData['kodepenerimaan_nama'] = $messengerData['kodepenerimaan_nama'];
         $requestData['volume']              = $messengerData['volume'];
         $requestData['satuan']              = $messengerData['satuan'];
         $requestData['tarif']               = $messengerData['tarif'];
         $requestData['nominal']             = $messengerData['totalterima'];
         $requestData['pagu']                = $messengerData['pagu'];
         $requestData['nominal_pagu']        = $messengerData['totalpagu'];
         $requestData['keterangan']          = $messengerData['keterangan'];
         $requestData['sumber_dana_id']      = $messengerData['sumber_dana'];
         $requestData['sumber_dana_nama']    = $messengerData['sumber_dana_label'];
         $requestData['nominal_total']       = $messengerData['totalpenerimaan'];
         $requestData['btn_toogle_state']    = $messengerData['btn_toogle_state'];
         if(!empty($messengerData['rincian'])){
            $rincianReq = $messengerData['rincian'];
         }
      }

      
      foreach ($months as $mon) {
         $dataRincianPenerimaan[$index]['id']      = $indonesianMonths[$mon]['id'];
         $dataRincianPenerimaan[$index]['name']    = $indonesianMonths[$mon]['name'];
         $dataRincianPenerimaan[$index]['persen']  = 0;
         $dataRincianPenerimaan[$index]['nominal'] = 0;

         if(!empty($rincianReq)){
            $dataRincianPenerimaan[$index]['persen']  = $rincianReq[$indonesianMonths[$mon]['id']]['persen'];
            $dataRincianPenerimaan[$index]['nominal'] = $rincianReq[$indonesianMonths[$mon]['id']]['nominal'];
         }
                 
         $index++;
      }

      

      foreach ($arrPeriodeTahun as $ta) {
         if((int)$ta['id'] === (int)$requestData['ta_id']){
            $requestData['ta_nama'] = $ta['name'];
         }
      }

      # Combobox
      Messenger::Instance()->SendToComponent(
         'combobox',
         'Combobox',
         'view',
         'html',
         'periode_tahun',
         array(
            'tahun_anggaran',
            $arrPeriodeTahun,
            $requestData['ta_id'],
            false,
            'id="cmb_periode_tahun" style="width: 115px;"'
         ),
         Messenger::CurrentRequest
      );

      $return['unit_kerja']      = $unitKerja;
      $return['query_string']    = $queryString;
      $return['message']         = $message;
      $return['style']           = $style;
      $return['request_data']    = $requestData;
      $return['rincian_penerimaan']['data']     = json_encode($dataRincianPenerimaan);

      return $return;
   }

   function ParseTemplate($data = NULL)
   {
      $requestData            = $data['request_data'];
      $queryString            = $data['query_string'];
      $message                = $data['message'];
      $style                  = $data['style'];
      $dataRincianPenerimaan  = $data['rincian_penerimaan'];
      $unitKerja              = $data['unit_kerja'];
      $urlReturn           = Dispatcher::Instance()->GetUrl(
         'rencana_penerimaan',
         'RencanaPenerimaan',
         'view',
         'html'
      ).'&search=1&'.$queryString;

      $urlAction           = Dispatcher::Instance()->GetUrl(
         'rencana_penerimaan',
         'AddRencanaPenerimaan',
         'do',
         'json'
      ).'&'.$queryString;

      $urlPopupUnit        = Dispatcher::Instance()->GetUrl(
         'rencana_penerimaan',
         'popupUnitKerja',
         'view',
         'html'
      );

      $urlKodePenerimaan   = Dispatcher::Instance()->GetUrl(
         'rencana_penerimaan',
         'popupKodePenerimaan',
         'view',
         'html'
      );

      $urlSumberDana       = Dispatcher::Instance()->GetUrl(
         'rencana_penerimaan',
         'popupSumberDana',
         'view',
         'html'
      );

      if($message){
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $message);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $style);
      }

      $this->mrTemplate->AddVar('content', 'URL_RETURN', $urlReturn);
      $this->mrTemplate->AddVar('content', 'URL_ACTION', $urlAction);
      $this->mrTemplate->AddVars('content', $dataRincianPenerimaan, 'RINCIAN_PENERIMAAN_');
      $this->mrTemplate->AddVar('data_unit', 'LEVEL', strtoupper($unitKerja['status']));
      $this->mrTemplate->AddVar('data_unit', 'URL_POPUP_UNIT', $urlPopupUnit);
      $this->mrTemplate->AddVar('data_unit', 'UNIT_ID', $requestData['unit_id']);
      $this->mrTemplate->AddVar('data_unit', 'UNIT_NAMA', $requestData['unit_nama']);

      $this->mrTemplate->AddVars('content', $requestData);
      $this->mrTemplate->AddVar('content', 'URL_POPUP_KODEPENERIMAAN', $urlKodePenerimaan);
      $this->mrTemplate->AddVar('content', 'URL_POPUP_SUMBER_DANA', $urlSumberDana);
   }
}
?>