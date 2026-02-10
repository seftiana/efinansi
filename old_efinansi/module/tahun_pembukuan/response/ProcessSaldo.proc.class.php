<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/tahun_pembukuan/business/TahunPembukuan.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/tahun_pembukuan/business/TahunPembukuanPeriode.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/tahun_pembukuan/business/BukuBesar.class.php';

class ProcessSaldo {

    var $POST;
    protected $msg;

    # subaccount
    public $jmlSubAcc; #jml subaccount yang digunakan
    var $defaultSubacc;

    function ProcessSaldo() {
        $this->subAccName = array('Pertama', 'Kedua', 'Ketiga', 'Keempat', 'Kelima', 'Keenam', 'Ketujuh');
        $this->jmlSubAcc = ((GTFWConfiguration::GetValue('application', 'subAccJml') == NULL) ? 7 : GTFWConfiguration::GetValue('application', 'subAccJml'));
        
        $this->defaultSubacc = str_replace('9', '0', GTFWConfiguration::GetValue('application', 'subAccFormat'));
    }

    function SetPost($param) {
        $this->POST = $param->AsArray();
    }

    function AddSaldoAkhir() {
        $objThn = new TahunPembukuan();
        $objBuku = new BukuBesar();

        $objThn->StartTrans();
        $objBuku->StartTrans();

        $coaId = $this->POST['coa_id'];
        $subAcc = $this->POST['sub_account'];
        $userId = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
        //$nominal = (float) $this->POST['nominal'];
        $debet   = (float) $this->POST['debet'];
        $kredit  = (float) $this->POST['kredit'];
        $tanggal = $this->POST['tanggal_year'] . "-" . $this->POST['tanggal_mon'] . "-" . $this->POST['tanggal_day'];

        //trap
        $pengali = $objBuku->GetPengali($coaId);
        /*
        if ($this->POST['is_debet_positif'] == '1') {
            $nominal *= $pengali['pengaliDebet'];
            $debet = $nominal;
            $kredit = 0;
        } else {
            $nominal *= $pengali['pengaliKredit'];
            $kredit = $nominal;
            $debet = 0;
        }
        */
        
        $trapSuccess = true;
        if ($debet == 0) {
            $saldo_akhir = $kredit * $pengali['pengaliKredit'];
        } elseif ($kredit == 0) {
            $saldo_akhir = $debet * $pengali['pengaliDebet'];
        } else {
            $trapSuccess = false;
        }

        $isBukuBesarInsert = $objBuku->GetBukuBesarFromCoa($coaId, $subAcc);
        if (empty($isBukuBesarInsert)) {
            $param_insert_buku_besar = array($tanggal, $coaId, $debet, $kredit, $saldo_akhir, $saldo_akhir, $userId);
            $insert_buku_besar = $objBuku->InsertBukuBesar($param_insert_buku_besar, $subAcc);
        }

        $isBukuBesarHisInsert = $objBuku->GetBukuBesarHistoriAkhirFromCoa($coaId, $subAcc);
        if (empty($isBukuBesarHisInsert)) {
            $param_insert_buku_besar_his = array($tanggal, $coaId, $debet, $kredit, $saldo_akhir, $saldo_akhir, $userId);
            $insert_buku_besar_histori = $objBuku->InsertBukuBesarHistory($param_insert_buku_besar_his, $subAcc);
        }

        $isTahunPembukuanInsert = $objThn->GetTahunPembukuanFromCoa($coaId, $subAcc);
        if (empty($isTahunPembukuanInsert)) {
            $param_insert_tahun_pembukuan = array($coaId, $coaId, $debet, $kredit, $saldo_akhir, $saldo_akhir);
            $insert_tahun_pembukuan = $objThn->InsertTahunPembukuan($param_insert_tahun_pembukuan, $subAcc);
        }

        if ($trapSuccess && $insert_buku_besar && $insert_buku_besar_histori && $insert_tahun_pembukuan) {
            $objThn->EndTrans(true);
            $objBuku->EndTrans(true);
            return true;
        } else {
            $objThn->EndTrans(false);
            $objBuku->EndTrans(false);
            return false;
        }
    }

    function UpdateSaldoAkhir() {

        $TahunPembukuan = new TahunPembukuan();
        $BukuBesar = new BukuBesar();

        $TahunPembukuan->StartTrans();
        $BukuBesar->StartTrans();
        //var_dump($this->POST);
        $coa_id = $this->POST['coa_id'];
        $sub_acc = $this->POST['sub_account'];
        //$nominal = (float) $this->POST['nominal'];
        $debet   = (float) $this->POST['debet'];
        $kredit  = (float) $this->POST['kredit'];
        $user_id = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
        $tanggal = $this->POST['tanggal_year'] . "-" . $this->POST['tanggal_mon'] . "-" . $this->POST['tanggal_day'];

        # trap
        $pengali = $BukuBesar->GetPengali($coa_id);
        /*
        if ($this->POST['is_debet_positif'] == '1') {
            $nominal *= $pengali['pengaliDebet'];
            $debet = $nominal;
            $kredit = 0;
        } else {
            $nominal *= $pengali['pengaliKredit'];
            $kredit = $nominal;
            $debet = 0;
        }
        */
        
        $is_proses = true;
        if ($debet == 0) {
            $saldo_akhir = $kredit * $pengali['pengaliKredit'];
        } elseif ($kredit == 0) {
            $saldo_akhir = $debet * $pengali['pengaliDebet'];
        } else {
            $is_proses = false;
        }

        //cek dan update saldo buku besar
        $is_buku_besar_insert = $BukuBesar->GetBukuBesarFromCoa($coa_id, $sub_acc);
        if (!empty($is_buku_besar_insert)) {
            $param_update_buku_besar = array($tanggal, $debet, $kredit, $saldo_akhir, $saldo_akhir, $user_id, $coa_id);
            $update_buku_besar = $BukuBesar->UpdateBukuBesar($param_update_buku_besar, $sub_acc);
        }

        //cek dan update saldo buku besar histori coa terakhir
        $data = $BukuBesar->GetBukuBesarHistoriAkhirFromCoa($coa_id, $sub_acc);
        if (!empty($data)) {
            $param_update_buku_besar_his = array($tanggal, $debet, $kredit, $saldo_akhir, $saldo_akhir, $user_id, $coa_id);
            $update_buku_besar_histori = $BukuBesar->UpdateBukuBesarHistory($param_update_buku_besar_his, $sub_acc);
        }

        //cek dan update tahun pembukuan
        $is_tahun_pembukuan_insert = $TahunPembukuan->GetTahunPembukuanFromCoa($coa_id, $sub_acc);
        if (!empty($is_tahun_pembukuan_insert)) {
            $param_update_tahun_pembukuan = array($debet, $kredit, $saldo_akhir, $saldo_akhir, $coa_id);
            $update_tahun_pembukuan = $TahunPembukuan->UpdateTahunPembukuan($param_update_tahun_pembukuan, $sub_acc);
        }

        if ($is_proses && $update_buku_besar && $update_buku_besar_histori && $update_tahun_pembukuan) {
            $TahunPembukuan->EndTrans(true);
            $BukuBesar->EndTrans(true);
            return true;
        } else {
            $TahunPembukuan->EndTrans(false);
            $BukuBesar->EndTrans(false);
            return false;
        }
    }

    function UpdateProses() {
        $pesan = $this->PesanErrorSubAkunTidakCocok();
        if ((isset($this->POST['btnsimpan'])) && ($this->POST['op'] == 'edit') && !isset($pesan)) {
            $rs_update = $this->UpdateSaldoAkhir();
            if ($rs_update == true) {
                $this->POST['done'] = 'ok';
                Messenger::Instance()->Send(
                    'tahun_pembukuan', 
                    'DetilSaldoAwal', 
                    'view', 
                    'html', 
                    array(
                        $this->POST, 
                        'Update saldo awal berhasil',
                        'notebox-done'
                    ), 
                    Messenger::NextRequest
                );
                $urlRedirect = Dispatcher::Instance()->GetUrl(
                    'tahun_pembukuan', 
                    'DetilSaldoAwal', 
                    'view', 
                    'html'
                ) . '&coaid=' . $this->POST['coa_id'];
            } else {
                $this->msg = 'Update saldo awal gagal';
                $urlRedirect['error'] = true;
                $urlRedirect['msg'] = $this->msg;
            }
        } else {
            if (isset($this->POST['btnbatal'])) {
                $urlRedirect = Dispatcher::Instance()->GetUrl('tahun_pembukuan', 'DetilSaldoAwal', 'view', 'html') . '&coaid=' . $this->POST['coa_id'];
            } else {
                $urlRedirect['error'] = true;
                $urlRedirect['msg'] = $this->msg . $pesan;
            }
        }

        return $urlRedirect;
    }

    function AddProses() {
        $pesan = $this->PesanErrorSubAkunTidakCocok();
        if ((isset($this->POST['btnsimpan'])) && ($this->POST['op'] == 'tambah') && $this->validasi() && !isset($pesan)) {
            $add = $this->AddSaldoAkhir();
            if ($add) {
                $this->POST['done'] = 'ok';
                Messenger::Instance()->Send('tahun_pembukuan', 'DetilSaldoAwal', 'view', 'html', array($this->POST, 'Tambah Saldo Awal Berhasil', 'notebox-done'), Messenger::NextRequest);
                $urlRedirect = Dispatcher::Instance()->GetUrl('tahun_pembukuan', 'DetilSaldoAwal', 'view', 'html') . '&coaid=' . $this->POST['coa_id'];
            } else {
                $this->msg = 'Perubahan data gagal dilakukan silahkan ulangi lagi';
                $urlRedirect['error'] = true;
                $urlRedirect['msg'] = $this->msg;
            }
        } else {
            if (isset($this->POST['btnbatal'])) {
                $urlRedirect = Dispatcher::Instance()->GetUrl('tahun_pembukuan', 'DetilSaldoAwal', 'view', 'html') . '&coaid=' . $this->POST['coa_id'];
            } else {
                $urlRedirect['error'] = true;
                $urlRedirect['msg'] = $this->msg . $pesan;
            }
        }

        return $urlRedirect;
    }

    function Delete() {
        $arrId = $this->POST['idDelete'];
        $arrSubAcc = str_replace('.', '-', $this->POST['nameDelete']);
        
        $objTahun = new TahunPembukuan();
        $objBuku = new BukuBesar();

        $objTahun->StartTrans();
        $objBuku->StartTrans();
        
        for ($i = 0; $i < count($arrId); $i++) {
            $tmp = explode('.', $arrId[$i]);
            $tpId = $tmp[0];
            $coaId = $tmp[1];
            $subAcc = $arrSubAcc[$i];
            $deleteBB = $objBuku->DeleteBukuBesarByCoaSubAccount($coaId, $subAcc);
            $deleteBBH = $objBuku->DeleteBukuBesarHistoryByCoaSubAccount($coaId, $subAcc);
            $deleteTP = $objTahun->DeleteTahunPembukuanById($tpId);
            $delete = $deleteBB && $deleteBBH && $deleteTP;
            if (!$delete)
                break;
        }

        if ($delete) {
            $objTahun->EndTrans(true);
            $objBuku->EndTrans(true);
            Messenger::Instance()->Send('tahun_pembukuan', 'DetilSaldoAwal', 'view', 'html', array($this->POST, 'Penghapusan Data Berhasil Dilakukan', 'notebox-done'), Messenger::NextRequest);
        } else {
            $objTahun->EndTrans(false);
            $objBuku->EndTrans(false);
            Messenger::Instance()->Send('tahun_pembukuan', 'DetilSaldoAwal', 'view', 'html', array($this->POST, ' Data Tidak Dapat Dihapus', 'notebox-warning'), Messenger::NextRequest);
        }

        $urlRedirect = Dispatcher::Instance()->GetUrl('tahun_pembukuan', 'DetilSaldoAwal', 'view', 'html') . '&coaid=' . $coaId;
        return $urlRedirect;
    }

    function validasi() {
        $this->msg = '';
        if (empty($this->POST))
            $this->msg = 'Data yang dimasukan masih kosong';

        # set sub_account menjadi defaultSubacc jika kosong
        if ($this->POST['sub_account'] == '')
            $this->POST['sub_account'] = $this->defaultSubacc;

        #cek coaId dan subacc sudah teregister atau belum
        $obj = new TahunPembukuan();
        $cek = $obj->GetBalancePembukuanSubAccCoa($this->POST['coa_id'], $this->POST['sub_account']);
        if (count($cek) > 0)
            $this->msg = 'Atribut <b>' . $this->POST['sub_account'] . '</b> sudah terdaftar';

        if ($this->msg <> '')
            return false;
        else
            return true;
    }

    # ================== SUBACCOUNT VALIDATION =========================== #

    function checkSubAkun() {
        $obj = new TahunPembukuan();
        $dataSubAkun = $obj->GetDataKodeAkun();
        @$SubAkun[0] = $this->POST['sub_account'];
        $subAkunExist = (count($SubAkun) > 0) ? true : false;

        if ($subAkunExist) {
            foreach ($SubAkun as $key => $value) {
                $arrSubAkun[$key] = explode("-", $value);
            }
        }

        foreach ($dataSubAkun as $key => $value) {
            //sub akun => spasi 1
            if ($value['akun'] == 1 && (1 <= $this->jmlSubAcc)) {
                if ($subAkunExist) {
                    foreach ($arrSubAkun as $key1 => $value1) {
                        if ($value1[0] <> '') {
                            if ($value['id'] == $value1[0]) {
                                $errorAkun[$key1][0] = 0;
                            } else {
                                if (isset($errorAkun[$key1][0]) && $errorAkun[$key1][0] == 0) {
                                    $errorAkun[$key1][0] = 0;
                                } else {
                                    $errorAkun[$key1][0] = 1;
                                }
                            }
                        } else {
                            $errorAkun[$key1][0] = 0;
                        }
                    }
                }
            }

            //sub akun => spasi 2
            if ($value['akun'] == 2 && (2 <= $this->jmlSubAcc)) {
                if ($subAkunExist) {
                    foreach ($arrSubAkun as $key1 => $value1) {
                        if ($value1[1] <> '') {
                            if ($value['id'] == $value1[1]) {
                                $errorAkun[$key1][1] = 0;
                            } else {
                                if (isset($errorAkun[$key1][1]) && $errorAkun[$key1][1] == 0) {
                                    $errorAkun[$key1][1] = 0;
                                } else {
                                    $errorAkun[$key1][1] = 1;
                                }
                            }
                        } else {
                            $errorAkun[$key1][1] = 0;
                        }
                    }
                }
            }

            //sub akun => spasi 3
            if ($value['akun'] == 3 && (3 <= $this->jmlSubAcc)) {
                if ($subAkunExist) {
                    foreach ($arrSubAkun as $key1 => $value1) {
                        if ($value1[2] <> '') {
                            if ($value['id'] == $value1[2]) {
                                $errorAkun[$key1][2] = 0;
                            } else {
                                if (isset($errorAkun[$key1][2]) && $errorAkun[$key1][2] == 0) {
                                    $errorAkun[$key1][2] = 0;
                                } else {
                                    $errorAkun[$key1][2] = 1;
                                }
                            }
                        } else {
                            $errorAkun[$key1][2] = 0;
                        }
                    }
                }
            }

            //sub akun => spasi 4
            if ($value['akun'] == 4 && (4 <= $this->jmlSubAcc)) {
                if ($subAkunExist) {
                    foreach ($arrSubAkun as $key1 => $value1) {
                        if ($value1[3] <> '') {
                            if ($value['id'] == $value1[3]) {
                                $errorAkun[$key1][3] = 0;
                            } else {
                                if (isset($errorAkun[$key1][3]) && $errorAkun[$key1][3] == 0) {
                                    $errorAkun[$key1][3] = 0;
                                } else {
                                    $errorAkun[$key1][3] = 1;
                                }
                            }
                        } else {
                            $errorAkun[$key1][3] = 0;
                        }
                    }
                }
            }

            //sub akun => spasi 5
            if ($value['akun'] == 5 && (5 <= $this->jmlSubAcc)) {
                if ($subAkunExist) {
                    foreach ($arrSubAkun as $key1 => $value1) {
                        if ($value1[4] <> '') {
                            if ($value['id'] == $value1[4]) {
                                $errorAkun[$key1][4] = 0;
                            } else {
                                if (isset($errorAkun[$key1][4]) && $errorAkun[$key1][4] == 0) {
                                    $errorAkun[$key1][4] = 0;
                                } else {
                                    $errorAkun[$key1][4] = 1;
                                }
                            }
                        } else {
                            $errorAkun[$key1][4] = 0;
                        }
                    }
                }
            }

            //sub akun => spasi 6
            if ($value['akun'] == 6 && (6 <= $this->jmlSubAcc)) {
                if ($subAkunExist) {
                    foreach ($arrSubAkun as $key1 => $value1) {
                        if ($value1[5] <> '') {
                            if ($value['id'] == $value1[5]) {
                                $errorAkun[$key1][5] = 0;
                            } else {
                                if (isset($errorAkun[$key1][5]) && $errorAkun[$key1][5] == 0) {
                                    $errorAkun[$key1][5] = 0;
                                } else {
                                    $errorAkun[$key1][5] = 1;
                                }
                            }
                        } else {
                            $errorAkun[$key1][5] = 0;
                        }
                    }
                }
            }

            //sub akun => spasi 7
            if ($value['akun'] == 7 && (7 <= $this->jmlSubAcc)) {
                if ($subAkunExist) {
                    foreach ($arrSubAkun as $key1 => $value1) {
                        if ($value1[6] <> '') {
                            if ($value['id'] == $value1[6]) {
                                $errorAkun[$key1][6] = 0;
                            } else {
                                if (isset($errorAkun[$key1][6]) && $errorAkun[$key1][6] == 0) {
                                    $errorAkun[$key1][6] = 0;
                                } else {
                                    $errorAkun[$key1][6] = 1;
                                }
                            }
                        } else {
                            $errorAkun[$key1][6] = 0;
                        }
                    }
                }
            }
        }

        @$return['errorAkun'] = $errorAkun;

        return $return;
    }

    function PesanErrorSubAkunTidakCocok() {
        $status = $this->checkSubAkun();
        @$SubAkun[0] = $this->POST['sub_account'];
        $subAkunExist = (count($SubAkun) > 0) ? true : false;

        if ($subAkunExist) {
            foreach ($SubAkun as $key => $value) {
                $arrSubAkun[$key] = explode("-", $value);
            }
        }

        if ($subAkunExist) {
            for ($i = 0; $i < count($status['errorAkun']); $i++) {
                for ($j = 0; $j <= ($this->jmlSubAcc - 1); $j++) {

                    if ($status['errorAkun'][$i][$j] == 1) {
                        $arrSubAkun[$i][$j] = "<u>" . $arrSubAkun[$i][$j] . "</u>";
                        @$errorAkun[$i] = $errorAkun[$i] + 1;
                    }
                    if ($status['errorAkun'][$i][$j] == 0) {
                        $arrSubAkun[$i][$j] = $arrSubAkun[$i][$j];
                        @$errorAkun[$i] = $errorAkun[$i] + 0;
                    }
                    if ($SubAkunGabung[$i] == "") {
                        $SubAkunGabung[$i] = $arrSubAkun[$i][$j];
                    } else {
                        $SubAkunGabung[$i] = $SubAkunGabung[$i] . "-" . $arrSubAkun[$i][$j];
                    }
                }
            }
        }

        if (count(@$errorAkun) > 0) {
            foreach ($errorAkun as $key => $value) {
                if ($value != 0) {
                    if (@$pesan == "") {
                        @$pesan .= $SubAkunGabung[$key];
                    } else {
                        $pesan .= "<br/>" . $SubAkunGabung[$key];
                    }
                }
            }
        }

        if (isset($pesan)) {
            @$pesanError .= "Data Atribut Berikut salah : <br/>" . @$pesan . " <br/>";
        }

        return(@$pesanError);
    }

    # ================== END SUBACCOUNT VALIDATION =========================== #
}

?>