<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/finansi_transaksi_penerimaan_bank_mhs/business/TransaksiPenerimaanBankMhs.class.php';

class ViewTransaksiPenerimaanBankMhs extends HtmlResponse {

    function TemplateModule() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'module/finansi_transaksi_penerimaan_bank_mhs/template');
        $this->SetTemplateFile('view_transaksi_penerimaan_bank_mhs.html');
    }

    function ProcessRequest() {
        $mObj = new TransaksiPenerimaanBankMhs();
        $messenger = Messenger::Instance()->Receive(__FILE__);
        $message = $style = NULL;
        $requestData = array();
        $queryString = '';
        $getdate = getdate();
        $currMonth = (int) $getdate['mon'];
        $currYear = (int) $getdate['year'];
        $arrTahun = $mObj->getTahunPencatatan();
        $tahunPembukuan   = $mObj->getTahunPembukuanPeriode(array('open' => true));
        $minYear = $arrTahun['min_year'];
        $maxYear = $arrTahun['max_year'];
        $arrPosting = array(
            array('id' => 'Y', 'name' => 'Sudah'),
            array('id' => 'T', 'name' => 'Belum')
        );

        if (isset($mObj->_POST['btnFilter'])) {
            $startDate_day = (int) $mObj->_POST['tanggal_awal_day'];
            $startDate_mon = (int) $mObj->_POST['tanggal_awal_mon'];
            $startDate_year = (int) $mObj->_POST['tanggal_awal_year'];
            $endDate_day = (int) $mObj->_POST['tanggal_akhir_day'];
            $endDate_mon = (int) $mObj->_POST['tanggal_akhir_mon'];
            $endDate_year = (int) $mObj->_POST['tanggal_akhir_year'];

            $requestData['start_date'] = date('Y-m-d', mktime(0, 0, 0, $startDate_mon, $startDate_day, $startDate_year));
            $requestData['end_date'] = date('Y-m-d', mktime(0, 0, 0, $endDate_mon, $endDate_day, $endDate_year));
            $requestData['referensi'] = trim($mObj->_POST['referensi']);
            $requestData['posting'] = strtoupper($mObj->_POST['posting']);
        } elseif (isset($mObj->_GET['search'])) {
            $requestData['start_date'] = date('Y-m-d', strtotime(Dispatcher::Instance()->Decrypt($mObj->_GET['start_date'])));
            $requestData['end_date'] = date('Y-m-d', strtotime(Dispatcher::Instance()->Decrypt($mObj->_GET['end_date'])));
            $requestData['referensi'] = Dispatcher::Instance()->Decrypt($mObj->_GET['referensi']);
            $requestData['posting'] = strtoupper(Dispatcher::Instance()->Decrypt($mObj->_GET['posting']));
        } else {
            $requestData['start_date'] = date('Y-m-d', mktime(0, 0, 0, $currMonth, 1, $currYear));
            $requestData['end_date'] = date('Y-m-t', mktime(0, 0, 0, $currMonth, 1, $currYear));
            $requestData['referensi'] = '';
            $requestData['posting'] = '';
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
                ) . '&search=' . Dispatcher::Instance()->Encrypt(1) . '&' . $queryString;

        $destination_id = "subcontent-element";
        $dataList = $mObj->getDataJurnal($offset, $limit, (array) $requestData);
        $total_data = $mObj->getCountJurnal((array) $requestData);

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
                'tanggal', 'Tanggal', 'view', 'html', 'tanggal_awal', array(
            $requestData['start_date'],
            $minYear,
            $maxYear,
            false,
            false,
            false
                ), Messenger::CurrentRequest
        );

        Messenger::Instance()->SendToComponent(
                'tanggal', 'Tanggal', 'view', 'html', 'tanggal_akhir', array(
            $requestData['end_date'],
            $minYear,
            $maxYear,
            false,
            false,
            false
                ), Messenger::CurrentRequest
        );

        Messenger::Instance()->SendToComponent(
                'combobox', 'Combobox', 'view', 'html', 'posting', array(
            'posting',
            $arrPosting,
            $requestData['posting'],
            true,
            ' style="width:100px;" id="cmb_posting"'
                ), Messenger::CurrentRequest);

        if ($messenger) {
            $message = $messenger[0][1];
            $style = $messenger[0][2];
        }
        $return['request_data'] = $requestData;
        $return['query_string'] = $queryString;
        $return['tahun_pembukuan'] = $tahunPembukuan;
        $return['message'] = $message;
        $return['style'] = $style;
        $return['data_list'] = $mObj->ChangeKeyName($dataList);
        $return['start'] = $offset + 1;
        return $return;
    }

    function ParseTemplate($data = NULL) {
        $requestData = (array) $data['request_data'];
        $queryString = $data['query_string'];
        $tahunPembukuan = $data['tahun_pembukuan'];
        $message = $data['message'];
        $style = $data['style'];
        $dataList = $data['data_list'];
        $start = $data['start'];
        $urlSearch = Dispatcher::Instance()->GetUrl(
                'finansi_transaksi_penerimaan_bank_mhs', 'TransaksiPenerimaanBankMhs', 'view', 'html'
        );

        $urlAdd = Dispatcher::Instance()->GetUrl(
                        'finansi_transaksi_penerimaan_bank_mhs', 'Transaksi', 'view', 'html'
                ) . '&' . $queryString;

        $urlEdit = Dispatcher::Instance()->GetUrl(
                        'finansi_transaksi_penerimaan_bank_mhs', 'editTransaksi', 'view', 'html'
                ) . '&' . $queryString;

        $urlExport    = Dispatcher::Instance()->GetUrl(
               'finansi_transaksi_penerimaan_bank_mhs',
               'BuktiTransaksi',
               'view',
               'xlsx'
            ).'&'.$query_string;

        $urlDetail    = Dispatcher::Instance()->GetUrl(
               'finansi_transaksi_penerimaan_bank_mhs',
               'BuktiTransaksi',
               'view',
               'html'
            ).'&'.$query_string;

        $urlCetak      = Dispatcher::Instance()->GetUrl(
                'finansi_transaksi_penerimaan_bank_mhs',
                'CetakBuktiTransaksi',
                'view',
                'html'
            ).'&'.$query_string;

        $parseUrl = parse_url($queryString);
        $urlExploded = explode('&', $parseUrl['path']);
        $urlIndex = 0;
        foreach ($urlExploded as $url) {
            list($urlKey[$urlIndex], $urlValue[$urlIndex]) = explode('=', $url);
            $patern = '/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/';
            $patern1 = '/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})/';
            if ((preg_match($patern, $urlValue[$urlIndex]) || preg_match($patern1, $urlValue[$urlIndex])) && strtotime($urlValue[$urlIndex]) !== false) {
                $urlValue[$urlIndex] = date('Y/m/d', strtotime($urlValue[$urlIndex]));
            }
            $urlIndex += 1;
        }
        unset($urlIndex);
        $keyUrl = implode('|', $urlKey);
        $valueUrl = implode('|', $urlValue);

        $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);
        $this->mrTemplate->AddVar('content', 'URL_ADD', $urlAdd);
        $this->mrTemplate->AddVars('content', $requestData);

        if ($message) {
            $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
            $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $message);
            $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $style);
        }

        if (empty($dataList)) {
            $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
        } else {
            $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
            $dataGrid = array();
            $pembukuanId = '';
            $transaksiId = '';
            $index = 0;
            $idx = 0;
            $dataJurnal = array();
            $rows = array();

            for ($i = 0; $i < count($dataList);) {
                if ((int) $transaksiId === (int) $dataList[$i]['id'] && (int) $pembukuanId === (int) $dataList[$i]['pembukuan_id']) {
                    $ks = $pembukuanId . '.' . $transaksiId;
                    $dataJurnal[$ks][$idx]['akun_id'] = $dataList[$i]['coa_id'];
                    $dataJurnal[$ks][$idx]['kode'] = $dataList[$i]['coa_kode_akun'];
                    $dataJurnal[$ks][$idx]['nama'] = $dataList[$i]['coa_nama_akun'];
                    $dataJurnal[$ks][$idx]['sub_account'] = $dataList[$i]['sub_account'];
                    $dataJurnal[$ks][$idx]['nominal_debet'] = number_format($dataList[$i]['nominal_debet'], 2, ',', '.');
                    $dataJurnal[$ks][$idx]['nominal_kredit'] = number_format($dataList[$i]['nominal_kredit'], 2, ',', '.');
                    $dataJurnal[$ks][$idx]['class_name'] = $className;
                    $rows[$ks]['row_span'] += 1;
                    $i++;
                    $idx++;
                } else {
                    unset($idx);
                    $idx = 0;
                    $pembukuanId = (int) $dataList[$i]['pembukuan_id'];
                    $transaksiId = (int) $dataList[$i]['id'];
                    $kodeSistem = $pembukuanId . '.' . $transaksiId;
                    if ($start % 2 <> 0) {
                        $className = 'table-common-even';
                    } else {
                        $className = '';
                    }
                    $dataJurnal[$kodeSistem][$idx]['id'] = $dataList[$i]['id'];
                    $dataJurnal[$kodeSistem][$idx]['nomor'] = $start;
                    $dataJurnal[$kodeSistem][$idx]['tp_id'] = $dataList[$i]['tpp_id'];
                    $dataJurnal[$kodeSistem][$idx]['pembukuan_id'] = $dataList[$i]['pembukuan_id'];
                    $dataJurnal[$kodeSistem][$idx]['kode_sistem'] = $kodeSistem;
                    $dataJurnal[$kodeSistem][$idx]['referensi'] = $dataList[$i]['referensi'];
                    $dataJurnal[$kodeSistem][$idx]['deskripsi'] = $dataList[$i]['catatan'];
                    $dataJurnal[$kodeSistem][$idx]['tanggal'] = $dataList[$i]['tanggal'];
                    $dataJurnal[$kodeSistem][$idx]['penanggung_jawab'] = $dataList[$i]['penanggung_jawab'];
                    $dataJurnal[$kodeSistem][$idx]['type'] = 'parent';
                    $dataJurnal[$kodeSistem][$idx]['status_approval'] = $dataList[$i]['status_approve'];
                    $dataJurnal[$kodeSistem][$idx]['status_posting'] = $dataList[$i]['status_posting'];
                    //$dataJurnal[$kodeSistem][$idx]['jurnal_balik'] = $dataList[$i]['jurnal_balik'];
                    //$dataJurnal[$kodeSistem][$idx]['has_jurnal'] = strtoupper($dataList[$i]['has_jurnal']);
                    $dataJurnal[$kodeSistem][$idx]['jurnal'] = $dataList[$i]['jurnal'];
                    $dataJurnal[$kodeSistem][$idx]['class_name'] = $className;
                    $rows[$kodeSistem]['row_span'] = 0;
                    $index++;
                    $start++;
                }
            }

            foreach ($dataJurnal as $grid) {
                foreach ($grid as $jurnal) {
                    if ($jurnal['type'] AND strtoupper($jurnal['type']) == 'PARENT') {
                        $jurnal['row_span'] = $rows[$jurnal['kode_sistem']]['row_span'];
                        $this->mrTemplate->AddVar('data_jurnal', 'LEVEL', 'PARENT');
                        // url delete
                        $urlAccept = 'finansi_transaksi_penerimaan_bank_mhs|DeleteTransaksi|do|json-search|' . $keyUrl . '-1|' . $valueUrl;
                        $urlReturn = 'finansi_transaksi_penerimaan_bank_mhs|TransaksiPenerimaanBankMhs|view|html-search|' . $keyUrl . '-1|' . $valueUrl;
                        $label = GTFWConfiguration::GetValue('language', 'transaksi_penerimaan_bank');
                        $message = 'Penghapusan Data ini akan menghapus Data secara permanen.';
                        $jurnal['url_delete'] = Dispatcher::Instance()->GetUrl(
                                        'confirm', 'confirmDelete', 'do', 'html'
                                ) . '&urlDelete=' . $urlAccept
                                . '&urlReturn=' . $urlReturn
                                . '&id=' . $jurnal['id'] . '.' . $jurnal['pembukuan_id']
                                . '&label=' . $label
                                . '&dataName=' . $jurnal['referensi']
                                . '&message=' . $message;

                        // condition status approve
                        if ($jurnal['status_approval'] == 'Y') {
                            $this->mrTemplate->AddVar('status_approval', 'APPROVE', 'YES');
                        } else {
                            $this->mrTemplate->AddVar('status_approval', 'APPROVE', 'NO');
                        }
                        // condition status posting
                        if ($jurnal['status_posting'] == 'Y') {
                            $this->mrTemplate->AddVar('status_posting', 'POSTING', 'YES');
                        } else {
                            $this->mrTemplate->AddVar('status_posting', 'POSTING', 'NO');
                        }

                        // condition links & cek tahun pembukuan aktif
                        if ($jurnal['status_approval'] == 'T') {
                            if($jurnal['tp_id'] != $tahunPembukuan[0]['id']){
                                $this->mrTemplate->AddVar('content_links', 'STATUS', 'APPROVE');
                                $this->mrTemplate->AddVar('content_links', 'DISPLAY_APPROVE', 'display: none;');
                            }else{
                                $this->mrTemplate->AddVar('content_links', 'STATUS', 'UNAPPROVE');
                            }
                        }

                         // condition links & cek tahun pembukuan aktif
                        if (($jurnal['status_approval'] == 'Y' OR $jurnal['status_approval'] == 'T') AND $jurnal['has_jurnal'] === 'YES') {
                            if($jurnal['tp_id'] != $tahunPembukuan[0]['id']){
                                $this->mrTemplate->AddVar('content_links', 'STATUS', 'APPROVE');
                                $this->mrTemplate->AddVar('content_links', 'DISPLAY_APPROVE', 'display: none;');
                            }else{
                                $this->mrTemplate->AddVar('content_links', 'STATUS', 'APPROVE');
                            }
                        }

                   
                        $this->mrTemplate->AddVar('content_links', 'URL_EDIT', $urlEdit . '&data_id=' . Dispatcher::Instance()->Encrypt($jurnal['id']) . '&pr_id=' . Dispatcher::Instance()->Encrypt($jurnal['pembukuan_id']));
                        $this->mrTemplate->AddVar('data_jurnal', 'URL_EXPORT', $urlExport . '&data_id=' . Dispatcher::Instance()->Encrypt($jurnal['id']) . '&pr_id=' . Dispatcher::Instance()->Encrypt($jurnal['pembukuan_id']));
                        $this->mrTemplate->AddVar('data_jurnal', 'URL_CETAK', $urlCetak . '&data_id=' . Dispatcher::Instance()->Encrypt($jurnal['id']) . '&pr_id=' . Dispatcher::Instance()->Encrypt($jurnal['pembukuan_id']));
                        $this->mrTemplate->AddVar('content_links', 'URL_DELETE', $jurnal['url_delete']);
                        $this->mrTemplate->AddVar('content_links', 'ID', $jurnal['id']);
                        $this->mrTemplate->AddVar('content_links', 'PEMBUKUAN_ID', $jurnal['pembukuan_id']);
                        $this->mrTemplate->AddVar('content_links', 'REFERENSI', $jurnal['referensi']);
                        
                        $this->mrTemplate->AddVars('data_jurnal', $jurnal);
                    } else {
                        $this->mrTemplate->AddVar('data_jurnal', 'LEVEL', 'DATA');
                        $this->mrTemplate->AddVars('data_jurnal', $jurnal);
                    }
                    $this->mrTemplate->parseTemplate('data_list', 'a');
                }
            }
        }
    }

}

?>