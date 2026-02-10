<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') .
    'module/history_apbnp/business/HistoryApbnp.class.php';

#doc
#    classname:    ViewHistoryApbnp
#    scope:        PUBLIC
#
#/doc

class ViewHistoryApbnp extends HtmlResponse {

    public $obj;
    public $post;
    public $data;

    public function __construct() {
        $this->obj = new HistoryApbnp();
    }

    public function TemplateModule() {
        $this->setTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') .
                'module/history_apbnp/template');
        $this->setTemplateFile('history_apbnp.html');
    }

    public function ProcessRequest() {

        $months = $this->obj->indonesianMonth;
        $arrPeriodeTahun     = $this->obj->GetPeriodeTahun();
        $periodeTahun        = $this->obj->GetPeriodeTahun(array(
           'active' => true
        ));
        $msg = Messenger::Instance()->Receive(__FILE__);
        $return['msg'] = $msg[0][1];
        $return['data'] = $msg[0][0];
        $return['css'] = $msg[0][2];

        if (isset($this->obj->_POST['btnCari'])) {
            $requestData['kode'] = $this->obj->_POST['kode'];
            $requestData['unit_id_asal'] = $this->obj->_POST['unit_id_asal'];
            $requestData['unit_nama_asal'] = $this->obj->_POST['unit_nama_asal'];
            $requestData['unit_id_tujuan'] = $this->obj->_POST['unit_id_tujuan'];
            $requestData['unit_nama_tujuan'] = $this->obj->_POST['unit_nama_tujuan'];
            $requestData['bulan_asal'] = $this->obj->_POST['bulan_asal'];
            $requestData['bulan_tujuan'] = $this->obj->_POST['bulan_tujuan'];
            $requestData['ta_id']      = $this->obj->_POST['tahun_anggaran'];
            $requestData['type'] = $this->obj->_POST['type'];            
        } else if (isset($this->obj->_GET['cari'])) {
            $requestData['kode'] = Dispatcher::Instance()->Decrypt($this->obj->_GET['kode']);
            $requestData['unit_id_asal'] = Dispatcher::Instance()->Decrypt($this->obj->_GET['unit_id_asal']);
            $requestData['unit_nama_asal'] = Dispatcher::Instance()->Decrypt($this->obj->_GET['unit_nama_asal']);
            $requestData['unit_id_tujuan'] = Dispatcher::Instance()->Decrypt($this->obj->_GET['unit_id_tujuan']);
            $requestData['unit_nama_tujuan'] = Dispatcher::Instance()->Decrypt($this->obj->_GET['unit_nama_tujuan']);
            $requestData['bulan_asal'] = Dispatcher::Instance()->Decrypt($this->obj->_GET['bulan_asal']);
            $requestData['bulan_tujuan'] = Dispatcher::Instance()->Decrypt($this->obj->_GET['bulan_tujuan']);
            $requestData['ta_id']      = Dispatcher::Instance()->Decrypt($this->obj->_GET['ta_id']);
            $requestData['type'] = Dispatcher::Instance()->Decrypt($this->obj->_GET['type']);
        } else {
            $requestData['kode'] = '';
            $requestData['unit_id_asal'] = 'all';
            $requestData['unit_nama_asal'] = '';
            $requestData['unit_id_tujuan'] = 'all';
            $requestData['unit_nama_tujuan'] = '';
            $requestData['bulan_asal'] = 'all';
            $requestData['bulan_tujuan'] = 'all';
            $requestData['ta_id'] = $periodeTahun[0]['id'];
            $requestData['type'] = 'asal';
        }

        $limit = 20;
        $page = 0;
        $offset = 0;

        if (isset($_GET['page'])) {
            $page = (string) $_GET['page']->StripHtmlTags()->SqlString()->Raw();
            $offset = ($page - 1) * $limit;
        }
        #pagging url
        $this->data = $this->obj->GetDataMovement(
            $requestData,
            $offset, 
            $limit
        );
        
        $num_rows = $this->obj->GetCountMovement();
        $url = Dispatcher::Instance()->GetUrl(
            Dispatcher::Instance()->mModule, 
            Dispatcher::Instance()->mSubModule, 
            Dispatcher::Instance()->mAction, 
            Dispatcher::Instance()->mType
            ) . 
            '&ta_id=' . Dispatcher::Instance()->Encrypt($requestData['ta_id']) . 
            '&kode=' . Dispatcher::Instance()->Encrypt($requestData['kode']) . 
            '&unit_id_asal=' . Dispatcher::Instance()->Encrypt($requestData['unit_id_asal']) .
            '&unit_id_tujuan=' . Dispatcher::Instance()->Encrypt($requestData['unit_id_tujuan']) .
            '&type=' . Dispatcher::Instance()->Encrypt($requestData['type']) . 
            '&bulan_asal=' . Dispatcher::Instance()->Encrypt($requestData['bulan_asal']) . 
            '&bulan_tujuan=' . Dispatcher::Instance()->Encrypt($requestData['bulan_tujuan']) . 
            '&cari=' . Dispatcher::Instance()->Encrypt(1);

        $destination_id = "subcontent-element"; #options: {popup-subcontent,subcontent-element}
        #send data to pagging component
        Messenger::Instance()->SendToComponent(
            'paging', 
            'Paging', 
            'view', 
            'html', 
            'paging_top', array(
                $limit,
                $num_rows,
                $url,
                $page,
                $destination_id
            ), 
            Messenger::CurrentRequest
        );

        Messenger::Instance()->SendToComponent(
         'combobox',
         'Combobox',
         'view',
         'html',
         'tahun_anggaran',
         array(
            'tahun_anggaran',
            $arrPeriodeTahun,
            $requestData['ta_id'],
            false,
            ' id="cmb_tahun_anggaran"'
         ), Messenger::CurrentRequest);
      
        Messenger::Instance()->SendToComponent(
            'combobox', 
            'Combobox', 
            'view', 
            'html', 
            'bulan_asal', 
            array(
                'bulan_asal',
                $months,
                $requestData['bulan_asal'],
                true,
                'id="cmb_bulan_asal"'
            ), 
            Messenger::CurrentRequest
        );

        Messenger::Instance()->SendToComponent(
            'combobox', 
            'Combobox', 
            'view', 
            'html', 
            'bulan_tujuan', 
            array(
                'bulan_tujuan',
                $months,
                $requestData['bulan_tujuan'],
                true,
                'id="cmb_bulan_tujuan"'
            ), 
            Messenger::CurrentRequest
        );
        $return['start'] = $offset + 1;
        $return['requestData'] = $requestData;
        return $return;
    }

    public function ParseTemplate($data = null) {
        $msg = $data['msg'];
        $css_box = $data['css'];
        $post = $data['data'];
        $type = $data['type'];
        if ($msg):
            $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
            $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $msg);
            $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $css_box);
        endif;

        if ($data['requestData']['type'] === 'asal') {
            $this->mrTemplate->AddVar('content', 'CHECK_ASAL', 'checked="checked"');
        } else {
            # code...
            $this->mrTemplate->AddVar('content', 'CHECK_TUJUAN', 'checked="checked"');
        }

        $kode_search = $data['kode'];
        $url_search = Dispatcher::Instance()->GetUrl('history_apbnp', 'HistoryApbnp', 'view', 'html');
        $url_detail = Dispatcher::Instance()->GetUrl('history_apbnp', 'DetailApbnp', 'view', 'html');
        $this->mrTemplate->AddVar('content', 'URL_SEARCH', $url_search);
        $this->mrTemplate->AddVar('content', 'KODE_SEARCH', $data['requestData']['kode']);
        $this->mrTemplate->AddVar('content', 'UNIT_NAMA_ASAL', $data['requestData']['unit_nama_asal']);
        $this->mrTemplate->AddVar('content', 'UNIT_ID_ASAL', $data['requestData']['unit_id_asal']);
        $this->mrTemplate->AddVar('content', 'UNIT_NAMA_TUJUAN', $data['requestData']['unit_nama_tujuan']);
        $this->mrTemplate->AddVar('content', 'UNIT_ID_TUJUAN', $data['requestData']['unit_id_tujuan']);
        
        $kode               = Dispatcher::Instance()->Encrypt($data['requestData']['kode']);
        $unit_id_asal       = Dispatcher::Instance()->Encrypt($data['requestData']['unit_id_asal']);
        $unit_id_tujuan     = Dispatcher::Instance()->Encrypt($data['requestData']['unit_id_tujuan']);
        $bulan_asal         = Dispatcher::Instance()->Encrypt($data['requestData']['bulan_asal']);
        $bulan_tujuan       = Dispatcher::Instance()->Encrypt($data['requestData']['bulan_tujuan']);
        $ta_id              = Dispatcher::Instance()->Encrypt($data['requestData']['ta_id']);
        $type               = Dispatcher::Instance()->Encrypt($data['requestData']['type']);
        
        $query = '&kode='.$kode.'&unit_id_asal='.$unit_id_asal.'&unit_id_tujuan='.$unit_id_tujuan.'&bulan_asal='.$bulan_asal.'&bulan_tujuan='.$bulan_tujuan.'&ta_id='.$ta_id.'&type='.$type;;

        $url_export    = Dispatcher::Instance()->GetUrl(
            'history_apbnp',
            'ExportHistoryApbnp',
            'view',
            'xlsx'
        ) . $query;

        $urlPopupUnit  = Dispatcher::Instance()->GetUrl(
         'history_apbnp',
         'PopupUnitKerja',
         'view',
         'html'
        );

        # url search
        $this->mrTemplate->AddVar('content', 'URL_EXPORT', $url_export);
        $this->mrTemplate->AddVar('content', 'URL_POPUP_UNIT', $urlPopupUnit);
        $this->mrTemplate->AddVar('content', 'URL_DETAIL', $url_detail);
        $dataList = $this->data;
        #$this->mrTemplate->AddVar('data_grid','DATA_EMPTY','YES');

        if (empty($dataList)) {
            $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
        } else {
            $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
            $urlAccept = 'history_apbnp|DeleteHistoryApbnp|do|json-search|' . $keyUrl . '-1|' . $valueUrl;
            $urlReturn = 'history_apbnp|HistoryApbnp|view|html-search|' . $keyUrl . '-1|' . $valueUrl;
            $label = GTFWConfiguration::GetValue('language', 'histori_movement_anggaran');
            $msg = 'Penghapusan Data ini akan menghapus Data secara permanen.';

            $url_edit = Dispatcher::Instance()->GetUrl(
                    'history_apbnp', 'UpdateHistoryApbnp', 'view', 'html'
            );
            echo '<pre>';
            //print_r($dataList);
            echo '</pre>';
            for ($i = 0; $i < count($dataList); $i++) {
                $dataList[$i]['nomor'] = $data['start'] + $i;
                $dataList[$i]['tanggal'] = date_format(date_create($dataList[$i]['tanggal']), 'Y-m-d');
                $dataList[$i]['tanggal'] = $this->_dateToIndo($dataList[$i]['tanggal']);

                if ($i % 2 != 0) {
                    $dataList[$i]['class_name'] = 'table-common-even';
                } else {
                    $dataList[$i]['class_name'] = '';
                }

                $dataList[$i]['url_delete'] = Dispatcher::Instance()->GetUrl(
                                'confirm', 'confirmDelete', 'do', 'html'
                        )
                        . '&urlDelete=' . $urlAccept
                        . '&urlReturn=' . $urlReturn
                        . '&id=' . $dataList[$i]['id']
                        . '&label=' . $label
                        . '&dataName=' . $dataList[$i]['nomor'] . '. ' . $dataList[$i]['tanggal'] . ' : ' . $dataList[$i]['unit_kerja_nama_asal']
                        . '&message=' . $msg;
                
                $dataList[$i]['nama_bulan_asal']  = $this->obj->indonesianMonth[($dataList[$i]['bulan_asal'] -1 )]['name'];
                $dataList[$i]['nama_bulan_tujuan']  = $this->obj->indonesianMonth[($dataList[$i]['bulan_tujuan'] -1 )]['name'];
                $dataList[$i]['komponen_asal_nilai'] = number_format($dataList[$i]['komponen_asal_nilai'], 2, ',', '.');
                $dataList[$i]['komponen_tujuan_nilai'] = number_format($dataList[$i]['komponen_tujuan_nilai'], 2, ',', '.');
                $dataList[$i]['nilai_asal'] = number_format($dataList[$i]['nilai_sekarang_asal'], 0, ',', '.');
                $dataList[$i]['nilai_tujuan'] = number_format($dataList[$i]['nilai_sekarang_tujuan'], 0, ',', '.');
                $dataList[$i]['url_edit'] = $url_edit . '&id=' . Dispatcher::Instance()->Encrypt($dataList[$i]['id']);

                $this->mrTemplate->AddVar('status_approval', 'STATUS', strtoupper($dataList[$i]['status_approval']));
                $this->mrTemplate->AddVar('ed_button', 'URL_EDIT', $dataList[$i]['url_edit']);
                $this->mrTemplate->AddVar('ed_button', 'URL_DELETE', $dataList[$i]['url_delete']);
                if (strtoupper($dataList[$i]['status_approval']) == 'BELUM') {
                    $this->mrTemplate->SetAttribute('ed_button', 'visibility', 'visible');
                } else {
                    $this->mrTemplate->SetAttribute('ed_button', 'visibility', 'hidden');
                }

                $this->mrTemplate->AddVars('data_list', $dataList[$i], '');
                $this->mrTemplate->parseTemplate('data_list', 'a');
            }
        }
    }

    function _dateToIndo($date) {
        $indonesian_months = array(
            'N/A',
            'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'Nopember',
            'Desember'
        );

        if (preg_match("/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})/", $date, $patch)) {
            $year = (int) $patch[1];
            $month = (int) $patch[2];
            $month = $indonesian_months[(int) $patch[2]];
            $day = (int) $patch[3];
            $hour = (int) $patch[4];
            $min = (int) $patch[5];
            $sec = (int) $patch[6];

            $return = $day . ' ' . $month . ' ' . $year;
        } elseif (preg_match("/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/", $date, $patch)) {
            $year = (int) $patch[1];
            $month = (int) $patch[2];
            $month = $indonesian_months[$month];
            $day = (int) $patch[3];

            $return = $day . ' ' . $month . ' ' . $year;
        } else {
            $return = (int) $date;
        }
        return $return;
    }

}

###
?>