<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') . 
    'module/lap_saldomaster/business/AppLapSaldomaster.class.php';

class ViewLapSaldoMaster extends HtmlResponse {

    var $Pesan;

    function TemplateModule() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 
            'module/lap_saldomaster/template');
        $this->SetTemplateFile('view_lap_saldomaster.html');
    }

    function ProcessRequest() {
        $Obj = new AppLapSaldoMaster();

        $post = $_POST->AsArray();
        #print_r($post);
        if (!empty($post['tanggal_akhir_year'])) {
            $tanggal_akhir = $post['tanggal_akhir_year'] . "-" . $post['tanggal_akhir_mon'] . "-" . $post['tanggal_akhir_day'];
        } else {
            $tanggal_akhir = date("Y-m-d");
        }

        if (!empty($post['tanggal_awal_year'])) {
            $tanggal_awal = $post['tanggal_awal_year'] . "-" . $post['tanggal_awal_mon'] . "-" . $post['tanggal_awal_day'];
        } else {
            $tanggal_awal = date("Y-01-01");
        }

        if (isset($_GET['cari'])) {
            $get_data = $_GET->AsArray();
            $tanggal_akhir = $get_data['tgl_akhir'];
            $tanggal_awal = $get_data['tgl_awal'];
        }
        //tahun untuk combo
        $tahunTrans = $Obj->GetMinMaxThnTrans();
        Messenger::Instance()->SendToComponent(
            'tanggal', 
            'Tanggal', 
            'view', 
            'html', 
            'tanggal_akhir', 
            array(
                $tanggal_akhir, 
                $tahunTrans['minTahun'], 
                $tahunTrans['maxTahun']
            ), 
            Messenger::CurrentRequest
        );

        Messenger::Instance()->SendToComponent(
            'tanggal', 
            'Tanggal', 
            'view', 
            'html', 
            'tanggal_awal', 
            array(
                $tanggal_awal, 
                $tahunTrans['minTahun'], 
                $tahunTrans['maxTahun']
            ), 
            Messenger::CurrentRequest
        );

        $saldo = $Obj->GetSaldo($tanggal_awal, $tanggal_akhir); 
        $saldoBerjalan = $Obj->GetSaldoBerjalan($tanggal_akhir);
        $return['saldo'] = $saldo;
        $return['saldo_berjalan'] = $saldoBerjalan;
        $return['search'] = $tanggal_akhir;
        $return['tanggal_awal'] = $tanggal_awal;
        return $return;
    }

    function ParseTemplate($data = NULL) {
        $search['tgl_akhir'] = $data['search'];
        $search['tgl_awal'] = $data['tanggal_awal'];
        $this->mrTemplate->AddVar('content', 'URL_SEARCH', Dispatcher::Instance()->GetUrl('lap_saldomaster', 'LapSaldomaster', 'view', 'html'));

        $this->mrTemplate->AddVar('content', 'URL_CETAK', Dispatcher::Instance()->GetUrl('lap_saldomaster', 'CetakLapSaldomaster', 'view', 'html') .
                '&tgl_awal=' . Dispatcher::Instance()->Encrypt($search['tgl_awal']) .
                '&tgl_akhir=' . Dispatcher::Instance()->Encrypt($search['tgl_akhir']) .
                '&cetak=' . Dispatcher::Instance()->Encrypt('yes'));

        $this->mrTemplate->AddVar('content', 'URL_EXCEL', Dispatcher::Instance()->GetUrl('lap_saldomaster', 'ExcelLapSaldomaster', 'view', 'xlsx') .
                '&tgl_awal=' . Dispatcher::Instance()->Encrypt($search['tgl_awal']) .
                '&tgl_akhir=' . Dispatcher::Instance()->Encrypt($search['tgl_akhir']) .
                '&cetak=' . Dispatcher::Instance()->Encrypt('yes'));

        $this->mrTemplate->AddVar('content', 'URL_RTF', Dispatcher::Instance()->GetUrl('lap_saldomaster', 'RtfLapSaldomaster', 'view', 'html') .
                '&tgl_awal=' . Dispatcher::Instance()->Encrypt($search['tgl_awal']) .
                '&tgl_akhir=' . Dispatcher::Instance()->Encrypt($search['tgl_akhir']) .
                '&cetak=' . Dispatcher::Instance()->Encrypt('yes'));

        if (empty($data['saldo'])) {
            $this->mrTemplate->AddVar('data_saldo', 'SALDO_EMPTY', 'YES');
        } else {
            $this->mrTemplate->AddVar('data_saldo', 'SALDO_EMPTY', 'NO');
            $saldoAwal = $mutasiDebet = $mutasiKredit = $saldoAkhir = 0;
            $no = 1;
            $j = 0;
            $saldoAwal = $debet = $kredit = $saldoAkhir = 0;
           
            for ($i = 0; $i < sizeof($data['saldo']); $i++) {
                if ($i == 0) {
                    $saldoAwal = $data['saldo'][$i]['saldo_awal'];
                }
                $debet += $data['saldo'][$i]['debet'];
                $kredit += $data['saldo'][$i]['kredit'];

                if($data['saldo'][$i]['rl_awal'] === '1') {
                    if($data['saldo_berjalan'] > 0) {                        
                        $kredit -= $data['saldo_berjalan'];
                    } else {
                        $debet += $data['saldo_berjalan'];
                    }
                }

                if($data['saldo'][$i]['rl_berjalan'] === '1') {
                    if($data['saldo_berjalan'] > 0) {                        
                        $debet -= $data['saldo_berjalan'];
                    } else {
                        $kredit += $data['saldo_berjalan'];
                    }
                }

                if ($data['saldo'][$i]['coa_kode_akun'] != $data['saldo'][$i + 1]['coa_kode_akun']) {
                    $saldoAkhir =    $data['saldo'][$i]['saldo_akhir'];
                    if($data['saldo'][$i]['rl_awal'] === '1') { 
                        $saldoAkhir += ($data['saldo_berjalan'] > 0 ? ($data['saldo_berjalan'] * -1) :  $data['saldo_berjalan']);
                    }
    
                    if($data['saldo'][$i]['rl_berjalan'] === '1') {
                        $saldoAkhir += ($data['saldo_berjalan'] > 0 ?  $data['saldo_berjalan'] : ($data['saldo_berjalan'] * -1));
                    }
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