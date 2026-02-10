<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/coa/business/Coa.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/unitkerja/business/AppUnitkerja.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') .
    'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewListCoa extends HtmlResponse {

    function TemplateModule() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'module/coa/template');
        $this->SetTemplateFile('view_list_coa.html');
    }

    function ProcessRequest() {
        $Obj = new Coa();

        // inisialisasi messaging
        $msg = Messenger::Instance()->Receive(__FILE__);
        $this->Data = $msg[0][0];
        $this->Pesan = $msg[0][1];
        $this->css = $msg[0][2];
        // ---------
        // inisialisasi filter
        $filter = array();
 
        $user_id = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
        $objUnit = new UserUnitKerja();
        $userUnit = $objUnit->GetUnitKerjaUser($user_id);
        
        if(!empty($userUnit)){
            $filter['unit_kerja'] = $userUnit['unit_kerja_id'];
        }else{
            $filter['unit_kerja'] = "all";
        }

        if (isset($_POST['btncari'])) {
            $filter = $_POST->AsArray();
        } elseif (isset($_GET['page']) && is_array($this->Data)) {
            $filter = $this->Data;
        }
        
        if (isset($_GET['unit_kerja'])) {
            $filter['unit_kerja'] = $_GET['unit_kerja'];
        }

        Messenger::Instance()->Send(Dispatcher::Instance()->mModule, Dispatcher::Instance()->mSubModule, Dispatcher::Instance()->mAction, Dispatcher::Instance()->mType, array($filter), Messenger::NextRequest);
        $return['filter'] = $filter;

        $mObjUnit = new AppUnitkerja();
        $dt_unitker = $mObjUnit->GetComboUnitKerja();
        Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 'unit_kerja',
         array('unit_kerja',$dt_unitker,$filter['unit_kerja'],'true',''), Messenger::CurrentRequest);
        
        // ---------
        // Inisialisasi komponen paging
        $itemViewed = 20;
        if (isset($_GET['page'])) {
            $currPage = $_GET['page']->Integer()->Raw();
        }
        if (!isset($currPage) OR $currPage < 1) {
            $currPage = 1;
        }
        $startRec = ($currPage - 1) * $itemViewed;

        $return['coa'] = $Obj->GetCoaFromNamaKode($filter, $startRec, $itemViewed);
        $totalData = $Obj->GetCoaFromNamaKodeCount();
        // ---------
        $url = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, Dispatcher::Instance()->mSubModule, Dispatcher::Instance()->mAction, Dispatcher::Instance()->mType);

        Messenger::Instance()->SendToComponent('paging', 'paging', 'view', 'html', 'paging_top', array($itemViewed, $totalData, $url, $currPage), Messenger::CurrentRequest);
        $return['start'] = $startRec + 1;
        $return['filter'] = $filter;
        return $return;
    }

    function ParseTemplate($data = NULL) {
        $filter = $data['filter'];
        if ($filter['coa_is_kas'] == '1') {
            $this->mrTemplate->AddVar('content', 'KAS_CHEKED', "checked='checked'");
        } else {
            $this->mrTemplate->AddVar('content', 'KAS_CHEKED', "");
        }

        if ($filter['coa_is_laba_rugi'] == '1') {
            $this->mrTemplate->AddVar('content', 'LABA_RUGI_CHEKED', "checked='checked'");
        } else {
            $this->mrTemplate->AddVar('content', 'LABA_RUGI_CHEKED', "");
        }

        if ($filter['coa_is_laba_rugi_at'] == '1') {
            $this->mrTemplate->AddVar('content', 'LABA_RUGI_AT_CHEKED', "checked='checked'");
        } else {
            $this->mrTemplate->AddVar('content', 'LABA_RUGI_AT_CHEKED', "");
        }

        $this->mrTemplate->AddVar('content', 'URL_SEARCH', Dispatcher::Instance()->GetUrl('coa', 'ListCoa', 'view', 'html'));

        $this->mrTemplate->AddVar('content', 'URL_KEMBALI', Dispatcher::Instance()->GetUrl('coa', 'Coa', 'view', 'html').'&unit_kerja='.$data['filter']['unit_kerja']);

        $this->mrTemplate->AddVar('content', 'INPUT', $data['filter']['input']);

        if (empty($data['coa'])) {
            $this->mrTemplate->AddVar('coa', 'USER_EMPTY', 'YES');
        } else {
            $this->mrTemplate->AddVar('coa', 'USER_EMPTY', 'NO');
            $no = 1;
            foreach ($data['coa'] as $dt => $item) {
                if ($no % 2 != 0) {
                    $this->mrTemplate->AddVar('list_coa', 'CLASS_NAME', 'table-common-even');
                } else {
                    $this->mrTemplate->AddVar('list_coa', 'CLASS_NAME', '');
                }
                $this->mrTemplate->AddVar('list_coa', 'NUMBER', $no);
                $this->mrTemplate->AddVar('list_coa', 'KODE_AKUN', $item['coaKodeAkun']);
                $this->mrTemplate->AddVar('list_coa', 'NAMA_AKUN', $item['coaNamaAkun']);
                $this->mrTemplate->AddVar('list_coa', 'URL_UBAH', Dispatcher::Instance()->GetUrl('coa', 'inputCoa', 'view', 'html') . '&coaid=' . $item['coaId'] . '&smpn=' . 'drlist'.'&unit_kerja='.$data['filter']['unit_kerja']);
                if ($item['coaIsDebetPositif'] == '1') {
                    $this->mrTemplate->AddVar('list_coa', 'SALDO_NORMAL', 'Debet');
                } else {
                    $this->mrTemplate->AddVar('list_coa', 'SALDO_NORMAL', 'Kredit');
                }
                $this->mrTemplate->parseTemplate('list_coa', 'a');
                $no++;
            }
        }
    }

}

?>