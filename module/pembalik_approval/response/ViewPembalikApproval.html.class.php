<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/pembalik_approval/business/AppPembalikApproval.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewPembalikApproval extends HtmlResponse
{
   var $Pesan;
   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
         'module/pembalik_approval/template');
      $this->SetTemplateFile('view_pembalik_approval.html');
   }

   function ProcessRequest() {
      $messenger           = Messenger::Instance()->Receive(__FILE__);
      $userId              = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());

      $mObj                = new AppPembalikApproval();
      $mUniObj             = new UserUnitKerja();
      $arrUnitKerjaRef     = $mUniObj->GetUnitKerjaRefUser($userId);
      $arrPeriodeTahun     = $mObj->GetPeriodeTahun();
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
         $requestData['bulan']      = $mObj->_POST['bulan'];
      }elseif(isset($mObj->_GET['search'])){
         $requestData['unit_id']    = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_id']);
         $requestData['unit_nama']  = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_nama']);
         $requestData['ta_id']      = Dispatcher::Instance()->Decrypt($mObj->_GET['ta_id']);
         $requestData['kode']       = Dispatcher::Instance()->Decrypt($mObj->_GET['kode']);
         $requestData['bulan']      = Dispatcher::Instance()->Decrypt($mObj->_GET['bulan']);
      }else{
         $requestData['unit_id']    = $arrUnitKerjaRef['id'];
         $requestData['unit_nama']  = $arrUnitKerjaRef['nama'];
         $requestData['ta_id']      = $periodeTahun[0]['id'];
         $requestData['kode']       = '';
         $requestData['bulan']       = '';
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
      // $total_data     = total_data;
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
      $dataList         = $mObj->GetDataPembalikApproval($offset, $limit, (array)$requestData);
      $total_data       = $mObj->GetCountDataPembalikApproval((array)$requestData);
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

      $this->Pesan         = $msg[0][1];
      $this->css           = $msg[0][2];
      $return['referensi_unit']        = $arrUnitKerjaRef;
      $return['data_list']             = $mObj->ChangeKeyName($dataList);
      $return['start']                 = $offset+1;
      $return['request_data']          = $requestData;
      $return['query_string']          = $queryString;
      return $return;
   }

   function ParseTemplate($data = NULL) {
      $requestData         = $data['request_data'];
      $start               = $data['start'];
      $dataList            = $data['data_list'];
      $referensiUnit       = $data['referensi_unit'];
      $queryString         = $data['query_string'];
      $urlSearch           = Dispatcher::Instance()->GetUrl(
         'pembalik_approval',
         'pembalikApproval',
         'view',
         'html'
      );
      $urlAdd              = Dispatcher::Instance()->GetUrl(
         'pembalik_approval',
         'inputPembalikApproval',
         'view',
         'html'
      );
      $urlPopupUnit        = Dispatcher::Instance()->GetUrl(
         'pembalik_approval',
         'popupUnitkerja',
         'view',
         'html'
      );
      $urlAction           = Dispatcher::Instance()->GetUrl(
         'pembalik_approval',
         'detilPembalikApproval',
         'view',
         'html'
      );

      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);
      $this->mrTemplate->AddVar('content', 'URL_ADD', $urlAdd);
      $this->mrTemplate->AddVar('status_unit', 'STATUS', strtoupper($referensiUnit['status']));
      $this->mrTemplate->AddVar('status_unit', 'URL_POPUP_UNITKERJA', $urlPopupUnit);
      $this->mrTemplate->AddVar('status_unit', 'UNIT_ID', $requestData['unit_id']);
      $this->mrTemplate->AddVar('status_unit', 'UNIT_NAMA', $requestData['unit_nama']);
      $this->mrTemplate->AddVars('content', $requestData);

      if($this->Pesan) {
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
      }

      if (empty($dataList)) {
         $this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'YES');
      } else {
         $this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'NO');
         // inisialisasi data
         $programId        = '';
         $kegiatanId       = '';
         $index            = 0;
         $dataGrid         = array();
         $dataPengadaan    = array();
         for ($i=0; $i < count($dataList);) {
            if((int)$dataList[$i]['program_id'] === (int)$programId  && (int)$dataList[$i]['kegiatan_id'] === (int)$kegiatanId){

               $programKodeSistem         = $programId;
               $kegiatanKodeSistem        = $programId.'.'.$kegiatanId;

               // data pengadaan program
               $dataPengadaan[$programKodeSistem]['nominal_pengadaan']        += $dataList[$i]['nominal_pengadaan'];
               $dataPengadaan[$programKodeSistem]['nominal_non_pengadaan']    += $dataList[$i]['nominal_non_pengadaan'];
               $dataPengadaan[$programKodeSistem]['nominal']                  += $dataList[$i]['nominal'];
               $dataPengadaan[$programKodeSistem]['nominal_approve_pengadaan']      += $dataList[$i]['nominal_approve_pengadaan'];
               $dataPengadaan[$programKodeSistem]['nominal_approve_non_pengadaan']  += $dataList[$i]['nominal_approve_non_pengadaan'];
               $dataPengadaan[$programKodeSistem]['nominal_approve']                += $dataList[$i]['nominal_approve'];
               // -- end data pengadaan program

               // data pengadaan kegiatan
               $dataPengadaan[$kegiatanKodeSistem]['nominal_pengadaan']        += $dataList[$i]['nominal_pengadaan'];
               $dataPengadaan[$kegiatanKodeSistem]['nominal_non_pengadaan']    += $dataList[$i]['nominal_non_pengadaan'];
               $dataPengadaan[$kegiatanKodeSistem]['nominal']                  += $dataList[$i]['nominal'];
               $dataPengadaan[$kegiatanKodeSistem]['nominal_approve_pengadaan']      += $dataList[$i]['nominal_approve_pengadaan'];
               $dataPengadaan[$kegiatanKodeSistem]['nominal_approve_non_pengadaan']  += $dataList[$i]['nominal_approve_non_pengadaan'];
               $dataPengadaan[$kegiatanKodeSistem]['nominal_approve']                += $dataList[$i]['nominal_approve'];
               // end data pengadaan kegiatan


               $dataGrid[$index]['id']          = $dataList[$i]['id'];
               $dataGrid[$index]['kode_sistem'] = $programId.'.'.$kegiatanId.'.'.$dataList[$i]['id'];
               $dataGrid[$index]['kode']        = $dataList[$i]['subkegiatan_nomor'];
               $dataGrid[$index]['nama']        = $dataList[$i]['subkegiatan_nama'];
               $dataGrid[$index]['tanggal']        = $dataList[$i]['tanggal'];
               $dataGrid[$index]['nomor']       = $start;
               $dataGrid[$index]['unit_id']     = $dataList[$i]['unit_id'];
               $dataGrid[$index]['unit_nama']   = $dataList[$i]['unit_nama'];
               $dataGrid[$index]['nominal_usulan']    = number_format($dataList[$i]['nominal'], 0, ',','.');
               $dataGrid[$index]['nominal_approve']   = number_format($dataList[$i]['nominal_approve'], 0, ',', '.');
               $dataGrid[$index]['status']      = strtoupper($dataList[$i]['status_approve']);
               $dataGrid[$index]['level']       = 'SUBKEGIATAN';
               $dataGrid[$index]['class_name']  = ($start % 2 <> 0) ? 'table-common-even' : '';
               $dataGrid[$index]['jenis_kegiatan_id']    = $dataList[$i]['jenis_kegiatan_id'];
               $dataGrid[$index]['jenis_kegiatan_nama']  = $dataList[$i]['jenis_kegiatan_nama'];
               $i++;
               $start++;
            }elseif((int)$dataList[$i]['program_id'] === (int)$programId && (int)$dataList[$i]['kegiatan_id'] !== (int)$kegiatanId){
               $kegiatanId          = (int)$dataList[$i]['kegiatan_id'];
               $kodeSistem          = $dataList[$i]['program_id'].'.'.$dataList[$i]['kegiatan_id'];

               $dataPengadaan[$kodeSistem]['nominal_pengadaan']      = 0;
               $dataPengadaan[$kodeSistem]['nominal_non_pengadaan']  = 0;
               $dataPengadaan[$kodeSistem]['nominal']                = 0;
               $dataPengadaan[$kodeSistem]['nominal_approve_pengadaan']       = 0;
               $dataPengadaan[$kodeSistem]['nominal_approve_non_pengadaan']   = 0;
               $dataPengadaan[$kodeSistem]['nominal_approve']                 = 0;

               $dataGrid[$index]['id']          = $dataList[$i]['kegiatan_id'];
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['kode']        = $dataList[$i]['kegiatan_nomor'];
               $dataGrid[$index]['nama']        = $dataList[$i]['kegiatan_nama'];
               $dataGrid[$index]['status']      = 'PARENT';
               $dataGrid[$index]['level']       = 'KEGIATAN';
               $dataGrid[$index]['class_name']  = 'table-common-even2 subprogram';
            }else{
               $programId           = $dataList[$i]['program_id'];
               $kodeSistem          = $dataList[$i]['program_id'];

               $dataPengadaan[$kodeSistem]['nominal_pengadaan']      = 0;
               $dataPengadaan[$kodeSistem]['nominal_non_pengadaan']  = 0;
               $dataPengadaan[$kodeSistem]['nominal']                = 0;
               $dataPengadaan[$kodeSistem]['nominal_approve_pengadaan']       = 0;
               $dataPengadaan[$kodeSistem]['nominal_approve_non_pengadaan']   = 0;
               $dataPengadaan[$kodeSistem]['nominal_approve']                 = 0;

               $dataGrid[$index]['id']          = $dataList[$i]['program_id'];
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['kode']        = $dataList[$i]['program_nomor'];
               $dataGrid[$index]['nama']        = $dataList[$i]['program_nama'];
               $dataGrid[$index]['status']      = 'PARENT';
               $dataGrid[$index]['level']       = 'PROGRAM';
               $dataGrid[$index]['class_name']  = 'table-common-even1 program';
            }
            $index++;
         }

         foreach ($dataGrid as $list) {
            $this->mrTemplate->clearTemplate('status_approve');
            $this->mrTemplate->clearTemplate('links');
            $this->mrTemplate->AddVar('status_approve', 'STATUS', strtoupper($list['status']));
            $this->mrTemplate->AddVar('links', 'STATUS', strtoupper($list['status']));
            switch (strtoupper($list['status'])) {
               case 'YA':
                  $url        = $urlAction. '&dataId=' . Dispatcher::Instance()->Encrypt($list['id']).'&jenis_kegiatan=' . Dispatcher::Instance()->Encrypt($list['jenis_kegiatan_nama']) . '&search=1&'.$queryString;
                  $this->mrTemplate->AddVar('links', 'URL_ACTION', $url);
                  break;
               case 'BELUM':
                  $url        = '';
                  $this->mrTemplate->AddVar('links', 'URL_ACTION', $url);
                  break;
               case 'TIDAK':
                  $this->mrTemplate->AddVar('links', 'URL_ACTION', '');
                  break;
               default:
                  $this->mrTemplate->AddVar('links', 'URL_ACTION', '');
                  break;
            }

            // nominal pengadaan
            if(strtoupper($list['level']) != 'SUBKEGIATAN'){
               $list['nominal_usulan']       = number_format($dataPengadaan[$list['kode_sistem']]['nominal'], 0, ',','.');
               $list['nominal_approve']      = number_format($dataPengadaan[$list['kode_sistem']]['nominal_approve'], 0, ',','.');
            }
            // end nominal pengadaan
            $this->mrTemplate->AddVars('data_item', $list);
            $this->mrTemplate->parseTemplate('data_item', 'a');
         }
      }
   }
}
?>