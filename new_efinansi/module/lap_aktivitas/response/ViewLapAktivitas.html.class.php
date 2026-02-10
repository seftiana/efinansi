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

require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/lap_aktivitas/business/AppLapAktifitas.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'main/function/date.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'main/function/number_format.class.php';

class ViewLapAktivitas extends HtmlResponse {

    function TemplateModule() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'module/lap_aktivitas/template');
        $this->SetTemplateFile('view_lap_aktivitas.html');
    }

    function ProcessRequest() {
        $Obj = new AppLapAktivitas();
        $post = $_POST->AsArray();

        if (!empty($post['tanggal_awal_day'])) {
            $tglAwal = $post['tanggal_awal_year'] . "-" . $post['tanggal_awal_mon'] . "-" . $post['tanggal_awal_day'];
        } else {
            $tglAwal = date("Y-01-01");
        }

        if (!empty($post['tanggal_akhir_day'])) {
            $tgl = $post['tanggal_akhir_year'] . "-" . $post['tanggal_akhir_mon'] . "-" . $post['tanggal_akhir_day'];
        } else {
            $tgl = date("Y-m-d");
        }

        //tahun untuk combo
        $tahunTrans = $Obj->GetMinMaxThnTrans();
        Messenger::Instance()->SendToComponent('tanggal', 'Tanggal', 'view', 'html', 'tanggal_awal', array(
            $tglAwal,
            $tahunTrans['minTahun'],
            $tahunTrans['maxTahun']
                ), Messenger::CurrentRequest);
        Messenger::Instance()->SendToComponent('tanggal', 'Tanggal', 'view', 'html', 'tanggal_akhir', array(
            $tgl,
            $tahunTrans['minTahun'],
            $tahunTrans['maxTahun']
                ), Messenger::CurrentRequest);
        $return['laporan_all'] = $Obj->GetLaporanAllDetil($tglAwal, $tgl);
        $return['tgl_awal'] = $tglAwal;
        $return['tgl_akhir'] = $tgl;

        return $return;
    }

    function ParseTemplate($data = NULL) {
        $this->mrTemplate->AddVar('content', 'URL_SEARCH', Dispatcher::Instance()->GetUrl('lap_aktivitas', 'LapAktivitas', 'view', 'html'));
        $this->mrTemplate->AddVar('content', 'URL_CETAK', Dispatcher::Instance()->GetUrl('lap_aktivitas', 'CetakLapAktivitas', 'view', 'html') . 
            '&tgl_awal=' . Dispatcher::Instance()->Encrypt($data['tgl_awal']) . 
            '&tgl_akhir=' . Dispatcher::Instance()->Encrypt($data['tgl_akhir']) . 
            '&cetak=' . Dispatcher::Instance()->Encrypt('yes'));
        $this->mrTemplate->AddVar('content', 'URL_EXCEL', Dispatcher::Instance()->GetUrl('lap_aktivitas', 'ExcelLapAktivitas', 'view', 'xlsx') . 
            '&tgl_awal=' . Dispatcher::Instance()->Encrypt($data['tgl_awal']) . 
            '&tgl_akhir=' . Dispatcher::Instance()->Encrypt($data['tgl_akhir']) .
            '&cetak=' . Dispatcher::Instance()->Encrypt('yes'));
        //detail
        $this->mrTemplate->AddVar('content', 'URL_CETAK_DETIL', Dispatcher::Instance()->GetUrl('lap_aktivitas', 'CetakLapAktivitasDetil', 'view', 'html') . 
            '&tgl_awal=' . Dispatcher::Instance()->Encrypt($data['tgl_awal']) . 
            '&tgl_akhir=' . Dispatcher::Instance()->Encrypt($data['tgl_akhir']) . 
            '&cetak=' . Dispatcher::Instance()->Encrypt('yes'));
        $this->mrTemplate->AddVar('content', 'URL_EXCEL_DETIL', Dispatcher::Instance()->GetUrl('lap_aktivitas', 'ExcelLapAktivitasDetil', 'view', 'xlsx') . 
            '&tgl_awal=' . Dispatcher::Instance()->Encrypt($data['tgl_awal']) . 
            '&tgl_akhir=' . Dispatcher::Instance()->Encrypt($data['tgl_akhir']) .
            '&cetak=' . Dispatcher::Instance()->Encrypt('yes'));
        
        $urlDetil = Dispatcher::Instance()->GetUrl('lap_aktivitas', 'detilLaporanAktivitas', 'view', 'html');

        //set pendapatan
        $this->mrTemplate->AddVar('content', 'URL_RTF', Dispatcher::Instance()->GetUrl('lap_aktivitas', 'RtfLapAktivitas', 'view', 'html') . 
            '&tgl_awal=' . Dispatcher::Instance()->Encrypt($data['tgl_awal']) . 
            '&tgl_akhir=' . Dispatcher::Instance()->Encrypt($data['tgl_akhir']) . 
            '&cetak=' . Dispatcher::Instance()->Encrypt('yes'));
        
        $gridList = $data['laporan_all'];
        //inisialisasi variable array
        $pendapatan = array();
        $beban = array();        
        if(!empty($gridList)){
            $nominalP = array();
            $nominalD = array();
            foreach ($gridList as $key => $value) {

                //if ($value['status'] == 'Ya') {
                if($value['kelJnsNama'] =='Pendapatan') {    
                    if($value['saldo_normal'] == 'D') {
                        $nilaiP[$value['kellapId']] += (0 - $value['nilai']);
                    } else {
                        $nilaiP[$value['kellapId']] += $value['nilai'];
                    }
                    
                    $pendapatan[$value['kelJnsNama']][$value['kellapId']] = array(
                        "nama_kel_lap" => $value['nama_kel_lap'],
                        "nilai" => $nilaiP[$value['kellapId']],
                        "kelJnsNama" => $value['kelJnsNama'],
                        "kellapId" => $value['kellapId']
                    );
                } else {
                    if($value['saldo_normal'] == 'K') {
                        $nilaiB[$value['kellapId']] += ( 0 - $value['nilai']);
                    } else {
                        $nilaiB[$value['kellapId']] += $value['nilai'];
                    }
                    $beban[$value['kelJnsNama']][$value['kellapId']] = array(
                        "nama_kel_lap" => $value['nama_kel_lap'],
                        "nilai" => $nilaiB[$value['kellapId']],
                        "kelJnsNama" => $value['kelJnsNama'],
                        "kellapId" => $value['kellapId']
                    );
                }
            }
        }

        $totalPendapatan = 0;
        $totalBiaya = 0;
        if (!empty($pendapatan)) {
            foreach ($pendapatan as $key => $value) {
                $total = 0;
                $this->mrTemplate->ClearTemplate('pendapatan_item');

                foreach ($value as $detilKey => $detilValue) {
                    $detilValue['url_detil'] = $urlDetil . '&dataId=' . $detilValue['kellapId'] . '&tgl=' . $data['tgl_akhir'] . '&tgl_awal=' . $data['tgl_awal'];
                    $total+= $detilValue['nilai'];
                    $detilValue['nilai'] = NumberFormat::Accounting($detilValue['nilai'],2);                    
                    $this->mrTemplate->AddVars('pendapatan_item', $detilValue, '');
                    $this->mrTemplate->parseTemplate('pendapatan_item', 'a');
                }
                $totalPendapatan+= $total;
                $this->mrTemplate->AddVar('pendapatan', 'KELJNSNAMA', $key);
                $this->mrTemplate->AddVar('pendapatan', 'TOTAL_NILAI', NumberFormat::Accounting($total, 2));
                $this->mrTemplate->parseTemplate('pendapatan', 'a');
            }
        }
        $this->mrTemplate->AddVar('content', 'TOTAL_PENDAPATAN',NumberFormat::Accounting($totalPendapatan, 2));
        if (!empty($beban)) {
            foreach ($beban as $key => $value) {
                $total = 0;
                $this->mrTemplate->ClearTemplate('beban_item');

                foreach ($value as $detilKey => $detilValue) {
                    $total+= $detilValue['nilai'];
                    $detilValue['url_detil'] = $urlDetil . '&dataId=' . $detilValue['kellapId'] . '&tgl=' . $data['tgl_akhir'] . '&tgl_awal=' . $data['tgl_awal'];
                    $detilValue['nilai'] =  NumberFormat::Accounting($detilValue['nilai'], 2);
                    $this->mrTemplate->AddVars('beban_item', $detilValue, '');
                    $this->mrTemplate->parseTemplate('beban_item', 'a');
                }
                $totalBiaya+= $total;
                $this->mrTemplate->AddVar('beban', 'KELJNSNAMA', $key);
                $this->mrTemplate->AddVar('beban', 'TOTAL_NILAI', NumberFormat::Accounting($total,2));
                $this->mrTemplate->parseTemplate('beban', 'a');
            }
        }
        $this->mrTemplate->AddVar('content', 'TOTAL_BEBAN', NumberFormat::Accounting($totalBiaya,2));

        $jumlahAktivaBersih = $totalPendapatan - $totalBiaya;

        $jumlahAktivaBersih =NumberFormat::Accounting($jumlahAktivaBersih, 2);
        $this->mrTemplate->AddVar('content', 'JUMLAH_AKTIVA_BERSIH', $jumlahAktivaBersih);
    }

}

?>