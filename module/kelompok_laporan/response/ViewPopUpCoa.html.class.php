<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/kelompok_laporan/business/AppPopupCoa.class.php';

class ViewPopUpCoa extends HtmlResponse {

    protected $mObj;

    public function TemplateModule() {
        $this->SetTemplateBasedir(
            GTFWConfiguration::GetValue('application', 'docroot') . 
            'module/kelompok_laporan/template'
        );
        $this->SetTemplateFile('view_popup_coa.html');
    }

    public function ProcessRequest() {
        $this->mObj = new AppPopupCoa();
        $requestData   = array();
        $queryString   = '';

        if (isset($this->mObj->_POST['btncari'])) {
            $requestData['kode'] = trim($this->mObj->_POST['kode']);
            $requestData['nama'] = trim($this->mObj->_POST['nama']);
        } elseif (isset($this->mObj->_GET['search'])) {
            $requestData['kode'] = Dispatcher::Instance()->Decrypt($this->mObj->_GET['kode']);
            $requestData['nama'] = Dispatcher::Instance()->Decrypt($this->mObj->_GET['nama']);
        } else {
            $requestData['kode'] = '';
            $requestData['nama'] = '';
      }

      if(method_exists(Dispatcher::Instance(), 'getQueryString')){
         # @param array
         $queryString      = Dispatcher::instance()->getQueryString($requestData);
      }else{
         $query            = array();
         foreach ($requestData as $key => $value) {
            $query[$key]   = Dispatcher::Instance()->Encrypt($value);
         }
         $queryString      = urldecode(http_build_query($query));
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
        ).'&search='.Dispatcher::Instance()->Encrypt(1).'&'.$queryString;

        $destination_id = "popup-subcontent";
        $dataList = $this->mObj->getDataCoa($offset, $limit, (array) $requestData);
        
        $total_data = $this->mObj->Count();
        #send data to pagging component
        Messenger::Instance()->SendToComponent(
            'paging', 
            'Paging', 
            'view', 
            'html', 
            'paging_top', 
            array(
                $limit,
                $total_data,
                $url,
                $page,
                $destination_id
            ), 
            Messenger::CurrentRequest
        );

        $return['data_list'] = $dataList;
        $return['request_data'] = $requestData;
        $return['start'] = $offset + 1;
        $return['request_query'] = $queryString;
        

        return $return;
    }

    public function ParseTemplate($data = null) {
        $requestData = $data['request_data'];
        $dataList = $data['data_list'];
        $start = $data['start'];
        $requestQuery = $data['request_query'];
        $dataCoa = array();
        $urlSearch = Dispatcher::Instance()->GetUrl(
            'kelompok_laporan', 
            'PopUpCoa', 
            'view', 
            'html'
            ) . 
            '&' . $requestQuery;

        $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);
        $this->mrTemplate->AddVars('content', $requestData);

        if (empty($dataList)) {
            $this->mrTemplate->AddVar('data_grid', 'IS_EMPTY', 'YES');
        } else {
            $this->mrTemplate->AddVar('data_grid', 'IS_EMPTY', 'NO');
            foreach ($dataList as $list) {
                $this->mrTemplate->clearTemplate('content_links');
                $dataCoa[$list['id']] = $list;
                $list['nomor'] = $start;
                $list['class_name'] = ($start % 2 <> 0) ? 'table-common-even' : '';
                $this->mrTemplate->AddVars('data_item', $list);
                $this->mrTemplate->parseTemplate('data_item', 'a');
                $start++;
            }
        }

        $dataJson['data'] = json_encode($dataCoa);
        $this->mrTemplate->AddVars('content', $dataJson, 'COA_');
    }

}

?>