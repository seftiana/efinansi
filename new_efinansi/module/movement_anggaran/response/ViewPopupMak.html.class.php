<?php
/**
* Module : movement_anggaran
* FileInclude : AppPopupMak.class.php
* Class : ViewPopupMak
* Extends : HtmlResponse
*/
    require_once GTFWConfiguration::GetValue('application','docroot').
    'module/movement_anggaran/business/AppPopupMak.class.php';

        class ViewPopupMak extends HtmlResponse{
        function TemplateModule(){
            $this->setTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
            'module/movement_anggaran/template/');
            $this->setTemplateFile('view_popup_mak.html');
        }
            
        function ProcessRequest(){
            $objMak = new AppPopupMak();

            $kode 	= $_POST['nama'];
            
            if(isset($_GET['nama'])){
                $kode = Dispatcher::Instance()->Decrypt($_GET['nama']);
            }
	  
            #fethc numrows
            //$numrows = $objMak->GetCount();// fetch here;
            $numrows = $objMak->CountMak($kode);
            
            $limit 	    = 20;
            $page 	    = 0;
            $offset 	= 0;
            if(isset($_GET['page'])){
                $page = (string) $_GET['page']->StripHtmlTags()->SqlString()->Raw();
                $offset = ($page - 1) * $limit;
            }
            #pagging url
            $url = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule,
                   Dispatcher::Instance()->mSubModule,
                   Dispatcher::Instance()->mAction,
                   Dispatcher::Instance()->mType)
                   .'&nama='.Dispatcher::Instance()->Encrypt($kode);

            $destination_id = "popup-subcontent"; # options: {popup-subcontent,subcontent-element}

            #send data to pagging component
            Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top',
            array($limit,$numrows, $url, $page, $destination_id),
            Messenger::CurrentRequest);
            #fetch data
            $return['data'] = $objMak->DataMak($kode, $offset, $limit);
            #send data to parse method
            $return['start'] = $offset+1;
            $return['page'] = $page;
            $return['numrows'] = $numrows;
            $return['data_search']	= $kode;
            return $return;
        }

        function ParseTemplate($data = null){
            $urlMak 		= Dispatcher::Instance()->GetUrl('movement_anggaran', 'PopupMak', 'view', 'html');
            $this->mrTemplate->AddVar('content','URL_SEARCH',$urlMak);
            if(isset($data['data_search'])):
                $this->mrTemplate->AddVar('content','NAMA',$data['data_search']);
            endif;
	 
            if (empty($data['data'])) {
                $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
            } else {
                $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');

                $dataList = $data['data'];

                for ($i=0; $i<sizeof($dataList); $i++) {
                    $no = $i+$data['start'];
                    $dataList[$i]['nomor'] = $no;

                    if ($no % 2 != 0){
                        $dataList[$i]['class_name'] = 'table-common-even';
                    }else{
                        $dataList[$i]['class_name'] = '';
                    }
                    
                //$dataList[$i]['nama']			= "nama'ku";
                $dataList[$i]['link']			= str_replace("'","\'",$dataList[$i]['nama']);
                $this->mrTemplate->AddVars('data_item', $dataList[$i], '');
                $this->mrTemplate->parseTemplate('data_item', 'a');
                }
            }
        }
    }
?>
