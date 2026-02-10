<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/realisasi_pencairan_2/business/RealisasiPencairan.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewInputRealisasiPencairan extends HtmlResponse {

    function TemplateModule() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') .
                'module/realisasi_pencairan_2/template');
        $this->SetTemplateFile('input_realisasi_pencairan.html');
    }

    function ProcessRequest() {
        $messenger = Messenger::Instance()->Receive(__FILE__);
        $userid = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
        $mObj = new RealisasiPencairan();
        $mUnitObj = new UserUnitKerja();
        $dataUnit = $mUnitObj->GetUnitKerjaRefUser($userid);
        $queryString = $mObj->_getQueryString();
        $periodeTahun = $mObj->GetPeriodeTahun(array('active' => true));
        //$nomorPengajuan     = $mObj->GetAutoGenerate();
        $minYear = date('Y', strtotime($periodeTahun[0]['start']));
        $maxYear = date('Y', strtotime($periodeTahun[0]['end']));
        $requestData = array();
        $dataKomponen = array();
        $grp = $_GET['grp']->mrVariable;
        $requestData['action'] = 'add'; // set default action
        $requestData['ta_id'] = $periodeTahun[0]['id'];
        $requestData['ta_nama'] = $periodeTahun[0]['name'];
        $requestData['tanggal'] = date('Y-m-d', time());
        $requestData['unit_id'] = $dataUnit['id'];
        $requestData['unit_nama'] = $dataUnit['nama'];
        //$requestData['nomor_pengajuan']     = $nomorPengajuan;

        if (isset($grp) AND $grp !== NULL) {
            $requestData['action'] = 'edit';
            $dataPengajuan = $mObj->ChangeKeyName($mObj->GetDataPengajuanRealisasiDet($grp));
            $komponenAnggaran = $mObj->ChangeKeyName($mObj->GetKomponenAnggaranPengajuanRealisasi($grp));

            for ($i = 0; $i < count($komponenAnggaran); $i++) {
                $dataKomponen[$i]['rp_id'] = $komponenAnggaran[$i]['id'];
                $dataKomponen[$i]['rp_kompkode'] = $komponenAnggaran[$i]['komponen_kode'];
                $dataKomponen[$i]['rp_kompnama'] = $komponenAnggaran[$i]['komponen_nama'];
                $dataKomponen[$i]['mak_kode'] = $komponenAnggaran[$i]['mak_kode'];
                $dataKomponen[$i]['deskripsi'] = $komponenAnggaran[$i]['deskripsi'];
                $dataKomponen[$i]['nilai'] = $komponenAnggaran[$i]['nominal'];
                $dataKomponen[$i]['nominal_sisa'] = $komponenAnggaran[$i]['sisa_dana'];
                $dataKomponen[$i]['nominal_budget'] = $komponenAnggaran[$i]['nominal_budget'];
            }

            $requestData['id'] = $dataPengajuan['id'];
            $requestData['kegiatanunit_id'] = $dataPengajuan['unit_id'];
            $requestData['kegiatandetail_id'] = $dataPengajuan['kegiatan_det_id'];
            $requestData['ta_id'] = $dataPengajuan['ta_id'];
            $requestData['ta_nama'] = $dataPengajuan['ta_nama'];
            $requestData['unit_id'] = $dataPengajuan['unit_id'];
            $requestData['unit_nama'] = $dataPengajuan['unit_nama'];
            $requestData['program_id'] = $dataPengajuan['program_id'];
            $requestData['program_nama'] = $dataPengajuan['program_nama'];
            $requestData['kegiatan_id'] = $dataPengajuan['kegiatan_id'];
            $requestData['kegiatan_nama'] = $dataPengajuan['kegiatan_nama'];
            $requestData['subkegiatan_id'] = $dataPengajuan['sub_kegiatan_id'];
            $requestData['subkegiatan_nama'] = $dataPengajuan['sub_kegiatan_nama'];
            $requestData['keterangan'] = $dataPengajuan['keterangan'];
            $requestData['nomor_pengajuan'] = $dataPengajuan['nomor_pengajuan'];
            $requestData['total_anggaran'] = $dataPengajuan['nominal_anggaran'];
            $requestData['realisasi_nominal'] = $dataPengajuan['nominal_realisasi'];
            $requestData['realisasi_pencairan'] = $dataPengajuan['nominal_pencairan'];
            $requestData['nominal'] = $dataPengajuan['nominal'];
            $requestData['persentase'] = $dataPengajuan['persen'];
            $requestData['tanggal'] = date('Y-m-d', strtotime($dataPengajuan['tanggal']));
            $requestData['tanggal_old'] = date('Y-m-d', strtotime($dataPengajuan['tanggal']));
        }

        if ($messenger) {
            $messengerData = $messenger[0][0];
            $messengerMsg = $messenger[0][1];
            $messengerStyle = $messenger[0][2];

            $tanggalDay = (int) $messengerData['tanggal_day'];
            $tanggalMon = (int) $messengerData['tanggal_mon'];
            $tanggalYear = (int) $messengerData['tanggal_year'];

            $requestData['action'] = $messengerData['data']['action'];
            $requestData['id'] = $messengerData['data']['id'];
            $requestData['kegiatanunit_id'] = $messengerData['data']['kegiatanunit_id'];
            $requestData['kegiatandetail_id'] = $messengerData['data']['kegiatandetail_id'];
            $requestData['ta_id'] = $messengerData['data']['ta_id'];
            $requestData['ta_nama'] = $messengerData['data']['ta_nama'];
            $requestData['unit_id'] = $messengerData['data']['unit_id'];
            $requestData['unit_nama'] = $messengerData['data']['unit_nama'];
            $requestData['program_id'] = $messengerData['data']['program_id'];
            $requestData['program_nama'] = $messengerData['data']['program_nama'];
            $requestData['kegiatan_id'] = $messengerData['data']['kegiatan_id'];
            $requestData['kegiatan_nama'] = $messengerData['data']['kegiatan_nama'];
            $requestData['subkegiatan_id'] = $messengerData['data']['subkegiatan_id'];
            $requestData['subkegiatan_nama'] = $messengerData['data']['subkegiatan_nama'];
            $requestData['keterangan'] = $messengerData['data']['keterangan'];
            $requestData['nomor_pengajuan'] = $messengerData['data']['nomor_pengajuan'];
            $requestData['total_anggaran'] = $messengerData['data']['total_anggaran'];
            $requestData['realisasi_nominal'] = $messengerData['data']['realisasi_nominal'];
            $requestData['realisasi_pencairan'] = $messengerData['data']['realisasi_pencairan'];
            $requestData['nominal'] = $messengerData['data']['nominal'];
            $requestData['persentase'] = $messengerData['persentase'];
            $requestData['tanggal'] = date('Y-m-d', mktime(0, 0, 0, $tanggalMon, $tanggalDay, $tanggalYear));
            $requestData['tanggal_old'] = $messengerData['tanggal_old'];

            $index = 0;
            if (!empty($messengerData['KOMP'])) {
                foreach ($messengerData['KOMP'] as $komp) {
                    $dataKomponen[$index]['rp_id'] = $komp['rp_id'];
                    $dataKomponen[$index]['rp_kompkode'] = $komp['kodeKomponen'];
                    $dataKomponen[$index]['rp_kompnama'] = $komp['namaKomponen'];
                    $dataKomponen[$index]['mak_kode'] = $komp['makKode'];
                    $dataKomponen[$index]['deskripsi'] = $komp['deskripsi'];
                    $dataKomponen[$index]['nilai'] = $komp['nominal_available'];
                    $dataKomponen[$index]['nominal_sisa'] = $komp['nominal'];
                    $dataKomponen[$index]['nominal_budget'] = $komp['nominal_budget'];
                    $index++;
                }
            }
        }

        // combobox tanggal
        Messenger::Instance()->SendToComponent(
                'tanggal', 'Tanggal', 'view', 'html', 'tanggal', array(
            $requestData['tanggal'],
            $minYear,
            $maxYear
                ), Messenger::CurrentRequest);

        $return['data_unit'] = $dataUnit;
        $return['query_string'] = $queryString;
        $return['request_data'] = $requestData;
        $return['message'] = $messengerMsg;
        $return['style'] = $messengerStyle;
        $return['data_komponen']['data'] = json_encode($dataKomponen);
        return $return;
    }

    function ParseTemplate($data = NULL) {
        $queryString = $data['query_string'];
        $dataUnit = $data['data_unit'];
        $requestData = $data['request_data'];
        $dataKomponen = $data['data_komponen'];
        $message = $data['message'];
        $style = $data['style'];
        $dataId = ($_GET['grp']->mrVariable === NULL) ? '' : $_GET['grp']->mrVariable;
        $requestData['total_anggaran_label'] = number_format((double) $requestData['total_anggaran'], 0, ',', '.');
        $requestData['realisasi_nominal_label'] = number_format((double) $requestData['realisasi_nominal'], 0, ',', '.');
        $requestData['realisasi_pencairan_label'] = number_format((double) $requestData['realisasi_pencairan'], 0, ',', '.');
        $urlReturn = Dispatcher::Instance()->GetUrl(
                        'realisasi_pencairan_2', 'RealisasiPencairan', 'view', 'html'
                ) . '&search=1&' . $queryString;
        $urlPopupUnit = Dispatcher::Instance()->GetUrl(
                'realisasi_pencairan_2', 'PopupUnitkerja', 'view', 'html'
        );
        $urlPopupSubKegiatan = Dispatcher::Instance()->GetUrl(
                        'realisasi_pencairan_2', 'subKegiatan', 'popup', 'html'
                ) . '&data_id=' . $dataId;

        // generate url action
        switch (strtoupper($requestData['action'])) {
            case 'ADD':
                $requestData['label'] = 'Tambah';
                $urlAction = Dispatcher::Instance()->GetUrl(
                                'realisasi_pencairan_2', 'AddRealisasiPencairan', 'do', 'json'
                        ) . '&' . $queryString;
                break;
            case 'EDIT':
                $requestData['label'] = 'Ubah';
                $urlAction = Dispatcher::Instance()->GetUrl(
                                'realisasi_pencairan_2', 'UpdateRealisasiPencairan', 'do', 'json'
                        ) . '&' . $queryString;
                break;
            default:
                $requestData['label'] = 'Tambah';
                $urlAction = Dispatcher::Instance()->GetUrl(
                                'realisasi_pencairan_2', 'AddRealisasiPencairan', 'do', 'json'
                        ) . '&' . $queryString;
                break;
        }

        if ($message) {
            $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
            $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $message);
            $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $style);
        }

        if (empty($dataId)) {
            $this->mrTemplate->AddVar('auto_number', 'AUTO_NUMBER', 'YES');
        } else {
            $this->mrTemplate->AddVar('auto_number', 'AUTO_NUMBER', 'NO');
        }
        $this->mrTemplate->AddVar('content', 'URL_RETURN', $urlReturn);
        $this->mrTemplate->AddVar('content', 'URL_ACTION', $urlAction);
        $this->mrTemplate->AddVar('content', 'POPUP_SUBKEGIATAN', $urlPopupSubKegiatan);
        $this->mrTemplate->AddVars('content', $requestData);
        $this->mrTemplate->AddVar('data_unit', 'STATUS', strtoupper($dataUnit['status']));
        $this->mrTemplate->AddVar('data_unit', 'POPUP_UNIT_KERJA', $urlPopupUnit);
        $this->mrTemplate->AddVar('data_unit', 'UNIT_ID', $requestData['unit_id']);
        $this->mrTemplate->AddVar('data_unit', 'UNIT_NAMA', $requestData['unit_nama']);
        $this->mrTemplate->AddVar('auto_number', 'NOMOR_PENGAJUAN', $requestData['nomor_pengajuan']);
        $this->mrTemplate->AddVars('content', $dataKomponen, 'KOMP_');
    }

}

?>