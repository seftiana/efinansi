<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/lap_posisi_keuangan/business/AppDetilLapPosisiKeuangan.class.php';

class ViewDetilLapPosisiKeuangan extends HtmlResponse {

    var $Pesan;

    function TemplateModule() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'module/lap_posisi_keuangan/template');
        $this->SetTemplateFile('view_detil_lap_posisi_keuangan.html');
    }

    function TemplateBase() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
        $this->SetTemplateFile('document-common-popup.html');
        $this->SetTemplateFile('layout-common-popup.html');
    }

    function ProcessRequest() {
        $Obj = new AppKelpLaporan();

        $id_kel_lap = Dispatcher::Instance()->Decrypt($_GET['dataId']);
        $tanggalAwal = Dispatcher::Instance()->Decrypt($_GET['tgl_awal']);
        $tanggal = Dispatcher::Instance()->Decrypt($_GET['tgl']);
        //view
        $itemViewed = 20;
        $currPage = 1;
        $startRec = 0;
        if (isset($_GET['page'])) {
            $currPage = (string) $_GET['page']->StripHtmlTags()->SqlString()->Raw();
            $startRec = ($currPage - 1) * $itemViewed;
        }
        $data_list = $Obj->GetDataDetilKlpLaporan($tanggalAwal, $tanggal, $id_kel_lap, $startRec, $itemViewed);
        $saldoBerjalan = $Obj->GetSaldoBerjalan($tanggal);
//    print_r($data_list);

        $url = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, Dispatcher::Instance()->mSubModule, Dispatcher::Instance()->mAction, Dispatcher::Instance()->mType . '&key=' . Dispatcher::Instance()->Encrypt($key) . '&cari=' . Dispatcher::Instance()->Encrypt(1));

        Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top', array($itemViewed, $totalData, $url, $currPage), Messenger::CurrentRequest);

        $msg = Messenger::Instance()->Receive(__FILE__);
        $this->Pesan = $msg[0][1];
        $this->css = $msg[0][2];

        $return['detil_lap_aktivitas'] = $data_list;
        $return['saldo_berjalan'] = $saldoBerjalan;
        $return['start'] = $startRec + 1;
        $return['id_lap_aktivitas'] = $id_kel_lap;

        return $return;
    }

    function ParseTemplate($data = NULL) {

        if (empty($data['detil_lap_aktivitas'])) {
            $this->mrTemplate->AddVar('data', 'DATA_EMPTY', 'YES');
        } else {
            $decPage = Dispatcher::Instance()->Decrypt($_REQUEST['page']);
            $encPage = Dispatcher::Instance()->Encrypt($decPage);
            $this->mrTemplate->AddVar('data', 'DATA_EMPTY', 'NO');
            $data_list = $data['detil_lap_aktivitas'];
            $jumlahTotal = 0;
            for ($i = 0; $i < sizeof($data_list); $i++) {
                $no = $i + $data['start'];
                $data_list[$i]['number'] = $no;
                if ($no % 2 != 0)
                    $data_list[$i]['class_name'] = 'table-common-even';
                else
                    $data_list[$i]['class_name'] = '';

                if ($i == 0)
                    $this->mrTemplate->AddVar('content', 'FIRST_NUMBER', $no);
                if ($i == sizeof($data_list) - 1)
                    $this->mrTemplate->AddVar('content', 'LAST_NUMBER', $no);
                $idEnc = Dispatcher::Instance()->Encrypt($data_list[$i]['id']);
                $jumlahTotal += $data_list[$i]['coa_nominal'];
                
                if($data_list[$i]['rl_awal'] === '1') { 
                    $data_list[$i]['coa_nominal'] += ($data['saldo_berjalan'] > 0 ? ($data['saldo_berjalan'] * -1) :  $data['saldo_berjalan']);
                }

                if($data_list[$i]['rl_berjalan'] === '1') {
                    $data_list[$i]['coa_nominal'] += ($data['saldo_berjalan'] > 0 ?  $data['saldo_berjalan'] : ($data['saldo_berjalan'] * -1));
                }

                if ($data_list[$i]['coa_nominal'] < 0)
                    $data_list[$i]['coa_nominal'] = '(' . number_format(str_replace('-', '', $data_list[$i]['coa_nominal']), 2, ',', '.') . ')';
                else
                    $data_list[$i]['coa_nominal'] = number_format($data_list[$i]['coa_nominal'], 2, ',', '.');
                $this->mrTemplate->AddVars('data_item', $data_list[$i], 'DATA_');
                $this->mrTemplate->parseTemplate('data_item', 'a');
            }
            if ($jumlahTotal < 0)
                $jumlahTotalL = '(' . number_format(str_replace('-', '', $jumlahTotal), 2, ',', '.') . ')';
            else
                $jumlahTotalL = number_format($jumlahTotal, 2, ',', '.');
            $this->mrTemplate->AddVar('data', 'JUMLAH_TOTAL', $jumlahTotalL);
        }
    }

}

?>