<?php

require_once GTFWConfiguration::GetValue('application', 'docroot'). 'module/jurnal_penerimaan_pembayaran/business/AppReferensiPembayaranMahasiswa.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot'). 'module/jurnal_penerimaan_pembayaran/business/AppReferensiPembayaranEfinansi.class.php';

class PopupReferensiPembayaranMahasiswa extends HtmlResponse {

    function TemplateModule() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') .
                'module/jurnal_penerimaan_pembayaran/template/');
        $this->SetTemplateFile('popup_referensi_pembayaran_mahasiswa.html');
    }

    function TemplateBase() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
        $this->SetTemplateFile('document-common-popup.html');
        $this->SetTemplateFile('layout-common-popup.html');
    }

    function ProcessRequest() {
        $mObj = new AppReferensiPembayaranMahasiswa();
        $requestData = array();   
        // $tahun            = $mObj->getTahunPembayaran();
        // $bulan            = $mObj->getBulan();

        if (isset($mObj->_POST['btnSearch'])) {
            $requestData['referensi_tanggal_day'] = trim($mObj->_POST['referensi_tanggal_day']);
            $requestData['referensi_tanggal_mon'] = trim($mObj->_POST['referensi_tanggal_mon']);
            $requestData['referensi_tanggal_year'] = trim($mObj->_POST['referensi_tanggal_year']);
        } elseif (isset($mObj->_GET['search'])) {
            $requestData['referensi_tanggal_day'] = Dispatcher::Instance()->Decrypt($mObj->_GET['referensi_tanggal_day']);
            $requestData['referensi_tanggal_mon'] = Dispatcher::Instance()->Decrypt($mObj->_GET['referensi_tanggal_mon']);
            $requestData['referensi_tanggal_year'] = Dispatcher::Instance()->Decrypt($mObj->_GET['referensi_tanggal_year']);
        } else {
            $requestData['referensi_tanggal_day'] = date('d');
            $requestData['referensi_tanggal_mon'] = date('m');
            $requestData['referensi_tanggal_year'] = date('Y');
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
        $dataList = $mObj->getDataReferensiPembayaran($offset, $limit, (array) $requestData);
        $total_data = $mObj->getCount();
	
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
	
		# GTFW Tanggal
        Messenger::Instance()->SendToComponent(
            'tanggal', 'Tanggal', 'view', 'html', 'referensi_tanggal', array(
				$requestData['referensi_tanggal_year'].'-'.$requestData['referensi_tanggal_mon'].'-'.$requestData['referensi_tanggal_day'],
				$minYear,
				$maxYear,
				false,
				false,
				false
            ), Messenger::CurrentRequest
        );
      
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
                'jurnal_penerimaan_pembayaran', 'ReferensiPembayaranMahasiswa', 'popup', 'html'
        );


        $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);
        $this->mrTemplate->AddVars('content', $requestData);

        if (empty($dataList)) {
            $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
        } else {
			$mEfi = new AppReferensiPembayaranEfinansi();
			
            $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
            $index = 0;
            foreach ($dataList as $list) {
                $transaksi[$list['id']] = $list;
				$getCoa = $mEfi->GetCoaDebet($list['coa_id']); 
				$dataCoa[$list['id']][] = array(
                        'coa_id'                => $getCoa[0]['coaId'],
                        'coa_is_debet_positif'  => $getCoa[0]['coa_is_debet_positif'],
                        'coa_kode'              => $getCoa[0]['coaKodeAkun'],
                        'coa_nama'              => $getCoa[0]['coaNamaAkun'],
                        'nominal'               => $list['real_bayar']
                );
        
                $list['akun_coa'] = $getCoa[0]['coaKodeAkun'];
                $list['nama_coa'] = $getCoa[0]['coaNamaAkun'];
				
                $list['nomor'] = $start;
                $list['class_name'] = ($start % 2 <> 0) ? 'table-common-even' : '';
                $list['nominal'] = number_format($list['real_bayar'], 0, ',', '.');
                $list['real_bayar'] = number_format($list['real_bayar'], 0, ',', '.');

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