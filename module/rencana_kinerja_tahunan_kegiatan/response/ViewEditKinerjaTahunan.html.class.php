<?php
/**
* ================= doc ====================
* FILENAME     : ViewEditKinerjaTahunan.html.class.php
* @package     : ViewEditKinerjaTahunan
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-02-07
* @Modified    : 2015-02-07
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/rencana_kinerja_tahunan_kegiatan/business/RencanaKinerjaTahunan.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewEditKinerjaTahunan extends HtmlResponse
{
   private $_getCountDetailBelanja = 0;
   
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/rencana_kinerja_tahunan_kegiatan/template/');
      if($this->_getCountDetailBelanja == '0') {
         $this->SetTemplateFile('view_edit_kinerja_tahunan.html');
      } else {
        $this->SetTemplateFile('view_edit_kinerja_tahunan_prioritas_tanggal.html');
      }
   }

   function ProcessRequest(){
      $messenger        = Messenger::Instance()->Receive(__FILE__);
      $mObj             = new RencanaKinerjaTahunan();
      $userId           = $mObj->getUserId();
      $mUserUnitObj     = new UserUnitKerja();
      $unitKerja        = $mUserUnitObj->GetUnitKerjaRefUser($userId);
      $queryString      = $mObj->_getQueryString();
      $queryString      = ($queryString == '') ? '' : '&search=1&'.$queryString;
      $dates            = $mObj->getRangeYear();
      $minYear          = date('Y', strtotime($dates['start_date']));
      $maxYear          = date('Y', strtotime($dates['end_date']));
      $getdate          = getdate();
      $currMon          = (int)$getdate['mon'];
      $currYear         = (int)$getdate['year'];
      $arrPrioritas     = $mObj->GetComboPrioritas();
      $arrJenisRPC     	= $mObj->GetComboJenisRPC(); #add ccp 28-11-2019
      $message          = $stle = $messengerData = NULL;
      $dataId           = Dispatcher::Instance()->Decrypt($mObj->_GET['data_id']);
      $arrPeriodeTahun     = $mObj->GetTahunAnggaranInput();
      $periodeTahun        = $mObj->getPeriodeTahun(array('active' => true));
      $dataKegiatan     = $mObj->ChangeKeyName($mObj->getDataDetail($dataId));
      $this->_getCountDetailBelanja = $mObj->getCountDetailBelanja($dataId);
      //var_dump($this->_getCountDetailBelanja);
      // set default data
      $requestData['id']            = $dataKegiatan['id'];
      $requestData['keg_id']        = $dataKegiatan['keg_id'];
      $requestData['ta_id']         = $dataKegiatan['ta_id'];
      $requestData['ta_nama']         = $dataKegiatan['ta_nama'];
      $requestData['unit_id']       = $dataKegiatan['unit_id'];
      $requestData['unit_nama']     = $dataKegiatan['unit_nama'];
      $requestData['program_id']    = $dataKegiatan['program_id'];
      $requestData['program_nama']  = $dataKegiatan['program_nama'];
      $requestData['kegiatan_id']   = $dataKegiatan['kegiatan_id'];
      $requestData['kegiatan_nama'] = $dataKegiatan['kegiatan_nama'];
      $requestData['sub_kegiatan_id']     = $dataKegiatan['sub_kegiatan_id'];
      $requestData['sub_kegiatan_nama']   = $dataKegiatan['sub_kegiatan_nama'];
      $requestData['latar_belakang']      = $dataKegiatan['latar_belakang'];
      $requestData['indikator']           = $dataKegiatan['indikator'];
      $requestData['baseline']            = $dataKegiatan['base_line'];
      $requestData['final']               = $dataKegiatan['final'];
      $requestData['ikk_id']              = $dataKegiatan['ikk_id'];
      $requestData['ikk_nama']            = $dataKegiatan['ikk_nama'];
      $requestData['iku_id']              = $dataKegiatan['iku_id'];
      $requestData['iku_nama']            = $dataKegiatan['iku_nama'];
      $requestData['tupoksi_id']          = $dataKegiatan['tupoksi_id'];
      $requestData['tupoksi_nama']        = $dataKegiatan['tupoksi_nama'];
      $requestData['deskripsi']           = $dataKegiatan['deskripsi'];
      $requestData['catatan']             = $dataKegiatan['catatan'];
      $requestData['prioritas']           = $dataKegiatan['prioritas_id'];
      $requestData['jenis_rpc']           = $dataKegiatan['jenis_rpc']; #add ccp 28-11-2019
      $requestData['start_date']          = date('Y-m-d', strtotime($dataKegiatan['start_date']));
      $requestData['end_date']            = date('Y-m-t', strtotime($dataKegiatan['end_date']));
      $requestData['mastuk']              = $dataKegiatan['mas_tuk'];
      $requestData['mastk']               = $dataKegiatan['mas_tk'];
      $requestData['keltuk']              = $dataKegiatan['kel_tuk'];
      $requestData['keltk']               = $dataKegiatan['kel_tk'];
      $requestData['nama_pic']            = $dataKegiatan['name_pic'];

      if($messenger){
         $messengerData          = $messenger[0][0];
         $message                = $messenger[0][1];
         $style                  = $messenger[0][2];
         $startDateMon           = (int)$messengerData['start_date_mon'];
         $startDateYear          = (int)$messengerData['start_date_year'];
         $endDateMon             = (int)$messengerData['end_date_mon'];
         $endDateYear            = (int)$messengerData['end_date_year'];

         $requestData['id']            = $messengerData['data_id'];
         $requestData['keg_id']        = $messengerData['kegiatan_id'];
         $requestData['ta_id']         = $messengerData['tahun_anggaran'];
         $requestData['ta_nama']       = $messengerData['tahun_anggaran_nama'];
         $requestData['unit_id']       = $messengerData['unit_id'];
         $requestData['unit_nama']     = $messengerData['unit_nama'];
         $requestData['program_id']    = $messengerData['program'];
         $requestData['program_nama']  = $messengerData['program_nama'];
         $requestData['kegiatan_id']   = $messengerData['kegiatan'];
         $requestData['kegiatan_nama'] = $messengerData['kegiatan_nama'];
         $requestData['sub_kegiatan_id']     = $messengerData['sub_kegiatan'];
         $requestData['sub_kegiatan_nama']   = $messengerData['sub_kegiatan_nama'];
         $requestData['latar_belakang']      = $messengerData['latar_belakang'];
         $requestData['indikator']           = $messengerData['indikator'];
         $requestData['baseline']            = $messengerData['baseline'];
         $requestData['final']               = $messengerData['final'];
         $requestData['ikk_id']              = $messengerData['ikk_id'];
         $requestData['ikk_nama']            = $messengerData['ikk'];
         $requestData['iku_id']              = $messengerData['iku_id'];
         $requestData['iku_nama']            = $messengerData['iku'];
         $requestData['tupoksi_id']          = $messengerData['tupoksi_id'];
         $requestData['tupoksi_nama']        = $messengerData['tupoksi'];
         $requestData['deskripsi']           = $messengerData['deskripsi'];
         $requestData['catatan']             = $messengerData['catatan'];
         $requestData['prioritas']           = $messengerData['prioritas'];
	 $requestData['jenis_rpc']           = $messengerData['jenis_rpc']; #add ccp 28-11-2019
         $requestData['start_date']          = date('Y-m-d', mktime(0,0,0, $startDateMon, 1, $startDateYear));
         $requestData['end_date']            = date('Y-m-t', mktime(0,0,0, $endDateMon, 1, $endDateYear));
         $requestData['mastuk']              = $messengerData['mastuk'];
         $requestData['mastk']               = $messengerData['mastk'];
         $requestData['keltuk']              = $messengerData['keltuk'];
         $requestData['keltk']               = $messengerData['keltk'];
         $requestData['nama_pic']            = $messengerData['nama_pic'];
      }

      foreach ($arrPeriodeTahun as $ta) {
         if((int)$ta['id'] === (int)$requestData['ta_id']){
            $requestData['ta_nama']    = $ta['name'];
         }
      }
      # Combobox
      Messenger::Instance()->SendToComponent(
         'combobox',
         'Combobox',
         'view',
         'html',
         'tahun_anggaran',
         array(
            'tahun_anggaran',
            $arrPeriodeTahun,
            $requestData['ta_id'],
            false,
            'id="cmb_tahun_anggaran"'
         ),
         Messenger::CurrentRequest
      );

      # Combobox
      Messenger::Instance()->SendToComponent(
         'combobox',
         'Combobox',
         'view',
         'html',
         'prioritas',
         array(
            'prioritas',
            $arrPrioritas,
            $requestData['prioritas'],
            false,
            'id="cmb_prioritas"'
         ),
         Messenger::CurrentRequest
      );

      #add ccp 28-11-2019
      Messenger::Instance()->SendToComponent(
         'combobox',
         'Combobox',
         'view',
         'html',
         'jenis_rpc',
         array(
            'jenis_rpc',
            $arrJenisRPC,
            $requestData['jenis_rpc'],
            false,
            'id="cmb_jenis_rpc"'
         ),
         Messenger::CurrentRequest
      );

      # GTFW Tanggal
      Messenger::Instance()->SendToComponent(
         'tanggal',
         'Tanggal',
         'view',
         'html',
         'start_date',
         array(
            $requestData['start_date'],
            $minYear,
            $maxYear,
            false,
            false,
            true
         ),
         Messenger::CurrentRequest
      );

      Messenger::Instance()->SendToComponent(
         'tanggal',
         'Tanggal',
         'view',
         'html',
         'end_date',
         array(
            $requestData['end_date'],
            $minYear,
            $maxYear,
            false,
            false,
            true
         ),
         Messenger::CurrentRequest
      );

      $return['query_string']    = $queryString;
      $return['unit_kerja']      = $mObj->ChangeKeyName($unitKerja);
      $return['request_data']    = $requestData;
      $return['message']         = $message;
      $return['style']           = $style;
      return $return;
   }

   function ParseTemplate($data = null){
      $queryString   = $data['query_string'];
      $unitKerja     = $data['unit_kerja'];
      $requestData   = $data['request_data'];
      $message       = $data['message'];
      $style         = $data['style'];

      $urlReturn     = Dispatcher::Instance()->GetUrl(
         'rencana_kinerja_tahunan_kegiatan',
         'RencanaKinerjaTahunan',
         'view',
         'html'
      ).'&search=1&'.$queryString;
      $urlAction     = Dispatcher::Instance()->GetUrl(
         'rencana_kinerja_tahunan_kegiatan',
         'updateData',
         'do',
         'html'
      ).'&'.$queryString;

      $urlPopupUnit  = Dispatcher::Instance()->GetUrl(
         'rencana_kinerja_tahunan_kegiatan',
         'PopupUnitKerja',
         'view',
         'html'
      );

      $urlPopupSubKegiatan    = Dispatcher::Instance()->GetUrl(
         'rencana_kinerja_tahunan_kegiatan',
         'PopupSubKegiatan',
         'view',
         'html'
      );

      $urlPopupIkk            = Dispatcher::Instance()->GetUrl(
         'rencana_kinerja_tahunan_kegiatan',
         'PopupIkk',
         'view',
         'html'
       );
       $urlPopupIku           = Dispatcher::Instance()->GetUrl(
         'rencana_kinerja_tahunan_kegiatan',
         'PopupIku',
         'view',
         'html'
       );
       $urlPopupTupoksi       = Dispatcher::Instance()->GetUrl(
         'rencana_kinerja_tahunan_kegiatan',
         'PopupTupoksi',
         'view',
         'html'
       );

      $this->mrTemplate->AddVar('content', 'URL_RETURN', $urlReturn);
      $this->mrTemplate->AddVar('content', 'URL_ACTION', $urlAction);
      $this->mrTemplate->AddVar('content', 'URL_SUB_KEG', $urlPopupSubKegiatan);
      $this->mrTemplate->AddVar('content', 'URL_IKK', $urlPopupIkk);
      $this->mrTemplate->AddVar('content', 'URL_IKU', $urlPopupIku);
      $this->mrTemplate->AddVar('content', 'URL_TUPOKSI', $urlPopupTupoksi);
      $this->mrTemplate->AddVar('data_unit', 'LEVEL', strtoupper($unitKerja['status']));
      $this->mrTemplate->AddVar('data_unit', 'UNIT_ID', $requestData['unit_id']);
      $this->mrTemplate->AddVar('data_unit', 'UNIT_NAMA', $requestData['unit_nama']);
      $this->mrTemplate->AddVar('data_unit', 'URL_POPUP_UNIT', $urlPopupUnit);
      $this->mrTemplate->AddVars('content', $requestData);

      if($message){
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $message);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $style);
      }
   }
}
?>