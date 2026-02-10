<?php

/**
 * ================= doc ====================
 * FILENAME     : ViewPopupFpa.html.class.php
 * @package     : ViewPopupFpa
 * scope        : PUBLIC
 * @Author      : noor hadi
 * @Created     : 2016-03-07
 * @Analysts    : Dyah Fajar N
 * @copyright   : Copyright (c) 2016 Gamatechno
 * ================= doc ====================
 */
require_once GTFWConfiguration::GetValue('application','docroot').
'main/function/date.php';

require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/finansi_sppu/business/Sppu.class.php';

require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/finansi_sppu/business/popupFpa.class.php';

class ViewPopupFpa extends HtmlResponse {

    public function TemplateModule() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') .
                'module/finansi_sppu/template/');
        $this->SetTemplateFile('view_popup_fpa.html');
    }

    public function TemplateBase() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
        $this->SetTemplateFile('document-common-popup.html');
        $this->SetTemplateFile('layout-common-popup.html');
    }

    public function ProcessRequest() {
        $mObj = new PopupFpa();
        $mObjSppu = new Sppu();
        $requestData = array();
        $arrPeriodeTahun = $mObjSppu->getPeriodeTahun();
        $periodeTahun = $mObjSppu->getPeriodeTahun(array('active' => true));
        $months = $mObjSppu->indonesianMonth;

        if (isset($mObj->_POST['btnSearch'])) {
            $requestData['unit'] = trim($mObj->_POST['unit']);
            $requestData['uraian'] = trim($mObj->_POST['uraian']);
            $requestData['ta_id'] = $mObj->_POST['tahun_anggaran'];
            $requestData['bulan'] = $mObj->_POST['bulan'];
        } elseif (isset($mObj->_GET['search'])) {
            $requestData['unit'] = Dispatcher::Instance()->Decrypt($mObj->_GET['unit']);
            $requestData['uraian'] = Dispatcher::Instance()->Decrypt($mObj->_GET['uraian']);
            $requestData['ta_id'] = Dispatcher::Instance()->Decrypt($mObj->_GET['ta_id']);
            $requestData['bulan'] = Dispatcher::Instance()->Decrypt($mObj->_GET['bulan']);
        } else {
            $requestData['unit'] = '';
            $requestData['uraian'] = '';
            $requestData['ta_id'] = $periodeTahun[0]['id'];
            $requestData['bulan'] = '';
        }

        if (method_exists(Dispatcher::Instance(), 'getQueryString')) {
            # @param array
            $queryString = Dispatcher::instance()->getQueryString($requestData);
        } else {
            $query = array();
            foreach ($requestData as $key => $value) {
                $query[$key] = Dispatcher::Instance()->Encrypt($value);
            }
            $queryString = urldecode(http_build_query($query));
        }

        $offset = 0;
        $limit = 20;
        $page = 0;
        if (isset($_GET['page'])) {
            $page = (string) $_GET['page']->StripHtmlTags()->SqlString()->Raw();
            $offset = ($page - 1) * $limit;
        }
        #paging url
        $url = Dispatcher::Instance()->GetUrl(
                        Dispatcher::Instance()->mModule, 
                        Dispatcher::Instance()->mSubModule, 
                        Dispatcher::Instance()->mAction, 
                        Dispatcher::Instance()->mType
                ) . '&search=' . Dispatcher::Instance()->Encrypt(1) . '&' . $queryString;

        $destination_id = "popup-subcontent";
        $dataList = $mObj->getData($offset, $limit, (array) $requestData);
        $total_data = $mObj->Count();

        #send data to pagging component
        Messenger::Instance()->SendToComponent(
                'paging', 'Paging', 'view', 'html', 'paging_top', array(
            $limit,
            $total_data,
            $url,
            $page,
            $destination_id
                ), Messenger::CurrentRequest
        );


        # Combobox
        Messenger::Instance()->SendToComponent(
                'combobox', 'Combobox', 'view', 'html', 'tahun_anggaran', array(
            'tahun_anggaran',
            $arrPeriodeTahun,
            $requestData['ta_id'],
            false,
            'id="cmb_tahun_anggaran" style="width: 135px;"'
                ), Messenger::CurrentRequest
        );

        # Combobox
        Messenger::Instance()->SendToComponent(
                'combobox', 'Combobox', 'view', 'html', 'bulan', array(
            'bulan',
            $months,
            $requestData['bulan'],
            true,
            'id="cmb_bulan"'
                ), Messenger::CurrentRequest
        );

        $return['request_data'] = $requestData;
        $return['data_list'] = $dataList;
        $return['start'] = $offset + 1;
        return $return;
    }

    public function ParseTemplate($data = null) {
        $requestData = $data['request_data'];
        $dataList = $data['data_list'];
        $start = $data['start'];
        $unitKerja = array();
        $urlSearch = Dispatcher::Instance()->GetUrl(
                'finansi_sppu', 'PopupFpa', 'view', 'html'
        );

        $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);
        $this->mrTemplate->AddVars('content', $requestData);

        $dataFPA = array();
        if (empty($dataList)) {
            $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
        } else {
            $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');

            $program = '';
            $kegiatan = '';
            $subKegiatan = '';
            $index = 0;
            $dataRealisasi = array();
            $dataGrid = array();

            for ($i = 0; $i < count($dataList);) {
                $dataFPA[$dataList[$i]['id']] = $dataList[$i];
                $dataFPA[$dataList[$i]['id']]['tanggal'] = IndonesianDate($dataList[$i]['tanggal'], 'YYYY-MM-DD');
                
                if ((int) $dataList[$i]['program_id'] === (int) $program &&
                        (int) $dataList[$i]['kegiatan_id'] === (int) $kegiatan &&
                        (int) $dataList[$i]['sub_kegiatan_id'] === (int) $subKegiatan) {
                    $kodeSistemProgram = $program;
                    $kodeSistemKegiatan = $program . '.' . $kegiatan;
                    $dataRealisasi[$kodeSistemProgram]['nominal_usulan'] += $dataList[$i]['nominal_detail_belanja_usulan'];
                    $dataRealisasi[$kodeSistemProgram]['nominal_setuju'] += $dataList[$i]['nominal_detail_belanja_setuju'];

                    $dataRealisasi[$kodeSistemKegiatan]['nominal_usulan'] += $dataList[$i]['nominal_detail_belanja_usulan'];
                    $dataRealisasi[$kodeSistemKegiatan]['nominal_setuju'] += $dataList[$i]['nominal_detail_belanja_setuju'];

                    //$dataGrid[$index]['nomor']    = $start;
                    $dataGrid[$index]['id'] = $dataList[$i]['id'];
                    //$dataGrid[$index]['tanggal']  = date('Y-m-d', strtotime($dataList[$i]['tanggal']));
                    $dataGrid[$index]['kode'] = $dataList[$i]['kode_akun'];
                    $dataGrid[$index]['nama'] = $dataList[$i]['detail_belanja_nama'];
                    $dataGrid[$index]['nominal_usulan'] = $dataList[$i]['nominal_detail_belanja_usulan'];
                    $dataGrid[$index]['nominal_setuju'] = $dataList[$i]['nominal_detail_belanja_setuju'];
                    //$dataGrid[$index]['status']         = strtoupper($dataList[$i]['status']);
                    //$dataGrid[$index]['spp']            = $dataList[$i]['spp'];
                    //$dataGrid[$index]['spp_id']         = $dataList[$i]['spp_id'];
                    //$dataGrid[$index]['spm']            = $dataList[$i]['spm'];
                    //$dataGrid[$index]['spm_id']         = $dataList[$i]['spm_id'];
                    $dataGrid[$index]['tipe'] = 'detail';
                    $dataGrid[$index]['class_name'] = ($start - 1 ) % 2 <> 0 ? 'table-common-even' : '';
                    $dataGrid[$index]['row_style'] = '';
                    $i++;
                } elseif ((int) $dataList[$i]['program_id'] === (int) $program &&
                        (int) $dataList[$i]['kegiatan_id'] === (int) $kegiatan &&
                        (int) $dataList[$i]['sub_kegiatan_id'] !== (int) $subKegiatan) {
                    $subKegiatan = (int) $dataList[$i]['sub_kegiatan_id'];
                    $kodeSistemProgram = $program;
                    $kodeSistemKegiatan = $program . '.' . $kegiatan;
                    $kodeSistemSubKegiatan = $program . '.' . $kegiatan . '.' . $subKegiatan;
                    $dataRealisasi[$kodeSistemProgram]['nominal_usulan'] += $dataList[$i]['nominal_usulan'];
                    $dataRealisasi[$kodeSistemProgram]['nominal_setuju'] += $dataList[$i]['nominal_setuju'];

                    $dataRealisasi[$kodeSistemKegiatan]['nominal_usulan'] += $dataList[$i]['nominal_usulan'];
                    $dataRealisasi[$kodeSistemKegiatan]['nominal_setuju'] += $dataList[$i]['nominal_setuju'];

                    $dataGrid[$index]['nomor'] = $start;
                    $dataGrid[$index]['id'] = $dataList[$i]['id'];
                    $dataGrid[$index]['tanggal'] =  IndonesianDate($dataList[$i]['tanggal'], 'YYYY-MM-DD');
                    $dataGrid[$index]['kode'] = $dataList[$i]['sub_kegiatan_kode'];
                    $dataGrid[$index]['nama'] = $dataList[$i]['sub_kegiatan_nama'];
                    $dataGrid[$index]['lk'] = $dataList[$i]['lingkup_komponen'];
                    $dataGrid[$index]['unit_nama'] = $dataList[$i]['unit_nama'];
                    $dataGrid[$index]['nominal_usulan'] = $dataList[$i]['nominal_usulan'];
                    $dataGrid[$index]['nominal_setuju'] = $dataList[$i]['nominal_setuju'];
                    $dataGrid[$index]['status'] = strtoupper($dataList[$i]['status']);
                    $dataGrid[$index]['spp'] = $dataList[$i]['spp'];
                    $dataGrid[$index]['spp_id'] = $dataList[$i]['spp_id'];
                    $dataGrid[$index]['spm'] = $dataList[$i]['spm'];
                    $dataGrid[$index]['spm_id'] = $dataList[$i]['spm_id'];
                    $dataGrid[$index]['tipe'] = 'sub_kegiatan';
                    $dataGrid[$index]['class_name'] = $start % 2 <> 0 ? 'table-common-even' : '';
                    //$dataGrid[$index]['class_name']     = 'table-common-even';
                    $dataGrid[$index]['row_style'] = '';
                    $start++;
                } elseif ((int) $dataList[$i]['program_id'] === (int) $program && (int) $dataList[$i]['kegiatan_id'] !== (int) $kegiatan) {
                    $kegiatan = (int) $dataList[$i]['kegiatan_id'];
                    $kodeSistem = $program . '.' . $kegiatan;
                    $dataRealisasi[$kodeSistem]['nominal_usulan'] = 0;
                    $dataRealisasi[$kodeSistem]['nominal_setuju'] = 0;

                    $dataGrid[$index]['id'] = $dataList[$i]['kegiatan_id'];
                    $dataGrid[$index]['kode'] = $dataList[$i]['kegiatan_kode'];
                    $dataGrid[$index]['nama'] = $dataList[$i]['kegiatan_nama'];
                    $dataGrid[$index]['kode_sistem'] = $kodeSistem;
                    $dataGrid[$index]['tipe'] = 'kegiatan';
                    $dataGrid[$index]['class_name'] = 'table-common-even2';
                    $dataGrid[$index]['row_style'] = '';
                } else {
                    $program = (int) $dataList[$i]['program_id'];
                    $kodeSistem = $program;
                    $dataRealisasi[$kodeSistem]['nominal_usulan'] = 0;
                    $dataRealisasi[$kodeSistem]['nominal_setuju'] = 0;

                    $dataGrid[$index]['id'] = $dataList[$i]['program_id'];
                    $dataGrid[$index]['kode'] = $dataList[$i]['program_kode'];
                    $dataGrid[$index]['nama'] = $dataList[$i]['program_nama'];
                    $dataGrid[$index]['kode_sistem'] = $kodeSistem;
                    $dataGrid[$index]['tipe'] = 'program';
                    $dataGrid[$index]['class_name'] = 'table-common-even1';
                    $dataGrid[$index]['row_style'] = 'font-weight: bold;';
                }
                $index++;
            }

            foreach ($dataGrid as $list) {
                $this->mrTemplate->clearTemplate('data_checkbox');
                $this->mrTemplate->SetAttribute('data_checkbox', 'visibility', 'hidden');
                switch (strtoupper($list['tipe'])) {
                    case 'PROGRAM':
                        $this->mrTemplate->SetAttribute('content_deskripsi', 'visibility', 'hidden');
                        $list['nominal_usulan'] = number_format($dataRealisasi[$list['kode_sistem']]['nominal_usulan'], 0, ',', '.');
                        $list['nominal_setuju'] = number_format($dataRealisasi[$list['kode_sistem']]['nominal_setuju'], 0, ',', '.');
                        break;
                    case 'KEGIATAN':
                        $this->mrTemplate->SetAttribute('content_deskripsi', 'visibility', 'hidden');
                        $list['nominal_usulan'] = number_format($dataRealisasi[$list['kode_sistem']]['nominal_usulan'], 0, ',', '.');
                        $list['nominal_setuju'] = number_format($dataRealisasi[$list['kode_sistem']]['nominal_setuju'], 0, ',', '.');
                        break;
                    case 'DETAIL':
                        $this->mrTemplate->SetAttribute('content_deskripsi', 'visibility', 'hidden');
                        $list['nominal_usulan'] = number_format($list['nominal_usulan'], 0, ',', '.');
                        $list['nominal_setuju'] = number_format($list['nominal_setuju'], 0, ',', '.');
                        break;
                    case 'SUB_KEGIATAN':
                        $this->mrTemplate->SetAttribute('content_deskripsi', 'visibility', 'visible');
                        $list['nominal_usulan'] = number_format($list['nominal_usulan'], 0, ',', '.');
                        $list['nominal_setuju'] = number_format($list['nominal_setuju'], 0, ',', '.');

                        $this->mrTemplate->AddVar('content_deskripsi', 'LK', $list['lk']);
                        $this->mrTemplate->SetAttribute('data_checkbox', 'visibility', 'show');
                        $this->mrTemplate->AddVar('data_checkbox', 'ID', $list['id']);
                        break;
                }
                $this->mrTemplate->AddVars('data_list', $list);
                $this->mrTemplate->parseTemplate('data_list', 'a');
            }
        }

        $dataFpaJson = json_encode($dataFPA);
        $this->mrTemplate->AddVar('content', 'DATA_FPA', $dataFpaJson);
    }

}

?>