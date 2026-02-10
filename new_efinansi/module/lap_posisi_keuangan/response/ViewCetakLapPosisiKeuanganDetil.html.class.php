<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/lap_posisi_keuangan/business/AppLapPosisiKeuangan.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'main/function/date.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'main/function/number_format.class.php';

class ViewCetakLapPosisiKeuanganDetil extends HtmlResponse {

    function TemplateModule() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'module/lap_posisi_keuangan/template');
        $this->SetTemplateFile('view_cetak_lap_posisi_keuangan_detail.html');
    }

    function TemplateBase() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
        $this->SetTemplateFile('document-print.html');
        $this->SetTemplateFile('layout-common-print.html');
    }

    function ProcessRequest() {
        $Obj = new AppLapPosisiKeuangan();
        $get = $_GET->AsArray();
        if (!empty($get['tgl_awal']))
            $tglAwal = $get['tgl_awal'];
        else
            $tglAwal = date("Y-m-d");

        if (!empty($get['tgl_akhir']))
            $tgl = $get['tgl_akhir'];
        else
            $tgl = date("Y-m-d");

        $return['laporan_all'] = $Obj->GetLaporanAllDetil($tglAwal, $tgl);
        $return['saldo_berjalan']= $Obj->GetSaldoBerjalan($tgl);

        $return['tgl_awal'] = $tglAwal;
        $return['tgl_akhir'] = $tgl;
        return $return;
    }

    function ParseTemplate($data = NULL) {
        $this->mrTemplate->AddVar('content', 'TGL_AKHIR', IndonesianDate($data['tgl_akhir'], 'yyyy-mm-dd'));
        $this->mrTemplate->AddVar('content', 'TGL_AWAL', IndonesianDate($data['tgl_awal'], 'yyyy-mm-dd'));

        $this->mrTemplate->AddVar('content', 'EKUITAS_AWAL', NumberFormat::Accounting($data['laba_thn_lalu']['saldo_akhir'],2));

        $gridList = $data['laporan_all'];
        $totalPerKelJns  = array();
        foreach ($gridList as $key => $value) {
            
                
            if($value['rl_awal'] === '1') { 
                $value['nilai'] += ($data['saldo_berjalan'] > 0 ? ($data['saldo_berjalan'] * -1) :  $data['saldo_berjalan']);
            }

            if($value['rl_berjalan'] === '1') {
                $value['nilai'] += ($data['saldo_berjalan'] > 0 ?  $data['saldo_berjalan'] : ($data['saldo_berjalan'] * -1));
            }

            $totalPerKelJns[$value['kellapId']] += $value['nilai'];
            if ($value['status'] == 'Ya') {
                $totalKelJns[$value['kellapId']] += $value['nilai'];
                $aktiva[$value['kelJnsNama']][] = array(
                    "nama_kel_lap" => $value['nama_kel_lap'],
                    "kode_coa" => $value['kode_coa'],
                    "nama_coa" => $value['nama_coa'],
                    "nilai" => $value['nilai'],
                    "kelJnsNama" => $value['kelJnsNama'],
                    "kellapId" => $value['kellapId']
                );
            } else {
                $kewajiban[$value['kelJnsNama']][] = array(
                    "nama_kel_lap" => $value['nama_kel_lap'],
                    "kode_coa" => $value['kode_coa'],
                    "nama_coa" => $value['nama_coa'],
                    "nilai" => $value['nilai'],
                    "kelJnsNama" => $value['kelJnsNama'],
                    "kellapId" => $value['kellapId']
                );
            }    
        }
        $totalAktiva = 0;
        $totalKewajiban = 0;

        foreach ($aktiva as $key => $value) {
            $total = 0;
            $this->mrTemplate->ClearTemplate('aktiva_item');
            $kelapId = null;
            $idx = 0;
            for ($k = 0; $k < sizeof($value); ) {
            //foreach ($value as $detilKey => $detilValue) {                
                if($value[$k]['kellapId'] ==  $kelapId) {
                    //$detilValue['url_detil'] = $urlDetil . '&dataId=' . $detilValue['kellapId'] . '&tgl=' . $data['tgl_akhir'] . '&tgl_awal=' . $data['tgl_awal'];
                    $total+= $value[$k]['nilai'];
                    $detilValue[$idx]['nama_kel_lap'] = $value[$k]['kode_coa']. ' - '.$value[$k]['nama_coa'];
                    $detilValue[$idx]['nilai'] =  NumberFormat::Accounting($value[$k]['nilai'], 2);
                    $detilValue[$idx]['style'] = 'padding:3px 0 0 20px';
                    $detilValue[$idx]['style_nominal'] = '';
                    $this->mrTemplate->AddVars('aktiva_item', $detilValue[$idx], '');
                    $this->mrTemplate->parseTemplate('aktiva_item', 'a');
                    $k++;
                } elseif($kelapId != $value[$k]['kellapId'])  {
                    $kelapId =  $value[$k]['kellapId'];                    
                    $detilValue[$idx]['nama_kel_lap'] ='<b>'. $value[$k]['nama_kel_lap'].'</b>';
                    if(array_key_exists($kelapId, $totalPerKelJns)) {
                        $detilValue[$idx]['nilai'] =  NumberFormat::Accounting($totalPerKelJns[$kelapId], 2);
                    } else {
                        $detilValue[$idx]['nilai'] = '';
                    }
                    $detilValue[$idx]['style'] = 'font-weight:bold';
                    $detilValue[$idx]['style_nominal'] = 'font-weight:bold';
                    $this->mrTemplate->AddVars('aktiva_item',$detilValue[$idx], '');
                    $this->mrTemplate->parseTemplate('aktiva_item', 'a');
                }                
                $idx++;
            }
           
            $totalAktiva+= $total;
            $this->mrTemplate->AddVar('aktiva', 'KELJNSNAMA', $key);
            $this->mrTemplate->AddVar('aktiva', 'TOTAL_NILAI',  NumberFormat::Accounting($total, 2));
            $this->mrTemplate->parseTemplate('aktiva', 'a');
        }

        $kelapId = null;
        $idx = 0;
        foreach ($kewajiban as $key => $value) {
            $total = 0;
            $this->mrTemplate->ClearTemplate('kewajiban_item');

             for ($k = 0; $k < sizeof($value); ) {
                if($value[$k]['kellapId'] ==  $kelapId) {
                    $total+= $value[$k]['nilai'];
                    //$detilValue['url_detil'] = $urlDetil . '&dataId=' . $detilValue['kellapId'] . '&tgl=' . $data['tgl_akhir'] . '&tgl_awal=' . $data['tgl_awal'];
                    $detilValue[$idx]['nama_kel_lap'] =  $value[$k]['kode_coa']. ' - '.$value[$k]['nama_coa'];
                    $detilValue[$idx]['nilai'] =  NumberFormat::Accounting($value[$k]['nilai'], 2);
                    $detilValue[$idx]['style'] = 'padding:3px 0 0 20px';
                    $detilValue[$idx]['style_nominal'] = '';
                    $this->mrTemplate->AddVars('kewajiban_item',$detilValue[$idx], '');
                    $this->mrTemplate->parseTemplate('kewajiban_item', 'a');
                    $k++;
                } elseif($kelapId != $value[$k]['kellapId'])  {
                    $kelapId =  $value[$k]['kellapId'];                    
                    $detilValue[$idx]['nama_kel_lap'] ='<b>'. $value[$k]['nama_kel_lap'].'</b>';
                    if(array_key_exists($kelapId, $totalPerKelJns)) {
                        $detilValue[$idx]['nilai'] =  NumberFormat::Accounting($totalPerKelJns[$kelapId], 2);
                    } else {
                        $detilValue[$idx]['nilai'] = '';
                    }
                    $detilValue[$idx]['style'] = 'font-weight:bold';
                    $detilValue[$idx]['style_nominal'] = 'font-weight:bold';
                    $this->mrTemplate->AddVars('kewajiban_item',$detilValue[$idx], '');
                    $this->mrTemplate->parseTemplate('kewajiban_item', 'a');
                }                
                $idx++;
            }
            $totalKewajiban+= $total;
            $this->mrTemplate->AddVar('kewajiban', 'KELJNSNAMA', $key);
            $this->mrTemplate->AddVar('kewajiban', 'TOTAL_NILAI', NumberFormat::Accounting($total, 2));
            $this->mrTemplate->parseTemplate('kewajiban', 'a');
        }


        $jumlahAktiva =  NumberFormat::Accounting(($totalAktiva), 2);
        $jumlahKewajiban =  NumberFormat::Accounting(($totalKewajiban), 2);
        $this->mrTemplate->AddVar('content', 'JUMLAH_AKTIVA', $jumlahAktiva);
        $this->mrTemplate->AddVar('content', 'JUMLAH_KEWAJIBAN_AKTIVA_BERSIH', $jumlahKewajiban);
    }

}

?>