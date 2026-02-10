<?php
/**
* ================= doc ====================
* FILENAME     : ViewLaporanRekapPenerimaanPengeluaranBulanan.html.class.php
* @package     : ViewLaporanRekapAPenerimaanPengeluaranBulanan
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-04-29
* @Modified    : 2015-04-29
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/laporan_rekap_penerimaan_pengeluaran_bulanan/business/LaporanRekapPenerimaanPengeluaranBulanan.class.php';

class ViewLaporanRekapPenerimaanPengeluaranBulanan extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/laporan_rekap_penerimaan_pengeluaran_bulanan/template/');
      $this->SetTemplateFile('view_laporan_rekap_penerimaan_pengeluaran_bulanan.html');
   }

   function ProcessRequest(){
      $mObj       = new LaporanRekapPenerimaanPengeluaranBulanan();
      $mUnitObj   = new UserUnitKerja();
      $userId     = $mObj->getUserId();
      $unitKerja  = $mUnitObj->GetUnitKerjaRefUser($userId);
      $requestData   = array();
      $arrPeriodeTahun     = $mObj->getPeriodeTahun();
      $periodeTahun        = $mObj->getPeriodeTahun(array('active' => true));
      $getdate             = getdate();
      $curr_mon            = (int)$getdate['mon'];
      $curr_day            = (int)$getdate['mday'];
      $curr_year           = (int)$getdate['year'];
      $tahun_awal          = date('Y',time())-5;
      $tahun_akhir         = date('Y', time())+5;

      if(isset($mObj->_POST['btnSearch'])){
         $tanggal_day      = (int)$mObj->_POST['tanggal_day'];
         $tanggal_mon      = (int)$mObj->_POST['tanggal_mon'];
         $tanggal_year     = (int)$mObj->_POST['tanggal_year'];

         $requestData['ta_id']      = $mObj->_POST['tahun_anggaran'];
         $requestData['unit_id']    = $mObj->_POST['unit_id'];
         $requestData['unit_nama']  = $mObj->_POST['unit_nama'];
         $requestData['program_id'] = $mObj->_POST['program_id'];
         $requestData['program']    = $mObj->_POST['program'];
         $requestData['tanggal']    = date('Y-m-d', mktime(0,0,0, $tanggal_mon, 1, $tanggal_year));
      }elseif(isset($mObj->_GET['search'])){
         $requestData['ta_id']      = Dispatcher::Instance()->Decrypt($mObj->_GET['ta_id']);
         $requestData['unit_id']    = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_id']);
         $requestData['unit_nama']  = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_nama']);
         $requestData['program_id'] = Dispatcher::Instance()->Decrypt($mObj->_GET['program_id']);
         $requestData['program']    = Dispatcher::Instance()->Decrypt($mObj->_GET['program']);
         $requestData['tanggal']    = date('Y-m-d', strtotime(Dispatcher::Instance()->Decrypt($mObj->_GET['tanggal'])));
      }else{
         $requestData['ta_id']      = $periodeTahun[0]['id'];
         $requestData['unit_id']    = $unitKerja['id'];
         $requestData['unit_nama']  = $unitKerja['nama'];
         $requestData['program_id'] = '';
         $requestData['program']    = '';
         $requestData['tanggal']    = date('Y-m-d', mktime(0,0,0, $curr_mon, 1, $curr_year));
      }
      $requestData['tanggal']    = date('Y-m-d', mktime(0,0,0, $curr_mon, 1, $curr_year));

      foreach ($arrPeriodeTahun as $ta) {
         if((int)$ta['id'] === (int)$requestData['ta_id']){
            $requestData['ta_nama']    = $ta['name'];
         }
      }

      if (method_exists(Dispatcher::Instance(), 'getQueryString')) {
         # @param array
         $queryString   = Dispatcher::instance()->getQueryString($requestData);
      } else {
         $query         = array();
         foreach ($requestData as $key => $value) {
            $query[$key]   = Dispatcher::Instance()->Encrypt($value);
         }
         $queryString   = urldecode(http_build_query($query));
      }

      $offset     = 0;
      $limit      = 2000000;
      $page       = 0;
      if(isset($_GET['page'])){
         $page    = (string) $_GET['page']->StripHtmlTags()->SqlString()->Raw();
         $offset  = ($page - 1) * $limit;
      }
      #paging url
      $url        = Dispatcher::Instance()->GetUrl(
        Dispatcher::Instance()->mModule,
        Dispatcher::Instance()->mSubModule,
        Dispatcher::Instance()->mAction,
        Dispatcher::Instance()->mType
      ).'&search='.Dispatcher::Instance()->Encrypt(1).'&'.$queryString;

      $destination_id      = "subcontent-element";
      $dataList            = $mObj->getDataAnggaranBelanjaBulanan($offset, $limit, $requestData);
      $dataRealisasiPengeluaran = $mObj->GetDataRealisasiPengeluaran($offset, $limit, $requestData);     
      $total_data          = $mObj->Count();

      #send data to pagging component
      // Messenger::Instance()->SendToComponent(
      //    'paging',
      //    'Paging',
      //    'view',
      //    'html',
      //    'paging_top',
      //    array(
      //       $limit,
      //       $total_data,
      //       $url,
      //       $page,
      //       $destination_id
      //    ),
      //    Messenger::CurrentRequest
      // );


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
            'id="cmb_periode_tahun"'
         ), Messenger::CurrentRequest
      );

      # GTFW Tanggal
      Messenger::Instance()->SendToComponent(
         'tanggal',
         'Tanggal',
         'view',
         'html',
         'tanggal',
         array(
            $requestData['tanggal'],
            $tahun_awal,
            $tahun_akhir,
            false,
            false,
            true
         ),
         Messenger::CurrentRequest
      );

      $return['request_data']    = $requestData;
      $return['unit_kerja']      = $unitKerja;
      $return['data_list']       = $dataList; 
      $return['data_realisasi_pengeluaran'] = $dataRealisasiPengeluaran;
      $return['start']           = $offset+1;
      $return['months']          = $mObj->indonesianMonth;
      $return['query_string']    = $queryString;
      return $return;
   }

   function ParseTemplate($data = null){
      $unitKerja     = $data['unit_kerja'];
      $requestData   = $data['request_data'];
      $dataList      = $data['data_list'];
      $dataRealisasiPengeluaran = $data['data_realisasi_pengeluaran'];
      $start         = $data['start'];
      $months        = $data['months'];
      $queryString   = $data['query_string'];
      $urlSearch     = Dispatcher::Instance()->GetUrl(
         'laporan_rekap_penerimaan_pengeluaran_bulanan',
         'LaporanRekapPenerimaanPengeluaranBulanan',
         'view',
         'html'
      );
      $urlPopupUnit  = Dispatcher::Instance()->GetUrl(
         'laporan_rekap_penerimaan_pengeluaran_bulanan',
         'PopupUnitKerja',
         'view',
         'html'
      );

      $urlPopupProgram  = Dispatcher::Instance()->GetUrl(
         'laporan_rekap_penerimaan_pengeluaran_bulanan',
         'PopupProgram',
         'view',
         'html'
      );

      $urlExportExcel   = Dispatcher::Instance()->GetUrl(
         'laporan_rekap_penerimaan_pengeluaran_bulanan',
         'LaporanRekapPenerimaanPengeluaranBulanan',
         'view',
         'xlsx'
      ).'&'.$queryString;

      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);
      $this->mrTemplate->AddVar('content', 'URL_POPUP_PROGRAM', $urlPopupProgram);
      $this->mrTemplate->AddVars('content', $requestData);
      $this->mrTemplate->AddVar('data_unit', 'TYPE', strtoupper($unitKerja['status']));
      $this->mrTemplate->AddVar('data_unit', 'UNIT_ID', $requestData['unit_id']);
      $this->mrTemplate->AddVar('data_unit', 'UNIT_NAMA', $requestData['unit_nama']);
      $this->mrTemplate->AddVar('data_unit', 'URL_POPUP_UNIT', $urlPopupUnit);
      $this->mrTemplate->AddVar('content_link', 'EXPORT_EXCEL', $urlExportExcel);
      $this->mrTemplate->addRows('header_months', $months);
      $this->mrTemplate->addRows('header_months_2', $months);
      

      if(empty($dataList)){
         $this->mrTemplate->SetAttribute('content_link', 'visibility', 'hidden');
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      }else{
         $this->mrTemplate->SetAttribute('content_link', 'visibility', 'visible');
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         $dataGrid      = array();
         $program       = '';
         $kegiatan      = '';
         $subkegiatan   = '';
         $index         = 0;
         $rkt           = array(); // untuk menyimpan nominal rkat
         $rkt_nominal   = array();
         for ($i=0; $i < count($dataList);) {
            if((int)$program === (int)$dataList[$i]['program_id'] && (int)$kegiatan === (int)$dataList[$i]['kegiatan_id'] && (int)$subkegiatan === (int)$dataList[$i]['sub_kegiatan_id']){
               $index--;
               $programKodeSistem      = $program.'.0.0.0';
               $kegiatanKodeSistem     = $program.'.'.$kegiatan.'.0.0';
               $subKegiatanKodeSistem  = $program.'.'.$kegiatan.'.'.$subkegiatan.'.0';
               $kodeSistem             = $program.'.'.$kegiatan.'.'.$subkegiatan.'.'.$dataList[$i]['id'];
               $idx      = 0;
               foreach ($months as $m) {
                  $rkat[$kodeSistem][$m['id']]                 = $m;
                  if((int)$dataList[$i]['bulan'] == (int)$m['id']){
                     $rkat[$kodeSistem][$idx]['nominal']   = $dataList[$i]['nominal'];
                     $rkat[$programKodeSistem][$idx]['nominal']     += $dataList[$i]['nominal'];
                     $rkat[$kegiatanKodeSistem][$idx]['nominal']    += $dataList[$i]['nominal'];
                     $rkat[$subKegiatanKodeSistem][$idx]['nominal'] += $dataList[$i]['nominal'];
                    
                  }else{
                     $rkat[$kodeSistem][$idx]['nominal']   = 0;
                  }
                  
                  //$rkat[$kodeSistem][$idx]['nominal_realisasi'] = $dataRealisasiPengeluaran[$dataList[$i]['ta_id']][$dataList[$i]['unit_id']][$dataList[$i]['program_id']][$dataList[$i]['kegiatan_id']][$dataList[$i]['sub_kegiatan_id']][$m['id']];   
                  $idx+=1;
               }

               $rkt_nominal[$programKodeSistem]['nominal']       += $dataList[$i]['nominal'];
               $rkt_nominal[$kegiatanKodeSistem]['nominal']      += $dataList[$i]['nominal'];
               $rkt_nominal[$subKegiatanKodeSistem]['nominal']   += $dataList[$i]['nominal'];
               $i++;
            } elseif((int)$program === (int)$dataList[$i]['program_id'] && (int)$kegiatan === (int)$dataList[$i]['kegiatan_id'] && (int)$subkegiatan !== (int)$dataList[$i]['sub_kegiatan_id']){
               $subkegiatan            = (int)$dataList[$i]['sub_kegiatan_id'];
               $programKodeSistem      = $program.'.0.0.0';
               $kegiatanKodeSistem     = $program.'.'.$kegiatan.'.0.0';
               $kodeSistem             = $program.'.'.$kegiatan.'.'.$dataList[$i]['sub_kegiatan_id'].'.0';
               $rkt_nominal[$subKegiatanKodeSistem]['nominal']    = 0;

               $dataGrid[$index]['id']          = $dataList[$i]['id'];
               $dataGrid[$index]['parent_id']   = $dataList[$i]['keg_id'];
               $dataGrid[$index]['program_id']  = $dataList[$i]['program_id'];
               $dataGrid[$index]['kegiatan_id'] = $dataList[$i]['kegiatan_id'];
               $dataGrid[$index]['kode']        = $dataList[$i]['sub_kegiatan_kode'];
               $dataGrid[$index]['nama']        = $dataList[$i]['sub_kegiatan_nama'];
               $dataGrid[$index]['type']        = 'SUB_KEGIATAN';
               $dataGrid[$index]['nomor']       = $start;
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['deskripsi']   = $dataList[$i]['deskripsi'];
               $dataGrid[$index]['nominal']     = $dataList[$i]['nominal'];
               $dataGrid[$index]['ta_id']       = $dataList[$i]['ta_id'];
               $dataGrid[$index]['ta_nama']     = $dataList[$i]['ta_nama'];
               $dataGrid[$index]['unit_id']     = $dataList[$i]['unit_id'];
               $dataGrid[$index]['unit_kode']   = $dataList[$i]['unit_kode'];
               $dataGrid[$index]['unit_nama']   = $dataList[$i]['unit_nama'];
               $dataGrid[$index]['jenis_kegiatan'] = $dataList[$i]['jenis_kegiatan_nama'];
               $dataGrid[$index]['prioritas']      = strtoupper($dataList[$i]['prioritas']);
               $dataGrid[$index]['status_approve'] = strtoupper($dataList[$i]['approval']);
               $dataGrid[$index]['class_name']     = ($start % 2 <> 0) ? 'table-common-even' : '';
               $dataGrid[$index]['rkat']           = $dataList[$i]['rkat'];
               $dataGrid[$index]['ta_aktif']       = $dataList[$i]['ta_aktif'];
               $dataGrid[$index]['ta_open']        = $dataList[$i]['ta_open'];
               $dataGrid[$index]['mon']            = (int)$dataList[$i]['bulan'];
               $dataGrid[$index]['year']           = (int)$dataList[$i]['tahun'];
               $dataGrid[$index]['bulan']          = $months[(int)$dataList[$i]['bulan']]['name'];
               
               
               $idx      = 0;
               foreach ($months as $m) {
                  $rkat[$kodeSistem][$idx]             = $m;
                  $rkat[$kodeSistem][$idx]['nominal']  = 0;
                  $rkat[$kodeSistem][$idx]['nominal_realisasi'] =$dataRealisasiPengeluaran[$dataList[$i]['ta_id']][$dataList[$i]['unit_id']][$dataList[$i]['program_id']][$dataList[$i]['kegiatan_id']][$dataList[$i]['sub_kegiatan_id']][$m['id']];
                  $idx+=1;
               }

               $start++;
            }elseif((int)$program === (int)$dataList[$i]['program_id'] && (int)$kegiatan !== (int)$dataList[$i]['kegiatan_id']){
               $kegiatan      = (int)$dataList[$i]['kegiatan_id'];
               $kodeSistem    = $program.'.'.$kegiatan.'.0.0';
               $rkt_nominal[$kodeSistem]['nominal']     = 0;
               $dataGrid[$index]['kode']        = $dataList[$i]['kegiatan_kode'];
               $dataGrid[$index]['nama']        = $dataList[$i]['kegiatan_nama'];
               $dataGrid[$index]['type']        = 'KEGIATAN';
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['class_name']  = 'table-common-even2';
               $dataGrid[$index]['row_style']   = 'font-weight: bold; font-style: italic;';
               $idx      = 0;
               foreach ($months as $m) {
                  $rkat[$kodeSistem][$idx]             = $m;
                  $rkat[$kodeSistem][$idx]['nominal']  = 0;
                  $rkat[$kodeSistem][$idx]['nominal_realisasi'] = 0;
                  $idx+=1;
               }
            }else{
               $program       = (int)$dataList[$i]['program_id'];
               $kodeSistem    = $program.'.0.0.0';

               $rkt_nominal[$kodeSistem]['nominal']     = 0;
               $dataGrid[$index]['kode']        = $dataList[$i]['program_kode'];
               $dataGrid[$index]['nama']        = $dataList[$i]['program_nama'];
               $dataGrid[$index]['type']        = 'PROGRAM';
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['class_name']  = 'table-common-even1';
               $dataGrid[$index]['row_style']   = 'font-weight: bold;';
               $idx      = 0;
               foreach ($months as $m) {
                  $rkat[$kodeSistem][$idx]             = $m;
                  $rkat[$kodeSistem][$idx]['nominal']  = 0;
                  $rkat[$kodeSistem][$idx]['nominal_realisasi'] = 0;
                  $idx+=1;
               }
            }

            $index++;
         }

         foreach ($dataGrid as $grid) {
            $this->mrTemplate->clearTemplate('month_list');
            switch (strtoupper($grid['type'])) {
               case 'PROGRAM':
                  if($grid['nominal'] < 0){
                     $grid['nominal']  = '('.number_format(abs($rkt_nominal[$grid['kode_sistem']]['nominal']), 0, ',','.').')';
                  }else{
                     $grid['nominal']  = number_format($rkt_nominal[$grid['kode_sistem']]['nominal'], 0, ',','.');

                  }
                  break;
               case 'KEGIATAN':
                  if($grid['nominal'] < 0){
                     $grid['nominal']  = '('.number_format(abs($rkt_nominal[$grid['kode_sistem']]['nominal']), 0, ',','.').')';
                  }else{
                     $grid['nominal']  = number_format($rkt_nominal[$grid['kode_sistem']]['nominal'], 0, ',','.');

                  }
                  break;
               case 'SUB_KEGIATAN':
                  if($grid['nominal'] < 0){
                     $grid['nominal']  = '('.number_format(abs($grid['nominal']), 0, ',','.').')';
                  }else{
                     $grid['nominal']  = number_format($grid['nominal'], 0, ',','.');
                  }
                  break;
               default:
                  $grid['nominal']     = 0;
                  break;
            }

            foreach ($rkat[$grid['kode_sistem']] as $key => $value) {
               if($value['nominal'] < 0){
                  $rkat[$grid['kode_sistem']][$key]['nominal']    = '('.number_format(abs($value['nominal']), 2, ',','.').')';
               }else{
                  $rkat[$grid['kode_sistem']][$key]['nominal']    = number_format($value['nominal'], 2, ',','.');
               }
               
               if($value['nominal_realisasi'] < 0){
                  $rkat[$grid['kode_sistem']][$key]['nominal_realisasi']    = '('.number_format(abs($value['nominal_realisasi']), 2, ',','.').')';
               }else{
                  $rkat[$grid['kode_sistem']][$key]['nominal_realisasi']    = number_format($value['nominal_realisasi'], 2, ',','.');
               }   
            }
          
            $this->mrTemplate->addRows('month_list', $rkat[$grid['kode_sistem']]);
            $this->mrTemplate->AddVars('data_list', $grid);
            $this->mrTemplate->parseTemplate('data_list', 'a');
         }

      }
   }
}
?>