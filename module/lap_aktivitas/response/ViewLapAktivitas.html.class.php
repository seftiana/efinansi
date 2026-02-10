<?php
/**
 * 
 * @ClassName : ViewLapAktivitas
 * @copyright (c) PT Gamatechno Indonesia
 * @analyzed by : Nanang Ruswianto <nanang@gamatechno.com>
 * @designed by Rosyid <rosyid@gamatechno.com>
 * @author by Dyan Galih <galih@gamatechno.com>
 * @modified by noor hadi <noorhadi@gamatechno.com>
 * @Version : 1.0
 * @StartDate : Jan 22, 2009
 * @LastUpdate : Jan 27, 2016
 * @Description :
 * 
 */

require_once GTFWConfiguration::GetValue('application', 'docroot') .
    'module/lap_aktivitas/business/AppLapAktifitas.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') .
    'main/function/date.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') .
    'main/function/number_format.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') .
    'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewLapAktivitas extends HtmlResponse {
    protected $mObj;

    function TemplateModule() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'module/lap_aktivitas/template');
        $this->SetTemplateFile('view_lap_aktivitas.html');
    }

    function ProcessRequest() {
        $this->mObj = new AppLapAktivitas();
        $arrSubAkun = $this->mObj->getSubAccountCombo();
        
        $this->mObj->LaporanBuilder();

        $this->mObj->Setup();
        $periodePembukuan = $this->mObj->LaporanBuilder()->getPeriodePembukuan();

        $post = is_object($_POST) ? $_POST->AsArray() : $_POST;

        if (isset($post['btncari'])) {
            $startDate = $post['tanggal_awal_year'] . '-' . $post['tanggal_awal_mon'] . '-' . $post['tanggal_awal_day'];
            $endDate = $post['tanggal_akhir_year'] . '-' . $post['tanggal_akhir_mon'] . '-' . $post['tanggal_akhir_day'];

            $requestData['tanggal_awal'] = date('Y-m-d', strtotime($startDate));
            $requestData['tanggal_akhir'] = date('Y-m-d', strtotime($endDate));
            
            $requestData['sub_account'] = $post['sub_account'];
        } else {
            $requestData['sub_account'] = 'all';
            $requestData['tanggal_awal'] = date('Y-m-d', strtotime($periodePembukuan['tanggal_awal']));
            $requestData['tanggal_akhir'] = date('Y-m-d');
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

        //tahun untuk combo
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

        //prepare data aliranKas
        $this->mObj->LaporanBuilder()->PrepareData(
            $requestData['tanggal_awal'],
            $requestData['tanggal_akhir'],
            $requestData['sub_account']
        );
        $getKelompokLaporan = $this->mObj->LaporanBuilder()->laporanView();
        
        $return['get_kelompok_laporan'] = $getKelompokLaporan;
        $return['request_data'] = $requestData;

        return $return;
    }

    function ParseTemplate($data = NULL) {
        $requestData = $data['request_data'];

        $this->mrTemplate->AddVar('content', 'SUB_ACCOUNT', $requestData['sub_account']);

        $this->mrTemplate->AddVar('content', 'URL_SEARCH',
            Dispatcher::Instance()->GetUrl(
                'lap_aktivitas',
                'LapAktivitas',
                'view',
                'html'
            )
        );

        $this->mrTemplate->AddVar('content', 'URL_CETAK',
            Dispatcher::Instance()->GetUrl(
                'lap_aktivitas',
                'CetakLapAktivitas',
                'view',
                'html'
            ).
            '&tanggal_awal=' . Dispatcher::Instance()->Encrypt($requestData['tanggal_awal']) .
            '&tanggal_akhir=' . Dispatcher::Instance()->Encrypt($requestData['tanggal_akhir']) .
            '&sub_account=' . Dispatcher::Instance()->Encrypt($requestData['sub_account'])
        );

        $this->mrTemplate->AddVar('content', 'URL_EXCEL',
            Dispatcher::Instance()->GetUrl(
                'lap_aktivitas',
                'ExcelLapAktivitas',
                'view',
                'xlsx'
            ).
            '&tanggal_awal=' . Dispatcher::Instance()->Encrypt($requestData['tanggal_awal']) .
            '&tanggal_akhir=' . Dispatcher::Instance()->Encrypt($requestData['tanggal_akhir']) .
            '&sub_account=' . Dispatcher::Instance()->Encrypt($requestData['sub_account'])
        );

        //detail
        $this->mrTemplate->AddVar('content', 'URL_CETAK_DETIL',
            Dispatcher::Instance()->GetUrl(
                'lap_aktivitas',
                'CetakLapAktivitasDetil',
                'view',
                'html'
            ). 
            '&tanggal_awal=' . Dispatcher::Instance()->Encrypt($requestData['tanggal_awal']) .
            '&tanggal_akhir=' . Dispatcher::Instance()->Encrypt($requestData['tanggal_akhir']) .
            '&sub_account=' . Dispatcher::Instance()->Encrypt($requestData['sub_account'])
        );

        $this->mrTemplate->AddVar('content', 'URL_EXCEL_DETIL', 
            Dispatcher::Instance()->GetUrl(
                'lap_aktivitas',
                'ExcelLapAktivitasDetil',
                'view',
                'xlsx'
            ).
            '&tanggal_awal=' . Dispatcher::Instance()->Encrypt($requestData['tanggal_awal']) .
            '&tanggal_akhir=' . Dispatcher::Instance()->Encrypt($requestData['tanggal_akhir']) .
            '&sub_account=' . Dispatcher::Instance()->Encrypt($requestData['sub_account'])
        );
        
        $urlDetil = Dispatcher::Instance()->GetUrl(
            'lap_aktivitas',
            'detilLaporanAktivitas',
            'view',
            'html'
        ).
        '&tanggal_awal=' . Dispatcher::Instance()->Encrypt($requestData['tanggal_awal']) .
        '&tanggal_akhir=' . Dispatcher::Instance()->Encrypt($requestData['tanggal_akhir']) .
        '&sub_account=' . Dispatcher::Instance()->Encrypt($requestData['sub_account']);
        
        $aliranKas = $data['get_kelompok_laporan'];

        foreach ($aliranKas as $key => $itemLaporan) {
            $itemLaporan['padding'] = ($itemLaporan['level'] - 1) * 15;
            if ($itemLaporan['is_summary'] == 'Y') {

                $itemLaporan['style'] = 'font-weight:bold';
                $pengali = 1;
                $jumlahSaldoKlp = $itemLaporan['saldo_summary'] * $pengali;
                $jumlahSaldoKlpTs = $itemLaporan['saldo_summary_lalu'] * $pengali;
                $this->mrTemplate->AddVar('status', 'KELLAP_STATUS', 'SUMMARY');
            } else {

                $pengali = ($itemLaporan['is_tambah'] == 'T') ? -1 : 1;
                $itemLaporan['style'] = '';
                $jumlahSaldoKlp = $itemLaporan['saldo'] * $pengali;
                $jumlahSaldoKlpTs = $itemLaporan['saldo_lalu'] * $pengali;

                if ($itemLaporan['is_child'] == '0') {
                    switch ($itemLaporan['level']) {
                        case '2': $title = '<h2>' . strtoupper($itemLaporan['nama']) . '</h2>';
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
                    $itemLaporan['url_detail_ts'] = $urlDetil . '&kellap_id=' . $itemLaporan['id'] . '&status=TL';
                    $this->mrTemplate->AddVar('status', 'KELLAP_STATUS', 'CHILD');
                }
            }


            if ($jumlahSaldoKlp >= 0) {
                $itemLaporan['nominal_saldo'] = number_format($jumlahSaldoKlp, 2, ',', '.');
            } else {
                $itemLaporan['nominal_saldo'] = '(' . number_format($jumlahSaldoKlp * (-1), 2, ',', '.') . ' )';
            }

            $this->mrTemplate->AddVar('status', 'KELLAP_URL_DETIL', $itemLaporan['url_detail']);
            $this->mrTemplate->AddVar('status', 'KELLAP_NOMINAL_SALDO', $itemLaporan['nominal_saldo']);
            $this->mrTemplate->AddVars('aktivitas', $itemLaporan, 'KELLAP_');
            $this->mrTemplate->parseTemplate('aktivitas', 'a');
        }
    }

}

?>