<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/lap_bukubesar/business/AppLapBukubesar.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'main/function/date.php';

class ViewCetakLapBukubesar extends HtmlResponse {
    #var $Pesan;

    function TemplateModule() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'module/lap_bukubesar/template');
        $this->SetTemplateFile('view_cetak_lap_bukubesar.html');
    }

    function TemplateBase() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
        $this->SetTemplateFile('document-print.html');
        $this->SetTemplateFile('layout-common-print.html');
    }

    function ProcessRequest() {
        $Obj = new AppLapBukubesar();
        $_GET = $_GET->AsArray();

        $requestData['coa_id'] = Dispatcher::Instance()->Decrypt($_GET['rekening']);
        $requestData['coa_nama'] = Dispatcher::Instance()->Decrypt($_GET['coa_nama']);
        $requestData['start_date'] =Dispatcher::Instance()->Decrypt($_GET['tgl_awal']);
        $requestData['end_date'] = Dispatcher::Instance()->Decrypt($_GET['tgl_akhir']);
        
        $data_cetak = $Obj->GetBukuBesarHis($requestData);
        $info_coa = $Obj->GetInfoCoa($requestData['coa_id']);        
       
        $totalSaldo = $Obj->getTotalSaldo((array) $requestData);
        #print_r($data_cetak); exit;
        $return['rekening'] = $rekening;
        $return['total_saldo'] = $totalSaldo;
        $return['tgl_awal'] = $requestData['start_date'] ;
        $return['tgl_akhir'] = $requestData['end_date'];
        $return['bbhis'] = $data_cetak;
        $return['info_coa'] = $info_coa;
        return $return;
    }

    function ParseTemplate($data = NULL) {
        $totalSaldo = $data['total_saldo'];
        $dataList = $data['bbhis'];
        if (empty($dataList)) {
            $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
        } else {
            $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
            $this->mrTemplate->AddVar('content', 'REKENING', $data['rekening']);
            $this->mrTemplate->AddVar('content', 'TGL_AWAL', IndonesianDate($data['tgl_awal'], 'yyyy-mm-dd'));
            $this->mrTemplate->AddVar('content', 'TGL_AKHIR', IndonesianDate($data['tgl_akhir'], 'yyyy-mm-dd'));
            $this->mrTemplate->AddVar('content', 'REKENING', $data['info_coa']['rekening']);
            $this->mrTemplate->AddVar('content', 'NO_REKENING', $data['info_coa']['no_rekening']);
            #print_r($data['saldo']); exit;


            $kodeAkun = '';
            $items = array();
            $max = sizeof($dataList);
            $nk = 0;
            $saldo = 0;
            $saldoAkhir = 0;
            for ($k = 0; $k < $max;) {

                if ($kodeAkun == $dataList[$k]['akun_kode']) {
                    $items[$nk]['akun_kode'] = '';
                    $items[$nk]['akun_nama'] = '';
                    $items[$nk]['tanggal_jurnal_entri'] = $dataList[$k]['tanggal_jurnal_entri'];
                    $items[$nk]['sub_account'] = $dataList[$k]['sub_account'];
                    $items[$nk]['keterangan'] = $dataList[$k]['keterangan'];
                    $items[$nk]['nomor_referensi'] = $dataList[$k]['nomor_referensi'];

                    if ($dataList[$k]['debet'] > 1) {
                        $items[$nk]['debet'] = number_format($dataList[$k]['debet'], 2, ',', '.');
                    } else {
                        $items[$nk]['debet'] = '(' . number_format(($dataList[$k]['debet'] * (-1)), 2, ',', '.') . ')';
                    }

                    if ($dataList[$k]['kredit'] > 1) {
                        $items[$nk]['kredit'] = number_format($dataList[$k]['kredit'], 2, ',', '.');
                    } else {
                        $items[$nk]['kredit'] = '(' . number_format(($dataList[$k]['kredit'] * (-1)), 2, ',', '.') . ')';
                    }
                    $saldo += $dataList[$k]['debet'];
                    $saldo -= $dataList[$k]['kredit'];

                    if ((int) $dataList[$k]['id'] == 0) {     
                        $items[$nk]['is_show'] = 'display:none';
                        $saldoAkhir = $dataList[$k]['saldo_awal'];
                    } else {
                        $items[$nk]['is_show'] = '';
                        $saldoAkhir = $totalSaldo[$dataList[$k]['akun_kode']][$dataList[$k]['tanggal_jurnal_entri']][$dataList[$k]['nomor_referensi']][$dataList[$k]['id']];
                        //$saldoAkhir = ($dataList[$k]['saldo_awal'] + $saldo);
                    }


                    if ($saldoAkhir > 1) {
                        $items[$nk]['saldo_akhir'] = number_format($saldoAkhir, 2, ',', '.');
                    } else {
                        $items[$nk]['saldo_akhir'] = '(' . number_format(($saldoAkhir * (-1)), 2, ',', '.') . ')';
                    }


                    if (isset($dataList[$k + 1]['akun_kode'])) {
                        $cek = $dataList[$k + 1]['akun_kode'];
                    } else {
                        $cek = null;
                    }

                    if ($kodeAkun != $cek) {

                        $this->mrTemplate->SetAttribute('jumlah', 'visibility', 'visible');
                        $this->mrTemplate->AddVar('jumlah', 'SALDO_AKHIR', $items[$nk]['saldo_akhir']);
                    } else {
                        $this->mrTemplate->SetAttribute('jumlah', 'visibility', 'hidden');
                    }

                    $this->mrTemplate->AddVar('data_item_grid', 'HEADER', 'NO');
                    $this->mrTemplate->AddVars('data_item_grid', $items[$nk]);


                    $k++;
                } elseif ($kodeAkun != $dataList[$k]['akun_kode']) {
                    $kodeAkun = $dataList[$k]['akun_kode'];
                    $saldo = 0;
                    $saldoAkhir = 0;
                    $items[$nk]['akun_kode'] = '';
                    $items[$nk]['akun_nama'] = '';
                    $items[$nk]['tanggal_jurnal_entri'] = '';
                    $items[$nk]['sub_account'] = '';
                    $items[$nk]['keterangan'] = $kodeAkun . ' - ' . $dataList[$k]['akun_nama'];
                    $items[$nk]['nomor_referensi'] = '';
                    if ($dataList[$k]['saldo_awal'] > 1) {
                        $items[$nk]['saldo_awal'] = number_format($dataList[$k]['saldo_awal'], 2, ',', '.');
                    } else {
                        $items[$nk]['saldo_awal'] = '(' . number_format(($dataList[$k]['saldo_awal'] * (-1)), 2, ',', '.') . ')';
                    }

                    $items[$nk]['debet'] = '';
                    $items[$nk]['kredit'] = '';
                    $items[$nk]['saldo_akhir'] = '';
                    $this->mrTemplate->AddVar('data_item_grid', 'HEADER', 'YES');
                    $this->mrTemplate->AddVars('data_item_grid', $items[$nk]);
                }

                $this->mrTemplate->AddVars('data_item', $items[$nk]);
                $this->mrTemplate->parseTemplate('data_item', 'a');
                $nk++;
            }
        }
    }

}

?>