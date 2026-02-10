<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/finansi_transaksi_penerimaan_bank/business/PopupRencanaPenerimaan.class.php';

require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/finansi_transaksi_penerimaan_bank/business/PopupDataLppa.class.php';

class ViewPopupRencanaPenerimaan extends HtmlResponse {

    protected $Pesan;
    protected $decUnitkerjaId;
    protected $encUnitkerjaId;

    protected $decKelRef;
    protected $encKelRef;

    public function TemplateModule() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') .
                'module/finansi_transaksi_penerimaan_bank/template');
        $this->SetTemplateFile('view_popup_rencana_penerimaan.html');
    }

    public function TemplateBase() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
        $this->SetTemplateFile('document-common-popup.html');
        $this->SetTemplateFile('layout-common-popup.html');
    }

    public function ProcessRequest() {
        $Obj = new PopupRencanaPenerimaan();
        $ObjLPPA = new PopupDataLppa();

        $arrPeriodeTahun  = $Obj->getPeriodeTahun();
        $periodeTahun     = $Obj->getPeriodeTahun(array('active' => true));

        $POST = $_POST->AsArray();
        $GET = $_GET->AsArray();
        if (!empty($POST)) {
            $kode = $POST['kode'];
            $nama = $POST['nama'];
            $tahunAnggaranId = $POST['ta_id'];
        } elseif (isset($GET['cari'])) {
            $kode = Dispatcher::Instance()->Decrypt($GET['kode']);
            $nama = Dispatcher::Instance()->Decrypt($GET['nama']);
            $tahunAnggaranId  = Dispatcher::Instance()->Decrypt($GET['ta_id']);
        } else {
            $kode = "";
            $nama = "";
            $tahunAnggaranId = $periodeTahun[0]['id'];
            
        }
        $this->decUnitkerjaId = Dispatcher::Instance()->Decrypt($GET['unit_kerja_id']);
        $this->encUnitkerjaId = Dispatcher::Instance()->Encrypt($this->decUnitkerjaId);
        $this->decKelRef = Dispatcher::Instance()->Decrypt($GET['kel_ref']);
        $this->encKelRef = Dispatcher::Instance()->Encrypt($this->decKelRef);
        
        $this->decTbankId = Dispatcher::Instance()->Decrypt($GET['tbank_id']);
        $this->encTbankId = Dispatcher::Instance()->Encrypt($this->decTbankId);

        $itemViewed = 20;
        $currPage = 1;
        $startRec = 0;
        if (isset($_GET['page'])) {
            $currPage = (string) $_GET['page']->StripHtmlTags()->SqlString()->Raw();
            $startRec = ($currPage - 1) * $itemViewed;
        }
         
        if($this->decKelRef === 'RPEN') {
            $data_ref = $Obj->getData($startRec, $itemViewed, $this->decUnitkerjaId,$tahunAnggaranId, $kode,$nama);
            $totalData = $Obj->GetCountData();
        } else {
            $data_ref = $ObjLPPA->getDataLppa($startRec, $itemViewed, $this->decUnitkerjaId,$tahunAnggaranId,$this->decTbankId, $kode,$nama); 
            $totalData = $ObjLPPA->getCountDataLppa();
        }
        
        $url = Dispatcher::Instance()->GetUrl(
            Dispatcher::Instance()->mModule, 
            Dispatcher::Instance()->mSubModule, 
            Dispatcher::Instance()->mAction, 
            Dispatcher::Instance()->mType . 
            '&unit_kerja_id=' . $this->encUnitkerjaId . 
            '&kel_ref=' . $this->encKelRef . 
            '&tbank_id=' . $this->encTbankId . 
            '&kode=' . Dispatcher::Instance()->Encrypt($kode) . 
            '&nama=' . Dispatcher::Instance()->Encrypt($nama) . 
            '&ta_id=' . Dispatcher::Instance()->Encrypt($tahunAnggaranId) . 
            '&cari=' . Dispatcher::Instance()->Encrypt(1));
        $dest = "popup-subcontent"; 
        Messenger::Instance()->SendToComponent(
            'paging', 
            'Paging', 
            'view', 
            'html', 
            'paging_top', 
            array($itemViewed, $totalData, $url, $currPage,$dest), 
            Messenger::CurrentRequest
        );

        
        # Combobox
        Messenger::Instance()->SendToComponent(
           'combobox',
           'Combobox',
           'view',
           'html',
           'periode_tahun',
           array(
              'ta_id',
              $arrPeriodeTahun,
              $tahunAnggaranId,
              false,
              'id="cmb_tahun_anggaran" style="width: 95px;"'
           ),
           Messenger::CurrentRequest
        );

        $msg = Messenger::Instance()->Receive(__FILE__);
        $this->Pesan = $msg[0][1];
        $this->css = $msg[0][2];

        $return['data_ref'] = $data_ref;
        $return['kel_ref'] = $this->decKelRef;
        $return['start'] = $startRec + 1;
        $return['search']['kode'] = $kode;
        $return['search']['nama'] = $nama;
        return $return;
    }

    public function ParseTemplate($data = NULL) {
        $search = $data['search'];
        $this->mrTemplate->AddVar('content', 'KODE', $search['kode']);
        $this->mrTemplate->AddVar('content', 'NAMA', $search['nama']);
        $this->mrTemplate->AddVar('kelompok_rpen_snama', 'NAMA', $search['nama']);
        $this->mrTemplate->AddVar('content', 'URL_SEARCH', 
                Dispatcher::Instance()->GetUrl(
                    'finansi_transaksi_penerimaan_bank', 
                    'popupRencanaPenerimaan', 
                    'view', 
                    'html') . 
                '&unit_kerja_id=' . $this->encUnitkerjaId .
                '&kel_ref=' . $this->encKelRef .  
                '&tbank_id=' . $this->encTbankId . 
                '&kode=' . Dispatcher::Instance()->Encrypt($search['kode']) . 
                '&nama=' . Dispatcher::Instance()->Encrypt($search['nama'])
        );
        
        if ($this->Pesan) {
            $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
            $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
            $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
        }
        
        if( $data['kel_ref'] === 'RPEN') {
            $this->mrTemplate->SetAttribute('kelompok_rpen', 'visibility', 'visible');
            $this->mrTemplate->SetAttribute('kelompok_rpen_title', 'visibility', 'visible');
            $this->mrTemplate->SetAttribute('kelompok_rpen_snama', 'visibility', 'visible');
            $this->mrTemplate->SetAttribute('kelompok_lppa', 'visibility', 'hidden');
            $this->mrTemplate->SetAttribute('kelompok_lppa_title', 'visibility', 'hidden');
            if (empty($data['data_ref'])) {
                $this->mrTemplate->AddVar('data_rpen', 'RPEN_EMPTY', 'YES');
            } else {
                $this->mrTemplate->AddVar('data_rpen', 'RPEN_EMPTY', 'NO');
                $data_rpen = $data['data_ref']; 
                for ($i = 0; $i < sizeof($data_rpen); $i++) {
                    $no = $i + $data['start'];
                    $data_rpen[$i]['number'] = $no;
                    if ($no % 2 != 0)
                        $data_rpen[$i]['class_name'] = 'table-common-even';
                    else
                        $data_rpen[$i]['class_name'] = '';

                    $data_rpen[$i]['nominal_aprove_hidden'] = ((int) $data_rpen[$i]['nominal_aprove'] - (int) $data_rpen[$i]['nominal_yg_sudah_dicairkan']);
                    if ($data_rpen[$i]['nominal_aprove_hidden'] < 0)
                        $data_rpen[$i]['nominal_aprove_hidden'] = 0;

                    $data_rpen[$i]['nominal_aprove'] = number_format($data_rpen[$i]['nominal_aprove'], 2, ',', '.');
                    $data_rpen[$i]['nominal_yg_bisa_dicairkan'] = number_format($data_rpen[$i]['sisa_pencairan'], 2, ',', '.');
                    $data_rpen[$i]['nominal_yg_sudah_dicairkan'] = number_format($data_rpen[$i]['nominal_yg_sudah_dicairkan'], 2, ',', '.');
                    $data_rpen[$i]['sisa'] = number_format($data_rpen[$i]['nominal_aprove_hidden'], 2, ',', '.');

                    if ($data_rpen[$i]['sisa'] > 0) {
                        $this->mrTemplate->SetAttribute('pilih', 'visibility', 'visible');
                    } else {
                        $this->mrTemplate->SetAttribute('pilih', 'visibility', 'hidden');
                    }

                    $this->mrTemplate->SetAttribute('content_description', 'visibility', 'visible');
                    $this->mrTemplate->AddVar('content_description', 'KETERANGAN', $data_rpen[$i]['keterangan']);
                    $this->mrTemplate->AddVar('cekbox', 'RPEN_DETIL_NAMA', "<b>".$data_rpen[$i]['nama']."</b>");

                    $this->mrTemplate->AddVars('data_rpen_item', $data_rpen[$i], 'RPEN_DETIL_');
                    $this->mrTemplate->AddVars('pilih', $data_rpen[$i], 'RPEN_DETIL_');
                    $this->mrTemplate->parseTemplate('data_rpen_item', 'a');
                }
            }
        } else {
            
            $this->mrTemplate->SetAttribute('kelompok_rpen', 'visibility', 'hidden');
            $this->mrTemplate->SetAttribute('kelompok_rpen_title', 'visibility', 'hidden');
            $this->mrTemplate->SetAttribute('kelompok_rpen_snama', 'visibility', 'hidden');
            $this->mrTemplate->SetAttribute('kelompok_lppa', 'visibility', 'visible');
            $this->mrTemplate->SetAttribute('kelompok_lppa_title', 'visibility', 'visible'); 

            if (empty($data['data_ref'])) {
                $this->mrTemplate->AddVar('data_lppa', 'LPPA_EMPTY', 'YES');
            } else {
                $this->mrTemplate->AddVar('data_lppa', 'LPPA_EMPTY', 'NO');
                $data_lppa = $data['data_ref'];   
                for ($i = 0; $i < sizeof($data_lppa); $i++) {
                    $no = $i + $data['start'];
                    $data_lppa[$i]['number'] = $no;
                    if ($no % 2 != 0)
                        $data_lppa[$i]['class_name'] = 'table-common-even';
                    else
                        $data_lppa[$i]['class_name'] = '';

                    $data_lppa[$i]['lppa_id'] = $data_lppa[$i]['lppaId'];
                    $data_lppa[$i]['nominal_sisa'] = $data_lppa[$i]['nominalRealisasiPencairan'] - $data_lppa[$i]['nominalSpjLppa']; 
 
                    $data_lppa[$i]['nominal_realisasi_pencairan'] = number_format($data_lppa[$i]['nominalRealisasiPencairan'], 2, ',', '.');
                    $data_lppa[$i]['nominal_lppa'] = number_format($data_lppa[$i]['nominalSpjLppa'], 2, ',', '.');
                    $data_lppa[$i]['nominal_sisa'] = number_format($data_lppa[$i]['nominal_sisa'], 2, ',', '.'); 
                    if ($data_lppa[$i]['isTercatat'] == 0) {
                        $this->mrTemplate->SetAttribute('lppa_pilih', 'visibility', 'visible');
                    } else {
                        $this->mrTemplate->SetAttribute('lppa_pilih', 'visibility', 'hidden');
                    }

                    $this->mrTemplate->SetAttribute('content_description', 'visibility', 'visible');
                    $this->mrTemplate->AddVar('content_description', 'KETERANGAN', $data_lppa[$i]['keterangan']);
                    $this->mrTemplate->AddVar('cekbox', 'RPEN_DETIL_NAMA', "<b>".$data_lppa[$i]['nama']."</b>");

                    $this->mrTemplate->AddVars('data_lppa_item', $data_lppa[$i], 'LPPA_DETIL_');
                    $this->mrTemplate->AddVars('lppa_pilih', $data_lppa[$i], 'LPPA_DETIL_');
                    $this->mrTemplate->parseTemplate('data_lppa_item', 'a');
                }
            }
                
        }
    }

}

?>