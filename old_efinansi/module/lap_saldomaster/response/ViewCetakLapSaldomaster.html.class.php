<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/lap_saldomaster/business/AppLapSaldomaster.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'main/function/date.php';

class ViewCetakLapSaldomaster extends HtmlResponse {
    #var $Pesan;

    function TemplateModule() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'module/lap_saldomaster/template');
        $this->SetTemplateFile('view_cetak_lap_saldomaster.html');
    }

    function TemplateBase() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
        $this->SetTemplateFile('document-print.html');
        $this->SetTemplateFile('layout-common-print.html');
    }

    function ProcessRequest() {
        $Obj = new AppLapSaldomaster();
        $_GET = $_GET->AsArray();
        #print_r($_GET); exit;
        $tgl_akhir = Dispatcher::Instance()->Decrypt($_GET['tgl_akhir']);
        $tgl_awal = Dispatcher::Instance()->Decrypt($_GET['tgl_awal']);

        $data_cetak = $Obj->GetSaldo($tgl_awal, $tgl_akhir);
        #print_r($data_cetak); exit;
        $return['tgl_akhir'] = $tgl_akhir;
        $return['tgl_awal'] = $tgl_awal;
        $return['saldo'] = $data_cetak;
        return $return;
    }

    function ParseTemplate($data = NULL) {
        if (empty($data)) {
            $this->mrTemplate->AddVar('data', 'DATA_EMPTY', 'YES');
        } else {
            $this->mrTemplate->AddVar('data', 'DATA_EMPTY', 'NO');
            $this->mrTemplate->AddVar('content', 'TGL_AKHIR', IndonesianDate($data['tgl_akhir'], 'yyyy-mm-dd'));
            $this->mrTemplate->AddVar('content', 'TGL_AWAL', IndonesianDate($data['tgl_awal'], 'yyyy-mm-dd'));
            #print_r($data['saldo']); exit;
            $saldoAwal = $mutasiDebet = $mutasiKredit = $saldoAkhir = 0;
            $no = 1;
            $saldoAwal = $debet = $kredit = $saldoAkhir = 0;
            for ($i = 0; $i < sizeof($data['saldo']); $i++) {
                if ($i == 0)
                    $saldoAwal = $data['saldo'][$i]['saldo_awal'];
                $debet += $data['saldo'][$i]['debet'];
                $kredit += $data['saldo'][$i]['kredit'];

                if ($data['saldo'][$i]['coa_kode_akun'] != $data['saldo'][$i + 1]['coa_kode_akun']) {
                    $saldoAkhir = $data['saldo'][$i]['saldo_akhir'];
                    $kode = explode(".", $data['saldo'][$i]['coa_kode_akun']);
                    $kodeNext = explode(".", $data['saldo'][$i + 1]['coa_kode_akun']);
                    $this->mrTemplate->AddVar("data_saldo_item", "STATUS", 'default');
                    $this->mrTemplate->AddVar("data_saldo_item", "NO", $no);
                    $this->mrTemplate->AddVar("data_saldo_item", "NO_REKENING", $data['saldo'][$i]['coa_kode_akun']);
                    $this->mrTemplate->AddVar("data_saldo_item", "NAMA_REKENING", $data['saldo'][$i]['coa_nama_akun']);

                    if ($saldoAwal < 0) {
                        $this->mrTemplate->AddVar("data_saldo_item", "SALDO_AWAL", "(" . number_format(str_replace("-", "", $saldoAwal), 2, ',', '.') . ")");
                    } else {
                        $this->mrTemplate->AddVar("data_saldo_item", "SALDO_AWAL", number_format($saldoAwal, 2, ',', '.'));
                    }

                    if ($debet < 0) {
                        $this->mrTemplate->AddVar("data_saldo_item", "MUTASI_DEBET", "(" . number_format(str_replace("_", "", $debet), 2, ',', '.') . ")");
                    } else {
                        $this->mrTemplate->AddVar("data_saldo_item", "MUTASI_DEBET", number_format($debet, 2, ',', '.'));
                    }

                    if ($kredit < 0) {
                        $this->mrTemplate->AddVar("data_saldo_item", "MUTASI_KREDIT", "(" . number_format(str_replace("-", "", $kredit), 2, ',', '.') . ")");
                    } else {
                        $this->mrTemplate->AddVar("data_saldo_item", "MUTASI_KREDIT", number_format($kredit, 2, ',', '.'));
                    }

                    if ($saldoAkhir < 0) {
                        $this->mrTemplate->AddVar("data_saldo_item", "SALDO_AKHIR", "(" . number_format(str_replace("-", "", $saldoAkhir), 2, ',', '.') . ")");
                    } else {
                        $this->mrTemplate->AddVar("data_saldo_item", "SALDO_AKHIR", number_format($saldoAkhir, 2, ',', '.'));
                    }

                    $this->mrTemplate->parseTemplate("data_saldo_item", "a");
                    $no++;
                    $subSAwal += $saldoAwal;
                    $subSAkhir += $saldoAkhir;
                    $mDebet += $debet;
                    $mKredit += $kredit;
                    if ($kode[0] != $kodeNext[0]) {
                        $this->mrTemplate->AddVar("data_saldo_item", "STATUS", 'sub_total');
                        if ($subSAwal < 0) {
                            $this->mrTemplate->AddVar("data_saldo_item", "SALDO_AWAL", "(" . number_format(str_replace("-", "", $subSAwal), 2, ',', '.') . ")");
                        } else {
                            $this->mrTemplate->AddVar("data_saldo_item", "SALDO_AWAL", number_format($subSAwal, 2, ',', '.'));
                        }

                        if ($subSAkhir < 0) {
                            $this->mrTemplate->AddVar("data_saldo_item", "SALDO_AKHIR", "(" . number_format(str_replace("-", "", $subSAkhir), 2, ',', '.') . ")");
                        } else {
                            $this->mrTemplate->AddVar("data_saldo_item", "SALDO_AKHIR", number_format($subSAkhir, 2, ',', '.'));
                        }

                        if ($mDebet < 0) {
                            $this->mrTemplate->AddVar("data_saldo_item", "MUTASI_DEBET", "(" . number_format(str_replace("-", "", $mDebet), 2, ',', '.') . ")");
                        } else {
                            $this->mrTemplate->AddVar("data_saldo_item", "MUTASI_DEBET", number_format($mDebet, 2, ',', '.'));
                        }

                        if ($mKredit < 0) {
                            $this->mrTemplate->AddVar("data_saldo_item", "MUTASI_KREDIT", "(" . number_format(str_replace("-", "", $mKredit), 2, ',', '.') . ")");
                        } else {
                            $this->mrTemplate->AddVar("data_saldo_item", "MUTASI_KREDIT", number_format($mKredit, 2, ',', '.'));
                        }

                        $this->mrTemplate->parseTemplate("data_saldo_item", "a");
                        $subSAwal = $subSAkhir = $mDebet = $mKredit = 0;
                        $no = 1;
                    }
                    $saldoAwal = $debet = $kredit = $saldoAkhir = 0;
                    $saldoAwal = $data['saldo'][$i + 1]['saldo_awal'];
                }
            }
        }
    }

}

?>