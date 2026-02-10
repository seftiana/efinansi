<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') . 
    'module/kelompok_laporan/business/AppKlpLaporan.class.php';

class ViewInputDetilKlpLaporan extends HtmlResponse {

    protected $mData;
    protected $mPesan;
    protected $mStyle;

    public function TemplateModule() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 
            'module/kelompok_laporan/template');
        $this->SetTemplateFile('input_detil_klp_laporan.html');
    }

    public function ProcessRequest() {

        $idDec = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
        $idDec = is_object($idDec) ? $idDec->mrVariable : $idDec;
        
        $jnsLap = Dispatcher::Instance()->Decrypt($_REQUEST['jns_lap']);
        $jnsLap = is_object($jnsLap) ? $jnsLap->mrVariable : $jnsLap;
        
        $cariId = Dispatcher::Instance()->Decrypt($_REQUEST['cari']);
        $cariId = is_object($cariId) ? $cariId->mrVariable : $cariId;
        
        $Obj = new AppKelpLaporan();
        $msg = Messenger::Instance()->Receive(__FILE__);       
        
        
        $index = 0;
        $klpIdx = 0;
        $data_klp_lap = $Obj->GetDataById($idDec);       
        $getCoaKelompok = $Obj->GetCoaPerKelompok($idDec);
        $getKlpLaporan =  $Obj->GetKlpPerKelompok($idDec);
        if(!empty($data_klp_lap)){
            $this->mData = $data_klp_lap;
            $kodeSistemUpParent = $this->mData['kellap_kode_sistem_up'];
            $this->mData ['kellap_parent_kode_sistem'] = $this->mData['kellap_kode_sistem_up'];
            $this->mData ['kellap_parent_level'] = $this->mData['kellap_level_up'];
            $this->mData['kellap_no_akhir'] = $Obj->getNoTerakhir($this->mData['kellap_parent_id']);
            $this->mData['kellap_no_selanjutnya'] =  $this->mData['kellap_order_by'];
        } 
        
        $dataCoa = array();
        $dataKlp = array();
        foreach($getCoaKelompok as $coa){
            $dataCoa[$index]['id'] = $coa['coa_id'];
            $dataCoa[$index]['kode'] = $coa['coa_kode'];
            $dataCoa[$index]['nama'] = $coa['coa_nama'];
            $dataCoa[$index]['is_positif'] = $coa['coa_is_positif'];
            $dataCoa[$index]['is_saldo_awal'] = $coa['coa_is_saldo_awal'];
            $dataCoa[$index]['is_mutasi_dk'] = $coa['coa_is_mutasi_dk'];
            $dataCoa[$index]['is_mutasi_d'] = $coa['coa_is_mutasi_d'];
            $dataCoa[$index]['is_mutasi_k'] = $coa['coa_is_mutasi_k'];
            $index++;
        }
        
        foreach($getKlpLaporan as $klp){
            $dataKlp[$klpIdx]['id'] = $klp['klp_id'];
            $dataKlp[$klpIdx]['nama'] = $klp['klp_nama'];
            $klpIdx++;
        }
        
        //get root parent
        $Obj->PrepareDataKelompokLaporanRoot($kodeSistemUpParent);
        $getRootKelompokLaporan = $Obj->GetLaporan(0);        
        
        
        if (!empty($msg)) {
            $this->mPesan = $msg[0][1];
            $this->mData  =  $msg[0][0];
        
            $dataCoa = array();
            $dataKlp = array();
            $messengerData = $msg[0][0];
            $message = $msg[0][1];
            $this->mStyle = $msg[0][2];
            
            $index = 0;

            if ($messengerData['coa'] && !empty($messengerData['coa'])) {
                foreach ($messengerData['coa'] as $coa) {
                    $dataCoa[$index]['id'] = $coa['id'];
                    $dataCoa[$index]['kode'] = $coa['kode'];
                    $dataCoa[$index]['nama'] = $coa['nama'];
                    $dataCoa[$index]['is_positif'] = $coa['is_positif'];
                    $dataCoa[$index]['is_saldo_awal'] = $coa['is_saldo_awal'];
                    $dataCoa[$index]['is_mutasi_dk'] = $coa['is_mutasi_dk'];
                    $dataCoa[$index]['is_mutasi_d'] = $coa['is_mutasi_d'];
                    $dataCoa[$index]['is_mutasi_k'] = $coa['is_mutasi_k'];                    
                    $index++;
                }
            }
            unset($index);
            
            $klpIdx = 0;
            if ($messengerData['klp'] && !empty($messengerData['klp'])) {
                foreach ($messengerData['klp'] as $klp) {
                    $dataKlp[$klpIdx]['id'] = $klp['id'];
                    $dataKlp[$klpIdx]['nama'] = $klp['nama'];
                    $klpIdx++;
                }
            }
            unset($klpIdx);
        }   

        $return['decDataId'] = $idDec;   
        $return['jns_lap'] = $jnsLap;
        $return['cari_id'] = $cariId;
        $return['get_root_kl']  = $getRootKelompokLaporan;
        $return['coa']['data'] = json_encode($dataCoa);
        $return['klp']['data'] = json_encode($dataKlp);
        return $return;
    }

    public function ParseTemplate($data = NULL) {  
        //$this->mrTemplate->AddVars('content',  $this->mData,'DATA_');
        $this->mrTemplate->AddVar('content','DATA_KELLAP_ID',$this->mData['kellap_id']);
        $this->mrTemplate->AddVar('content','DATA_KELLAP_TIPE',$this->mData['kellap_tipe']);
        $this->mrTemplate->AddVar('content','DATA_KELLAP_KODE_SISTEM',$this->mData['kellap_kode_sistem']);
        $this->mrTemplate->AddVar('content','DATA_KELLAP_LEVEL',$this->mData['kellap_level']);
        $this->mrTemplate->AddVar('content','DATA_KELLAP_PAGE',$this->mData['page']);
        $this->mrTemplate->AddVar('content','DATA_KELLAP_NAMA',$this->mData['kellap_nama']);
        $this->mrTemplate->AddVar('content', 'COA_DATA',$data['coa']['data']);
        $this->mrTemplate->AddVar('content','KLP_DATA', $data['klp']['data']);
        //$this->mrTemplate->clearTemplate('content');
        //get root kelompok laporan
        if(!empty($data['get_root_kl'])) {
            $this->mrTemplate->AddVar('root_data', 'DATA_EMPTY', 'NO');
            foreach ($data['get_root_kl'] as $value) {                          
                $title = '<b>' . $value['nama'] . '</b>';                        
                $value['nama'] = $title;                    
                $value['padding'] = ($value['level'] - 1) * 15;
                $this->mrTemplate->AddVars('root_data_item', $value, 'KELLAP_');
                $this->mrTemplate->parseTemplate('root_data_item', 'a');
            }       
        } else {
            $this->mrTemplate->AddVar('root_data', 'DATA_EMPTY', 'YES');
        }
        
        if ($this->mPesan) {
            $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
            $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->mPesan);
            $this->mrTemplate->AddVar('warning_box', 'CSS_PESAN', $this->mStyle);
        }
        #$klp_lap = $data['data_klp_lap'];

        $this->mrTemplate->AddVar('content', 'JUDUL', 'Tambah');
        $this->mrTemplate->AddVar(
            'content', 
            'URL_ACTION', 
            Dispatcher::Instance()->GetUrl(
                'kelompok_laporan', 
                'addDetilKlpLaporan', 
                'do', 
                'html'
            ) .
            "&jns_lap=" .  Dispatcher::Instance()->Encrypt($data['jns_lap']).
            "&cari=" .  Dispatcher::Instance()->Encrypt($data['cari_id']).
            "&dataId=" .  Dispatcher::Instance()->Encrypt($data['decDataId'])
        );

        $this->mrTemplate->AddVar(
            'content', 
            'URL_POP_UP_COA', 
            Dispatcher::Instance()->GetUrl(
                'kelompok_laporan', 
                'PopUpCoa', 
                'view', 
                'html'
            )
        );
        
        $this->mrTemplate->AddVar(
            'content', 
            'URL_POPUP_KLP',
            Dispatcher::Instance()->GetUrl(
                'kelompok_laporan', 
                'PopupKlpLaporan', 
                'view', 
                'html'
            )
        );
    }
}

?>