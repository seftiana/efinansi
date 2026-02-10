<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/kelompok_laporan/business/AppKlpLaporan.class.php';

class ViewKlpLaporan extends HtmlResponse {

    var $Pesan;

    public function TemplateModule() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') .
                'module/kelompok_laporan/template');
        $this->SetTemplateFile('view_klp_laporan.html');
    }

    public function ProcessRequest() {
        $post = (is_object($_POST) ? $_POST->AsArray() :  $_POST);
        $get = (is_object($_GET) ? $_GET->AsArray() :  $_GET);
        
        $msg = Messenger::Instance()->Receive(__FILE__);
        $this->Pesan = $msg[0][1];
        $this->css = $msg[0][2];
        $Obj = new AppKelpLaporan();
        $getComboRoot = $Obj->getComboRoot();
        
        if(isset($post['btncari'])){
            $jnsLap = $post['jns_lap'];
        }elseif(isset($get['cari'])){
            $jnsLap = Dispatcher::Instance()->Decrypt($get['jns_lap']);
        } else {            
            if(!empty($getComboRoot)) {
                $jnsLap = $getComboRoot[0]['id'];
            }
        }
        
        $ks = $Obj->getRootKodeSistem($jnsLap);        
        $Obj->PrepareDataKelompokLaporan($ks);
        $dataKelompok = $Obj->GetLaporan();

        
        # Combobox
        Messenger::Instance()->SendToComponent(
           'combobox',
           'Combobox',
           'view',
           'html',
           'jns_lap',
           array(
              'jns_lap',
              $getComboRoot,
              $jnsLap,
              false,
              'style="width: 135px;" id="cmb_jenis_laporan"'
           ),
           Messenger::CurrentRequest
        );
        
        $return['data_kelompok'] = $dataKelompok;
        $return['jns_lap'] = $jnsLap;
        return $return;
    }

    public function ParseTemplate($data = NULL) {
        $dataKelompok = $data['data_kelompok'];
        $search = $data['search'];
        $this->mrTemplate->AddVar('content', 'KEY', $search['key']);
        $this->mrTemplate->AddVar('content', 'URL_SEARCH', Dispatcher::Instance()->GetUrl(
                        'kelompok_laporan', 'KlpLaporan', 'view', 'html'));

        $this->mrTemplate->AddVar('content', 'URL_ADD', Dispatcher::Instance()->GetUrl(
                        'kelompok_laporan', 'inputKlpLaporan', 'view', 'html'));
        if ($this->Pesan) {
            $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
            $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
            $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
        }

        if (empty($dataKelompok)) {
            $this->mrTemplate->AddVar('data', 'DATA_EMPTY', 'YES');
        } else {
            $this->mrTemplate->AddVar('data', 'DATA_EMPTY', 'NO');

            //untuk proes hapus
            //dipake componenet confirm delete
            $urlAccept = 'kelompok_laporan|deleteKlpLaporan|do|html-jns_lap|cari-'.
                $data['jns_lap'].'|'.Dispatcher::Instance()->Encrypt(1);
            $urlReturn = 'kelompok_laporan|KlpLaporan|view|html-jns_lap|cari-'.
				$data['jns_lap'].'|'.Dispatcher::Instance()->Encrypt(1);
			$label = 'Kelompok Laporan';
            //end
            
            $jnsLap = Dispatcher::Instance()->Encrypt($data['jns_lap']);
            $cariId = Dispatcher::Instance()->Encrypt(1);            
            foreach ($dataKelompok as $value) {
                
                $idEnc = Dispatcher::Instance()->Encrypt($value['id']);
                
                if ($value['is_child'] == '0' || $value['tipe'] =='root') {                        
                    $title = '<b>' . $value['nama'] . '</b>';                        
                    $value['nama'] = $title;                    
                } else {
                    $value['nama'] = $value['nama'];                    
                }
                $value['padding'] = ($value['level'] - 1) * 15;
                $value['url_edit'] = Dispatcher::Instance()->GetUrl(
                    'kelompok_laporan', 
                    'inputKlpLaporan', 
                    'view', 
                    'html'
                ) . '&dataId=' . $idEnc . '&jns_lap=' . $jnsLap . '&cari=' . $cariId;
                
                $value['url_add'] = Dispatcher::Instance()->GetUrl(
                    'kelompok_laporan', 
                    'inputKlpLaporan', 
                    'view', 
                    'html'
                ) . 
                '&process='.Dispatcher::Instance()->Encrypt('add') .
                '&dataId=' . $idEnc . '&jns_lap=' . $jnsLap . '&cari=' . $cariId;
                 
                $value['url_detail'] = Dispatcher::Instance()->GetUrl(
                    'kelompok_laporan', 
                    'InputDetilKlpLaporan', 'view', 'html'
                ) . '&dataId=' . $idEnc . '&jns_lap=' . $jnsLap . '&cari=' . $cariId;
                
                $value['url_add_summary'] = Dispatcher::Instance()->GetUrl(
                    'kelompok_laporan', 
                    'inputDetilKlpLapSummary', 
                    'view', 
                    'html'
                ) . 
                '&process='.Dispatcher::Instance()->Encrypt('add') .
                '&dataId=' . $idEnc . '&jns_lap=' . $jnsLap . '&cari=' . $cariId;
                
                $value['url_edit_summary'] = Dispatcher::Instance()->GetUrl(
                    'kelompok_laporan', 
                    'inputDetilKlpLapSummary', 
                    'view', 
                    'html'
                ) . '&dataId=' . $idEnc . '&jns_lap=' . $jnsLap . '&cari=' . $cariId;
                
                $value['url_hapus'] = Dispatcher::Instance()->GetUrl(
                    'confirm', 
                    'confirmDelete', 
                    'do', 
                    'html'
                ) . 
                '&urlDelete=' . $urlAccept . 
                '&urlReturn=' . $urlReturn . 
                '&id=' . $idEnc . 
                '&label=' . $label . 
                '&dataName=' . $value['nama'];
                
                //url set is tambah
      
                $value['url_set_is_tambah']  = Dispatcher::Instance()->GetUrl(
                   'kelompok_laporan',
                   'SetIsTambah',
                   'do',
                   'json'
                ) . '&dataId=' . $idEnc . '&jns_lap=' . $jnsLap . '&cari=' . $cariId;

                $value['url_unset_is_tambah']  = Dispatcher::Instance()->GetUrl(
                   'kelompok_laporan',
                   'UnsetIsTambah',
                   'do',
                   'json'
                ) . '&dataId=' . $idEnc . '&jns_lap=' . $jnsLap . '&cari=' . $cariId;
                //end
                
                if($value['pid'] == '2' && ($value['nama']=='Kas dan Setara Kas' || strtoupper($value['nama'])=='KAS DAN SETARA KAS')){
                    $title = '<b>' . $value['nama'] . '</b>';                        
                    $value['nama'] = $title;
                    $this->mrTemplate->AddVar('tombol', 'IS_PARENT', 'KAS_SETARA_KAS');
                    $this->mrTemplate->AddVar('tombol', 'KELLAP_URL_DETAIL', $value['url_detail']);                    
                } elseif($value['pid'] == '6' && ($value['nama']=='Aset Neto' || strtoupper($value['nama'])=='ASET NETO')){
                    $title = '<b>' . $value['nama'] . '</b>';                        
                    $value['nama'] = $title;
                    $this->mrTemplate->AddVar('tombol', 'IS_PARENT', 'ASET_NETO');
                    $this->mrTemplate->AddVar('tombol', 'KELLAP_URL_DETAIL', $value['url_detail']);                    
                } elseif($value['tipe'] ==='child'  && ($value['is_summary'] == 'T' || empty($value['is_summary']))) {
                    $this->mrTemplate->AddVar('tombol', 'IS_PARENT', 'NO');
                    if($value['is_tambah'] == 'T'){
                        $this->mrTemplate->AddVar('tombol', 'KELLAP_ICON_IS_TAMBAH', 'if_add_16_t.png');
                        $this->mrTemplate->AddVar('tombol', 'KELLAP_URL_IS_TAMBAH', $value['url_set_is_tambah']);                        
                    } else {
                        $this->mrTemplate->AddVar('tombol', 'KELLAP_ICON_IS_TAMBAH', 'if_sub_16_t.png');
                        $this->mrTemplate->AddVar('tombol', 'KELLAP_URL_IS_TAMBAH', $value['url_unset_is_tambah']);
                    }
                    $this->mrTemplate->AddVar('tombol', 'KELLAP_ID',$value['id']);
                    $this->mrTemplate->AddVar('tombol', 'KELLAP_URL_EDIT', $value['url_edit']);
                    $this->mrTemplate->AddVar('tombol', 'KELLAP_URL_DETAIL', $value['url_detail']);
                    $this->mrTemplate->AddVar('tombol', 'KELLAP_URL_HAPUS', $value['url_hapus']);
                    $this->mrTemplate->AddVar('tombol', 'KELLAP_URL_ADD', $value['url_add']);
                } elseif($value['tipe'] ==='child' && $value['is_summary'] == 'Y'){                    
                    $this->mrTemplate->AddVar('tombol', 'IS_PARENT', 'SUMMARY');
                    $this->mrTemplate->AddVar('tombol', 'KELLAP_URL_EDIT', $value['url_edit_summary']);
                    $this->mrTemplate->AddVar('tombol', 'KELLAP_URL_DETAIL', $value['url_detail']);
                    $this->mrTemplate->AddVar('tombol', 'KELLAP_URL_HAPUS', $value['url_hapus']);
                }else {
                    if($value['level'] == '1'){
                        $this->mrTemplate->AddVar('tombol', 'IS_PARENT', 'ROOT');
                    } else {
                        $this->mrTemplate->AddVar('tombol', 'IS_PARENT', 'YES');
                    }
                    if($value['is_tambah'] == 'T'){                        
                        $this->mrTemplate->AddVar('tombol', 'KELLAP_ICON_IS_TAMBAH', 'if_add_16_t.png'); 
                        $this->mrTemplate->AddVar('tombol', 'KELLAP_URL_IS_TAMBAH', $value['url_set_is_tambah']);                        
                    } else {
                        $this->mrTemplate->AddVar('tombol', 'KELLAP_ICON_IS_TAMBAH', 'if_sub_16_t.png');
                        $this->mrTemplate->AddVar('tombol', 'KELLAP_URL_IS_TAMBAH', $value['url_unset_is_tambah']);
                    }
                    $this->mrTemplate->AddVar('tombol', 'KELLAP_ID',$value['id']);
                    $this->mrTemplate->AddVar('tombol', 'KELLAP_URL_EDIT', $value['url_edit']);
                    $this->mrTemplate->AddVar('tombol', 'KELLAP_URL_ADD', $value['url_add']);
                    $this->mrTemplate->AddVar('tombol', 'KELLAP_URL_SUM', $value['url_add_summary']);
                }         
                
                if($value['is_tambah'] == 'Y' && ($value['is_summary'] == 'T' || empty($value['is_summary']))) {
                    $this->mrTemplate->AddVar('status_fungsi','STATUS_FUNGSI','MENAMBAH');
                } elseif($value['is_tambah'] == 'T' && ($value['is_summary'] == 'T' || empty($value['is_summary'])) ){
                    $this->mrTemplate->AddVar('status_fungsi','STATUS_FUNGSI','MENGURANG');
                } elseif( $value['is_summary'] == 'Y'){
                    $this->mrTemplate->AddVar('status_fungsi','STATUS_FUNGSI','SUMMARY');
                } else {
                    $this->mrTemplate->AddVar('status_fungsi','STATUS_FUNGSI','NONE');
                }
                
                if($value['is_summary'] == 'Y') {
                    $value['summary_style'] ='italic';
                    $value['summary_style_b'] ='bold';
                } else {
                    $value['summary_style'] ='normal';
                    $value['summary_style_b'] ='normal';
                }
                $this->mrTemplate->AddVars('data_item', $value, 'KELLAP_');
                $this->mrTemplate->parseTemplate('data_item', 'a');
            }
        }
    }
}


?>