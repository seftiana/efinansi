<?php
require_once GTFWConfiguration::GetValue('application','docroot').
'module/angsuran_detil/business/RencanaPengeluaran.class.php';

class ViewAngsuranDetil extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') .
         'module/angsuran_detil/template');
      $this->SetTemplateFile('view_angsuran_detil.html');
   }

   function ProcessRequest(){
      $messenger        = Messenger::Instance()->Receive(__FILE__);
      $mObj             = new RencanaPengeluaran();
      $mUnitObj         = new UserUnitKerja();
      $userid           = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
      $arrUnitKerja     = $mObj->ChangeKeyName($mUnitObj->GetUnitKerjaRefUser($userid));
      $arrPeriodeTahun  = $mObj->GetPeriodeTahun();
      $periodeAktif     = $mObj->GetPeriodeTahun(array('active' => true));
      $arrJenisKegiatan = $mObj->GetComboJenisKegiatan();
      $statusYa         = GTFWConfiguration::GetValue('language','ya');
      $statusTidak      = GTFWConfiguration::GetValue('language','tidak');
      $arrPengadaan     = array(
         array('id' => 'Y','name' => $statusYa),
         array('id' => 'T','name' => $statusTidak)
      );
      $requestData      = array();
      $months              = $mObj->indonesianMonth;

      if(isset($mObj->_POST['btnTampilkan'])){
         $requestData['ta_id']            = $mObj->_POST['data']['ta_id'];
         $requestData['unit_id']          = $mObj->_POST['data']['unit_id'];
         $requestData['unit_nama']        = $mObj->_POST['data']['unit_nama'];
         $requestData['jenis_kegiatan']   = $mObj->_POST['data']['jenis_kegiatan'];
         $requestData['pengadaan']        = $mObj->_POST['data']['pengadaan'];
         $requestData['kode']             = $mObj->_POST['data']['kode'];
         $requestData['nama']             = $mObj->_POST['data']['nama'];
         $requestData['bulan']            = $mObj->_POST['data']['bulan'];
      }elseif(isset($mObj->_GET['search'])){
         $requestData['ta_id']            = Dispatcher::Instance()->Decrypt($mObj->_GET['ta_id']);
         $requestData['unit_id']          = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_id']);
         $requestData['unit_nama']        = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_nama']);
         $requestData['jenis_kegiatan']   = Dispatcher::Instance()->Decrypt($mObj->_GET['jenis_kegiatan']);
         $requestData['pengadaan']        = Dispatcher::Instance()->Decrypt($mObj->_GET['pengadaan']);
         $requestData['kode']             = Dispatcher::Instance()->Decrypt($mObj->_GET['kode']);
         $requestData['nama']             = Dispatcher::Instance()->Decrypt($mObj->_GET['nama']);
         $requestData['bulan']             = Dispatcher::Instance()->Decrypt($mObj->_GET['bulan']);
      }else{
         $requestData['ta_id']            = $periodeAktif[0]['id'];
         $requestData['unit_id']          = $arrUnitKerja['id'];
         $requestData['unit_nama']        = $arrUnitKerja['nama'];
         $requestData['jenis_kegiatan']   = '';
         $requestData['pengadaan']        = '';
         $requestData['kode']             = '';
         $requestData['nama']             = '';
         $requestData['bulan']             = '';
      }

      foreach ($arrPeriodeTahun as $periodeTahun) {
         if((int)$periodeTahun['id'] === (int)$requestData['ta_id']){
            $requestData['ta_nama']       = $periodeTahun['name'];
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
      #paging url
      $url    = Dispatcher::Instance()->GetUrl(
        Dispatcher::Instance()->mModule,
        Dispatcher::Instance()->mSubModule,
        Dispatcher::Instance()->mAction,
        Dispatcher::Instance()->mType
      ).'&search='.Dispatcher::Instance()->Encrypt(1).'&'.$queryString;

      $destination_id   = "subcontent-element";
      $dataList         = $mObj->GetData($offset, $limit, (array)$requestData);
      $total_data       = $mObj->GetCount((array)$requestData);
      $dataResume       = $mObj->GetDataResume((array)$requestData);

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
            'data[ta_id]',
            $arrPeriodeTahun,
            $requestData['ta_id'],
            false,
            'style="width: 135px;" id="cmb_periode_tahun"'
         ),
         Messenger::CurrentRequest
      );

      # Combobox
      Messenger::Instance()->SendToComponent(
         'combobox',
         'Combobox',
         'view',
         'html',
         'jenis_kegiatan',
         array(
            'data[jenis_kegiatan]',
            $arrJenisKegiatan,
            $requestData['jenis_kegiatan'],
            true,
            'style="width: 215px" id="cmb_jenis_kegiatan"'
         ),
         Messenger::CurrentRequest
      );

      # Combobox
      Messenger::Instance()->SendToComponent(
         'combobox',
         'Combobox',
         'view',
         'html',
         'pengadaan',
         array(
            'data[pengadaan]',
            $arrPengadaan,
            $requestData['pengadaan'],
            true,
            'style="width: 135px;" id="cmb_pengadaan"'
         ),
         Messenger::CurrentRequest
      );

      # Combobox
      Messenger::Instance()->SendToComponent(
         'combobox',
         'Combobox',
         'view',
         'html',
         'bulan',
         array(
            'data[bulan]',
            $months,
            $requestData['bulan'],
            true,
            'id="cmb_bulan"'
         ),
         Messenger::CurrentRequest
      );
      
      if($messenger){
         $messengerData       = $messenger[0][0];
         $messengerMsg        = $messenger[0][1];
         $messengerStyle      = $messenger[0][2];
      }

      $return['data_unit']    = $arrUnitKerja;
      $return['query_string'] = $queryString;
      $return['request_data'] = $requestData;
      $return['data_list']    = $mObj->ChangeKeyName($dataList);
      $return['start']        = $offset+1;
      $return['data_resume']  = $mObj->ChangeKeyName($dataResume);
      $return['message']      = $messengerMsg;
      $return['style']        = $messengerStyle;
      return $return;
   }

   function ParseTemplate($data = NULL){
      $page                = 1;
      if(isset($_GET['page'])){
         $page             = (string) $_GET['page']->StripHtmlTags()->SqlString()->Raw();
      }
      $requestData         = $data['request_data'];
      $start               = $data['start'];
      $queryString         = $data['query_string'].'&page='.$page;
      $dataUnit            = $data['data_unit'];
      $dataList            = $data['data_list'];
      $protocol            = (int)$_SERVER['HTTP_PORT'] === 443 ? 'https://' : 'http://';
      $serverRoot          = realpath($_SERVER['DOCUMENT_ROOT']);
      $baseAddress         = $protocol.$_SERVER['HTTP_HOST'];
      $documentRoot        = GTFWConfiguration::GetValue('application', 'docroot');
      $documentRabPath     = realpath($documentRoot.'/document/rab/');
      $dataResume          = $data['data_resume'];
      $url_search          = Dispatcher::Instance()->GetUrl(
         'angsuran_detil',
         'AngsuranDetil',
         'view',
         'html'
      );

      $url_add             = Dispatcher::Instance()->GetUrl(
         'angsuran_detil',
         'inputRencanaPengeluaranRutin',
         'view',
         'html'
      );

      $url_cetak           = Dispatcher::Instance()->GetUrl(
         'angsuran_detil',
         'cetakRencanaPengeluaran',
         'view',
         'html'
      );

      // popup
      $popup_unit_kerja    = Dispatcher::Instance()->GetUrl(
         'angsuran_detil',
         'PopupUnitkerja',
         'view',
         'html'
      );

      $popup_sub_unit      = Dispatcher::Instance()->GetUrl(
         'angsuran_detil',
         'subUnitKerja',
         'popup',
         'html'
      );

      $urlUpload           =  Dispatcher::Instance()->GetUrl(
         'angsuran_detil',
         'UploadRABFile',
         'popup',
         'html'
      ).'&'.$queryString.'&page='.$page;

      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $url_search);
      $this->mrTemplate->AddVar('content', 'URL_ADD', $url_add);
      $this->mrTemplate->AddVar('content', 'URL_CETAK', $url_cetak);
      $this->mrTemplate->AddVar('content', 'POPUP_UNIT_KERJA', $popup_unit_kerja);
      $this->mrTemplate->AddVar('content', 'POPUP_SUBUNIT_KERJA', $popup_sub_unit);
      $this->mrTemplate->AddVar('STATUS_UNIT_KERJA', 'TYPE', strtoupper($dataUnit['status']));
      $this->mrTemplate->AddVar('STATUS_UNIT_KERJA', 'UNIT_ID', $requestData['unit_id']);
      $this->mrTemplate->AddVar('STATUS_UNIT_KERJA', 'UNIT_NAMA', str_replace(chr(92),'',$requestData['unit_nama']));
      $this->mrTemplate->AddVars('CONTENT', $requestData);

      if (isset($data['message']))
      {
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $data['message']);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $data['style']);
      }

      if (empty($dataList)){
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');

         /**
          * Insisialisasi data
          */
         $programId        = '';
         $kegiatanId       = '';
         $subkegiatanId    = '';
         $komponenId       = '';
         $dataGrid         = array();
         $index            = 0;
         $dataPengadaan    = array();
         $dataKomponen     = array();

         for ($i=0; $i < count($dataList);) {
            if((int)$dataList[$i]['program_id'] === (int)$programId && (int)$dataList[$i]['kegiatan_id'] === (int)$kegiatanId && (int)$dataList[$i]['id'] === (int)$subkegiatanId){
               $kegdetId                  = $dataList[$i]['id'];

               $programKodeSistem         = $programId.'.0.0';
               $kegiatanKodeSistem        = $programId.'.'.$kegiatanId.'.0';
               $subkegiatanKodeSistem     = $programId.'.'.$kegiatanId.'.'.$subkegiatanId;
               if(!is_null($dataList[$i]['komp_id'])){
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

                  // data pengadaan subkegiatan
                  $dataPengadaan[$subkegiatanKodeSistem]['nominal_pengadaan']        += $dataList[$i]['nominal_pengadaan'];
                  $dataPengadaan[$subkegiatanKodeSistem]['nominal_non_pengadaan']    += $dataList[$i]['nominal_non_pengadaan'];
                  $dataPengadaan[$subkegiatanKodeSistem]['nominal']                  += $dataList[$i]['nominal'];
                  $dataPengadaan[$subkegiatanKodeSistem]['nominal_approve_pengadaan']      += $dataList[$i]['nominal_approve_pengadaan'];
                  $dataPengadaan[$subkegiatanKodeSistem]['nominal_approve_non_pengadaan']  += $dataList[$i]['nominal_approve_non_pengadaan'];
                  $dataPengadaan[$subkegiatanKodeSistem]['nominal_approve']                += $dataList[$i]['nominal_approve'];
                  // -- end data pengadaan subkegiatan

                  $dataKomponen[$kodeSistem][]     = $dataList[$i]['status_komponen'];
                  /**
                   * Data Komponen (Detail Belanja)
                   */
                  $dataGrid[$index]['id']          = $dataList[$i]['komp_id'];
                  $dataGrid[$index]['kode']        = $dataList[$i]['komp_kode'];
                  // $dataGrid[$index]['kode']        = $dataList[$i]['akun_kode'];
                  $dataGrid[$index]['pejabat_approval']  = $dataList[$i]['user_nama'];
                  $dataGrid[$index]['nama']        = $dataList[$i]['komp_nama'];
                  // $dataGrid[$index]['nama']        = $dataList[$i]['akun_nama'];
                  $dataGrid[$index]['deskripsi']   = $dataList[$i]['komp_deskripsi'];
                  $dataGrid[$index]['nominal_pengadaan']       = number_format($dataList[$i]['nominal_pengadaan'], 0, ',','.');
                  $dataGrid[$index]['nominal_non_pengadaan']   = number_format($dataList[$i]['nominal_non_pengadaan'], 0, ',','.');
                  $dataGrid[$index]['nominal']                 = number_format($dataList[$i]['nominal'], 0, ',', '.');
                  $dataGrid[$index]['nominal_approve_pengadaan']        = number_format($dataList[$i]['nominal_approve_pengadaan'], 0, ',','.');
                  $dataGrid[$index]['nominal_approve_non_pengadaan']    = number_format($dataList[$i]['nominal_approve_non_pengadaan'], 0, ',','.');
                  $dataGrid[$index]['nominal_approve']                  = number_format($dataList[$i]['nominal_approve'], 0, ',','.');

                  $dataGrid[$index]['level']       = 'komponen';
               }else{
                  $index--;
               }
               $i++;
            }elseif((int)$dataList[$i]['program_id'] === (int)$programId && (int)$dataList[$i]['kegiatan_id'] === (int)$kegiatanId && (int)$dataList[$i]['id'] !== (int)$subkegiatanId){
               $subkegiatanId    = (int)$dataList[$i]['id'];
               $kodeSistem       = $dataList[$i]['program_id'].'.'.$dataList[$i]['kegiatan_id'].'.'.$dataList[$i]['id'];

               $dataPengadaan[$kodeSistem]['nominal_pengadaan']      = 0;
               $dataPengadaan[$kodeSistem]['nominal_non_pengadaan']  = 0;
               $dataPengadaan[$kodeSistem]['nominal']                = 0;
               $dataPengadaan[$kodeSistem]['nominal_approve_pengadaan']       = 0;
               $dataPengadaan[$kodeSistem]['nominal_approve_non_pengadaan']   = 0;
               $dataPengadaan[$kodeSistem]['nominal_approve']                 = 0;

               $dataKomponen[$kodeSistem]       = array();
               /**
                * Data RKAKT
                */
               $dataGrid[$index]['id']          = $dataList[$i]['id'];
               $dataGrid[$index]['kode']        = $dataList[$i]['subkegiatan_nomor'];
               $dataGrid[$index]['nama']        = $dataList[$i]['subkegiatan_nama'];
               $dataGrid[$index]['subkegiatan_id'] = $dataList[$i]['subkegiatan_id'];
               $dataGrid[$index]['deskripsi']   = $dataList[$i]['deskripsi'];
               $dataGrid[$index]['unit_kode']   = $dataList[$i]['unit_kode'];
               $dataGrid[$index]['unit_nama']   = $dataList[$i]['unit_nama'];
               $dataGrid[$index]['nomor']       = $start;
               $dataGrid[$index]['level']       = 'subkegiatan';
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;

               // generate file rab
               if($dataList[$i]['kegdet_r_a_b_file'] != ''){
                  $rabFile                      = $documentRabPath.'/'.$dataList[$i]['kegdet_r_a_b_file'];
                  $documentDownload             = str_replace($serverRoot, $baseAddress, $rabFile);
                  if(file_exists($rabFile)){
                     $dataGrid[$index]['document']    = 'exists';
                     $dataGrid[$index]['rab_file']    = $documentDownload;
                     $dataGrid[$index]['class_name']  = 'table-common-even rkat';
                  }else{
                     $dataGrid[$index]['document']    = 'not_exists';
                     $dataGrid[$index]['class_name']  = 'table-common-even rkat no_document';
                  }
               }else{
                  $dataGrid[$index]['document']       = 'not_exists';
                  $dataGrid[$index]['class_name']     = 'table-common-even rkat no_document';
               }

               // URL AKSI
               $param               = array(
                  $dataList[$i]['id'],
                  $dataList[$i]['subkegiatan_id'],
                  str_replace(array("\r\n","\r","\n","&","|"," "),'_',htmlentities($dataList[$i]['subkegiatan_nama'])),
                  str_replace(array("\r\n","\r","\n","&","|"," "),'_',htmlentities($dataList[$i]['jenis_kegiatan_nama'])),
                  $dataList[$i]['ta_id'],
                  $dataList[$i]['unit_id']
               );
               $urlParameter        = implode('|', $param);
               if(strtoupper($dataList[$i]['jenis_kegiatan_nama']) == 'RUTIN' OR is_null($dataList[$i]['jenis_kegiatan_nama'])){
                  $urlAksi          = Dispatcher::Instance()->GetUrl(
                     'angsuran_detil',
                     'InputRencanaPengeluaranRutin2',
                     'view',
                     'html'
                  );
               }else{
                  $urlAksi          = Dispatcher::Instance()->GetUrl(
                     'angsuran_detil',
                     'inputRencanaPengeluaranRutin2',
                     'view',
                     'html'
                  );
               }
               if((int)$dataList[$i]['detail_belanja'] === 0){
                  $dataGrid[$index]['aksi']           = 'add';
                  $dataGrid[$index]['url']            = $urlAksi.'&par='.$urlParameter.'|add&'.$queryString;
               }elseif(strtoupper($dataList[$i]['status_approve']) != 'YA' AND strtoupper($dataList[$i]['status_approve']) != 'TIDAK'){
                  $dataGrid[$index]['aksi']           = 'update';
                  $dataGrid[$index]['url']            = $urlAksi.'&par='.$urlParameter.'|edit&'.$queryString;
               }else{
                  $dataGrid[$index]['aksi']           = 'nothing';
               }
               $start++;
            }elseif((int)$dataList[$i]['program_id'] === (int)$programId && (int)$dataList[$i]['kegiatan_id'] !== (int)$kegiatanId){
               $kegiatanId                      = (int)$dataList[$i]['kegiatan_id'];
               $kodeSistem                      = $dataList[$i]['program_id'].'.'.$dataList[$i]['kegiatan_id'].'.0';

               $dataPengadaan[$kodeSistem]['nominal_pengadaan']      = 0;
               $dataPengadaan[$kodeSistem]['nominal_non_pengadaan']  = 0;
               $dataPengadaan[$kodeSistem]['nominal']                = 0;
               $dataPengadaan[$kodeSistem]['nominal_approve_pengadaan']       = 0;
               $dataPengadaan[$kodeSistem]['nominal_approve_non_pengadaan']   = 0;
               $dataPengadaan[$kodeSistem]['nominal_approve']                 = 0;
               /**
                * Data Kegiatan
                */
               $dataGrid[$index]['id']          = $dataList[$i]['kegiatan_id'];
               $dataGrid[$index]['kode']        = $dataList[$i]['kegiatan_nomor'];
               $dataGrid[$index]['nama']        = $dataList[$i]['kegiatan_nama'];
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['class_name']  = 'table-common-even2 kegiatan';
               $dataGrid[$index]['level']       = 'kegiatan';
            }else{
               $programId                       = (int)$dataList[$i]['program_id'];
               $kodeSistem                      = $dataList[$i]['program_id'].'.0.0';
               $dataPengadaan[$kodeSistem]['nominal_pengadaan']      = 0;
               $dataPengadaan[$kodeSistem]['nominal_non_pengadaan']  = 0;
               $dataPengadaan[$kodeSistem]['nominal']                = 0;
               $dataPengadaan[$kodeSistem]['nominal_approve_pengadaan']       = 0;
               $dataPengadaan[$kodeSistem]['nominal_approve_non_pengadaan']   = 0;
               $dataPengadaan[$kodeSistem]['nominal_approve']                 = 0;
               /**
                * Data Program
                */
               $dataGrid[$index]['id']          = $dataList[$i]['program_id'];
               $dataGrid[$index]['kode']        = $dataList[$i]['program_nomor'];
               $dataGrid[$index]['nama']        = $dataList[$i]['program_nama'];
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['class_name']  = 'table-common-even1 program';
               $dataGrid[$index]['level']       = 'program';
            }
            $index++;
         }

         foreach ($dataGrid as $grid) {
            $this->mrTemplate->clearTemplate('data_type');
            // deskripsi
            $this->mrTemplate->clearTemplate("LEVEL");
            $this->mrTemplate->AddVar('LEVEL','TYPE', strtoupper($grid['level']));
            $this->mrTemplate->AddVar('LEVEL', 'deskripsi', $grid['deskripsi']);

            // end deskripsi
            // nominal pengadaan
            if(strtoupper($grid['level']) != 'KOMPONEN'){
               $grid['nominal_pengadaan']                = number_format($dataPengadaan[$grid['kode_sistem']]['nominal_pengadaan'], 0, ',','.');
               $grid['nominal_non_pengadaan']            = number_format($dataPengadaan[$grid['kode_sistem']]['nominal_non_pengadaan'], 0, ',','.');
               $grid['nominal']                          = number_format($dataPengadaan[$grid['kode_sistem']]['nominal'], 0, ',','.');
               $grid['nominal_approve_pengadaan']        = number_format($dataPengadaan[$grid['kode_sistem']]['nominal_approve_pengadaan'], 0, ',','.');
               $grid['nominal_approve_non_pengadaan']    = number_format($dataPengadaan[$grid['kode_sistem']]['nominal_approve_non_pengadaan'], 0, ',','.');
               $grid['nominal_approve']                  = number_format($dataPengadaan[$grid['kode_sistem']]['nominal_approve'], 0, ',','.');
            }
            // end nominal pengadaan

            // url aksi
            $this->mrTemplate->clearTemplate("url_aksi");
            $this->mrTemplate->AddVar('url_aksi', 'LEVEL', strtoupper($grid['level']));
            // document
            $this->mrTemplate->clearTemplate('rab_file');
            if(strtoupper($grid['level']) == 'SUBKEGIATAN'){
               $urlUpload        = $urlUpload.'&data_id='.Dispatcher::Instance()->Encrypt($grid['id']);
               $this->mrTemplate->AddVar('URL_AKSI', 'URL_UPLOAD', $urlUpload);
               $statusApproval   = array_unique($dataKomponen[$grid['kode_sistem']]);

               $this->mrTemplate->AddVar('data_type', 'TYPE', strtoupper($grid['aksi']));

               $this->mrTemplate->AddVar('data_type', 'URL', $grid['url']);
               $this->mrTemplate->AddVar('data_type', 'SUBKEGIATAN_ID', $grid['subkegiatan_id']);
               if(strtoupper(strtoupper($grid['document'])) == 'EXISTS'){
                  $this->mrTemplate->AddVar('rab_file', 'EXISTS', 'YES');
                  $this->mrTemplate->AddVar('rab_file', 'DOCUMENT', $grid['rab_file']);
               }else{
                  $this->mrTemplate->AddVar('rab_file', 'EXISTS', 'NO');
               }
            }else{
               $this->mrTemplate->AddVar('rab_file', 'EXISTS', 'NOT_DOWNLOAD');
            }


            $this->mrTemplate->AddVars('data_item', $grid);
            $this->mrTemplate->parseTemplate('data_item', 'a');
         }
      }

      // DATA RESUME
      if (empty($dataResume)){
         $this->mrTemplate->AddVar('resume', 'RESUME_EMPTY', 'YES');
      } else {
         $this->mrTemplate->AddVar('resume', 'RESUME_EMPTY', 'NO');
         $nominalPengadaanResume             = 0;
         $nominalNonPengadaanResume          = 0;
         $nominalResume                      = 0;
         $nominalApprovePengadaanResume      = 0;
         $nominalApproveNonPengadaanResume   = 0;
         $nominalApproveResume               = 0;
         foreach ($dataResume as $resume) {
            $nominalPengadaanResume             += $resume['nominal_pengadaan'];
            $nominalNonPengadaanResume          += $resume['nominal_non_pengadaan'];
            $nominalResume                      += $resume['nominal'];
            $nominalApprovePengadaanResume      += $resume['nominal_approve_pengadaan'];
            $nominalApproveNonPengadaanResume   += $resume['nominal_approve_non_pengadaan'];
            $nominalApproveResume               += $resume['nominal_approve'];
            $resume['nominal_pengadaan']     = number_format($resume['nominal_pengadaan'], 0, ',','.');
            $resume['nominal_non_pengadaan'] = number_format($resume['nominal_non_pengadaan'], 0, ',','.');
            $resume['nominal']               = number_format($resume['nominal'], 0, ',','.');
            $resume['nominal_approve_pengadaan']   = number_format($resume['nominal_approve_pengadaan'], 0, ',','.');
            $resume['nominal_approve_non_pengadaan']  = number_format($resume['nominal_approve_non_pengadaan'], 0, ',','.');
            $resume['nominal_approve']       = number_format($resume['nominal_approve'], 0, ',','.');
            $this->mrTemplate->AddVars('resume_item', $resume);
            $this->mrTemplate->parseTemplate('resume_item', 'a');
         }

         $sumaryResume     = array(
            'nominal_pengadaan_total' => number_format($nominalPengadaanResume, 0, ',','.'),
            'nominal_non_pengadaan_total' => number_format($nominalNonPengadaanResume, 0, ',', '.'),
            'nominal_total' => number_format($nominalResume, 0, ',','.'),
            'nominal_approve_pengadaan_total' => number_format($nominalApprovePengadaanResume, 0, ',','.'),
            'nominal_approve_non_pengadaan_total' => number_format($nominalApproveNonPengadaanResume, 0, ',','.'),
            'nominal_approve_total' => number_format($nominalApproveResume, 0, ',','.')
         );

         $this->mrTemplate->AddVars('resume', $sumaryResume);
      }
      // END DATA RESUME
   }
}
/**
 * URL CETAK
$dataGrid[$i]['url_cetak'] = '<a class="xhr_dest_subcontent-element" href="javascript:void(0)" onclick="bukaPopupCetak(' . $dataGrid[$i]['kegiatandetail_id'] . ')" title="Cetak"><img src="images/button-print.gif" alt="Cetak"/></a>';
//if (($dataGrid[$i]['is_approve'] != 'Ya') && ($dataGrid[$i]['is_approve'] != 'Tidak')){
$url_cetak_rtf = Dispatcher::Instance()->GetUrl('angsuran_detil', 'RtfRencanaPengeluaran', 'view', 'html'). '&grp=' . $dataGrid[$i]['kegiatandetail_id'];
//}else{
//$url_cetak_rtf = Dispatcher::Instance()->GetUrl('angsuran_detil', 'RtfRencanaPengeluaranApproved', 'view', 'html'). '&grp=' . $dataGrid[$i]['kegiatandetail_id'];
//}
$url_cetak_rtf = Dispatcher::Instance()->Encrypt($url_cetak_rtf);
$dataGrid[$i]['url_cetak_rtf'] = '<a class="xhr_dest_subcontent-element" href="' . $url_cetak_rtf . '"  title="Cetak Rtf"><img src="images/button-print.gif" alt="Cetak Rtf"/></a>';
$url_cetak_rkakl = Dispatcher::Instance()->GetUrl('angsuran_detil', 'cetakRencanaPengeluaranRkakl', 'view', 'html'). '&grp=' . $dataGrid[$i]['kegiatandetail_id'];
$url_cetak_rkakl = Dispatcher::Instance()->Encrypt($url_cetak_rkakl);
$dataGrid[$i]['url_cetak_rkakl'] = '<a class="xhr_dest_subcontent-element" href="javascript:void(0)" onclick="cetak(\'' . $url_cetak_rkakl . '\')"  title="Cetak Rkakl"><img src="images/button-print.gif" alt="Cetak Rkakl"  style="background-color: lime"/></a>';
 */
?>