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
'module/lap_realisasi_penerimaan_pengeluaran/business/LapRealisasiPenerimaanPengeluaran.class.php';
require_once GTFWConfiguration::GetValue('application','docroot').
'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewLapRealisasiPenerimaanPengeluaran extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/lap_realisasi_penerimaan_pengeluaran/template/');
      $this->SetTemplateFile('view_lap_realisasi_penerimaan_pengeluaran.html');
   }

   function ProcessRequest(){
      $mObj       = new LapRealisasiPenerimaanPengeluaran();
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
      $dataList         = $mObj->getDataPenerimaanPengeluaran($offset, $limit, (array)$requestData);
      $total_data       = $mObj->Count();
      
      $dataPengeluaranBulan = $mObj->getPengeluaranPerBulan($offset, $limit, (array)$requestData);
      $dataTotalPengeluaranBulan = $mObj->getTotalPengeluaranPerBulan((array)$requestData);
      
      $dataTotalPenerimaan = $mObj->getTotalPenerimaan($offset, $limit, (array)$requestData);
      $dataTotalPengeluaran = $mObj->getTotalPengeluaran($offset, $limit, (array)$requestData);
        
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
        
        
      $return['get_header_bulan'] = $mObj->getHeaderBulan($requestData['start_date'],$requestData['end_date']);
      
      $return['unit_kerja']      = $unitKerja;
      $return['request_data']    = $requestData;
      $return['data_list']       = $dataList;
      $return['data_total_penerimaan'] = $dataTotalPenerimaan;
      $return['data_total_pengeluaran'] = $dataTotalPengeluaran;
      $return['data_pengeluaran_bulan'] = $dataPengeluaranBulan;
      
      $return['data_total_pengeluaran_bulan'] = $dataTotalPengeluaranBulan;
      
      $return['start']           = $offset+1;
      $return['query_string']    = $queryString;
      return $return;
   }

   function ParseTemplate($data = null){
      $getHeaderBulan = $data['get_header_bulan'];
      $unitKerja      = $data['unit_kerja'];
      $requestData    = $data['request_data'];
      $dataList       = $data['data_list'];
      $dataTotalPenerimaan = $data['data_total_penerimaan'];
      $dataTotalPengeluaran = $data['data_total_pengeluaran'];
      $dataPengeluaranBulan = $data['data_pengeluaran_bulan'];
      
      $dataTotalPengeluaranBulan = $data['data_total_pengeluaran_bulan'];
      
      $start         = $data['start'];
      $queryString   = $data['query_string'];
      $urlSearch     = Dispatcher::Instance()->GetUrl(
         'lap_realisasi_penerimaan_pengeluaran',
         'LapRealisasiPenerimaanPengeluaran',
         'view',
         'html'
      );

      $urlPopupUnit  = Dispatcher::Instance()->GetUrl(
         'lap_realisasi_penerimaan_pengeluaran',
         'PopupUnitKerja',
         'view',
         'html'
      );

      $urlExport     = Dispatcher::Instance()->GetUrl(
         'lap_realisasi_penerimaan_pengeluaran',
         'LapRealisasiPenerimaanPengeluaran',
         'view',
         'xlsx'
      ).'&'.$queryString;

      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);
      $this->mrTemplate->AddVar('content', 'URL_EXPORT_EXCEL', $urlExport);
      $this->mrTemplate->AddVar('data_unit', 'LEVEL', strtoupper($unitKerja['status']));
      $this->mrTemplate->AddVar('data_unit', 'UNIT_ID', $requestData['unit_id']);
      $this->mrTemplate->AddVar('data_unit', 'UNIT_NAMA', $requestData['unit_nama']);
      $this->mrTemplate->AddVar('data_unit', 'URL_POPUP_UNIT', $urlPopupUnit);

      //create header bulan
      if(!empty($getHeaderBulan)){
        $countColsBulan = (2 * (sizeof($getHeaderBulan)));
        $this->mrTemplate->AddVar('section_header_bulan_top', 'DATA_EMPTY', 'NO');
        $this->mrTemplate->AddVar('section_header_bulan', 'DATA_EMPTY', 'NO');
        $this->mrTemplate->AddVar('section_header_bulan_top', 'COLSPAN_BULAN', $countColsBulan );
        $this->mrTemplate->AddVar('content','HEADER_ROWSPAN','rowspan="3"');
        foreach($getHeaderBulan as $bulan){
           $this->mrTemplate->AddVars('header_bulan', $bulan,'');
           $this->mrTemplate->parseTemplate('header_bulan', 'a');
           $this->mrTemplate->parseTemplate('header_bulan_keterangan', 'a');
        }
      } else {
        $this->mrTemplate->AddVar('section_header_bulan_top', 'DATA_EMPTY', 'YES');
        $this->mrTemplate->AddVar('section_header_bulan', 'DATA_EMPTY', 'YES');
        $this->mrTemplate->AddVar('content','HEADER_ROWSPAN','');
      }
      
      if (empty($dataList)) {
         $this->mrTemplate->AddVar('data_grid', 'DATA_COLSPAN', ($countColsBulan  + 3));
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      } else {
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');

         $program          = '';
         $kegiatan         = '';
         $index            = 0;
         $dataRealisasi    = array();
         $dataGrid         = array();
         
         $sectionP =  '';
         $sectionPn =  '';
         
         for ($i=0; $i < count($dataList);) {
            if($dataList[$i]['section'] =='PENERIMAAN') {
                if($sectionPn == $dataList[$i]['section']) {
                    $dataGrid[$index]['section_id']     = $dataList[$i]['section_id'];
                    $dataGrid[$index]['section']        = $dataList[$i]['section'];
                    $dataGrid[$index]['id_kode']        = $dataList[$i]['id_kode'];
                    $dataGrid[$index]['nomor']          = '1.'.$start;
                    $dataGrid[$index]['kode']           = $dataList[$i]['kode'];
                    $dataGrid[$index]['nama']           = $dataList[$i]['nama'];
                    $dataGrid[$index]['nominal_total_usulan'] = $dataList[$i]['nominal_total_usulan'];
                    $dataGrid[$index]['tipe']           = 'kode_penerimaan';
                    $dataGrid[$index]['class_name']     = $start % 2 <> 0 ? 'table-common-even' : '';
                    $dataGrid[$index]['row_style']      = '';                
                    $start++;
                    $i++;
                 } elseif($sectionPn != $dataList[$i]['section']) {
                    $sectionPn = $dataList[$i]['section'];
                    $dataGrid[$index]['section_id']     = $dataList[$i]['section_id'];
                    $dataGrid[$index]['section']        = $dataList[$i]['section'];
                    $dataGrid[$index]['id_kode']        = $dataList[$i]['id_kode'];
                    $dataGrid[$index]['nomor']          = '1';
                    $dataGrid[$index]['kode']           = '';
                    $dataGrid[$index]['nama']           = $dataList[$i]['section'];
                    $dataGrid[$index]['tipe']        = 'header';                    
                    $dataGrid[$index]['class_name']  = 'table-common-even2';
                    $dataGrid[$index]['row_style']   = 'font-weight: bold;';    
                     $start = 1;             
                 }
            } else {
            
            if($sectionP == $dataList[$i]['section']){
               $dataGrid[$index]['section_id']     = $dataList[$i]['section_id'];
               $dataGrid[$index]['section']        = $dataList[$i]['section'];
               $dataGrid[$index]['id_kode']        = $dataList[$i]['id_kode'];
               $dataGrid[$index]['nomor']    = '2.'.$start;
               $dataGrid[$index]['id']       = $dataList[$i]['id'];
               $dataGrid[$index]['tanggal']  = date('Y-m-d', strtotime($dataList[$i]['tanggal']));
               $dataGrid[$index]['kode']           = $dataList[$i]['kode'];
               $dataGrid[$index]['nama']           = $dataList[$i]['nama'];
               $dataGrid[$index]['nominal_total_usulan'] = $dataList[$i]['nominal_total_usulan'];
               $dataGrid[$index]['status']         = strtoupper($dataList[$i]['status']);
               $dataGrid[$index]['spp']            = $dataList[$i]['spp'];
               $dataGrid[$index]['spp_id']         = $dataList[$i]['spp_id'];
               $dataGrid[$index]['tipe']           = 'unit';
               $dataGrid[$index]['class_name']     = $start % 2 <> 0 ? 'table-common-even' : '';
               $dataGrid[$index]['row_style']      = '';
               $i++;
               $start++;
            
            }elseif($sectionP != $dataList[$i]['section']) {
               $sectionP = $dataList[$i]['section'];
               $dataGrid[$index]['section_id']     = $dataList[$i]['section_id'];
               $dataGrid[$index]['section']        = $dataList[$i]['section'];
               $dataGrid[$index]['id_kode']        = $dataList[$i]['id_kode'];
               $dataGrid[$index]['nomor']          = '2';
               $dataGrid[$index]['kode']           = '';
               $dataGrid[$index]['nama']           = $dataList[$i]['section'];
               $dataGrid[$index]['tipe']        = 'header';                    
               $dataGrid[$index]['class_name']  = 'table-common-even2';
               $dataGrid[$index]['row_style']   = 'font-weight: bold;';            
                $start = 1;     
            }
           
            } 
            
            $index++;
         }

         foreach ($dataGrid as $key => $list) {             
                 
                 
                 if($list['tipe'] != 'header'){
                     $list['nominal_total_usulan'] =   number_format($list ['nominal_total_usulan'], 0, ',','.');
                 }
                 
                 if(isset($dataGrid[$key + 1]['section'])) {
                    $cek = $dataGrid[$key + 1]['section'];
                 } else {
                    $cek = null;
                 }
                   
                 if($list['section']  != $cek){
                    $this->mrTemplate->SetAttribute('total', 'visibility', 'visible');
                    if($cek != null){
                        $this->mrTemplate->SetAttribute('space', 'visibility', 'visible');
                    }
                    $this->mrTemplate->AddVar('total', 'SECTION',$list['section']);
                    } else {
                        $this->mrTemplate->SetAttribute('total', 'visibility', 'hidden');  
                        $this->mrTemplate->SetAttribute('space', 'visibility', 'hidden');
                    } 
                  
                if($list['section'] ==  'PENERIMAAN') {
                    $dataTotal = $dataTotalPenerimaan;
                } else {
                    $dataTotal = $dataTotalPengeluaran;
                }
                
                //$dtp['nominal_total_usulan'] =   number_format($dataTotal['nominal_total_usulan'], 0, ',','.');

               
               
                //$nominalUsulan = array();   
                $nominal ='';
                $space =  '';
                if($list['section'] ==  'PENERIMAAN') {
                    $total = '<td style="text-align:right;">'.  number_format($dataTotalPengeluaranBulan[$list['section_id']]['nominal_total_usulan'], 0, ',','.').'</td>';
                } else {
                    $total = '<td style="text-align:right;">'.  number_format($dataTotalPengeluaranBulan[$list['section_id']]['nominal_total_usulan'], 0, ',','.').'</td>';
                }            
                
                foreach($getHeaderBulan as $key => $bulan) {
                   if($list['tipe'] != 'header'){
                        $nominal .= '<td style="text-align:right;">'.  number_format($dataPengeluaranBulan[$list['section_id']][$bulan['kode_bulan']][$list['id_kode']]['nominal_usulan'], 0, ',','.').'</td>';
                        $nominal .= '<td style="text-align:right;">'.  number_format($dataPengeluaranBulan[$list['section_id']][$bulan['kode_bulan']][$list['id_kode']]['nominal_realisasi'], 0, ',','.').'</td>';               
                    }else {
                        $nominal .= '<td style="text-align:right;"></td>';
                        $nominal .= '<td style="text-align:right;"></td>';     
                    }                    
                    $list['nominal'] = $nominal;
                    
                    if($list['section'] ==  'PENERIMAAN') {
                        $total .= '<td style="text-align:right;">'. number_format($dataTotalPengeluaranBulan[$list['section_id']][$bulan['kode_bulan']]['nominal_usulan'], 0, ',','.').'</td>';
                        $total .= '<td style="text-align:right;">'. number_format($dataTotalPengeluaranBulan[$list['section_id']][$bulan['kode_bulan']]['nominal_realisasi'], 0, ',','.').'</td>';
                        $space .= '<td style="text-align:right;"></td><td style="text-align:right;"></td>';   
                    } else {
                        $total .= '<td style="text-align:right;">'.  number_format($dataTotalPengeluaranBulan[$list['section_id']][$bulan['kode_bulan']]['nominal_usulan'], 0, ',','.').'</td>';
                        $total .= '<td style="text-align:right;">'.  number_format($dataTotalPengeluaranBulan[$list['section_id']][$bulan['kode_bulan']]['nominal_realisasi'], 0, ',','.').'</td>';
                        $space .= '<td style="text-align:right;"></td><td style="text-align:right;"></td>';
                    }                
                           
                }
            $this->mrTemplate->AddVar('total','TOTAL',$total);
            $this->mrTemplate->AddVar('space','SPACE',$space);
            $this->mrTemplate->AddVars('data_list', $list);
            $this->mrTemplate->parseTemplate('data_list', 'a');
         }
      }
   }
}
?>