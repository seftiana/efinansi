<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/finansi_coa_jenis_biaya/business/AppPopupCoa.class.php';

class PopupCoa extends HtmlResponse {

    protected $data;
    protected $search;
    protected $Coa;

    function PopupCoa() { //constructor
        $this->Coa = new AppPopupCoa;
    }

    function TemplateModule() {
        $this->SetTemplateBasedir($this->mrConfig->mApplication['docroot'] .
                'module/finansi_coa_jenis_biaya/template');
        $this->SetTemplateFile('popup_coa.html');
    }

    function TemplateBase() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
        $this->SetTemplateFile('document-common-popup.html');
        $this->SetTemplateFile('layout-common-popup.html');
    }

    function ProcessRequest() {
        $this->data['kode'] = '';
        $this->data['nama'] = '';
        $status = (is_object($_GET['st']) ? $_GET['st']->mrVariable : $_GET['st']) ;
        if (isset($_POST['data'])) {
            if (is_object($_POST['data']))
                $this->data = $_POST['data']->AsArray();
            else
                $this->data = $_POST['data'];
        }elseif (isset($_GET['data'])) {
            if (is_object($_GET['data']))
                $this->data = $_GET['data']->AsArray();
            else
                $this->data = $_GET['data'];
        }




        $itemViewed = 20;
        $currPage = 1;
        $startRec = 0;
        if (isset($_GET['page'])) {
            $currPage = (string) $_GET['page']->StripHtmlTags()->SqlString()->Raw();
            $startRec = ($currPage - 1) * $itemViewed;
        }

        $dataGrid = $this->Coa->GetDataCoa($startRec, $itemViewed, $this->data['kode'], $this->data['nama']);
        $totalData = $this->Coa->GetCountCoa($this->data['kode'], $this->data['nama']);

        for ($i = 0; $i < count($dataGrid); $i++) {
            $unitkerja = $this->Coa->GetUnitkerjaById($dataGrid[$i]['unitkerja']);
            $dataGrid[$i]['nama_unitkerja'] = $unitkerja['nama'];
        }


        //$dataProgram = $ProgramObj->GetDataProgram($startRec,$itemViewed, $this->data['program'],$is_cari);

        $url = Dispatcher::Instance()->GetUrl(
                Dispatcher::Instance()->mModule, 
                Dispatcher::Instance()->mSubModule, 
                Dispatcher::Instance()->mAction, 
                Dispatcher::Instance()->mType) . 
                '&data[kode]=' . $this->data['kode'] . 
                '&data[nama]=' . $this->data['nama'].
                '&st='.$status;

        Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top', array($itemViewed, $totalData, $url, $currPage, "popup-subcontent"), Messenger::CurrentRequest);

        $return['dataGrid'] = $dataGrid;
        $return['start'] = $startRec + 1;
        $return['status'] = $status;

        return $return;
    }

    function ParseTemplate($data = NULL) {
        
        $this->mrTemplate->AddVar('content', 'SEARCH_KODE', $this->data['kode']);
        $this->mrTemplate->AddVar('content', 'SEARCH_NAMA', $this->data['nama']);
        $this->mrTemplate->AddVar('content', 'URL_SEARCH', 
                Dispatcher::Instance()->GetUrl('finansi_coa_jenis_biaya', 'coa', 'popup', 'html').'&st='.$data['status']
        );
        if (isset($data['msg'])) {
            $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
            $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $data['msg']['message']);
            if ($data['msg']['action'] == 'msg')
                $class = 'notebox-done';
            else
                $class = 'notebox-warning';

            $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
            $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $data['msg']['message']);
            $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $class);
        }



        if (empty($data['dataGrid'])) {
            $this->mrTemplate->AddVar('data_grid', 'IS_EMPTY', 'YES');
        } else {


            $this->mrTemplate->AddVar('data_grid', 'IS_EMPTY', 'NO');
            $dataGrid = $data['dataGrid'];
            $i = 0;
            $no = $data['start'];
            for ($i = 0; $i < sizeof($dataGrid); $i++) {
                $dataGrid[$i]['no'] = $no;
                $no++;

                if (!$dataGrid[$i]['isParent']) {
                    $dataGrid[$i]['class_name'] = 'table-common-even';
                } else {
                    $dataGrid[$i]['class_name'] = '';
                }

                //$idEnc = Dispatcher::Instance()->Encrypt($dataGrid[$i]['program_id']);			   
                $dataGrid[$i]['status']= $data['status'];
                $this->mrTemplate->AddVars('data_item', $dataGrid[$i], 'DATA_');
                $this->mrTemplate->parseTemplate('data_item', 'a');
            }
        }
    }

}

?>