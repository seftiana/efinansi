<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/laporan_konsolidasi/business/AppLaporanKonsolidasi.class.php';

class ViewDetailLaporanKonsolidasi extends HtmlResponse {

    var $Pesan;

    function TemplateModule() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'module/laporan_konsolidasi/template');
        $this->SetTemplateFile('view_detil_laporan_konsolidasi.html');
    }

    function TemplateBase() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
        $this->SetTemplateFile('document-common-popup.html');
        $this->SetTemplateFile('layout-common-popup.html');
    }

    function ProcessRequest() {
        $Obj = new AppLaporanKonsolidasi();

        $GET = is_object($_GET) ? $_GET->AsArray() : $_GET;
        $id_kel_lap = Dispatcher::Instance()->Decrypt($GET['kellap_id']);
        $status = Dispatcher::Instance()->Decrypt($GET['status']);
        $tanggalAwal = Dispatcher::Instance()->Decrypt($GET['tanggal_awal']);
        $tanggalAkhir = Dispatcher::Instance()->Decrypt($GET['tanggal_akhir']);
        $title = Dispatcher::Instance()->Decrypt($GET['title']);
        $subAccount = $GET['sub_account'];

        $data_list = $Obj->LaporanBuilder()->getLaporanDetail($tanggalAwal, $tanggalAkhir, $id_kel_lap,$subAccount,$status);
        $kodeSistem = $Obj->getKodeSistem();
        $data_list_ref = $Obj->LaporanBuilder()->getDataLaporanRefDetail($kodeSistem, $tanggalAwal, $tanggalAkhir, $id_kel_lap);

        $namaKelompok = $Obj->LaporanBuilder()->getKelompokInfo($id_kel_lap);

        $return['detil_lap'] = $data_list;
        $return['detil_lap_ref'] = $data_list_ref;
        $return['start'] = $startRec + 1;
        $return['id_lap'] = $id_kel_lap;
        $return['nama_kelompok'] = $namaKelompok['kellap_nama'];
        $return['is_tambah'] = $namaKelompok['kellap_is_tambah'];
        $return['status'] = $status;
        $return['title'] = $title;
        return $return;
    }

    function ParseTemplate($data = NULL) {
        $this->mrTemplate->AddVar('content', 'NAMA_KELOMPOK', $data['nama_kelompok']);
        $this->mrTemplate->AddVar('content', 'title', $data['title']);
        if (empty($data['detil_lap'])) {
            $this->mrTemplate->AddVar('data', 'DATA_EMPTY', 'YES');
        } else {
            $decPage = Dispatcher::Instance()->Decrypt($_REQUEST['page']);
            $encPage = Dispatcher::Instance()->Encrypt($decPage);
            $this->mrTemplate->AddVar('data', 'DATA_EMPTY', 'NO');
            $data_list = $data['detil_lap'];

            $totalNominal = 0;
            for ($i = 0; $i < sizeof($data_list); $i++) {
                $no = $i + $data['start'];
                $data_list[$i]['number'] = $no;
                $totalNominal += $data_list[$i]['nominal'];
                if ($no % 2 != 0) {
                    $data_list[$i]['class_name'] = 'table-common-even';
                } else {
                    $data_list[$i]['class_name'] = '';
                }

                if ($i == 0) {
                    $this->mrTemplate->AddVar('content', 'FIRST_NUMBER', $no);
                }
                if ($i == sizeof($data_list) - 1) {
                    $this->mrTemplate->AddVar('content', 'LAST_NUMBER', $no);
                }

                if (!empty($data['status']) && $data['status'] == 'TL') {
                    $nominal = $data_list[$i]['kellap_coa_saldo_lalu'];
                } else {
                    $nominal = $data_list[$i]['kellap_coa_saldo'];
                }

                $totalNominal += $nominal;

                if ($nominal >= 0) {
                    $data_list[$i]['kellap_nominal_saldo'] = number_format($nominal, 2, ',', '.');
                } else {
                    $data_list[$i]['kellap_nominal_saldo'] = '(' . number_format($nominal * (-1), 2, ',', '.') . ' )';
                }
                $this->mrTemplate->AddVars('data_item', $data_list[$i], 'DATA_');
                $this->mrTemplate->parseTemplate('data_item', 'a');
            }

            if ($totalNominal < 0) {
                $totalNominalF = '(' . number_format(($totalNominal * -1), 2, ',', '.') . ')';
            } else {
                $totalNominalF = number_format($totalNominal, 2, ',', '.');
            }
            $this->mrTemplate->AddVar('data', 'TOTAL_NOMINAL', $totalNominalF);
        }
        
        // untuk kelompok laporan ref
        if (empty($data['detil_lap_ref'])) {
            $this->mrTemplate->AddVar('data_ref', 'DATA_EMPTY', 'YES');
        } else {
            $this->mrTemplate->AddVar('data_ref', 'DATA_EMPTY', 'NO');
            $data_list_ref = $data['detil_lap_ref'];

            $totalNominalRef = 0;
            for ($i = 0; $i < sizeof($data_list_ref); $i++) {
                $no = $i + $data['start'];
                $data_list_ref[$i]['number'] = $no;
                $totalNominal += $data_list_ref[$i]['nominal'];
                if ($no % 2 != 0) {
                    $data_list_ref[$i]['class_name'] = 'table-common-even';
                } else {
                    $data_list_ref[$i]['class_name'] = '';
                }

                if ($i == 0) {
                    $this->mrTemplate->AddVar('content', 'FIRST_NUMBER', $no);
                }
                if ($i == sizeof($data_list_ref) - 1) {
                    $this->mrTemplate->AddVar('content', 'LAST_NUMBER', $no);
                }

                if (!empty($data['status']) && $data['status'] == 'TL') {
                    $nominal = $data_list_ref[$i]['saldo_lalu'];
                } else {
                    $nominal = $data_list_ref[$i]['saldo'];
                }

                $totalNominalRef += $nominal;

                if ($nominal >= 0) {
                    $data_list_ref[$i]['kellap_nominal_saldo'] = number_format($nominal, 2, ',', '.');
                } else {
                    $data_list_ref[$i]['kellap_nominal_saldo'] = '(' . number_format($nominal * (-1), 2, ',', '.') . ' )';
                }
                $this->mrTemplate->AddVars('data_item_ref', $data_list_ref[$i], 'DATA_');
                $this->mrTemplate->parseTemplate('data_item_ref', 'a');
            }

            if ($totalNominalRef < 0) {
                $totalRefNominalF = '(' . number_format(($totalNominalRef * -1), 2, ',', '.') . ')';
            } else {
                $totalRefNominalF = number_format($totalNominalRef, 2, ',', '.');
            }
          
            $this->mrTemplate->AddVar('data_ref', 'TOTAL_REF_NOMINAL', $totalRefNominalF);
        }
    }

}

?>