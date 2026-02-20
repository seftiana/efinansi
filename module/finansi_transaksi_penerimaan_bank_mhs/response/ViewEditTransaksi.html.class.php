<?php

/**
 * ================= doc ====================
 * FILENAME     : ViewEditJurnalUmum.html.class.php
 * @package     : ViewEditJurnalUmum
 * scope        : PUBLIC
 * @Author      : Eko Susilo
 * @modified    : noor hadi
 * @Created     : 2015-02-22
 * @Modified    : 2015-02-22
 * @Analysts    : Dyah Fajar N
 * @copyright   : Copyright (c) 2012 Gamatechno
 * ================= doc ====================
 */
require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/finansi_transaksi_penerimaan_bank_mhs/business/TransaksiPenerimaanBankMhs.class.php';

class ViewEditTransaksi extends HtmlResponse {

    function TemplateModule() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') .
                'module/finansi_transaksi_penerimaan_bank_mhs/template/');
        $this->SetTemplateFile('view_edit_transaksi_penerimaan_bank.html');
    }

    function ProcessRequest() {
        $mObj = new TransaksiPenerimaanBankMhs();
        
        $userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());

        $mUniObj             = new UserUnitKerja();
        $arrUnitKerjaRef     = $mUniObj->GetUnitKerjaRefUser($userId);
        
        //$autoApprove = $mObj->getApplicationSetting('JURNAL_AUTO_APPROVE');
        $messenger = Messenger::Instance()->Receive(__FILE__);
        $message = $style = $messengerData = NULL;
        $queryString = $mObj->_getQueryString();
        $queryString = ($queryString == '' OR $queryString === NULL) ? '' : '&' . $queryString;
        $queryReturn = ($queryString == '' OR $queryString === NULL) ? '' : '&search=1' . $queryString;
        $queryReturn = preg_replace('/\&data_id=[0-9]+/', '', $queryReturn);
        $queryReturn = preg_replace('/\&pr_id=[0-9]+/', '', $queryReturn);
        $transaksiId = Dispatcher::Instance()->Decrypt($mObj->_GET['data_id']);
        $pembukuanId = Dispatcher::Instance()->Decrypt($mObj->_GET['pr_id']);

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
        $requestData = array();
        $isEmptyJB = true;                          
        $isEmptyPP = true;
        
        $dataJB = array();
        
        $index = 0;
        $dataJurnalDetil = $mObj->ChangeKeyName($mObj->getDataJurnalDetail($transaksiId, $pembukuanId));
        $dataJurnalSubAkun = $mObj->ChangeKeyName($mObj->getDataJurnalSubAkun($transaksiId, $pembukuanId));
        $dataPembayaranByTransId = $mObj->getDataPembayaranByTransId($transaksiId);
       
        $debetIndex = 0;
        $kreditIndex = 0;

        foreach ($dataJurnalSubAkun as $jurnal) {
            if (strtoupper($jurnal['status']) == 'D') {
                $jurnalDebet[$debetIndex]['id'] = $jurnal['id'];
                $jurnalDebet[$debetIndex]['kode'] = $jurnal['kode'];
                $jurnalDebet[$debetIndex]['nama'] = $jurnal['nama'];
                $jurnalDebet[$debetIndex]['sub_akun'] = $jurnal['sub_account'];
                $jurnalDebet[$debetIndex]['referensi'] = $jurnal['referensi'];
                $jurnalDebet[$debetIndex]['keterangan'] = $jurnal['keterangan'];
                $jurnalDebet[$debetIndex]['nominal'] = $jurnal['nominal'];
                $debetIndex+=1;
            }

            if (strtoupper($jurnal['status']) == 'K') {
                $jurnalKredit[$kreditIndex]['id'] = $jurnal['id'];
                $jurnalKredit[$kreditIndex]['kode'] = $jurnal['kode'];
                $jurnalKredit[$kreditIndex]['nama'] = $jurnal['nama'];
                $jurnalKredit[$kreditIndex]['sub_akun'] = $jurnal['sub_account'];
                $jurnalKredit[$kreditIndex]['referensi'] = $jurnal['referensi'];
                $jurnalKredit[$kreditIndex]['keterangan'] = $jurnal['keterangan'];
                $jurnalKredit[$kreditIndex]['nominal'] = $jurnal['nominal'];
                $kreditIndex+=1;
            }
        }

        if ($dataPembayaranByTransId && !empty($dataPembayaranByTransId)) {
            $index = 0;
            foreach ($dataPembayaranByTransId as $jbp) {
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
            unset($index);           
            $isEmptyJB = false;
        } else {
            $isEmptyJB = true;
        }        
        
        $requestData['sub_akun_patern'] = $patern;
        $requestData['tanggal']     = date('Y-m-d', strtotime($dataJurnalDetil['tanggal']));
        $requestData['tanggal_old'] = date('Y-m-d', strtotime($dataJurnalDetil['tanggal']));
        $requestData['status'] = $dataJurnalDetil['status_kas'];
        $requestData['bentuk_transaksi'] = $dataJurnalDetil['kel_jns_id'];        
        $requestData['nama_penyetor']  = $dataJurnalDetil['nama_penyetor'];        
        $requestData['nama_penerima']  = $dataJurnalDetil['nama_penerima'];        
        $requestData['keterangan'] = $dataJurnalDetil['keterangan'];
        $requestData['id'] = $dataJurnalDetil['id'];
        $requestData['pembukuan_id'] = $dataJurnalDetil['pembukuan_id'];
        $requestData['tbank_id'] = $dataJurnalDetil['tbank_id'];
        if($dataJurnalDetil['tipe_transaksi'] == 'lppa_sisa') {
            $requestData['ref_kelompok'] = 'LPPA';
        } else {
            $requestData['ref_kelompok'] = 'RPEN';
        }
        
        $requestData['auto_number_status'] =  $dataJurnalDetil['is_auto_number'];
        $requestData['auto_number'] =  $dataJurnalDetil['is_auto_number'];
        $requestData['bpkb_ro_v'] = 'readonly="readonly"';
        
        $requestData['bpkb_old']  = $dataJurnalDetil['referensi'];
        $requestData['referensi'] = $dataJurnalDetil['referensi'];
        
        if($requestData['auto_number_status'] == 'Y') {
            $requestData['bpkb_auto'] = $requestData['referensi'];
            $requestData['bpkb'] = '';
        } else {
            $requestData['bpkb_auto'] ='';
            $requestData['bpkb']  = $requestData['referensi'];
        }
        
        $requestData['tipe_transaksi']   = $dataJurnalDetil['tipe_transaksi'];
        $requestData['unit_kerja_id']    = $dataJurnalDetil['unit_kerja_id'];
        $requestData['unit_kerja_nama']  = $dataJurnalDetil['unit_kerja_nama'];
        $requestData['is_institute']    = $dataJurnalDetil['is_institute'];
        $requestData['rpen_id']          = $dataJurnalDetil['rpen_id'];
        $requestData['rpen_nama']        = $dataJurnalDetil['rpen_nama'];
        $requestData['rpen_nominal']     = $dataJurnalDetil['rpen_nominal'];
        $requestData['skenario_id']      = $dataJurnalDetil['skenario_id'];
        $requestData['skenario_nama']    = $dataJurnalDetil['skenario_nama'];
        $requestData['jenis_biaya_id']    = $dataJurnalDetil['jenis_biaya_id'];
        $requestData['jenis_biaya_nama']    = $dataJurnalDetil['jenis_biaya_nama'];
        $requestData['tipe_bayar_skenario']    = $dataJurnalDetil['tipe_bayar_skenario'];
        $requestData['tipe_transaksi']   = $dataJurnalDetil['tipe_transaksi'];
        //data transaksi pembayaran
        $requestData['pemb_id']          = $dataJurnalDetil['pemb_id'];
        $requestData['pemb_nominal']     = $dataJurnalDetil['pemb_nominal'];        
        $requestData['pemb_id_detil']    = $dataJurnalDetil['pemb_id_detil'];
        $requestData['pemb_id_detil_old']    = $dataJurnalDetil['pemb_id_detil'];
        $requestData['pemb_tipe_pembayaran'] = $dataJurnalDetil['pemb_tipe_pembayaran'];
        $requestData['pemb_tipe_pembayaran_old'] = $dataJurnalDetil['pemb_tipe_pembayaran'];
        $requestData['tipe_transaksi']   = $dataJurnalDetil['tipe_transaksi'];
        $requestData['pemb_prodi_id']    = $dataJurnalDetil['pemb_prodi_id'];
        $requestData['pemb_prodi_nama']  = $dataJurnalDetil['pemb_prodi_nama'];
        $requestData['pemb_jenis_biaya'] = $dataJurnalDetil['pemb_jenis_biaya'];
        $requestData['pemb_nominal']     = $dataJurnalDetil['pemb_nominal'];
        $requestData['pemb_potongan']    = $dataJurnalDetil['pemb_potongan'];
        $requestData['pemb_deposit']     = $dataJurnalDetil['pemb_deposit']; 
        $requestData['pemb_deposit_masuk']     = $dataJurnalDetil['pemb_deposit_masuk']; 
        $requestData['pemb_keterangan']    = $dataJurnalDetil['pemb_keterangan']; 
        $requestData['pemb_penanggung_jawab'] = $dataJurnalDetil['pemb_penanggung_jawab'];
        $requestData['pemb_id_detail'] = $dataJurnalDetil['pemb_id_detail'];
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
                    'prodi_tipe_pembayaran' =>   $dataJurnalDetil['tipe_transaksi'],//$requestData['pemb_tipe_pembayaran'],
                    'prodi_keterangan' => $requestData['pemb_keterangan'],
                    'prodi_penanggung_jawab' => $requestData['pemb_penanggung_jawab'],
                    'prodi_id_detail' => $requestData['pemb_id_detail']
                );
                $isEmptyPP = false;
            } else {
                $isEmptyPP = true;
            }        
            /**/           
        
        if ($messenger) {
            //print_r($messenger);
            $messengerData = $messenger[0][0];
            $message = $messenger[0][1];
            $style = $messenger[0][2];

            $tanggalDay = (int) $messengerData['referensi_tanggal_day'];
            $tanggalMon = (int) $messengerData['referensi_tanggal_mon'];
            $tanggalYear = (int) $messengerData['referensi_tanggal_year'];
            $requestData['id'] = $messengerData['data_id'];
            $requestData['pembukuan_id'] = $messengerData['pembukuan_referensi_id'];
            $requestData['tanggal'] = date('Y-m-d', mktime(0, 0, 0, $tanggalMon, $tanggalDay, $tanggalYear));
            $requestData['status'] = $messengerData['status_kas'];
            $requestData['bentuk_transaksi'] = $messengerData['bentuk_transaksi'];
            $requestData['nama_penyetor']  = $messengerData['nama_penyetor'];        
            $requestData['nama_penerima']  = $messengerData['nama_penerima'];        
            $requestData['keterangan'] = $messengerData['keterangan'];

            $requestData['auto_number'] = $messengerData['auto_number'];
            $requestData['bpkb'] = $messengerData['bpkb'];
            $requestData['bpkb_ro_v']= $messengerData['bpkb_ro_v'];
            
            $requestData['tipe_transaksi']   =  $messengerData['tipe_transaksi'];
            $requestData['unit_kerja_id']    =  $messengerData['unit_kerja_id'];
            $requestData['unit_kerja_nama']  =  $messengerData['unit_kerja_nama'];
            $requestData['is_institute']     =  $messengerData['is_institute'];
            $requestData['rpen_id']          =  $messengerData['rpen_id'];
            $requestData['rpen_nama']        =  $messengerData['rpen_nama'];
            $requestData['rpen_nominal']     =  $messengerData['rpen_nominal'];
            $requestData['skenario_id']      =  $messengerData['skenario_id'];
            $requestData['skenario_nama']    =  $messengerData['skenario_nama'];
            $requestData['tipe_transaksi']   =  $messengerData['tipe_transaksi'];    
            //data transaksi pembayaran
            $requestData['pemb_id']          = $messengerData['pemb_id'];
            $requestData['pemb_nominal']     = $messengerData['pemb_nominal'];        
            $requestData['pemb_id_detil']    = $messengerData['pemb_id_detil'];
            $requestData['pemb_tipe_pembayaran'] = $messengerData['pemb_tipe_pembayaran'];
            $requestData['tipe_transaksi']   = $messengerData['tipe_transaksi'];
            $requestData['pemb_prodi_id']    = $messengerData['pemb_prodi_id'];
            $requestData['pemb_prodi_nama']  = $messengerData['pemb_prodi_nama'];
            $requestData['pemb_jenis_biaya'] = $messengerData['pemb_jenis_biaya'];
            $requestData['pemb_nominal']     = $messengerData['pemb_nominal'];
            $requestData['pemb_potongan']    = $messengerData['pemb_potongan'];
            $requestData['pemb_deposit']     = $messengerData['pemb_deposit'];            
            $requestData['pemb_deposit_masuk']     = $messengerData['pemb_deposit_masuk'];   
            $requestData['pemb_keterangan']     =  $messengerData['pemb_keterangan'];
            $requestData['pemb_penanggung_jawab'] = $messengerData['pemb_penanggung_jawab'];
            $requestData['pemb_id_detail']    = $messengerData['pemb_id_detail'];

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
            //print_r($messengerData['debet']);
            $index = 0;
            $jurnalDebet = array();
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
            $jurnalKredit = array();
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
            unset($index);
            $index = 0;
            $dataJB = array();
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
        $return['sub_account'] = $mObj->getSubAccountCombo();
        $return['message'] = $message;
        $return['style'] = $style;
        //$return['auto_approve'] = $autoApprove;
        $return['query_return'] = $queryReturn;
        $return['akun_debet']['data'] = json_encode($jurnalDebet);
        $return['akun_kredit']['data'] = json_encode($jurnalKredit);
        $return['data_jb'] = json_encode($dataJB);
        $return['data_pemb_prodi'] = json_encode($dataPembProdi);
        
        $return['is_empty_jb'] = $isEmptyJB;
        $return['is_empty_pp'] = $isEmptyPP;
        $return['tipe_transaksi'] =  $requestData['tipe_transaksi']   ;
        return $return;
    }

    function ParseTemplate($data = null) {
        
        //$autoApprove = $data['auto_approve'];
        $requestData = $data['request_data'];
        $queryString = $data['query_string'];
        $queryReturn = $data['query_return'];
        $dataAkunDebet = $data['akun_debet'];
        $dataAkunKredit = $data['akun_kredit'];
        $subAccount = empty($data['sub_account']) || is_null($data['sub_account']) ? array() :$data['sub_account'];
        
        $dataJB = $data['data_jb'];        
        $dataPembProdi = $data['data_pemb_prodi'];
        $message = $data['message'];
        $style = $data['style'];
        $urlAction = Dispatcher::Instance()->GetUrl(
                        'finansi_transaksi_penerimaan_bank_mhs', 'UpdateTransaksi', 'do', 'json'
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
        $this->mrTemplate->AddVars('content', $requestData);
        $this->mrTemplate->AddVar('content', 'POPUP_COA', $urlPopupCoa);
        $this->mrTemplate->AddVar('content', 'URL_POPUP_UK', $urlPopupUK);
        $this->mrTemplate->AddVar('content', 'URL_POPUP_RENPEN', $urlPopupRenPen);
        $this->mrTemplate->AddVar('content', 'URL_POPUP_SKJ', $urlPopupSKJ);
        $this->mrTemplate->AddVar('content', 'URL_POPUP_PEMB_MHS', $urlPopupPembMhs);
        $this->mrTemplate->AddVars('content', $dataAkunDebet, 'AKUN_DEBET_');
        $this->mrTemplate->AddVars('content', $dataAkunKredit, 'AKUN_KREDIT_');
        $this->mrTemplate->AddVar('content', 'JSON_SUBACCOUNT', json_encode($subAccount));
        
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

        if($param['pemb_prodi_id'] != '' && $param['tipe_transaksi'] != 'penerimaan'){
            $this->mrTemplate->AddVar('content',  'SHOW_JB','style=""');
            $this->mrTemplate->AddVar('content',  'SHOW_PP','style=""');
        } else {
            $this->mrTemplate->AddVar('content',  'SHOW_JB','style="display:none;"');
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