<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/finansi_coa_jenis_biaya/business/PopupJenisBiaya.class.php';

class ViewPopupJenisBiaya extends HtmlResponse {

    public function TemplateModule() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') .
                'module/finansi_coa_jenis_biaya/template'
        );
        $this->SetTemplateFile('view_popup_jenis_biaya.html');
    }

    public function TemplateBase() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') .
                'main/template/');
        $this->SetTemplateFile('document-common-popup.html');
        $this->SetTemplateFile('layout-common-popup.html');
    }

    public function ProcessRequest() {
        $mObj = new PopupJenisBiaya();

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
        $dataList = $mObj->GetData();
        $total_data = $mObj->GetCount();
        #send data to pagging component
        Messenger::Instance()->SendToComponent(
            'paging', 'Paging', 'view', 'html', 'paging_top', array(
            $limit,
            $total_data,
            $url,
            $page,
            $destination_id
            ), 
            Messenger::CurrentRequest
        );

        $return['get_jb_id'] = $mObj->GetJenisBiayaId();
        $return['request_data'] = $requestData;
        $return['query_string'] = $queryString;
        $return['data_list'] = $dataList['data_list'] ;
        $return['start'] = $offset + 1;
        return $return;
    }

    public function ParseTemplate($data = NULL) {
        $dataList = $data['data_list'];
        $start = $data['start'];
        $requestData = $data['request_data'];
        $queryString = $data['query_string'];
        
        $getJBId = $data['get_jb_id'];
        
        $urlSearch = Dispatcher::Instance()->GetUrl(
                'finansi_coa_jenis_biaya', 'popupJenisBiaya', 'view', 'html'
        );

        $this->mrTemplate->AddVars('content', $requestData);
        $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);

        if (empty($dataList)) {
            $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
        } else {
            $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
            foreach ($dataList as $list) {
                $list['nomor'] = $start;
                $list['id'] = $list['jenisBiayaId'];
                $list['nama'] = $list['jenisBiayaNama'];
                if(in_array( $list['jenisBiayaId'],$getJBId)) {
                     $this->mrTemplate->SetAttribute('jb_pilih', 'visibility', 'hidden');
                } else {
                    $this->mrTemplate->SetAttribute('jb_pilih', 'visibility', 'visible');
                }
                $this->mrTemplate->AddVar('jb_pilih', 'ID',$list['jenisBiayaId']);
                $this->mrTemplate->AddVar('jb_pilih', 'NAMA', $list['jenisBiayaNama']);
                $this->mrTemplate->AddVars('data_list', $list);
                $this->mrTemplate->parseTemplate('data_list', 'a');
                $start++;
            }
        }
    }

}

?>