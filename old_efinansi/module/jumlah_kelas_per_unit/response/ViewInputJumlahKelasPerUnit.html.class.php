<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/jumlah_kelas_per_unit/business/GetDataAkademik.class.php';

require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/jumlah_kelas_per_unit/business/JumlahKelasPerUnit.class.php';

require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewInputJumlahKelasPerUnit extends HtmlResponse {

    var $Data;
    var $Pesan;
    var $Role;
    var $mObj;

    public function TemplateModule() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') .
                'module/jumlah_kelas_per_unit/template');
        $this->SetTemplateFile('view_input_jumlah_kelas_per_unit.html');
    }

    public function ProcessRequest() {

        $_POST = $_POST->AsArray();

        $idDec = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);

        $this->mObj = new JumlahKelasPerUnit;

        $daObj = new GetDataAkademik;
        $daObj->GetSemesterList();
       
        $msg = Messenger::Instance()->Receive(__FILE__);
        $this->Pesan = $msg[0][1];


        $userUnitKerja = new UserUnitKerja();
        $userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
        $this->Role = $userUnitKerja->GetRoleUser($userId);
        $unit = $userUnitKerja->GetUnitKerjaUser($userId);
        $unit_parent = $userUnitKerja->GetUnitKerja($unit['unit_kerja_parent_id']);

        if ($_REQUEST['dataId'] == '') {

            $this->Data = $_POST;
            //tahun anggaran dari $_POST dan $_GET, unitkerja dan sub-unit dari $_POST dan $_GET
            $tahun_anggaran = $this->mObj->GetTahunAnggaranAktif();
            $this->Data['tahun_anggaran'] = $tahun_anggaran['id'];
            $this->Data['tahun_anggaran_label'] = $tahun_anggaran['name'];
            $this->Data['unitkerja'] = $unit['unit_kerja_id'];
            $this->Data['unitkerja_label'] = $unit['unit_kerja_nama'];
        } else {
            //edit data
            $dataJk = $this->mObj->GetDataJummlahKelasPerUnitById($idDec);
            $this->Data['tahun_anggaran_old'] = $dataJk['tahun_anggaran_id_old'];
            $this->Data['tahun_anggaran'] = $dataJk['tahun_anggaran_id'];
            $this->Data['unitkerja'] = $dataJk['unit_kerja_id'];
            $this->Data['unitkerja_old'] = $dataJk['unit_kerja_id_old'];
            $this->Data['unitkerja_label'] = $dataJk['unit_kerja_nama'];
            $this->Data['jumlah_kelas'] = $dataJk['jumlah_kelas'];
            $this->Data['jumlah_kelas_id'] = $dataJk['jumlah_kelas_id'];
            $this->Data['prodi_nama'] = $dataJk['prodi_nama'];
            $this->Data['sgasal'] = $dataJk['prodi_sm_gasal_id'];
            $this->Data['sgenap'] = $dataJk['prodi_sm_genap_id'];
            $this->Data['prodi_kelas_gasal'] = $dataJk['prodi_sm_gasal'];
            $this->Data['prodi_kelas_genap'] = $dataJk['prodi_sm_genap'];
        }

        if (isset($msg[0][0])):
            $this->Data = $msg[0][0];
        endif;


        $arr_tahun_anggaran = $this->mObj->GetComboTahunAnggaran();

        Messenger::Instance()->SendToComponent(
                'combobox', 'Combobox', 'view', 'html', 'tahun_anggaran', array(
            'tahun_anggaran',
            $arr_tahun_anggaran,
            $this->Data['tahun_anggaran'], '-',
            ' style="width:200px;" id="tahun_anggaran"'), Messenger::CurrentRequest);

        //semester gasal
        $sgasal = $daObj->GetSemesterGasal();
        Messenger::Instance()->SendToComponent(
                'combobox', 'Combobox', 'view', 'html', 'sgasal', array(
            'sgasal',
            $sgasal,
            $this->Data['sgasal'], 'false',
            ' style="width:200px;"  id="sgasal" onChange="getProdiKelasGasal(this.value)"'), Messenger::CurrentRequest);
        //semester genap
        $sgenap = $daObj->GetSemesterGenap();
        Messenger::Instance()->SendToComponent(
                'combobox', 'Combobox', 'view', 'html', 'sgenap', array(
            'sgenap',
            $sgenap,
            $this->Data['sgenap'], 'false',
            ' style="width:200px;"  id="sgenap"  onChange="getProdiKelasGenap(this.value)"'), Messenger::CurrentRequest);

        $prodiKelasGasal = $daObj->GetJumlahKelasPerProdi($this->Data['sgasal']);

        Messenger::Instance()->SendToComponent(
                'combobox', 'Combobox', 'view', 'html', 'prodi_kelas_gasal', array(
            'prodi_kelas_gasal',
            $prodiKelasGasal,
            $this->Data['prodi_kelas_gasal'],
            'false',
            ''
                ), Messenger::CurrentRequest);


        $prodiKelasGenap = $daObj->GetJumlahKelasPerProdi($this->Data['sgenap']);

        Messenger::Instance()->SendToComponent(
                'combobox', 'Combobox', 'view', 'html', 'prodi_kelas_genap', array(
            'prodi_kelas_genap',
            $prodiKelasGenap,
            $this->Data['prodi_kelas_genap'],
            'false',
            ''
                ), Messenger::CurrentRequest);
        $return['decDataId'] = $idDec;
        $return['total_sub_unit'] = $userUnitKerja->GetTotalSubUnitKerja($unit['unit_kerja_id']);
        
        //cek status service iintegrasi ke gtakademik
        $return['status_service'] = $daObj->GetStatusService();        
        return $return;
    }

    public function ParseTemplate($data = NULL) {

        if ($data['status_service'] == true) {
            $this->mrTemplate->AddVar('status_service', 'IS_AVAILABLE', 'YES');
        } else {
            $this->mrTemplate->AddVar('status_service', 'IS_AVAILABLE', 'NO');
        }
        
        if ($data['total_sub_unit'] > 0) {
            $this->mrTemplate->AddVar('cek_unitkerja_parent', 'IS_PARENT', 'YES');
        } else {
            $this->mrTemplate->AddVar('cek_unitkerja_parent', 'IS_PARENT', 'NO');
        }
        //print_r($this->Data);
        $this->mrTemplate->AddVar('content', 'JUDUL', $tambah);
        $this->mrTemplate->AddVar('content', 'JUMLAH_KELAS_ID', $this->Data['jumlah_kelas_id']);
        $this->mrTemplate->AddVar('content', 'JUMLAH_KELAS', $this->Data['jumlah_kelas']);
        $this->mrTemplate->AddVar('content', 'UNITKERJA', $this->Data['unitkerja']);
        $this->mrTemplate->AddVar('content', 'UNITKERJA_OLD', $this->Data['unitkerja_old']);
        $this->mrTemplate->AddVar('content', 'TAHUN_ANGGARAN_OLD', $this->Data['tahun_anggaran_old']);
        $this->mrTemplate->AddVar('cek_unitkerja_parent', 'UNITKERJA_LABEL', $this->Data['unitkerja_label']);
        $this->mrTemplate->AddVar('cek_unitkerja_parent', 'URL_POPUP_UNITKERJA', Dispatcher::Instance()->GetUrl(
                        'jumlah_kelas_per_unit', 'popupUnitKerja', 'view', 'html'));


        if ($this->Pesan) {
            $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
            $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
        }


        if ($_REQUEST['dataId'] == '') {
            $url = "addJumlahKelasPerUnit";
            $tambah = "Tambah";
        } else {
            $url = "updateJumlahKelasPerUnit";
            $tambah = "Ubah";
            $this->mrTemplate->AddVar('content', 'KELAS_ID', $data['decDataId']);
        }

        $this->mrTemplate->AddVar('content', 'URL_ACTION', Dispatcher::Instance()->GetUrl(
                        'jumlah_kelas_per_unit', $url, 'do', 'html') .
                "&dataId=" .
                Dispatcher::Instance()->Encrypt($data['decDataId']));

        $this->mrTemplate->AddVar('content', 'JUDUL', $tambah);
        $this->mrTemplate->AddVar('content', 'DATAID', Dispatcher::Instance()->Decrypt($_GET['dataId']));
        $this->mrTemplate->AddVar('content', 'PAGE', Dispatcher::Instance()->Decrypt($_GET['page']));
    }

}

?>