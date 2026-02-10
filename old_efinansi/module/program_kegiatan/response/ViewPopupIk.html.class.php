<?php
require_once GTFWConfiguration::GetValue('application','docroot').
'module/program_kegiatan/business/PopupIk.class.php';

class ViewPopupIk extends HtmlResponse{
    function TemplateModule(){
        $this->setTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
        'module/program_kegiatan/template/');
        $this->setTemplateFile('popup_ik.html');
    }
    function TemplateBase() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
        $this->SetTemplateFile('document-common-popup.html');
        $this->SetTemplateFile('layout-common-popup.html');
    }
    function ProcessRequest(){
        $obj    = new PopupIk();
        
        $_POST  = $_POST->AsArray();
        if(isset($_POST['btnTampilkan'])){
            $kode   = trim($_POST['text_search']);
        }else{
            $kode   = Dispatcher::Instance()->Decrypt($_GET['kode']);
        }
        
        $totalData  = $obj->Count($kode);
        $itemViewed = 20;
        $currPage = 1;
        $startRec = 0 ;
        if(isset($_GET['page'])) {
            $currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();  
            $startRec =($currPage-1) * $itemViewed;
        }
		
        $data       = $obj->GetData($kode);
		//done
        $url = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, 
               Dispatcher::Instance()->mSubModule, 
               Dispatcher::Instance()->mAction, 
               Dispatcher::Instance()->mType
               ). "&kode=".Dispatcher::Instance()->Encrypt($kode);
		$dest = "popup-subcontent";	   
        Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top', 
        array($itemViewed,$totalData, $url, $currPage, $dest), 
        Messenger::CurrentRequest);   
        
        $return['start']    = $startRec+1;
        $return['kode']     = $kode;
        $return['dataList'] = $data;
        return $return;
    }

    function ParseTemplate($data = null){
        $url_search = Dispatcher::Instance()->GetUrl('program_kegiatan','PopupIk','view','html');
        $this->mrTemplate->AddVar('content','URL_SEARCH',$url_search);
        $this->mrTemplate->AddVar('content','TXT_KODE',$data['kode']);
        
        $dataList   = $data['dataList'];
        
        if(count($dataList) < 1){
            $this->mrTemplate->AddVar('data_list','DATA_EMPTY','YES');
        }else{
            $this->mrTemplate->AddVar('data_list','DATA_EMPTY','NO');
            for($i=0;$i<count($dataList);$i++){
                $dataList[$i]['number'] = $data['start']+$i;
                $this->mrTemplate->AddVars('data_grid',$dataList[$i],'');
                $this->mrTemplate->parseTemplate('data_grid','a');
            }
        }
    }
}
?>