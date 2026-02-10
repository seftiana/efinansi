<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/finansi_transaksi_penerimaan_bank/business/PopupPembayaranMhs.class.php';

require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/finansi_transaksi_penerimaan_bank/business/GetCoaJenisBiaya.class.php';

require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'main/function/number_format.class.php';

class ViewPopupPembayaranMhs extends HtmlResponse {

    public function TemplateModule() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') .
                'module/finansi_transaksi_penerimaan_bank/template'
        );

        $this->SetTemplateFile('view_popup_pembayaran_mhs.html');
    }

    public function TemplateBase() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') .
                'main/template/');
        $this->SetTemplateFile('document-common-popup.html');
        $this->SetTemplateFile('layout-common-popup.html');
    }

    public function ProcessRequest() {

        $mObj = new PopupPembayaranMhs();
        $mCoaJBObj = new GetCoaJenisBiaya();

        $getRange = $mObj->getRangeYear();
        $getTipe = $mObj->getTipe();
        $requestQuery = $mObj->_getQueryString();

        /*
          $decJbId = Dispatcher::Instance()->Decrypt($mObj->_GET['jk_jb_id']);
          $decJbNama = Dispatcher::Instance()->Decrypt($mObj->_GET['jk_jb_nama']);
          $decModeCatat = Dispatcher::Instance()->Decrypt($mObj->_GET['mode_catat']);
          $encJbId = Dispatcher::Instance()->Encrypt($decJbId);
          $encJbNama = Dispatcher::Instance()->Encrypt($decJbNama);
          $encModeCatat = Dispatcher::Instance()->Encrypt($decModeCatat);
         */

        $requestData = array();
        $queryString = '';

        $minYear = $getRange['min_year'] - 5;
        $maxYear = $getRange['max_year'];

        $requestData['tanggal_awal'] = date('Y-m-d', strtotime($getRange['tanggal_awal']));
        $requestData['tanggal_akhir'] = date('Y-m-d', strtotime($getRange['tanggal_akhir']));

        /*
          $requestData['jenis_biaya_id'] = $encJbId;
          $requestData['jenis_biaya_nama'] = $encJbNama;
          $requestData['tipe_bayar_skenario'] = $encModeCatat;
         */

        if (isset($mObj->_POST['btncari'])) {
            $startDate_day = (int) $mObj->_POST['start_date_day'];
            $startDate_mon = (int) $mObj->_POST['start_date_mon'];
            $startDate_year = (int) $mObj->_POST['start_date_year'];
            $endDate_day = (int) $mObj->_POST['end_date_day'];
            $endDate_mon = (int) $mObj->_POST['end_date_mon'];
            $endDate_year = (int) $mObj->_POST['end_date_year'];
            $requestData['tanggal_awal'] = date('Y-m-d', mktime(0, 0, 0, $startDate_mon, $startDate_day, $startDate_year));
            $requestData['tanggal_akhir'] = date('Y-m-d', mktime(0, 0, 0, $endDate_mon, $endDate_day, $endDate_year));
            $requestData['periodeId'] = $mObj->_POST['periode_pembayaran'];
            $requestData['tipe_pembayaran'] = $mObj->_POST['tipe_pembayaran'];
            /**
              $requestData['jenis_biaya_id'] = $mObj->_POST['jenis_biaya_id'];
              $requestData['jenis_biaya_nama'] = $mObj->_POST['jenis_biaya_nama'];
              $requestData['tipe_bayar_skenario'] = $mObj->_POST['tipe_bayar_skenario'];
             * 
             */
        } elseif (isset($mObj->_GET['search'])) {
            $requestData['periodeId'] = Dispatcher::Instance()->Decrypt($mObj->_GET['periodeId']);
            $requestData['tanggal_awal'] = date('Y-m-d', strtotime(Dispatcher::Instance()->Decrypt($mObj->_GET['tanggal_awal'])));
            $requestData['tanggal_akhir'] = date('Y-m-d', strtotime(Dispatcher::Instance()->Decrypt($mObj->_GET['tanggal_akhir'])));
            $requestData['tipe_pembayaran'] = Dispatcher::Instance()->Decrypt($mObj->_GET['tipe_pembayaran']);
            /*
             *
              $requestData['jenis_biaya_id'] = Dispatcher::Instance()->Decrypt($mObj->_GET['jenis_biaya_id']);
              $requestData['jenis_biaya_nama'] = Dispatcher::Instance()->Decrypt($mObj->_GET['jenis_biaya_nama']);
              $requestData['tipe_bayar_skenario'] = Dispatcher::Instance()->Decrypt($mObj->_GET['tipe_bayar_skenario']);
             * 
             */
        }

        if (method_exists(Dispatcher::Instance(), 'getQueryString')) {
            # @param array
            $queryString = Dispatcher::instance()->getQueryString($requestData);
        } else {
            $query = array();
            foreach ($requestData as $key => $value) {
                $query[$key] = Dispatcher::Instance()->Encrypt($value);
            }
            $queryString = urldecode(http_build_query($query));
        }

        $offset = 0;
        $limit = 20;
        $page = 0;
        if (isset($_GET['page'])) {
            $page = (string) $_GET['page']->StripHtmlTags()->SqlString()->Raw();
            $offset = ($page - 1) * $limit;
        }
        #paging url
        $url = Dispatcher::Instance()->GetUrl(
                        Dispatcher::Instance()->mModule, Dispatcher::Instance()->mSubModule, Dispatcher::Instance()->mAction, Dispatcher::Instance()->mType
                ) .
                '&search=' . Dispatcher::Instance()->Encrypt(1) .
                '&' . $queryString;

        $destination_id = "popup-subcontent";

        $getPeriodePembayaran = $mObj->getPeriodePembayaran();
        $getPeriodePembayaranPasca = $mObj->getPeriodePembayaranPasca();
        //print_r($getPeriodePembayaranPasca);
        
        if ( (isset($mObj->_POST['btncari'])) || isset($mObj->_GET['search']) ) {
            $dataList = $mObj->getDataPembayaran($requestData);
        }

        if (!empty($dataList)) {
            $coaJb = $mCoaJBObj->GetArray($dataList['jbIds']);
            $coaDepMasuk = $mCoaJBObj->GetCoaDepositMasuk();
        } else {
            $coaJb = null;
            $coaDepMasuk = null;
        }
        #send data to pagging component
        Messenger::Instance()->SendToComponent(
                'paging', 'Paging', 'view', 'html', 'paging_top', array(
            $limit,
            $total_data,
            $url,
            $page,
            $destination_id
                ), Messenger::CurrentRequest
        );

        # GTFW Tanggal
        Messenger::Instance()->SendToComponent(
                'tanggal', 'Tanggal', 'view', 'html', 'start_date', array(
            $requestData['tanggal_awal'],
            $minYear,
            $maxYear,
            false,
            false,
            false
                ), Messenger::CurrentRequest
        );

        Messenger::Instance()->SendToComponent(
                'tanggal', 'Tanggal', 'view', 'html', 'end_date', array(
            $requestData['tanggal_akhir'],
            $minYear,
            $maxYear,
            false,
            false,
            false
                ), Messenger::CurrentRequest
        );

        # Combobox Periode Pembayaran
        Messenger::Instance()->SendToComponent(
                'combobox', 'Combobox', 'view', 'html', 'periode_pembayaran', array(
            'periode_pembayaran',
            $getPeriodePembayaran['data_list'],
            $requestData['periodeId'],
            false,
            'id="cmb_periode_bayar" '
                ), Messenger::CurrentRequest
        );

        # Combobox Get Tipe Pembayaran
        Messenger::Instance()->SendToComponent(
                'combobox', 'Combobox', 'view', 'html', 'tipe_pembayaran', array(
            'tipe_pembayaran',
            $getTipe,
            $requestData['tipe_pembayaran'],
            false,
            'id="cmb_tipe_pembayaran" '
                ), Messenger::CurrentRequest
        );

        $return['request_query'] = $requestQuery;
        $return['request_data'] = $requestData;
        $return['query_string'] = $queryString;
        $return['data_list'] = $mObj->ChangeKeyName($dataList);
        $return['coa_jb'] = $coaJb;
        $return['coa_dep_masuk'] = $coaDepMasuk;
        return $return;
    }

    public function ParseTemplate($data = NULL) {
        $displayNone = 'style="display:none"';
        $displayYes = '';
        $dataList = $data['data_list'];
        $coaJB = $data['coa_jb'];
        $coaDepMasuk = $data['coa_dep_masuk'];
        $requestData = $data['request_data'];
        $queryString = $data['query_string'];
        $requestQuery = $data['request_query'];
        #echo '<pre>';
        #print_r($coaJB);
        #echo '</pre>';
        $urlSearch = Dispatcher::Instance()->GetUrl(
                        Dispatcher::Instance()->mModule, Dispatcher::Instance()->mSubModule, Dispatcher::Instance()->mAction, Dispatcher::Instance()->mType
                ) . '&' . $requestQuery;

        // untuk piutang
        //    echo '<pre>';
        #echo $requestData['tipe_pembayaran'];
        #echo $requestData['jenis_biaya_nama'] ;
        #echo $requestData['tipe_bayar_skenario'];
        //    print_r($dataList['data_list']);
        //    echo '</pre>';
        $this->mrTemplate->AddVars('content', $requestData);
        $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);


        if (strtoupper($requestData['tipe_pembayaran']) == 'PENGAKUAN_DEPMASUK') {

            $this->mrTemplate->AddVar('content', 'IS_DISPLAY', $displayNone);
            if (empty($dataList['data_list'])) {
                $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
            } else {
                $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
                $start = 1;

                $prodiId = -1;
                $dataCoa = null;
                $dataJB = null;
                $dataProdi = null;
                $list = $dataList['data_list'];
                for ($d = 0; $d < sizeof($list); $d++) {
                    $prodiId = $list[$d]['prodi'];
                    $depMasuk = $list[$d]['pemasukan_deposit'];
                    //prodi tak punya jenis biaya id
                    //jadi di isi 0
                    $jenisBiayaId = 0;
                    /** data prodi */
                    $dataProdi[$list[$d]['prodi']][0]['prodi_id'] = $list[$d]['prodi'];
                    $dataProdi[$list[$d]['prodi']][0]['prodi_nama'] = $list[$d]['nama_prodi'];
                    $dataProdi[$list[$d]['prodi']][0]['prodi_nominal'] = $list[$d]['pemasukan_deposit'];
                    $dataProdi[$list[$d]['prodi']][0]['prodi_potongan'] = 0;
                    $dataProdi[$list[$d]['prodi']][0]['prodi_deposit'] = 0;
                    $dataProdi[$list[$d]['prodi']][0]['prodi_deposit_masuk'] = 0;
                    $dataProdi[$list[$d]['prodi']][0]['prodi_tipe_pembayaran'] = $dataList['tipe_pembayaran'];
                    $dataProdi[$list[$d]['prodi']][0]['prodi_keterangan'] = '';
                    $dataProdi[$list[$d]['prodi']][0]['prodi_id_detail'] =  $list[$d]['id_detil'];
                    $dataProdi[$list[$d]['prodi']][0]['prodi_penanggung_jawab'] =  $list[$d]['penanggung_jawab'];                    
                    /**/

                    $dataJB = array();
                    /** buat map coa * */
                    if ($depMasuk > 0) {
                          $dataCoa[$prodiId][$jenisBiayaId][] = array(
                          'coa_id' => $coaDepMasuk['coa_id'],
                          'coa_kode' => $coaDepMasuk['coa_kode'],
                          'coa_nama' => $coaDepMasuk['coa_nama'],
                          'coa_dk' => $coaDepMasuk['coa_dk'],
                          'ket' => $list[$d]['nama_prodi'] . ' (Deposit Masuk)',
                          'nominal' => $depMasuk
                          );
                    }

                    $list[$d]['nomor'] = $start;
                    $list[$d]['f_prodi'] = $list[$d]['prodi'];
                    $list[$d]['f_nama'] = $list[$d]['nama_prodi'];
                    $list[$d]['f_nominal'] = NumberFormat::Accounting($list[$d]['pemasukan_deposit'], 2);
                    $list[$d]['f_potongan'] = 0;
                    $list[$d]['tipe_pembayaran'] = $dataList['tipe_pembayaran'];
                    $list[$d]['class_name'] = '';
                    $list[$d]['is_display'] = $displayNone;
                    $this->mrTemplate->SetAttribute('link_pilih', 'visibility', 'visible');
                    $this->mrTemplate->AddVar('link_pilih', 'F_JENIS_BIAYA', $jenisBiayaId);
                    $this->mrTemplate->AddVar('link_pilih', 'F_PRODI', $list[$d]['f_prodi']);
                    $this->mrTemplate->AddVar('link_pilih', 'F_PRODI_NAMA', $list[$d]['f_nama']);
                    $this->mrTemplate->AddVar('link_pilih', 'T_NOMINAL', $list[$d]['total_nominal']);
                    $this->mrTemplate->AddVar('link_pilih', 'T_POTONGAN', 0);
                    $this->mrTemplate->AddVar('link_pilih', 'T_DEPOSIT', 0);
                    $this->mrTemplate->AddVar('link_pilih', 'T_DEPOSIT_MASUK', 0);
                    $this->mrTemplate->AddVar('link_pilih', 'TIPE_BAYAR', $list[$d]['tipe_pembayaran']);
                    $this->mrTemplate->AddVars('data_list', $list[$d]);
                    $this->mrTemplate->parseTemplate('data_list', 'a');
                    $start++;
                }
            }
        } else if (strtoupper($requestData['tipe_pembayaran']) == 'PIUTANG') {

            $this->mrTemplate->AddVar('content', 'IS_DISPLAY', $displayNone);
            if (empty($dataList['data_list'])) {
                $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
            } else {
                $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
                $start = 1;

                $prodiId = -1;
                $dataCoa = null;
                $dataJB = null;
                $dataProdi = null;
                $list = $dataList['data_list'];

                for ($d = 0; $d < sizeof($list);) {
                    if ($prodiId == $list[$d]['prodi']) {
                        $prodiId = $list[$d]['prodi'];
                        $jenisBiayaId = $list[$d]['jenis_biaya_id'];

                        $dataProdi[$prodiId][$jenisBiayaId]['prodi_id'] = $list[$d]['prodi'];
                        $dataProdi[$prodiId][$jenisBiayaId]['prodi_nama'] = $list[$d]['nama_prodi'];
                        $dataProdi[$prodiId][$jenisBiayaId]['prodi_nominal'] = $list[$d]['total_nominal'];
                        $dataProdi[$prodiId][$jenisBiayaId]['prodi_potongan'] = $list[$d]['total_potongan'];
                        $dataProdi[$prodiId][$jenisBiayaId]['prodi_deposit'] = 0;
                        $dataProdi[$prodiId][$jenisBiayaId]['prodi_deposit_masuk'] = 0;
                        $dataProdi[$prodiId][$jenisBiayaId]['prodi_tipe_pembayaran'] = $dataList['tipe_pembayaran'];
                        $dataProdi[$prodiId][$jenisBiayaId]['prodi_keterangan'] = $list[$d]['keterangan'];
                        $dataProdi[$prodiId][$jenisBiayaId]['prodi_id_detail'] ='';
                        $dataProdi[$prodiId][$jenisBiayaId]['prodi_penanggung_jawab'] =  $list[$d]['penanggung_jawab']; 

                        $dataJB[$prodiId][$jenisBiayaId][] = array(
                            'prodi_id' => $list[$d]['prodi'],
                            'prodi_nama' => $list[$d]['nama_prodi'],
                            'jenis_biaya_id' => $list[$d]['jenis_biaya_id'],
                            'jenis_biaya_nama' => $list[$d]['jenis_biaya'],
                            'nominal' => $list[$d]['nominal'],
                            'potongan' => $list[$d]['potongan'],
                            'deposit' => 0,
                            'penanggung_jawab' => $list[$d]['penanggung_jawab'],
                            'keterangan' => $list[$d]['keterangan'],
                            'id_detail' => $list[$d]['id_detail'],
                            'tipe' => $list[$d]['tipe']
                        );
                        /** buat map coa * */
                        $dataCoa[$prodiId][$jenisBiayaId][] = array(
                            'coa_id' => $coaJB[$list[$d]['jenis_biaya_id']]['jb_piutang_coa_id'],
                            'coa_kode' => $coaJB[$list[$d]['jenis_biaya_id']]['jb_piutang_coa_kode'],
                            'coa_nama' => $coaJB[$list[$d]['jenis_biaya_id']]['jb_piutang_coa_nama'],
                            'coa_dk' => $coaJB[$list[$d]['jenis_biaya_id']]['jb_piutang_dk'],
                            'ket' => $list[$d]['jenis_biaya'],
                            'nominal' => $list[$d]['nominal']
                        );

                        /*
                          if($list[$d]['potongan'] > 0) {
                          $dataCoa[$prodiId][] = array(
                          'coa_id' => $coaJB[$list[$d]['jenis_biaya_id']]['jb_potongan_coa_id'],
                          'coa_kode' => $coaJB[$list[$d]['jenis_biaya_id']]['jb_potongan_coa_kode'],
                          'coa_nama' => $coaJB[$list[$d]['jenis_biaya_id']]['jb_potongan_coa_nama'],
                          'coa_dk' => $coaJB[$list[$d]['jenis_biaya_id']]['jb_potongan_dk'],
                          'ket' => $list[$d]['jenis_biaya'] .'(Potongan)',
                          'nominal' => $list[$d]['potongan']
                          );
                          }
                         */
                        /** end * */
                        $list[$d]['nomor'] = $start;
                        $list[$d]['f_prodi'] = $list[$d]['prodi'];
                        $list[$d]['f_nama'] = $list[$d]['jenis_biaya'];
                        $list[$d]['f_jenis_biaya'] = $list[$d]['jenis_biaya_id'];
                        $list[$d]['f_nominal'] = NumberFormat::Accounting($list[$d]['nominal'], 2);
                        $list[$d]['f_potongan'] = NumberFormat::Accounting($list[$d]['potongan'], 2);
                        $list[$d]['tipe_pembayaran'] = $dataList['tipe_pembayaran'];
                        $list[$d]['is_display'] = $displayNone;
                        $list[$d]['class_name'] = '';
                        //if ($prodiId == 0) {
                        $this->mrTemplate->SetAttribute('link_pilih', 'visibility', 'visible');
                        //} else {
                        //$this->mrTemplate->SetAttribute('link_pilih', 'visibility', 'hidden');
                        //}

                        $this->mrTemplate->AddVar('link_pilih', 'F_JENIS_BIAYA', $list[$d]['f_jenis_biaya']);
                        $this->mrTemplate->AddVar('link_pilih', 'F_PRODI', $list[$d]['f_prodi']);
                        $this->mrTemplate->AddVar('link_pilih', 'F_PRODI_NAMA', $list[$d]['f_nama']);
                        $this->mrTemplate->AddVar('link_pilih', 'T_NOMINAL', $list[$d]['total_nominal']);
                        $this->mrTemplate->AddVar('link_pilih', 'T_POTONGAN', 0);
                        $this->mrTemplate->AddVar('link_pilih', 'T_DEPOSIT', 0);
                        $this->mrTemplate->AddVar('link_pilih', 'T_DEPOSIT_MASUK', 0);
                        $this->mrTemplate->AddVar('link_pilih', 'TIPE_BAYAR', $list[$d]['tipe_pembayaran']);

                        $this->mrTemplate->AddVars('data_list', $list[$d]);
                        $this->mrTemplate->parseTemplate('data_list', 'a');
                        $d++;
                        $start++;
                    } else {
                        $prodiId = $list[$d]['prodi'];
                        /** data prodi 
                          $dataProdi[$list[$d]['prodi']]['prodi_id'] = $list[$d]['prodi'];
                          $dataProdi[$list[$d]['prodi']]['prodi_nama'] = $list[$d]['nama_prodi'];
                          $dataProdi[$list[$d]['prodi']]['prodi_nominal'] = $list[$d]['total_nominal'];
                          $dataProdi[$list[$d]['prodi']]['prodi_potongan'] = $list[$d]['total_potongan'];
                          $dataProdi[$list[$d]['prodi']]['prodi_deposit'] = 0;
                          $dataProdi[$list[$d]['prodi']]['prodi_deposit_masuk'] = 0;
                          $dataProdi[$list[$d]['prodi']]['prodi_tipe_pembayaran'] = $dataList['tipe_pembayaran'];
                          $dataProdi[$list[$d]['prodi']]['prodi_keterangan'] = '';
                         */
                        $list[$d]['f_prodi'] = $list[$d]['prodi'];
                        $list[$d]['f_nama'] = $list[$d]['nama_prodi'];
                        $list[$d]['f_nominal'] = NumberFormat::Accounting($list[$d]['total_nominal'], 2);
                        $list[$d]['f_potongan'] = NumberFormat::Accounting($list[$d]['total_potongan'], 2);
                        $list[$d]['tipe_pembayaran'] = $dataList['tipe_pembayaran'];
                        $list[$d]['class_name'] = 'table-highlight';
                        $list[$d]['is_display'] = $displayNone;
                        $this->mrTemplate->SetAttribute('link_pilih', 'visibility', 'hidden');
                        $this->mrTemplate->AddVar('link_pilih', 'F_PRODI', $list[$d]['f_prodi']);
                        $this->mrTemplate->AddVar('link_pilih', 'F_PRODI_NAMA', $list[$d]['f_nama']);
                        $this->mrTemplate->AddVar('link_pilih', 'T_NOMINAL', $list[$d]['total_nominal']);
                        $this->mrTemplate->AddVar('link_pilih', 'T_POTONGAN', 0);
                        $this->mrTemplate->AddVar('link_pilih', 'T_DEPOSIT', 0);
                        $this->mrTemplate->AddVar('link_pilih', 'T_DEPOSIT_MASUK', 0);
                        $this->mrTemplate->AddVar('link_pilih', 'TIPE_BAYAR', $list[$d]['tipe_pembayaran']);
                        $this->mrTemplate->AddVars('data_list', $list[$d]);
                        $this->mrTemplate->parseTemplate('data_list', 'a');
                    }
                }
            }
        } else {
            // untuk pengakuan
            $this->mrTemplate->AddVar('content', 'IS_DISPLAY', $displayYes);
            if (empty($dataList['data_list']['pengakuan'])) {
                $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
            } else {
                $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');

                $start = 1;
                //foreach ($dataList['data_list']['pengakuan_get'] as $list) {
                $list = $dataList['data_list']['pengakuan'];

                $prodiId = -1;
                $dataCoa = null;
                $dataJB = null;
                $dataProdi = null;
                for ($d = 0; $d < sizeof($list);) {
                    if ($prodiId == $list[$d]['prodi']) {
                        $jenisBiayaId = $list[$d]['jenis_biaya_id'];
                        $prodiId = $list[$d]['prodi'];
                        /** data prodi */
                        $dataProdi[$prodiId][$jenisBiayaId]['prodi_id'] = $list[$d]['prodi'];
                        $dataProdi[$prodiId][$jenisBiayaId]['prodi_nama'] = $list[$d]['nama_prodi'];
                        $dataProdi[$prodiId][$jenisBiayaId]['prodi_nominal'] = $list[$d]['total_nominal'];
                        $dataProdi[$prodiId][$jenisBiayaId]['prodi_potongan'] = $list[$d]['total_potongan'];
                        $dataProdi[$prodiId][$jenisBiayaId]['prodi_deposit'] = $list[$d]['total_penggunaan_deposit'];
                        $dataProdi[$prodiId][$jenisBiayaId]['prodi_deposit_masuk'] = 0; //$depMasuk;
                        $dataProdi[$prodiId][$jenisBiayaId]['prodi_tipe_pembayaran'] = $dataList['tipe_pembayaran'];
                        $dataProdi[$prodiId][$jenisBiayaId]['prodi_keterangan'] = $list[$d]['keterangan']; //$depMasukKet;
                        $dataProdi[$prodiId][$jenisBiayaId]['prodi_penanggung_jawab'] = $list[$d]['penanggung_jawab']; //$depMasukPj;
                        $dataProdi[$prodiId][$jenisBiayaId]['prodi_id_detail'] ='';
                        /**/
                        /** data jenis biaya * */
                        $dataJB[$prodiId][$jenisBiayaId][] = array(
                            'prodi_id' => $list[$d]['prodi'],
                            'prodi_nama' => $list[$d]['nama_prodi'],
                            'jenis_biaya_id' => $list[$d]['jenis_biaya_id'],
                            'jenis_biaya_nama' => $list[$d]['jenis_biaya'],
                            'nominal' => $list[$d]['nominal'],
                            'potongan' => $list[$d]['potongan'],
                            'deposit' => $list[$d]['penggunaan_deposit'],
                            'penanggung_jawab' => $list[$d]['penanggung_jawab'],
                            'keterangan' => $list[$d]['keterangan'],
                            'id_detail' => $list[$d]['id_detail'],
                            'tipe' => $list[$d]['tipe']
                        );

                        /** buat map coa * */
                        $dataCoa[$prodiId][$jenisBiayaId][] = array(
                            'coa_id' => $coaJB[$list[$d]['jenis_biaya_id']]['jb_pembayaran_coa_id'],
                            'coa_kode' => $coaJB[$list[$d]['jenis_biaya_id']]['jb_pembayaran_coa_kode'],
                            'coa_nama' => $coaJB[$list[$d]['jenis_biaya_id']]['jb_pembayaran_coa_nama'],
                            'coa_dk' => $coaJB[$list[$d]['jenis_biaya_id']]['jb_pembayaran_dk'],
                            'ket' => $list[$d]['jenis_biaya'],
                            'nominal' => $list[$d]['nominal']
                        );
                        if ($list[$d]['potongan'] > 0) {
                            $dataCoa[$prodiId][$jenisBiayaId][] = array(
                                'coa_id' => $coaJB[$list[$d]['jenis_biaya_id']]['jb_potongan_coa_id'],
                                'coa_kode' => $coaJB[$list[$d]['jenis_biaya_id']]['jb_potongan_coa_kode'],
                                'coa_nama' => $coaJB[$list[$d]['jenis_biaya_id']]['jb_potongan_coa_nama'],
                                'coa_dk' => $coaJB[$list[$d]['jenis_biaya_id']]['jb_potongan_dk'],
                                'ket' => $list[$d]['jenis_biaya'] . '(Potongan)',
                                'nominal' => $list[$d]['potongan']
                            );
                        }

                        if ($list[$d]['penggunaan_deposit']) {
                            $dataCoa[$prodiId][$jenisBiayaId][] = array(
                                'coa_id' => $coaJB[$list[$d]['jenis_biaya_id']]['jb_deposit_coa_id'],
                                'coa_kode' => $coaJB[$list[$d]['jenis_biaya_id']]['jb_deposit_coa_kode'],
                                'coa_nama' => $coaJB[$list[$d]['jenis_biaya_id']]['jb_deposit_coa_nama'],
                                'coa_dk' => $coaJB[$list[$d]['jenis_biaya_id']]['jb_deposit_dk'],
                                'ket' => $list[$d]['jenis_biaya'] . '(Deposit)',
                                'nominal' => $list[$d]['penggunaan_deposit']
                            );
                        }
                        /** end * */
                        $list[$d]['nomor'] = $start;
                        $list[$d]['f_prodi'] = $list[$d]['prodi'];
                        $list[$d]['f_jenis_biaya'] = $list[$d]['jenis_biaya_id'];
                        $list[$d]['f_nama'] = $list[$d]['jenis_biaya'];
                        $list[$d]['f_nominal'] = NumberFormat::Accounting($list[$d]['nominal'], 2);
                        $list[$d]['f_potongan'] = NumberFormat::Accounting($list[$d]['potongan'], 2);
                        $list[$d]['f_deposit'] = NumberFormat::Accounting($list[$d]['penggunaan_deposit'], 2);
                        //$depMasuk = $dataList['data_list']['deposit_masuk'][$list[$d]['prodi']]['pemasukan_deposit'];
                        $list[$d]['f_deposit_masuk'] = ''; // NumberFormat::Accounting($depMasuk, 2);
                        $list[$d]['tipe_pembayaran'] = $dataList['tipe_pembayaran'];
                        $list[$d]['class_name'] = '';
                        //if ($prodiId == 0) {
                        $this->mrTemplate->SetAttribute('link_pilih', 'visibility', 'visible');
                        //} else {
                        //$this->mrTemplate->SetAttribute('link_pilih', 'visibility', 'hidden');
                        //}
                        $this->mrTemplate->AddVar('link_pilih', 'F_JENIS_BIAYA', $list[$d]['f_jenis_biaya']);

                        $this->mrTemplate->AddVar('link_pilih', 'F_PRODI', $list[$d]['f_prodi']);
                        $this->mrTemplate->AddVar('link_pilih', 'F_PRODI_NAMA', $list[$d]['f_nama']);
                        $this->mrTemplate->AddVar('link_pilih', 'T_NOMINAL', $list[$d]['total_nominal']);
                        $this->mrTemplate->AddVar('link_pilih', 'T_POTONGAN', $list[$d]['total_potongan']);
                        $this->mrTemplate->AddVar('link_pilih', 'T_DEPOSIT', $list[$d]['total_penggunaan_deposit']);
                        //$this->mrTemplate->AddVar('link_pilih', 'T_DEPOSIT_MASUK',$depMasuk);
                        $this->mrTemplate->AddVar('link_pilih', 'TIPE_BAYAR', $list[$d]['tipe_pembayaran']);

                        $this->mrTemplate->AddVars('data_list', $list[$d]);
                        $this->mrTemplate->parseTemplate('data_list', 'a');
                        $d++;
                        $start++;
                    } else {
                        $prodiId = $list[$d]['prodi'];
                        //$depMasuk = $dataList['data_list']['deposit_masuk'][$list[$d]['prodi']]['pemasukan_deposit'];
                        //$depMasukKet = $dataList['data_list']['deposit_masuk'][$list[$d]['prodi']]['keterangan'];
                        //$depMasukPj = $dataList['data_list']['deposit_masuk'][$list[$d]['prodi']]['penanggung_jawab'];
                        /** data prodi */
                        /*
                          $dataProdi[$list[$d]['prodi']]['prodi_id'] = $list[$d]['prodi'];
                          $dataProdi[$list[$d]['prodi']]['prodi_nama'] = $list[$d]['nama_prodi'];
                          $dataProdi[$list[$d]['prodi']]['prodi_nominal'] = $list[$d]['total_prodi_nominal'];
                          $dataProdi[$list[$d]['prodi']]['prodi_potongan'] = $list[$d]['total_prodi_potongan'];
                          $dataProdi[$list[$d]['prodi']]['prodi_deposit'] = $list[$d]['total_prodi_penggunaan_deposit'];
                          $dataProdi[$list[$d]['prodi']]['prodi_deposit_masuk'] = $depMasuk;
                          $dataProdi[$list[$d]['prodi']]['prodi_tipe_pembayaran'] = $dataList['tipe_pembayaran'];
                          $dataProdi[$list[$d]['prodi']]['prodi_keterangan'] = $depMasukKet;
                          $dataProdi[$list[$d]['prodi']]['prodi_penanggung_jawab'] = $depMasukPj;
                         * 
                         */
                        /*
                          if ($depMasuk > 0) {
                          $dataCoa[$prodiId][] = array(
                          'coa_id' => $coaDepMasuk['coa_id'],
                          'coa_kode' => $coaDepMasuk['coa_kode'],
                          'coa_nama' => $coaDepMasuk['coa_nama'],
                          'coa_dk' => $coaDepMasuk['coa_dk'],
                          'ket' => $list[$d]['nama_prodi'] . ' (Deposit Masuk)',
                          'nominal' => $depMasuk
                          );
                          }
                         * 
                         */
                        $list[$d]['f_prodi'] = $list[$d]['prodi'];
                        $list[$d]['f_nama'] = $list[$d]['nama_prodi'];
                        $list[$d]['f_nominal'] = NumberFormat::Accounting($list[$d]['total_prodi_nominal'], 2);
                        $list[$d]['f_potongan'] = NumberFormat::Accounting($list[$d]['total_prodi_potongan'], 2);
                        $list[$d]['f_deposit'] = NumberFormat::Accounting($list[$d]['total_prodi_penggunaan_deposit'], 2);
                        //$list[$d]['f_deposit_masuk'] = NumberFormat::Accounting($depMasuk, 2);
                        $list[$d]['tipe_pembayaran'] = $dataList['tipe_pembayaran'];
                        $list[$d]['class_name'] = 'table-highlight';
                        $this->mrTemplate->SetAttribute('link_pilih', 'visibility', 'hidden');
                        $this->mrTemplate->AddVar('link_pilih', 'F_PRODI', $list[$d]['f_prodi']);
                        $this->mrTemplate->AddVar('link_pilih', 'F_PRODI_NAMA', $list[$d]['f_nama']);
                        $this->mrTemplate->AddVar('link_pilih', 'T_NOMINAL', $list[$d]['total_nominal']);
                        $this->mrTemplate->AddVar('link_pilih', 'T_POTONGAN', $list[$d]['total_potongan']);
                        $this->mrTemplate->AddVar('link_pilih', 'T_DEPOSIT', $list[$d]['total_penggunaan_deposit']);
                        //$this->mrTemplate->AddVar('link_pilih', 'T_DEPOSIT_MASUK', $depMasuk);
                        $this->mrTemplate->AddVar('link_pilih', 'TIPE_BAYAR', $list[$d]['tipe_pembayaran']);
                        $this->mrTemplate->AddVars('data_list', $list[$d]);
                        $this->mrTemplate->parseTemplate('data_list', 'a');
                    }
                }

                //end pengakuan
            }
        }

        if (!empty($dataCoa)) {
            $this->mrTemplate->AddVar('content', 'DATA_COA', json_encode($dataCoa));
        } else {
            $this->mrTemplate->AddVar('content', 'DATA_COA', 'null');
        }

        if (!empty($dataJB)) {
            $this->mrTemplate->AddVar('content', 'DATA_JENIS_BIAYA', json_encode($dataJB));
        } else {
            $this->mrTemplate->AddVar('content', 'DATA_JENIS_BIAYA', 'null');
        }
   
        if (!empty($dataProdi)) {
            $this->mrTemplate->AddVar('content', 'DATA_PRODI', json_encode($dataProdi));
        } else {
            $this->mrTemplate->AddVar('content', 'DATA_PRODI', 'null');
        }
    }

}

?>