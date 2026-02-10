<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/jurnal_kode/business/KodeJurnal.class.php';

require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/jurnal_kode/business/DetilKodeJurnal.class.php';

class ViewInputKodeJurnal extends HtmlResponse {

    var $Data;
    var $Pesan;

    function TemplateModule() {
        $this->SetTemplateBasedir($this->mrConfig->mApplication['docroot'] . 'module/jurnal_kode/template');
        $this->SetTemplateFile('input_kode_jurnal.html');
    }

    function ProcessRequest() {
        $idDec = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
        $Obj = new KodeJurnal();
        $msg = Messenger::Instance()->Receive(__FILE__);
        $this->Pesan = $msg[0][1];
        $this->Data = $msg[0][0];

        $dataKodeJurnal = $Obj->GetDataById($idDec);
        //print_r($dataKodeJurnal);
        $return['decDataId'] = $idDec;
        $return['dataKodeJurnal'] = $dataKodeJurnal;

        // tes detil
        $detilKodeJurnalObj = new DetilKodeJurnal();

        $dataDetilKodeJurnal = $detilKodeJurnalObj->getDataAll($idDec);
        $return['dataDetilKodeJurnal'] = $dataDetilKodeJurnal;

        return $return;
    }

    function ParseTemplate($data = NULL) {
        if ($this->Pesan) {
            $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
            $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
        }
        $dataKodeJurnal = $data['dataKodeJurnal'];

        if ($_REQUEST['dataId'] == '') {
            $url = "addKodeJurnal";
            $tambah = "Tambah";
        } else {
            $url = "updateKodeJurnal";
            $tambah = "Ubah";
        }
        if ($dataKodeJurnal[0]['metode_catat'] == 'accrual') {
            $this->mrTemplate->AddVar('content', 'AB', 'checked="checked"');
            $this->mrTemplate->AddVar('content', 'CB', '');
        } else {
            $this->mrTemplate->AddVar('content', 'AB', '');
            $this->mrTemplate->AddVar('content', 'CB', 'checked="checked"');
        }

        if ($dataKodeJurnal[0]['status_aktif'] == 'N') {
            $this->mrTemplate->AddVar('content', 'AKTIF_TIDAK', 'checked="checked"');
            $this->mrTemplate->AddVar('content', 'AKTIF_YA', '');
        } else {
            $this->mrTemplate->AddVar('content', 'AKTIF_TIDAK', '');
            $this->mrTemplate->AddVar('content', 'AKTIF_YA', 'checked="checked"');
        }
        
        $this->mrTemplate->AddVar('content', 'JUDUL', $tambah);
        $this->mrTemplate->AddVar('content', 'KODE', empty($dataKodeJurnal[0]['kode']) ? $this->Data['kode'] : $dataKodeJurnal[0]['kode']);
        $this->mrTemplate->AddVar('content', 'NAMA', empty($dataKodeJurnal[0]['nama']) ? $this->Data['nama'] : $dataKodeJurnal[0]['nama']);
        $this->mrTemplate->AddVar('content', 'JENIS_BIAYA_ID', empty($dataKodeJurnal[0]['jenis_biaya_id']) ? $this->Data['jenis_biaya_id'] : $dataKodeJurnal[0]['jenis_biaya_id']);
        $this->mrTemplate->AddVar('content', 'JENIS_BIAYA_NAMA', empty($dataKodeJurnal[0]['jenis_biaya_nama']) ? $this->Data['jenis_biaya_nama'] : $dataKodeJurnal[0]['jenis_biaya_nama']);

        $this->mrTemplate->AddVar('content', 'URL_ACTION', Dispatcher::Instance()->GetUrl('jurnal_kode', $url, 'do', 'html') . "&dataId=" . Dispatcher::Instance()->Encrypt($data['decDataId']));
        $this->mrTemplate->AddVar('content', 'URL_SEARCH', Dispatcher::Instance()->GetUrl('jurnal_kode', 'coa', 'popup', 'html') . '&data[name]=' . $this->data['nama'] . '&tipe=' . $data['tipe']);
        $this->mrTemplate->AddVar('content', 'URL_POPUP_JB', Dispatcher::Instance()->GetUrl('jurnal_kode', 'PopupJenisBiaya', 'view', 'html') );

        $this->mrTemplate->AddVar('content', 'DATAID', Dispatcher::Instance()->Decrypt($_GET['dataId']));
        $this->mrTemplate->AddVar('content', 'PAGE', Dispatcher::Instance()->Decrypt($_GET['page']));



        // tes detil
        if (empty($data['dataDetilKodeJurnal'])) {
            $this->mrTemplate->AddVar('data', 'DATA_EMPTY', 'YES');
        } else {
            $this->mrTemplate->AddVar('data', 'DATA_EMPTY', 'NO');
            $dataDetilKodeJurnal = $data['dataDetilKodeJurnal'];

            for ($i = 0; $i < sizeof($dataDetilKodeJurnal); $i++) {
                $no = $i + $data['start'];
                $dataDetilKodeJurnal[$i]['number'] = $no;
                if ($no % 2 != 0)
                    $dataDetilKodeJurnal[$i]['class_name'] = 'table-common-even';
                else
                    $dataDetilKodeJurnal[$i]['class_name'] = '';

                if ($i == 0)
                    $this->mrTemplate->AddVar('content', 'FIRST_NUMBER', $no);
                if ($i == sizeof($dataDetilKodeJurnal) - 1)
                    $this->mrTemplate->AddVar('content', 'LAST_NUMBER', $no);

                if ($dataDetilKodeJurnal[$i]['isdebet'] == 1) {
                    $dataDetilKodeJurnal[$i]['isdebet'] = "Debet";
                } else {
                    $dataDetilKodeJurnal[$i]['isdebet'] = "Kredit";
                }

                $this->mrTemplate->AddVars('data_item', $dataDetilKodeJurnal[$i], 'DATA_');
                $this->mrTemplate->parseTemplate('data_item', 'a');
            }
        }
    }

}

?>