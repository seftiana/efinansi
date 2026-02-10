<?php
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/laporan_konsolidasi/business/AppLaporanKonsolidasi.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'main/function/date.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'main/function/number_format.class.php';

class ViewLaporanKonsolidasiArusKas extends HtmlResponse {
    protected $mObj;

    function TemplateModule() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot')
            . 'module/laporan_konsolidasi/template');
        $this->SetTemplateFile('view_laporan_konsolidasi_arus_kas.html');
    }

    function ProcessRequest() {
        $this->mObj = new AppLaporanKonsolidasi();

        $this->mObj->Setup(2);

        $periodePembukuanRange = $this->mObj->LaporanBuilder()->getPeriodePembukuan();

        $post = is_object($_POST) ? $_POST->AsArray() : $_POST;

        if (isset($post['btncari'])) {
            $startDate = $post['tanggal_awal_year'] . '-' . $post['tanggal_awal_mon'] . '-' . $post['tanggal_awal_day'];
            $endDate = $post['tanggal_akhir_year'] . '-' . $post['tanggal_akhir_mon'] . '-' . $post['tanggal_akhir_day'];

            $requestData['tanggal_awal'] = date('Y-m-d', strtotime($startDate));
            $requestData['tanggal_akhir'] = date('Y-m-d', strtotime($endDate));
        } else {
            $requestData['tanggal_awal'] = date('Y-m-d', strtotime($periodePembukuanRange['tanggal_awal']));
            $requestData['tanggal_akhir'] = date('Y-m-d');
        }

        $tahunTrans = $this->mObj->LaporanBuilder()->GetMinMaxThnTrans();
        Messenger::Instance()->SendToComponent('tanggal', 'Tanggal', 'view', 'html', 'tanggal_awal', array(
                $requestData['tanggal_awal'],
                $tahunTrans['minTahun'],
                $tahunTrans['maxTahun']
            ), Messenger::CurrentRequest
        );

        Messenger::Instance()->SendToComponent('tanggal', 'Tanggal', 'view', 'html', 'tanggal_akhir', array(
                $requestData['tanggal_akhir'],
                $tahunTrans['minTahun'],
                $tahunTrans['maxTahun']
            ), Messenger::CurrentRequest
        );
        
        $this->mObj->LaporanBuilder()->PrepareData(
            $requestData['tanggal_awal'],
            $requestData['tanggal_akhir'],
            '00'
        );
        $dataInstitute = $this->mObj->LaporanBuilder()->laporanView();

        $this->mObj->LaporanBuilder()->PrepareData(
            $requestData['tanggal_awal'],
            $requestData['tanggal_akhir'],
            '01'
        );
        $dataYayasan = $this->mObj->LaporanBuilder()->laporanView();

        $return['laporan_institute'] = $dataInstitute;
        $return['laporan_yayasan'] = $dataYayasan;
        $return['periode_nama'] = $this->mObj->LaporanBuilder()->getPeriodeNama();
        $return['request_data'] = $requestData;
        return $return;
    }

    function ParseTemplate($data = NULL) {
        $requestData = $data['request_data'];

        $this->mrTemplate->AddVar('content', 'URL_SEARCH',
            Dispatcher::Instance()->GetUrl(
                'laporan_konsolidasi',
                'LaporanKonsolidasiArusKas',
                'view',
                'html'
            )
        );

        $this->mrTemplate->AddVar('content', 'URL_CETAK',
            Dispatcher::Instance()->GetUrl(
                'laporan_konsolidasi',
                'CetakLaporanKonsolidasiArusKas',
                'view',
                'html'
            ).
            '&tanggal_awal='.Dispatcher::Instance()->Encrypt($requestData['tanggal_awal']).
            '&tanggal_akhir=' . Dispatcher::Instance()->Encrypt($requestData['tanggal_akhir'])
        );

        $this->mrTemplate->AddVar('content', 'URL_EXCEL',
            Dispatcher::Instance()->GetUrl(
                'laporan_konsolidasi',
                'ExcelLaporanKonsolidasiArusKas',
                'view',
                'xlsx'
            ).
            '&tanggal_awal='.Dispatcher::Instance()->Encrypt($requestData['tanggal_awal']).
            '&tanggal_akhir=' . Dispatcher::Instance()->Encrypt($requestData['tanggal_akhir'])
        );

        $urlDetil = Dispatcher::Instance()->GetUrl(
            'laporan_konsolidasi',
            'DetailLaporanKonsolidasi',
            'view',
            'html'
        ).
        '&tanggal_awal='.Dispatcher::Instance()->Encrypt($requestData['tanggal_awal']).
        '&tanggal_akhir=' . Dispatcher::Instance()->Encrypt($requestData['tanggal_akhir']);

        $gridList = $data['laporan_institute'];
        $dataYayasan = $data['laporan_yayasan'];
        
        foreach ($gridList as $key => $itemLaporan) {
            $itemLaporan['padding'] = ($itemLaporan['level'] - 1) * 15;
            if ($itemLaporan['is_summary'] == 'Y') {

                $itemLaporan['style'] = 'font-weight:bold';
                $pengali = 1;
                $jumlahSaldoKlp = $itemLaporan['saldo_summary'] * $pengali;
                $jumlahSaldoyayasan =$dataYayasan[$key]['saldo_summary'] * $pengali;
                $jumlahGabung = $jumlahSaldoKlp + $jumlahSaldoyayasan;
                
                $this->mrTemplate->AddVar('status', 'KELLAP_STATUS', 'SUMMARY');
            } else {

                $pengali = ($itemLaporan['is_tambah'] == 'T') ? -1 : 1;
                $itemLaporan['style'] = '';
                $jumlahSaldoKlp = $itemLaporan['saldo'] * $pengali;
                $jumlahSaldoyayasan =$dataYayasan[$key]['saldo'] * $pengali;
                $jumlahGabung = $jumlahSaldoKlp + $jumlahSaldoyayasan;

                if ($itemLaporan['is_child'] == '0') {
                    switch ($itemLaporan['level']) {
                        case '2': $title = '<h2>' . strtoupper($itemLaporan['nama']) . '</h2>';
                            $labelTotalAliranKas[] = strtoupper($itemLaporan['nama']);
                            break;
                        default : $title = '<b>' . $itemLaporan['nama'] . '</b>';
                            break;
                    }
                    $itemLaporan['nama'] = $title;
                    $this->mrTemplate->AddVar('status', 'KELLAP_STATUS', 'PARENT');
                } else {
                    if ($itemLaporan['level'] == 2) {
                        $title = '<b>' . $itemLaporan['nama'] . '</b>';
                    } else {
                        $title = $itemLaporan['nama'];
                    }
                    $itemLaporan['nama'] = $title;
                    $itemLaporan['url_detail'] = $urlDetil . '&kellap_id=' . $itemLaporan['id'];
                    $this->mrTemplate->AddVar('status', 'KELLAP_STATUS', 'CHILD');
                }
            }


            if ($jumlahSaldoKlp >= 0) {
                $itemLaporan['nominal_saldo'] = number_format($jumlahSaldoKlp, 2, ',', '.');
            } else {
                $itemLaporan['nominal_saldo'] = '(' . number_format($jumlahSaldoKlp * (-1), 2, ',', '.') . ' )';
            }

            if ($jumlahSaldoyayasan >= 0) {
                $itemLaporan['nominal_saldo_yayasan'] = number_format($jumlahSaldoyayasan, 2, ',', '.');
            } else {
                $itemLaporan['nominal_saldo_yayasan'] = '(' . number_format($jumlahSaldoyayasan * (-1), 2, ',', '.') . ' )';
            }

            if ($jumlahGabung >= 0) {
                $itemLaporan['nominal_saldo_gabung'] = number_format($jumlahGabung, 2, ',', '.');
            } else {
                $itemLaporan['nominal_saldo_gabung'] = '(' . number_format($jumlahGabung * (-1), 2, ',', '.') . ' )';
            }

            $this->mrTemplate->AddVar('status', 'KELLAP_URL_DETIL', $itemLaporan['url_detail']);
            $this->mrTemplate->AddVar('status', 'KELLAP_NOMINAL_SALDO', $itemLaporan['nominal_saldo']);
            $this->mrTemplate->AddVar('status', 'KELLAP_NOMINAL_SALDO_YAYASAN', $itemLaporan['nominal_saldo_yayasan']);
            $this->mrTemplate->AddVar('status', 'KELLAP_NOMINAL_SALDO_GABUNG', $itemLaporan['nominal_saldo_gabung']);
            $this->mrTemplate->AddVars('laporan_konsolidasi', $itemLaporan, 'KELLAP_');
            $this->mrTemplate->parseTemplate('laporan_konsolidasi', 'a');
        }
    }

}

?>
