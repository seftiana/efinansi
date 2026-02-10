<?php

/**
* ================= doc ====================
* FILENAME     : ViewLaporanKeuanganAkademik.html.class.php
* @package     : ViewLaporanKeuanganAkademik
* scope        : PUBLIC
* @Author      : noor hadi
* @Created     : 2015-04-29
* @Modified    : 2015-04-29
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2015 Gamatechno
* ================= doc ====================
*/

require_once GTFWConfiguration::GetValue('application','docroot').
'module/laporan_keuangan_akademik/business/LaporanKeuanganAkademik.class.php';

class ViewLaporanKeuanganAkademik extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/laporan_keuangan_akademik/template/');
      $this->SetTemplateFile('view_laporan_keuangan_akademik.html');
   }

   function ProcessRequest(){
      $mObj       = new LaporanKeuanganAkademik();
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
         $requestData['m']          =  $tanggal_mon;
      }elseif(isset($mObj->_GET['search'])){
         $requestData['ta_id']      = Dispatcher::Instance()->Decrypt($mObj->_GET['ta_id']);
         $requestData['unit_id']    = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_id']);
         $requestData['unit_nama']  = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_nama']);
         $requestData['program_id'] = Dispatcher::Instance()->Decrypt($mObj->_GET['program_id']);
         $requestData['program']    = Dispatcher::Instance()->Decrypt($mObj->_GET['program']);
         $requestData['tanggal']    = date('Y-m-d', strtotime(Dispatcher::Instance()->Decrypt($mObj->_GET['tanggal'])));
         $requestData['m']          = date('m', strtotime(Dispatcher::Instance()->Decrypt($mObj->_GET['tanggal'])));
      }else{
         $requestData['ta_id']      = $periodeTahun[0]['id'];
         $requestData['unit_id']    = $unitKerja['id'];
         $requestData['unit_nama']  = $unitKerja['nama'];
         $requestData['program_id'] = '';
         $requestData['program']    = '';
         $requestData['tanggal']    = date('Y-m-d', mktime(0,0,0, $curr_mon, 1, $curr_year));
         $requestData['m']          =  $curr_mon;
      }
      
      $getTaDetail = $mObj->GetTahunAnggaranDetailById($requestData['ta_id']);
      //$requestData['tanggal']    = date('Y-m-d', mktime(0,0,0, $curr_mon, 1, $curr_year));
      
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

      $destination_id      = "subcontent-element";
      $dataList            = $mObj->GetDataLaporanKeuanganAkademikLimit($requestData['tanggal'],$offset,$limit);
      $total_data          = $mObj->Count();
     
      $getUnitKerja = $mObj->GetUnitKerja($requestData['ta_id'],(int) $requestData['m']);
     
      //$getJumlahKelasPerUnit = $mObj->GetJumlahKelasPerUnit($requestData['ta_id'],(int) $requestData['m']);
      
    

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

      $return['get_ta_detail']   = $getTaDetail ;
      $return['request_data']    = $requestData;
      $return['unit_kerja']      = $unitKerja;
      $return['data_list']       = $dataList;
      $return['start']           = $offset+1;
      $return['months']          = $mObj->indonesianMonth;
      $return['query_string']    = $queryString;
      $return['get_unit_kerja']  = $getUnitKerja;
     // $return['get_jumlah_kelas_per_unit']  = $getJumlahKelasPerUnit;

           
         
      $return['get_nominal_per_unit_penerimaan'] = $mObj->GetNominalPerUnitPenerimaan($requestData['tanggal'],$requestData['tanggal'],$requestData['ta_id'],(int) $requestData['m']);
      $return['get_nominal_per_unit_pengeluaran'] = $mObj->GetNominalPerUnitPengeluaran($requestData['tanggal'],$requestData['tanggal'],$requestData['ta_id'],(int) $requestData['m']);
      
      $return['get_nominal_per_kelompok_unit_penerimaan'] = $mObj->GetNominalPerKelompokUnitPenerimaan();
      $return['get_nominal_per_kelompok_unit_pengeluaran'] = $mObj->GetNominalPerKelompokUnitPengeluaran();
      //total per peneirmaan pengeluarn      
      $return['get_nominal_per_penerimaan'] = $mObj->GetNominalPerPenerimaan();
      $return['get_nominal_per_pengeluaran'] = $mObj->GetNominalPerPengeluaran();

      $return['get_nominal_per_item_penerimaan_bulan'] = $mObj->GetNominalPerItemPenerimaan($requestData['tanggal'],$requestData['tanggal']);
      $return['get_nominal_per_item_penerimaan_range'] = $mObj->GetNominalPerItemPenerimaanRange($getTaDetail['tanggal_awal'],$requestData['tanggal']);
      
      $return['get_nominal_per_item_pengeluaran_bulan'] = $mObj->GetNominalPerItemPengeluaran($requestData['tanggal'],$requestData['tanggal'],$requestData['ta_id'],(int) $requestData['m']);
      $return['get_nominal_per_item_pengeluaran_range'] = $mObj->GetNominalPerItemPengeluaranRange($getTaDetail['tanggal_awal'],$requestData['tanggal']);
   
         
      $return['get_total_per_penerimaan_bulan'] = $mObj->GetTotalPerPenerimaan();
      $return['get_total_per_penerimaan_range'] = $mObj->GetTotalPerPenerimaanRange();
      $return['get_total_per_pengeluaran_bulan'] = $mObj->GetTotalPerPengeluaran();
      $return['get_total_per_pengeluaran_range'] = $mObj->GetTotalPerPengeluaranRange();
      
      $return['get_total_per_kelompok_penerimaan_bulan'] = $mObj->GetTotalPerKelompokPenerimaan();
      $return['get_total_per_kelompok_penerimaan_range'] = $mObj->GetTotalPerKelompokPenerimaanRange();     
      $return['get_total_per_kelompok_pengeluaran_bulan'] = $mObj->GetTotalPerKelompokPengeluaran();
      $return['get_total_per_kelompok_pengeluaran_range'] = $mObj->GetTotalPerKelompokPengeluaranRange();
           
      $return['get_coa_alokasi_akademik'] = $mObj->GetCoaAlokasiAkademik();
      
      
      return $return;
   }

   function ParseTemplate($data = null){
      $getTaDetail = $data['get_ta_detail']; 
      $unitKerja     = $data['unit_kerja'];
      $requestData   = $data['request_data'];
      $dataList      = $data['data_list'];
      $start         = $data['start'];
      $months        = $data['months'];
      $queryString   = $data['query_string'];
      $getUnitKerja  = $data['get_unit_kerja'];
    //  $getJumlahKelasPerUnit  = $data['get_jumlah_kelas_per_unit'];
      
      $getNominalPerItemPenerimaanBulan  = $data['get_nominal_per_item_penerimaan_bulan'];
      $getNominalPerItemPenerimaanRange  = $data['get_nominal_per_item_penerimaan_range'];
      $getNominalPerItemPengeluaranBulan  = $data['get_nominal_per_item_pengeluaran_bulan'];
      $getNominalPerItemPengeluaranRange = $data['get_nominal_per_item_pengeluaran_range'];
      
      $getNominalPerUnitPenerimaan  = $data['get_nominal_per_unit_penerimaan'];
      $getNominalPerUnitPengeluaran  = $data['get_nominal_per_unit_pengeluaran'];
      $getCoaAlokasiAkademik = $data['get_coa_alokasi_akademik'];
      
      $getNominalPerKelompokUnitPenerimaan  = $data['get_nominal_per_kelompok_unit_penerimaan'];
      $getNominalPerKelompokUnitPengeluaran  = $data['get_nominal_per_kelompok_unit_pengeluaran'];
      
      $getTotalPerKelompokPenerimaanBulan  = $data['get_total_per_kelompok_penerimaan_bulan'];
      $getTotalPerKelompokPenerimaanRange  = $data['get_total_per_kelompok_penerimaan_range'];
      $getTotalPerKelompokPengeluaranBulan  = $data['get_total_per_kelompok_pengeluaran_bulan'];
      $getTotalPerKelompokPengeluaranRange  = $data['get_total_per_kelompok_pengeluaran_range'];
      
      $getNominalPerPenerimaan  = $data['get_nominal_per_penerimaan'];
      $getNominalPerPengeluaran  = $data['get_nominal_per_pengeluaran'];
      
      $getTotalPerPenerimaanBulan  = $data['get_total_per_penerimaan_bulan'];
      $getTotalPerPenerimaanRange  = $data['get_total_per_penerimaan_range'];
      $getTotalPerPengeluaranBulan  = $data['get_total_per_pengeluaran_bulan'];
      $getTotalPerPengeluaranRange  = $data['get_total_per_pengeluaran_range'];
      
      $bulan =(int) date('m',strtotime($requestData['tanggal']));     
      $tahun =date('Y',strtotime($requestData['tanggal']));
      $namaBulan  = ($months[$bulan]);
      
      $this->mrTemplate->AddVar('content', 'NAMA_BULAN_AWAL', $getTaDetail['nama_bulan_awal']);
      if( $getTaDetail['tahun_awal'] == $tahun){
            $this->mrTemplate->AddVar('content', 'TAHUN_AWAL',  '');    
      } else {
            $this->mrTemplate->AddVar('content', 'TAHUN_AWAL',  $getTaDetail['tahun_awal']);
      }
      
      
      $this->mrTemplate->AddVar('content', 'NAMA_BULAN', $namaBulan);
      $this->mrTemplate->AddVar('content', 'TAHUN', $tahun);
      
      $urlSearch     = Dispatcher::Instance()->GetUrl(
         'laporan_keuangan_akademik',
         'LaporanKeuanganAkademik',
         'view',
         'html'
      );
      $urlPopupUnit  = Dispatcher::Instance()->GetUrl(
         'laporan_keuangan_akademik',
         'PopupUnitKerja',
         'view',
         'html'
      );

      $urlPopupProgram  = Dispatcher::Instance()->GetUrl(
         'laporan_keuangan_akademik',
         'PopupProgram',
         'view',
         'html'
      );

      $urlExportExcel   = Dispatcher::Instance()->GetUrl(
         'laporan_keuangan_akademik',
         'LaporanKeuanganAkademik',
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
      
      if(empty($getUnitKerja)) {
          $this->mrTemplate->AddVar('data_unit_header', 'DATA_EMPTY', 'YES');
      } else {
          $this->mrTemplate->AddVar('data_unit_header', 'DATA_EMPTY', 'NO');
          foreach($getUnitKerja as $key => $v) {
              $this->mrTemplate->AddVars('data_unit_header_item', $getUnitKerja[$key]);
              $this->mrTemplate->parseTemplate('data_unit_header_item', 'a');
          }
      }
      
      if(empty($getUnitKerja)) {
          $this->mrTemplate->AddVar('data_jml_kelas_header', 'DATA_EMPTY', 'YES');
      } else {
          $this->mrTemplate->AddVar('data_jml_kelas_header', 'DATA_EMPTY', 'NO');
          foreach($getUnitKerja as $key => $v) {
              //$getUnitKerja[$key]['jml_kelas'] = $getJumlahKelasPerUnit[$v['unit_kerja_id']];
              $this->mrTemplate->AddVars('data_jml_kelas_header_item', $getUnitKerja[$key]);
              $this->mrTemplate->parseTemplate('data_jml_kelas_header_item', 'a');
          }
      }

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
         $kegiatanDetailId = '';
         $index         = 0;
         $rkt           = array(); // untuk menyimpan nominal rkat
         $rkt_nominal   = array();
         

         $identitasId = '';
         $kelompokId  = '';
         $lka = array();         
         $i2 = 0;
         $kodeT = '';
         
         $noIdentitas = 0;
         $noKelompok = 0;
         $noSubKelompok = 1;
        
         for ($i=0; $i < count($dataList);) {
              $kodeIdentitas = $dataList[$i]['identitas'];
              $kodeKelompok =  $kodeIdentitas .  $dataList[$i]['kelompok_id'];
              $kodeSubKelompok = $kodeKelompok . $dataList[$i]['sub_kelompok_id'];
              
              $kelompokIdx = $dataList[$i]['kelompok_id'];
              
              if(($identitasId == $kodeIdentitas) &&
                   ($kelompokId == $kodeKelompok) ){
                    $nominalUnitTxt = '';   
                    $nominalUnitTxtSpace ='';
                    $nominalUnitTotalKelompokTxt = '';       
                    $nominalUnitTotalTxt = '';       
                    foreach($getUnitKerja as $key => $v) {
                        if($dataList[$i]['identitas'] == '1') {
                          if($getNominalPerUnitPenerimaan[$v['unit_kerja_id']][$dataList[$i]['identitas']][$dataList[$i]['kelompok_id']][$dataList[$i]['sub_kelompok_id']]) {
                                $nominalUnitTxt .='<td align="right">' . number_format($getNominalPerUnitPenerimaan[$v['unit_kerja_id']][$dataList[$i]['identitas']][$dataList[$i]['kelompok_id']][$dataList[$i]['sub_kelompok_id']], 0, ',','.') . '</td>';                                
                                $nominalUnitTxtSpace .='<td> </td>';
                            } else {
                                $nominalUnitTxt .='<td> </td>';
                                $nominalUnitTxtSpace .='<td> </td>';
                            }                     
                        } else {
                               
                            if($getNominalPerUnitPengeluaran[$v['unit_kerja_id']][$dataList[$i]['identitas']][$dataList[$i]['kelompok_id']][$dataList[$i]['sub_kelompok_id']]) {
                                $nominalUnitTxt .='<td align="right">' . number_format( $getNominalPerUnitPengeluaran[$v['unit_kerja_id']][$dataList[$i]['identitas']][$dataList[$i]['kelompok_id']][$dataList[$i]['sub_kelompok_id']], 0, ',','.') . '</td>';
                                $nominalUnitTxtSpace .='<td> </td>';
                            } else {
                                $nominalUnitTxt .='<td> </td>';
                                $nominalUnitTxtSpace .='<td> </td>';
                            } 
                        }
                   } 

                   $dataList[$i]['nominal_unit_txt'] =  $nominalUnitTxt;
                   $lka[$i2]['class'] = '';
                   $lka[$i2]['row_style'] = 'font-weight:normal';
                   $lka[$i2]['nomor'] = $noIdentitas .'.'.$noKelompok . '.'.$noSubKelompok; 
                   $lka[$i2]['perkiraan'] = $dataList[$i]['sub_kelompok_nama'];
                   if($identitasId == '1') {
                        $lka[$i2]['nominal_bulan_f'] = number_format($getNominalPerItemPenerimaanBulan[$dataList[$i]['identitas']][$dataList[$i]['kelompok_id']][$dataList[$i]['sub_kelompok_id']], 0, ',','.');
                        $lka[$i2]['nominal_f'] = number_format($getNominalPerItemPenerimaanRange[$dataList[$i]['identitas']][$dataList[$i]['kelompok_id']][$dataList[$i]['sub_kelompok_id']], 0, ',','.');
                   } else {
                       $lka[$i2]['nominal_bulan_f'] = number_format($getNominalPerItemPengeluaranBulan[$dataList[$i]['identitas']][$dataList[$i]['kelompok_id']][$dataList[$i]['sub_kelompok_id']], 0, ',','.');
                       $lka[$i2]['nominal_f'] = number_format($getNominalPerItemPengeluaranRange[$dataList[$i]['identitas']][$dataList[$i]['kelompok_id']][$dataList[$i]['sub_kelompok_id']], 0, ',','.');
                   }                             
                   $lka[$i2]['nominal_unit_txt'] =  $nominalUnitTxt;


                   $this->mrTemplate->AddVars('data_list', $lka[$i2]);
                   $this->mrTemplate->parseTemplate('data_list', 'a');
                   $i2 +=1;
                   $noSubKelompok++;
                   //untuk hitung per kelompok
                   if(isset($dataList[ $i + 1 ]['kelompok_id'])) {
                       $isCekKelompok = $dataList[ $i + 1 ]['kelompok_id'];
                   } else {
                       $isCekKelompok =NULL;
                   }
                   $nominalUnitTxtSpace ='';
                   if($kelompokIdx != $isCekKelompok ) {
                       
                       foreach($getUnitKerja as $key => $v) {
                           
                            if($dataList[$i]['identitas'] == '1') {
                               
                                if($getNominalPerKelompokUnitPenerimaan[$v['unit_kerja_id']][$dataList[$i]['identitas']][$dataList[$i]['kelompok_id']]) {
                                    $nominalUnitTotalKelompokTxt .='<td align="right">' . 
                                    number_format( $getNominalPerKelompokUnitPenerimaan[$v['unit_kerja_id']][$dataList[$i]['identitas']][$dataList[$i]['kelompok_id']], 0, ',','.'). '</td>';
                                    $nominalUnitTxtSpace .='<td> </td>';
                                } else {
                                    $nominalUnitTotalKelompokTxt .='<td> </td>';
                                    $nominalUnitTxtSpace .='<td> </td>';
                                }                     
                            } else {   
                                if($getNominalPerKelompokUnitPengeluaran[$v['unit_kerja_id']][$dataList[$i]['identitas']][$dataList[$i]['kelompok_id']]) {
                                   $nominalUnitTotalKelompokTxt .='<td align="right">' . number_format( $getNominalPerKelompokUnitPengeluaran[$v['unit_kerja_id']][$dataList[$i]['identitas']][$dataList[$i]['kelompok_id']], 0, ',','.') . '</td>';
                                   $nominalUnitTxtSpace .='<td> </td>';
                                } else {
                                   $nominalUnitTotalKelompokTxt .='<td></td>';
                                   $nominalUnitTxtSpace .='<td> </td>';
                                } 
                            }
                        } 
                       
                        $lka[$i2]['class'] = 'table-common-even2';
                        $lka[$i2]['row_style'] = 'font-weight:bold';
                        $lka[$i2]['nomor'] ='';                        
                        $lka[$i2]['perkiraan'] = 'Total '.$dataList[$i]['kelompok_nama'];   
                             
                        if($identitasId == '1') {
                            $lka[$i2]['nominal_bulan_f'] = number_format($getTotalPerKelompokPenerimaanBulan[$kelompokIdx], 0, ',','.');
                            $lka[$i2]['nominal_f'] = number_format($getTotalPerKelompokPenerimaanRange[$kelompokIdx], 0, ',','.');
                        } else {
                            $lka[$i2]['nominal_bulan_f'] = number_format($getTotalPerKelompokPengeluaranBulan[$kelompokIdx], 0, ',','.');
                            $lka[$i2]['nominal_f'] = number_format($getTotalPerKelompokPengeluaranRange[$kelompokIdx], 0, ',','.');
                        }
                       
                        
                        $lka[$i2]['nominal_unit_txt'] = $nominalUnitTotalKelompokTxt;                          
                        $this->mrTemplate->AddVars('data_list', $lka[$i2]);
                        $this->mrTemplate->parseTemplate('data_list', 'a');    
                        
                        //if($isCekKelompok != NULL){
                            $lka[$i2]['class'] = '';
                            $lka[$i2]['row_style'] = '';
                            $lka[$i2]['nomor'] ='';
                            $lka[$i2]['perkiraan'] = '&nbsp;';
                            $lka[$i2]['nominal_bulan_f'] = '';
                            $lka[$i2]['nominal_f'] = '';
                            $lka[$i2]['nominal_unit_txt'] =  $nominalUnitTxtSpace;                          
                            $this->mrTemplate->AddVars('data_list', $lka[$i2]);
                            $this->mrTemplate->parseTemplate('data_list', 'a');    
                        //}
                   }
                    $i2 +=1;
                   // untuk hitung total
                   if(isset($dataList[ $i + 1 ]['identitas'])) {
                       $isCek = $dataList[ $i + 1 ]['identitas'];
                   } else {
                       $isCek =NULL;
                   }
                   $nominalUnitTxtSpace='';
                   if($identitasId != $isCek ) {
                       foreach($getUnitKerja as $key => $v) {
                            if($dataList[$i]['identitas'] == '1') {                               
                                
                                if($getNominalPerPenerimaan[$v['unit_kerja_id']][$dataList[$i]['identitas']]) {
                                    $nominalUnitTotalTxt .='<td align="right">' . number_format($getNominalPerPenerimaan[$v['unit_kerja_id']][$dataList[$i]['identitas']], 0, ',','.') . '</td>';
                                    $nominalUnitTxtSpace .='<td> </td>';
                                } else {
                                    $nominalUnitTotalTxt .='<td> </td>';
                                    $nominalUnitTxtSpace .='<td> </td>';
                                }                     
                            } else {   
                                if($getNominalPerPengeluaran[$v['unit_kerja_id']][$dataList[$i]['identitas']]) {
                                    $nominalUnitTotalTxt .='<td align="right">' . number_format( $getNominalPerPengeluaran[$v['unit_kerja_id']][$dataList[$i]['identitas']], 0, ',','.') . '</td>';
                                    $nominalUnitTxtSpace .='<td> </td>';
                                } else {
                                    $nominalUnitTotalTxt .='<td> </td>';
                                    $nominalUnitTxtSpace .='<td> </td>';
                                } 
                            }
                        } 
                       
                        $lka[$i2]['class'] = 'table-common-even2';
                        $lka[$i2]['row_style'] = 'font-weight:bold';
                        $lka[$i2]['nomor'] ='';                        
                        $lka[$i2]['perkiraan'] = 'Total '.($identitasId == '1' ? 'Pendapatan' : 'Beban');    
                        if($identitasId == '1') {
                            $lka[$i2]['nominal_bulan_f'] = number_format($getTotalPerPenerimaanBulan, 0, ',','.');
                            $lka[$i2]['nominal_f'] = number_format($getTotalPerPenerimaanRange, 0, ',','.');
                        } else {
                            $lka[$i2]['nominal_bulan_f'] = number_format($getTotalPerPengeluaranBulan, 0, ',','.');
                            $lka[$i2]['nominal_f'] = number_format($getTotalPerPengeluaranRange, 0, ',','.');
                        }
                        $lka[$i2]['nominal_unit_txt'] = $nominalUnitTotalTxt;                          
                        $this->mrTemplate->AddVars('data_list', $lka[$i2]);
                        $this->mrTemplate->parseTemplate('data_list', 'a');    
                        
                        if($isCek != NULL){
                            $lka[$i2]['class'] = '';
                            $lka[$i2]['row_style'] = '';
                            $lka[$i2]['nomor'] ='';
                            $lka[$i2]['perkiraan'] = '&nbsp;';
                            $lka[$i2]['nominal_bulan_f'] = '';
                            $lka[$i2]['nominal_f'] = '';
                            $lka[$i2]['nominal_unit_txt'] =  $nominalUnitTxtSpace;                            
                            $this->mrTemplate->AddVars('data_list', $lka[$i2]);
                            $this->mrTemplate->parseTemplate('data_list', 'a');    
                        }
                   }
                  
                   $i++;
               }  elseif($identitasId != $kodeIdentitas) {
                    $identitasId = $kodeIdentitas;
                    $noSubKelompok = 1;
                    $noKelompok = 0;
                    $nominalUnitTxt = '';     
                    $nominalUnitTxtSpace ='';       
                    $noIdentitas++;
                    foreach($getUnitKerja as $key => $v) {
                        if($dataList[$i]['identitas'] == '1') {
                             $nominalUnitTxtSpace .= '<td> </td>';
                        } else {   
                            $nominalUnitTxt .='<td> </td>';
                            $nominalUnitTxtSpace .= '<td> </td>';
                        }
                   } 
                   $dataList[$i]['nominal_unit_txt'] =  $nominalUnitTxt;
                   $lka[$i2]['class'] = 'table-common-even1';
                   $lka[$i2]['row_style'] = 'font-weight:bold';
                   $lka[$i2]['nomor'] = $noIdentitas; 
                   $lka[$i2]['perkiraan'] = ($identitasId == '1' ? 'Pendapatan' : 'Beban');
                   $lka[$i2]['nominal_bulan_f'] = '';   //number_format($dataList[$i]['nominal'], 0, ',','.');
                   $lka[$i2]['nominal_f'] = '';   //number_format($dataList[$i]['nominal'], 0, ',','.');
                   $lka[$i2]['nominal_unit_txt'] =   $nominalUnitTxtSpace ;
                   
                   $this->mrTemplate->AddVars('data_list', $lka[$i2]);
                   $this->mrTemplate->parseTemplate('data_list', 'a');
                   
               }  elseif($kelompokId != $kodeKelompok) {
                    $kelompokId = $kodeKelompok;
                    $noSubKelompok = 1;
                    $nominalUnitTxt = '';     
                    $nominalUnitTxtSpace ='';       
                    $noKelompok++;
                    foreach($getUnitKerja as $key => $v) {
                        if($dataList[$i]['identitas'] == '1') {
                           $nominalUnitTxt .='<td> </td>';
                           $nominalUnitTxtSpace .= '<td> </td>';
                        } else {  
                           $nominalUnitTxt .='<td> </td>';
                           $nominalUnitTxtSpace .= '<td> </td>';                            
                        }
                   } 
                   $dataList[$i]['nominal_unit_txt'] =  $nominalUnitTxt;
                   $lka[$i2]['class'] = 'table-common-even1';
                   $lka[$i2]['row_style'] = 'font-weight:bold';
                   $lka[$i2]['nomor'] = $noIdentitas.'.'.$noKelompok;
                   $lka[$i2]['perkiraan'] = $dataList[$i]['kelompok_nama'];
                   $lka[$i2]['nominal_bulan_f'] = '';   //number_format($dataList[$i]['nominal'], 0, ',','.');
                   $lka[$i2]['nominal_f'] = '';   //number_format($dataList[$i]['nominal'], 0, ',','.');
                   $lka[$i2]['nominal_unit_txt'] =   $nominalUnitTxtSpace ;
                   
                   $this->mrTemplate->AddVars('data_list', $lka[$i2]);
                   $this->mrTemplate->parseTemplate('data_list', 'a');
                  
               }
               $i2++;
            }            
         }  
      }
}

?>