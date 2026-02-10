<?php

require_once GTFWConfiguration::GetValue('application', 'docroot').
    'module/laporan_calk/business/AppLaporanCalk.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot').
    'main/function/date.php';
require_once GTFWConfiguration::GetValue('application', 'docroot').
    'main/function/number_format.class.php';

class ViewCetakLaporanCalk extends HtmlResponse {
    protected $mObj;

    public function TemplateModule() {
        $this->SetTemplateBasedir(
            GTFWConfiguration::GetValue('application', 'docroot').
                'module/laporan_calk/template'
        );
        $this->SetTemplateFile('view_cetak_laporan_calk.html');
    }

    public function TemplateBase() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
        $this->SetTemplateFile('document-print.html');
        $this->SetTemplateFile('layout-common-print-custom-header.html');
    }

    public function ProcessRequest() {
        $this->mObj = new AppLaporanCalk();
        $this->mObj->Setup();
        $get =  is_object($_GET) ? $_GET->AsArray() : $_GET;
        $tglAwal =  Dispatcher::Instance()->Decrypt($get['tanggal_awal']);
        $tglAkhir =  Dispatcher::Instance()->Decrypt($get['tanggal_akhir']);
        $subAccount = Dispatcher::Instance()->Decrypt($get['sub_account']);
        
        if($subAccount == '01-00-00-00-00-00-00'){
            $header = strtoupper(GTFWConfiguration::GetValue('organization', 'header_lap_yayasan'));
        }elseif($subAccount == '00-00-00-00-00-00-00'){
            $header = strtoupper(GTFWConfiguration::GetValue('organization', 'header_lap_perbanas'));
        }else{
            $header = strtoupper(GTFWConfiguration::GetValue('organization', 'header_lap_all'));
        }

        //prepare data posisi keuangan
        $this->mObj->LaporanBuilder()->PrepareData($tglAwal, $tglAkhir,$subAccount);
        $getKelompokLaporan = $this->mObj->LaporanBuilder()->laporanView();
        $return['get_kelompok_laporan'] = $getKelompokLaporan;
        $return['periode_nama'] = $this->mObj->LaporanBuilder()->getPeriodeNama();
        $return['periode_nama_ts'] = $this->mObj->LaporanBuilder()->getPeriodeNamaTs();
        $return['tgl_awal']= $tglAwal;
        $return['tgl_akhir']= $tglAkhir;
        $return['sub_account']= $subAccount;
        $return['header']= $header;

        return $return;
    }

    public function ParseTemplate($data = NULL) {
        $this->mrTemplate->AddVar('content', 'TGL_AWAL', IndonesianDate($data['tgl_awal'], 'yyyy-mm-dd'));
        $this->mrTemplate->AddVar('content', 'TGL_AKHIR', IndonesianDate($data['tgl_akhir'], 'yyyy-mm-dd'));
        $bulanIni = date("n", strtotime($data['tgl_akhir']));
        $date = $data['tgl_akhir'];
        $bulanLalu = date("n", strtotime("first day of $date -1 month"));
        $this->mrTemplate->AddVar('content', 'LABEL_BULAN_INI', $this->mObj->indonesianMonth[$bulanIni]);
        $this->mrTemplate->AddVar('content', 'LABEL_BULAN_LALU', $this->mObj->indonesianMonth[$bulanLalu]);
        $this->mrTemplate->AddVar('content', 'HEADER_LAPORAN', $data['header']);
        
        $posisiKeuangan = $data['get_kelompok_laporan'];
        foreach ($posisiKeuangan as $itemLaporan) {
            $itemLaporan['padding'] = ($itemLaporan['level'] - 1) * 15;
            if ($itemLaporan['is_summary'] == 'Y') {

                $itemLaporan['style'] = 'font-weight:bold';
                $pengali = 1;
                $jumlahSaldoKlp = $itemLaporan['saldo_summary'] * $pengali;
                $jumlahSaldoKlpBl = $itemLaporan['saldo_summary_lalu'] * $pengali;
                $this->mrTemplate->AddVar('status', 'KELLAP_STATUS', 'SUMMARY');
            } else {
                $pengali = ($itemLaporan['is_tambah'] == 'T') ? -1 : 1;
                $itemLaporan['style'] = '';
                $jumlahSaldoKlp = $itemLaporan['saldo'] * $pengali;
                $jumlahSaldoKlpBl = $itemLaporan['saldo_lalu'] * $pengali;

                if ($itemLaporan['is_child'] == '0') {
                    if($itemLaporan['level'] == '2') {
                        $title = '<h2>' . strtoupper($itemLaporan['nama']) . '</h2>';
                        $labelTotalAliranKas[] = strtoupper($itemLaporan['nama']);
                    }else{
                        $title = '<b>' . $itemLaporan['nama'] . '</b>';
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

                    $dataDetail = $this->mObj->LaporanBuilder()->getLaporanDetail($data['tgl_awal'], $data['tgl_akhir'], $itemLaporan['id'],$data['sub_account'],$status);
                    if(!empty($dataDetail)){
                        $this->mrTemplate->AddVar('is_show_detail', 'SHOW_DETAIL', 'YA');

                        $this->mrTemplate->ClearTemplate('posisi_keuangan_coa');
                        $this->mrTemplate->SetAttribute('posisi_keuangan_coa', 'visibility', 'visible');

                        $nomor = 1;
                        $total = 0;
                        $totalBl = 0;
                        foreach($dataDetail as $valueDet){
                            $valueDet['padding'] = ($itemLaporan['level'] + 1) * 15;

                            $nominal = $valueDet['kellap_coa_saldo']*$pengali;
                            $nominalLalu = $valueDet['kellap_coa_saldo_lalu']*$pengali;

                            if($nominalLalu != 0){
                                $valueDet['persentase'] = round((($nominal - $nominalLalu)/$nominalLalu),2)*100;
                            }else{
                                $valueDet['persentase'] = 0;
                            }
                            $total += $nominal;
                            $totalBl += $nominalLalu;

                            if ($nominal >= 0) {
                                $valueDet['kellap_nominal_saldo'] = number_format($nominal, 2, ',', '.');
                            } else {
                                $valueDet['kellap_nominal_saldo'] = '(' . number_format($nominal * (-1), 2, ',', '.') . ' )';
                            }

                            if ($nominalLalu >= 0) {
                                $valueDet['kellap_nominal_saldo_lalu'] = number_format($nominalLalu, 2, ',', '.');
                            } else {
                                $valueDet['kellap_nominal_saldo_lalu'] = '(' . number_format($nominalLalu * (-1), 2, ',', '.') . ' )';
                            }

                            $valueDet['nomor_coa'] = $nomor;
                            $this->mrTemplate->AddVars('posisi_keuangan_coa', $valueDet, 'DET_');
                            $this->mrTemplate->parseTemplate('posisi_keuangan_coa', 'a');
                            $nomor++;
                        }

                        if ($total >= 0) {
                            $total = number_format($total, 2, ',', '.');
                        } else {
                            $total = '(' . number_format($total * (-1), 2, ',', '.') . ' )';
                        }

                        if ($totalBl >= 0) {
                            $totalBl = number_format($totalBl, 2, ',', '.');
                        } else {
                            $totalBl = '(' . number_format($totalBl * (-1), 2, ',', '.') . ' )';
                        }

                        if($totalBl != 0){
                            $persentase = round((($total - $totalBl)/$totalBl),2)*100;
                        }else{
                            $persentase = 0;
                        }

                        $this->mrTemplate->AddVar('is_show_detail', 'PADDING', $itemLaporan['padding']);
                        $this->mrTemplate->AddVar('is_show_detail', 'PARENT_NAMA', $itemLaporan['nama']);
                        $this->mrTemplate->AddVar('is_show_detail', 'TOTAL_SALDO', $total);
                        $this->mrTemplate->AddVar('is_show_detail', 'TOTAL_BL', $totalBl);
                        $this->mrTemplate->AddVar('is_show_detail', 'PERSENTASE', $persentase);
                    }else{
                        $this->mrTemplate->SetAttribute('posisi_keuangan_coa', 'visibility', 'hidden');
                    }

                }
            }


            if ($jumlahSaldoKlp >= 0) {
                $itemLaporan['nominal_saldo'] = number_format($jumlahSaldoKlp, 2, ',', '.');
            } else {
                $itemLaporan['nominal_saldo'] = '(' . number_format($jumlahSaldoKlp * (-1), 2, ',', '.') . ' )';
            }

            if ($jumlahSaldoKlpBl >= 0) {
                $itemLaporan['nominal_saldo_lalu'] = number_format($jumlahSaldoKlpBl, 2, ',', '.');
            } else {
                $itemLaporan['nominal_saldo_lalu'] = '(' . number_format($jumlahSaldoKlpBl * (-1), 2, ',', '.') . ' )';
            }
            
            if($jumlahSaldoKlpBl != 0){
                $persentaseParent = round((($jumlahSaldoKlp - $jumlahSaldoKlpBl)/$jumlahSaldoKlpBl),2)*100;
            }else{
                $persentaseParent = 0;
            }

            $this->mrTemplate->AddVar('status', 'KELLAP_NOMINAL_SALDO', $itemLaporan['nominal_saldo']);
            $this->mrTemplate->AddVar('status', 'KELLAP_NOMINAL_SALDO_LALU', $itemLaporan['nominal_saldo_lalu']);
            $this->mrTemplate->AddVar('status', 'KELLAP_PERSENTASE', $persentaseParent);
            $this->mrTemplate->AddVars('posisi_keuangan', $itemLaporan, 'KELLAP_');
            $this->mrTemplate->parseTemplate('posisi_keuangan', 'a');
        }
    }

}
