<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/coa/business/Coa.class.php';

class ProsessCoa {

    var $POST;
    var $Pesan = array();
    var $CoaTipeRef;

    var $ObjCoa;
    
    function __construct() {
        $this->ObjCoa = new Coa();
    }

    function SetPost($param) {
        $this->POST = $param->AsArray();
    }

    function TipeCrashCheck() {
        $this->CoaTipeRef = $this->ObjCoa->GetCoaTipeRefByArrayCrashId($this->POST['tipe_coa_id']);
        $is_crash = false;

        if (!empty($this->CoaTipeRef)) {

            for ($i = 0; $i < sizeof($this->CoaTipeRef); $i++) {

                if (in_array($this->CoaTipeRef[$i]['id'], $this->POST['tipe_coa_id'])) {

                    //crash
                    $is_crash = true;
                    $this->Pesan[$i] = "Tipe " . $this->POST['tipe_coa_name'][$this->CoaTipeRef[$i]['crash_id']] . " dan " . $this->CoaTipeRef[$i]['name'] . " tidak boleh digunakan bersamaan";

                    //return false;
                }
            }

            if ($is_crash == true) {
                return false;
            }
        }

        return true;
    }

    function TipeIndukCrashCheck() {
        $arr_induk = $this->ObjCoa->GetCoaTipeCoaByCoaId($this->POST['induk']);
        $induk = null;
        for ($i = 0; $i < sizeof($arr_induk); $i++) {
            $induk['tipe'][$i] = $arr_induk[$i]['tipe_coa_id'];
            $induk['nama'][$i] = $arr_induk[$i]['nama_tipe'];
        }

        if (!empty($this->CoaTipeRef)) {

            for ($i = 0; $i < sizeof($this->CoaTipeRef); $i++) {

                if (in_array($this->CoaTipeRef[$i]['id'], $induk['tipe'])) {
                    $is_crash = true;

                    //$ctr = $this->ObjCoa->GetCoaTipeRefById($this->CoaTipeRef[$i]['id']);
                    $this->Pesan[$i] = "Tipe yang Anda pilih bertentangan dengan Tipe Induk (" . $this->CoaTipeRef[$i]['name'] . ")";

                    //return false;
                }
            }

            if ($is_crash == true) {
                return false;
            }
        }

        return true;
    }

    function AddCoa() {
        if (!empty($this->POST['induk'])) {
            $inp = explode("-", $this->POST['induk']);
            $parent = $inp['0'];
            $level = $inp['1'] + 1;
        } else {
            $parent = 0;
            $level = 1;
        }
        $userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());

        if (!empty($this->POST['induk'])) {
            $inp = explode("-", $this->POST['induk']);
            $parent = $inp['0'];
            $level = $inp['1'] + 1;
        } else {
            $parent = 0;
            $level = 1;
        }

        $kodeSistem = $this->ObjCoa->GetGenerateKodeSistem($parent);

        $vLr = (int) $this->POST['coa_is_laba_rugi'];
        $vLrAt = (int) $this->POST['coa_is_laba_rugi_at'];
        $vDm = (int) $this->POST['coa_is_deposit_masuk'];
        //end of check is crash
        $params = array(
            $kodeSistem,
            str_replace("'", "", $this->POST['kode_akun']),
            str_replace("'", "", $this->POST['nama_akun']),
            $level,
            $parent,
            $this->POST['coa_is_debet'],
            $this->POST['coa_is_kas'],
            $vLr,
            $vLrAt,
            $vDm,
            $userId,
            Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId(),
            $parent
        );
      
        $gnumLR = $this->ObjCoa->GetNumCoaLR($vLr);
        $gnumLRAT = $this->ObjCoa->GetNumCoaLRAT($vLrAt);
        $gnumDM = $this->ObjCoa->GetNumCoaDepMasuk($vDm);
        #var_dump($gnumLRAT);
        #var_dump($gnumLR);
        if (($vLr == $vLrAt) && ($vLr == 1) && ($vLrAt == 1)) {
            return 'coa_lr_at_same';
        } elseif ($vLr == 1 && $gnumLR > 0) {
            return 'coa_lr_exist';
        } elseif ($vLrAt == 1 && $gnumLRAT > 0) {
            return 'coa_lr_at_exist';
        } elseif ($vDm == 1 && $gnumDM > 0) {
            return 'coa_dm_exist';
        } else {
            if (($this->POST['kode_akun'] != '') && ($this->POST['nama_akun'] != '')) {
                return $this->ObjCoa->InsertCoa($params, $this->POST['tipe_coa_id']);
            } else {
                return 'coa_kode_akun';
            }
        }
    }

    function UpdateCoa() {
        
        if (!empty($this->POST['induk'])) {
            $inp = explode("-", $this->POST['induk']);
            $parent = $inp['0'];
            $level = $inp['1'] + 1;
        } else {
            $parent = 0;
            $level = 1;
        }

        $userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());

        $kodeSistem = $this->ObjCoa->GetGenerateKodeSistem($parent);

        $vLr = (int) $this->POST['coa_is_laba_rugi'];
        $vLrAt = (int) $this->POST['coa_is_laba_rugi_at'];
        $vDm = (int) $this->POST['coa_is_deposit_masuk'];
        //end of check is crash        
        //end of check is crash
        $params = array(
            $kodeSistem,
            str_replace("'", "", $this->POST['kode_akun']),
            str_replace("'", "", $this->POST['nama_akun']),
            $level,
            $parent,
            $this->POST['coa_is_debet'],
            $this->POST['coa_is_kas'],
            $vLr,
            $vLrAt,
            $vDm,
            $userId,
            $parent,
            $this->POST['coa_id']
        );
        // cek coa lr / coa lr at
        $gnumLR = $this->ObjCoa->GetNumCoaLR($vLr);
        $gnumLRAT = $this->ObjCoa->GetNumCoaLRAT($vLrAt);
        $gnumDM = $this->ObjCoa->GetNumCoaDepositMasuk();
        $gCDM = $this->ObjCoa->GetCoaDepositMasuk();
        
        if (($vLr == $vLrAt) && ($vLr == 1) && ($vLrAt == 1)) {
            return 'coa_lr_at_same';
        } elseif ($vLr == 1 && $gnumLR > 0) {
            return 'coa_lr_exist';
        } elseif ($vLrAt == 1 && $gnumLRAT > 0) {
            return 'coa_lr_at_exist';
        } elseif ($vDm == 1 && $gnumDM > 0 && ($gCDM['c_id'] != $this->POST['coa_id'])) {
            return 'coa_dm_exist';
        } else {
            if (($this->POST['kode_akun'] != '') && ($this->POST['nama_akun'] != '')) {
                return $this->ObjCoa->UpdateCoa($params, $this->POST['tipe_coa_id']);
            } else {
                return 'coa_kode_akun';
            }
        }
    }

    function InputCoa() {
        $tmp = ($this->POST['op'] == 'edit') ? '&coaid=' . $this->POST['coa_id'] : '';
        
        if ((isset($this->POST['btnsimpan'])) && ($this->POST['op'] == 'add')) {
            $rs_add = $this->AddCoa();

            if ($rs_add === true) {
                Messenger::Instance()->Send('coa', 'Coa', 'view', 'html', array(
                    $this->POST,
                    'Penambahan data berhasil'
                        ), Messenger::NextRequest);
                $urlRedirect = Dispatcher::Instance()->GetUrl('coa', 'Coa', 'view', 'html');
            } elseif ($rs_add == "tipe_is_crash") {
                $msg = implode("<br />", $this->Pesan);
                Messenger::Instance()->Send('coa', 'inputCoa', 'view', 'html', array(
                    $this->POST,
                    $msg
                        ), Messenger::NextRequest);
                $urlRedirect = Dispatcher::Instance()->GetUrl('coa', 'InputCoa', 'view', 'html');
            } elseif ($rs_add == "tipe_induk_is_crash") {
                $msg = implode("<br />", $this->Pesan);
                Messenger::Instance()->Send('coa', 'inputCoa', 'view', 'html', array(
                    $this->POST,
                    $msg
                        ), Messenger::NextRequest);
                $urlRedirect = Dispatcher::Instance()->GetUrl('coa', 'InputCoa', 'view', 'html');
            } elseif ($rs_add == 'coa_dm_exist') {

                Messenger::Instance()->Send('coa', 'InputCoa', 'view', 'html', array(
                    $this->POST,
                    'Coa Untuk Deposit Masuk sudah ada'
                        ), Messenger::NextRequest);
                $urlRedirect = Dispatcher::Instance()->GetUrl('coa', 'InputCoa', 'view', 'html');
            } elseif ($rs_add == 'coa_lr_exist') {

                Messenger::Instance()->Send('coa', 'InputCoa', 'view', 'html', array(
                    $this->POST,
                    'Coa Untuk Laba Rugi Tahun Berjalan sudah ada'
                        ), Messenger::NextRequest);
                $urlRedirect = Dispatcher::Instance()->GetUrl('coa', 'InputCoa', 'view', 'html');
            } elseif ($rs_add == 'coa_lr_at_exist') {

                Messenger::Instance()->Send('coa', 'InputCoa', 'view', 'html', array(
                    $this->POST,
                    'Coa Untuk Laba Rugi Awal Tahun sudah ada'
                        ), Messenger::NextRequest);
                $urlRedirect = Dispatcher::Instance()->GetUrl('coa', 'InputCoa', 'view', 'html');
            } elseif ($rs_add == 'coa_lr_at_same') {

                Messenger::Instance()->Send('coa', 'InputCoa', 'view', 'html', array(
                    $this->POST,
                    'Tipe Coa hanya boleh satu pilihan'
                        ), Messenger::NextRequest);
                $urlRedirect = Dispatcher::Instance()->GetUrl('coa', 'InputCoa', 'view', 'html');
            } elseif ($rs_add == 'coa_kode_akun') {

                Messenger::Instance()->Send('coa', 'InputCoa', 'view', 'html', array(
                    $this->POST,
                    'Kode Akun dan Nama Rekening harus diisi'
                        ), Messenger::NextRequest);
                $urlRedirect = Dispatcher::Instance()->GetUrl('coa', 'InputCoa', 'view', 'html');
            } else {
                Messenger::Instance()->Send('coa', 'inputCoa', 'view', 'html', array(
                    $this->POST,
                    'Gagal Menyimpan Data '
                        ), Messenger::NextRequest);
                $urlRedirect = Dispatcher::Instance()->GetUrl('coa', 'InputCoa', 'view', 'html');
            }
            //$urlRedirect = Dispatcher::Instance()->GetUrl('coa', 'InputCoa', 'view', 'html');
        } else
        if ((isset($this->POST['btnsimpan'])) && ($this->POST['op'] == 'edit')) {
            $rs_update = $this->UpdateCoa();

            if ($rs_update === true) {

                if ($_POST['smpn'] != 'drlist') {
                    Messenger::Instance()->Send('coa', 'Coa', 'view', 'html', array(
                        $this->POST,
                        'Perubahan data berhasil'
                            ), Messenger::NextRequest);
                    $urlRedirect = Dispatcher::Instance()->GetUrl('coa', 'Coa', 'view', 'html') . $tmp;
                } else {
                    Messenger::Instance()->Send('coa', 'ListCoa', 'view', 'html', array(
                        $this->POST,
                        'Perubahan data berhasil'
                            ), Messenger::NextRequest);
                    $urlRedirect = Dispatcher::Instance()->GetUrl('coa', 'ListCoa', 'view', 'html');
                }
            } elseif ($rs_update == "tipe_is_crash") {
                $msg = implode("<br />", $this->Pesan);
                Messenger::Instance()->Send('coa', 'inputCoa', 'view', 'html', array(
                    $this->POST,
                    $msg
                        ), Messenger::NextRequest);
                $urlRedirect = Dispatcher::Instance()->GetUrl('coa', 'InputCoa', 'view', 'html');
            } elseif ($rs_update == "tipe_induk_is_crash") {
                $msg = implode("<br />", $this->Pesan);
                Messenger::Instance()->Send('coa', 'inputCoa', 'view', 'html', array(
                    $this->POST,
                    $msg
                        ), Messenger::NextRequest);
                $urlRedirect = Dispatcher::Instance()->GetUrl('coa', 'InputCoa', 'view', 'html');
            }elseif ($rs_update == 'coa_dm_exist') {
                $gCDM = $this->ObjCoa->GetCoaDepositMasuk();
                Messenger::Instance()->Send('coa', 'InputCoa', 'view', 'html', array(
                    $this->POST,
                    'Coa Untuk Deposit Masuk sudah ada ('.$gCDM['c_kode'].' - '.$gCDM['c_nama'].')'
                        ), Messenger::NextRequest);
                $urlRedirect = Dispatcher::Instance()->GetUrl('coa', 'InputCoa', 'view', 'html');
            }  elseif ($rs_update == 'coa_lr_exist') {

                Messenger::Instance()->Send('coa', 'InputCoa', 'view', 'html', array(
                    $this->POST,
                    'Coa Untuk Laba Rugi Tahun Berjalan sudah ada'
                        ), Messenger::NextRequest);
                $urlRedirect = Dispatcher::Instance()->GetUrl('coa', 'InputCoa', 'view', 'html');
            } elseif ($rs_update == 'coa_lr_at_exist') {

                Messenger::Instance()->Send('coa', 'InputCoa', 'view', 'html', array(
                    $this->POST,
                    'Coa Untuk Laba Rugi Awal Tahun sudah ada'
                        ), Messenger::NextRequest);
                $urlRedirect = Dispatcher::Instance()->GetUrl('coa', 'InputCoa', 'view', 'html');
            } elseif ($rs_update == 'coa_lr_at_same') {

                Messenger::Instance()->Send('coa', 'InputCoa', 'view', 'html', array(
                    $this->POST,
                    'Tipe Coa hanya boleh satu pilihan'
                        ), Messenger::NextRequest);
                $urlRedirect = Dispatcher::Instance()->GetUrl('coa', 'InputCoa', 'view', 'html');
            } elseif ($rs_update == 'coa_kode_akun') {

                Messenger::Instance()->Send('coa', 'InputCoa', 'view', 'html', array(
                    $this->POST,
                    'Kode Akun dan Nama Rekening harus diisi'
                        ), Messenger::NextRequest);
                $urlRedirect = Dispatcher::Instance()->GetUrl('coa', 'InputCoa', 'view', 'html');
            } else {
                Messenger::Instance()->Send('coa', 'InputCoa', 'view', 'html', array(
                    $this->POST,
                    'Gagal Update Data'
                        ), Messenger::NextRequest);
                $urlRedirect = Dispatcher::Instance()->GetUrl('coa', 'InputCoa', 'view', 'html');
            }
        } else {

            if ($_POST['smpn'] != 'drlist') {
                $urlRedirect = Dispatcher::Instance()->GetUrl('coa', 'Coa', 'view', 'html') . $tmp;
            } else {
                $urlRedirect = Dispatcher::Instance()->GetUrl('coa', 'ListCoa', 'view', 'html');
            }
        }

        return $urlRedirect;
    }

}

?>