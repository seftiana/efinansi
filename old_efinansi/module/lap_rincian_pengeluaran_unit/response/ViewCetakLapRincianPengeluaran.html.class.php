<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/lap_rincian_pengeluaran_unit/business/AppLapRincianPengeluaran.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewCetakLapRincianPengeluaran extends HtmlResponse {

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
         'module/lap_rincian_pengeluaran_unit/template');
      $this->SetTemplateFile('cetak_lap_rincian_pengeluaran.html');
   }

   function TemplateBase(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-print.html');
      $this->SetTemplateFile('layout-common-print.html');
   }

   function ProcessRequest(){
      $mObj       = new AppLapRincianPengeluaran();
      $mUnitObj   = new UserUnitKerja();
      $userid     = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
      $arrPeriodeTahun     = $mObj->GetPeriodeTahun();
      $periodeTahun        = $mObj->GetPeriodeTahun(array('active' => true));
      $dataUnit            = $mUnitObj->GetUnitKerjaRefUser($userid);
      $requestData         = array();

      $requestData['ta_id']      = Dispatcher::Instance()->Decrypt($mObj->_GET['ta_id']);
      $requestData['unit_id']    = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_id']);
      $requestData['unit_nama']  = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_nama']);

      foreach ($arrPeriodeTahun as $ta) {
         if((int)$ta['id'] === (int)$requestData['ta_id']){
            $requestData['ta_nama'] = $ta['name'];
         }
      }

      $dataList      = $mObj->GetData(0, 100000, (array)$requestData);
      $totalData     = $mObj->Count();
      $unitkerja     = $mObj->GetUnitKerja($requestData['unit_id']);

      $return['data_list']    = $mObj->ChangeKeyName($dataList);
      $return['data_unit']    = $unitkerja;
      $return['request_data'] = $requestData;
      return $return;
   }

   function ParseTemplate($data = NULL) {
      $dataList         = $data['data_list'];
      $dataUnit         = $data['data_unit'];
      $requestData      = $data['request_data'];

      $this->mrTemplate->AddVar('content', 'TAHUN_PERIODE', $requestData['ta_nama']);
      $this->mrTemplate->AddVar('content', 'UNIT_KERJA_LABEL', $dataUnit['unit_kerja_nama']);

      if (empty($dataList)) {
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      } else {
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         // inisialisasi data
         $program       = '';
         $kegiatan      = '';
         $subkegiatan   = '';
         $kegdet        = '';
         $makId         = '';
         $index         = 0;
         $dataGrid      = array();
         $dataRincian   = array();
         $start         = 1;
         for ($i=0; $i < count($dataList);) {
            if((int)$dataList[$i]['program_id'] === (int)$program
               && (int)$dataList[$i]['kegiatan_id'] === (int)$kegiatan
               && (int)$subkegiatan === (int)$dataList[$i]['sub_kegiatan_id']
               && (int)$kegdet === (int)$dataList[$i]['kegdet_id']
               && (int)$makId === (int)$dataList[$i]['mak_id']){
               // kode sistem
               $kodeSistemProgram         = $program;
               $kodeSistemKegiatan        = $program.'.'.$kegiatan;
               $kodeSistemSubKegiatan     = $program.'.'.$kegiatan.'.'.$subkegiatan;
               $kodeSistemKegdet          = $program.'.'.$kegiatan.'.'.$subkegiatan.'.'.$kegdet;
               $kodeSistemMak             = $program.'.'.$kegiatan.'.'.$subkegiatan.'.'.$kegdet.'.'.$makId;

               $dataRincian[$kodeSistemProgram]['nominal_satuan']       += $dataList[$i]['setuju_nominal'];
               $dataRincian[$kodeSistemKegiatan]['nominal_satuan']      += $dataList[$i]['setuju_nominal'];
               $dataRincian[$kodeSistemSubKegiatan]['nominal_satuan']   += $dataList[$i]['setuju_nominal'];
               $dataRincian[$kodeSistemKegdet]['nominal_satuan']        += $dataList[$i]['setuju_nominal'];
               $dataRincian[$kodeSistemMak]['nominal_satuan']           += $dataList[$i]['setuju_nominal'];

               $dataRincian[$kodeSistemProgram]['nominal_total']       += $dataList[$i]['setuju_jumlah'];
               $dataRincian[$kodeSistemKegiatan]['nominal_total']      += $dataList[$i]['setuju_jumlah'];
               $dataRincian[$kodeSistemSubKegiatan]['nominal_total']   += $dataList[$i]['setuju_jumlah'];
               $dataRincian[$kodeSistemKegdet]['nominal_total']        += $dataList[$i]['setuju_jumlah'];
               $dataRincian[$kodeSistemMak]['nominal_total']           += $dataList[$i]['setuju_jumlah'];

               $dataGrid[$index]['kode']        = $dataList[$i]['komp_kode'];
               $dataGrid[$index]['nama']        = $dataList[$i]['komp_nama'];
               $dataGrid[$index]['unit_nama']   = $dataList[$i]['unit_nama'];
               $dataGrid[$index]['nomor']       = $start;
               $dataGrid[$index]['tipe']        = 'komponen';
               $dataGrid[$index]['volume']      = $dataList[$i]['volume'];
               $dataGrid[$index]['nominal_satuan']    = $dataList[$i]['setuju_nominal'];
               $dataGrid[$index]['nominal_total']     = $dataList[$i]['setuju_jumlah'];
               $i++;
               $start++;
            }elseif((int)$dataList[$i]['program_id'] === (int)$program
               && (int)$dataList[$i]['kegiatan_id'] === (int)$kegiatan
               && (int)$subkegiatan === (int)$dataList[$i]['sub_kegiatan_id']
               && (int)$kegdet === (int)$dataList[$i]['kegdet_id']
               && (int)$makId !== (int)$dataList[$i]['mak_id']){
               $makId            = (int)$dataList[$i]['mak_id'];
               $kodeSistem       = $program.'.'.$kegiatan.'.'.$subkegiatan.'.'.$kegdet.'.'.$makId;
               $dataRincian[$kodeSistem]['nominal_satuan']  = 0;
               $dataRincian[$kodeSistem]['nominal_total']   = 0;
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['nomor']       = '';
               $dataGrid[$index]['kode']        = $dataList[$i]['mak_kode'];
               $dataGrid[$index]['nama']        = $dataList[$i]['mak_nama'];
               $dataGrid[$index]['class_name']  = 'rkat';
               $dataGrid[$index]['row_style']   = 'font-weight: bold;';
               $dataGrid[$index]['tipe']        = 'rkat';
            }elseif((int)$dataList[$i]['program_id'] === (int)$program
               && (int)$dataList[$i]['kegiatan_id'] === (int)$kegiatan
               && (int)$subkegiatan === (int)$dataList[$i]['sub_kegiatan_id']
               && (int)$kegdet !== (int)$dataList[$i]['kegdet_id']){
               $kegdet           = (int)$dataList[$i]['kegdet_id'];
               $kodeSistem       = $program.'.'.$kegiatan.'.'.$subkegiatan.'.'.$kegdet;
               $dataRincian[$kodeSistem]['nominal_satuan']  = 0;
               $dataRincian[$kodeSistem]['nominal_total']   = 0;
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['nomor']       = '';
               $dataGrid[$index]['kode']        = $dataList[$i]['sub_kegiatan_kode'].'.'.$kegdet;
               $dataGrid[$index]['nama']        = $dataList[$i]['deskripsi'];
               $dataGrid[$index]['class_name']  = 'table-common-even2';
               $dataGrid[$index]['row_style']   = 'font-style: italic;';
               $dataGrid[$index]['tipe']        = 'referensi';
            }elseif((int)$dataList[$i]['program_id'] === (int)$program
               && (int)$dataList[$i]['kegiatan_id'] === (int)$kegiatan
               && (int)$subkegiatan !== (int)$dataList[$i]['sub_kegiatan_id']){
               $subkegiatan      = (int)$dataList[$i]['sub_kegiatan_id'];
               $kodeSistem       = $program.'.'.$kegiatan.'.'.$subkegiatan;
               $dataRincian[$kodeSistem]['nominal_satuan']  = 0;
               $dataRincian[$kodeSistem]['nominal_total']   = 0;
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['nomor']       = '';
               $dataGrid[$index]['kode']        = $dataList[$i]['sub_kegiatan_kode'];
               $dataGrid[$index]['nama']        = $dataList[$i]['sub_kegiatan_nama'];
               $dataGrid[$index]['class_name']  = 'table-common-even2';
               $dataGrid[$index]['row_style']   = 'font-weight: bold;';
               $dataGrid[$index]['tipe']        = 'sub_kegiatan';
               $dataGrid[$index]['rkakl']       = 'sub_kegiatan';
               $dataGrid[$index]['rkakl_nama']  = $dataList[$i]['rkakl_sub_kegiatan_nama'];
            }elseif((int)$dataList[$i]['program_id'] === (int)$program
               && (int)$dataList[$i]['kegiatan_id'] !== (int)$kegiatan){
               $kegiatan         = (int)$dataList[$i]['kegiatan_id'];
               $kodeSistem       = $program.'.'.$kegiatan;
               $dataRincian[$kodeSistem]['nominal_satuan']  = 0;
               $dataRincian[$kodeSistem]['nominal_total']   = 0;
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['nomor']       = '';
               $dataGrid[$index]['kode']        = $dataList[$i]['kegiatan_kode'];
               $dataGrid[$index]['nama']        = $dataList[$i]['kegiatan_nama'];
               $dataGrid[$index]['class_name']  = 'table-common-even1';
               $dataGrid[$index]['row_style']   = 'font-weight: bold; font-style: italic;';
               $dataGrid[$index]['tipe']        = 'kegiatan';
               $dataGrid[$index]['rkakl']       = 'output';
               $dataGrid[$index]['rkakl_nama']  = $dataList[$i]['rkakl_output_nama'];
               $dataGrid[$index]['ikk_nama']    = $dataList[$i]['ikk_nama'];
               $dataGrid[$index]['iku_nama']    = $dataList[$i]['iku_nama'];
               $dataGrid[$index]['output']      = '-';
            }else{
               $program          = (int)$dataList[$i]['program_id'];
               $kodeSistem       = $program;
               $dataRincian[$kodeSistem]['nominal_satuan']  = 0;
               $dataRincian[$kodeSistem]['nominal_total']   = 0;
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['kode']        = $dataList[$i]['program_kode'];
               $dataGrid[$index]['nama']        = $dataList[$i]['program_nama'];
               $dataGrid[$index]['class_name']  = 'table-common-even1';
               $dataGrid[$index]['row_style']   = 'font-weight: bold;';
               $dataGrid[$index]['nomor']       = '';
               $dataGrid[$index]['tipe']        = 'program';
               $dataGrid[$index]['rkakl']       = 'kegiatan';
               $dataGrid[$index]['rkakl_nama']  = $dataList[$i]['rkakl_kegiatan_nama'];
            }
            $index++;
         }

         foreach ($dataGrid as $list) {
            $this->mrTemplate->clearTemplate('rkakl');
            switch (strtoupper($list['tipe'])) {
               case 'PROGRAM':
                  $this->mrTemplate->AddVar('rkakl', 'JENIS', strtoupper($list['rkakl']));
                  $this->mrTemplate->AddVar('rkakl', 'RKAKL_NAMA', $list['rkakl_nama']);

                  $list['nominal_satuan']    = number_format($dataRincian[$list['kode_sistem']]['nominal_satuan'], 0, ',','.');
                  $list['nominal_total']     = number_format($dataRincian[$list['kode_sistem']]['nominal_total'], 0, ',','.');
                  break;
               case 'KEGIATAN':
                  $this->mrTemplate->AddVar('rkakl', 'JENIS', strtoupper($list['rkakl']));
                  $this->mrTemplate->AddVar('rkakl', 'RKAKL_NAMA', $list['rkakl_nama']);

                  $list['nominal_satuan']    = number_format($dataRincian[$list['kode_sistem']]['nominal_satuan'], 0, ',','.');
                  $list['nominal_total']     = number_format($dataRincian[$list['kode_sistem']]['nominal_total'], 0, ',','.');
                  break;
               case 'SUB_KEGIATAN':
                  $this->mrTemplate->AddVar('rkakl', 'JENIS', strtoupper($list['rkakl']));
                  $this->mrTemplate->AddVar('rkakl', 'RKAKL_NAMA', $list['rkakl_nama']);

                  $list['nominal_satuan']    = number_format($dataRincian[$list['kode_sistem']]['nominal_satuan'], 0, ',','.');
                  $list['nominal_total']     = number_format($dataRincian[$list['kode_sistem']]['nominal_total'], 0, ',','.');
                  break;
               case 'REFERENSI':
                  break;
               case 'RKAT':
                  $list['nominal_satuan']    = number_format($dataRincian[$list['kode_sistem']]['nominal_satuan'], 0, ',','.');
                  $list['nominal_total']     = number_format($dataRincian[$list['kode_sistem']]['nominal_total'], 0, ',','.');
                  break;
               case 'KOMPONEN':
                  $list['nominal_satuan']    = number_format($list['nominal_satuan'], 0, ',','.');
                  $list['nominal_total']     = number_format($list['nominal_total'], 0, ',','.');
                  break;
               default:
                  $list['nominal_satuan']    = number_format($list['nominal_satuan'], 0, ',','.');
                  $list['nominal_total']     = number_format($list['nominal_total'], 0, ',','.');
                  break;
            }
            $this->mrTemplate->AddVars('data_list', $list);
            $this->mrTemplate->parseTemplate('data_list', 'a');
         }
      }
   }
}
?>