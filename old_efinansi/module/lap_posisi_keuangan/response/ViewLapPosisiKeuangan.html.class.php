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

require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/lap_posisi_keuangan/business/AppLapPosisiKeuangan.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'main/function/date.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'main/function/number_format.class.php';

class ViewLapPosisiKeuangan extends HtmlResponse {

    function TemplateModule() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'module/lap_posisi_keuangan/template');
        $this->SetTemplateFile('view_lap_posisi_keuangan.html');
    }

    function ProcessRequest() {
        $Obj = new AppLapPosisiKeuangan();

        $post = $_POST->AsArray();

        if (!empty($post['tanggal_awal_day']))
            $tglAwal = $post['tanggal_awal_year'] . "-" . $post['tanggal_awal_mon'] . "-" . $post['tanggal_awal_day'];
        else
            $tglAwal = date("Y-01-01");

        if (!empty($post['tanggal_akhir_day']))
            $tgl = $post['tanggal_akhir_year'] . "-" . $post['tanggal_akhir_mon'] . "-" . $post['tanggal_akhir_day'];
        else
            $tgl = date("Y-m-d");

        //tahun untuk combo
        $tahunTrans = $Obj->GetMinMaxThnTrans();
        Messenger::Instance()->SendToComponent('tanggal', 'Tanggal', 'view', 'html', 'tanggal_awal', array($tglAwal, $tahunTrans['minTahun'], $tahunTrans['maxTahun']), Messenger::CurrentRequest);

        Messenger::Instance()->SendToComponent('tanggal', 'Tanggal', 'view', 'html', 'tanggal_akhir', array($tgl, $tahunTrans['minTahun'], $tahunTrans['maxTahun']), Messenger::CurrentRequest);

        $return['laporan_all'] = $Obj->GetLaporanAll($tglAwal, $tgl);

        $return['tgl_awal'] = $tglAwal;
        $return['tgl_akhir'] = $tgl;
        return $return;
    }

    function ParseTemplate($data = NULL) {

        $this->mrTemplate->AddVar('content', 'URL_SEARCH', Dispatcher::Instance()->GetUrl('lap_posisi_keuangan', 'LapPosisiKeuangan', 'view', 'html'));
        $this->mrTemplate->AddVar('content', 'URL_CETAK', Dispatcher::Instance()->GetUrl('lap_posisi_keuangan', 'CetakLapPosisiKeuangan', 'view', 'html') . '&tgl_awal=' . Dispatcher::Instance()->Encrypt($data['tgl_awal']) . '&tgl_akhir=' . Dispatcher::Instance()->Encrypt($data['tgl_akhir']) . '&cetak=' . Dispatcher::Instance()->Encrypt('yes'));
        $this->mrTemplate->AddVar('content', 'URL_CETAK_DETIL', Dispatcher::Instance()->GetUrl('lap_posisi_keuangan', 'CetakLapPosisiKeuanganDetil', 'view', 'html') . '&tgl_awal=' . Dispatcher::Instance()->Encrypt($data['tgl_awal']) . '&tgl_akhir=' . Dispatcher::Instance()->Encrypt($data['tgl_akhir']) . '&cetak=' . Dispatcher::Instance()->Encrypt('yes'));
        $this->mrTemplate->AddVar('content', 'URL_EXCEL', Dispatcher::Instance()->GetUrl('lap_posisi_keuangan', 'excelLapPosisiKeuangan', 'view', 'xlsx') . '&tgl_awal=' . Dispatcher::Instance()->Encrypt($data['tgl_awal']) . '&tgl_akhir=' . Dispatcher::Instance()->Encrypt($data['tgl_akhir']) . '&cetak=' . Dispatcher::Instance()->Encrypt('yes'));
        $this->mrTemplate->AddVar('content', 'URL_EXCEL_DETIL', Dispatcher::Instance()->GetUrl('lap_posisi_keuangan', 'excelLapPosisiKeuanganDetil', 'view', 'xlsx') . '&tgl_awal=' . Dispatcher::Instance()->Encrypt($data['tgl_awal']) . '&tgl_akhir=' . Dispatcher::Instance()->Encrypt($data['tgl_akhir']) . '&cetak=' . Dispatcher::Instance()->Encrypt('yes'));
        $this->mrTemplate->AddVar('content', 'URL_RTF', Dispatcher::Instance()->GetUrl('lap_posisi_keuangan', 'RtfLapPosisiKeuangan', 'print', 'rtf') . '&tgl_awal=' . Dispatcher::Instance()->Encrypt($data['tgl_awal']) . '&tgl_akhir=' . Dispatcher::Instance()->Encrypt($data['tgl_akhir']) . '&cetak=' . Dispatcher::Instance()->Encrypt('yes'));

        $urlDetil = Dispatcher::Instance()->GetUrl('lap_posisi_keuangan', 'detilLapPosisiKeuangan', 'view', 'html');

        $gridList = $data['laporan_all'];
        foreach ($gridList as $key => $value) {
            if ($value['status'] == 'Ya')
                $aktiva[$value['kelJnsNama']][] = array(
                    "nama_kel_lap" => $value['nama_kel_lap'],
                    "nilai" => $value['nilai'],
                    "kelJnsNama" => $value['kelJnsNama'],
                    "kellapId" => $value['kellapId']
                );
            else
                $kewajiban[$value['kelJnsNama']][] = array(
                    "nama_kel_lap" => $value['nama_kel_lap'],
                    "nilai" => $value['nilai'],
                    "kelJnsNama" => $value['kelJnsNama'],
                    "kellapId" => $value['kellapId']
                );
        }
        $totalAktiva = 0;
        $totalKewajiban = 0;

        if (!empty($aktiva)) {
            foreach ($aktiva as $key => $value) {
                $total = 0;
                $this->mrTemplate->ClearTemplate('aktiva_item');

                foreach ($value as $detilKey => $detilValue) {
                    $detilValue['url_detil'] = $urlDetil . '&dataId=' . $detilValue['kellapId'] . '&tgl=' . $data['tgl_akhir'] . '&tgl_awal=' . $data['tgl_awal'];                    
                    $total += $detilValue['nilai'];                    
                    $detilValue['nilai'] = NumberFormat::Accounting($detilValue['nilai'], 2);
                    $this->mrTemplate->AddVars('aktiva_item', $detilValue, '');
                    $this->mrTemplate->parseTemplate('aktiva_item', 'a');
                }
                $totalAktiva+= $total;
                $this->mrTemplate->AddVar('aktiva', 'KELJNSNAMA', $key);
                $this->mrTemplate->AddVar('aktiva', 'TOTAL_NILAI', NumberFormat::Accounting($total, 2));
                $this->mrTemplate->parseTemplate('aktiva', 'a');
            }
        }

        if (!empty($kewajiban)) {
            foreach ($kewajiban as $key => $value) {
                $total = 0;
                $this->mrTemplate->ClearTemplate('kewajiban_item');

                foreach ($value as $detilKey => $detilValue) {
                    $total+= $detilValue['nilai'];
                    $detilValue['url_detil'] = $urlDetil . '&dataId=' . $detilValue['kellapId'] . '&tgl=' . $data['tgl_akhir'] . '&tgl_awal=' . $data['tgl_awal'];
                    $detilValue['nilai'] = NumberFormat::Accounting($detilValue['nilai'], 2);
                    $this->mrTemplate->AddVars('kewajiban_item', $detilValue, '');
                    $this->mrTemplate->parseTemplate('kewajiban_item', 'a');
                }
                $totalKewajiban+= $total;
                $this->mrTemplate->AddVar('kewajiban', 'KELJNSNAMA', $key);
                $this->mrTemplate->AddVar('kewajiban', 'TOTAL_NILAI', NumberFormat::Accounting($total, 2));
                $this->mrTemplate->parseTemplate('kewajiban', 'a');
            }
        }

        $jumlahAktiva = NumberFormat::Accounting($totalAktiva, 2);
        $jumlahKewajiban = NumberFormat::Accounting($totalKewajiban, 2);
        $this->mrTemplate->AddVar('content', 'JUMLAH_AKTIVA', $jumlahAktiva);
        $this->mrTemplate->AddVar('content', 'JUMLAH_KEWAJIBAN_AKTIVA_BERSIH', $jumlahKewajiban);
    }

}

?>
