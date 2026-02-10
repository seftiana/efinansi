<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/posting/business/AppPosting.class.php';

class ViewPosting extends HtmlResponse {

    function TemplateModule() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') .
                'module/posting/template');
        $this->SetTemplateFile('view_posting.html');
    }

    function ProcessRequest() {
        //tahun untuk combo
        $messenger = Messenger::Instance()->Receive(__FILE__);
        $message = $style = NULL;
        $Obj = new AppPosting();
        $dateParameter = $Obj->getParameterDate();
        $requestData['last_posting'] = $dateParameter['last_posting'];
        $requestData['last_transaksi'] = $dateParameter['last_transaksi'];
        $dataJurnalDueDate = $Obj->getJurnalDueDate($requestData['last_posting'], $requestData['last_transaksi']);
        $countJurnalDueDate = $Obj->countJurnalDueDate($requestData['last_posting'], $requestData['last_transaksi']);
        $statePosting = $Obj->getStatePosting();
        $dataJurnal = $Obj->getJurnalPembukuan(
                $requestData['last_posting'], $requestData['last_transaksi']
        );

        if (empty($dataJurnal) && $statePosting == 1) {
            $message = 'Semua jurnal sudah terposting';
            $style = 'notebox-done';
        }
        
        if ($messenger) {
            $message = $messenger[0][1];
            $style = $messenger[0][2];
        }
        $tahunTrans = $Obj->GetMinMaxThnTrans();
        $now = date("Y-m-d");

        Messenger::Instance()->SendToComponent(
                'tanggal', 'Tanggal', 'view', 'html', 'tanggal_posting', array(
            $requestData['last_transaksi'],
            $tahunTrans['minTahun'],
            $tahunTrans['maxTahun']
                ), Messenger::CurrentRequest);
        $get = $_GET->AsArray();
        $return['msg'] = Dispatcher::Instance()->Decrypt($get['err']);

        $return['data_jurnal_due_date'] = $Obj->ChangeKeyName($dataJurnalDueDate);
        $return['count_jurnal_due_date'] = $countJurnalDueDate;
        $return['request_data'] = $requestData;
        $return['message'] = $message;
        $return['style'] = $style;
        $return['data_jurnal'] = $dataJurnal;

        $return['get_count_jurnal_terposting'] = $Obj->getCountJurnalPosting();
        $return['get_state_posting'] = $statePosting;
        return $return;
    }

    function ParseTemplate($data = NULL) {
        $message = $data['message'];
        $style = $data['style'];
        $start = 1;
        $requestData = $data['request_data'];
        $jurnalDueDate = $data['data_jurnal_due_date'];
        $countJurnalDueDate = $data['count_jurnal_due_date'];
        $dataJurnal = $data['data_jurnal'];
        $gcJBP = $data['get_count_jurnal_terposting'];
        $statePosting = $data['get_state_posting'];
        //echo $statePosting;
        $urlAction = Dispatcher::Instance()->GetUrl(
                'posting', 'Posting', 'do', 'html'
        );

        $urlJurnalDueDate = Dispatcher::Instance()->GetUrl(
                'posting', 'updateJurnal', 'view', 'html'
        );

        if ($statePosting == 1) {
            if ($gcJBP > 0) {
                $this->mrTemplate->AddVar('tgl_posting', 'AVAILABLE', 'ENABLE');
                $this->mrTemplate->AddVar('tgl_posting', 'LAST_POSTING', $requestData['last_posting']);
            }
        } else {
            $this->mrTemplate->AddVar('tgl_posting', 'AVAILABLE', 'DISABLED');
        }

        $this->mrTemplate->AddVar('content', 'URL_SUBMIT', $urlAction);
        $this->mrTemplate->AddVars('content', $requestData);

        if (empty($dataJurnal)) {
            $this->mrTemplate->AddVar('buttons', 'ACTION', 'ALL_POSTING');
        // } elseif (empty($jurnalDueDate) && $statePosting == 0) {
        //     $this->mrTemplate->AddVar('buttons', 'ACTION', 'DISABLED');
        } else {
            $this->mrTemplate->AddVar('buttons', 'ACTION', 'ENABLE');
        }
        //if(empty($jurnalDueDate)){
        //   $this->mrTemplate->AddVar('buttons', 'ACTION', 'ALL_POSTING');
        //}else{
        //    $this->mrTemplate->AddVar('buttons', 'ACTION', 'ENABLE');
        //disable due date//
        /*
          if($countJurnalDueDate <> 0){
          $this->mrTemplate->AddVar('buttons', 'ACTION', 'DISABLED');
          $this->mrTemplate->AddVar('buttons', 'COUNT', $countJurnalDueDate);
          }else{
          $this->mrTemplate->AddVar('buttons', 'ACTION', 'ENABLE');
          }
         * 
         */
        // }
        //
        if ($message) {
            $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
            $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $message);
            $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $style);
        }

        if (isset($data['msg'])) {
            if ($data['msg'] == 'posting') {
                $isiPesan = 'Proses Posting Berhasil';
                $class = 'notebox-done';
            } elseif ($data['msg'] == 'noposting') {
                $isiPesan = 'Proses Posting Gagal';
                $class = 'notebox-warning';
            } elseif ($data['msg'] == 'nodataposting') {
                $isiPesan = 'Data Transaksi untuk Di Posting Tidak Ada';
                $class = 'notebox-warning';
            }

            $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
            $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $isiPesan);
            $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $class);
        }

        // data transaksi due date
        // merupakan data-data jurnal yang tanggal nya di bawah dari tanggal terakhir posting / terlewat

        if (!empty($jurnalDueDate)) {
            $this->mrTemplate->SetAttribute('jurnal_due_date', 'visibility', 'show');
            $dataGrid = array();
            $pembukuanId = '';
            $transaksiId = '';
            $index = 0;
            $idx = 0;
            $dataJurnal = array();
            $rows = array();

            for ($i = 0; $i < count($jurnalDueDate);) {
                if ((int) $transaksiId === (int) $jurnalDueDate[$i]['id'] && (int) $pembukuanId === (int) $jurnalDueDate[$i]['pembukuan_id']) {
                    $ks = $pembukuanId . '.' . $transaksiId;
                    $dataJurnal[$ks][$idx]['akun_id'] = $jurnalDueDate[$i]['coa_id'];
                    $dataJurnal[$ks][$idx]['kode'] = $jurnalDueDate[$i]['coa_kode_akun'];
                    $dataJurnal[$ks][$idx]['nama'] = $jurnalDueDate[$i]['coa_nama_akun'];
                    $dataJurnal[$ks][$idx]['sub_account'] = $jurnalDueDate[$i]['sub_account'];
                    $dataJurnal[$ks][$idx]['nominal_debet'] = number_format($jurnalDueDate[$i]['nominal_debet'], 2, ',', '.');
                    $dataJurnal[$ks][$idx]['nominal_kredit'] = number_format($jurnalDueDate[$i]['nominal_kredit'], 2, ',', '.');
                    $dataJurnal[$ks][$idx]['class_name'] = $className;
                    $rows[$ks]['row_span'] += 1;
                    $i++;
                    $idx++;
                } else {
                    unset($idx);
                    $idx = 0;
                    $pembukuanId = (int) $jurnalDueDate[$i]['pembukuan_id'];
                    $transaksiId = (int) $jurnalDueDate[$i]['id'];
                    $kodeSistem = $pembukuanId . '.' . $transaksiId;
                    if ($start % 2 <> 0) {
                        $className = 'table-common-even';
                    } else {
                        $className = '';
                    }
                    $dataJurnal[$kodeSistem][$idx]['id'] = $jurnalDueDate[$i]['id'];
                    $dataJurnal[$kodeSistem][$idx]['nomor'] = $start;
                    $dataJurnal[$kodeSistem][$idx]['pembukuan_id'] = $jurnalDueDate[$i]['pembukuan_id'];
                    $dataJurnal[$kodeSistem][$idx]['kode_sistem'] = $kodeSistem;
                    $dataJurnal[$kodeSistem][$idx]['referensi'] = $jurnalDueDate[$i]['referensi'];
                    $dataJurnal[$kodeSistem][$idx]['deskripsi'] = $jurnalDueDate[$i]['catatan'];
                    $dataJurnal[$kodeSistem][$idx]['tanggal'] = $jurnalDueDate[$i]['tanggal_entry'];
                    $dataJurnal[$kodeSistem][$idx]['penanggung_jawab'] = $jurnalDueDate[$i]['penanggung_jawab'];
                    $dataJurnal[$kodeSistem][$idx]['type'] = 'parent';
                    $dataJurnal[$kodeSistem][$idx]['status_approval'] = $jurnalDueDate[$i]['status_approve'];
                    $dataJurnal[$kodeSistem][$idx]['status_posting'] = $jurnalDueDate[$i]['status_posting'];
                    $dataJurnal[$kodeSistem][$idx]['jurnal_balik'] = $jurnalDueDate[$i]['jurnal_balik'];
                    $dataJurnal[$kodeSistem][$idx]['has_jurnal'] = strtoupper($jurnalDueDate[$i]['has_jurnal']);
                    $dataJurnal[$kodeSistem][$idx]['jurnal'] = $jurnalDueDate[$i]['jurnal'];
                    $dataJurnal[$kodeSistem][$idx]['class_name'] = $className;
                    $rows[$kodeSistem]['row_span'] = 0;
                    $index++;
                    $start++;
                }
            }

            foreach ($dataJurnal as $grid) {
                foreach ($grid as $jurnal) {
                    if ($jurnal['type'] AND strtoupper($jurnal['type']) == 'PARENT') {
                        $jurnal['row_span'] = $rows[$jurnal['kode_sistem']]['row_span'];
                        $jurnal['url_update'] = $urlJurnalDueDate . '&transaksi_id=' . Dispatcher::Instance()->Encrypt($jurnal['id']) . '&pembukuan_id=' . $jurnal['pembukuan_id'];
                        $this->mrTemplate->AddVar('data_jurnal', 'LEVEL', 'PARENT');
                        $this->mrTemplate->AddVars('data_jurnal', $jurnal);
                    } else {
                        $this->mrTemplate->AddVar('data_jurnal', 'LEVEL', 'DATA');
                        $this->mrTemplate->AddVars('data_jurnal', $jurnal);
                    }
                    $this->mrTemplate->parseTemplate('data_list', 'a');
                }
            }
        }
    }

}

?>