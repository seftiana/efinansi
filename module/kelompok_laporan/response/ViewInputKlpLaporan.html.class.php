<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/kelompok_laporan/business/AppKlpLaporan.class.php';

class ViewInputKlpLaporan extends HtmlResponse {

    protected $mData;
    protected $mPesan;
    
    public function TemplateModule() {
        $this->SetTemplateBasedir(
            GTFWConfiguration::GetValue('application', 'docroot') .
            'module/kelompok_laporan/template'
        );
        $this->SetTemplateFile('input_klp_laporan.html');
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
        if(!empty($data_klp_lap) && empty($inputProcess)){
            $this->mData = $data_klp_lap;
            $kodeSistemUpParent = $this->mData['kellap_kode_sistem_up'];
            $this->mData ['kellap_parent_kode_sistem'] = $this->mData['kellap_kode_sistem_up'];
            $this->mData ['kellap_parent_level'] = $this->mData['kellap_level_up'];
            $this->mData['kellap_no_akhir'] = $Obj->getNoTerakhir($this->mData['kellap_parent_id']);
            $this->mData['kellap_no_selanjutnya'] =  $this->mData['kellap_order_by'];
            $process = 'edit';
        } else {
            $process = 'add';
            $parent = $Obj->GetDataById($idDec);
            
            $this->mData  = array();
            $this->mData['kellap_id'] = $parent['kellap_id'];
            $this->mData['kellap_pid'] = $parent['kellap_parent_id'];
            $this->mData['kellap_parent_id'] = $parent['kellap_id'];
            $this->mData ['kellap_parent_nama'] = $parent['kellap_nama'];
            $this->mData ['kellap_parent_kode_sistem'] = $parent['kellap_kode_sistem'];
            $this->mData ['kellap_parent_level'] = $parent['kellap_level'];
            $kodeSistemUpParent = $parent['kellap_kode_sistem'];
            $this->mData['kellap_kelompok'] = $parent['kellap_kelompok'];
            $this->mData['kellap_no_akhir'] = $Obj->getNoTerakhir($parent['kellap_id']);
            $this->mData['kellap_no_selanjutnya'] =  $Obj->getNoSelanjutnya($parent['kellap_id']);
            switch ($parent['kellap_tipe']){
                case 'root' : 
                        $tipe_parent = $parent['kellap_tipe'];
                        $tipe = 'child';
                        break;
                case 'parent' : 
                        $tipe_parent = $parent['kellap_tipe'];
                        $tipe = 'child';
                        break;
                case 'child' : 
                        $tipe ='child'; 
                        $tipe_parent = 'parent';
                        break;
            }
            
            $this->mData['kellap_parent_tipe'] = $tipe_parent;
            $this->mData['kellap_tipe'] = $tipe;
            
        }
        //get root parent
        $Obj->PrepareDataKelompokLaporanRoot($kodeSistemUpParent);
        $getRootKelompokLaporan = $Obj->GetLaporan(0);        
        
        if (!empty($msg)) {
            $this->mPesan = $msg[0][1];
            $this->mData  =  $msg[0][0];
        }
  
        $return['no_urutan'] = 0;//$Obj->GenerateNoUrutan($idJenisLaporan);
        $return['decDataId'] = $idDec;   
        $return['process'] = $process;
        $return['jns_lap'] = $jnsLap;
        $return['cari_id'] = $cariId;
        $return['get_root_kl']  = $getRootKelompokLaporan;
        return $return;
    }

    public function ParseTemplate($data = NULL) {
        if ($this->mPesan) {
            $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
            $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->mPesan);
        }

        if ($data['process'] =='edit') {
            $url = "updateKlpLaporan";
            $tambah = "Ubah";
            $process = '';
        } else {
            $url = "addKlpLaporan";
            $tambah = "Tambah";
            $process = '&process='.Dispatcher::Instance()->Encrypt('add');
        }

        $this->mrTemplate->AddVar('content', 'POPUP_PARENT_CHILD_KELLAP', 
            Dispatcher::Instance()->GetUrl(
                'kelompok_laporan', 
                'PopupParentChild', 
                'view', 
                'html'
            ));

        $this->mrTemplate->AddVar('content', 'URL_ACTION', 
            Dispatcher::Instance()->GetUrl(
                'kelompok_laporan', 
                $url, 
                'do', 
                'html'
            ) . 
            $process.
            "&jns_lap=" .  Dispatcher::Instance()->Encrypt($data['jns_lap']).
            "&cari=" .  Dispatcher::Instance()->Encrypt($data['cari_id']).
            "&dataId=" .  Dispatcher::Instance()->Encrypt($data['decDataId'])
        );
        
        switch ($this->mData['kellap_kelompok']){
            case 'AKTIVA': //aset kode 1
                $this->mrTemplate->AddVar('status',  'KELOMPOK_A_CHECKED','checked="checked"');
                $this->mrTemplate->AddVar('status',  'KELOMPOK_P_CHECKED','');
                $this->mrTemplate->AddVar('status',  'KELOMPOK_M_CHECKED','');
                $this->mrTemplate->AddVar('status',  'KELOMPOK_PP_CHECKED','');
                $this->mrTemplate->AddVar('status',  'KELOMPOK_B_CHECKED','');
                break;
            case 'PASIVA': //pasiva/kewajiban(liability) kode 2
                $this->mrTemplate->AddVar('status',  'KELOMPOK_A_CHECKED','');
                $this->mrTemplate->AddVar('status',  'KELOMPOK_P_CHECKED','checked="checked"');
                $this->mrTemplate->AddVar('status',  'KELOMPOK_M_CHECKED','');
                $this->mrTemplate->AddVar('status',  'KELOMPOK_PP_CHECKED','');
                $this->mrTemplate->AddVar('status',  'KELOMPOK_B_CHECKED','');
                break;
            case 'MODAL': //modal(equity) kode 3
                $this->mrTemplate->AddVar('status',  'KELOMPOK_A_CHECKED','');
                $this->mrTemplate->AddVar('status',  'KELOMPOK_P_CHECKED','');
                $this->mrTemplate->AddVar('status',  'KELOMPOK_M_CHECKED','checked="checked"');
                $this->mrTemplate->AddVar('status',  'KELOMPOK_PP_CHECKED','');
                $this->mrTemplate->AddVar('status',  'KELOMPOK_B_CHECKED','');
                break;
            case 'PENDAPATAN': //pendapatan kode 4
                $this->mrTemplate->AddVar('status',  'KELOMPOK_A_CHECKED','');
                $this->mrTemplate->AddVar('status',  'KELOMPOK_P_CHECKED','');
                $this->mrTemplate->AddVar('status',  'KELOMPOK_M_CHECKED','');
                $this->mrTemplate->AddVar('status',  'KELOMPOK_PP_CHECKED','checked="checked"');
                $this->mrTemplate->AddVar('status',  'KELOMPOK_B_CHECKED','');
                break;
            case 'BEBAN': //beban kode 5
                $this->mrTemplate->AddVar('status',  'KELOMPOK_A_CHECKED','');
                $this->mrTemplate->AddVar('status',  'KELOMPOK_P_CHECKED','');
                $this->mrTemplate->AddVar('status',  'KELOMPOK_M_CHECKED','');
                $this->mrTemplate->AddVar('status',  'KELOMPOK_PP_CHECKED','');
                $this->mrTemplate->AddVar('status',  'KELOMPOK_B_CHECKED','checked="checked"');
                break;
        }
        
        if($this->mData['kellap_is_tambah'] === 'Y') {
            $this->mrTemplate->AddVar('status',  'KELOMPOK_IT_Y_CHECKED','checked="checked"');
            $this->mrTemplate->AddVar('status',  'KELOMPOK_IT_T_CHECKED','');
        } else {
            $this->mrTemplate->AddVar('status',  'KELOMPOK_IT_T_CHECKED','checked="checked"');
            $this->mrTemplate->AddVar('status',  'KELOMPOK_IT_Y_CHECKED','');
        }   

        
        if($this->mData['kellap_tipe'] === 'root') {
            $this->mrTemplate->SetAttribute('status', 'visibility', 'hidden');
        } else {
            $this->mrTemplate->SetAttribute('status', 'visibility', 'visible');
            $this->mrTemplate->AddVar('status',  'KELLAP_NO_AKHIR',  $this->mData['kellap_no_akhir']);
            $this->mrTemplate->AddVar('status',  'KELLAP_NO_SELANJUTNYA',  $this->mData['kellap_no_selanjutnya']);
        }        
        
        $this->mrTemplate->AddVars('content',  $this->mData,'DATA_');
        //get root kelompok laporan
        if(!empty($data['get_root_kl'])) {
            $this->mrTemplate->AddVar('root_data', 'DATA_EMPTY', 'NO');
            foreach ($data['get_root_kl'] as $value) {  
            if($value['is_child'] == 0){                        
                $title = '<b>' . $value['nama'] . '</b>';                        
                $value['nama'] = $title;                    
                $value['padding'] = ($value['level'] - 1) * 15;
                $this->mrTemplate->AddVar('content', 'PADDING', $value['padding']+15);
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