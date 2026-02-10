<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
'module/rencana_kinerja_tahunan_kegiatan/business/AppPopupProgram.class.php';

class ViewPopupProgram extends HtmlResponse {

    var $Pesan;

    function TemplateModule() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
        'module/rencana_kinerja_tahunan_kegiatan/template');
        $this->SetTemplateFile('view_popup_program.html');
    }
   
    function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }
    
    function ProcessRequest() {
        $popupProgramObj = new AppPopupProgram();
        $POST = $_POST->AsArray();
        $tahun_anggaran = Dispatcher::Instance()->Decrypt($_GET['tahun_anggaran']);
        $tahun_anggaran_label = Dispatcher::Instance()->Decrypt($_GET['tahun_anggaran_label']);
        //$tahun_anggaran = $_GET['tahun_anggaran'];
        if(!empty($POST)) {
            $program = $POST['program'];
            $kode= $POST['kode'];
        } elseif(isset($_GET['cari'])) {
            $program = Dispatcher::Instance()->Decrypt($_GET['program']);
            $kode = Dispatcher::Instance()->Decrypt($_GET['kode']);
        } else {
            $program="";
            $kode="";
        }

        $totalData = $popupProgramObj->GetCountDataProgram($tahun_anggaran, $program, $kode);

        $itemViewed = 20;
        $currPage = 1;
        $startRec = 0 ;
        if(isset($_GET['page'])) {
            $currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
            $startRec =($currPage-1) * $itemViewed;
        }
        $dataProgram = $popupProgramObj->getDataProgram($startRec, $itemViewed, $tahun_anggaran, $program, $kode);
        $url = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, Dispatcher::Instance()->mSubModule, Dispatcher::Instance()->mAction, Dispatcher::Instance()->mType . '&tahun_anggaran=' . Dispatcher::Instance()->Encrypt($tahun_anggaran) . '&program=' . Dispatcher::Instance()->Encrypt($program) . '&kode=' . Dispatcher::Instance()->Encrypt($kode) . '&cari=' . Dispatcher::Instance()->Encrypt(1));
        $dest = "popup-subcontent";
        Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top', array($itemViewed,$totalData, $url, $currPage, $dest), Messenger::CurrentRequest);

        $msg = Messenger::Instance()->Receive(__FILE__);
        $this->Pesan = $msg[0][1];
        $this->css = $msg[0][2];

        $return['dataProgram'] = $dataProgram;
        $return['start'] = $startRec+1;

        $return['search']['tahun_anggaran'] = $tahun_anggaran;
        $return['search']['tahun_anggaran_label'] = $tahun_anggaran_label;
        $return['search']['program'] = $program;
        $return['search']['kode'] = $kode;
        return $return;
    }
    
    function ParseTemplate($data = NULL) {
        $search = $data['search'];
        $this->mrTemplate->AddVar('content', 'TAHUN_ANGGARAN_LABEL', $search['tahun_anggaran_label']);
        $this->mrTemplate->AddVar('content', 'PROGRAM', $search['program']);
        $this->mrTemplate->AddVar('content', 'KODE', $search['kode']);
        $this->mrTemplate->AddVar('content', 'URL_SEARCH', Dispatcher::Instance()->GetUrl('rencana_kinerja_tahunan_kegiatan', 'popupProgram', 'view', 'html') . '&tahun_anggaran=' . Dispatcher::Instance()->Encrypt($search['tahun_anggaran']) . '&tahun_anggaran_label=' . Dispatcher::Instance()->Encrypt($search['tahun_anggaran_label']));
        if($this->Pesan) {
            $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
            $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
            $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
        }

        if (empty($data['dataProgram'])) {
            $this->mrTemplate->AddVar('data_program', 'PROGRAM_EMPTY', 'YES');
        } else {
            $this->mrTemplate->AddVar('data_program', 'PROGRAM_EMPTY', 'NO');
            $dataProgram = $data['dataProgram'];
            for($i=0;$i<sizeof($dataProgram);$i++) {
                $dataProgram[$i]['enc_program_id'] = Dispatcher::Instance()->Encrypt($dataProgram[$i]['id']);
                $dataProgram[$i]['enc_program_nama'] = Dispatcher::Instance()->Encrypt($dataProgram[$i]['nama']);
            }

            for ($i=0; $i<sizeof($dataProgram); $i++) {
                $no = $i+$data['start'];
                $dataProgram[$i]['number'] = $no;
                $dataProgram[$i]['link'] = str_replace("'","\'",$dataProgram[$i]['nama']);
                if ($no % 2 != 0) $dataProgram[$i]['class_name'] = 'table-common-even';
                else $dataProgram[$i]['class_name'] = '';

                $this->mrTemplate->AddVars('data_program_item', $dataProgram[$i], 'PROGRAM_');
                $this->mrTemplate->parseTemplate('data_program_item', 'a');     
            }
        }
    }
}
?>
