<?php

require_once GTFWConfiguration::GetValue('application','docroot').
'module/jurnal_penerimaan/business/JurnalPenerimaan.class.php';

class ViewJurnalPenerimaan extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/jurnal_penerimaan/template/');
      $this->SetTemplateFile('view_jurnal_penerimaan.html');
   }

   function ProcessRequest(){
      $mObj             = new JurnalPenerimaan();
      $messenger        = Messenger::Instance()->Receive(__FILE__);
      $message          = $style    = NULL;
      $requestData      = array();
      $queryString      = '';
      $getdate          = getdate();
      $currMonth        = (int)$getdate['mon'];
      $currYear         = (int)$getdate['year'];
      $arrTahun         = $mObj->getTahunPencatatan();
      $tahunPembukuan   = $mObj->getTahunPembukuanPeriode(array('open' => true));
      $minYear          = $arrTahun['min_year'];
      $maxYear          = $arrTahun['max_year'];
      $arrPosting       = array(
         array('id'=>'Y','name'=>'Sudah'),
         array('id'=>'T','name'=>'Belum')
      );

      if(isset($mObj->_POST['btnSearch'])){
         $startDate_day       = (int)$mObj->_POST['tanggal_awal_day'];
         $startDate_mon       = (int)$mObj->_POST['tanggal_awal_mon'];
         $startDate_year      = (int)$mObj->_POST['tanggal_awal_year'];
         $endDate_day         = (int)$mObj->_POST['tanggal_akhir_day'];
         $endDate_mon         = (int)$mObj->_POST['tanggal_akhir_mon'];
         $endDate_year        = (int)$mObj->_POST['tanggal_akhir_year'];

         $requestData['start_date']    = date('Y-m-d', mktime(0,0,0, $startDate_mon, $startDate_day, $startDate_year));
         $requestData['end_date']      = date('Y-m-d', mktime(0,0,0, $endDate_mon, $endDate_day, $endDate_year));
         $requestData['referensi']     = trim($mObj->_POST['referensi']);
         $requestData['posting']       = strtoupper($mObj->_POST['posting']);
      }elseif(isset($mObj->_GET['search'])){
         $requestData['start_date']    = date('Y-m-d', strtotime(Dispatcher::Instance()->Decrypt($mObj->_GET['start_date'])));
         $requestData['end_date']      = date('Y-m-d', strtotime(Dispatcher::Instance()->Decrypt($mObj->_GET['end_date'])));
         $requestData['referensi']     = Dispatcher::Instance()->Decrypt($mObj->_GET['referensi']);
         $requestData['posting']       = strtoupper(Dispatcher::Instance()->Decrypt($mObj->_GET['posting']));
      }else{
         $requestData['start_date']    = date('Y-m-d', mktime(0,0,0, $currMonth, 1, $currYear));
         $requestData['end_date']      = date('Y-m-t', mktime(0,0,0, $currMonth, 1, $currYear));
         $requestData['referensi']     = '';
         $requestData['posting']       = '';
      }

      if(method_exists(Dispatcher::Instance(), 'getQueryString')){
         # @param array
         $queryString      = Dispatcher::instance()->getQueryString($requestData);
      }else{
         $query            = array();
         foreach ($requestData as $key => $value) {
            $query[$key]   = Dispatcher::Instance()->Encrypt($value);
         }
         $queryString      = urldecode(http_build_query($query));
      }

      $offset         = 0;
      $limit          = 20;
      $page           = 0;
      if(isset($_GET['page'])){
         $page    = (string) $_GET['page']->StripHtmlTags()->SqlString()->Raw();
         $offset  = ($page - 1) * $limit;
      }
      #paging url
      $url    = Dispatcher::Instance()->GetUrl(
        Dispatcher::Instance()->mModule,
        Dispatcher::Instance()->mSubModule,
        Dispatcher::Instance()->mAction,
        Dispatcher::Instance()->mType
      ).'&search='.Dispatcher::Instance()->Encrypt(1).'&'.$queryString;

      $destination_id   = "subcontent-element";
      $data             = $mObj->getDataJurnalPenerimaan($offset, $limit, (array)$requestData);
      $dataList         = $data['result'];
      $total_data       = $data['count'];
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


      # GTFW Tanggal
      Messenger::Instance()->SendToComponent(
         'tanggal',
         'Tanggal',
         'view',
         'html',
         'tanggal_awal',
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
         'tanggal_akhir',
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

      Messenger::Instance()->SendToComponent(
         'combobox',
         'Combobox',
         'view',
         'html',
         'posting',
         array(
            'posting',
            $arrPosting,
            $requestData['posting'],
            true,
            ' style="width:100px;" id="cmb_posting"'
         ), Messenger::CurrentRequest);

      if($messenger){
         $message       = $messenger[0][1];
         $style         = $messenger[0][2];
      }
      $return['request_data']    = $requestData;
      $return['query_string']    = $queryString;
      $return['tahun_pembukuan'] = $tahunPembukuan;
      $return['message']         = $message;
      $return['style']           = $style;
      $return['data_list']       = $dataList;
      $return['start']           = $offset+1;
      return $return;
   }

   function ParseTemplate($data = null){
      $requestData      = (array)$data['request_data'];
      $queryString      = $data['query_string'];
      $tahunPembukuan   = $data['tahun_pembukuan'];
      $message          = $data['message'];
      $style            = $data['style'];
      $dataList         = $data['data_list'];
      $start            = $data['start'];

      $urlSearch     = Dispatcher::Instance()->GetUrl(
         'jurnal_penerimaan',
         'JurnalPenerimaan',
         'view',
         'html'
      );

      $urlAdd        = Dispatcher::Instance()->GetUrl(
         'jurnal_penerimaan',
         'inputJurnalPenerimaan',
         'view',
         'html'
      ).'&'.$queryString;

      $urlEdit          = Dispatcher::Instance()->GetUrl(
         'jurnal_penerimaan',
         'editJurnalPenerimaan',
         'view',
         'html'
      ).'&'.$queryString;

      $urlJurnalBalik   = Dispatcher::Instance()->GetUrl(
         'jurnal_penerimaan',
         'jurnalBalik',
         'do',
         'json'
      ).'&'.$queryString;

      $historyJurnal    = Dispatcher::Instance()->GetUrl(
         'jurnal_penerimaan',
         'HistoryJurnalBalik',
         'popup',
         'html'
      );

      $parseUrl      = parse_url($queryString);
      $urlExploded   = explode('&', $parseUrl['path']);
      $urlIndex      = 0;
      foreach ($urlExploded as $url) {
         list($urlKey[$urlIndex], $urlValue[$urlIndex]) = explode('=', $url);
         $patern     = '/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/';
         $patern1    = '/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})/';
         if((preg_match($patern, $urlValue[$urlIndex]) || preg_match($patern1, $urlValue[$urlIndex])) && strtotime($urlValue[$urlIndex]) !== false){
            $urlValue[$urlIndex]    = date('Y/m/d', strtotime($urlValue[$urlIndex]));
         }
         $urlIndex   += 1;
      }
      unset($urlIndex);
      $keyUrl     = implode('|', $urlKey);
      $valueUrl   = implode('|', $urlValue);

      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);
      $this->mrTemplate->AddVar('content', 'URL_ADD', $urlAdd);
      $this->mrTemplate->AddVars('content', $requestData);

      if($message){
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $message);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $style);
      }

      if(empty($dataList)){
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         $dataGrid      = array();
         $pembukuanId   = '';
         $transaksiId   = '';
         $index         = 0;
         $idx           = 0;
         $dataJurnal    = array();
         $rows          = array();
         $nomor         = 1;

         for ($i=0; $i < count($dataList);) {
            if((int)$transaksiId === (int)$dataList[$i]['id']
               && (int)$pembukuanId === (int)$dataList[$i]['pembukuan_id']){

               $ks      = $pembukuanId.'.'.$transaksiId;
               $dataJurnal[$ks][$idx]['akun_id']         = $dataList[$i]['coa_id'];
               $dataJurnal[$ks][$idx]['kode']            = $dataList[$i]['coa_kode_akun'];
               $dataJurnal[$ks][$idx]['nama']            = $dataList[$i]['coa_nama_akun'];
               $dataJurnal[$ks][$idx]['sub_account']     = $dataList[$i]['sub_account'];
               $dataJurnal[$ks][$idx]['nominal_debet']   = number_format($dataList[$i]['nominal_debet'], 2, ',','.');
               $dataJurnal[$ks][$idx]['nominal_kredit']  = number_format($dataList[$i]['nominal_kredit'], 2, ',','.');
               $dataJurnal[$ks][$idx]['class_name']      = $className;
               $rows[$ks]['row_span']        += 1;
               $i++;
               $idx++;
            }else{
               unset($idx);
               $idx              = 0;
               $pembukuanId      = (int)$dataList[$i]['pembukuan_id'];
               $transaksiId      = (int)$dataList[$i]['id'];
               $kodeSistem       = $pembukuanId.'.'.$transaksiId;
               if($start % 2 <> 0){
                  $className     = 'table-common-even';
               }else{
                  $className     = '';
               }
               $dataJurnal[$kodeSistem][$idx]['id']             = $dataList[$i]['id'];
               $dataJurnal[$kodeSistem][$idx]['nomor']          = $start;
               $dataJurnal[$kodeSistem][$idx]['pembukuan_id']   = $dataList[$i]['pembukuan_id'];
               $dataJurnal[$kodeSistem][$idx]['tp_id']          = $dataList[$i]['tpp_id'];
               $dataJurnal[$kodeSistem][$idx]['kode_sistem']    = $kodeSistem;
               $dataJurnal[$kodeSistem][$idx]['referensi']      = $dataList[$i]['referensi'];
               $dataJurnal[$kodeSistem][$idx]['deskripsi']      = $dataList[$i]['catatan'];
               $dataJurnal[$kodeSistem][$idx]['tanggal']        = $dataList[$i]['tanggal'];
               $dataJurnal[$kodeSistem][$idx]['penanggung_jawab'] = $dataList[$i]['penanggung_jawab'];
               $dataJurnal[$kodeSistem][$idx]['type']          = 'parent';
               $dataJurnal[$kodeSistem][$idx]['status_approval']  = $dataList[$i]['status_approve'];
               $dataJurnal[$kodeSistem][$idx]['status_posting']   = $dataList[$i]['status_posting'];
               $dataJurnal[$kodeSistem][$idx]['jurnal_balik']     = $dataList[$i]['jurnal_balik'];
               $dataJurnal[$kodeSistem][$idx]['has_jurnal']       = strtoupper($dataList[$i]['has_jurnal']);
               $dataJurnal[$kodeSistem][$idx]['jurnal']           = $dataList[$i]['jurnal'];
               $dataJurnal[$kodeSistem][$idx]['class_name']       = $className;
               $rows[$kodeSistem]['row_span']      = 0;
               $index++;
               $start+=1;
            }
         }

         foreach ($dataJurnal as $grid) {
            foreach ($grid as $jurnal) {
               if($jurnal['type'] AND strtoupper($jurnal['type']) == 'PARENT'){
                  $start++;
                  $jurnal['row_span']     = $rows[$jurnal['kode_sistem']]['row_span'];
                  $this->mrTemplate->AddVar('data_jurnal', 'LEVEL', 'PARENT');
                  // url delete
                  $urlAccept              = 'jurnal_penerimaan|DeleteJurnalPenerimaan|do|json-search|'.$keyUrl.'-1|'.$valueUrl;
                  $urlReturn              = 'jurnal_penerimaan|JurnalPenerimaan|view|html-search|'.$keyUrl.'-1|'.$valueUrl;
                  $label                  = GTFWConfiguration::GetValue('language', 'jurnal_penerimaan');
                  $message                = 'Penghapusan Data ini akan menghapus Data secara permanen.';
                  $jurnal['url_delete']   = Dispatcher::Instance()->GetUrl(
                     'confirm',
                     'confirmDelete',
                     'do',
                     'html'
                  ).'&urlDelete='. $urlAccept
                  .'&urlReturn='.$urlReturn
                  .'&id='.$jurnal['id'].'.'.$jurnal['pembukuan_id']
                  .'&label='.$label
                  .'&dataName='.$jurnal['referensi']
                  .'&message='.$message;

                  // condition status approve
                  if($jurnal['status_approval'] == 'Y'){
                     $this->mrTemplate->AddVar('status_approval', 'APPROVE', 'YES');
                  }else{
                     $this->mrTemplate->AddVar('status_approval', 'APPROVE', 'NO');
                  }
                  // condition status posting
                  if($jurnal['status_posting'] == 'Y'){
                     $this->mrTemplate->AddVar('status_posting', 'POSTING', 'YES');
                  }else{
                     $this->mrTemplate->AddVar('status_posting', 'POSTING', 'NO');
                  }

                  if((int)$jurnal['jurnal'] > 1){
                     $this->mrTemplate->SetAttribute('history_jurnal', 'visibility', 'visible');
                     $this->mrTemplate->AddVar('history_jurnal', 'URL_DETAIL', $historyJurnal.'&data_id='.$jurnal['id'].'&pr_id='.$jurnal['pembukuan_id']);
                  }

                  // condition links
                  if($jurnal['status_approval'] == 'T'){
                     $this->mrTemplate->AddVar('content_links', 'STATUS', 'UNAPPROVE');
                  }

                  if(($jurnal['status_approval'] == 'Y' OR $jurnal['status_approval'] == 'T') AND $jurnal['has_jurnal'] === 'YES'){
                     $this->mrTemplate->AddVar('content_links', 'STATUS', 'APPROVE');
                  }

                  if($jurnal['status_approval'] == 'Y' AND $jurnal['status_posting'] == 'Y' AND $jurnal['has_jurnal'] == 'NO'){
                     $this->mrTemplate->AddVar('content_links', 'STATUS', 'POSTING');
                  }

                  // Cek Tahun Pembukuan Aktif
                  if($tahunPembukuan[0]['id'] != $jurnal['tp_id']){
                     $this->mrTemplate->AddVar('content_links', 'DISPLAY_EDIT', 'display: none;');
                     $this->mrTemplate->AddVar('content_links', 'DISPLAY_DELETE', 'display: none;');
                     $this->mrTemplate->AddVar('content_links', 'DISPLAY_JURNAL_BALIK', 'display: none;');
                  }

                  $this->mrTemplate->AddVar('content_links', 'URL_EDIT', $urlEdit.'&data_id='.Dispatcher::Instance()->Encrypt($jurnal['id']).'&pr_id='.Dispatcher::Instance()->Encrypt($jurnal['pembukuan_id']));
                  $this->mrTemplate->AddVar('content_links', 'URL_DELETE', $jurnal['url_delete']);
                  $this->mrTemplate->AddVar('content_links', 'ID', $jurnal['id']);
                  $this->mrTemplate->AddVar('content_links', 'PEMBUKUAN_ID', $jurnal['pembukuan_id']);
                  $this->mrTemplate->AddVar('content_links', 'REFERENSI', $jurnal['referensi']);
                  $this->mrTemplate->AddVar('content_links', 'URL_JURNAL_BALIK', $urlJurnalBalik);
                  $this->mrTemplate->AddVars('data_jurnal', $jurnal);
               }else{
                  $this->mrTemplate->AddVar('data_jurnal', 'LEVEL', 'DATA');
                  $this->mrTemplate->AddVars('data_jurnal', $jurnal);
               }
               $this->mrTemplate->parseTemplate('data_list', 'a');
            }
         }
         $this->mrTemplate->AddVar('content', 'URL_JURNAL_BALIK', $urlJurnalBalik);
      }
   }
}
?>