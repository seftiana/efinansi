<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/coa/business/Coa.class.php';
//require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/upd/business/Upd.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/coa_tipe/business/CoaTipe.class.php';

class ViewInputCoa extends HtmlResponse {

    var $Data;
    var $Pesan;
    var $Op;

    function TemplateModule() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') .
                'module/coa/template');
        $this->SetTemplateFile('input_coa.html');
    }

    function PrepareData() {
        // print_r($_GET['smpn']);
        //get data dari detail data atau dari form
        if ($_GET['coaid'] != '') {
            $coaId = $_GET['coaid'];
            $ObjCoa = new Coa();
            $rs = $ObjCoa->GetCoaFromId($coaId);
            for ($i = 0; $i < sizeof($rs); $i++) {
                $tipe[] = $rs[$i]['coatipecoaCtrId'];
            }
            $this->Data = array(
                $rs[0]['coaId'], //0
                $rs[0]['coaCtrId'],//1
                $rs[0]['coaUpdId'] . '-' . $rs[0]['updCoaPrecode'], //2
                $rs[0]['coaKodeAkun'], //3
                $rs[0]['coaNamaAkun'], //4
                $rs[0]['coaLevelAkun'], //5
                $rs[0]['coaParentAkun'] . '-' . ($rs[0]['coaLevelAkun'] - 1), //6
                $rs[0]['coaIsDebetPositif'], //7 ( saldo normal)
                $rs[0]['coaIsKas'], //8
                $rs[0]['coaIsLocked'], //9
                $rs[0]['coaIsLabaRugiThJln'],//10
                $rs[0]['coaIsLabaRugiThAwal'],//11
                $tipe,//12,
                $rs[0]['coaIsDepMasuk'],//13
            );
        } else {
            $msg = Messenger::Instance()->Receive(__FILE__);
            $post = $msg[0][0];
            $this->Pesan = $msg[0][1];
            $this->Op = $post['op'];
            $this->Data = array(
                $post['coa_id'], //0
                $post['tipe_coa'], //1
                $post['satuan_akuntansi'],//2 
                $post['kode_akun'], //3
                $post['nama_akun'], //4
                '', //5
                $post['induk'], //6
                $post['coa_is_debet'], //7
                $post['coa_is_kas'], //8
                '', //9
                $post['coa_is_laba_rugi'],//10
                $post['coa_is_laba_rugi_at'],//11
                $post['tipe_coa_id'],//12
                $post['coa_is_deposit_masuk'],//13
            );
        }
    }

    function ProcessRequest() {
        ini_set(max_execution_time, 0);
        //get data detail data
        $this->PrepareData();

        //set combo upd
        /*
        $Coa = new Coa();
       
          $ret_unitkerja = $Coa->GetComboUnitKerja();
          Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 'unitkerjaid',
          array('unitkerjaid',$ret_unitkerja,$this->Data[2],'','style="width:200px"'), Messenger::CurrentRequest);
         */
        //set combo induk
        $ObjCoa = new Coa();
        $ret_coa = $ObjCoa->GetComboCoa();
        Messenger::Instance()->SendToComponent(
                'combobox', 
                'Combobox', 
                'view', 
                'html', 
                'induk', 
                array(
                    'induk', 
                    $ret_coa, 
                    $this->Data[6], 
                    'false', 
                    ' style="width:200px;"'), 
                    Messenger::CurrentRequest
                );

        //set combo tipe coa
        $ObjCoaTipe = new CoaTipe();
        $return['combo_tipe_coa'] = $ObjCoaTipe->GetComboCoaTipe();
        //11
        //Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 'tipe_coa',
        //   array('tipe_coa',$ret_coa_tipe,$this->Data[1],'',''), Messenger::CurrentRequest);

        return $return;
    }

    function ParseTemplate($data = NULL) {
        if (isset($this->Pesan)) {
            $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
            $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
        }

        //set aksi input
        $this->mrTemplate->AddVar('content', 'URL_ACTION', Dispatcher::Instance()->GetUrl('coa', 'InputCoa', 'do', 'html'));

        if (($_REQUEST['op'] == 'add') || ($this->Op == 'add')) {
            $this->mrTemplate->AddVar('content', 'OPERASI', 'add');
            $tambah = "Tambah";
        } else {
            $this->mrTemplate->AddVar('content', 'OPERASI', 'edit');
            $tambah = "Ubah";
        }
        //set title
        $this->mrTemplate->AddVar('content', 'JUDUL', $tambah);
        $this->mrTemplate->AddVar('content', 'ID', $this->Data[0]);
        $this->mrTemplate->AddVar('content', 'KODE_AKUN', $this->Data[3]);
        $this->mrTemplate->AddVar('content', 'NAMA_AKUN', $this->Data[4]);
        $this->mrTemplate->AddVar('content', 'SMPN', $_GET['smpn']);
        if ($this->Data[7] == '0') {
            $this->mrTemplate->AddVar('content', 'SALDO_NORMAL_0_CHEKED', "checked='checked'");
        } else {
            $this->mrTemplate->AddVar('content', 'SALDO_NORMAL_1_CHEKED', "checked='checked'");
        }

        if ($this->Data[8] == '1') {
            $this->mrTemplate->AddVar('content', 'KAS_1_CHEKED', "checked='checked'");
        } else {
            $this->mrTemplate->AddVar('content', 'KAS_0_CHEKED', "checked='checked'");
        }

        if ($this->Data[10] == '1') {
            $this->mrTemplate->AddVar('content', 'LABA_RUGI_CHEKED', "checked='checked'");
        } else {
            $this->mrTemplate->AddVar('content', 'LABA_RUGI_CHEKED', "");
        }

        if ($this->Data[11] == '1') {
            $this->mrTemplate->AddVar('content', 'LABA_RUGI_AT_CHEKED', "checked='checked'");
        } else {
            $this->mrTemplate->AddVar('content', 'LABA_RUGI_AT_CHEKED', "");
        }
        
        if ($this->Data[13] == '1') {
            $this->mrTemplate->AddVar('content', 'DEPOSIT_MASUK_1_CHEKED', "checked='checked'");
        } else {
            $this->mrTemplate->AddVar('content', 'DEPOSIT_MASUK_0_CHEKED',  "checked='checked'");
        }
        /*
        if (empty($data['combo_tipe_coa'])) {
            $this->mrTemplate->AddVar('tipe_coa', 'TIPE_COA_EMPTY', 'YES');
        } else {
            $this->mrTemplate->AddVar('tipe_coa', 'TIPE_COA_EMPTY', 'NO');

            for ($i = 0; $i < sizeof($data['combo_tipe_coa']); $i++) {
                $data['combo_tipe_coa'][$i]['name'] = str_replace("_", " ", $data['combo_tipe_coa'][$i]['name']);
                if (!empty($this->Data[11]) && in_array($data['combo_tipe_coa'][$i]['id'], $this->Data[12])) {
                    $data['combo_tipe_coa'][$i]['checked'] = "checked=\"checked\"";
                } else {
                    $data['combo_tipe_coa'][$i]['checked'] = "";
                }
                $this->mrTemplate->AddVars('tipe_coa_item', $data['combo_tipe_coa'][$i], 'TIPE_COA_');
                $this->mrTemplate->parseTemplate('tipe_coa_item', 'a');
            }
        }*/
    }

}

?>