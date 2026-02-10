<?php
   require_once GTFWCOnfiguration::GetValue('application','docroot').
   'module/pagu_anggaran_unit_per_mak/business/PopupOutput.class.php';
   
   class ViewPopupOutput extends HtmlResponse{
      function TemplateModule(){
         $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot')
         .'module/pagu_anggaran_unit_per_mak/template/');
         $this->SetTemplateFile('popup_output.html');
      }
      
      function TemplateBase() {
         $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') 
         . 'main/template/');
         $this->SetTemplateFile('document-common-popup.html');
         $this->SetTemplateFile('layout-common-popup.html');
      }
      
      function ProcessRequest(){
         $_POST  = $_POST->AsArray();
         $obj    = new PopupOutput();
         
         if(isset($_POST['action'])){
            $kode       = trim($_POST['nama']);
            $kegiatan   = trim($_POST['kegiatan']);
         }elseif(isset($_GET['nama']) AND $_GET['nama'] != ''){
            $kode       = Dispatcher::Instance()->Decrypt($_GET['nama']);
            $kegiatan   = Dispatcher::Instance()->Decrypt($_GET['kegiatan']);
         }else{
            $kode       = '';
            $kegiatan   = '';
         }
         
         $num_rows   = $obj->CountData($kode, $kegiatan);
         #set default pagging
         $limit      = 20;
         $page       = 0;
         $offset     = 0;
         
         if(isset($_GET['page'])){
            $page    = (string) $_GET['page']->StripHtmlTags()->SqlString()->Raw();
            $offset  = ($page - 1) * $limit;
         }
         
         #pagging url
         $url        = Dispatcher::Instance()->GetUrl(
            Dispatcher::Instance()->mModule,
            Dispatcher::Instance()->mSubModule,
            Dispatcher::Instance()->mAction,
            Dispatcher::Instance()->mType)
         .'&nama='.Dispatcher::Instance()->Encrypt($kode)
         .'&kegiatan='.Dispatcher::Instance()->Encrypt($kegiatan);
         
         $destination_id      = "popup-subcontent";

         #send data to pagging component
         Messenger::Instance()->SendToComponent(
            'paging', 
            'Paging', 
            'view', 
            'html', 
            'paging_top',
            array(
               $limit,
               $num_rows, 
               $url, 
               $page, 
               $destination_id
            ),Messenger::CurrentRequest);
              
         $data                = $obj->GetData($kode, $kegiatan, $offset,$limit);
         
         $return['data']      = $data;
         $return['start']     = $offset+1;
         $return['nama']      = $kode;
         $return['kegiatan']  = $kegiatan;
         return $return;
         
      }
      
      function ParseTemplate($data = null){
         $url_search     = Dispatcher::Instance()->GetUrl(
            'pagu_anggaran_unit_per_mak',
            'PopupOutput',
            'view',
            'html'
         );
         
         $this->mrTemplate->AddVar('content','URL_SEARCH',$url_search);
         $this->mrTemplate->AddVar('content','NAMA',$data['nama']);
         $this->mrTemplate->AddVar('content', 'KEGIATAN', $data['kegiatan']);
         
         $dataList         = $data['data'];
         
         if(empty($dataList)){
            $this->mrTemplate->AddVar('data_grid','DATA_EMPTY','YES');
         }else{
            $this->mrTemplate->AddVar('data_grid','DATA_EMPTY','NO');
            $kegiatanId       = '';
            $dataLists        = array();
            $i                = 0;
            $index            = 0;
            $no               = 0;
            for($i = 0; $i < count($dataList);){
               if($kegiatanId == $dataList[$i]['kegiatan_id']){
                  $dataLists[$index]['id']      = $dataList[$i]['id'];
                  $dataLists[$index]['kode']    = $dataList[$i]['kode'];
                  $dataLists[$index]['nama']    = $dataList[$i]['nama'];
                  $dataLists[$index]['type']    = 'output';
                  $dataLists[$index]['nomor']   = ($data['start']+$i);
                  if(($no+$data['start']) % 2 == 0){
                     $dataLists[$index]['class_name']    = 'table-common-even';
                  }else{
                     $dataLists[$index]['class_name']    = '';
                  }
                  $dataLists[$index]['parent_id']     = $dataList[$i]['kegiatan_id'];
                  $dataLists[$index]['parent_kode']   = $dataList[$i]['keg_kode'];
                  $dataLists[$index]['parent_nama']   = $dataList[$i]['keg_nama'];
                  $no++;
                  $i++;
               }elseif($kegiatanId != $dataList[$i]['kegiatan_id']){
                  $kegiatanId       = $dataList[$i]['kegiatan_id'];
                  $dataLists[$index]['id']      = $dataList[$i]['kegiatan_id'];
                  $dataLists[$index]['kode']    = $dataList[$i]['keg_kode'];
                  $dataLists[$index]['nama']    = $dataList[$i]['keg_nama'];
                  $dataLists[$index]['type']    = 'kegiatan';
                  $dataLists[$index]['class_name']    = 'table-common-even1';
                  $dataLists[$index]['style']   = 'font-weight: bold;';
                  $dataLists[$index]['link']    = 'display: none;';
                  unset($no);
               }
               $index++;
            }
            unset($i);
            for($i=0;$i<count($dataLists);$i++){
               $this->mrTemplate->AddVars('data_item',$dataLists[$i],'');
               $this->mrTemplate->parseTemplate('data_item','a');
            }
         }
      }
   }
?>