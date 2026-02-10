<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') .
    'module/kelompok_laporan/business/PopupKlpLaporan.class.php';

class ViewPopupKlpLaporan extends HtmlResponse {

    protected $mObj;

    public function TemplateModule() {
        $this->SetTemplateBasedir(
            GTFWConfiguration::GetValue('application', 'docroot') . 
            'module/kelompok_laporan/template'
        );
        $this->SetTemplateFile('view_popup_klp_laporan.html');
    }

    public function ProcessRequest() {
        
        $this->mObj = new PopupKlpLaporan();
        $getComboRoot = $this->mObj->getKlpRoot();
        $requestData   = array();
        $queryString   = '';

        if (isset($this->mObj->_POST['btncari'])) {
            $requestData['nama'] = trim($this->mObj->_POST['nama']);
            $requestData['jns_lap']  = $this->mObj->_POST['jns_lap'];
        } elseif (isset($this->mObj->_GET['search'])) {
            $requestData['nama'] = Dispatcher::Instance()->Decrypt($this->mObj->_GET['nama']);
            $requestData['jns_lap'] = Dispatcher::Instance()->Decrypt($this->mObj->_GET['jns_lap']);
        } else {
            $requestData['nama'] = '';
            $requestData['jns_lap'] = $getComboRoot[0]['id'];
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

        $this->mObj->getData( (array) $requestData);
        $dataList = $this->mObj->GetLaporan();
        
        
        # Combobox
        Messenger::Instance()->SendToComponent(
           'combobox',
           'Combobox',
           'view',
           'html',
           'jns_lap',
           array(
              'jns_lap',
              $getComboRoot,
              $requestData['jns_lap'],
              false,
              'style="width: 135px;" id="cmb_jenis_laporan"'
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
        $dataKlp = array();
        $urlSearch = Dispatcher::Instance()->GetUrl(
            'kelompok_laporan', 
            'PopupKlpLaporan', 
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
                if($list['level'] == 1){
                    continue;
                }
                $list['padding'] = ($list['level'] - 1) * 15;
                         
                if($list['is_summary'] == 'Y'  || $list['is_child'] == '0') {
                    $list['summary_style'] ='normal';
                    $list['summary_style_b'] ='bold';
                } else {
                    $list['summary_style'] ='normal';
                    $list['summary_style_b'] ='normal';
                }
                $dataKlp[$list['id']] = $list;
                $list['nomor'] = $start;
                $list['class_name'] = ($start % 2 <> 0) ? 'table-common-even' : '';
                $this->mrTemplate->AddVars('data_item', $list);
                $this->mrTemplate->parseTemplate('data_item', 'a');
                $start++;
            }
        }

        $dataJson['data'] = json_encode($dataKlp);
        $this->mrTemplate->AddVars('content', $dataJson, 'KLP_');
    }

}

?>