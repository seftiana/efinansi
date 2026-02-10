<?php

/**
* ================= doc ====================
* FILENAME     : ViewLaporanRekapAnggaranBelanjaBulanan.html.class.php
* @package     : ViewLaporanRekapAnggaranBelanjaBulanan
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-04-29
* @Modified    : 2015-04-29
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/

require_once GTFWConfiguration::GetValue('application','docroot').
'module/summary_realisasi/business/LaporanRekapAnggaranBelanjaBulanan.class.php';

class ViewLaporanRekapAnggaranBelanjaBulanan extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/summary_realisasi/template/');
      $this->SetTemplateFile('view_laporan_rekap_anggaran_belanja_bulanan.html');
   }

   function ProcessRequest(){
      $mObj       = new LaporanRekapAnggaranBelanjaBulanan();
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
         /*
         $tanggal_day      = (int)$mObj->_POST['tanggal_day'];
         $tanggal_mon      = (int)$mObj->_POST['tanggal_mon'];
         $tanggal_year     = (int)$mObj->_POST['tanggal_year'];
         */
         $requestData['ta_id']      = $mObj->_POST['tahun_anggaran'];
         $requestData['unit_id']    = $mObj->_POST['unit_id'];
         $requestData['unit_nama']  = $mObj->_POST['unit_nama'];
         $requestData['program_id'] = $mObj->_POST['program_id'];
         $requestData['program']    = $mObj->_POST['program'];
         $requestData['status_approval']    = $mObj->_POST['status_approval'];
         $requestData['tanggal']    = date('Y-m-d', mktime(0,0,0, $tanggal_mon, 1, $tanggal_year));
      }elseif(isset($mObj->_GET['search'])){
         $requestData['ta_id']      = Dispatcher::Instance()->Decrypt($mObj->_GET['ta_id']);
         $requestData['unit_id']    = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_id']);
         $requestData['unit_nama']  = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_nama']);
         $requestData['program_id'] = Dispatcher::Instance()->Decrypt($mObj->_GET['program_id']);
        $requestData['program']    = Dispatcher::Instance()->Decrypt($mObj->_GET['program']);
         $requestData['status_approval']    = Dispatcher::Instance()->Decrypt($mObj->_GET['status_approval']);
         $requestData['tanggal']    = date('Y-m-d', strtotime(Dispatcher::Instance()->Decrypt($mObj->_GET['tanggal'])));
      }else{
         $requestData['ta_id']      = $periodeTahun[0]['id'];
         $requestData['unit_id']    = $unitKerja['id'];
         $requestData['unit_nama']  = $unitKerja['nama'];
         $requestData['program_id'] = '';
         $requestData['program']    = '';
         $requestData['status_approval']    = 'Ya';
         $requestData['tanggal']    = date('Y-m-d', mktime(0,0,0, $curr_mon, 1, $curr_year));
      }
      //print_r($requestData);
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
      // $dataList            = $mObj->getDataAnggaranBelanjaBulanan($offset, $limit, $requestData);
      // $total_data          = $mObj->Count();
      $nominalPerBulan     = $mObj->getNominalDetailBelanjaBulanan($requestData);
      $nominalRealisasiPerBulan = $mObj->getNominaRealisasiBulanan($requestData); 
	  //echo"<pre>"; print_r($nominalPerBulan);
     #send data to pagging component
       // Messenger::Instance()->SendToComponent(
          // 'paging',
          // 'Paging',
          // 'view',
          // 'html',
          // 'paging_top',
          // array(
             // $limit,
             // $total_data,
             // $url,
             // $page,
             // $destination_id
          // ),
          // Messenger::CurrentRequest
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
      /*
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

       */
        
        $statusApproval = array(
                                array('id'=>'Belum','name'=>'Belum Disetujui'), 
                                array('id'=>'Ya','name'=>'Disetujui'),
                                array('id'=>'Tidak','name'=>'Ditolak')
                                );
                                
        Messenger::Instance()->SendToComponent(
                                              'combobox', 
                                              'Combobox', 
                                              'view', 
                                              'html', 
                                              'status_approval', 
                                              array(
                                                    'status_approval',                                                    
                                                    $statusApproval, 
                                                    $requestData['status_approval'],
                                                    true, 
                                                    ' style="width:100px; id="status_approval"'), 
                                              Messenger::CurrentRequest);
      //end                                               
      $return['get_header_bulan']   = $mObj->getHeaderBulan( $requestData['ta_id']);
      $return['request_data']       = $requestData;
      $return['unit_kerja']         = $unitKerja;
      // $return['data_list']       = $dataList;
      $return['nominal_per_bulan']  =  $nominalPerBulan;
      $return['nominal_realisasi_per_bulan'] = $nominalRealisasiPerBulan;
      $return['start']           = $offset+1;
      $return['months']          = $mObj->indonesianMonth;
      $return['query_string']    = $queryString;
      return $return;
   }

   function ParseTemplate($data = null){
      $getHeaderBulan   = $data['get_header_bulan'];
      $unitKerja        = $data['unit_kerja'];
      $requestData      = $data['request_data'];
      // $dataList      = $data['data_list'];
      $nominalPerBulan           = $data['nominal_per_bulan'];
      $nominalRealisasiPerBulan  = $data['nominal_realisasi_per_bulan'];
      $start         = $data['start'];
      $months        = $data['months'];
      $queryString   = $data['query_string'];
      $urlSearch     = Dispatcher::Instance()->GetUrl(
         'summary_realisasi',
         'LaporanRekapAnggaranBelanjaBulanan',
         'view',
         'html'
      );
      $urlPopupUnit  = Dispatcher::Instance()->GetUrl(
         'summary_realisasi',
         'PopupUnitKerja',
         'view',
         'html'
      );

      $urlPopupProgram  = Dispatcher::Instance()->GetUrl(
         'summary_realisasi',
         'PopupProgram',
         'view',
         'html'
      );

      $urlExportExcel   = Dispatcher::Instance()->GetUrl(
         'summary_realisasi',
         'LaporanRekapAnggaranBelanjaBulanan',
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
      $this->mrTemplate->SetAttribute('content_link', 'visibility', 'visible');
      //create header bulan
      $countColsBulan = ((sizeof($getHeaderBulan)));
      $this->mrTemplate->AddVar('content', 'COLSPAN_BULAN', $countColsBulan );
      foreach($getHeaderBulan as $bulan){
         $this->mrTemplate->AddVars('header_bulan', $bulan,'');
         $this->mrTemplate->parseTemplate('header_bulan', 'a');
      }

      // if(empty($dataList)){
      //    $this->mrTemplate->SetAttribute('content_link', 'visibility', 'hidden');
      //    $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      // }else{
      //    $this->mrTemplate->SetAttribute('content_link', 'visibility', 'visible');
      //    $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
      //    $dataGrid      = array();
      //    $program       = '';
      //    $kegiatan      = '';
      //    $subkegiatan   = '';
      //    $kegiatanDetailId = '';
      //    $index         = 0;
      //    $rkt           = array(); // untuk menyimpan nominal rkat
      //    $rkt_nominal   = array();
             
         // for ($i=0; $i < count($dataList);) {
            // if(
                    // (int)$program === (int)$dataList[$i]['program_id'] && 
                    // (int)$kegiatan === (int)$dataList[$i]['kegiatan_id'] && 
                    // (int)$subkegiatan === (int)$dataList[$i]['sub_kegiatan_id'] 
               // )
              // {
              //$index--;
               // $programKodeSistem      = $program.'.0.0.0';
               // $kegiatanKodeSistem     = $program.'.'.$kegiatan.'.0.0';
               // $subKegiatanKodeSistem  = $program.'.'.$kegiatan.'.'.$subkegiatan.'.0';
               // $kodeSistem             = $program.'.'.$kegiatan.'.'.$subkegiatan.'.'.$dataList[$i]['detail_belanja_kode'];
               
               // $dataGrid[$index]['id']          = $dataList[$i]['id'];
               // $dataGrid[$index]['parent_id']   = $dataList[$i]['keg_id'];
               // $dataGrid[$index]['nomor']       = $start;
               // $dataGrid[$index]['program_id']  = $dataList[$i]['program_id'];
               // $dataGrid[$index]['kegiatan_id'] = $dataList[$i]['kegiatan_id'];
               // $dataGrid[$index]['kode']        = '';
               //$dataGrid[$index]['nama']        = $dataList[$i]['detail_belanja_nama'];
               //$dataGrid[$index]['unit_nama']        = $dataList[$i]['unit_nama'];
              //$dataGrid[$index]['type']        = 'RP';
               //$dataGrid[$index]['nominal']     = $dataList[$i]['detail_belanja_nominal'];
               // $dataGrid[$index]['kode_sistem'] = $kodeSistem;

              
               // $start++;
               // $i++;
            // } elseif((int)$program === (int)$dataList[$i]['program_id'] && 
                        // (int)$kegiatan === (int)$dataList[$i]['kegiatan_id'] && 
                        // (int)$subkegiatan !== (int)$dataList[$i]['sub_kegiatan_id']){
               // $subkegiatan            = (int)$dataList[$i]['sub_kegiatan_id'];
               // $programKodeSistem      = $program.'.0.0.0';
               // $kegiatanKodeSistem     = $program.'.'.$kegiatan.'.0.0';
               // $kodeSistem             = $program.'.'.$kegiatan.'.'.$dataList[$i]['sub_kegiatan_id'].'.0';

               // $dataGrid[$index]['id']          = $dataList[$i]['id'];
               // $dataGrid[$index]['parent_id']   = $dataList[$i]['keg_id'];
               // $dataGrid[$index]['program_id']  = $dataList[$i]['program_id'];
               // $dataGrid[$index]['kegiatan_id'] = $dataList[$i]['kegiatan_id'];
               // $dataGrid[$index]['kode']        = $dataList[$i]['sub_kegiatan_kode'];
               // $dataGrid[$index]['nama']        = $dataList[$i]['sub_kegiatan_nama'];
               // $dataGrid[$index]['type']        = 'SUB_KEGIATAN';
               // #$dataGrid[$index]['nomor']       = $start;
               // $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               // $dataGrid[$index]['deskripsi']   = $dataList[$i]['deskripsi'];
               // $dataGrid[$index]['class_name']  = 'table-common-even';
               // $dataGrid[$index]['row_style']   = 'font-weight: bold;';
               // $dataGrid[$index]['nominal'] = $nominalPerBulan['nominal'][$kodeSistem];

              
               
               // #$start++;
            // }elseif((int)$program === (int)$dataList[$i]['program_id'] && (int)$kegiatan !== (int)$dataList[$i]['kegiatan_id']){
               // $kegiatan      = (int)$dataList[$i]['kegiatan_id'];
               // $kodeSistem    = $program.'.'.$kegiatan.'.0.0';
              //$rkt_nominal[$kodeSistem]['nominal']     = 0;
               // $dataGrid[$index]['kode']        = $dataList[$i]['kegiatan_kode'];
               // $dataGrid[$index]['nama']        = $dataList[$i]['kegiatan_nama'];
               // $dataGrid[$index]['type']        = 'KEGIATAN';
               // $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               // $dataGrid[$index]['class_name']  = 'table-common-even2';
               // $dataGrid[$index]['row_style']   = 'font-weight: bold; font-style: italic;';
               // $dataGrid[$index]['nominal'] = $nominalPerBulan['nominal'][$kodeSistem];
              
            // }elseif((int)$program !== (int)$dataList[$i]['program_id']){
               // $program       = (int)$dataList[$i]['program_id'];
               // $kodeSistem    = $program.'.0.0.0';

               //$rkt_nominal[$kodeSistem]['nominal']     = 0;
               // $dataGrid[$index]['kode']        = $dataList[$i]['program_kode'];
               // $dataGrid[$index]['nama']        = $dataList[$i]['program_nama'];
               // $dataGrid[$index]['type']        = 'PROGRAM';
               // $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               // $dataGrid[$index]['class_name']  = 'table-common-even1';
               // $dataGrid[$index]['row_style']   = 'font-weight: bold;';
               // $dataGrid[$index]['nominal'] = $nominalPerBulan['nominal'][$kodeSistem];
               
            // }

            // $index++;
         // }

         // foreach ($dataGrid as $grid) {

                // $nominal ='';
                // foreach($getHeaderBulan as $key => $bulan) {                  
                        // $nominal .= '<td style="text-align:right;">'.number_format($nominalPerBulan[$grid['kode_sistem']][$bulan['kode_bulan']], 0, ',','.').'</td>'; 
                           
                // }
               // $grid['nominal_per_bulan'] = $nominal;
               
               // if($rkt_nominal[$grid['kode_sistem']]['nominal'] < 0){
                     // $grid['nominal']  = '('.number_format(abs($grid['nominal']), 0, ',','.').')';
               // }else{
                     // $grid['nominal']  = number_format($grid['nominal'], 0, ',','.');

              // }
              // $this->mrTemplate->AddVars('data_list', $grid);
              // $this->mrTemplate->parseTemplate('data_list', 'a');
            // }

         // }
          $totalNominalAnggaran = array();
          $totalNominalRealisasi = array();
          foreach($getHeaderBulan as $key => $bulan) {                   
            $totalNominalAnggaran[$key]['total_anggaran_bulan']   = number_format($nominalPerBulan['total'][$bulan['kode_bulan']], 0, ',','.');            
            $totalNominalRealisasi[$key]['total_realisasi_bulan']  = number_format($nominalRealisasiPerBulan['total'][$bulan['kode_bulan']], 0, ',','.');
            
            $this->mrTemplate->AddVars('total_anggaran_per_bulan', $totalNominalAnggaran[$key],'');
            $this->mrTemplate->parseTemplate('total_anggaran_per_bulan', 'a');
            
            $this->mrTemplate->AddVars('total_realisasi_per_bulan', $totalNominalRealisasi[$key],'');
            $this->mrTemplate->parseTemplate('total_realisasi_per_bulan', 'a');
          }  

          $this->mrTemplate->AddVar('data_grid', 'TOTAL_NOMINAL_ANGGARAN', number_format($nominalPerBulan['jml_total'],0,',','.'));       
          $this->mrTemplate->AddVar('data_grid', 'TOTAL_NOMINAL_REALISASI', number_format($nominalRealisasiPerBulan['jml_total'],0,',','.'));       
        }
}
?>