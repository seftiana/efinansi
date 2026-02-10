<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/lap_realisasi_anggaran_program/business/AppLapRekapProgram.class.php';

class ViewCetakLapRekapProgram extends HtmlResponse
{
   function TemplateModule()
   {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') .
         'module/lap_realisasi_anggaran_program/template');
      $this->SetTemplateFile('view_cetak_lap_rekap_program.html');
   }

   function TemplateBase()
   {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-print.html');
      $this->SetTemplateFile('layout-common-print.html');
   }

   function ProcessRequest()
   {
      $mObj             = new AppLapRekapProgram();
      $requestData      = array();
      $requestData['ta_id']            = Dispatcher::Instance()->Decrypt($mObj->_GET['ta_id']);
      $requestData['unit_id']          = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_id']);
      $requestData['unit_nama']        = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_nama']);
      $requestData['program_id']       = Dispatcher::Instance()->Decrypt($mObj->_GET['program_id']);
      $requestData['program_nama']     = Dispatcher::Instance()->Decrypt($mObj->_GET['program_nama']);
      $requestData['jenis_kegiatan']   = Dispatcher::Instance()->Decrypt($mObj->_GET['jenis_kegiatan']);
      $requestData['bulan']            = Dispatcher::Instance()->Decrypt($mObj->_GET['bulan']);
      $requestData['ta_nama']          = Dispatcher::Instance()->Decrypt($mObj->_GET['ta_nama']);

      $dataUnit         = $mObj->GetUnitIdentity($requestData['unit_id']);
      $dataList         = $mObj->GetData(0, 100000, (array)$requestData);
      $total_data       = $mObj->GetCountData();
      $dataResume       = $mObj->GetDataResume((array)$requestData);
      $return['request_data']    = $requestData;
      $return['data_list']       = $mObj->ChangeKeyName($dataList);
      $return['data_resume']     = $mObj->ChangeKeyName($dataResume);
      $return['data_unit']       = $mObj->ChangeKeyName($dataUnit[0]);
      return $return;
   }

   function ParseTemplate($data = NULL)
   {
      $mObj          = new AppLapRekapProgram();
      $dataUnit      = $data['data_unit'];
      $requestData   = $data['request_data'];
      $dataList      = $data['data_list'];
      $dataResume    = $data['data_resume'];
      $requestData['program_nama']  = $requestData['program_id'] == '' ? 'Semua Program' : $requestData['program_nama'];
      $tanggalCetak  = $mObj->indonesianDate(date('Y-m-d', time()));
      $pimpinan      = empty($dataUnit['unitkerja_nama_pimpinan']) ? str_repeat('.', 50) : $dataUnit['unitkerjaNamaPimpinan'];
      $this->mrTemplate->AddVars('content', $requestData);
      $this->mrTemplate->AddVar('content', 'DATE', $tanggalCetak);
      $this->mrTemplate->AddVar('content', 'UNIT_KERJA', $requestData['unit_nama']);
      $this->mrTemplate->AddVar('content', 'NAMA_PIMPINAN', $pimpinan);

      if(empty($dataList)){
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
         $this->mrTemplate->AddVar('data_resume', 'DATA_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         $this->mrTemplate->AddVar('data_resume', 'DATA_EMPTY', 'NO');

         // inisialisasi data
         $program          = '';
         $kegiatan         = '';
         $dataGrid         = array();
         $dataMonitoring   = array();
         $index            = 0;

         for ($i=0; $i < count($dataList);) {
            if((int)$dataList[$i]['program_id'] === (int)$program && (int)$dataList[$i]['kegiatan_id'] === (int)$kegiatan){
               $programKodeSistem      = $program;
               $kegiatanKodeSistem     = $program.'.'.$kegiatan;

               $dataMonitoring[$programKodeSistem]['nominal_usulan']     += $dataList[$i]['nominal_usulan'];
               $dataMonitoring[$programKodeSistem]['nominal_setuju']     += $dataList[$i]['nominal_setuju'];
               $dataMonitoring[$programKodeSistem]['nominal_pencairan']  += $dataList[$i]['nominal_pencairan'];
               $dataMonitoring[$programKodeSistem]['nominal_realisasi']  += $dataList[$i]['nominal_realisasi'];
               $dataMonitoring[$programKodeSistem]['sisa_dana']          += $dataList[$i]['sisa_dana'];

               $dataMonitoring[$kegiatanKodeSistem]['nominal_usulan']     += $dataList[$i]['nominal_usulan'];
               $dataMonitoring[$kegiatanKodeSistem]['nominal_setuju']     += $dataList[$i]['nominal_setuju'];
               $dataMonitoring[$kegiatanKodeSistem]['nominal_pencairan']  += $dataList[$i]['nominal_pencairan'];
               $dataMonitoring[$kegiatanKodeSistem]['nominal_realisasi']  += $dataList[$i]['nominal_realisasi'];
               $dataMonitoring[$kegiatanKodeSistem]['sisa_dana']          += $dataList[$i]['sisa_dana'];

               $dataGrid[$index]['kode']        = $dataList[$i]['sub_kegiatan_kode'];
               $dataGrid[$index]['nama']        = $dataList[$i]['sub_kegiatan_nama'];
               $dataGrid[$index]['unit_kode']   = $dataList[$i]['unit_kode'];
               $dataGrid[$index]['unit_nama']   = $dataList[$i]['unit_nama'];
               $dataGrid[$index]['status']      = strtoupper($dataList[$i]['status']);
               $dataGrid[$index]['deskripsi']   = $dataList[$i]['deskripsi'];
               $dataGrid[$index]['nominal_usulan']    = $dataList[$i]['nominal_usulan'];
               $dataGrid[$index]['nominal_setuju']    = $dataList[$i]['nominal_setuju'];
               $dataGrid[$index]['nominal_pencairan'] = $dataList[$i]['nominal_pencairan'];
               $dataGrid[$index]['nominal_realisasi'] = $dataList[$i]['nominal_realisasi'];
               $dataGrid[$index]['sisa_dana']         = $dataList[$i]['sisa_dana'];
               $dataGrid[$index]['type']        = 'sub_kegiatan';
               $dataGrid[$index]['class_name']  = ($start % 2 <> 0) ? 'table-common-even' : '';
               $dataGrid[$index]['row_style']   = '';
               $i++;
               $start++;
            }elseif ((int)$dataList[$i]['program_id'] === (int)$program && (int)$dataList[$i]['kegiatan_id'] !== (int)$kegiatan) {
               $kegiatan      = (int)$dataList[$i]['kegiatan_id'];
               $kodeSistem    = $program.'.'.$kegiatan;
               $dataMonitoring[$kodeSistem]['nominal_usulan']     = 0;
               $dataMonitoring[$kodeSistem]['nominal_setuju']     = 0;
               $dataMonitoring[$kodeSistem]['nominal_pencairan']  = 0;
               $dataMonitoring[$kodeSistem]['nominal_realisasi']  = 0;
               $dataMonitoring[$kodeSistem]['sisa_dana']          = 0;

               $dataGrid[$index]['kode']        = $dataList[$i]['kegiatan_kode'];
               $dataGrid[$index]['nama']        = $dataList[$i]['kegiatan_nama'];
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['type']        = 'kegiatan';
               $dataGrid[$index]['class_name']  = 'table-common-even2';
               $dataGrid[$index]['row_style']   = 'background-color:#DCDCDC;';
            }else{
               $program       = (int)$dataList[$i]['program_id'];
               $kodeSistem    = $program;
               $dataMonitoring[$kodeSistem]['nominal_usulan']     = 0;
               $dataMonitoring[$kodeSistem]['nominal_setuju']     = 0;
               $dataMonitoring[$kodeSistem]['nominal_pencairan']  = 0;
               $dataMonitoring[$kodeSistem]['nominal_realisasi']  = 0;
               $dataMonitoring[$kodeSistem]['sisa_dana']          = 0;

               $dataGrid[$index]['kode']        = $dataList[$i]['program_kode'];
               $dataGrid[$index]['nama']        = $dataList[$i]['program_nama'];
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['type']        = 'program';
               $dataGrid[$index]['class_name']  = 'table-common-even1';
               $dataGrid[$index]['row_style']   = 'background-color:#CCCCCC; font-weight: bold;';
            }
            $index++;
         }

         foreach ($dataGrid as $list) {
            switch (strtoupper($list['type'])) {
               case 'PROGRAM':
                  $list['nominal_usulan']    = number_format($dataMonitoring[$list['kode_sistem']]['nominal_usulan'], 0, ',','.');
                  $list['nominal_setuju']    = number_format($dataMonitoring[$list['kode_sistem']]['nominal_setuju'], 0, ',','.');
                  $list['nominal_pencairan'] = number_format($dataMonitoring[$list['kode_sistem']]['nominal_pencairan'], 0, ',','.');
                  $list['nominal_realisasi'] = number_format($dataMonitoring[$list['kode_sistem']]['nominal_realisasi'], 0, ',','.');
                  $list['sisa_dana']         = number_format($dataMonitoring[$list['kode_sistem']]['sisa_dana'], 0, ',','.');
                  break;
               case 'KEGIATAN':
                  $list['nominal_usulan']    = number_format($dataMonitoring[$list['kode_sistem']]['nominal_usulan'], 0, ',','.');
                  $list['nominal_setuju']    = number_format($dataMonitoring[$list['kode_sistem']]['nominal_setuju'], 0, ',','.');
                  $list['nominal_pencairan'] = number_format($dataMonitoring[$list['kode_sistem']]['nominal_pencairan'], 0, ',','.');
                  $list['nominal_realisasi'] = number_format($dataMonitoring[$list['kode_sistem']]['nominal_realisasi'], 0, ',','.');
                  $list['sisa_dana']         = number_format($dataMonitoring[$list['kode_sistem']]['sisa_dana'], 0, ',','.');
                  break;
               default:
                  $list['nominal_usulan']    = number_format($list['nominal_usulan'], 0, ',','.');
                  $list['nominal_setuju']    = number_format($list['nominal_setuju'], 0, ',','.');
                  $list['nominal_pencairan'] = number_format($list['nominal_pencairan'], 0, ',','.');
                  $list['nominal_realisasi'] = number_format($list['nominal_realisasi'], 0, ',','.');
                  $list['sisa_dana']         = number_format($list['sisa_dana'], 0, ',','.');
                  break;
            }
            $this->mrTemplate->AddVars('data_list', $list);
            $this->mrTemplate->parseTemplate('data_list', 'a');
         }

         foreach ($dataResume as $resume) {
            $resume['nominal_usulan']     = number_format($resume['nominal_usulan'], 0, ',','.');
            $resume['nominal_setuju']     = number_format($resume['nominal_setuju'], 0, ',','.');
            $resume['nominal_pencairan']  = number_format($resume['nominal_pencairan'], 0, ',','.');
            $resume['nominal_realisasi']  = number_format($resume['nominal_realisasi'], 0, ',','.');
            $resume['sisa_dana']          = number_format($resume['sisa_dana'], 0, ',','.');
            $this->mrTemplate->AddVars('resume_item', $resume);
            $this->mrTemplate->parseTemplate('resume_item', 'a');
         }
      }
   }
}
?>