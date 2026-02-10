<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot').
   'module/laporan_konsolidasi/business/AppLaporanKonsolidasi.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot').
   'main/function/date.php';
require_once GTFWConfiguration::GetValue('application', 'docroot').
   'main/function/number_format.class.php';

class ViewCetakLaporanKonsolidasiAktivitas extends HtmlResponse {
    protected $mObj;

    function TemplateModule() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/laporan_konsolidasi/template');
        $this->SetTemplateFile('view_cetak_laporan_konsolidasi_aktivitas.html');
    }
   
    function TemplateBase() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
        $this->SetTemplateFile('document-print.html');
        $this->SetTemplateFile('layout-common-print-custom-header.html');
    }
   
    function ProcessRequest() {
        $this->mObj = new AppLaporanKonsolidasi();

        $this->mObj->Setup(6);
        
        $GET = is_object($_GET) ? $_GET->AsArray() : $_GET;
        $requestData['tanggal_awal'] = $GET['tanggal_awal'];
        $requestData['tanggal_akhir'] = $GET['tanggal_akhir'];
        $header = strtoupper(GTFWConfiguration::GetValue('organization', 'header_lap_all'));
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
        $return['header']= $header;
        return $return;
    }
   
   function ParseTemplate($data = NULL) {
        $requestData = $data['request_data'];

        $this->mrTemplate->AddVar('content', 'TGL_AKHIR', IndonesianDate($requestData['tanggal_akhir'], 'yyyy-mm-dd'));
        $this->mrTemplate->AddVar('content', 'TGL_AWAL', IndonesianDate($requestData['tanggal_awal'], 'yyyy-mm-dd'));
        $this->mrTemplate->AddVar('content', 'HEADER_LAPORAN', $data['header']);
        
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
