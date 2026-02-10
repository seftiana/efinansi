<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/finansi_transaksi_penerimaan_bank/business/PopupSkenarioKodeJurnal.class.php';

class ViewPopupSkenarioKodeJurnal extends HtmlResponse {

    public function TemplateModule() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') .
                'module/finansi_transaksi_penerimaan_bank/template'
        );
        $this->SetTemplateFile('view_popup_skenario_kode_jurnal.html');
    }

    public function TemplateBase() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') .
                'main/template/');
        $this->SetTemplateFile('document-common-popup.html');
        $this->SetTemplateFile('layout-common-popup.html');
    }

    public function ProcessRequest() {
        $mObj = new PopupSkenarioKodeJurnal();
        $requestData = array();
        $queryString = '';

        if (isset($mObj->_POST['btncari'])) {
            $requestData['nama'] = $mObj->_POST['nama'];
        } elseif (isset($mObj->_GET['search'])) {
            $requestData['nama'] = Dispatcher::Instance()->Decrypt($mObj->_GET['nama']);
        } else {
            $requestData['nama'] = '';
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
                ) . 
                '&search=' . Dispatcher::Instance()->Encrypt(1) . 
                '&' . $queryString;

        $destination_id = "popup-subcontent";
        $dataList = $mObj->getData($offset, $limit, (array) $requestData);
        // echo "<pre>";
        // print_r($dataList);exit();
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

     
        $return['request_data'] = $requestData;
        $return['query_string'] = $queryString;
        $return['data_list'] = $mObj->ChangeKeyName($dataList);
        $return['start'] = $offset + 1;
        return $return;
    }

    public function ParseTemplate($data = NULL) {
        $dataList = $data['data_list'];
        $start = $data['start'];
        $requestData = $data['request_data'];
        $queryString = $data['query_string'];
        $urlSearch = Dispatcher::Instance()->GetUrl(
                    Dispatcher::Instance()->mModule, 
                    Dispatcher::Instance()->mSubModule, 
                    Dispatcher::Instance()->mAction, 
                    Dispatcher::Instance()->mType
        );

        $this->mrTemplate->AddVars('content', $requestData);
        $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);
        $dataCoa = array();
        if (empty($dataList)) {
            $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
        } else {
            $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
            $idKodeJurnal = NULL;
            $skj = array();
            $idx = 0;
            $nomor = 1 ;
           
            for ($i=0; $i < sizeof($dataList);) {
                if($dataList[$i]['jk_id'] == $idKodeJurnal) {
                    $dataCoa[$dataList[$i]['jk_id']][] = array(
                        'coa_id' => $dataList[$i]['coa_id'],
                        'coa_kode' => $dataList[$i]['coa_kode'],
                        'coa_nama' => $dataList[$i]['coa_nama'],
                        'is_debet' => $dataList[$i]['is_debet']
                    );
                    
                    $skj[$idx]['nomor'] = '';
                    $skj[$idx]['id'] = '';
                    $skj[$idx]['kode'] = $dataList[$i]['coa_kode'];
                    $skj[$idx]['nama'] = $dataList[$i]['coa_nama'];
                    $skj[$idx]['style'] = 'display:none';
                    $skj[$idx]['style_row'] = '';
                    $skj[$idx]['class_name'] = '';
                    if($dataList[$i]['is_debet'] == 1) {
                        $this->mrTemplate->SetAttribute('is_debet', 'visibility', 'visible');
                        $this->mrTemplate->SetAttribute('is_kredit', 'visibility', 'hidden');
                    } else {
                        $this->mrTemplate->SetAttribute('is_debet', 'visibility', 'hidden');
                        $this->mrTemplate->SetAttribute('is_kredit', 'visibility', 'visible');
                    }
                    $this->mrTemplate->SetAttribute('is_accrual', 'visibility', 'hidden');
                    $this->mrTemplate->SetAttribute('is_cash', 'visibility', 'hidden');
                    $this->mrTemplate->AddVars('data_list', $skj[$idx]);
                    $this->mrTemplate->parseTemplate('data_list', 'a');
                    $i++;
                } elseif($dataList[$i]['jk_id'] != $idKodeJurnal) {
                    $idKodeJurnal = $dataList[$i]['jk_id'];
                    $skj[$idx]['nomor'] = $nomor;
                    $skj[$idx]['id'] = $dataList[$i]['jk_id'];
                    $skj[$idx]['kode'] = $dataList[$i]['jk_kode'];
                    $skj[$idx]['nama'] = $dataList[$i]['jk_nama'];
                    $skj[$idx]['jk_jb_id'] = $dataList[$i]['jk_jb_id'];
                    $skj[$idx]['jk_jb_nama'] = $dataList[$i]['jk_jb_nama'];
                    $skj[$idx]['jk_mode_catat'] = $dataList[$i]['jk_mode_catat'];
                    $skj[$idx]['style'] = 'display:block;';
                    $skj[$idx]['style_row'] = 'font-weight:bold;';
                    $skj[$idx]['class_name'] = 'table-common-even1';
                    if($dataList[$i]['jk_mode_catat'] == 'accrual') {
                        $this->mrTemplate->SetAttribute('is_accrual', 'visibility', 'visible');
                        $this->mrTemplate->SetAttribute('is_cash', 'visibility', 'hidden');
                    } else {
                        $this->mrTemplate->SetAttribute('is_accrual', 'visibility', 'hidden');
                        $this->mrTemplate->SetAttribute('is_cash', 'visibility', 'visible');
                    }
                    $this->mrTemplate->SetAttribute('is_debet', 'visibility', 'hidden');
                    $this->mrTemplate->SetAttribute('is_kredit', 'visibility', 'hidden');
                    $this->mrTemplate->AddVars('data_list', $skj[$idx]);
                    $this->mrTemplate->parseTemplate('data_list', 'a');
                     $nomor++;
                }
                $idx++;
            }
                     
        }
        if(!empty($dataCoa)) {
          $this->mrTemplate->AddVar('content', 'DATA_COA', json_encode($dataCoa));
        } else {
          $this->mrTemplate->AddVar('content',  'DATA_COA','null');
        }   
    }
}

?>