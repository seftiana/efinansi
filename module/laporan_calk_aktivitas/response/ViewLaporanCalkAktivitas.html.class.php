<?php
require_once GTFWConfiguration::GetValue('application', 'docroot').
    'module/laporan_calk_aktivitas/business/AppLaporanCalkAktivitas.class.php';

require_once GTFWConfiguration::GetValue('application', 'docroot').
    'main/function/date.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') .
    'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewLaporanCalkAktivitas extends HtmlResponse {

    protected $mObj;

    public function TemplateModule() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') .
            'module/laporan_calk_aktivitas/template');
        $this->SetTemplateFile('view_laporan_calk_aktivitas.html');
    }

    public function ProcessRequest() {
        $this->mObj = new AppLaporanCalkAktivitas();
        $arrSubAkun = $this->mObj->getSubAccountCombo();

        $this->mObj->Setup();

        $post = is_object($_POST) ? $_POST->AsArray() : $_POST;
        if (isset($post['btncari'])) {
            $startDate = $post['tanggal_awal_year'].'-'.$post['tanggal_awal_mon'].'-01';

            $requestData['tanggal_awal'] = date('Y-m-d', strtotime($startDate));
            $requestData['tanggal_akhir'] = date('Y-m-t', strtotime($startDate));
            $requestData['sub_account'] = $post['sub_account'] == 'all' ? '' : $post['sub_account'];
        } else {
            $requestData['tanggal_awal'] = date('Y-m-01');
            $requestData['tanggal_akhir'] = date('Y-m-t');
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
            $tahunTrans['maxTahun'],
            false,
            false,
            true
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

        $this->mrTemplate->AddVar('content', 'SUB_ACCOUNT_PATERN', $data['sub_account_patern']);
        $this->mrTemplate->AddVar('content', 'SUB_ACCOUNT', $requestData['sub_account']);

        $this->mrTemplate->AddVar('content', 'URL_SEARCH', Dispatcher::Instance()->GetUrl(
            'laporan_calk_aktivitas', 'LaporanCalkAktivitas', 'view', 'html'
        )
        );

        $filterQs = '&tanggal_awal=' . Dispatcher::Instance()->Encrypt($requestData['tanggal_awal']) .
            '&tanggal_akhir=' . Dispatcher::Instance()->Encrypt($requestData['tanggal_akhir']) .
            '&sub_account=' . Dispatcher::Instance()->Encrypt($requestData['sub_account']).
            '&cetak=' . Dispatcher::Instance()->Encrypt('yes');

        $this->mrTemplate->AddVar('content', 'URL_CETAK',
            Dispatcher::Instance()->GetUrl(
                'laporan_calk_aktivitas', 'CetakLaporanCalkAktivitas', 'view', 'html'
            ) . $filterQs
        );


        $this->mrTemplate->AddVar('content', 'URL_EXCEL',
            Dispatcher::Instance()->GetUrl(
                'laporan_calk_aktivitas', 'ExcelLaporanCalkAktivitas', 'view', 'xlsx'
            ) . $filterQs
        );

        $bulanIni = date("n", strtotime($requestData['tanggal_akhir']));
        $date = $requestData['tanggal_akhir'];
        $bulanLalu = date("n", strtotime("first day of $date -1 month"));
        $this->mrTemplate->AddVar('content', 'LABEL_BULAN_INI', $this->mObj->indonesianMonth[$bulanIni]);
        $this->mrTemplate->AddVar('content', 'LABEL_BULAN_LALU', $this->mObj->indonesianMonth[$bulanLalu]);
        
        $aktivitas = $data['get_kelompok_laporan'];

        foreach ($aktivitas as $itemLaporan) {
            $itemLaporan['padding'] = ($itemLaporan['level'] - 1) * 15;
            if ($itemLaporan['is_summary'] == 'Y') {
                $itemLaporan['style'] = 'font-weight:bold';
                $pengali = 1;
                $jumlahSaldoKlp = $itemLaporan['saldo_summary'] * $pengali;
                $jumlahSaldoKlpAkm = $jumlahSaldoKlp + ($itemLaporan['saldo_summary_lalu'] * $pengali);
                $jumlahSaldoTransLalu = $itemLaporan['saldo_summary_trans_lalu'] * $pengali;
                $this->mrTemplate->AddVar('status', 'KELLAP_STATUS', 'SUMMARY');
            } else {
                $pengali = ($itemLaporan['is_tambah'] == 'T') ? -1 : 1;
                $itemLaporan['style'] = '';
                $jumlahSaldoKlp = $itemLaporan['saldo'] * $pengali;
                $jumlahSaldoKlpAkm = $jumlahSaldoKlp + ($itemLaporan['saldo_lalu'] * $pengali);

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
                        $totalLalu = 0;
                        $totalAkm = 0;
                        foreach($dataDetail as $valueDet){
                            $valueDet['padding'] = ($itemLaporan['level']) * 15;

                            $nominal = $valueDet['kellap_coa_saldo']*$pengali;
                            $transLalu = $valueDet['kellap_coa_trans_lalu']*$pengali;
                            $akumulasi = $nominal + ( $valueDet['kellap_coa_akum']*$pengali);

                            if($transLalu != 0){
                                $valueDet['persentase'] = round((($nominal - $transLalu)/abs($transLalu))*100,2);
                            }else{
                                $pembagi = ($nominal==0) ? 1 : $nominal;
                                $valueDet['persentase'] = round(($nominal/(abs($pembagi)))*100,2);
                            }

                            $total += $nominal;
                            $totalLalu += $transLalu;
                            $totalAkm += $akumulasi;

                            if ($nominal >= 0) {
                                $valueDet['kellap_nominal_saldo'] = number_format($nominal, 2, ',', '.');
                            } else {
                                $valueDet['kellap_nominal_saldo'] = '('.number_format($nominal*(-1), 2, ',', '.').')';
                            }

                            if ($transLalu >= 0) {
                                $valueDet['kellap_nominal_trans_lalu'] = number_format($transLalu, 2, ',', '.');
                            } else {
                                $valueDet['kellap_nominal_trans_lalu'] = '('.number_format($transLalu*(-1), 2, ',', '.').')';
                            }

                            if ($akumulasi >= 0) {
                                $valueDet['kellap_nominal_akumulasi'] = number_format($akumulasi, 2, ',', '.');
                            } else {
                                $valueDet['kellap_nominal_akumulasi'] = '('.number_format($akumulasi*(-1), 2, ',', '.').')';
                            }
                            $valueDet['nomor_coa'] = $nomor;
                            $this->mrTemplate->AddVars('posisi_keuangan_coa', $valueDet, 'DET_');
                            $this->mrTemplate->parseTemplate('posisi_keuangan_coa', 'a');
                            $nomor++;
                        }

                        
                        if($totalLalu != 0){
                            $persentase = round((($total - $totalLalu)/abs($totalLalu))*100,2);
                        }else{
                            $pembagi = ($total==0) ? 1 : $total;
                            $persentase = round(($total/abs($pembagi))*100,2);
                        }

                        if ($total >= 0) {
                            $total = number_format($total, 2, ',', '.');
                        } else {
                            $total = '(' . number_format($total * (-1), 2, ',', '.') . ' )';
                        }

                        if ($totalAkm >= 0) {
                            $totalAkm = number_format($totalAkm, 2, ',', '.');
                        } else {
                            $totalAkm = '(' . number_format($totalAkm * (-1), 2, ',', '.') . ' )';
                        }

                        if ($totalLalu >= 0) {
                            $totalLalu = number_format($totalLalu, 2, ',', '.');
                        } else {
                            $totalLalu = '(' . number_format($totalLalu * (-1), 2, ',', '.') . ' )';
                        }

                        $this->mrTemplate->AddVar('is_show_detail', 'PADDING', $itemLaporan['padding']);
                        $this->mrTemplate->AddVar('is_show_detail', 'PARENT_NAMA', $itemLaporan['nama']);
                        $this->mrTemplate->AddVar('is_show_detail', 'TOTAL_SALDO', $total);
                        $this->mrTemplate->AddVar('is_show_detail', 'TOTAL_TRANS_LALU', $totalLalu);
                        $this->mrTemplate->AddVar('is_show_detail', 'TOTAL_AKUMULASI', $totalAkm);
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

            
            if($jumlahSaldoTransLalu != 0){
                $persentaseParent = round((($jumlahSaldoKlp - $jumlahSaldoTransLalu)/abs($jumlahSaldoTransLalu))*100,2);
            }else{
                $pembagi = ($jumlahSaldoKlp==0) ? 1 : $jumlahSaldoKlp;
                $persentaseParent = round((($jumlahSaldoKlp)/abs($pembagi))*100,2);
            }
            
            if ($jumlahSaldoKlpAkm >= 0) {
                $itemLaporan['nominal_saldo_akmumulasi'] = number_format($jumlahSaldoKlpAkm, 2, ',', '.');
            } else {
                $itemLaporan['nominal_saldo_akmumulasi'] = '(' . number_format($jumlahSaldoKlpAkm * (-1), 2, ',', '.') . ' )';
            }

            if ($jumlahSaldoTransLalu >= 0) {
                $itemLaporan['nominal_trans_lalu'] = number_format($jumlahSaldoTransLalu, 2, ',', '.');
            } else {
                $itemLaporan['nominal_trans_lalu'] = '(' . number_format($jumlahSaldoTransLalu * (-1), 2, ',', '.') . ' )';
            }

            $this->mrTemplate->AddVar('status', 'KELLAP_URL_DETIL', $itemLaporan['url_detail']);
            $this->mrTemplate->AddVar('status', 'KELLAP_URL_DETIL_BL', $itemLaporan['url_detail_bl']);
            $this->mrTemplate->AddVar('status', 'KELLAP_NOMINAL_SALDO', $itemLaporan['nominal_saldo']);
            $this->mrTemplate->AddVar('status', 'KELLAP_NOMINAL_SALDO_AKUMULASI', $itemLaporan['nominal_saldo_akmumulasi']);
            $this->mrTemplate->AddVar('status', 'KELLAP_NOMINAL_TRANS_LALU', $itemLaporan['nominal_trans_lalu']);
            $this->mrTemplate->AddVar('status', 'KELLAP_PERSENTASE', $persentaseParent);
            $this->mrTemplate->AddVars('posisi_keuangan', $itemLaporan, 'KELLAP_');
            $this->mrTemplate->parseTemplate('posisi_keuangan', 'a');
        }
    }

}
