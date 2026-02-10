<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') . 
    'module/kelompok_laporan/business/AppPopupCoa.class.php';

class ViewPopupCoa extends HtmlResponse {

    protected $data;
    protected $search;
    protected $Coa;

    function ViewPopupCoa() { //constructor
        $this->Coa = new AppPopupCoa;
    }

    function TemplateModule() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'module/kelompok_laporan/template');
        $this->SetTemplateFile('view_popup_coa.html');
    }

    function TemplateBase() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
        $this->SetTemplateFile('document-common-popup.html');
        $this->SetTemplateFile('layout-common-popup.html');
    }

    function ProcessRequest() {
        $this->data['nama'] = '';
        $this->data['kode'] = '';

        if (!isset($_GET['tipe'])) {
            echo "gagal membukan popup akun, silahkan ulangi lagi";
            exit;
        } else {

            if (is_object($_GET['tipe'])) {
                $return['tipe'] = $_GET['tipe']->mrVariable;
            } else {
                $return['tipe'] = $_GET['tipe'];
            }
        }

        if (isset($_POST['data'])) {

            if (is_object($_POST['data'])) {
                $this->data = $_POST['data']->AsArray();
            } else {
                $this->data = $_POST['data'];
            }
        }
        elseif (isset($_GET['data'])) {

            if (is_object($_GET['data'])) {
                $this->data = $_GET['data']->AsArray();
            } else {
                $this->data = $_GET['data'];
            }
        }
        $itemViewed = 20;
        $currPage = 1;
        $startRec = 0;

        if (isset($_GET['page'])) {
            $currPage = (string) $_GET['page']->StripHtmlTags()->SqlString()->Raw();
            $startRec = ($currPage - 1) * $itemViewed;
        }
        $dataGrid = $this->Coa->GetDataCoa($startRec, $itemViewed, $this->data['nama'], $this->data['kode']);
        $totalData = $this->Coa->GetCountCoa('%' . $this->data['nama'] . '%', '%' . $this->data['kode'] . '%');

        //$dataProgram = $ProgramObj->GetDataProgram($startRec,$itemViewed, $this->data['program'],$is_cari);
        $url = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, Dispatcher::Instance()->mSubModule, Dispatcher::Instance()->mAction, Dispatcher::Instance()->mType) . '&data[nama]=' . $this->data['nama'] . '&data[kode]=' . $this->data['kode'] . '&tipe=' . $return['tipe'];
        $dest = "popup-subcontent";
        Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top', array(
            $itemViewed,
            $totalData,
            $url,
            $currPage,
            $dest
                ), Messenger::CurrentRequest);
        $return['dataGrid'] = $dataGrid;
        $return['start'] = $startRec + 1;

        return $return;
    }

    function ParseTemplate($data = NULL) {
        $this->mrTemplate->AddVar('content', 'SEARCH_NAMA', $this->data['nama']);
        $this->mrTemplate->AddVar('content', 'SEARCH_KODE', $this->data['kode']);
        $this->mrTemplate->AddVar('content', 'URL_SEARCH', Dispatcher::Instance()->GetUrl('kelompok_laporan', 'PopUpCoa', 'view', 'html') . '&data[name]=' . $this->data['nama'] . '&data[kode]=' . $this->data['kode'] . '&tipe=' . $data['tipe']);
        $this->mrTemplate->AddVar('content', 'TIPE_POPUP', $data['tipe']);

        if (isset($data['msg'])) {
            $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
            $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $data['msg']['message']);

            if ($data['msg']['action'] == 'msg') {
                $class = 'notebox-done';
            } else {
                $class = 'notebox-warning';
            }
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

                    if ($data['tipe'] == 'all') {

                        if($dataGrid[$i]['saldo_normal'] =='D') {
                        $dataGrid[$i]['set_parent'] = '<a class="link_' . $dataGrid[$i]['id'] .
                                '" href="javascript:void(0)" onclick="setParent(this, \'' .
                                $dataGrid[$i]['id'] . '\',\'' . $dataGrid[$i]['kode'] . '\',\'' .
                                $dataGrid[$i]['nama'] . '\',\'D\')" onmouseover="status=\'Set preferences...\';return true" >'.
                                '<img src="images/button-check.gif" /></a>';
                        } else {
                        $dataGrid[$i]['set_parent'] = '<a class="link_' .
                                $dataGrid[$i]['id'] . '" href="javascript:void(0)" onclick="setParent(this, \'' .
                                $dataGrid[$i]['id'] . '\',\'' . $dataGrid[$i]['kode'] . '\',\'' . $dataGrid[$i]['nama'] .
                                '\',\'K\')" onmouseover="status=\'Set preferences...\';return true" ><img src="images/button-check.gif" /></a>';
                        }
                    }
                    $dataGrid[$i]['class_name'] = 'table-common-even';
                } else {
                    $dataGrid[$i]['class_name'] = '';
                }

                //$idEnc = Dispatcher::Instance()->Encrypt($dataGrid[$i]['program_id']);
                $this->mrTemplate->AddVars('data_item', $dataGrid[$i], 'DATA_');
                $this->mrTemplate->parseTemplate('data_item', 'a');
            }
        }
    }

}

?>