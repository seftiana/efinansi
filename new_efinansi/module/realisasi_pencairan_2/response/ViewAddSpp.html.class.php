<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot').
'module/realisasi_pencairan_2/business/spp.class.php';

class ViewAddSpp extends HtmlResponse
{
   function TemplateModule(){
      $this->setTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/realisasi_pencairan_2/template/');
      $this->setTemplateFile('view_add_spp.html');
   }

   function ProcessRequest(){
      $messenger           = Messenger::Instance()->Receive(__FILE__);
      $mObj                = new Spp();
      $queryString         = $mObj->_getQueryString();
      $arrSifatPembayaran  = $mObj->ComboSifatPembayaran();
      $arrJenisPembayaran  = $mObj->ComboJenisPembayaran();
      $periodeTahun        = $mObj->GetPeriodeTahun(array(
         'active' => true
      ));
      $startYear           = date('Y', strtotime($periodeTahun[0]['start']));
      $endYear             = date('Y', strtotime($periodeTahun[0]['end']));
      $requestData         = array();
      $realisasiId         = Dispatcher::Instance()->Decrypt($mObj->_GET['id']);
      $sppId               = Dispatcher::Instance()->Decrypt($mObj->_GET['spp_id']);
      $dataRealisasi       = $mObj->ChangeKeyName($mObj->GetDataPengajuanRealisasi($realisasiId));
      $dataRealisasiDet    = $mObj->ChangeKeyName($mObj->GetPengajuanRealisaiDetail($realisasiId));
      // set default data
      $requestData['spk_tanggal']      = date('Y-m-d', time());
      $requestData['ta_id']            = $dataRealisasi['ta_id'];
      $requestData['ta_nama']          = $dataRealisasi['ta_nama'];
      $requestData['unit_id']          = $dataRealisasi['unit_id'];
      $requestData['unit_nama']        = $dataRealisasi['unit_nama'];
      $requestData['realisasi_id']     = $dataRealisasi['id'];

      if($messenger){
         $messengerData       = $messenger[0][0];
         $messengerMsg        = $messenger[0][1];
         $messengerStyle      = $messenger[0][2];
         $tanggalSpkDay       = (int)$messengerData['spk_tanggal_day'];
         $tanggalSpkMon       = (int)$messengerData['spk_tanggal_mon'];
         $tanggalSpkYear      = (int)$messengerData['spk_tanggal_year'];

         $requestData['id']               = $messengerData['data_id'];
         $requestData['realisasi_id']     = $messengerData['realisasi_id'];
         $requestData['sifat_pembayaran'] = $messengerData['sifat_pembayaran'];
         $requestData['jenis_pembayaran'] = $messengerData['jenis_pembayaran'];
         $requestData['keperluan']        = trim($messengerData['keperluan']);
         $requestData['jenis_belanja']    = trim($messengerData['jenis_belanja']);
         $requestData['nama']             = trim($messengerData['nama']);
         $requestData['alamat']           = trim($messengerData['alamat']);
         $requestData['rekening']         = trim($messengerData['rekening']);
         $requestData['npwp']             = trim($messengerData['npwp']);
         $requestData['nomor_spk']        = trim($messengerData['nomor_spk']);
         $requestData['nominal_spk']      = $messengerData['nominal_spk'];
         $requestData['spk_tanggal']      = date('Y-m-d', mktime(0,0,0, $tanggalSpkMon, $tanggalSpkDay, $tanggalSpkYear));
      }

      Messenger::Instance()->SendToComponent(
         'combobox',
         'Combobox',
         'view',
         'html',
         'sifat_pembayaran',
         array(
            'sifat_pembayaran',
            $arrSifatPembayaran,
            $requestData['sifat_pembayaran'],
            false,
            ' style="width:175px;"'
         ), Messenger::CurrentRequest);

      Messenger::Instance()->SendToComponent(
         'combobox',
         'Combobox',
         'view',
         'html',
         'jenis_pembayaran',
         array(
            'jenis_pembayaran',
            $arrJenisPembayaran,
            $requestData['jenis_pembayaran'],
            false,
            ' style="width:175px;"'
         ), Messenger::CurrentRequest);

      # GTFW Tanggal
      Messenger::Instance()->SendToComponent(
         'tanggal',
         'Tanggal',
         'view',
         'html',
         'spk_tanggal',
         array(
            $requestData['spk_tanggal'],
            $startYear,
            $endYear,
            true,
            true,
            false
         ),
         Messenger::CurrentRequest
      );

      $return['query_string']    = $queryString;
      $return['request_data']    = $requestData;
      $return['message']         = $messengerMsg;
      $return['style']           = $messengerStyle;
      $return['data_list']       = $dataRealisasiDet;
      return $return;
   }

   function ParseTemplate($data = null){
      $queryString      = $data['query_string'];
      $requestData      = $data['request_data'];
      $message          = $data['message'];
      $style            = $data['style'];
      $dataList         = $data['data_list'];
      $urlReturn        = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'RealisasiPencairan',
         'view',
         'html'
      ).'&search=1&'.$queryString;
      $urlAction        = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'AddSpp',
         'do',
         'json'
      ).'&'.$queryString;

      $this->mrTemplate->AddVar('content', 'URL_RETURN', $urlReturn);
      $this->mrTemplate->AddVar('content', 'URL_ACTION', $urlAction);
      $this->mrTemplate->AddVars('content', $requestData);

      // set messenger message
      if($message){
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $message);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $style);
      }

      if(empty($dataList)){
         $this->mrTemplate->AddVar('data_spp', 'DATA_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('data_spp', 'DATA_EMPTY', 'NO');
         $dataGrid      = array();
         $nominalPagu   = 0;
         $sppIni        = 0;
         $sppLalu       = 0;
         $nominalSpp    = 0;
         $idPagu        = '';
         $index         = 0;
         for ($i=0; $i < count($dataList);) {
            if((int)$idPagu === (int)$dataList[$i]['pagu_id']){
               $nominalSpp       += $dataList[$i]['nominal'];
               $sppIni           += $dataList[$i]['nominal'];
               $dataGrid[$index]['realisasi_id']      = $dataList[$i]['id'];
               $dataGrid[$index]['mak_kode']          = $dataList[$i]['mak_kode'];
               $dataGrid[$index]['nominal_pagu']      = $nominalPagu;
               $dataGrid[$index]['spp_lalu']          = $dataList[$i]['spp_lalu']+($sppIni-$dataList[$i]['nominal']);
               $dataGrid[$index]['spp_ini']           = $dataList[$i]['nominal'];
               $dataGrid[$index]['nominal']           = $dataList[$i]['nominal'];
               $dataGrid[$index]['spp_total']         = $dataList[$i]['spp_lalu']+$sppIni;
               $dataGrid[$index]['sisa_dana']         = $nominalPagu-($dataList[$i]['spp_lalu']+$sppIni);
               $i++;
               $index++;
            }else{
               $idPagu        = (int)$dataList[$i]['pagu_id'];
               $nominalPagu   += $dataList[$i]['nominal_pagu'];
               unset($sppIni);
               unset($sppLalu);
               $sppIni        = 0;
               $sppLalu       = $dataList[$i]['spp_lalu'];
            }
         }

         $this->mrTemplate->AddVar('content', 'NOMINAL', number_format($nominalSpp, 2, ',','.'));
         foreach ($dataGrid as $list) {
            $list['nominal_pagu']      = number_format($list['nominal_pagu'], 2, ',','.');
            $list['spp_lalu']          = number_format($list['spp_lalu'], 2, ',','.');
            $list['spp_ini']           = number_format($list['spp_ini'], 2, ',','.');
            $list['spp_total']         = number_format($list['spp_total'], 2, ',','.');
            $list['sisa_dana']         = number_format($list['sisa_dana'], 2, ',','.');
            $this->mrTemplate->AddVars('data_list', $list);
            $this->mrTemplate->parseTemplate('data_list', 'a');
         }
      }
   }
}
?>