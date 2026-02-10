<?php

/*
  @ClassName : ViewLapAktivitas
  @Copyright : PT Gamatechno Indonesia
  @Analyzed By : Nanang Ruswianto <nanang@gamatechno.com>
  @Designed By : Rosyid <rosyid@gamatechno.com>
  @Author By : Dyan Galih <galih@gamatechno.com>
  @Version : 1.0
  @StartDate : Jan 22, 2009
  @LastUpdate : Jan 22, 2009
  @Description :
 */
require_once GTFWConfiguration::GetValue('application', 'docroot') .
    'module/lap_posisi_keuangan/business/AppLapPosisiKeuangan.class.php';

require_once GTFWConfiguration::GetValue('application', 'docroot') .
    'main/function/date.php';

require_once GTFWConfiguration::GetValue('application', 'docroot') .
    'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewLapPosisiKeuangan extends HtmlResponse {

    protected $mObj;

    public function TemplateModule() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') .
                'module/lap_posisi_keuangan/template');
        $this->SetTemplateFile('view_lap_posisi_keuangan.html');
    }

    public function ProcessRequest() {
        $this->mObj = new AppLapPosisiKeuangan();
        $arrSubAkun = $this->mObj->getSubAccountCombo();

        $this->mObj->Setup();
        $periodePembukuanRange = $this->mObj->LaporanBuilder()->getPeriodePembukuan();

        $post = is_object($_POST) ? $_POST->AsArray() : $_POST;
        if (isset($post['btncari'])) {
            $startDate = $post['tanggal_awal_year'] . '-' . $post['tanggal_awal_mon'] . '-' . $post['tanggal_awal_day'];
            $endDate = $post['tanggal_akhir_year'] . '-' . $post['tanggal_akhir_mon'] . '-' . $post['tanggal_akhir_day'];

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

        $this->mrTemplate->AddVar('content', 'SUB_ACCOUNT_PATERN', $data['sub_account_patern']);
        $this->mrTemplate->AddVar('content', 'SUB_ACCOUNT', $requestData['sub_account']);

        $urlPopupUnit = Dispatcher::Instance()->GetUrl(
            'lap_posisi_keuangan', 'PopupUnitKerja', 'view', 'html'
        );

        $this->mrTemplate->AddVar('content', 'URL_POPUP_UNIT', $urlPopupUnit);
        $this->mrTemplate->AddVar('content', 'URL_SEARCH', Dispatcher::Instance()->GetUrl(
            'lap_posisi_keuangan', 'LapPosisiKeuangan', 'view', 'html'
        )
        );

        $queryStringSearch = '&tanggal_awal='.Dispatcher::Instance()->Encrypt($requestData['tanggal_awal']).
            '&tanggal_akhir='.Dispatcher::Instance()->Encrypt($requestData['tanggal_akhir']).
            '&sub_account='.Dispatcher::Instance()->Encrypt($requestData['sub_account']).
            '&cetak='.Dispatcher::Instance()->Encrypt('yes');

        $this->mrTemplate->AddVar('content', 'URL_CETAK', Dispatcher::Instance()->GetUrl(
            'lap_posisi_keuangan', 'CetakLapPosisiKeuangan', 'view', 'html'
        ).$queryStringSearch);

        $this->mrTemplate->AddVar('content', 'URL_CETAK_DETIL', Dispatcher::Instance()->GetUrl(
            'lap_posisi_keuangan', 'CetakLapPosisiKeuanganDetil', 'view', 'html'
        ).$queryStringSearch);

        $this->mrTemplate->AddVar('content', 'URL_EXCEL', Dispatcher::Instance()->GetUrl(
            'lap_posisi_keuangan', 'ExcelLapPosisiKeuangan', 'view', 'xlsx'
        ).$queryStringSearch);

        $this->mrTemplate->AddVar('content', 'URL_EXCEL_DETIL', Dispatcher::Instance()->GetUrl(
            'lap_posisi_keuangan', 'ExcelLapPosisiKeuanganDetil', 'view', 'xlsx'
        ).$queryStringSearch);

        $this->mrTemplate->AddVar('content', 'URL_EXCEL_ASET_BERSIH',
            Dispatcher::Instance()->GetUrl(
                'lap_posisi_keuangan',
                'ExcelLapAsetBersih',
                'view',
                'xlsx'
            ).$queryStringSearch
        );

        $urlDetil = Dispatcher::Instance()->GetUrl(
            'lap_posisi_keuangan', 'DetilLapPosisiKeuangan', 'view', 'html'
        ) .
            '&tanggal_awal=' . Dispatcher::Instance()->Encrypt($requestData['tanggal_awal']) .
            '&tanggal_akhir=' . Dispatcher::Instance()->Encrypt($requestData['tanggal_akhir'] .
            '&unit_kode=' . Dispatcher::Instance()->Encrypt($requestData['unit_kode']) .
            '&sub_account=' . Dispatcher::Instance()->Encrypt($requestData['sub_account'])
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
                    $itemLaporan['url_detail'] = $urlDetil . '&kellap_id=' . $itemLaporan['id'];
                    $itemLaporan['url_detail_bl'] = $urlDetil . '&kellap_id=' . $itemLaporan['id']. '&status=BL';
                    $this->mrTemplate->AddVar('status', 'KELLAP_STATUS', 'CHILD');
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

            $this->mrTemplate->AddVar('status', 'KELLAP_URL_DETIL', $itemLaporan['url_detail']);
            $this->mrTemplate->AddVar('status', 'KELLAP_URL_DETIL_BL', $itemLaporan['url_detail_bl']);
            $this->mrTemplate->AddVar('status', 'KELLAP_NOMINAL_SALDO', $itemLaporan['nominal_saldo']);
            $this->mrTemplate->AddVar('status', 'KELLAP_NOMINAL_SALDO_LALU', $itemLaporan['nominal_saldo_lalu']);
            $this->mrTemplate->AddVars('posisi_keuangan', $itemLaporan, 'KELLAP_');
            $this->mrTemplate->parseTemplate('posisi_keuangan', 'a');
        }
    }

}
