<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/lap_rekap_unitkerja/business/RekapUnitKerja.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') .
'main/function/date.php';

class ViewCetakUnitKerja extends HtmlResponse
{
   function TemplateModule()
   {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
        'module/lap_rekap_unitkerja/template');
      $this->SetTemplateFile('view_cetak_unitkerja.html');
   }

   function TemplateBase()
   {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-print.html');
      $this->SetTemplateFile('layout-common-print.html');
   }

   function ProcessRequest()
   {
      $mObj          = new RekapUnitKerja();
      $requestData   = array();
      $requestData['ta_id']            = Dispatcher::Instance()->Decrypt($mObj->_GET['ta_id']);
      $requestData['ta_nama']          = Dispatcher::Instance()->Decrypt($mObj->_GET['ta_nama']);
      $requestData['unit_nama']        = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_nama']);
      $requestData['unit_id']          = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_id']);
      $requestData['program_id']       = Dispatcher::Instance()->Decrypt($mObj->_GET['program_id']);
      $requestData['program_nama']     = Dispatcher::Instance()->Decrypt($mObj->_GET['program_nama']);
      $requestData['jenis_kegiatan']   = Dispatcher::Instance()->Decrypt($mObj->_GET['jenis_kegiatan']);

      $unitData      = $mObj->GetUnitIdentity($requestData['unit_id']);
      $offset        = 0;
      $limit         = 10000;
      $page          = 0;
      if(isset($_GET['page'])){
         $page       = (string) $_GET['page']->StripHtmlTags()->SqlString()->Raw();
         $offset     = ($page - 1) * $limit;
      }
      $dataList      = $mObj->GetData($offset, $limit, (array)$requestData);
      $dataResume    = $mObj->GetResumeUnitKerja((array)$requestData);

      $return['data_list']    = $mObj->ChangeKeyName($dataList);
      $return['data_unit']    = $mObj->ChangeKeyName($unitData[0]);
      $return['data_resume']  = $mObj->ChangeKeyName($dataResume);
      return $return;
   }

   function ParseTemplate($data = NULL) {
      $dataUnit         = $data['data_unit'];
      $dataList         = $data['data_list'];
      $dataResume       = $data['data_resume'];
      $this->mrTemplate->AddVar('content', 'TAHUN_ANGGARAN', $dataList[0]['ta_nama']);
      $this->mrTemplate->AddVar('content', 'UNIT_KERJA', $dataUnit['unitkerja_nama']);
      $pimpinan         = $dataUnit['unitkerjaNamaPimpinan'];
      if(!empty($pimpinan)){
         $this->mrTemplate->AddVar('content', 'NAMA_PIMPINAN', $pimpinan);
      }else{
         $this->mrTemplate->AddVar('content', 'NAMA_PIMPINAN', str_repeat('.', 50));
      }
      $date             = date('d-m-Y');
      $date             = IndonesianDate($date,'dd-mm-yyyy');
      $this->mrTemplate->AddVar('content', 'DATE', $date);

      if(empty($dataList)){
         $this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'YES');
         $this->mrTemplate->AddVar('resume', 'RESUME_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'NO');
         $this->mrTemplate->AddVar('resume', 'RESUME_EMPTY', 'NO');

         $index      = 0;
         $dataGrid   = array();
         $program    = '';
         $kegiatan   = '';
         $unit       = '';
         $dataRekap  = array();

         for ($i=0; $i < count($dataList);) {
            if((int)$dataList[$i]['unit_id'] === (int)$unit && (int)$dataList[$i]['program_id'] === (int)$program && (int)$dataList[$i]['kegiatan_id'] === (int)$kegiatan){

               $programKodeSistem         = $unit.'.'.$program;
               $kegiatanKodeSistem        = $unit.'.'.$program.'.'.$kegiatan;

               $dataRekap[$programKodeSistem]['nominal_usulan']   += $dataList[$i]['nominal_usulan'];
               $dataRekap[$programKodeSistem]['nominal_setuju']   += $dataList[$i]['nominal_setuju'];
               $dataRekap[$kegiatanKodeSistem]['nominal_usulan']  += $dataList[$i]['nominal_usulan'];
               $dataRekap[$kegiatanKodeSistem]['nominal_setuju']  += $dataList[$i]['nominal_setuju'];

               $dataGrid[$index]['id']    = $dataList[$i]['sub_kegiatan_id'];
               $dataGrid[$index]['kode']  = $dataList[$i]['sub_kegiatan_kode'];
               $dataGrid[$index]['nama']  = $dataList[$i]['sub_kegiatan_nama'];
               $dataGrid[$index]['nominal_usulan']    = $dataList[$i]['nominal_usulan'];
               $dataGrid[$index]['nominal_setuju']    = $dataList[$i]['nominal_setuju'];
               $dataGrid[$index]['level']             = 'sub_kegiatan';
               $i++;
            }elseif((int)$dataList[$i]['unit_id'] === (int)$unit && (int)$dataList[$i]['program_id'] === (int)$program && (int)$dataList[$i]['kegiatan_id'] !== (int)$kegiatan){
               $kegiatan                  = (int)$dataList[$i]['kegiatan_id'];
               $kodeSistem                = $unit.'.'.$program.'.'.$dataList[$i]['kegiatan_id'];
               // unset nominal
               $dataRekap[$kodeSistem]['nominal_usulan']    = 0;
               $dataRekap[$kodeSistem]['nominal_setuju']    = 0;

               $dataGrid[$index]['id']    = $dataList[$i]['kegiatan_id'];
               $dataGrid[$index]['kode']  = $dataList[$i]['kegiatan_kode'];
               $dataGrid[$index]['nama']  = $dataList[$i]['kegiatan_nama'];
               $dataGrid[$index]['kode_sistem']    = $kodeSistem;
               $dataGrid[$index]['level']          = 'kegiatan';
               $dataGrid[$index]['class_name']     = 'table-common-even2';
            }elseif((int)$dataList[$i]['unit_id'] === (int)$unit && (int)$dataList[$i]['program_id'] !== (int)$program){
               $program    = (int)$dataList[$i]['program_id'];
               $index--;
            }else{
               $unit                      = (int)$dataList[$i]['unit_id'];
               $kodeSistem                = $unit.'.'.$dataList[$i]['program_id'];
               // unset nominal
               $dataRekap[$kodeSistem]['nominal_usulan']    = 0;
               $dataRekap[$kodeSistem]['nominal_setuju']    = 0;

               $dataGrid[$index]['id']    = $dataList[$i]['program_id'];
               $dataGrid[$index]['kode']  = $dataList[$i]['program_kode'];
               $dataGrid[$index]['nama']  = $dataList[$i]['program_nama'];
               $dataGrid[$index]['unit_nama']      = $dataList[$i]['unit_nama'];
               $dataGrid[$index]['kode_sistem']    = $kodeSistem;
               $dataGrid[$index]['level']          = 'program';
               $dataGrid[$index]['class_name']     = 'table-common-even1';
               $dataGrid[$index]['row_style']      = 'font-weight: bold;';
            }
            $index++;
         }

         foreach ($dataGrid as $list) {
            switch (strtoupper($list['level'])) {
               case 'PROGRAM':
                  $list['nominal_usulan']       = number_format($dataRekap[$list['kode_sistem']]['nominal_usulan'], 0, ',','.');
                  $list['nominal_setuju']       = number_format($dataRekap[$list['kode_sistem']]['nominal_setuju'], 0, ',','.');
                  break;
               case 'KEGIATAN':
                  $list['nominal_usulan']       = number_format($dataRekap[$list['kode_sistem']]['nominal_usulan'], 0, ',','.');
                  $list['nominal_setuju']       = number_format($dataRekap[$list['kode_sistem']]['nominal_setuju'], 0, ',','.');
                  break;
               default:
                  $list['nominal_usulan']       = number_format($list['nominal_usulan'], 0, ',','.');
                  $list['nominal_setuju']       = number_format($list['nominal_setuju'], 0, ',','.');
                  break;
            }
            $this->mrTemplate->AddVars('data_item', $list);
            $this->mrTemplate->parseTemplate('data_item', 'a');
         }

         $number     = 0;
         foreach ($dataResume as $resume) {
            $resume['class_name']         = ($number % 2 == 0) ? 'table-common-even' : '';
            $resume['nominal_usulan']     = number_format($resume['nominal_usulan'], 0, ',','.');
            $resume['nominal_setuju']     = number_format($resume['nominal_setuju'], 0, ',','.');
            $this->mrTemplate->AddVars('resume_item', $resume);
            $this->mrTemplate->parseTemplate('resume_item', 'a');
            $number++;
         }

      }
   }
}
?>