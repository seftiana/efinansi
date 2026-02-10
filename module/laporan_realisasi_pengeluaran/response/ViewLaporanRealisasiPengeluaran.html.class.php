<?php
/**
* ================= doc ====================
* FILENAME     : ViewLaporanRealisasiPengeluaran.html.class.php
* @package     : ViewLaporanRealisasiPengeluaran
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-03-13
* @Modified    : 2015-03-13
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/laporan_realisasi_pengeluaran/business/LaporanRealisasiPengeluaran.class.php';
require_once GTFWConfiguration::GetValue('application','docroot').
'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewLaporanRealisasiPengeluaran extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/laporan_realisasi_pengeluaran/template/');
      $this->SetTemplateFile('view_laporan_realisasi_pengeluaran.html');
   }

   function ProcessRequest(){
      $mObj       = new LaporanRealisasiPengeluaran();
      $userId     = $mObj->getUserId();
      $mUnitObj   = new UserUnitKerja();
      $unitKerja  = $mUnitObj->GetUnitKerjaRefUser($userId);
      $arrPeriodeTahun  = $mObj->getPeriodeTahun();
      $periodeTahun     = $mObj->getPeriodeTahun(array('active' => true));
      $dateRange        = $mObj->getDateRange();
      $requestData      = array();
      $minYear          = $dateRange['min_year'];
      $maxYear          = $dateRange['max_year'];

      if(isset($mObj->_POST['btnSearch'])){
         $startDateDay  = (int)$mObj->_POST['start_date_day'];
         $startDateMon  = (int)$mObj->_POST['start_date_mon'];
         $startDateYear = (int)$mObj->_POST['start_date_year'];
         $endDateDay    = (int)$mObj->_POST['end_date_day'];
         $endDateMon    = (int)$mObj->_POST['end_date_mon'];
         $endDateYear   = (int)$mObj->_POST['end_date_year'];

         $requestData['ta_id']      = $mObj->_POST['periode_tahun'];
         $requestData['unit_id']    = $mObj->_POST['unit_id'];
         $requestData['unit_nama']  = $mObj->_POST['unit_nama'];
         $requestData['start_date'] = date('Y-m-d', mktime(0,0,0, $startDateMon, $startDateDay, $startDateYear));
         $requestData['end_date']   = date('Y-m-d', mktime(0,0,0, $endDateMon, $endDateDay, $endDateYear));
      }elseif(isset($mObj->_GET['search'])){
         $requestData['ta_id']      = Dispatcher::Instance()->Decrypt($mObj->_GET['ta_id']);
         $requestData['unit_id']    = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_id']);
         $requestData['unit_nama']  = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_nama']);
         $requestData['start_date'] = date('Y-m-d', strtotime(Dispatcher::Instance()->Decrypt($mObj->_GET['start_date'])));
         $requestData['end_date']   = date('Y-m-d', strtotime(Dispatcher::Instance()->Decrypt($mObj->_GET['end_date'])));
      }else{
         $requestData['ta_id']      = $periodeTahun[0]['id'];
         $requestData['unit_id']    = $unitKerja['id'];
         $requestData['unit_nama']  = $unitKerja['nama'];
         $requestData['start_date'] = date('Y-m-d', strtotime($dateRange['start_date']));
         $requestData['end_date']   = date('Y-m-d', strtotime($dateRange['end_date']));
      }

      foreach ($arrPeriodeTahun as $ta) {
         if((int)$ta['id'] === (int)$requestData['ta_id']){
            $requestData['ta_nama'] = $ta['name'];
         }
      }

      if(method_exists(Dispatcher::Instance(), 'getQueryString')){
         # @param array
         $queryString   = Dispatcher::instance()->getQueryString($requestData);
      }else{
         $query         = array();
         foreach ($requestData as $key => $value) {
            $query[$key]   = Dispatcher::Instance()->Encrypt($value);
         }
         $queryString      = urldecode(http_build_query($query));
      }

      $offset     = 0;
      $limit      = 20;
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

      $destination_id   = "subcontent-element";
      $dataList         = $mObj->getData($offset, $limit, (array)$requestData);
      #echo "<pre>";
      #print_r($dataList);exit();
      $total_data       = $mObj->Count();

      #send data to pagging component
      Messenger::Instance()->SendToComponent(
         'paging',
         'Paging',
         'view',
         'html',
         'paging_top',
         array(
            $limit,
            $total_data,
            $url,
            $page,
            $destination_id
         ),
         Messenger::CurrentRequest
      );

      # Combobox
      Messenger::Instance()->SendToComponent(
         'combobox',
         'Combobox',
         'view',
         'html',
         'periode_tahun',
         array(
            'periode_tahun',
            $arrPeriodeTahun,
            $requestData['ta_id'],
            false,
            'id="cmb_periode_tahun"'
         ),
         Messenger::CurrentRequest
      );

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
            false
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
            false
         ),
         Messenger::CurrentRequest
      );

      $return['unit_kerja']      = $unitKerja;
      $return['request_data']    = $requestData;
      $return['data_list']       = $dataList;
      $return['start']           = $offset+1;
      $return['query_string']    = $queryString;
      return $return;
   }

   function ParseTemplate($data = null){
      $unitKerja     = $data['unit_kerja'];
      $requestData   = $data['request_data'];
      $dataList      = $data['data_list'];
      $start         = $data['start'];
      $queryString   = $data['query_string'];
      $urlSearch     = Dispatcher::Instance()->GetUrl(
         'laporan_realisasi_pengeluaran',
         'LaporanRealisasiPengeluaran',
         'view',
         'html'
      );

      $urlPopupUnit  = Dispatcher::Instance()->GetUrl(
         'laporan_realisasi_pengeluaran',
         'PopupUnitKerja',
         'view',
         'html'
      );

      $urlExport     = Dispatcher::Instance()->GetUrl(
         'laporan_realisasi_pengeluaran',
         'LaporanRealisasiPengeluaran',
         'view',
         'xlsx'
      ).'&'.$queryString;

      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);
      $this->mrTemplate->AddVar('content', 'URL_EXPORT_EXCEL', $urlExport);
      $this->mrTemplate->AddVar('data_unit', 'LEVEL', strtoupper($unitKerja['status']));
      $this->mrTemplate->AddVar('data_unit', 'UNIT_ID', $requestData['unit_id']);
      $this->mrTemplate->AddVar('data_unit', 'UNIT_NAMA', $requestData['unit_nama']);
      $this->mrTemplate->AddVar('data_unit', 'URL_POPUP_UNIT', $urlPopupUnit);

      if (empty($dataList)) {
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      } else {
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');

         $program          = '';
         $kegiatan         = '';
         $index            = 0;
         $dataRealisasi    = array();
         $dataGrid         = array();

         for ($i=0; $i < count($dataList);) {
            if((int)$dataList[$i]['program_id'] === (int)$program && (int)$dataList[$i]['kegiatan_id'] === (int)$kegiatan){
               $kodeSistemProgram            = $program;
               $kodeSistemKegiatan           = $program.'.'.$kegiatan;
               
               $dataGrid[$index]['id']       = $dataList[$i]['id'];
               $dataGrid[$index]['tanggal']  = date('Y-m-d', strtotime($dataList[$i]['tanggal']));              
               $dataGrid[$index]['kid']      = $dataList[$i]['kid'];
               $dataGrid[$index]['id']       = $dataList[$i]['id'];
               if(($i > 0) && ($dataList[$i - 1]['kid'] == $dataList[$i]['kid'])){
                    $dataGrid[$index]['nomor']    = $start;
                    $start++;                
                    $dataGrid[$index]['kode']     = '';
                    $dataGrid[$index]['nama']     = '';
                    $dataGrid[$index]['nominal_anggaran_awal'] = '';
                    $dataGrid[$index]['nominal_anggaran_revisi'] = '';
                    $dataGrid[$index]['nominal_anggaran_setuju']  = '';
                    $dataGrid[$index]['nominal_sisa_saldo']       = '';
                    $dataGrid[$index]['class_name']     = $start % 2 <> 0 ? 'table-common-even' : '';
                } else {
                    $dataGrid[$index]['nomor']    = $start;
                    $start++;
                    $dataGrid[$index]['kode']     = $dataList[$i]['sub_kegiatan_kode'];
                    $dataGrid[$index]['nama']     = $dataList[$i]['sub_kegiatan_nama'];
                    $dataGrid[$index]['nominal_anggaran_awal']   = $dataList[$i]['nominal_anggaran_awal'];
                    $dataGrid[$index]['nominal_anggaran_revisi'] = $dataList[$i]['nominal_anggaran_revisi'];
                    $dataGrid[$index]['nominal_anggaran_setuju'] = $dataList[$i]['nominal_anggaran_setuju'];
                    $dataGrid[$index]['nominal_sisa_saldo']      += $dataList[$i]['nominal_anggaran_setuju'] - $dataList[$i]['total_per_subkegiatan'];
                    $dataGrid[$index]['class_name']     = $start % 2 <> 0 ? 'table-common-even' : '';
               }
                
               $dataRealisasi[$kodeSistemProgram]['nominal_usulan']  += $dataList[$i]['nominal_usulan'];
               $dataRealisasi[$kodeSistemProgram]['nominal_setuju']  += $dataList[$i]['nominal_setuju'];
               
               $dataRealisasi[$kodeSistemProgram]['nominal_anggaran_awal']    += $dataGrid[$index]['nominal_anggaran_awal'];
               $dataRealisasi[$kodeSistemProgram]['nominal_anggaran_revisi']  += $dataGrid[$index]['nominal_anggaran_revisi'];
               $dataRealisasi[$kodeSistemProgram]['nominal_anggaran_setuju']  += $dataGrid[$index]['nominal_anggaran_setuju'];

               $dataRealisasi[$kodeSistemKegiatan]['nominal_usulan'] += $dataList[$i]['nominal_usulan'];
               $dataRealisasi[$kodeSistemKegiatan]['nominal_setuju'] += $dataList[$i]['nominal_setuju'];
               
               $dataRealisasi[$kodeSistemKegiatan]['nominal_anggaran_awal']   += $dataGrid[$index]['nominal_anggaran_awal'];
               $dataRealisasi[$kodeSistemKegiatan]['nominal_anggaran_revisi'] += $dataGrid[$index]['nominal_anggaran_revisi'];
               $dataRealisasi[$kodeSistemKegiatan]['nominal_anggaran_setuju'] += $dataGrid[$index]['nominal_anggaran_setuju'];
                            
               $dataGrid[$index]['nominal_usulan'] = $dataList[$i]['nominal_usulan'];
               $dataGrid[$index]['nominal_setuju'] = $dataList[$i]['nominal_setuju'];
               $dataGrid[$index]['status']         = strtoupper($dataList[$i]['status']);
               $dataGrid[$index]['spp']            = $dataList[$i]['spp'];
               $dataGrid[$index]['spp_id']         = $dataList[$i]['spp_id'];
               $dataGrid[$index]['tipe']           = 'sub_kegiatan';
               
               $dataGrid[$index]['row_style']      = '';
               $i++;
            }elseif((int)$dataList[$i]['program_id'] === (int)$program && (int)$dataList[$i]['kegiatan_id'] !== (int)$kegiatan){
               $kegiatan         = (int)$dataList[$i]['kegiatan_id'];
               $kodeSistem       = $program.'.'.$kegiatan;
               $dataRealisasi[$kodeSistem]['nominal_usulan']   = 0;
               $dataRealisasi[$kodeSistem]['nominal_setuju']   = 0;

               $dataRealisasi[$kodeSistem]['nominal_anggaran_awal']  = 0;
               $dataRealisasi[$kodeSistem]['nominal_anggaran_revisi']= 0;
               $dataRealisasi[$kodeSistem]['nominal_anggaran_setuju']= 0;


               $dataGrid[$index]['id']       = $dataList[$i]['kegiatan_id'];
               $dataGrid[$index]['kode']     = $dataList[$i]['kegiatan_kode'];
               $dataGrid[$index]['nama']     = $dataList[$i]['kegiatan_nama'];
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['tipe']        = 'kegiatan';
               $dataGrid[$index]['class_name']  = 'table-common-even2';
               $dataGrid[$index]['row_style']   = '';
            }else{
               $program          = (int)$dataList[$i]['program_id'];
               $kodeSistem       = $program;
               $dataRealisasi[$kodeSistem]['nominal_usulan']   = 0;
               $dataRealisasi[$kodeSistem]['nominal_setuju']   = 0;

               $dataRealisasi[$kodeSistem]['nominal_anggaran_awal']  = 0;
               $dataRealisasi[$kodeSistem]['nominal_anggaran_revisi']= 0;
               $dataRealisasi[$kodeSistem]['nominal_anggaran_setuju']= 0;


               $dataGrid[$index]['id']       = $dataList[$i]['program_id'];
               $dataGrid[$index]['kode']     = $dataList[$i]['program_kode'];
               $dataGrid[$index]['nama']     = $dataList[$i]['program_nama'];
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['tipe']        = 'program';
               $dataGrid[$index]['class_name']  = 'table-common-even1';
               $dataGrid[$index]['row_style']   = 'font-weight: bold;';
            }
            $index++;
         }

         foreach ($dataGrid as $list) {
            switch (strtoupper($list['tipe'])) {
               case 'PROGRAM':
                  $list['nominal_anggaran_awal']  = number_format($dataRealisasi[$list['kode_sistem']]['nominal_anggaran_awal'], 0, ',','.');
                  $list['nominal_anggaran_revisi']  = number_format($dataRealisasi[$list['kode_sistem']]['nominal_anggaran_revisi'], 0, ',','.');
                  $list['nominal_anggaran_setuju']  = number_format($dataRealisasi[$list['kode_sistem']]['nominal_anggaran_setuju'], 0, ',','.');
                  $list['nominal_usulan']    = number_format($dataRealisasi[$list['kode_sistem']]['nominal_usulan'], 0, ',','.');
                  $list['nominal_setuju']    = number_format($dataRealisasi[$list['kode_sistem']]['nominal_setuju'], 0, ',','.');
                  $list['nominal_sisa_saldo'] = number_format(($dataRealisasi[$list['kode_sistem']]['nominal_anggaran_setuju']) - ($dataRealisasi[$list['kode_sistem']]['nominal_setuju']), 0, ',', '.');
                  break;
               case 'KEGIATAN':
                  $list['nominal_anggaran_awal']  = number_format($dataRealisasi[$list['kode_sistem']]['nominal_anggaran_awal'], 0, ',','.');
                  $list['nominal_anggaran_revisi']  = number_format($dataRealisasi[$list['kode_sistem']]['nominal_anggaran_revisi'], 0, ',','.');
                  $list['nominal_anggaran_setuju']  = number_format($dataRealisasi[$list['kode_sistem']]['nominal_anggaran_setuju'], 0, ',','.'); 
                  $list['nominal_usulan']    = number_format($dataRealisasi[$list['kode_sistem']]['nominal_usulan'], 0, ',','.');
                  $list['nominal_setuju']    = number_format($dataRealisasi[$list['kode_sistem']]['nominal_setuju'], 0, ',','.');
                  $list['nominal_sisa_saldo'] = number_format(($dataRealisasi[$list['kode_sistem']]['nominal_anggaran_setuju']) - ($dataRealisasi[$list['kode_sistem']]['nominal_setuju']), 0, ',', '.');
                  break;
               case 'SUB_KEGIATAN':
                  if( $list['nominal_anggaran_awal']!= '') {
                      $list['nominal_anggaran_awal']    = number_format($list['nominal_anggaran_awal'], 0, ',','.');    
                  } else {
                      $list['nominal_anggaran_awal']    = '';
                  } 
                  
                  if($list['nominal_anggaran_revisi'] != '') {
                      $list['nominal_anggaran_revisi']  = number_format($list['nominal_anggaran_revisi'], 0, ',','.');    
                  } else {
                      $list['nominal_anggaran_revisi']  = '';
                  }
                   
                  if($list['nominal_anggaran_setuju'] != ''){
                        $list['nominal_anggaran_setuju']  = number_format($list['nominal_anggaran_setuju'], 0, ',','.');    
                  } else {
                        $list['nominal_anggaran_setuju']  = '';
                  }

                  if($list['nominal_sisa_saldo'] != ''){
                        $list['nominal_sisa_saldo']  = number_format($list['nominal_sisa_saldo'], 0, ',','.');    
                  } else {
                        $list['nominal_sisa_saldo']  = 0;
                  }
                  
                  $list['nominal_usulan']    = number_format($list['nominal_usulan'], 0, ',','.');
                  $list['nominal_setuju']    = number_format($list['nominal_setuju'], 0, ',','.');
                  break;
               default:
                  if( $list['nominal_anggaran_awal']!= '') {
                      $list['nominal_anggaran_awal']    = number_format($list['nominal_anggaran_awal'], 0, ',','.');    
                  } else {
                      $list['nominal_anggaran_awal']    = '';
                  } 
                  
                  if($list['nominal_anggaran_revisi'] != '') {
                      $list['nominal_anggaran_revisi']  = number_format($list['nominal_anggaran_revisi'], 0, ',','.');    
                  } else {
                      $list['nominal_anggaran_revisi']  = '';
                  }
                   
                  if($list['nominal_anggaran_setuju'] != ''){
                        $list['nominal_anggaran_setuju']  = number_format($list['nominal_anggaran_setuju'], 0, ',','.');    
                  } else {
                        $list['nominal_anggaran_setuju']  = '';
                  }
                                     
                  $list['nominal_usulan']    = number_format($list['nominal_usulan'], 0, ',','.');
                  $list['nominal_setuju']    = number_format($list['nominal_setuju'], 0, ',','.');
                  break;
            }

            $this->mrTemplate->AddVars('data_list', $list);
            $this->mrTemplate->parseTemplate('data_list', 'a');
         }
      }
   }
}
?>