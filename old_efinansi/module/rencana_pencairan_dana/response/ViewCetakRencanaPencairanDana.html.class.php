<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/rencana_pencairan_dana/business/AppRencanaPencairanDana.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewCetakRencanaPencairanDana extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') .
         'module/rencana_pencairan_dana/template');
      $this->SetTemplateFile('cetak_rencana_pencairan_dana.html');
   }

   function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-print.html');
      $this->SetTemplateFile('layout-common-print.html');
   }

   function ProcessRequest(){
      $mObj       = new AppRencanaPencairanDana();
      $mUnitObj   = new UserUnitKerja();
      $userId     = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
      $dataUnit   = $mUnitObj->GetUnitKerjaRefUser($userId);
      $arrPeriodeTahun  = $mObj->GetPeriodeTahun();
      $periodeTahun     = $mObj->GetPeriodeTahun(array(
         'active' => true
      ));
      $requestData      = array();

      $requestData['ta_id']      = Dispatcher::Instance()->Decrypt($mObj->_GET['ta_id']);
      $requestData['unit_id']    = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_id']);
      $requestData['unit_nama']  = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_nama']);

      foreach ($arrPeriodeTahun as $ta) {
         if((int)$ta['id'] === (int)$requestData['ta_id']){
            $requestData['ta_nama'] = $ta['name'];
         }
      }

      $dataList      = $mObj->GetData(0, 10000, (array)$requestData);
      $totalData     = $mObj->Count();

      $return['request_data']       = $requestData;
      $return['data_list']          = $mObj->ChangeKeyName($dataList);
      return $return;
   }

   function ParseTemplate($data = NULL) {
      $requestData   = $data['request_data'];
      $dataList      = $data['data_list'];
      $start         = 1;
      $this->mrTemplate->AddVar('content', 'TAHUN_PERIODE', $requestData['ta_nama']);
      $this->mrTemplate->AddVar('content', 'UNIT_KERJA_LABEL', $requestData['unit_nama']);

      if (empty($dataList))  {
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

               $dataRincian[$kodeSistemProgram]['nominal_approve']       += $dataList[$i]['nominal_approve'];
               $dataRincian[$kodeSistemKegiatan]['nominal_approve']      += $dataList[$i]['nominal_approve'];
               $dataRincian[$kodeSistemSubKegiatan]['nominal_approve']   += $dataList[$i]['nominal_approve'];
               $dataRincian[$kodeSistemKegdet]['nominal_approve']        += $dataList[$i]['nominal_approve'];
               $dataRincian[$kodeSistemMak]['nominal_approve']           += $dataList[$i]['nominal_approve'];

               $dataRincian[$kodeSistemProgram]['nominal_januari']      += $dataList[$i]['nominal_januari'];
               $dataRincian[$kodeSistemProgram]['nominal_februari']     += $dataList[$i]['nominal_februari'];
               $dataRincian[$kodeSistemProgram]['nominal_maret']        += $dataList[$i]['nominal_maret'];
               $dataRincian[$kodeSistemProgram]['nominal_april']        += $dataList[$i]['nominal_april'];
               $dataRincian[$kodeSistemProgram]['nominal_mei']          += $dataList[$i]['nominal_mei'];
               $dataRincian[$kodeSistemProgram]['nominal_juni']         += $dataList[$i]['nominal_juni'];
               $dataRincian[$kodeSistemProgram]['nominal_juli']         += $dataList[$i]['nominal_juli'];
               $dataRincian[$kodeSistemProgram]['nominal_agustus']      += $dataList[$i]['nominal_agustus'];
               $dataRincian[$kodeSistemProgram]['nominal_september']    += $dataList[$i]['nominal_september'];
               $dataRincian[$kodeSistemProgram]['nominal_oktober']      += $dataList[$i]['nominal_oktober'];
               $dataRincian[$kodeSistemProgram]['nominal_november']     += $dataList[$i]['nominal_november'];
               $dataRincian[$kodeSistemProgram]['nominal_desember']     += $dataList[$i]['nominal_desember'];

               // nominal bulan kegiatan
               $dataRincian[$kodeSistemKegiatan]['nominal_januari']     += $dataList[$i]['nominal_januari'];
               $dataRincian[$kodeSistemKegiatan]['nominal_februari']    += $dataList[$i]['nominal_februari'];
               $dataRincian[$kodeSistemKegiatan]['nominal_maret']       += $dataList[$i]['nominal_maret'];
               $dataRincian[$kodeSistemKegiatan]['nominal_april']       += $dataList[$i]['nominal_april'];
               $dataRincian[$kodeSistemKegiatan]['nominal_mei']         += $dataList[$i]['nominal_mei'];
               $dataRincian[$kodeSistemKegiatan]['nominal_juni']        += $dataList[$i]['nominal_juni'];
               $dataRincian[$kodeSistemKegiatan]['nominal_juli']        += $dataList[$i]['nominal_juli'];
               $dataRincian[$kodeSistemKegiatan]['nominal_agustus']     += $dataList[$i]['nominal_agustus'];
               $dataRincian[$kodeSistemKegiatan]['nominal_september']   += $dataList[$i]['nominal_september'];
               $dataRincian[$kodeSistemKegiatan]['nominal_oktober']     += $dataList[$i]['nominal_oktober'];
               $dataRincian[$kodeSistemKegiatan]['nominal_november']    += $dataList[$i]['nominal_november'];
               $dataRincian[$kodeSistemKegiatan]['nominal_desember']    += $dataList[$i]['nominal_desember'];

               // nominal bulan sub kegiatan
               $dataRincian[$kodeSistemSubKegiatan]['nominal_januari']   += $dataList[$i]['nominal_januari'];
               $dataRincian[$kodeSistemSubKegiatan]['nominal_februari']  += $dataList[$i]['nominal_februari'];
               $dataRincian[$kodeSistemSubKegiatan]['nominal_maret']     += $dataList[$i]['nominal_maret'];
               $dataRincian[$kodeSistemSubKegiatan]['nominal_april']     += $dataList[$i]['nominal_april'];
               $dataRincian[$kodeSistemSubKegiatan]['nominal_mei']       += $dataList[$i]['nominal_mei'];
               $dataRincian[$kodeSistemSubKegiatan]['nominal_juni']      += $dataList[$i]['nominal_juni'];
               $dataRincian[$kodeSistemSubKegiatan]['nominal_juli']      += $dataList[$i]['nominal_juli'];
               $dataRincian[$kodeSistemSubKegiatan]['nominal_agustus']   += $dataList[$i]['nominal_agustus'];
               $dataRincian[$kodeSistemSubKegiatan]['nominal_september'] += $dataList[$i]['nominal_september'];
               $dataRincian[$kodeSistemSubKegiatan]['nominal_oktober']   += $dataList[$i]['nominal_oktober'];
               $dataRincian[$kodeSistemSubKegiatan]['nominal_november']  += $dataList[$i]['nominal_november'];
               $dataRincian[$kodeSistemSubKegiatan]['nominal_desember']  += $dataList[$i]['nominal_desember'];

               // nominal bulan kegiatan detail
               $dataRincian[$kodeSistemKegdet]['nominal_januari']    += $dataList[$i]['nominal_januari'];
               $dataRincian[$kodeSistemKegdet]['nominal_februari']   += $dataList[$i]['nominal_februari'];
               $dataRincian[$kodeSistemKegdet]['nominal_maret']      += $dataList[$i]['nominal_maret'];
               $dataRincian[$kodeSistemKegdet]['nominal_april']      += $dataList[$i]['nominal_april'];
               $dataRincian[$kodeSistemKegdet]['nominal_mei']        += $dataList[$i]['nominal_mei'];
               $dataRincian[$kodeSistemKegdet]['nominal_juni']       += $dataList[$i]['nominal_juni'];
               $dataRincian[$kodeSistemKegdet]['nominal_juli']       += $dataList[$i]['nominal_juli'];
               $dataRincian[$kodeSistemKegdet]['nominal_agustus']    += $dataList[$i]['nominal_agustus'];
               $dataRincian[$kodeSistemKegdet]['nominal_september']  += $dataList[$i]['nominal_september'];
               $dataRincian[$kodeSistemKegdet]['nominal_oktober']    += $dataList[$i]['nominal_oktober'];
               $dataRincian[$kodeSistemKegdet]['nominal_november']   += $dataList[$i]['nominal_november'];
               $dataRincian[$kodeSistemKegdet]['nominal_desember']   += $dataList[$i]['nominal_desember'];

               // nominal bulan MAK
               $dataRincian[$kodeSistemMak]['nominal_januari']    += $dataList[$i]['nominal_januari'];
               $dataRincian[$kodeSistemMak]['nominal_februari']   += $dataList[$i]['nominal_februari'];
               $dataRincian[$kodeSistemMak]['nominal_maret']      += $dataList[$i]['nominal_maret'];
               $dataRincian[$kodeSistemMak]['nominal_april']      += $dataList[$i]['nominal_april'];
               $dataRincian[$kodeSistemMak]['nominal_mei']        += $dataList[$i]['nominal_mei'];
               $dataRincian[$kodeSistemMak]['nominal_juni']       += $dataList[$i]['nominal_juni'];
               $dataRincian[$kodeSistemMak]['nominal_juli']       += $dataList[$i]['nominal_juli'];
               $dataRincian[$kodeSistemMak]['nominal_agustus']    += $dataList[$i]['nominal_agustus'];
               $dataRincian[$kodeSistemMak]['nominal_september']  += $dataList[$i]['nominal_september'];
               $dataRincian[$kodeSistemMak]['nominal_oktober']    += $dataList[$i]['nominal_oktober'];
               $dataRincian[$kodeSistemMak]['nominal_november']   += $dataList[$i]['nominal_november'];
               $dataRincian[$kodeSistemMak]['nominal_desember']   += $dataList[$i]['nominal_desember'];

               $dataRincian[$kodeSistemProgram]['nominal_total']       += $dataList[$i]['total_approve'];
               $dataRincian[$kodeSistemKegiatan]['nominal_total']      += $dataList[$i]['total_approve'];
               $dataRincian[$kodeSistemSubKegiatan]['nominal_total']   += $dataList[$i]['total_approve'];
               $dataRincian[$kodeSistemKegdet]['nominal_total']        += $dataList[$i]['total_approve'];
               $dataRincian[$kodeSistemMak]['nominal_total']           += $dataList[$i]['total_approve'];

               $dataGrid[$index]['kode']        = $dataList[$i]['komp_kode'];
               $dataGrid[$index]['nama']        = $dataList[$i]['komp_nama'];
               $dataGrid[$index]['unit_nama']   = $dataList[$i]['unit_nama'];
               $dataGrid[$index]['nomor']       = $start;
               $dataGrid[$index]['tipe']        = 'komponen';
               $dataGrid[$index]['volume']      = $dataList[$i]['volume'];
               $dataGrid[$index]['nominal_approve']      = $dataList[$i]['nominal_approve'];
               $dataGrid[$index]['nominal_total']        = $dataList[$i]['total_approve'];
               $dataGrid[$index]['nominal_januari']      = $dataList[$i]['nominal_januari'];
               $dataGrid[$index]['nominal_februari']     = $dataList[$i]['nominal_februari'];
               $dataGrid[$index]['nominal_maret']        = $dataList[$i]['nominal_maret'];
               $dataGrid[$index]['nominal_april']        = $dataList[$i]['nominal_april'];
               $dataGrid[$index]['nominal_mei']          = $dataList[$i]['nominal_mei'];
               $dataGrid[$index]['nominal_juni']         = $dataList[$i]['nominal_juni'];
               $dataGrid[$index]['nominal_juli']         = $dataList[$i]['nominal_juli'];
               $dataGrid[$index]['nominal_agustus']      = $dataList[$i]['nominal_agustus'];
               $dataGrid[$index]['nominal_september']    = $dataList[$i]['nominal_september'];
               $dataGrid[$index]['nominal_oktober']      = $dataList[$i]['nominal_oktober'];
               $dataGrid[$index]['nominal_november']     = $dataList[$i]['nominal_november'];
               $dataGrid[$index]['nominal_desember']     = $dataList[$i]['nominal_desember'];
               $i++;
               $start++;
            }elseif((int)$dataList[$i]['program_id'] === (int)$program
               && (int)$dataList[$i]['kegiatan_id'] === (int)$kegiatan
               && (int)$subkegiatan === (int)$dataList[$i]['sub_kegiatan_id']
               && (int)$kegdet === (int)$dataList[$i]['kegdet_id']
               && (int)$makId !== (int)$dataList[$i]['mak_id']){
               $makId            = (int)$dataList[$i]['mak_id'];
               $kodeSistem       = $program.'.'.$kegiatan.'.'.$subkegiatan.'.'.$kegdet.'.'.$makId;
               $dataRincian[$kodeSistem]['nominal_approve']    = 0;
               $dataRincian[$kodeSistem]['nominal_total']      = 0;
               $dataRincian[$kodeSistem]['nominal_januari']    = 0;
               $dataRincian[$kodeSistem]['nominal_februari']   = 0;
               $dataRincian[$kodeSistem]['nominal_maret']      = 0;
               $dataRincian[$kodeSistem]['nominal_april']      = 0;
               $dataRincian[$kodeSistem]['nominal_mei']        = 0;
               $dataRincian[$kodeSistem]['nominal_juni']       = 0;
               $dataRincian[$kodeSistem]['nominal_juli']       = 0;
               $dataRincian[$kodeSistem]['nominal_agustus']    = 0;
               $dataRincian[$kodeSistem]['nominal_september']  = 0;
               $dataRincian[$kodeSistem]['nominal_oktober']    = 0;
               $dataRincian[$kodeSistem]['nominal_november']   = 0;
               $dataRincian[$kodeSistem]['nominal_desember']   = 0;

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
               $dataRincian[$kodeSistem]['nominal_approve']  = 0;
               $dataRincian[$kodeSistem]['nominal_total']   = 0;
               $dataRincian[$kodeSistem]['nominal_januari']    = 0;
               $dataRincian[$kodeSistem]['nominal_februari']   = 0;
               $dataRincian[$kodeSistem]['nominal_maret']      = 0;
               $dataRincian[$kodeSistem]['nominal_april']      = 0;
               $dataRincian[$kodeSistem]['nominal_mei']        = 0;
               $dataRincian[$kodeSistem]['nominal_juni']       = 0;
               $dataRincian[$kodeSistem]['nominal_juli']       = 0;
               $dataRincian[$kodeSistem]['nominal_agustus']    = 0;
               $dataRincian[$kodeSistem]['nominal_september']  = 0;
               $dataRincian[$kodeSistem]['nominal_oktober']    = 0;
               $dataRincian[$kodeSistem]['nominal_november']   = 0;
               $dataRincian[$kodeSistem]['nominal_desember']   = 0;

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
               $dataRincian[$kodeSistem]['nominal_approve']  = 0;
               $dataRincian[$kodeSistem]['nominal_total']   = 0;
               $dataRincian[$kodeSistem]['nominal_januari']    = 0;
               $dataRincian[$kodeSistem]['nominal_februari']   = 0;
               $dataRincian[$kodeSistem]['nominal_maret']      = 0;
               $dataRincian[$kodeSistem]['nominal_april']      = 0;
               $dataRincian[$kodeSistem]['nominal_mei']        = 0;
               $dataRincian[$kodeSistem]['nominal_juni']       = 0;
               $dataRincian[$kodeSistem]['nominal_juli']       = 0;
               $dataRincian[$kodeSistem]['nominal_agustus']    = 0;
               $dataRincian[$kodeSistem]['nominal_september']  = 0;
               $dataRincian[$kodeSistem]['nominal_oktober']    = 0;
               $dataRincian[$kodeSistem]['nominal_november']   = 0;
               $dataRincian[$kodeSistem]['nominal_desember']   = 0;

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
               $dataRincian[$kodeSistem]['nominal_approve']  = 0;
               $dataRincian[$kodeSistem]['nominal_total']   = 0;
               $dataRincian[$kodeSistem]['nominal_januari']    = 0;
               $dataRincian[$kodeSistem]['nominal_februari']   = 0;
               $dataRincian[$kodeSistem]['nominal_maret']      = 0;
               $dataRincian[$kodeSistem]['nominal_april']      = 0;
               $dataRincian[$kodeSistem]['nominal_mei']        = 0;
               $dataRincian[$kodeSistem]['nominal_juni']       = 0;
               $dataRincian[$kodeSistem]['nominal_juli']       = 0;
               $dataRincian[$kodeSistem]['nominal_agustus']    = 0;
               $dataRincian[$kodeSistem]['nominal_september']  = 0;
               $dataRincian[$kodeSistem]['nominal_oktober']    = 0;
               $dataRincian[$kodeSistem]['nominal_november']   = 0;
               $dataRincian[$kodeSistem]['nominal_desember']   = 0;

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
               $dataRincian[$kodeSistem]['nominal_approve']  = 0;
               $dataRincian[$kodeSistem]['nominal_total']   = 0;
               $dataRincian[$kodeSistem]['nominal_januari']    = 0;
               $dataRincian[$kodeSistem]['nominal_februari']   = 0;
               $dataRincian[$kodeSistem]['nominal_maret']      = 0;
               $dataRincian[$kodeSistem]['nominal_april']      = 0;
               $dataRincian[$kodeSistem]['nominal_mei']        = 0;
               $dataRincian[$kodeSistem]['nominal_juni']       = 0;
               $dataRincian[$kodeSistem]['nominal_juli']       = 0;
               $dataRincian[$kodeSistem]['nominal_agustus']    = 0;
               $dataRincian[$kodeSistem]['nominal_september']  = 0;
               $dataRincian[$kodeSistem]['nominal_oktober']    = 0;
               $dataRincian[$kodeSistem]['nominal_november']   = 0;
               $dataRincian[$kodeSistem]['nominal_desember']   = 0;

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

                  $list['nominal_approve']    = number_format($dataRincian[$list['kode_sistem']]['nominal_approve'], 0, ',','.');
                  $list['nominal_total']     = number_format($dataRincian[$list['kode_sistem']]['nominal_total'], 0, ',','.');
                  $list['nominal_januari']   = number_format($dataRincian[$list['kode_sistem']]['nominal_januari'], 0, ',','.');
                  $list['nominal_februari']  = number_format($dataRincian[$list['kode_sistem']]['nominal_februari'], 0, ',','.');
                  $list['nominal_maret']     = number_format($dataRincian[$list['kode_sistem']]['nominal_maret'], 0, ',','.');
                  $list['nominal_april']     = number_format($dataRincian[$list['kode_sistem']]['nominal_april'], 0, ',','.');
                  $list['nominal_mei']       = number_format($dataRincian[$list['kode_sistem']]['nominal_mei'], 0, ',','.');
                  $list['nominal_juni']      = number_format($dataRincian[$list['kode_sistem']]['nominal_juni'], 0, ',','.');
                  $list['nominal_juli']      = number_format($dataRincian[$list['kode_sistem']]['nominal_juli'], 0, ',','.');
                  $list['nominal_agustus']   = number_format($dataRincian[$list['kode_sistem']]['nominal_agustus'], 0, ',','.');
                  $list['nominal_september'] = number_format($dataRincian[$list['kode_sistem']]['nominal_september'], 0, ',','.');
                  $list['nominal_oktober']   = number_format($dataRincian[$list['kode_sistem']]['nominal_oktober'], 0, ',','.');
                  $list['nominal_november']  = number_format($dataRincian[$list['kode_sistem']]['nominal_november'], 0, ',','.');
                  $list['nominal_desember']  = number_format($dataRincian[$list['kode_sistem']]['nominal_desember'], 0, ',','.');
                  break;
               case 'KEGIATAN':
                  $this->mrTemplate->AddVar('rkakl', 'JENIS', strtoupper($list['rkakl']));
                  $this->mrTemplate->AddVar('rkakl', 'RKAKL_NAMA', $list['rkakl_nama']);

                  $list['nominal_approve']    = number_format($dataRincian[$list['kode_sistem']]['nominal_approve'], 0, ',','.');
                  $list['nominal_total']     = number_format($dataRincian[$list['kode_sistem']]['nominal_total'], 0, ',','.');
                  $list['nominal_januari']   = number_format($dataRincian[$list['kode_sistem']]['nominal_januari'], 0, ',','.');
                  $list['nominal_februari']  = number_format($dataRincian[$list['kode_sistem']]['nominal_februari'], 0, ',','.');
                  $list['nominal_maret']     = number_format($dataRincian[$list['kode_sistem']]['nominal_maret'], 0, ',','.');
                  $list['nominal_april']     = number_format($dataRincian[$list['kode_sistem']]['nominal_april'], 0, ',','.');
                  $list['nominal_mei']       = number_format($dataRincian[$list['kode_sistem']]['nominal_mei'], 0, ',','.');
                  $list['nominal_juni']      = number_format($dataRincian[$list['kode_sistem']]['nominal_juni'], 0, ',','.');
                  $list['nominal_juli']      = number_format($dataRincian[$list['kode_sistem']]['nominal_juli'], 0, ',','.');
                  $list['nominal_agustus']   = number_format($dataRincian[$list['kode_sistem']]['nominal_agustus'], 0, ',','.');
                  $list['nominal_september'] = number_format($dataRincian[$list['kode_sistem']]['nominal_september'], 0, ',','.');
                  $list['nominal_oktober']   = number_format($dataRincian[$list['kode_sistem']]['nominal_oktober'], 0, ',','.');
                  $list['nominal_november']  = number_format($dataRincian[$list['kode_sistem']]['nominal_november'], 0, ',','.');
                  $list['nominal_desember']  = number_format($dataRincian[$list['kode_sistem']]['nominal_desember'], 0, ',','.');
                  break;
               case 'SUB_KEGIATAN':
                  $this->mrTemplate->AddVar('rkakl', 'JENIS', strtoupper($list['rkakl']));
                  $this->mrTemplate->AddVar('rkakl', 'RKAKL_NAMA', $list['rkakl_nama']);

                  $list['nominal_approve']    = number_format($dataRincian[$list['kode_sistem']]['nominal_approve'], 0, ',','.');
                  $list['nominal_total']     = number_format($dataRincian[$list['kode_sistem']]['nominal_total'], 0, ',','.');
                  $list['nominal_januari']   = number_format($dataRincian[$list['kode_sistem']]['nominal_januari'], 0, ',','.');
                  $list['nominal_februari']  = number_format($dataRincian[$list['kode_sistem']]['nominal_februari'], 0, ',','.');
                  $list['nominal_maret']     = number_format($dataRincian[$list['kode_sistem']]['nominal_maret'], 0, ',','.');
                  $list['nominal_april']     = number_format($dataRincian[$list['kode_sistem']]['nominal_april'], 0, ',','.');
                  $list['nominal_mei']       = number_format($dataRincian[$list['kode_sistem']]['nominal_mei'], 0, ',','.');
                  $list['nominal_juni']      = number_format($dataRincian[$list['kode_sistem']]['nominal_juni'], 0, ',','.');
                  $list['nominal_juli']      = number_format($dataRincian[$list['kode_sistem']]['nominal_juli'], 0, ',','.');
                  $list['nominal_agustus']   = number_format($dataRincian[$list['kode_sistem']]['nominal_agustus'], 0, ',','.');
                  $list['nominal_september'] = number_format($dataRincian[$list['kode_sistem']]['nominal_september'], 0, ',','.');
                  $list['nominal_oktober']   = number_format($dataRincian[$list['kode_sistem']]['nominal_oktober'], 0, ',','.');
                  $list['nominal_november']  = number_format($dataRincian[$list['kode_sistem']]['nominal_november'], 0, ',','.');
                  $list['nominal_desember']  = number_format($dataRincian[$list['kode_sistem']]['nominal_desember'], 0, ',','.');
                  break;
               case 'REFERENSI':
                  break;
               case 'RKAT':
                  $list['nominal_approve']    = number_format($dataRincian[$list['kode_sistem']]['nominal_approve'], 0, ',','.');
                  $list['nominal_total']     = number_format($dataRincian[$list['kode_sistem']]['nominal_total'], 0, ',','.');
                  $list['nominal_januari']   = number_format($dataRincian[$list['kode_sistem']]['nominal_januari'], 0, ',','.');
                  $list['nominal_februari']  = number_format($dataRincian[$list['kode_sistem']]['nominal_februari'], 0, ',','.');
                  $list['nominal_maret']     = number_format($dataRincian[$list['kode_sistem']]['nominal_maret'], 0, ',','.');
                  $list['nominal_april']     = number_format($dataRincian[$list['kode_sistem']]['nominal_april'], 0, ',','.');
                  $list['nominal_mei']       = number_format($dataRincian[$list['kode_sistem']]['nominal_mei'], 0, ',','.');
                  $list['nominal_juni']      = number_format($dataRincian[$list['kode_sistem']]['nominal_juni'], 0, ',','.');
                  $list['nominal_juli']      = number_format($dataRincian[$list['kode_sistem']]['nominal_juli'], 0, ',','.');
                  $list['nominal_agustus']   = number_format($dataRincian[$list['kode_sistem']]['nominal_agustus'], 0, ',','.');
                  $list['nominal_september'] = number_format($dataRincian[$list['kode_sistem']]['nominal_september'], 0, ',','.');
                  $list['nominal_oktober']   = number_format($dataRincian[$list['kode_sistem']]['nominal_oktober'], 0, ',','.');
                  $list['nominal_november']  = number_format($dataRincian[$list['kode_sistem']]['nominal_november'], 0, ',','.');
                  $list['nominal_desember']  = number_format($dataRincian[$list['kode_sistem']]['nominal_desember'], 0, ',','.');
                  break;
               case 'KOMPONEN':
                  $list['nominal_approve']    = number_format($list['nominal_approve'], 0, ',','.');
                  $list['nominal_total']     = number_format($list['nominal_total'], 0, ',','.');
                  $list['nominal_januari']   = number_format($list['nominal_januari'], 0, ',','.');
                  $list['nominal_februari']  = number_format($list['nominal_februari'], 0, ',','.');
                  $list['nominal_maret']     = number_format($list['nominal_maret'], 0, ',','.');
                  $list['nominal_april']     = number_format($list['nominal_april'], 0, ',','.');
                  $list['nominal_mei']       = number_format($list['nominal_mei'], 0, ',','.');
                  $list['nominal_juni']      = number_format($list['nominal_juni'], 0, ',','.');
                  $list['nominal_juli']      = number_format($list['nominal_juli'], 0, ',','.');
                  $list['nominal_agustus']   = number_format($list['nominal_agustus'], 0, ',','.');
                  $list['nominal_september'] = number_format($list['nominal_september'], 0, ',','.');
                  $list['nominal_oktober']   = number_format($list['nominal_oktober'], 0, ',','.');
                  $list['nominal_november']  = number_format($list['nominal_november'], 0, ',','.');
                  $list['nominal_desember']  = number_format($list['nominal_desember'], 0, ',','.');
                  break;
               default:
                  $list['nominal_approve']   = number_format($list['nominal_approve'], 0, ',','.');
                  $list['nominal_total']     = number_format($list['nominal_total'], 0, ',','.');
                  $list['nominal_januari']   = number_format($list['nominal_januari'], 0, ',','.');
                  $list['nominal_februari']  = number_format($list['nominal_februari'], 0, ',','.');
                  $list['nominal_maret']     = number_format($list['nominal_maret'], 0, ',','.');
                  $list['nominal_april']     = number_format($list['nominal_april'], 0, ',','.');
                  $list['nominal_mei']       = number_format($list['nominal_mei'], 0, ',','.');
                  $list['nominal_juni']      = number_format($list['nominal_juni'], 0, ',','.');
                  $list['nominal_juli']      = number_format($list['nominal_juli'], 0, ',','.');
                  $list['nominal_agustus']   = number_format($list['nominal_agustus'], 0, ',','.');
                  $list['nominal_september'] = number_format($list['nominal_september'], 0, ',','.');
                  $list['nominal_oktober']   = number_format($list['nominal_oktober'], 0, ',','.');
                  $list['nominal_november']  = number_format($list['nominal_november'], 0, ',','.');
                  $list['nominal_desember']  = number_format($list['nominal_desember'], 0, ',','.');
                  break;
            }
            $this->mrTemplate->AddVars('data_list', $list);
            $this->mrTemplate->parseTemplate('data_list', 'a');
         }
      }

   }
}
?>