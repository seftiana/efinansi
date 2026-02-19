<?php

require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/user_unit_kerja/business/UserUnitKerja.class.php';

require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/finansi_transaksi_penerimaan_bank_mhs/business/TransaksiPenerimaanBankMhs.class.php';

class ViewTransaksi extends HtmlResponse {

    public function TemplateModule() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'module/finansi_transaksi_penerimaan_bank_mhs/template');
        $this->SetTemplateFile('view_input_transaksi_penerimaan_bank_mhs.html');
    }

    public function ProcessRequest() {
        $mObj = new TransaksiPenerimaanBankMhs();
        
        $userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
        
        $mUniObj             = new UserUnitKerja();
        $arrUnitKerjaRef     = $mUniObj->GetUnitKerjaRefUser($userId);
        
        $messenger = Messenger::Instance()->Receive(__FILE__);
        $message = $style = $messengerData = NULL;
        $queryString = $mObj->_getQueryString();
        $queryString = ($queryString == '' OR $queryString === NULL) ? '' : '&' . $queryString;
        $queryReturn = ($queryString == '' OR $queryString === NULL) ? '' : '&search=1' . $queryString;

        $tahunPencatatan = $mObj->getTahunPencatatan();
        $tipeTransaksi = $mObj->getTipeTransaksi();
        $minYear = $tahunPencatatan['min_year'];
        $maxYear = $tahunPencatatan['max_year'];
        $subAkunPatern = $mObj->getPaternSubAccount();          
        $patern = $subAkunPatern['patern'];
        $regex =  $subAkunPatern['regex'];
        $arrStatus = array(
            array('id' => 'Y', 'name' => 'Ya'),
            array('id' => 'T', 'name' => 'Tidak')
        );
        $arrBentukTransaksi = $mObj->GetBentukTransaksi();
        $jurnalDebet = array();
        $jurnalKredit = array();
        
        $dataJB = array();
        $requestData = array();
        /** data prodi */
        $dataPembProdi = array();

        $index = 0;
        $requestData['sub_akun_patern'] = $patern;
        $requestData['tanggal'] = date('Y-m-d');
        $requestData['auto_number'] = 'Y';
        $requestData['ref_kelompok'] = 'RPEN';
        $requestData['bpkb_ro_v'] = 'readonly="readonly"';
        $requestData['nama_penyetor']    = '';//diterima dari
        $requestData['nama_penerima']    = '';// tujuan
        $requestData['tipe_transaksi']   = '';// tipe transaksi
        $requestData['unit_kerja_id']    = $arrUnitKerjaRef['id'];
        $requestData['unit_kerja_nama']  = $arrUnitKerjaRef['nama'];
        $requestData['is_institute']  = $arrUnitKerjaRef['isInstitute'];
        $isEmptyJB = true;                          
        $isEmptyPP = true;
        if ($messenger) {
            $messengerData = $messenger[0][0];
            $message = $messenger[0][1];
            $style = $messenger[0][2];

            $tanggalDay = (int) $messengerData['referensi_tanggal_day'];
            $tanggalMon = (int) $messengerData['referensi_tanggal_mon'];
            $tanggalYear = (int) $messengerData['referensi_tanggal_year'];
            $requestData['tanggal'] = date('Y-m-d', mktime(0, 0, 0, $tanggalMon, $tanggalDay, $tanggalYear));
            $requestData['status'] = $messengerData['status_kas'];
            $requestData['bentuk_transaksi'] = $messengerData['bentuk_transaksi'];
            $requestData['keterangan'] = $messengerData['keterangan'];
            $requestData['auto_number'] = $messengerData['auto_number'];
            $requestData['ref_kelompok'] = $messengerData['ref_kelompok'];
            $requestData['bpkb'] = $messengerData['bpkb'];
            $requestData['bpkb_ro_v']= $messengerData['bpkb_ro_v'];
            $requestData['nama_penyetor']    = trim($messengerData['nama_penyetor']);//diterima dari
            $requestData['nama_penerima']    = trim($messengerData['nama_penerima']);// tujuan
            //additional
            $requestData['unit_kerja_id']    = trim($messengerData['unit_kerja_id']);
            $requestData['unit_kerja_nama']  = trim($messengerData['unit_kerja_nama']);
            $requestData['is_institute']     = trim($messengerData['is_institute']);
            $requestData['rpen_id']          = trim($messengerData['rpen_id']);
            $requestData['rpen_nama']        = trim($messengerData['rpen_nama']);
            $requestData['rpen_nominal']     = trim($messengerData['rpen_nominal']);
            $requestData['skenario_id']      = trim($messengerData['skenario_id']);
            $requestData['skenario_nama']    = trim($messengerData['skenario_nama']);
            $requestData['pemb_id']          = trim($messengerData['pemb_id']);
            $requestData['pemb_id_detil']    = trim($messengerData['pemb_id_detil']);
            $requestData['pemb_tipe_pembayaran'] = trim($messengerData['pemb_tipe_pembayaran']);
            $requestData['pemb_nominal']     = trim($messengerData['pemb_nominal']);
            $requestData['tipe_transaksi']   = trim($messengerData['tipe_transaksi']);
            $requestData['pemb_prodi_id']        = trim($messengerData['pemb_prodi_id']);
            $requestData['pemb_prodi_nama']      = trim($messengerData['pemb_prodi_nama']);
            $requestData['pemb_jenis_biaya']     = trim($messengerData['pemb_jenis_biaya']);
            $requestData['pemb_nominal']     = trim($messengerData['pemb_nominal']);
            $requestData['pemb_potongan']    = trim($messengerData['pemb_potongan']);
            $requestData['pemb_deposit']     = trim($messengerData['pemb_deposit']);
            $requestData['pemb_deposit_masuk']     = trim($messengerData['pemb_deposit_masuk']);
            $requestData['pemb_keterangan']     = trim($messengerData['pemb_keterangan']);
            $requestData['pemb_penanggung_jawab']     = trim($messengerData['pemb_penanggung_jawab']);
            $requestData['pemb_id_detail']     = trim($messengerData['pemb_id_detail']);
               //end
            //pembayaran
            /** data prodi */
            $dataPembProdi = array();
            if($requestData['pemb_prodi_id'] && !empty($requestData['pemb_prodi_id'])) {
                $dataPembProdi = array(
                    'prodi_id' => $requestData['pemb_prodi_id'],
                    'prodi_nama' => $requestData['pemb_prodi_nama'],
                    'prodi_nominal' => $requestData['pemb_nominal'],
                    'prodi_potongan' => $requestData['pemb_potongan'],
                    'prodi_deposit' => $requestData['pemb_deposit'],
                    'prodi_deposit_masuk' => $requestData['pemb_deposit_masuk'],
                    'prodi_tipe_pembayaran' =>  $requestData['pemb_tipe_pembayaran'],
                    'prodi_keterangan' => $requestData['pemb_keterangan'],
                    'prodi_penanggung_jawab' => $requestData['pemb_penanggung_jawab'],
                    'prodi_id_detail' => $requestData['pemb_id_detail']
                );
                $isEmptyPP = false;
            } else {
                $isEmptyPP = true;
            } 
            /**/               
            //end
            if ($messengerData['debet'] && !empty($messengerData['debet'])) {
                foreach ($messengerData['debet'] as $debet) {
                    $jurnalDebet[$index]['id'] = $debet['id'];
                    $jurnalDebet[$index]['kode'] = $debet['kode'];
                    $jurnalDebet[$index]['nama'] = $debet['nama'];
                    $jurnalDebet[$index]['sub_akun'] = $debet['subaccount'];
                    $jurnalDebet[$index]['referensi'] = $debet['nomor_referensi'];
                    $jurnalDebet[$index]['keterangan'] = $debet['keterangan'];
                    $jurnalDebet[$index]['nominal'] = $debet['nominal'];
                    $index++;
                }                
            } 
            unset($index);
            $index = 0;
            if ($messengerData['kredit'] && !empty($messengerData['kredit'])) {
                foreach ($messengerData['kredit'] as $kredit) {
                    $jurnalKredit[$index]['id'] = $kredit['id'];
                    $jurnalKredit[$index]['kode'] = $kredit['kode'];
                    $jurnalKredit[$index]['nama'] = $kredit['nama'];
                    $jurnalKredit[$index]['sub_akun'] = $kredit['subaccount'];
                    $jurnalKredit[$index]['referensi'] = $kredit['nomor_referensi'];
                    $jurnalKredit[$index]['keterangan'] = $kredit['keterangan'];
                    $jurnalKredit[$index]['nominal'] = $kredit['nominal'];
                    $index++;
                }
            }
            $index = 0;
            if ($messengerData['jb'] && !empty($messengerData['jb'])) {
                foreach ($messengerData['jb'] as $jbp) {
                    $dataJB[$index]['prodi_id'] = $jbp['prodi_id'];
                    $dataJB[$index]['prodi_nama'] = $jbp['prodi_nama'];
                    $dataJB[$index]['jenis_biaya_id'] = $jbp['jenis_biaya_id'];
                    $dataJB[$index]['jenis_biaya_nama'] = $jbp['jenis_biaya_nama'];
                    $dataJB[$index]['nominal'] = $jbp['nominal'];
                    $dataJB[$index]['potongan'] = $jbp['potongan'];
                    $dataJB[$index]['deposit'] = $jbp['deposit'];
                    $dataJB[$index]['keterangan'] = $jbp['keterangan'];
                    $dataJB[$index]['id_detail'] = $jbp['id_detail'];
                    $dataJB[$index]['tipe'] = $jbp['tipe'];
                    $index++;
                }
                $isEmptyJB = false;
            } else {
                $isEmptyJB = true;
            }
        } 
        # GTFW Tanggal
        Messenger::Instance()->SendToComponent(
                'tanggal', 'Tanggal', 'view', 'html', 'referensi_tanggal', array(
            $requestData['tanggal'],
            $minYear,
            $maxYear,
            false,
            false,
            false
                ), Messenger::CurrentRequest
        );
        
        # Combobox
        Messenger::Instance()->SendToComponent(
            'combobox', 'Combobox', 'view', 'html',  'tipe_transaksi', array(
            'tipe_transaksi',
            $tipeTransaksi,
            $requestData['tipe_transaksi'],
            'false',
            'id="cmb_tipe_transaksi"'
                ), Messenger::CurrentRequest
        );
        
        # Combobox
        Messenger::Instance()->SendToComponent(
                'combobox', 'Combobox', 'view', 'html', 'status', array(
            'status_kas',
            $arrStatus,
            $requestData['status'],
            false,
            'id="cmb_status_kas"'
                ), Messenger::CurrentRequest
        );

        # Combobox
        Messenger::Instance()->SendToComponent(
                'combobox', 'Combobox', 'view', 'html', 'bentuk_transaksi', array(
            'bentuk_transaksi',
            $arrBentukTransaksi,
            $requestData['bentuk_transaksi'],
            false,
            'id="cmb_bentuk_transaksi"'
                ), Messenger::CurrentRequest
        );

        $return['query_string'] = $queryString;
        $return['request_data'] = $requestData;
        $return['sub_account']     = $mObj->getSubAccountCombo();
        $return['message'] = $message;
        $return['style'] = $style;
        $return['query_return'] = $queryReturn;
        $return['akun_debet']['data'] = json_encode($jurnalDebet);
        $return['akun_kredit']['data'] = json_encode($jurnalKredit);
        $return['data_jb'] = json_encode($dataJB);
        $return['data_pemb_prodi'] = json_encode($dataPembProdi);
        
        $return['is_empty_jb'] = $isEmptyJB;
        $return['is_empty_pp'] = $isEmptyPP;
        return $return;
    }

    public function ParseTemplate($data = NULL) {
        
        $requestData = $data['request_data'];
        $queryString = $data['query_string'];
        $queryReturn = $data['query_return'];
        $dataAkunDebet = $data['akun_debet'];
        $dataAkunKredit = $data['akun_kredit'];
        $dataJB = $data['data_jb'];
        $dataPembProdi = $data['data_pemb_prodi'];
        $message = $data['message'];
        $subAccount = empty($data['sub_account']) || is_null($data['sub_account']) ? array() : $data['sub_account'];
        $style = $data['style'];
        $urlAction = Dispatcher::Instance()->GetUrl(
                        'finansi_transaksi_penerimaan_bank_mhs', 'SaveTransaksi', 'do', 'json'
                ) . $queryString;

        $urlReturn = Dispatcher::Instance()->GetUrl(
                        'finansi_transaksi_penerimaan_bank_mhs', 'TransaksiPenerimaanBankMhs', 'view', 'html'
                ) . $queryReturn;

        $urlPopupCoa = Dispatcher::Instance()->GetUrl(
                'finansi_transaksi_penerimaan_bank_mhs', 'coa', 'popup', 'html'
        );
        $urlPopupUK = Dispatcher::Instance()->GetUrl(
                'finansi_transaksi_penerimaan_bank_mhs', 'PopupUnitKerja', 'view', 'html'
        );

        $urlPopupRenPen = Dispatcher::Instance()->GetUrl(
                'finansi_transaksi_penerimaan_bank_mhs', 'PopupRencanaPenerimaan', 'view', 'html'
        );
        $urlPopupSKJ = Dispatcher::Instance()->GetUrl(
                'finansi_transaksi_penerimaan_bank_mhs', 'PopupSkenarioKodeJurnal', 'view', 'html'
        );
        $urlPopupPembMhs = Dispatcher::Instance()->GetUrl(
                'finansi_transaksi_penerimaan_bank_mhs', 'PopupPembayaranMhs', 'view', 'html'
        );
        
        if($requestData['auto_number'] ==='Y') {
            $requestData['auto_ya'] = 'checked="checked"';
            $requestData['auto_tidak'] = '';
            $requestData['bpkb_ro_v'] = 'readonly="readonly"';
        } else {
            $requestData['auto_ya'] = '';
            $requestData['auto_tidak'] = 'checked="checked"';
            $requestData['bpkb_ro_v'] = '';
        }

        if($requestData['ref_kelompok'] ==='RPEN') {
            $requestData['kel_rpen'] = 'checked="checked"';
            $requestData['kel_lppa'] = ''; 
        } else {
            $requestData['kel_rpen'] = '';
            $requestData['kel_lppa'] = 'checked="checked"'; 
        }

        $this->mrTemplate->AddVar('content', 'URL_ACTION', $urlAction);
        $this->mrTemplate->AddVar('content', 'URL_RETURN', $urlReturn);
        $this->mrTemplate->AddVar('content', 'JSON_SUBACCOUNT', json_encode($subAccount));
        $this->mrTemplate->AddVars('content', $requestData);
        $this->mrTemplate->AddVar('content', 'POPUP_COA', $urlPopupCoa);
        $this->mrTemplate->AddVar('content', 'URL_POPUP_UK', $urlPopupUK);
        $this->mrTemplate->AddVar('content', 'URL_POPUP_RENPEN', $urlPopupRenPen);
        $this->mrTemplate->AddVar('content', 'URL_POPUP_SKJ', $urlPopupSKJ);
        $this->mrTemplate->AddVar('content', 'URL_POPUP_PEMB_MHS', $urlPopupPembMhs);
        $this->mrTemplate->AddVars('content', $dataAkunDebet, 'AKUN_DEBET_');
        $this->mrTemplate->AddVars('content', $dataAkunKredit, 'AKUN_KREDIT_');
        
        $this->mrTemplate->AddVar('content',  'DATA_JB',$dataJB);
        $this->mrTemplate->AddVar('content',  'DATA_PEMB_PRODI',$dataPembProdi);
        /*
        if ($autoApprove !== NULL AND (bool) $autoApprove === TRUE) {
            $this->mrTemplate->SetAttribute('approval', 'visibility', 'visible');
            $this->mrTemplate->SetAttribute('auto_approval', 'visibility', 'visible');
        } else {
            $this->mrTemplate->SetAttribute('approval', 'visibility', 'hidden');
        }
        * 
        */
  
        if($data['is_empty_jb'] == true) {
            $this->mrTemplate->AddVar('content',  'SHOW_JB','style="display:none;"');
        }
        if($data['is_empty_pp'] == true) {
            $this->mrTemplate->AddVar('content',  'SHOW_PP','style="display:none;"');
        }
        
        if ($message) {
            $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
            $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $message);
            $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $style);
        }
    }

}

?>