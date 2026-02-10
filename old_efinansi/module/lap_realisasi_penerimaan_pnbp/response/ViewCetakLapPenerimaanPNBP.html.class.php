<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/lap_realisasi_penerimaan_pnbp/business/AppLapPenerimaanPNBP.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewCetakLapPenerimaanPNBP extends HtmlResponse {

    function TemplateModule() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') .
                'module/lap_realisasi_penerimaan_pnbp/template');
        $this->SetTemplateFile('cetak_lap_penerimaan_pnbp.html');
    }

    function TemplateBase() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
        //$this->SetTemplateFile('document-print.html');
        $this->SetTemplateFile('document-print-custom-wide.html');
        $this->SetTemplateFile('layout-common-print.html');
    }

    function ProcessRequest() {
        $Obj = new AppLapPenerimaanPNBP();
        $tahun_anggaran = Dispatcher::Instance()->Decrypt($_GET['tgl']);
        $unitkerja_label = Dispatcher::Instance()->Decrypt($_GET['unitkerja_label']);
        $unitkerja = Dispatcher::Instance()->Decrypt($_GET['unitkerja']);
        $userId = Dispatcher::Instance()->Decrypt($_GET['id']);
        $data = $Obj->GetDataRealisasiPNBPCetak($tahun_anggaran, $unitkerja);

        $unitkerja = $Obj->GetUnitKerja($unitkerjaId);
        $tahunanggaran = $Obj->GetTahunAnggaran($tahun_anggaran);

        $return['data'] = $data;
        $return['tahunanggaran_nama'] = $tahunanggaran['name'];
        $return['unitkerja_nama'] = $unitkerja_label;

        return $return;
    }

    function ParseTemplate($data = NULL) {
        $this->mrTemplate->AddVar('content', 'TAHUN_PERIODE', $data['tahunanggaran_nama']);
        $this->mrTemplate->AddVar('content', 'UNIT_KERJA_LABEL', $data['unitkerja_nama']);

        if (empty($data['data'])) {
            $this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'YES');
        } else {
            $this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'NO');

            $total = '';
            $jumlah_total = '';
            $idrencana = '';
            $idkode = '';
            $kode = '';
            $nama = '';

            $data_list = $data['data'];
            $kode_satker = '';
            $kode_unit = '';
            $nama_satker = '';
            $nama_unit = '';
            $surplusDefisit = 0;
            
            for ($i = 0; $i < sizeof($data_list);) {

                if ($i == 0) {
                    $this->mrTemplate->AddVar('content', 'FIRST_NUMBER', $number);
                }
                if ($i == sizeof($data_list) - 1) {
                    $this->mrTemplate->AddVar('content', 'LAST_NUMBER', $number);
                }

                if (($data_list[$i]['kode_satker'] == $kode_satker) && ($data_list[$i]['kode_unit'] == $kode_unit)) {

                    $send = $data_list[$i];
                    $send['target_pnbp'] = number_format($data_list[$i]['target_pnbp'], 0, ',', '.');
                    $send['realjan'] = number_format($data_list[$i]['realJan'], 0, ',', '.');
                    $send['realfeb'] = number_format($data_list[$i]['realFeb'], 0, ',', '.');
                    $send['realmar'] = number_format($data_list[$i]['realMar'], 0, ',', '.');
                    $send['realapr'] = number_format($data_list[$i]['realApr'], 0, ',', '.');
                    $send['realmei'] = number_format($data_list[$i]['realMei'], 0, ',', '.');
                    $send['realjun'] = number_format($data_list[$i]['realJun'], 0, ',', '.');
                    $send['realjul'] = number_format($data_list[$i]['realJul'], 0, ',', '.');
                    $send['realags'] = number_format($data_list[$i]['realAgs'], 0, ',', '.');
                    $send['realsep'] = number_format($data_list[$i]['realSep'], 0, ',', '.');
                    $send['realokt'] = number_format($data_list[$i]['realOkt'], 0, ',', '.');
                    $send['realnov'] = number_format($data_list[$i]['realNov'], 0, ',', '.');
                    $send['realdes'] = number_format($data_list[$i]['realDes'], 0, ',', '.');
                    $send['total_realisasi'] = number_format($data_list[$i]['total_realisasi'], 0, ',', '.');
                    $surplusDefisit = ($data_list[$i]['total_realisasi'] - $data_list[$i]['target_pnbp']);
                    $send['surplus_defisit'] = number_format($surplusDefisit, 0, ',', '.');

                    $send['class_name'] = "";
                    $send['nomor'] = $no;
                    $send['class_button'] = "links";

                    $send['kode'] = "";
                    $send['nama'] = "<b>" . $data_list[$i]['jenisBiayaNama'] . "</b>";

                    $this->mrTemplate->SetAttribute('content_description', 'visibility', 'visible');
                    $this->mrTemplate->AddVar('content_description', 'KETERANGAN', $data_list[$i]['keterangan']);

                    $this->mrTemplate->AddVar('cekbox', 'data_number', $number);
                    $this->mrTemplate->AddVar('cekbox', 'data_idrencana', $data_list[$i]['idrencana']);
                    $this->mrTemplate->AddVar('cekbox', 'data_nama', "<b>" . $data_list[$i]['jenisBiayaNama'] . "</b>");
                    $this->mrTemplate->AddVar('cekbox', 'IS_SHOW', 'YES');
                    $i++;
                    $no++;
                    $number++;
                } elseif ($data_list[$i]['kode_satker'] != $kode_satker &&
                        $data_list[$i]['nama_satker'] == $data_list[$i]['nama_unit']) {
                    $kode_satker = $data_list[$i]['kode_satker'];
                    $kode_unit = $data_list[$i]['kode_unit'];
                    $nama_satker = $data_list[$i]['nama_satker'];
                    $nama_unit = $data_list[$i]['nama_unit'];
                    $send['kode'] = "<b>" . $kode_unit . "</b>";
                    $send['nama'] = "<b>" . $data_list[$i]['nama_unit'] . "</b>";

                    $send['target_pnbp'] = "";
                    $send['realjan'] = "";
                    $send['realfeb'] = "";
                    $send['realmar'] = "";
                    $send['realapr'] = "";
                    $send['realmei'] = "";
                    $send['realjun'] = "";
                    $send['realjul'] = "";
                    $send['realags'] = "";
                    $send['realsep'] = "";
                    $send['realokt'] = "";
                    $send['realnov'] = "";
                    $send['realdes'] = "";
                    $send['total_realisasi'] = "";
                    $send['surplus_defisit'] = "";
                    //print_r($send['jumlah_total']."<br/>");

                    $send['class_name'] = "table-common-even1";
                    $send['nomor'] = "";
                    $send['class_button'] = "toolbar";

                    $this->mrTemplate->SetAttribute('content_description', 'visibility', 'hidden');
                    $this->mrTemplate->AddVar('content_description', 'KETERANGAN', $data_list[$i]['keterangan']);
                    $this->mrTemplate->AddVar('cekbox', 'data_nama', $send['nama']);

                    $no = 1;
                    // }
                } elseif ($data_list[$i]['kode_unit'] != $kode_unit) {
                    $kode_satker = $data_list[$i]['kode_satker'];
                    $kode_unit = $data_list[$i]['kode_unit'];
                    $nama_satker = $data_list[$i]['nama_satker'];
                    $nama_unit = $data_list[$i]['nama_unit'];
                    $send['kode'] = "<b>" . $kode_unit . "</b>";
                    $send['nama'] = "<b>" . $data_list[$i]['nama_unit'] . "</b>";
                    $send['target_pnbp'] = "";
                    $send['realjan'] = "";
                    $send['realfeb'] = "";
                    $send['realmar'] = "";
                    $send['realapr'] = "";
                    $send['realmei'] = "";
                    $send['realjun'] = "";
                    $send['realjul'] = "";
                    $send['realags'] = "";
                    $send['realsep'] = "";
                    $send['realokt'] = "";
                    $send['realnov'] = "";
                    $send['realdes'] = "";
                    $send['total_realisasi'] = "";
                    $send['surplus_defisit'] = "";
                    $send['tarif'] = "";
                    $send['nomor'] = "";
                    $send['class_button'] = "toolbar";

                    $this->mrTemplate->SetAttribute('content_description', 'visibility', 'hidden');
                    $this->mrTemplate->AddVar('content_description', 'KETERANGAN', $data_list[$i]['keterangan']);
                    $this->mrTemplate->AddVar('cekbox', 'data_nama', $send['nama']);

                    $no = 1;
                }
                $this->mrTemplate->AddVars('data_item', $send, 'DATA_');
                $this->mrTemplate->parseTemplate('data_item', 'a');
            }

            $total_target = 0;
            $total_jan = 0;
            $total_feb = 0;
            $total_mar = 0;
            $total_apr = 0;
            $total_mei = 0;
            $total_jun = 0;
            $total_jul = 0;
            $total_ags = 0;
            $total_sep = 0;
            $total_okt = 0;
            $total_nov = 0;
            $total_des = 0;
            $total_real = 0;
            $total_surplus_defisit = 0;
            foreach ($data_list as $key => $value) {
                $total_target+= $value['target_pnbp'];
                $total_jan+= $value['realJan'];
                $total_feb+= $value['realFeb'];
                $total_mar+= $value['realMar'];
                $total_apr+= $value['realApr'];
                $total_mei+= $value['realMei'];
                $total_jun+= $value['realJun'];
                $total_jul+= $value['realJul'];
                $total_ags+= $value['realAgs'];
                $total_sep+= $value['realSep'];
                $total_okt+= $value['realOkt'];
                $total_nov+= $value['realNov'];
                $total_des+= $value['realDes'];
                $total_real+= $value['total_realisasi'];
            }
            $total_surplus_defisit = ($total_real - $total_target);
            $this->mrTemplate->AddVar('data_total', 'TOTAL_TARGET_PNBP', number_format($total_target, 0, ',', '.'));
            $this->mrTemplate->AddVar('data_total', 'TOTAL_JAN', number_format($total_jan, 0, ',', '.'));
            $this->mrTemplate->AddVar('data_total', 'TOTAL_FEB', number_format($total_feb, 0, ',', '.'));
            $this->mrTemplate->AddVar('data_total', 'TOTAL_MAR', number_format($total_mar, 0, ',', '.'));
            $this->mrTemplate->AddVar('data_total', 'TOTAL_APR', number_format($total_apr, 0, ',', '.'));
            $this->mrTemplate->AddVar('data_total', 'TOTAL_MEI', number_format($total_mei, 0, ',', '.'));
            $this->mrTemplate->AddVar('data_total', 'TOTAL_JUN', number_format($total_jun, 0, ',', '.'));
            $this->mrTemplate->AddVar('data_total', 'TOTAL_JUL', number_format($total_jul, 0, ',', '.'));
            $this->mrTemplate->AddVar('data_total', 'TOTAL_AGS', number_format($total_ags, 0, ',', '.'));
            $this->mrTemplate->AddVar('data_total', 'TOTAL_SEP', number_format($total_sep, 0, ',', '.'));
            $this->mrTemplate->AddVar('data_total', 'TOTAL_OKT', number_format($total_okt, 0, ',', '.'));
            $this->mrTemplate->AddVar('data_total', 'TOTAL_NOV', number_format($total_nov, 0, ',', '.'));
            $this->mrTemplate->AddVar('data_total', 'TOTAL_DES', number_format($total_des, 0, ',', '.'));
            $this->mrTemplate->AddVar('data_total', 'TOTAL_TOTAL_REALISASI', number_format($total_real, 0, ',', '.'));
            $this->mrTemplate->AddVar('data_total', 'TOTAL_SURPLUS_DEFISIT', number_format($total_surplus_defisit, 0, ',', '.'));
        }
    }

}

?>