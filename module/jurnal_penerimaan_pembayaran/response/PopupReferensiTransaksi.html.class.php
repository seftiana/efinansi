<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/jurnal_penerimaan/business/AppReferensiTransaksi.class.php';

class PopupReferensiTransaksi extends HtmlResponse {

    function TemplateModule() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') .
                'module/jurnal_penerimaan/template/');
        $this->SetTemplateFile('popup_referensi_transaksi.html');
    }

    function TemplateBase() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
        $this->SetTemplateFile('document-common-popup.html');
        $this->SetTemplateFile('layout-common-popup.html');
    }

    function ProcessRequest() {
        $mObj = new AppReferensiTransaksi();
        $requestData = array();
        $tahunPembukuan = $mObj->getTahunPembukuan(true);
        $tahunAnggaran = $mObj->getTahunAnggaran(array('active' => true));        
        $bulan            = $mObj->getBulan();

        $requestData['ta_id'] = $tahunAnggaran[0]['id'];
        $requestData['ta_nama'] = $tahunAnggaran[0]['name'];
        $requestData['tp_id'] = $tahunPembukuan[0]['id'];
        $requestData['tp_nama'] = $tahunPembukuan[0]['name'];

        if (isset($mObj->_POST['btnSearch'])) {
            $requestData['kode'] = trim($mObj->_POST['kode']);
            $requestData['bulan'] = trim($mObj->_POST['bulan']);
        } elseif (isset($mObj->_GET['search'])) {
            $requestData['kode'] = Dispatcher::Instance()->Decrypt($mObj->_GET['kode']);
            $requestData['bulan'] = Dispatcher::Instance()->Decrypt($mObj->_GET['bulan']);
        } else {
            $requestData['kode'] = '';
            $requestData['bulan'] = 'all';
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
                        Dispatcher::Instance()->mModule, Dispatcher::Instance()->mSubModule, Dispatcher::Instance()->mAction, Dispatcher::Instance()->mType
                ) . '&search=' . Dispatcher::Instance()->Encrypt(1) . '&' . $queryString;

        $destination_id = "popup-subcontent";
        $dataList = $mObj->getDataReferensiTransaksi($offset, $limit, (array) $requestData);
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


        Messenger::Instance()->SendToComponent(
         'combobox',
         'Combobox',
         'view',
         'html',
         'bulan',
         array(
            'bulan',
            $bulan,
            $requestData['bulan'],
            true,
            'id="cmb_month"'
         ), Messenger::CurrentRequest);
      
        $return['query_string'] = $queryString;
        $return['request_data'] = $requestData;
        $return['data_list'] = $dataList;
        $return['start'] = $offset + 1;
        return $return;
    }

    function ParseTemplate($data = null) {
        $requestData = $data['request_data'];
        $dataList = $data['data_list'];
        $start = $data['start'];
        $object = array();
        $transaksi = array();
        $dataCoa = array();
        $urlSearch = Dispatcher::Instance()->GetUrl(
                'jurnal_penerimaan', 'ReferensiTransaksi', 'popup', 'html'
        );


        $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);
        $this->mrTemplate->AddVars('content', $requestData);

        if (empty($dataList)) {
            $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
        } else {
            $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
            $index = 0;
            foreach ($dataList as $list) {
                $transaksi[$list['id']] = $list;
                if(!empty($list['coa_id'])) {
                    $dataCoa[$list['id']][] = array(
                        'coa_id'                => $list['coa_id'],
                        'coa_is_debet_positif'  => $list['coa_is_debet_positif'],
                        'coa_kode'              => $list['coa_kode_akun'],
                        'coa_nama'              => $list['coa_nama_akun'],
                        'nominal'               => $list['nominal']
                    );
                }
                $list['nomor'] = $start;
                $list['class_name'] = ($start % 2 <> 0) ? 'table-common-even' : '';
                $list['nominal'] = number_format($list['nominal'], 2, ',', '.');

                $this->mrTemplate->AddVars('data_list', $list);
                $this->mrTemplate->parseTemplate('data_list', 'a');
                $start++;
                $index++;
            }
        }

        $object['transaksi']['data'] = json_encode($transaksi);
        $object['coa']['data'] = json_encode($dataCoa);
        $this->mrTemplate->AddVars('content', $object['transaksi'], 'TRANSAKSI_');
        $this->mrTemplate->AddVars('content', $object['coa'], 'COA_');
    }

}

?>