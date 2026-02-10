<?php
require_once GTFWConfiguration::GetValue('application', 'docroot').
    'module/laporan_calk/business/AppLaporanCalk.class.php';

require_once GTFWConfiguration::GetValue('application', 'docroot').
    'main/function/date.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') .
    'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewLaporanCalk extends HtmlResponse {

    protected $mObj;

    public function TemplateModule() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') .
            'module/laporan_calk/template');
        $this->SetTemplateFile('view_laporan_calk.html');
    }

    public function ProcessRequest() {
        $this->mObj = new AppLaporanCalk();
        $arrSubAkun = $this->mObj->getSubAccountCombo();

        $this->mObj->Setup();
        $periodePembukuanRange = $this->mObj->LaporanBuilder()->getPeriodePembukuan();

        $post = is_object($_POST) ? $_POST->AsArray() : $_POST;
        if (isset($post['btncari'])) {
            $startDate = $post['tanggal_awal_year'].'-'.$post['tanggal_awal_mon'].'-'.$post['tanggal_awal_day'];
            $endDate = $post['tanggal_akhir_year'].'-'.$post['tanggal_akhir_mon'].'-'.$post['tanggal_akhir_day'];

            $requestData['tanggal_awal'] = date('Y-m-d', strtotime($startDate));
            $requestData['tanggal_akhir'] = date('Y-m-d', strtotime($endDate));
            $requestData['sub_account'] = $post['sub_account'] == 'all' ? '' : $post['sub_account'];
        } else {
            $requestData['tanggal_awal'] = date('Y-m-d', strtotime($periodePembukuanRange['tanggal_awal']));
            $requestData['tanggal_akhir'] = date('Y-m-d');
            $requestData['sub_account'] = '';
        }

        $user_id = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
        $objUnit = new UserUnitKerja();
        $userUnit = $objUnit->GetUnitKerjaUser($user_id);
        
        if(preg_match("/YAYASAN/i",$userUnit['unit_kerja_nama'])){
            $disabled = '';
        }else{
            $disabled = 'disabled';
            $requestData['sub_account'] = '00-00-00-00-00-00-00';
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

        Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 'sub_account', array(
            'sub_account',
            $arrSubAkun,
            $requestData['sub_account'],
            "true",
            " $disabled"
        ),Messenger::CurrentRequest);

        $this->mObj->LaporanBuilder()->PrepareData(
            $requestData['tanggal_awal'],
            $requestData['tanggal_akhir'],
            $requestData['sub_account']
        );

        $getKelompokLaporan = $this->mObj->LaporanBuilder()->laporanView();

        $return['get_kelompok_laporan'] = $getKelompokLaporan;
        $return['periode_nama'] = $this->mObj->LaporanBuilder()->getPeriodeNama();
        $return['request_data'] = $requestData;
        return $return;
    }

    public function ParseTemplate($data = null) {
        $requestData = $data['request_data'];
        $this->mrTemplate->AddVar('content', 'TAHUN_SEKARANG', $data['periode_nama']);

        $this->mrTemplate->AddVar('content', 'TANGGAL_AWAL', $requestData['tanggal_awal']);
        $this->mrTemplate->AddVar('content', 'TANGGAL_AKHIR', $requestData['tanggal_akhir']);
        $this->mrTemplate->AddVar('content', 'URL_SEARCH', Dispatcher::Instance()->GetUrl(
            'laporan_calk', 'LaporanCalk', 'view', 'html'
        )
        );

        $filterQs = '&tanggal_awal=' . Dispatcher::Instance()->Encrypt($requestData['tanggal_awal']) .
            '&tanggal_akhir=' . Dispatcher::Instance()->Encrypt($requestData['tanggal_akhir']) .
            '&sub_account=' . Dispatcher::Instance()->Encrypt($requestData['sub_account']).
            '&cetak=' . Dispatcher::Instance()->Encrypt('yes');

        $this->mrTemplate->AddVar('content', 'URL_CETAK',
            Dispatcher::Instance()->GetUrl(
                'laporan_calk', 'CetakLaporanCalk', 'view', 'html'
            ) . $filterQs
        );


        $this->mrTemplate->AddVar('content', 'URL_EXCEL',
            Dispatcher::Instance()->GetUrl(
                'laporan_calk', 'ExcelLaporanCalk', 'view', 'xlsx'
            ) . $filterQs
        );

        $bulanIni = date("n", strtotime($requestData['tanggal_akhir']));
        $date = $requestData['tanggal_akhir'];
        $bulanLalu = date("n", strtotime("first day of $date -1 month"));
        $this->mrTemplate->AddVar('content', 'LABEL_BULAN_INI', $this->mObj->indonesianMonth[$bulanIni]);
        $this->mrTemplate->AddVar('content', 'LABEL_BULAN_LALU', $this->mObj->indonesianMonth[$bulanLalu]);
        
        /**
         * posisi keuangan
         */
        $aliranKas = $data['get_kelompok_laporan'];

        foreach ($aliranKas as $itemLaporan) {
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

                    $dataDetail = $this->mObj->LaporanBuilder()->getLaporanDetail(
                        $requestData['tanggal_awal'],
                        $requestData['tanggal_akhir'],
                        $itemLaporan['id'],
                        $requestData['sub_account'],
                        $status
                    );

                    if(!empty($dataDetail)){
                        $this->mrTemplate->AddVar('is_show_detail', 'SHOW_DETAIL', 'YA');

                        $this->mrTemplate->ClearTemplate('posisi_keuangan_coa');
                        $this->mrTemplate->SetAttribute('posisi_keuangan_coa', 'visibility', 'visible');
                        $nomor = 1;
                        $total = 0;
                        $totalBl = 0;
                        foreach($dataDetail as $valueDet){
                            $valueDet['padding'] = ($itemLaporan['level']) * 15;

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
                                $valueDet['kellap_nominal_saldo'] = '('.number_format($nominal*(-1), 2, ',', '.').')';
                            }

                            if ($nominalLalu >= 0) {
                                $valueDet['kellap_nominal_saldo_lalu'] = number_format($nominalLalu, 2, ',', '.');
                            } else {
                                $valueDet['kellap_nominal_saldo_lalu'] = '('.number_format($nominalLalu*(-1), 2, ',', '.').')';
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

                        if($totalBl != 0){
                            $persentase = round((($total - $totalBl)/$totalBl),2)*100;
                        }else{
                            $persentase = 0;
                        }

                        if ($totalBl >= 0) {
                            $totalBl = number_format($totalBl, 2, ',', '.');
                        } else {
                            $totalBl = '(' . number_format($totalBl * (-1), 2, ',', '.') . ' )';
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


            $this->mrTemplate->AddVar('status', 'KELLAP_URL_DETIL', $itemLaporan['url_detail']);
            $this->mrTemplate->AddVar('status', 'KELLAP_URL_DETIL_BL', $itemLaporan['url_detail_bl']);
            $this->mrTemplate->AddVar('status', 'KELLAP_NOMINAL_SALDO', $itemLaporan['nominal_saldo']);
            $this->mrTemplate->AddVar('status', 'KELLAP_NOMINAL_SALDO_LALU', $itemLaporan['nominal_saldo_lalu']);
            $this->mrTemplate->AddVar('status', 'KELLAP_PERSENTASE', $persentaseParent);
            $this->mrTemplate->AddVars('posisi_keuangan', $itemLaporan, 'KELLAP_');
            $this->mrTemplate->parseTemplate('posisi_keuangan', 'a');
        }
    }

}
