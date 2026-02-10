<?php

/**
 * 
 * @ClassName : ViewCetakLapAktivitas
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
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/lap_aktivitas/business/AppLapAktifitas.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'main/function/date.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'main/function/number_format.class.php';

class ViewCetakLapAktivitasDetil extends HtmlResponse {

    function TemplateModule() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'module/lap_aktivitas/template');
        $this->SetTemplateFile('view_cetak_lap_aktivitas_detil.html');
    }

    function TemplateBase() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
        $this->SetTemplateFile('document-print.html');
        $this->SetTemplateFile('layout-common-print.html');
    }

    function ProcessRequest() {
        $Obj = new AppLapAktivitas();
        $get = $_GET->AsArray();

        if (!empty($get['tgl_akhir'])) {
            $tgl = $get['tgl_akhir'];
        } else {
            $tgl = date("Y-m-d");
        }

        if (!empty($get['tgl_awal'])) {
            $tglAwal = $get['tgl_awal'];
        } else {
            $tglAwal = date("Y-01-01");
        }
        $return['laporan_all'] = $Obj->GetLaporanAllDetil($tglAwal, $tgl);
        $return['tgl_akhir'] = $tgl;
        $return['tgl_awal'] = $tglAwal;

        return $return;
    }

    function ParseTemplate($data = NULL) {
        $this->mrTemplate->AddVar('content', 'TGL_AWAL', IndonesianDate($data['tgl_awal'], 'yyyy-mm-dd'));
        $this->mrTemplate->AddVar('content', 'TGL_AKHIR', IndonesianDate($data['tgl_akhir'], 'yyyy-mm-dd'));
        $gridList = $data['laporan_all'];

        //inisialisasi variable array
        $pendapatan = array();
        $beban = array();

        if (!empty($gridList)) {
            foreach ($gridList as $key => $value) {

                //if ($value['status'] == 'Ya') {
                if ($value['kelJnsNama'] == 'Pendapatan') {
                    if ($value['saldo_normal'] == 'D') {
                        $nilai = 0 - $value['nilai'];
                    } else {
                        $nilai = $value['nilai'];
                    }
                    $totalPerKelJns[$value['kellapId']] += $nilai;
                    $pendapatan[$value['kelJnsNama']][] = array(
                        "nama_kel_lap" => $value['nama_kel_lap'],
                        "kode_coa" => $value['kode_coa'],
                        "nama_coa" => $value['nama_coa'],
                        "nilai" => $nilai,
                        "kelJnsNama" => $value['kelJnsNama'],
                        "kellapId" => $value['kellapId']
                    );
                } else {
                    if ($value['saldo_normal'] == 'K') {
                        $nilai = 0 - $value['nilai'];
                    } else {
                        $nilai = $value['nilai'];
                    }
                    $totalPerKelJns[$value['kellapId']] += $nilai;
                    $beban[$value['kelJnsNama']][] = array(
                        "nama_kel_lap" => $value['nama_kel_lap'],
                        "kode_coa" => $value['kode_coa'],
                        "nama_coa" => $value['nama_coa'],
                        "nilai" => $nilai,
                        "kelJnsNama" => $value['kelJnsNama'],
                        "kellapId" => $value['kellapId']
                    );
                }
            }
        }
        $totalPendapatan = 0;
        
        if (!empty($pendapatan)) {
            foreach ($pendapatan as $key => $value) {
                $total = 0;
                $this->mrTemplate->ClearTemplate('pendapatan_item');
                $detilValue = array();

                $kelapId = null;
                $idx = 0;
                for ($k = 0; $k < sizeof($value);) {
                    //foreach ($value as $detilKey => $detilValue) {                
                    if ($value[$k]['kellapId'] == $kelapId) {
                        //$detilValue['url_detil'] = $urlDetil . '&dataId=' . $detilValue['kellapId'] . '&tgl=' . $data['tgl_akhir'] . '&tgl_awal=' . $data['tgl_awal'];
                        $total+= $value[$k]['nilai'];
                        $detilValue[$idx]['nama_kel_lap'] = $value[$k]['kode_coa']. ' - '.$value[$k]['nama_coa'];
                        $detilValue[$idx]['nilai'] = NumberFormat::Accounting($value[$k]['nilai'], 2);
                        $detilValue[$idx]['style'] = 'padding:3px 0 0 20px';
                        $detilValue[$idx]['style_nominal'] = '';
                        $this->mrTemplate->AddVars('pendapatan_item', $detilValue[$idx], '');
                        $this->mrTemplate->parseTemplate('pendapatan_item', 'a');
                        $k++;
                    } elseif ($kelapId != $value[$k]['kellapId']) {
                        $kelapId = $value[$k]['kellapId'];
                        $detilValue[$idx]['nama_kel_lap'] = '<b>' . $value[$k]['nama_kel_lap'] . '</b>';
                        if (array_key_exists($kelapId, $totalPerKelJns)) {
                            $detilValue[$idx]['nilai'] = NumberFormat::Accounting($totalPerKelJns[$kelapId], 2);
                        } else {
                            $detilValue[$idx]['nilai'] = '';
                        }
                        $detilValue[$idx]['style'] = 'font-weight:bold';
                        $detilValue[$idx]['style_nominal'] = 'font-weight:bold';
                        $this->mrTemplate->AddVars('pendapatan_item', $detilValue[$idx], '');
                        $this->mrTemplate->parseTemplate('pendapatan_item', 'a');
                    }
                    $idx++;
                }
                /*
                  foreach ($value as $detilKey => $detilValue) {
                  $total+= $detilValue['nilai'];
                  $detilValue['nilai'] = NumberFormat::Accounting($detilValue['nilai'],2);
                  $this->mrTemplate->AddVars('pendapatan_item', $detilValue[$idx], '');
                  $this->mrTemplate->parseTemplate('pendapatan_item', 'a');
                  }
                 */
                $totalPendapatan+= $total;
                $this->mrTemplate->AddVar('pendapatan', 'KELJNSNAMA', $key);
                $this->mrTemplate->AddVar('pendapatan', 'TOTAL_NILAI', NumberFormat::Accounting($total, 2));
                $this->mrTemplate->parseTemplate('pendapatan', 'a');
            }
        }

        $this->mrTemplate->AddVar('content', 'TOTAL_PENDAPATAN', NumberFormat::Accounting($totalPendapatan, 2));
        if (!empty($beban)) {
            foreach ($beban as $key => $value) {
                $total = 0;
                $this->mrTemplate->ClearTemplate('beban_item');
                $detilValue = array();
                $kelapId = null;
                $idx = 0;
                for ($k = 0; $k < sizeof($value);) {
                    //foreach ($value as $detilKey => $detilValue) {                
                    if ($value[$k]['kellapId'] == $kelapId) {
                        //$detilValue['url_detil'] = $urlDetil . '&dataId=' . $detilValue['kellapId'] . '&tgl=' . $data['tgl_akhir'] . '&tgl_awal=' . $data['tgl_awal'];
                        $total+= $value[$k]['nilai'];
                        $detilValue[$idx]['nama_kel_lap'] = $value[$k]['kode_coa']. ' - '.$value[$k]['nama_coa'];
                        $detilValue[$idx]['nilai'] = NumberFormat::Accounting($value[$k]['nilai'], 2);
                        $detilValue[$idx]['style'] = 'padding:3px 0 0 20px';
                        $detilValue[$idx]['style_nominal'] = '';
                        $this->mrTemplate->AddVars('beban_item', $detilValue[$idx], '');
                        $this->mrTemplate->parseTemplate('beban_item', 'a');
                        $k++;
                    } elseif ($kelapId != $value[$k]['kellapId']) {
                        $kelapId = $value[$k]['kellapId'];
                        $detilValue[$idx]['nama_kel_lap'] = '<b>' . $value[$k]['nama_kel_lap'] . '</b>';
                        if (array_key_exists($kelapId, $totalPerKelJns)) {
                            $detilValue[$idx]['nilai'] = NumberFormat::Accounting($totalPerKelJns[$kelapId], 2);
                        } else {
                            $detilValue[$idx]['nilai'] = '';
                        }
                        $detilValue[$idx]['style'] = 'font-weight:bold';
                        $detilValue[$idx]['style_nominal'] = 'font-weight:bold';
                        $this->mrTemplate->AddVars('beban_item', $detilValue[$idx], '');
                        $this->mrTemplate->parseTemplate('beban_item', 'a');
                    }
                    $idx++;
                }
                /*
                  foreach ($value as $detilKey => $detilValue) {
                  $total+= $detilValue['nilai'];
                  $detilValue['nilai'] = NumberFormat::Accounting($detilValue['nilai'], 2);
                  $this->mrTemplate->AddVars('beban_item', $detilValue, '');
                  $this->mrTemplate->parseTemplate('beban_item', 'a');
                  } */
                $totalBiaya+= $total;
                $this->mrTemplate->AddVar('beban', 'KELJNSNAMA', $key);
                $this->mrTemplate->AddVar('beban', 'TOTAL_NILAI', NumberFormat::Accounting($total, 2));
                $this->mrTemplate->parseTemplate('beban', 'a');
            }
        }

        $jumlahAktivaBersih = $totalPendapatan - $totalBiaya;

        $this->mrTemplate->AddVar('content', 'TOTAL_BEBAN', NumberFormat::Accounting($totalBiaya, 2));

        $jumlahAktivaBersih = NumberFormat::Accounting($jumlahAktivaBersih, 2);
        $this->mrTemplate->AddVar('content', 'JUMLAH_AKTIVA_BERSIH', $jumlahAktivaBersih);
    }

}

?>