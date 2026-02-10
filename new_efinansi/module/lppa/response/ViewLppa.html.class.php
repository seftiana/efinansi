<?php

require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/lppa/business/Lppa.class.php';

require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewLppa extends HtmlResponse
{
   public function TemplateModule()
   {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') .
         'module/lppa/template');
      $this->SetTemplateFile('view_lppa.html');
   }

   public function ProcessRequest()
   {
      $messenger           = Messenger::Instance()->Receive(__FILE__);
      $message             = $style = NULL;
      $userId              = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
      $mObj                = new Lppa();
      $mUniObj             = new UserUnitKerja();
      $arrUnitKerjaRef     = $mUniObj->GetUnitKerjaRefUser($userId);
      $arrPeriodeTahun     = $mObj->GetComboTahunAnggaran();
      $periodeTahun        = $mObj->GetPeriodeTahun(array(
         'active' => true
      ));
      $requestData         = array();
      $months              = $mObj->indonesianMonth;

      if(isset($mObj->_POST['btncari'])){
         $requestData['unit_id']    = $mObj->_POST['unitkerja'];
         $requestData['unit_nama']  = $mObj->_POST['unitkerja_label'];
         $requestData['ta_id']      = $mObj->_POST['tahun_anggaran'];
         $requestData['kode']       = $mObj->_POST['kodenama'];
      }elseif(isset($mObj->_GET['search'])){
         $requestData['unit_id']    = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_id']);
         $requestData['unit_nama']  = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_nama']);
         $requestData['ta_id']      = Dispatcher::Instance()->Decrypt($mObj->_GET['ta_id']);
         $requestData['kode']       = Dispatcher::Instance()->Decrypt($mObj->_GET['kode']);
      }else{
         $requestData['unit_id']    = $arrUnitKerjaRef['id'];
         $requestData['unit_nama']  = $arrUnitKerjaRef['nama'];
         $requestData['ta_id']      = $periodeTahun[0]['id'];
         $requestData['kode']       = '';
      }

      foreach ($arrPeriodeTahun as $ta) {
         if((int)$ta['id'] === (int)$requestData['ta_id']){
            $requestData['ta_nama'] = $ta['name'];
         }
      }

      if(method_exists(Dispatcher::Instance(), 'getQueryString')){
         $queryString      = Dispatcher::Instance()->getQueryString($requestData);
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

      $dataList         = $mObj->GetListLppa($offset, $limit, (array)$requestData);
      $total_data       = $mObj->GetCountListLppa();
      #paging url
      $url    = Dispatcher::Instance()->GetUrl(
        Dispatcher::Instance()->mModule,
        Dispatcher::Instance()->mSubModule,
        Dispatcher::Instance()->mAction,
        Dispatcher::Instance()->mType
      ).'&search='.Dispatcher::Instance()->Encrypt(1).'&'.$queryString;

      $destination_id   = "subcontent-element";
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
            ' style="width:200px;" id="cmb_tahun_anggaran"'
         ), Messenger::CurrentRequest);

      Messenger::Instance()->SendToComponent(
         'combobox',
         'Combobox',
         'view',
         'html',
         'bulan',
         array(
            'bulan',
            $months,
            $requestData['bulan'],
            true,
            'id="cmb_bulan"'
         ),
         Messenger::CurrentRequest
      );

      if($messenger){
         $message       = $messenger[0][1];
         $style         = $messenger[0][2];
      }
      $return['referensi_unit']        = $arrUnitKerjaRef;
      $return['data_list']             = $dataList;
      $return['start']                 = $offset+1;
      $return['request_data']          = $requestData;
      $return['query_string']          = $queryString;
      $return['message']               = $message;
      $return['style']                 = $style;
      return $return;
   }

   function ParseTemplate($data = NULL) {
      $message             = $data['message'];
      $style               = $data['style'];
      $requestData         = $data['request_data'];
      $start               = $data['start'];
      $dataList            = $data['data_list'];
      $referensiUnit       = $data['referensi_unit'];
      $queryString         = $data['query_string'];
      
      $urlSearch           = Dispatcher::Instance()->GetUrl(
         'lppa',
         'lppa',
         'view',
         'html'
      );

      $urlAdd              = Dispatcher::Instance()->GetUrl(
         'lppa',
         'addLppa',
         'view',
         'html'
      );

      $urlCetak              = Dispatcher::Instance()->GetUrl(
         'lppa',
         'CetakLaporanLppa',
         'view',
         'html'
      );

      $urlExcel              = Dispatcher::Instance()->GetUrl(
         'lppa',
         'ExcelLaporanLppa',
         'view',
         'xlsx'
      );
            
      $urlUnitKerja        = Dispatcher::Instance()->GetUrl(
         'lppa',
         'popupUnitKerja',
         'view',
         'html'
      );

      $urlDetailApproval         = Dispatcher::Instance()->GetUrl(
         'lppa',
         'detailLppa',
         'view',
         'html'
      );

      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);
      $this->mrTemplate->AddVar('content', 'URL_ADD', $urlAdd);
      $this->mrTemplate->AddVar('status_unit', 'STATUS', strtoupper($referensiUnit['status']));
      $this->mrTemplate->AddVar('status_unit', 'URL_POPUP_UNITKERJA', $urlUnitKerja);
      $this->mrTemplate->AddVar('status_unit', 'UNIT_ID', $requestData['unit_id']);
      $this->mrTemplate->AddVar('status_unit', 'UNIT_NAMA', $requestData['unit_nama']);
      $this->mrTemplate->AddVars('content', $requestData);

      if($message) {
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $message);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $style);
      }

      if (empty($dataList)) {
         $this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'YES');
      } else {
         $this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'NO');
         
         $urlAccept              = 'lppa|DeleteLppa|do|json|';
         $urlReturn              = 'lppa|Lppa|view|html|';
         $labelDelete            = 'LPPA';
         $messageDelete          = 'Penghapusan Data ini akan menghapus Data Transaksi secara permanen.';
         
         for ($i=0; $i < count($dataList);$i++) {
            $dataList[$i]['nomor'] = $i+1;            
            $idEnc = Dispatcher::Instance()->Encrypt($dataList[$i]['lppa_id']);
             $dataList[$i]['url_delete']     = Dispatcher::Instance()->GetUrl(
               'confirm',
               'confirmDelete',
               'do',
               'html'
            ).'&urlDelete='. $urlAccept
            .'&urlReturn='.$urlReturn
            .'&id='.$idEnc
            .'&label='.$labelDelete
            .'&dataName='.$dataList[$i]['no_pengajuan']. ' - '.$dataList[$i]['unit_kerja_nama']
            .'&message='.$messageDelete;
            
                        
            $dataList[$i]['class_name']   = $dataList[$i]['nomor'] % 2 <> 0 ? 'table-common-even' : '';
            $dataList[$i]['url_edit']     = $urlAdd . '&dataId=' . $idEnc ;
            $dataList[$i]['url_excel']    = $urlExcel . '&dataId=' . $idEnc ;
            $dataList[$i]['url_cetak']    = $urlCetak . '&dataId=' . $idEnc ;
            $dataList[$i]['url_detail_approval']   = $urlDetailApproval. '&dataId=' . $idEnc ;
            $dataList[$i]['nominal_approve']       = number_format( $dataList[$i]['nominal_approve'] ,2,',','.');
            $dataList[$i]['nominal_lppa']          = number_format( $dataList[$i]['nominal_lppa'] ,2,',','.');                                                                        
            $this->mrTemplate->AddVar('status_approval', 'STATUS', strtoupper($dataList[$i]['is_approve']));
            $this->mrTemplate->AddVar('links', 'STATUS', strtoupper($dataList[$i]['is_approve']));
            $this->mrTemplate->AddVar('links', 'URL_EDIT', $dataList[$i]['url_edit']);
            $this->mrTemplate->AddVar('links', 'URL_DELETE', $dataList[$i]['url_delete']);
            $this->mrTemplate->AddVar('links', 'URL_CETAK', $dataList[$i]['url_cetak']);
            $this->mrTemplate->AddVar('links', 'URL_EXCEL', $dataList[$i]['url_excel']);
            $this->mrTemplate->AddVar('links', 'URL_DETAIL_APPROVAL', $dataList[$i]['url_detail_approval']);
            $this->mrTemplate->AddVars('data_item', $dataList[$i]);
            $this->mrTemplate->parseTemplate('data_item', 'a');
         }
      }
   }
}

?>