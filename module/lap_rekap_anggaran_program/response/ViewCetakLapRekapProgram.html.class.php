<?php
/**
* ================= doc ====================
* FILENAME     : ViewCetakLapRekapProgram.html.class.php
* @package     : ViewCetakLapRekapProgram
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2014-07-18
* @Modified    : 2014-07-18
* @Analysts    : Dyah Fajar N
* @contact     : eko.susilo@gamatechno.com
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/lap_rekap_anggaran_program/business/AppLapRekapProgram.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') .
'main/function/date.php';

class ViewCetakLapRekapProgram extends HtmlResponse {
   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
         'module/lap_rekap_anggaran_program/template');
      $this->SetTemplateFile('view_cetak_lap_rekap_program.html');
   }

   function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-print.html');
      $this->SetTemplateFile('layout-common-print.html');
   }

   function ProcessRequest() {
      $mObj                = new AppLapRekapProgram();
      $requestData         = array();
      $arrTahunAnggaran    = $mObj->GetPeriodeTahun();
      $arrJenisKegiatan    = $mObj->GetComboJenisKegiatan();
      $requestData['ta_id']            = Dispatcher::Instance()->Decrypt($mObj->_GET['ta_id']);
      $requestData['unit_id']          = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_id']);
      $requestData['unit_nama']        = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_nama']);
      $requestData['program_id']       = Dispatcher::Instance()->Decrypt($mObj->_GET['program_id']);
      $requestData['program_nama']     = Dispatcher::Instance()->Decrypt($mObj->_GET['program_nama']);
      $requestData['jenis_kegiatan']   = Dispatcher::Instance()->Decrypt($mObj->_GET['jenis_kegiatan']);

      foreach ($arrTahunAnggaran as $ta) {
         if((int)$ta['id'] === (int)$requestData['ta_id']){
            $requestData['ta_nama']    = $ta['name'];
         }
      }

      foreach ($arrJenisKegiatan as $jenis) {
         if((int)$jenis['id'] === (int)$requestData['jenis_kegiatan']){
            $requestData['jenis_kegiatan_nama']    = $jenis['name'];
         }else{
            $requestData['jenis_kegiatan_nama']    = 'Semua '.GTFWConfiguration::GetValue('language', 'jenis_kegiatan');
         }
      }

      $requestData['program_nama']  = ($requestData['program_id'] == '') ? 'Semua '.GTFWConfiguration::GetValue('language', 'program') : $requestData['program_nama'];
      $offset              = 0;
      $limit               = 100000;
      $dataList            = $mObj->GetData($offset, $limit, (array)$requestData);
      $total_data          = $mObj->GetCountData();
      $dataResume          = $mObj->GetResume((array)$requestData);

      $return['request_data']    = $requestData;
      $return['data_list']       = $mObj->ChangeKeyName($dataList);
      $return['data_resume']     = $mObj->ChangeKeyName($dataResume);
      return $return;
   }

   function ParseTemplate($data = NULL) {
      $requestData      = $data['request_data'];
      $dataList         = $data['data_list'];
      $dataResume       = $data['data_resume'];
      if (empty($dataList)) {
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
         $this->mrTemplate->AddVar('resume', 'RESUME_EMPTY', 'YES');
      } else {
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         $this->mrTemplate->AddVar('resume', 'RESUME_EMPTY', 'NO');

         $this->mrTemplate->AddVar('content', 'TAHUN_ANGGARAN', $requestData['ta_nama']);
         $this->mrTemplate->AddVar('content', 'PROGRAM', $requestData['program_nama']);
         $this->mrTemplate->AddVar('content', 'UNITKERJA', $requestData['unit_nama']);
         $this->mrTemplate->AddVar('content', 'JENIS_KEGIATAN', $requestData['jenis_kegiatan_nama']);
         $this->mrTemplate->AddVar('content', 'UNIT_KERJA', $requestData['unit_nama']);

         $date       = date('d-m-Y');
         $date       = IndonesianDate($date,'dd-mm-yyyy');
         $this->mrTemplate->AddVar('content', 'DATE', $date);
         $pimpinan   = $data['unit_kerja']['unitkerjaNamaPimpinan'];

         if(!empty($pimpinan)){
            $this->mrTemplate->AddVar('content', 'NAMA_PIMPINAN', $pimpinan);
         }else{
            $this->mrTemplate->AddVar('content', 'NAMA_PIMPINAN', str_repeat('.', 50));
         }
         # inisialisasi data
         $program       = '';
         $kegiatan      = '';
         $index         = 0;
         $dataGrid      = array();
         $dataRekap     = array();
         for ($i=0; $i < count($dataList);) {
            if((int)$dataList[$i]['program_id'] === (int)$program && (int)$dataList[$i]['kegiatan_id'] === (int)$kegiatan){
               $programKodeSistem         = $program;
               $kegiatanKodeSistem        = $program.'.'.$kegiatan;
               $dataRekap[$programKodeSistem]['nominal_setuju']      += $dataList[$i]['nominal_setuju'];
               $dataRekap[$kegiatanKodeSistem]['nominal_setuju']     += $dataList[$i]['nominal_setuju'];

               $dataGrid[$index]['id']    = $dataList[$i]['sub_kegiatan_id'];
               $dataGrid[$index]['kode']  = $dataList[$i]['sub_kegiatan_kode'];
               $dataGrid[$index]['nama']  = $dataList[$i]['sub_kegiatan_nama'];
               $dataGrid[$index]['unit_nama']      = $dataList[$i]['unit_nama'];
               $dataGrid[$index]['nominal_setuju'] = $dataList[$i]['nominal_setuju'];
               $dataGrid[$index]['type']           = 'sub_kegiatan';
               $dataGrid[$index]['class_name']     = ($start % 2 <> 0) ? 'table-common-even' : '';
               $i++;
               $start++;
            }elseif((int)$dataList[$i]['program_id'] === (int)$program && (int)$dataList[$i]['kegiatan_id'] !== (int)$kegiatan){
               $kegiatan         = (int)$dataList[$i]['kegiatan_id'];
               $kodeSistem       = $program.'.'.$kegiatan;
               $dataRekap[$kodeSistem]['nominal_setuju']    = 0;
               $dataGrid[$index]['id']    = $dataList[$i]['kegiatan_id'];
               $dataGrid[$index]['kode']  = $dataList[$i]['kegiatan_kode'];
               $dataGrid[$index]['nama']  = $dataList[$i]['kegiatan_nama'];
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['type']        = 'kegiatan';
               $dataGrid[$index]['class_name']  = 'table-common-even2';
            }else{
               $program          = (int)$dataList[$i]['program_id'];
               $kodeSistem       = $program;
               $dataRekap[$kodeSistem]['nominal_setuju']    = 0;
               $dataGrid[$index]['id']    = $dataList[$i]['program_id'];
               $dataGrid[$index]['kode']  = $dataList[$i]['program_kode'];
               $dataGrid[$index]['nama']  = $dataList[$i]['program_nama'];
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['type']        = 'program';
               $dataGrid[$index]['class_name']  = 'table-common-even1';
               $dataGrid[$index]['row_style']   = 'font-weight: bold;';
            }
            $index++;
         }

         foreach ($dataGrid as $list) {
            switch (strtoupper($list['type'])) {
               case 'PROGRAM':
                  $list['nominal_setuju']    = number_format($dataRekap[$list['kode_sistem']]['nominal_setuju'], 0, ',', '.');
                  break;
               case 'KEGIATAN':
                  $list['nominal_setuju']    = number_format($dataRekap[$list['kode_sistem']]['nominal_setuju'], 0, ',', '.');
                  break;
               default:
                  $list['nominal_setuju']    = number_format($list['nominal_setuju'], 0, ',','.');
                  break;
            }
            $this->mrTemplate->AddVars('data_list', $list);
            $this->mrTemplate->parseTemplate('data_list', 'a');
         }

         foreach ($dataResume as $res) {
            $res['nominal_usulan']     = number_format($res['nominal_usulan'], 0, ',','.');
            $res['nominal_setuju']     = number_format($res['nominal_setuju'], 0, ',','.');
            $this->mrTemplate->AddVars('resume_item', $res);
            $this->mrTemplate->parseTemplate('resume_item', 'a');
         }
      }
   }
}
?>
