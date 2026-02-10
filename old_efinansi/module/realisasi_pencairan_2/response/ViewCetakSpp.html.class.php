<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot').
'module/realisasi_pencairan_2/business/spp.class.php';
require_once GTFWConfiguration::GetValue('application','docroot').
'main/function/terbilang.php';

class ViewCetakSpp extends HtmlResponse
{
   function TemplateModule(){
      $this->setTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/realisasi_pencairan_2/template/');
      $this->setTemplateFile('view_cetak_spp.html');
   }
   function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-print.html');
      $this->SetTemplateFile('layout-common-print.html');
   }
   function ProcessRequest(){
      $mObj                = new Spp();
      $queryString         = $mObj->_getQueryString();
      $realisasiId         = Dispatcher::Instance()->Decrypt($mObj->_GET['id']);
      $sppId               = Dispatcher::Instance()->Decrypt($mObj->_GET['spp_id']);
      $dataRealisasi       = $mObj->ChangeKeyName($mObj->GetDataPengajuanRealisasi($realisasiId));
      $dataSpp             = $mObj->ChangeKeyName($mObj->GetDataSpp($sppId));
      $dataRealisasiDet    = $mObj->ChangeKeyName($mObj->GetPengajuanRealisaiDetail($realisasiId));
      $dataSpp             = array_merge((array)$dataRealisasi, (array)$dataSpp);
      $dataSpp['tanggal_string'] = $mObj->indonesianDate(date('Y-m-d', strtotime($dataSpp['tanggal'])));
      $dataSpp['tanggal_cetak']  = $mObj->indonesianDate(date('Y-m-d', time()));
      $dataSpp['dipa_tahun']     = date('Y', strtotime($dataSpp['dipa_tanggal']));
      $dataSpp['spk']            = ($dataSpp['spk_nomor'] == '') ? '-' : $dataSpp['spk_nomor'].'/'.date('d/m/Y', strtotime($dataSpp['spk_tanggal']));
      $dataSpp['spk_nominal']    = ($dataSpp['spk_nomor'] == '') ? '-' : number_format($dataSpp['spk_nominal'], 0, ',','.');

      $return['data_spp']        = $dataSpp;
      $return['data_list']       = $dataRealisasiDet;
      return $return;
   }

   function ParseTemplate($data = null){
      $number           = new Number();
      $dataSpp          = $data['data_spp'];
      $dataList         = $data['data_list'];
      $userName         = Security::Instance()->mAuthentication->GetCurrentUser()->GetRealName();
      $this->mrTemplate->AddVars('content', $dataSpp);
      $this->mrTemplate->AddVar('content','TIMESTAMP',date('Y/m/d H:i:s', time()));
      $this->mrTemplate->AddVar('content', 'USERNAME', $userName);

      if(empty($dataList)){
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         $dataGrid      = array();
         $nominalPagu   = 0;
         $sppIni        = 0;
         $sppLalu       = 0;
         $nominalSpp    = 0;
         $idPagu        = '';
         $index         = 0;
         $nomor         = 1;

         $totalPagu     = 0;
         $totalSppLalu  = 0;
         $totalSppIni   = 0;
         $totalSisaDana = 0;
         $sppTotal      = 0;
         for ($i=0; $i < count($dataList);) {
            if((int)$idPagu === (int)$dataList[$i]['pagu_id']){
               $nominalSpp       += $dataList[$i]['nominal'];
               $sppIni           += $dataList[$i]['nominal'];

               $totalPagu        += $nominalPagu;
               $totalSppLalu     += $dataList[$i]['spp_lalu']+($sppIni-$dataList[$i]['nominal']);
               $totalSppIni      += $dataList[$i]['nominal'];
               $sppTotal         += $dataList[$i]['spp_lalu']+$sppIni;
               $totalSisaDana    += $nominalPagu-($dataList[$i]['spp_lalu']+$sppIni);

               $dataGrid[$index]['realisasi_id']      = $dataList[$i]['id'];
               $dataGrid[$index]['nomor']             = $nomor;
               $dataGrid[$index]['mak_kode']          = $dataList[$i]['mak_kode'];
               $dataGrid[$index]['kode']              = $dataList[$i]['program_kode'].'/'.$dataList[$i]['kegiatan_kode'].'/'.$dataList[$i]['sub_kegiatan_kode'];
               $dataGrid[$index]['nominal_pagu']      = $nominalPagu;
               $dataGrid[$index]['spp_lalu']          = $dataList[$i]['spp_lalu']+($sppIni-$dataList[$i]['nominal']);
               $dataGrid[$index]['spp_ini']           = $dataList[$i]['nominal'];
               $dataGrid[$index]['nominal']           = $dataList[$i]['nominal'];
               $dataGrid[$index]['spp_total']         = $dataList[$i]['spp_lalu']+$sppIni;
               $dataGrid[$index]['sisa_dana']         = $nominalPagu-($dataList[$i]['spp_lalu']+$sppIni);
               $i++;
               $index++;
               $nomor++;
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
         $this->mrTemplate->AddVar('content', 'TERBILANG', $number->Terbilang($nominalSpp, 3));
         foreach ($dataGrid as $list) {
            $list['nominal_pagu']      = number_format($list['nominal_pagu'], 2, ',','.');
            $list['spp_lalu']          = number_format($list['spp_lalu'], 2, ',','.');
            $list['spp_ini']           = number_format($list['spp_ini'], 2, ',','.');
            $list['spp_total']         = number_format($list['spp_total'], 2, ',','.');
            $list['sisa_dana']         = number_format($list['sisa_dana'], 2, ',','.');
            $this->mrTemplate->AddVars('data_list', $list);
            $this->mrTemplate->parseTemplate('data_list', 'a');
         }
         $this->mrTemplate->AddVar('data_grid', 'PROGRAM_KODE', $dataSpp['program_kode']);
         $this->mrTemplate->AddVar('data_grid', 'KEGIATAN_KODE', $dataSpp['kegiatan_kode']);
         $this->mrTemplate->AddVar('data_grid', 'TOTAL_PAGU', number_format($totalPagu, 2, ',','.'));
         $this->mrTemplate->AddVar('data_grid', 'TOTAL_SPP_LALU', number_format($totalSppLalu, 2, ',','.'));
         $this->mrTemplate->AddVar('data_grid', 'TOTAL_SPP_INI', number_format($totalSppIni, 2, ',','.'));
         $this->mrTemplate->AddVar('data_grid', 'TOTAL_SPP', number_format($sppTotal, 2, ',','.'));
         $this->mrTemplate->AddVar('data_grid', 'TOTAL_SISA_DANA', number_format($totalSisaDana, 2, ',','.'));
      }
   }
}
?>