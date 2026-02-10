<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') . 
    'module/finansi_coa_jenis_biaya/business/CoaJenisBiaya.class.php';

require_once GTFWConfiguration::GetValue('application', 'docroot') . 
    'module/finansi_coa_jenis_biaya/business/DetilCoaJenisBiaya.class.php';

class ViewInputCoaJenisBiaya extends HtmlResponse {

    var $Data;
    var $Pesan;

    function TemplateModule() {
        $this->SetTemplateBasedir($this->mrConfig->mApplication['docroot'] . 'module/finansi_coa_jenis_biaya/template');
        $this->SetTemplateFile('input_coa_jenis_biaya.html');
    }

    function ProcessRequest() {
        $idDec = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
        $Obj = new CoaJenisBiaya();
        $msg = Messenger::Instance()->Receive(__FILE__);
        $this->Pesan = $msg[0][1];
        $this->Style = $msg[0][2];
        $this->Data = $msg[0][0];
        
        $dataCoaJenisBiaya = $Obj->GetDataById($idDec);
        $return['decDataId'] = $idDec;
        $return['dataCoaJenisBiaya'] = $dataCoaJenisBiaya;

        $listCoaJenisBiaya = $Obj->getCoaJenisBiaya();
        $return['listCoaJenisBiaya'] = $listCoaJenisBiaya;

        return $return;
    }

    function ParseTemplate($data = NULL) {
        if ($this->Pesan) {
            $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
            $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
            $this->mrTemplate->AddVar('warning_box', 'STYLE_PESAN', $this->Style);
        }
        $dataCoaJenisBiaya = $data['dataCoaJenisBiaya'];
        $listCoaJenisBiaya = $data['listCoaJenisBiaya'];

        if ($_REQUEST['dataId'] == '') {
            $url = "addCoaJenisBiaya";
            $tambah = "Tambah";
        } else {
            $url = "updateCoaJenisBiaya";
            $tambah = "Ubah";
        }
        
        $pemCoaDk = empty($dataCoaJenisBiaya[0]['jenis_biaya_pembayaran_coa_dk']) ? $this->Data['jenis_biaya_pembayaran_coa_dk'] : $dataCoaJenisBiaya[0]['jenis_biaya_pembayaran_coa_dk'];
        if ($pemCoaDk == 'K') {
            $this->mrTemplate->AddVar('content', 'IS_PEMBAYARAN_DEBET', '');
            $this->mrTemplate->AddVar('content', 'IS_PEMBAYARAN_KREDIT', 'checked="checked"');
        } else {
            $this->mrTemplate->AddVar('content', 'IS_PEMBAYARAN_DEBET', 'checked="checked"');
            $this->mrTemplate->AddVar('content', 'IS_PEMBAYARAN_KREDIT', '');
        }
        
        $potCoaDk = empty($dataCoaJenisBiaya[0]['jenis_biaya_potongan_coa_dk']) ? $this->Data['jenis_biaya_potongan_coa_dk'] : $dataCoaJenisBiaya[0]['jenis_biaya_potongan_coa_dk'];
        if ($potCoaDk == 'K') {
            $this->mrTemplate->AddVar('content', 'IS_POTONGAN_DEBET', '');
            $this->mrTemplate->AddVar('content', 'IS_POTONGAN_KREDIT', 'checked="checked"');
        } else {
            $this->mrTemplate->AddVar('content', 'IS_POTONGAN_DEBET', 'checked="checked"');
            $this->mrTemplate->AddVar('content', 'IS_POTONGAN_KREDIT', '');
        }
        
        $depCoaDk = empty($dataCoaJenisBiaya[0]['jenis_biaya_deposit_coa_dk']) ? $this->Data['jenis_biaya_deposit_coa_dk'] : $dataCoaJenisBiaya[0]['jenis_biaya_deposit_coa_dk'];        
        if ($depCoaDk == 'K') {
            $this->mrTemplate->AddVar('content', 'IS_DEPOSIT_DEBET', '');
            $this->mrTemplate->AddVar('content', 'IS_DEPOSIT_KREDIT', 'checked="checked"');
        } else {
            $this->mrTemplate->AddVar('content', 'IS_DEPOSIT_DEBET', 'checked="checked"');
            $this->mrTemplate->AddVar('content', 'IS_DEPOSIT_KREDIT', '');
        }
        
        $puCoaDk = empty($dataCoaJenisBiaya[0]['jenis_biaya_piutang_coa_dk']) ? $this->Data['jenis_biaya_piutang_coa_dk'] : $dataCoaJenisBiaya[0]['jenis_biaya_piutang_coa_dk'];        
        if ($puCoaDk == 'K') {
            $this->mrTemplate->AddVar('content', 'IS_PIUTANG_DEBET', '');
            $this->mrTemplate->AddVar('content', 'IS_PIUTANG_KREDIT', 'checked="checked"');
        } else {
            $this->mrTemplate->AddVar('content', 'IS_PIUTANG_DEBET', 'checked="checked"');
            $this->mrTemplate->AddVar('content', 'IS_PIUTANG_KREDIT', '');
        }

        $this->mrTemplate->AddVar('content', 'JUDUL', $tambah);
        $this->mrTemplate->AddVar('content', 'JENIS_BIAYA_ID', empty($dataCoaJenisBiaya[0]['jenis_biaya_id']) ? $this->Data['jenis_biaya_id'] : $dataCoaJenisBiaya[0]['jenis_biaya_id']);
        $this->mrTemplate->AddVar('content', 'JENIS_BIAYA_NAMA', empty($dataCoaJenisBiaya[0]['jenis_biaya_nama']) ? $this->Data['jenis_biaya_nama'] : $dataCoaJenisBiaya[0]['jenis_biaya_nama']);
        $this->mrTemplate->AddVar('content', 'JENIS_BIAYA_PEMBAYARAN_COA_ID', empty($dataCoaJenisBiaya[0]['jenis_biaya_pembayaran_coa_id']) ? $this->Data['jenis_biaya_pembayaran_coa_id'] : $dataCoaJenisBiaya[0]['jenis_biaya_pembayaran_coa_id']);
        $this->mrTemplate->AddVar('content', 'JENIS_BIAYA_PEMBAYARAN_COA_NAMA', empty($dataCoaJenisBiaya[0]['jenis_biaya_pembayaran_coa_nama']) ? $this->Data['jenis_biaya_pembayaran_coa_nama'] : $dataCoaJenisBiaya[0]['jenis_biaya_pembayaran_coa_nama']);
        
        $this->mrTemplate->AddVar('content', 'JENIS_BIAYA_POTONGAN_COA_ID', empty($dataCoaJenisBiaya[0]['jenis_biaya_potongan_coa_id']) ? $this->Data['jenis_biaya_potongan_coa_id'] : $dataCoaJenisBiaya[0]['jenis_biaya_potongan_coa_id']);
        $this->mrTemplate->AddVar('content', 'JENIS_BIAYA_POTONGAN_COA_NAMA', empty($dataCoaJenisBiaya[0]['jenis_biaya_potongan_coa_nama']) ? $this->Data['jenis_biaya_potongan_coa_nama'] : $dataCoaJenisBiaya[0]['jenis_biaya_potongan_coa_nama']);
        
        $this->mrTemplate->AddVar('content', 'JENIS_BIAYA_DEPOSIT_COA_ID', empty($dataCoaJenisBiaya[0]['jenis_biaya_deposit_coa_id']) ? $this->Data['jenis_biaya_deposit_coa_id'] : $dataCoaJenisBiaya[0]['jenis_biaya_deposit_coa_id']);
        $this->mrTemplate->AddVar('content', 'JENIS_BIAYA_DEPOSIT_COA_NAMA', empty($dataCoaJenisBiaya[0]['jenis_biaya_deposit_coa_nama']) ? $this->Data['jenis_biaya_deposit_coa_nama'] : $dataCoaJenisBiaya[0]['jenis_biaya_deposit_coa_nama']);
        
        $this->mrTemplate->AddVar('content', 'JENIS_BIAYA_PIUTANG_COA_ID', empty($dataCoaJenisBiaya[0]['jenis_biaya_piutang_coa_id']) ? $this->Data['jenis_biaya_piutang_coa_id'] : $dataCoaJenisBiaya[0]['jenis_biaya_piutang_coa_id']);
        $this->mrTemplate->AddVar('content', 'JENIS_BIAYA_PIUTANG_COA_NAMA', empty($dataCoaJenisBiaya[0]['jenis_biaya_piutang_coa_nama']) ? $this->Data['jenis_biaya_piutang_coa_nama'] : $dataCoaJenisBiaya[0]['jenis_biaya_piutang_coa_nama']);
        

        $this->mrTemplate->AddVar('content', 'URL_ACTION', Dispatcher::Instance()->GetUrl('finansi_coa_jenis_biaya', $url, 'do', 'html') . "&dataId=" . Dispatcher::Instance()->Encrypt($data['decDataId']));
        $this->mrTemplate->AddVar('content', 'URL_SEARCH', Dispatcher::Instance()->GetUrl('finansi_coa_jenis_biaya', 'coa', 'popup', 'html') . '&data[name]=' . $this->data['nama'] . '&tipe=' . $data['tipe']);
        $this->mrTemplate->AddVar('content', 'URL_POPUP_COA_PEMBAYARAN', 
                Dispatcher::Instance()->GetUrl('finansi_coa_jenis_biaya', 'coa', 'popup', 'html')
                . '&st=pembayaran'
        );

        $this->mrTemplate->AddVar('content', 'URL_POPUP_COA_POTONGAN', 
                Dispatcher::Instance()->GetUrl('finansi_coa_jenis_biaya', 'coa', 'popup', 'html')
                . '&st=potongan'
        );

        $this->mrTemplate->AddVar('content', 'URL_POPUP_COA_DEPOSIT', 
                Dispatcher::Instance()->GetUrl('finansi_coa_jenis_biaya', 'coa', 'popup', 'html')
                . '&st=deposit'
        );        
        $this->mrTemplate->AddVar('content', 'URL_POPUP_COA_PIUTANG', 
                Dispatcher::Instance()->GetUrl('finansi_coa_jenis_biaya', 'coa', 'popup', 'html')
                . '&st=piutang'
        );
        $this->mrTemplate->AddVar('content', 'URL_POPUP_JB', Dispatcher::Instance()->GetUrl('finansi_coa_jenis_biaya', 'PopupJenisBiaya', 'view', 'html'));

        $this->mrTemplate->AddVar('content', 'DATAID', Dispatcher::Instance()->Decrypt($_GET['dataId']));
        $this->mrTemplate->AddVar('content', 'PAGE', Dispatcher::Instance()->Decrypt($_GET['page']));



        // tes detil
        if (empty($listCoaJenisBiaya)) {
            $this->mrTemplate->AddVar('data', 'DATA_EMPTY', 'YES');
        } else {
            $this->mrTemplate->AddVar('data', 'DATA_EMPTY', 'NO');
            for ($i = 0; $i < sizeof($listCoaJenisBiaya); $i++) {
                $no = $i + $data['start'] + 1;
                $listCoaJenisBiaya[$i]['jb_nomor'] = $no;
                if ($no % 2 != 0) {
                    $listCoaJenisBiaya[$i]['jb_class_name'] = 'table-common-even';
                } else {
                    $listCoaJenisBiaya[$i]['jb_class_name'] = '';
                }

                if ($i == 0) {
                    $this->mrTemplate->AddVar('content', 'FIRST_NUMBER', $no);
                }
                if ($i == sizeof($listCoaJenisBiaya) - 1) {
                    $this->mrTemplate->AddVar('content', 'LAST_NUMBER', $no);
                }
                if ($listCoaJenisBiaya[$i]['jb_pembayaran_dk'] == 'D') {
                    $listCoaJenisBiaya[$i]['jb_pembayaran_debet_kredit'] = "debet";
                } else {
                    $listCoaJenisBiaya[$i]['jb_pembayaran_debet_kredit'] = "kredit";
                }
                if ($listCoaJenisBiaya[$i]['jb_potongan_dk'] == 'D') {
                    $listCoaJenisBiaya[$i]['jb_potongan_debet_kredit'] = "debet";
                } else {
                    $listCoaJenisBiaya[$i]['jb_potongan_debet_kredit'] = "kredit";
                }
                if ($listCoaJenisBiaya[$i]['jb_deposit_dk'] == 'D') {
                    $listCoaJenisBiaya[$i]['jb_deposit_debet_kredit'] = "debet";
                } else {
                    $listCoaJenisBiaya[$i]['jb_deposit_debet_kredit'] = "kredit";
                }
                if ($listCoaJenisBiaya[$i]['jb_piutang_dk'] == 'D') {
                    $listCoaJenisBiaya[$i]['jb_piutang_debet_kredit'] = "debet";
                } else {
                    $listCoaJenisBiaya[$i]['jb_piutang_debet_kredit'] = "kredit";
                }
                //
                $idDelete   = Dispatcher::Instance()->Encrypt($listCoaJenisBiaya[$i]['id']);
                $urlAccept  = 'finansi_coa_jenis_biaya|DeleteCoaJenisBiaya|do|html';
                $urlReturn  = 'finansi_coa_jenis_biaya|InputCoaJenisBiaya|view|html';
                $label      = 'Hapus Coa Jenis Biaya';
                $dataName   = 'Jenis Biaya : '.$listCoaJenisBiaya[$i]['jb_nama'].' <br />';
                $dataName   .= 'Coa Pengakuan : '.$listCoaJenisBiaya[$i]['jb_pembayaran_coa_nama'].' <br />';
                $dataName   .= 'Coa Piutang : '.$listCoaJenisBiaya[$i]['jb_piutang_coa_nama'];
                //
                $listCoaJenisBiaya[$i]['jb_url_delete'] = Dispatcher::Instance()->GetUrl(
                        'confirm', 
                        'confirmDelete', 
                        'do', 
                        'html'
                    ) .
                    '&urlDelete=' . $urlAccept . 
                    '&urlReturn=' . $urlReturn . 
                    '&id=' . $idDelete . 
                    '&label=' . $label . 
                    '&dataName=' . $dataName;
                
                $listCoaJenisBiaya[$i]['jb_url_edit'] = Dispatcher::Instance()->GetUrl(
                    'finansi_coa_jenis_biaya', 'inputCoaJenisBiaya', 'view', 'html') . '&dataId=' . $listCoaJenisBiaya[$i]['id'];

                $this->mrTemplate->AddVars('data_item', $listCoaJenisBiaya[$i], '');
                $this->mrTemplate->parseTemplate('data_item', 'a');
            }
        }
    }

}

?>