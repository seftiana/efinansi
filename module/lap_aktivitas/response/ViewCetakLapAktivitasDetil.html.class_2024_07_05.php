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
    protected $mObj;

    function TemplateModule() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'module/lap_aktivitas/template');
        $this->SetTemplateFile('view_cetak_lap_aktivitas_detil.html');
    }

    function TemplateBase() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
        $this->SetTemplateFile('document-print.html');
        $this->SetTemplateFile('layout-common-print-custom-header.html');
    }

    function ProcessRequest() {
        $this->mObj = new AppLapAktivitas();
        $this->mObj->LaporanBuilder();
        
        $this->mObj->Setup();

        $get =  is_object($_GET) ? $_GET->AsArray() : $_GET;
        $tglAwal =  Dispatcher::Instance()->Decrypt($get['tanggal_awal']);
        $tglAkhir =  Dispatcher::Instance()->Decrypt($get['tanggal_akhir']);
        $subAccount = Dispatcher::Instance()->Decrypt($get['sub_account']);

        if($subAccount == '01-00-00-00-00-00-00'){
            $header = strtoupper(GTFWConfiguration::GetValue('organization', 'header_lap_yayasan'));
        }elseif($subAccount == '00-00-00-00-00-00-00'){
            $header = strtoupper(GTFWConfiguration::GetValue('organization', 'header_lap_perbanas'));
        }else{
            $header = strtoupper(GTFWConfiguration::GetValue('organization', 'company_name'));
        }

        $this->mObj->LaporanBuilder()->PrepareData($tglAwal, $tglAkhir,$subAccount);
        $getKelompokLaporan = $this->mObj->LaporanBuilder()->laporanView();
        $return['get_kelompok_laporan'] = $getKelompokLaporan;
        $return['tgl_awal']= $tglAwal;
        $return['tgl_akhir']= $tglAkhir;
        $return['sub_account']= $subAccount;
        $return['header']= $header;

        return $return;
    }

    function ParseTemplate($data = NULL) {
        $this->mrTemplate->AddVar('content', 'TGL_AWAL', IndonesianDate($data['tgl_awal'], 'yyyy-mm-dd'));
        $this->mrTemplate->AddVar('content', 'TGL_AKHIR', IndonesianDate($data['tgl_akhir'], 'yyyy-mm-dd'));
        $this->mrTemplate->AddVar('content', 'HEADER_LAPORAN', $data['header']);

        $aliranKas = $data['get_kelompok_laporan'];
        
        foreach ($aliranKas as $key => $itemLaporan) {
            $itemLaporan['padding'] = ($itemLaporan['level'] - 1) * 15;
            if ($itemLaporan['is_summary'] == 'Y') {
                
                $itemLaporan['style'] = 'font-weight:bold';
                $pengali = 1;
                $jumlahSaldoKlp = $itemLaporan['saldo_summary'] * $pengali;
                $this->mrTemplate->AddVar('status', 'KELLAP_STATUS', 'SUMMARY');
                
            }else {
                
                $pengali = ($itemLaporan['is_tambah'] == 'T') ? -1 : 1;
                $itemLaporan['style'] = '';
                $jumlahSaldoKlp = $itemLaporan['saldo'] * $pengali;
                
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
                    if($itemLaporan['level'] == 2){
                        $title ='<b>'.$itemLaporan['nama'].'</b>';
                    } else {
                        $title = $itemLaporan['nama'];
                    }
                    $itemLaporan['nama'] = $title;

                    $this->mrTemplate->AddVar('status', 'KELLAP_STATUS', 'CHILD');
                    $dataDetail = $this->mObj->LaporanBuilder()->getLaporanDetail($data['tgl_awal'], $data['tgl_akhir'], $itemLaporan['id'],$data['sub_account'],$status);
                    if(!empty($dataDetail)){
                        $this->mrTemplate->AddVar('is_show_detail', 'SHOW_DETAIL', 'YA');

                        $this->mrTemplate->ClearTemplate('aktivitas_coa');
                        $this->mrTemplate->SetAttribute('aktivitas_coa', 'visibility', 'visible');

                        foreach($dataDetail as $valueDet){
                            $valueDet['padding'] = ($itemLaporan['level'] + 1) * 15;

                            if (!empty($data['status']) && $data['status'] == 'TL') {
                                $nominal = $valueDet['kellap_coa_saldo_lalu']*$pengali;
                            } else {
                                $nominal = $valueDet['kellap_coa_saldo'] * $pengali;
                            }
                        
                            if ($nominal >= 0) {
                                $valueDet['kellap_nominal_saldo'] = number_format($nominal, 2, ',', '.');
                            } else {
                                $valueDet['kellap_nominal_saldo'] = '(' . number_format($nominal * (-1), 2, ',', '.') . ' )';
                            }

                            $this->mrTemplate->AddVars('aktivitas_coa', $valueDet, 'DET_');
                            $this->mrTemplate->parseTemplate('aktivitas_coa', 'a');
                        }
                        
                    }else{
                        $this->mrTemplate->SetAttribute('aktivitas_coa', 'visibility', 'hidden');
                    }
                }
            }
            
            
            if ($jumlahSaldoKlp >= 0) {
                $itemLaporan['nominal_saldo'] = number_format($jumlahSaldoKlp, 2, ',', '.');
            } else {
                $itemLaporan['nominal_saldo'] = '(' . number_format($jumlahSaldoKlp * (-1), 2, ',', '.') . ' )';
            }
            
            $this->mrTemplate->AddVar('status', 'KELLAP_URL_DETIL', $itemLaporan['url_detil']);
            $this->mrTemplate->AddVar('status', 'KELLAP_NOMINAL_SALDO', $itemLaporan['nominal_saldo']);
            $this->mrTemplate->AddVars('aktivitas', $itemLaporan, 'KELLAP_');
            $this->mrTemplate->parseTemplate('aktivitas', 'a');
        }
    }

}

?>