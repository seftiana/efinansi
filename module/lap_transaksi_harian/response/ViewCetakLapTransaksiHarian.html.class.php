<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/lap_transaksi_harian/business/AppLapTransaksiHarian.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'main/function/date.php';


require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'main/function/number_format.class.php';


class ViewCetakLapTransaksiHarian extends HtmlResponse {
    #var $Pesan;

    protected $mObj;
    
    function TemplateModule() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'module/lap_transaksi_harian/template');
        $this->SetTemplateFile('view_cetak_lap_transaksi_harian.html');
    }

    function TemplateBase() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
        $this->SetTemplateFile('document-print.html');
        $this->SetTemplateFile('layout-common-print.html');
    }

    function ProcessRequest() {
        $this->mObj = new AppLapTransaksiHarian();
        $_GET = $_GET->AsArray();
        #print_r($_GET); exit;
        $tgl_awal = Dispatcher::Instance()->Decrypt($_GET['tgl_awal']);
        $tgl = Dispatcher::Instance()->Decrypt($_GET['tgl']);
        $jenis_transaksi = Dispatcher::Instance()->Decrypt($_GET['jenis_transaksi']);

        $data_cetak = $this->mObj->GetDataCetak($tgl_awal, $tgl, $jenis_transaksi);
        $this->mObj->prepareDataSaldoAwal($tgl_awal, $tgl,$jenis_transaksi);
        //print_r($data_cetak); exit;
        $data['tgl_awal'] = $tgl_awal;
        $data['tgl'] = $tgl;
        $data['jenis_transaksi'] = $jenis_transaksi;
        $data['transaksi'] = $data_cetak;
        return $data;
    }

    function ParseTemplate($data = NULL) {
        $Obj = new AppLapTransaksiHarian();
        if (empty($data['transaksi'])) {
            $this->mrTemplate->AddVar('data', 'DATA_EMPTY', 'YES');
        } else {
            $this->mrTemplate->AddVar('data', 'DATA_EMPTY', 'NO');
            //$this->mrTemplate->AddVar('content', 'TGL_TRANSAKSI', $this->date2string($data['tgl_transaksi']));
            $this->mrTemplate->AddVar('content', 'TGL_AWAL', $this->date2string($data['tgl_awal']));
            $this->mrTemplate->AddVar('content', 'TGL_AKHIR', $this->date2string($data['tgl']));
            #print_r($data['transaksi']); exit;

            $no = 1;
            for ($i = 0; $i < sizeof($data['transaksi']); $i++) {
                if ($data['transaksi'][$i]['coa_kode_akun'] != $data['transaksi'][$i - 1]['coa_kode_akun']) {
                    $this->mrTemplate->AddVar("data_transaksi_item", "NO", '');
                    $this->mrTemplate->AddVar("data_transaksi_item", "STATUS", 'default');
                    $this->mrTemplate->AddVar("data_transaksi_item", "NO_REKENING", '<b>' . $data['transaksi'][$i]['coa_kode_akun'] . '</b>');
                    $this->mrTemplate->AddVar("data_transaksi_item", "CATATAN", '<b>' . $data['transaksi'][$i]['coa_nama_akun'] . '</b>');
                    $this->mrTemplate->AddVar("data_transaksi_item", "DEBET", '');
                    $this->mrTemplate->AddVar("data_transaksi_item", "KREDIT", '');

                    /*
                    if (!empty($data['transaksi'][$i]['saldo_awal']))
                        $saldo = $data['transaksi'][$i]['saldo_awal']; //$saldoTran['saldo_awal_transaksi'];
                    else
                        $saldo = 0;
                     * 
                     */
                    $saldo = $this->mObj->getSaldoAwalAkunBulanLalu($data['transaksi'][$i]['coa_id'], $data['transaksi'][$i]['coa_kelompok_id']);
                    $this->mrTemplate->AddVar("data_transaksi_item", "SALDO", NumberFormat::Accounting($saldo, 2));
                    $this->mrTemplate->parseTemplate("data_transaksi_item", "a");
                }

                $this->mrTemplate->AddVar("data_transaksi_item", "STATUS", 'default');
                $this->mrTemplate->AddVar("data_transaksi_item", "NO", $no);
                $this->mrTemplate->AddVar("data_transaksi_item", "NO_REKENING", $data['transaksi'][$i]['no_bpkb']);
                $this->mrTemplate->AddVar("data_transaksi_item", "CATATAN", $data['transaksi'][$i]['transaksi_catatan']);
                $this->mrTemplate->AddVar("data_transaksi_item", "SALDO", '');

                
                if ($data['transaksi'][$i]['transaksi_nilai_d'] != 0) {
                    $nilaiDebet = number_format($data['transaksi'][$i]['transaksi_nilai_d'], 2, ',', '.');
                } else {
                    $nilaiDebet = '';
                }
                if ($data['transaksi'][$i]['transaksi_nilai_k'] != 0) {
                    $nilaiKredit = number_format($data['transaksi'][$i]['transaksi_nilai_k'], 2, ',', '.');
                } else {
                    $nilaiKredit = '';
                }
                $this->mrTemplate->AddVar("data_transaksi_item", 'DEBET', $nilaiDebet);
                $this->mrTemplate->AddVar("data_transaksi_item", 'KREDIT', $nilaiKredit);
                $this->mrTemplate->parseTemplate("data_transaksi_item", "a");
                $no++;
                if ($data['transaksi'][$i]['coa_kode_akun'] != $data['transaksi'][$i + 1]['coa_kode_akun']) {
                    $this->mrTemplate->AddVar("data_transaksi_item", "STATUS", 'total');
                    $this->mrTemplate->AddVar("data_transaksi_item", "KETERANGAN", 'Sub Total');
                    

                    $debet = $this->mObj->getSaldoDebet($data['transaksi'][$i]['coa_id']);
                    $kredit = $this->mObj->getSaldoKredit($data['transaksi'][$i]['coa_id']);
                    $saldoBerjalan = $this->mObj->getSaldoAkunBulanBerjalan($data['transaksi'][$i]['coa_id'], $data['transaksi'][$i]['coa_kelompok_id']);
                    
                    if ($debet != 0)
                        $debetRp = number_format($debet, 2, ',', '.');
                    else
                        $debetRp = '';
                    if ($kredit != 0)
                        $kreditRp = number_format($kredit, 2, ',', '.');
                    else
                        $kreditRp = '';

                    $this->mrTemplate->AddVar("data_transaksi_item", "DEBET", $debetRp);
                    $this->mrTemplate->AddVar("data_transaksi_item", "KREDIT", $kreditRp);
                    $this->mrTemplate->AddVar("data_transaksi_item", "SALDO", NumberFormat::Accounting($saldo + $saldoBerjalan, 2));
                    $this->mrTemplate->parseTemplate("data_transaksi_item", "a");
                    $no = 1;
                    $totalDebet += $debet;
                    $totalKredit += $kredit;
                    $debet = $kredit = $saldo = 0;
                }
            }
            $this->mrTemplate->AddVar("data_transaksi_item", "STATUS", 'total');
            $this->mrTemplate->AddVar("data_transaksi_item", "KETERANGAN", 'Grand Total');
            $this->mrTemplate->AddVar("data_transaksi_item", "DEBET", NumberFormat::Accounting($totalDebet, 2));
            $this->mrTemplate->AddVar("data_transaksi_item", "KREDIT", NumberFormat::Accounting($totalKredit, 2));
            $this->mrTemplate->AddVar("data_transaksi_item", "SALDO", '');
            $this->mrTemplate->parseTemplate("data_transaksi_item", "a");
        }
    }

    function date2string($date) {
        $bln = array(
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        );
        $arrtgl = explode('-', $date);
        return $arrtgl[2] . ' ' . $bln[(int) $arrtgl[1]] . ' ' . $arrtgl[0];
    }

}

?>