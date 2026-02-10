<?php
/**
* ================= doc ====================
* FILENAME     : PopupParentChild.html.class.php
* @package     : PopupParentChild
* scope        : PUBLIC
* @Author      : Rochmad Widianto <rochmad@gamatechno.com>
* @Created     : 2017-06-22
* @Modified    : 2017-06-22
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2017 Gamatechno
* ================= doc ====================
*/

require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/kelompok_laporan/business/AppKlpLaporan.class.php';

class ViewPopupParentChild extends HtmlResponse {

    function TemplateModule() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') .
                'module/kelompok_laporan/template');
        $this->SetTemplateFile('view_popup_parent_child.html');
    }

    function TemplateBase() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
        $this->SetTemplateFile('document-common-popup.html');
        $this->SetTemplateFile('layout-common-popup.html');
    }

    function ProcessRequest() {
        $this->klpLaporan = new AppKelpLaporan();
        $parentId = Dispatcher::Instance()->Decrypt($_GET['parentId'])->mrVariable;

        if (isset($_POST['btncari'])) {
            $parent_id      = $_POST['parent_id'];
            $nama_kellap    = $_POST['nama_kellap'];
        } elseif (isset($_GET['search'])) {
            $parent_id      = Dispatcher::Instance()->Decrypt($_GET['parent_id']);
            $nama_kellap    = Dispatcher::Instance()->Decrypt($_GET['nama_kellap']);
        } else {
            $parent_id      = $parentId;
            $nama_kellap    = '';
        }

        $totalData  = $this->klpLaporan->GetCountkellapParent($parent_id, $nama_kellap);
        $itemViewed = 20;
        $currPage   = 1;
        $startRec   = 0;

        if (isset($_GET['page'])) {
            $currPage = (string) $_GET['page']->StripHtmlTags()->SqlString()->Raw();
            $startRec = ($currPage - 1) * $itemViewed;
        }

        $kellapParent = $this->klpLaporan->GetDataParentChild($parent_id, $nama_kellap, $startRec, $itemViewed);

        $url = Dispatcher::Instance()->GetUrl(
                Dispatcher::Instance()->mModule, Dispatcher::Instance()->mSubModule, Dispatcher::Instance()->mAction, Dispatcher::Instance()->mType .
                '&parent_id=' . Dispatcher::Instance()->Encrypt($parent_id) .
                '&nama_kellap=' . Dispatcher::Instance()->Encrypt($nama_kellap) .
                '&search=' . Dispatcher::Instance()->Encrypt(1));

        $dest = "popup-subcontent";
        Messenger::Instance()->SendToComponent(
                'paging', 'Paging', 'view', 'html', 'paging_top', array(
            $itemViewed,
            $totalData,
            $url,
            $currPage,
            $dest), Messenger::CurrentRequest);

        $return['kellapParent'] = $kellapParent;
        $return['start']        = $startRec + 1;
        $return['search']['parent_id']      = $parent_id;
        $return['search']['nama_kellap']    = $nama_kellap;

        return $return;
    }

    function ParseTemplate($data = NULL) {
        $search = $data['search'];

        $this->mrTemplate->AddVar('content', 'PARENT_ID', $search['parent_id']);
        $this->mrTemplate->AddVar('content', 'NAMA_KELLAP', $search['nama_kellap']);
        $this->mrTemplate->AddVar('content', 'URL_SEARCH', Dispatcher::Instance()->GetUrl(
                        Dispatcher::Instance()->mModule, Dispatcher::Instance()->mSubModule, 'view', 'html'));

        if (empty($data['kellapParent'])) {
            $this->mrTemplate->AddVar('kellap_parent', 'KELLAP_PARENT_EMPTY', 'YES');
        } else {
            $this->mrTemplate->AddVar('kellap_parent', 'KELLAP_PARENT_EMPTY', 'NO');
            $kellapParent = $data['kellapParent'];

            for ($i = 0; $i < sizeof($kellapParent); $i++) {
                $no = $i + $data['start'];
                $kellapParent[$i]['number']     = $no;
                if ($no % 2 != 0) $kellapParent[$i]['class_name'] = 'table-common-even';
                else $kellapParent[$i]['class_name'] = '';

                $this->mrTemplate->AddVars('data_kellap_parent', $kellapParent[$i], '');
                $this->mrTemplate->parseTemplate('data_kellap_parent', 'a');
            }
        }
    }

}

?>