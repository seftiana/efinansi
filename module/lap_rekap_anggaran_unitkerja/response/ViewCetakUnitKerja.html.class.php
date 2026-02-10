<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/lap_rekap_anggaran_unitkerja/business/RekapUnitKerja.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') .
'main/function/date.php';

class ViewCetakUnitKerja extends HtmlResponse
{
   public function TemplateModule()
   {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
         'module/lap_rekap_anggaran_unitkerja/template');
      $this->SetTemplateFile('view_cetak_unitkerja.html');
   }

   public function TemplateBase()
   {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-print.html');
      $this->SetTemplateFile('layout-common-print.html');
   }

   public function ProcessRequest()
   {
      $mObj             = new RekapUnitKerja();
      $requestData      = array();
      $arrPeriodeTahun  = $mObj->GetPeriodeTahun();
      $requestData['ta_id']            = Dispatcher::Instance()->Decrypt($mObj->_GET['ta_id']);
      $requestData['jenis_kegiatan']   = Dispatcher::Instance()->Decrypt($mObj->_GET['jenis_kegiatan']);
      $requestData['unit_id']          = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_id']);
      $requestData['unit_nama']        = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_nama']);
      $requestData['program_id']       = Dispatcher::Instance()->Decrypt($mObj->_GET['program_id']);
      $requestData['program_nama']     = Dispatcher::Instance()->Decrypt($mObj->_GET['program_nama']);
      $unitData                        = $mObj->GetUnitIdentity($requestData['unit_id']);
      foreach ($arrPeriodeTahun as $ta) {
         if((int)$ta['id'] === (int)$requestData['ta_id']){
            $requestData['ta_nama']       = $ta['name'];
         }
      }

      $dataList         = $mObj->GetData(0, 100000, (array)$requestData);
      $total_data       = $mObj->GetCountData();
      $dataResume       = $mObj->GetDataResume((array)$requestData);

      $return['data_list']       = $mObj->ChangeKeyName($dataList);
      $return['data_resume']     = $mObj->ChangeKeyName($dataResume);
      $return['data_unit']       = $mObj->ChangeKeyName($unitData[0]);
      $return['request_data']    = $requestData;
      return $return;
   }

   public function ParseTemplate($data = NULL)
   {
      $requestData         = $data['request_data'];
      $dataUnit            = $data['data_unit'];
      $dataList            = $data['data_list'];
      $dataResume          = $data['data_resume'];
      $pimpinan            = $dataUnit['unitkerja_nama_pimpinan'];
      $date                = date('d-m-Y');
      $date                = IndonesianDate($date,'dd-mm-yyyy');

      $this->mrTemplate->AddVar('content', 'TAHUN_ANGGARAN', $requestData['ta_nama']);
      $this->mrTemplate->AddVar('content', 'UNIT_KERJA', $dataUnit['unitkerja_nama']);
      $this->mrTemplate->AddVar('content', 'DATE', $date);
      if(!empty($pimpinan)){
         $this->mrTemplate->AddVar('content', 'NAMA_PIMPINAN', $pimpinan);
      }else{
         $this->mrTemplate->AddVar('content', 'NAMA_PIMPINAN', str_repeat('.', 50));
      }

      if(empty($dataList)){
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
         $this->mrTemplate->AddVar('data_resume', 'DATA_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         $this->mrTemplate->AddVar('data_resume', 'DATA_EMPTY', 'NO');
         // inisialisasi data
         $unit          = '';
         $program       = '';
         $kegiatan      = '';
         $subkegiatan   = '';
         $index         = 0;
         $dataGrid      = array();
         $nomor         = 0;
         $dataRekap     = array();
         for ($i=0; $i < count($dataList);) {
            if((int)$unit === (int)$dataList[$i]['unit_id'] && (int)$program === (int)$dataList[$i]['program_id'] && (int)$kegiatan === (int)$dataList[$i]['kegiatan_id']){
               $programKodeSistem         = $unit.'.'.$program;
               $kegiatanKodeSistem        = $unit.'.'.$program.'.'.$kegiatan;
               $dataRekap[$programKodeSistem]['nominal_setuju']   += $dataList[$i]['nominal_setuju'];
               $dataRekap[$kegiatanKodeSistem]['nominal_setuju']  += $dataList[$i]['nominal_setuju'];
               $dataGrid[$index]['id']    = $dataList[$i]['sub_kegiatan_id'];
               $dataGrid[$index]['kode']  = $dataList[$i]['sub_kegiatan_kode'];
               $dataGrid[$index]['nama']  = $dataList[$i]['sub_kegiatan_nama'];
               $dataGrid[$index]['jenis'] = 'sub_kegiatan';
               $dataGrid[$index]['class_name']     = ($nomor % 2 <> 0) ? 'table-common-even' : '';
               $dataGrid[$index]['row_style']      = '';
               $dataGrid[$index]['nominal_setuju'] = $dataList[$i]['nominal_setuju'];
               $i++;
               $nomor++;
            }elseif((int)$unit === (int)$dataList[$i]['unit_id'] && (int)$program === (int)$dataList[$i]['program_id'] && (int)$kegiatan !== (int)$dataList[$i]['kegiatan_id']){
               unset($nomor);
               $nomor                     = 1;
               $kegiatan                  = (int)$dataList[$i]['kegiatan_id'];
               $kodeSistem                = $unit.'.'.$program.'.'.$kegiatan;
               $dataRekap[$kodeSistem]['nominal_setuju']    = 0;
               $dataGrid[$index]['id']    = $dataList[$i]['kegiatan_id'];
               $dataGrid[$index]['kode']  = $dataList[$i]['kegiatan_kode'];
               $dataGrid[$index]['nama']  = $dataList[$i]['kegiatan_nama'];
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['unit_nama']   = '';
               $dataGrid[$index]['class_name']  = 'table-common-even2';
               $dataGrid[$index]['row_style']   = '';
               $dataGrid[$index]['jenis'] = 'kegiatan';
            }elseif((int)$unit === (int)$dataList[$i]['unit_id'] && (int)$program !== (int)$dataList[$i]['program_id']){
               $program       = (int)$dataList[$i]['program_id'];
               // continue($index);
            }else{
               $unit          = (int)$dataList[$i]['unit_id'];
               $kodeSistem    = $unit.'.'.(int)$dataList[$i]['program_id'];
               $dataRekap[$kodeSistem]['nominal_setuju']    = 0;
               $dataGrid[$index]['id']    = $dataList[$i]['program_id'];
               $dataGrid[$index]['kode']  = $dataList[$i]['program_kode'];
               $dataGrid[$index]['nama']  = $dataList[$i]['program_nama'];
               $dataGrid[$index]['unit_nama']   = $dataList[$i]['unit_nama'];
               $dataGrid[$index]['unit_id']     = $dataList[$i]['unit_id'];
               $dataGrid[$index]['unit_kode']   = $dataList[$i]['unit_kode'];
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['class_name']  = 'table-common-even1';
               $dataGrid[$index]['row_style']   = 'font-weight: bold;';
               $dataGrid[$index]['jenis']       = 'program';
            }
            $index++;
         }

         foreach ($dataGrid as $list) {
            switch (strtoupper($list['jenis'])) {
               case 'PROGRAM':
                  $list['nominal_setuju']    = number_format($dataRekap[$list['kode_sistem']]['nominal_setuju'], 0, ',', '.');
                  break;
               case 'KEGIATAN':
                  $list['nominal_setuju']    = number_format($dataRekap[$list['kode_sistem']]['nominal_setuju'], 0, ',', '.');
                  break;
               case 'SUB_KEGIATAN':
                  $list['nominal_setuju']    = number_format($list['nominal_setuju'], 0, ',','.');
                  break;
               default:
                  $list['nominal_setuju']    = number_format($list['nominal_setuju'], 0, ',','.');
                  break;
            }
            $this->mrTemplate->AddVars('data_list', $list);
            $this->mrTemplate->parseTemplate('data_list', 'a');
         }

         // DATA RESUME
         foreach ($dataResume as $resume) {
            $resume['nominal_setuju']     = number_format($resume['nominal_setuju'], 0, ',','.');
            $this->mrTemplate->AddVars('resume_item', $resume);
            $this->mrTemplate->parseTemplate('resume_item', 'a');
         }
      }
   }

}
?>