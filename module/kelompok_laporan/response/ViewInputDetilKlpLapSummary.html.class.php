<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/kelompok_laporan/business/AppKlpLaporan.class.php';

class ViewInputDetilKlpLapSummary extends HtmlResponse {

    protected $mData;
    protected $mPesan;

    public function TemplateModule() {
        $this->SetTemplateBasedir(
                GTFWConfiguration::GetValue('application', 'docroot') .
                'module/kelompok_laporan/template'
        );
        $this->SetTemplateFile('input_detil_klp_lap_summary.html');
    }

    public function ProcessRequest() {
        $idDec = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
        $idDec = is_object($idDec) ? $idDec->mrVariable : $idDec;
        $inputProcess = Dispatcher::Instance()->Decrypt($_REQUEST['process']);
        $inputProcess = is_object($inputProcess) ? $inputProcess->mrVariable : $inputProcess;

        $jnsLap = Dispatcher::Instance()->Decrypt($_REQUEST['jns_lap']);
        $jnsLap = is_object($jnsLap) ? $jnsLap->mrVariable : $jnsLap;

        $cariId = Dispatcher::Instance()->Decrypt($_REQUEST['cari']);
        $cariId = is_object($cariId) ? $cariId->mrVariable : $cariId;

        $Obj = new AppKelpLaporan();
        $msg = Messenger::Instance()->Receive(__FILE__);

        $data_klp_lap = $Obj->GetDataById($idDec);
        $idSubLap = array();
        if (!empty($data_klp_lap) && empty($inputProcess)) {
            $process = 'edit';
            $this->mData = $data_klp_lap;
            $parentId = $this->mData['kellap_parent_id'];
            $kodeSistemUpParent = $this->mData['kellap_kode_sistem_up'];
            $this->mData['kellap_parent_kode_sistem'] = $this->mData['kellap_kode_sistem_up'];
            $this->mData['kellap_parent_level'] = $this->mData['kellap_level_up'];
            $this->mData['kellap_no_akhir'] = $Obj->getNoTerakhir($this->mData['kellap_parent_id']);
            $this->mData['kellap_no_selanjutnya'] = $this->mData['kellap_order_by'];
            
            if (!empty($this->mData['kellap_summary_detail'])) {
                $summaryDetail = json_decode($this->mData['kellap_summary_detail'], true);
                $this->mData['kellap_operasi_perhitungan'] = $summaryDetail['operasiPerhitungan'];
                $getSubLap = $summaryDetail['dataKlpLap'];
                if($summaryDetail['operasiPerhitungan'] == '1'){                    
                    foreach ($getSubLap as $itemSubLap){
                        array_push($idSubLap, $itemSubLap['id']);
                    }
                }
            }
            
        } else {
            $process = 'add';
            $parent = $Obj->GetDataById($idDec);

            $this->mData = array();
            $this->mData['kellap_id'] = $parent['kellap_id'];
            $this->mData['kellap_pid'] = $parent['kellap_parent_id'];
            $this->mData['kellap_parent_id'] = $parent['kellap_id'];
            $this->mData['kellap_parent_nama'] = $parent['kellap_nama'];
            $this->mData['kellap_parent_kode_sistem'] = $parent['kellap_kode_sistem'];
            $this->mData['kellap_parent_level'] = $parent['kellap_level'];
            $kodeSistemUpParent = $parent['kellap_kode_sistem'];
            $this->mData['kellap_kelompok'] = $parent['kellap_kelompok'];
            $this->mData['kellap_no_akhir'] = $Obj->getNoTerakhir($parent['kellap_id']);
            $this->mData['kellap_no_selanjutnya'] = $Obj->getNoSelanjutnya($parent['kellap_id']);
            switch ($parent['kellap_tipe']) {
                case 'root' :
                    $tipe_parent = $parent['kellap_tipe'];
                    $tipe = 'child';
                    break;
                case 'parent' :
                    $tipe_parent = $parent['kellap_tipe'];
                    $tipe = 'child';
                    break;
                case 'child' :
                    $tipe = 'child';
                    $tipe_parent = 'parent';
                    break;
            }

            $this->mData['kellap_parent_tipe'] = $tipe_parent;
            $this->mData['kellap_tipe'] = $tipe;
        }


        
        //get root parent
        $Obj->PrepareDataKelompokLaporanRoot($kodeSistemUpParent);
        $getRootKelompokLaporan = $Obj->GetLaporan(0);
        
        //get child kelompok untuk tree pertama
        $Obj->PrepareDataKelompokLaporan($kodeSistemUpParent);
        if($process =='edit') {
            $pLapId = $parentId;
        } else {
            $pLapId = $idDec;
        }
        $getSubKelompok = $Obj->GetLaporan($pLapId, false);        
        $dataKlp = array();
        $klpIdx = 0;
        $klpIdxAll = 0;
        if (!empty($getSubKelompok)) {
            foreach ($getSubKelompok as $klp) {
                if ($klp['is_summary'] == 'Y') {
                    continue;
                }
                
                if(in_array($klp['id'], $idSubLap) && ($process =='edit')){
                    if ($klp['is_child'] == 0) {
                        $title = '<b>' . $klp['nama'] . '</b>';
                        $dataKlp[$klpIdx]['nama'] = $title;
                    } else {
                        $dataKlp[$klpIdx]['nama'] = $klp['nama'];
                    }

                    $dataKlp[$klpIdx]['padding'] = ($klp['level'] - 1) * 7;
                    $dataKlp[$klpIdx]['id'] = $klp['id'];
                    $dataKlp[$klpIdx]['level'] = $klp['level'];
                    $dataKlp[$klpIdx]['is_child'] = $klp['is_child'];
                    $klpIdx++;
                }
                
                
                //for klp all
                
                if ($klp['is_child'] == 0) {
                    $title = '<b>' . $klp['nama'] . '</b>';
                    $storeKlpLapAll[$klpIdxAll]['nama'] = $title;
                } else {
                    $storeKlpLapAll[$klpIdxAll]['nama'] = $klp['nama'];
                }
                
                $storeKlpLapAll[$klpIdxAll]['padding'] = ($klp['level'] - 1) * 7;
                $storeKlpLapAll[$klpIdxAll]['id'] = $klp['id'];
                $storeKlpLapAll[$klpIdxAll]['level'] = $klp['level'];
                $storeKlpLapAll[$klpIdxAll]['is_child'] = $klp['is_child'];
                $klpIdxAll++;
            }
        }
        

        if (!empty($msg)) {
            $this->mPesan = $msg[0][1];
            $this->mData = $msg[0][0];
        }

        if (!empty($this->mData['klp'])) {
            $dataKlp = array();
            $klpIdx = 0;
            $getSubKelompok = $this->mData['klp'];

            if (!empty($getSubKelompok)) {
                foreach ($getSubKelompok as $klp) {
                    //Tidak memfilter is_summary lagi
                    //karena hanya mengambil dari input form
                    if ($klp['is_child'] == 0) {
                        $title = '<b>' . $klp['nama'] . '</b>';
                        $dataKlp[$klpIdx]['nama'] = $title;
                    } else {
                        $dataKlp[$klpIdx]['nama'] = $klp['nama'];
                    }
                    $dataKlp[$klpIdx]['padding'] = ($klp['level'] - 1) * 7;
                    $dataKlp[$klpIdx]['id'] = $klp['id'];
                    $dataKlp[$klpIdx]['level'] = $klp['level'];
                    $dataKlp[$klpIdx]['is_child'] = $klp['is_child'];
                    $klpIdx++;
                }
            }
        }
        $return['no_urutan'] = 0; //$Obj->GenerateNoUrutan($idJenisLaporan);
        $return['decDataId'] = $idDec;
        $return['process'] = $process;
        $return['jns_lap'] = $jnsLap;
        $return['cari_id'] = $cariId;
        $return['get_root_kl'] = $getRootKelompokLaporan;
        $return['get_sub_kelompok'] = json_encode($dataKlp);
        $return['get_sub_kelompok_all'] = json_encode($storeKlpLapAll);
        return $return;
    }

    public function ParseTemplate($data = NULL) {

        $this->mrTemplate->AddVar('content', 'SUB_KLP_DATA', $data['get_sub_kelompok']);
        $this->mrTemplate->AddVar('content', 'SUB_KLP_DATA_ALL', $data['get_sub_kelompok_all']);
        if ($this->mPesan) {
            $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
            $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->mPesan);
        }

        if ($data['process'] == 'edit') {
            $url = "updateDetilKlpLapSummary";
            $tambah = "Ubah";
            $process = '';
        } else {
            $url = "addDetilKlpLapSummary";
            $tambah = "Tambah";
            $process = '&process=' . Dispatcher::Instance()->Encrypt('add');
        }

        $this->mrTemplate->AddVar('content', 'POPUP_PARENT_CHILD_KELLAP', Dispatcher::Instance()->GetUrl(
                        'kelompok_laporan', 'PopupParentChild', 'view', 'html'
        ));

        $this->mrTemplate->AddVar('content', 'URL_ACTION', Dispatcher::Instance()->GetUrl(
                        'kelompok_laporan', $url, 'do', 'html'
                ) .
                $process .
                "&jns_lap=" . Dispatcher::Instance()->Encrypt($data['jns_lap']) .
                "&cari=" . Dispatcher::Instance()->Encrypt($data['cari_id']) .
                "&dataId=" . Dispatcher::Instance()->Encrypt($data['decDataId'])
        );


        $this->mrTemplate->AddVar('content', 'DATA_KELLAP_ID', $this->mData['kellap_id']);
        $this->mrTemplate->AddVar('content', 'DATA_KELLAP_TIPE', $this->mData['kellap_tipe']);
        $this->mrTemplate->AddVar('content', 'DATA_KELLAP_KODE_SISTEM', $this->mData['kellap_kode_sistem']);
        $this->mrTemplate->AddVar('content', 'DATA_KELLAP_LEVEL', $this->mData['kellap_level']);
        $this->mrTemplate->AddVar('content', 'DATA_KELLAP_KELOMPOK', $this->mData['kellap_kelompok']);

        $this->mrTemplate->AddVar('content', 'DATA_KELLAP_PID', $this->mData['kellap_pid']);
        $this->mrTemplate->AddVar('content', 'DATA_KELLAP_PARENT_ID', $this->mData['kellap_parent_id']);
        $this->mrTemplate->AddVar('content', 'DATA_KELLAP_PARENT_NAMA', $this->mData['kellap_parent_nama']);
        $this->mrTemplate->AddVar('content', 'DATA_KELLAP_PARENT_TIPE', $this->mData['kellap_parent_tipe']);
        $this->mrTemplate->AddVar('content', 'DATA_KELLAP_PARENT_LEVEL', $this->mData['kellap_parent_level']);
        $this->mrTemplate->AddVar('content', 'DATA_KELLAP_PARENT_KODE_SISTEM', $this->mData['kellap_parent_kode_sistem']);

        $this->mrTemplate->AddVar('content', 'DATA_KELLAP_NO_AKHIR', $this->mData['kellap_no_akhir']);
        $this->mrTemplate->AddVar('content', 'DATA_KELLAP_NO_SELANJUTNYA', $this->mData['kellap_no_selanjutnya']);

        $this->mrTemplate->AddVar('content', 'DATA_KELLAP_NAMA', $this->mData['kellap_nama']);


        if ($this->mData['kellap_operasi_perhitungan'] == '1') {
            $this->mrTemplate->AddVar('content', 'DATA_OP_ADV_CHECKED', 'checked="checked"');
            $this->mrTemplate->AddVar('content', 'DATA_OP_BASIC_CHECKED', '');
        } else {
            $this->mrTemplate->AddVar('content', 'DATA_OP_BASIC_CHECKED', 'checked="checked"');
            $this->mrTemplate->AddVar('content', 'DATA_OP_ADV_CHECKED', '');
        }

        //get root kelompok laporan
        if (!empty($data['get_root_kl'])) {
            $this->mrTemplate->AddVar('root_data', 'DATA_EMPTY', 'NO');
            foreach ($data['get_root_kl'] as $value) {
                if ($value['is_child'] == 0) {
                    $title = '<b>' . $value['nama'] . '</b>';
                    $value['nama'] = $title;
                    $value['padding'] = ($value['level'] - 1) * 15;
                    $this->mrTemplate->AddVar('content', 'PADDING', $value['padding'] + 15);
                    $this->mrTemplate->AddVars('root_data_item', $value, 'KELLAP_');
                    $this->mrTemplate->parseTemplate('root_data_item', 'a');
                }
            }
        } else {
            $this->mrTemplate->AddVar('root_data', 'DATA_EMPTY', 'YES');
        }
    }

}

?>