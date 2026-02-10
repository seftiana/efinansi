<?php

/**
 * @package pm_jenis_biaya
 * modul ini digunakan untuk men-setting status jenis biaya (acrual / cash bases)
 * modul ini melakukan koneksi langsung dengan database gtfinansi pembayaran
 * 
 * @author gtPembayaran
 * 
 * @analized by dyah fajar <dyah@gamatechno.com>
 * @modified by noor hadi <noor.hadi@gamatechno.com>
 * 
 * 
 * @copyright (c) 2017, Gamatechno Indonesia
 * 
 */
require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/pm_jenis_biaya/business/Jenisbiaya.class.php';

class ViewJenisbiaya extends HtmlResponse {

    public function TemplateModule() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') .
                'module/pm_jenis_biaya/template');
        $this->SetTemplateFile('view_jenisbiaya.html');
    }

    public function ProcessRequest() {
        $POST = is_object($_POST) ? $_POST->AsArray() : $_POST;
        $GET = is_object($_GET) ? $_GET->AsArray() : $_GET;
        //mentukan koneksi ke reguler atau pasca
        if (isset($GET['prodi'])) {
            $prodi = Dispatcher::Instance()->Decrypt($GET['prodi']);
        } else {
            $prodi = 'reguler';
        }

        if ($prodi == 'reguler') {
            $connectionId = 1;
            $idProgram = 1;
        } else {
            $connectionId = 2;
            $idProgram = 2;
        }
       
        $jenisbiayaObj = new Jenisbiaya($connectionId);
        
        $prodi_keterangan = $jenisbiayaObj->GetProgramStudi($idProgram);
        $jenisbiaya = Dispatcher::Instance()->Decrypt($GET['jenisbiaya']);

        if($POST['btncari']){
            $jenisbiaya = $POST['jenisbiaya'];
            $return['kelJenisBiayaId'] = $POST['kelJenisBiayaId'];
            $return['jenisBiayaAccrual'] = $POST['jenisBiayaAccrual'];
        } elseif($GET['cari']){
            $jenisbiaya = Dispatcher::Instance()->Decrypt($GET['jenisbiaya']);
            $return['kelJenisBiayaId'] = Dispatcher::Instance()->Decrypt($GET['kelJenisBiayaId']);
            $return['jenisBiayaAccrual'] = Dispatcher::Instance()->Decrypt($GET['jenisBiayaAccrual']);
        } else {
            $jenisbiaya = '';
            $return['kelJenisBiayaId'] = 'all';
            $return['jenisBiayaAccrual'] = 'all';
        }
        
        $getKelompok = $jenisbiayaObj->GetNamaKelompokPencarian(); //combobox untuk nama kelompok

        $getJbBelumSisetTipePencatatannya = $jenisbiayaObj->GetJenisBiayaBelumDiSetTipePencatatan();        
        Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 'jeniskeljns_nama', array(
            'kelJenisBiayaId',
            $getKelompok,
            $return['kelJenisBiayaId'],
            'true',
            ''
            ), Messenger::CurrentRequest);


        $getTipePencatatan = $jenisbiayaObj->GetTipePencatatan();
        Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 'jeniskeljns_tipe_catat', array(
            'jenisBiayaAccrual',
            $getTipePencatatan,
            $return['jenisBiayaAccrual'],
            'true',
            ''
            ), Messenger::CurrentRequest);
        
        //view
        $itemViewed = 20;
        $currPage = 1;
        $startRec = 0;

        if (isset($_GET['page'])) {
            $currPage = (string) $_GET['page'];
            $startRec = ($currPage - 1) * $itemViewed;
        }
        $dataJenisbiaya = $jenisbiayaObj->getDataJenisbiaya(
                $startRec, $itemViewed, $jenisbiaya, $return['kelJenisBiayaId'], $return['jenisBiayaAccrual']
        );
        $totalData = $jenisbiayaObj->GetSearchCount();
        //print_r($dataJenisbiaya);
        $url = Dispatcher::Instance()->GetUrl(
            Dispatcher::Instance()->mModule, 
            Dispatcher::Instance()->mSubModule, 
            Dispatcher::Instance()->mAction, 
            Dispatcher::Instance()->mType . 
            '&prodi=' . Dispatcher::Instance()->Encrypt($prodi) .
            '&jenisbiaya=' . Dispatcher::Instance()->Encrypt($jenisbiaya) . 
            '&kelJenisBiayaId=' . Dispatcher::Instance()->Encrypt($return['kelJenisBiayaId']) . 
            '&jenisBiayaAccrual=' . Dispatcher::Instance()->Encrypt($return['jenisBiayaAccrual']) . 
            '&cari=' . Dispatcher::Instance()->Encrypt(1)
        );
        
        Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top', array(
            $itemViewed,
            $totalData,
            $url,
            $currPage
            ), Messenger::CurrentRequest);
        $msg = Messenger::Instance()->Receive(__FILE__);
        $return['Pesan'] = $msg[0][1];
        $return['css'] = $msg[0][2];
        $return['dataJenisbiaya'] = $dataJenisbiaya;
        $return['start'] = $startRec + 1;
        $return['search']['jenisbiaya'] = $jenisbiaya;        
        $return['prodi'] = $prodi;
        $return['prodi_ket'] = $prodi_keterangan['name'];
        $return['get_jb_belum_di_set_tipe_pencatatannya'] = $getJbBelumSisetTipePencatatannya;
        return $return;
    }

    public function ParseTemplate($data = NULL) {
        $search = $data['search'];
        $OrderMax = $data['OrderMax'];
        $OrderMin = $data['OrderMin'];

        $totalTipeJbSet = (int) $data['get_jb_belum_di_set_tipe_pencatatannya'];
        if($totalTipeJbSet > 0){
            $this->mrTemplate->SetAttribute('information_box', 'visibility', 'visible');
            $this->mrTemplate->AddVar('information_box', 'JB_TOTAL_BS', $totalTipeJbSet);
        } else {
            $this->mrTemplate->SetAttribute('information_box', 'visibility', 'hidden');
        }
        
        $urlJb = Dispatcher::Instance()->GetUrl(
            'pm_jenis_biaya', 
            'Jenisbiaya', 
            'view', 
            'html'
        ) . '&prodi=' . Dispatcher::Instance()->Encrypt($data['prodi']);

        $this->mrTemplate->AddVar('content', 'JENISKELJNS_NAMA', $search['jeniskeljns_nama']);
        $this->mrTemplate->AddVar('content', 'JENISBIAYA', $search['jenisbiaya']);
        $this->mrTemplate->AddVar('content', 'JENISBIAYA_PRODI_KET', $data['prodi_ket']);
        $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlJb);

        $this->mrTemplate->AddVar('content', 'URL_SET_JB', 
            Dispatcher::Instance()->GetUrl(
                'pm_jenis_biaya', 
                'UpdateJenisbiaya', 
                'do', 
                'json'
            ) . '&prodi=' . Dispatcher::Instance()->Encrypt($data['prodi'])
        );

        $this->mrTemplate->AddVar('content', 'URL_REGULER', 
            Dispatcher::Instance()->GetUrl(
                'pm_jenis_biaya', 
                'Jenisbiaya', 
                'view', 
                'html'
            ) . '&prodi=' . Dispatcher::Instance()->Encrypt('reguler')
        );
        $this->mrTemplate->AddVar('content', 'URL_PASCA', 
            Dispatcher::Instance()->GetUrl(
                'pm_jenis_biaya', 
                'Jenisbiaya', 
                'view', 
                'html'
            ) . '&prodi=' . Dispatcher::Instance()->Encrypt('pasca')
        );

        if ($data['prodi'] == 'reguler') {
            $this->mrTemplate->AddVar('content', 'CLASS_URL_ACTIVE_R', '');
            $this->mrTemplate->AddVar('content', 'CLASS_URL_ACTIVE_P', 'inactive');
        } else {
            $this->mrTemplate->AddVar('content', 'CLASS_URL_ACTIVE_P', '');
            $this->mrTemplate->AddVar('content', 'CLASS_URL_ACTIVE_R', 'inactive');
        }
        
        if ($data['Pesan']) {
            $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
            $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $data['Pesan']);
            $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $data['css']);
        }

        if (empty($data['dataJenisbiaya'])) {
            $this->mrTemplate->AddVar('data_jenisbiaya', 'JENISBIAYA_EMPTY', 'YES');
        } else {
            $decPage = Dispatcher::Instance()->Decrypt($_REQUEST['page']);
            $encPage = Dispatcher::Instance()->Encrypt($decPage);
            $this->mrTemplate->AddVar('data_jenisbiaya', 'JENISBIAYA_EMPTY', 'NO');
            $dataJenisbiaya = $data['dataJenisbiaya'];

            //mulai bikin tombol delete
            $label = "Manajemen Jenisbiaya";
            $urlDelete = Dispatcher::Instance()->GetUrl('pm_jenis_biaya', 'deleteJenisbiaya', 'do', 'html');
            $urlReturn = Dispatcher::Instance()->GetUrl('pm_jenis_biaya', 'jenisbiaya', 'view', 'html');
            Messenger::Instance()->Send('confirm', 'confirmDelete', 'do', 'html', array(
                $label,
                $urlDelete,
                $urlReturn
                    ), Messenger::NextRequest);
            $this->mrTemplate->AddVar('content', 'URL_DELETE', Dispatcher::Instance()->GetUrl('confirm', 'confirmDelete', 'do', 'html'));

            for ($i = 0; $i < sizeof($dataJenisbiaya); $i++) {
                $no = $i + $data['start'];
                $dataJenisbiaya[$i]['number'] = $no;

                if ($no % 2 != 0) {
                    $dataJenisbiaya[$i]['class_name'] = 'table-common-even';
                } else {
                    $dataJenisbiaya[$i]['class_name'] = '';
                }

                if ($i == 0) {
                    $this->mrTemplate->AddVar('content', 'FIRST_NUMBER', $no);
                }

                if ($i == sizeof($dataJenisbiaya) - 1) {
                    $this->mrTemplate->AddVar('content', 'LAST_NUMBER', $no);
                }
                $idEnc = Dispatcher::Instance()->Encrypt($dataJenisbiaya[$i]['jenisbiaya_id']);
                $dataJenisbiaya[$i]['url_edit'] = Dispatcher::Instance()->GetUrl('pm_jenis_biaya', 'inputJenisbiaya', 'view', 'html') . '&dataId=' . $idEnc . '&page=' . $encPage . '&cari=' . $cari;

                if ($dataJenisbiaya[$i]['jenisbiaya_order'] != $OrderMax['max_order']) {
                    $url_down = Dispatcher::Instance()->GetUrl('pm_jenis_biaya', 'orderJenisBiaya', 'do', 'html') . '&dataId=' . $idEnc . '&orderId=' . Dispatcher::Instance()->Encrypt($dataJenisbiaya[$i]['jenisbiaya_order']) . '&order=down' . '&page=' . $encPage . '&cari=' . $cari;
                    $dataJenisbiaya[$i]['url_down'] = '<a class="xhr dest_subcontent-element" href="' . $url_down . '" title="Down"><img src="" alt="Down"/></a>';
                } else {
                    $dataJenisbiaya[$i]['url_down'] = '';
                }

                if ($dataJenisbiaya[$i]['jenisbiaya_order'] != $OrderMin['min_order']) {
                    $url_up = Dispatcher::Instance()->GetUrl('pm_jenis_biaya', 'orderJenisBiaya', 'do', 'html') . '&dataId=' . $idEnc . '&orderId=' . Dispatcher::Instance()->Encrypt($dataJenisbiaya[$i]['jenisbiaya_order']) . '&order=up' . '&page=' . $encPage . '&cari=' . $cari;
                    $dataJenisbiaya[$i]['url_up'] = '<a class="xhr dest_subcontent-element" href="' . $url_up . '" title="UP"><img src="" alt="Up"/></a>';
                } else {
                    $dataJenisbiaya[$i]['url_up'] = '';
                }

                $statusRadioButton = 'checked="checked"';
                if ($dataJenisbiaya[$i]['jenisbiaya_pencatatan'] == '1') {
                    $this->mrTemplate->AddVar('data_jenisbiaya_item', 'JENISBIAYA_ACRUAL_DIPILIH', $statusRadioButton);
                    $this->mrTemplate->AddVar('data_jenisbiaya_item', 'JENISBIAYA_CASH_DIPILIH', '');
                    $dataJenisbiaya[$i]['status_label'] = "accrual";
                    $dataJenisbiaya[$i]['status_color'] ="pink";
                } else if ($dataJenisbiaya[$i]['jenisbiaya_pencatatan'] == '0') { 
                    $this->mrTemplate->AddVar('data_jenisbiaya_item', 'JENISBIAYA_ACRUAL_DIPILIH', '');
                    $this->mrTemplate->AddVar('data_jenisbiaya_item', 'JENISBIAYA_CASH_DIPILIH', $statusRadioButton);
                    $dataJenisbiaya[$i]['status_label'] = "cash";
                    $dataJenisbiaya[$i]['status_color'] ="green";
                } else {
                    $this->mrTemplate->AddVar('data_jenisbiaya_item', 'JENISBIAYA_ACRUAL_DIPILIH', '');
                    $this->mrTemplate->AddVar('data_jenisbiaya_item', 'JENISBIAYA_CASH_DIPILIH', '');
                    $dataJenisbiaya[$i]['status_label'] = 'none';
                    $dataJenisbiaya[$i]['status_color'] ="grey";
                }
                /*
                if ($dataJenisbiaya[$i]['jenisbiaya_is_aktif'] == "Tidak") {
                    $dataJenisbiaya[$i]['status_img'] = 'ren';
                } else {
                    $dataJenisbiaya[$i]['status_img'] = "green";
                }
                 * 
                 */
                $this->mrTemplate->AddVars('data_jenisbiaya_item', $dataJenisbiaya[$i], 'JENISBIAYA_');
                $this->mrTemplate->parseTemplate('data_jenisbiaya_item', 'a');
            }
        }
    }

}

?>