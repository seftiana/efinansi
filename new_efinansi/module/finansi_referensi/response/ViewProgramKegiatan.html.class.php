<?php
#doc
# package:     ViewProgramKegiatan
# scope:       PUBLIC
# @created     ucil.619
# @Author      Eko Susilo
# @Created     2013-09-06
# @Modified    2013-09-6-06
# @Analysts    Nanang Ruswianto
# @copyright   Copyright (c) 2012 Gamatechno
#/doc
require_once GTFWConfiguration::GetValue('application','docroot').
'module/'.Dispatcher::Instance()->mModule.'/business/FinansiReferensi.class.php';

class ViewProgramKegiatan extends HtmlResponse
{
   #   internal variables
   private $mObj;
   protected $_POST;
   protected $_GET;
   #   Constructor
   function __construct ()
   {
      $this->mObj       = new FinansiReferensi();
      if(is_object($_POST)){
         $this->_POST   = $_POST->AsArray();
      }else{
         $this->_POST   = $_POST;
      }
      if(is_object($_GET)){
         $this->_GET    = $_GET->AsArray();
      }else{
         $this->_GET    = $_GET;
      }
   }
   
   function TemplateModule(){
     $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
     'module/'.Dispatcher::Instance()->mModule.'/template/');
     $this->SetTemplateFile('view_program_kegiatan.html');
   }
   
   function ProcessRequest(){
      $msg                    = Messenger::Instance()->Receive(__FILE__);
      $tahunAnggaranArr       = $this->mObj->GetTahunAnggaran();
      $tahunAnggaranAktif     = $this->mObj->GetTahunAnggaran(array('active' => true));
      $requestData            = array();
      
      if($msg){
         $messengerMessage    = $msg[0][1];
         $messengerStyle      = $msg[0][2];
      }

      if(isset($this->_POST['btnSearch'])){
         $requestData['tahun_anggaran']   = $this->_POST['tahun_anggaran'];
         $requestData['kegiatan_id']      = $this->_POST['kegiatan_id'];
         $requestData['kegiatan']         = $this->_POST['kegiatan'];
         $requestData['output_id']        = $this->_POST['output_id'];
         $requestData['output']           = $this->_POST['output'];
         $requestData['kode']             = trim($this->_POST['kode']);
         $requestData['nama']             = trim($this->_POST['nama']);
      }elseif(isset($this->_GET['search'])){
         $requestData['tahun_anggaran']   = Dispatcher::Instance()->Decrypt($this->_GET['tahun_anggaran']);
         $requestData['kegiatan_id']      = Dispatcher::Instance()->Decrypt($this->_GET['kegiatan_id']);
         $requestData['kegiatan']         = Dispatcher::Instance()->Decrypt($this->_GET['kegiatan']);
         $requestData['output_id']        = Dispatcher::Instance()->Decrypt($this->_GET['output_id']);
         $requestData['output']           = Dispatcher::Instance()->Decrypt($this->_GET['output']);
         $requestData['kode']             = Dispatcher::Instance()->Decrypt($this->_GET['kode']);
         $requestData['nama']             = Dispatcher::Instance()->Decrypt($this->_GET['nama']);
      }else{
         $requestData['tahun_anggaran']   = $tahunAnggaranAktif[0]['id'];
         $requestData['kegiatan_id']      = '';
         $requestData['kegiatan']         = '--Pilih data output untuk memilih kegiatan--';
         $requestData['output_id']        = '';
         $requestData['output']           = '';
         $requestData['kode']             = '';
         $requestData['nama']             = '';
      }

      foreach ($tahunAnggaranArr as $ta) {
         if((int)$ta['id'] === (int)$requestData['tahun_anggaran']){
            $requestData['ta_label']      = $ta['name'];
         }
      }

      foreach ($requestData as $key => $value) {
         $query[$key]      = Dispatcher::Instance()->Encrypt($value);
      }
      $queryString         = urldecode(http_build_query($query));
      
      $offset        = 0;
      $limit         = 20;
      $page          = 0;
      if(isset($_GET['page'])){
         $page       = (string) $_GET['page']->StripHtmlTags()->SqlString()->Raw();
         $page       = ($page == '') ? 1 : $page;
         $offset     = ($page - 1) * $limit;
      }elseif(isset($_GET['curpage'])){
         $page       = (string) $_GET['curpage']->StripHtmlTags()->SqlString()->Raw();
         $page       = ($page == '') ? 1 : $page;
         $offset     = ($page - 1) * $limit;
      }
      
      #paging url
      $url           = Dispatcher::Instance()->GetUrl(
        Dispatcher::Instance()->mModule,
        Dispatcher::Instance()->mSubModule,
        Dispatcher::Instance()->mAction,
        Dispatcher::Instance()->mType
      ).'&search='.Dispatcher::Instance()->Encrypt(1).'&'.$queryString;
      
      $destination_id   = "subcontent-element";
      $dataList         = $this->mObj->GetDataProgramKegiatan(array(
         'limit' => $limit, 
         'offset' => $offset, 
         'options' => (array)$requestData
      ));
      $total_data       = $this->mObj->GetCountProgramKegiatan(array(
         'options' => (array)$requestData
      ));

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
      

      # Combobox
      Messenger::Instance()->SendToComponent(
         'combobox', 
         'Combobox', 
         'view', 
         'html', 
         'tahun_anggaran', 
         array(
            'tahun_anggaran', 
            $tahunAnggaranArr, 
            $requestData['tahun_anggaran'], 
            false, 
            'id="cb_tahun_anggaran" style="width: 115px;"'
         ), 
         Messenger::CurrentRequest
      );

      $return['request_data']    = $requestData;
      $return['query_string']    = $queryString;
      $return['data_list']       = $this->mObj->ChangeKeyName($dataList);
      $return['start']           = $offset+1;
      $return['messenger_msg']   = $messengerMessage;
      $return['messenger_style'] = $messengerStyle;
      return $return;
   }
   
   function ParseTemplate($data = null){
      $page             = 1;
      if(isset($_GET['page'])){
         $page          = (string) $_GET['page']->StripHtmlTags()->SqlString()->Raw();
      }elseif(isset($_GET['curpage'])){
         $page          = (string) $_GET['curpage']->StripHtmlTags()->SqlString()->Raw();
      }
      $requestData      = $data['request_data'];
      $queryString      = $data['query_string'];
      $dataList         = $data['data_list'];
      $start            = $data['start'];
      $messengerMsg     = $data['messenger_msg'];
      $messengerStyle   = $data['messenger_style'];

      $urlSearch        = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         Dispatcher::Instance()->mSubModule,
         Dispatcher::Instance()->mAction,
         Dispatcher::Instance()->mType
      );
      $urlPopupOutput   = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'PopupOutput',
         'view',
         'html'
      );
      $urlAddKegiatan   = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'AddKegiatan',
         'view',
         'html'
      ).'&'.$queryString.'&curpage='.$page;
      $urlEditKegiatan  = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'EditKegiatan',
         'view',
         'html'
      ).'&'.$queryString.'&curpage='.$page;
      $urlAddOutput     = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'AddOutput',
         'view',
         'html'
      ).'&'.$queryString.'&curpage='.$page;
      $urlEditOutput    = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'EditOutput',
         'view',
         'html'
      ).'&'.$queryString.'&curpage='.$page;
      $urlAddKomponen   = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'AddKomponen',
         'view',
         'html'
      ).'&'.$queryString.'&curpage='.$page;
      $urlEditKomponen  = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'EditKomponen',
         'view',
         'html'
      ).'&'.$queryString.'&curpage='.$page;
      $urlDetailBelanja    = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'ManajemenDetailBelanja',
         'view',
         'html'
      ).'&'.$queryString.'&curpage='.$page;
      
      $urlExportExcel      = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'ExcelProgramKegiatan',
         'view',
         'xlsx'
      ).'&'.$queryString;
      
      $parseUrl      = parse_url($queryString);
      $urlExploded   = explode('&', $parseUrl['path']);
      $urlIndex      = 0;
      foreach ($urlExploded as $url) {
         list($urlKey[$urlIndex], $urlValue[$urlIndex]) = explode('=', $url);
         $urlIndex   += 1;
      }
      unset($urlIndex);
      $keyUrl     = implode('|', $urlKey);
      $valueUrl   = implode('|', $urlValue);
                      
      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);
      $this->mrTemplate->AddVars('content', $requestData, '');
      $this->mrTemplate->AddVar('content', 'URL_POPUP_OUTPUT', $urlPopupOutput);
      $this->mrTemplate->AddVar('content', 'URL_ADD_KEGIATAN', $urlAddKegiatan);
      $this->mrTemplate->AddVar('content', 'URL_ADD_OUTPUT', $urlAddOutput);
      $this->mrTemplate->AddVar('content', 'URL_ADD_KOMPONEN', $urlAddKomponen);
      $this->mrTemplate->AddVar('content', 'URL_EXPORT_EXCEL', $urlExportExcel);

      if($messengerMsg){
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $messengerMsg);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $messengerStyle);
      }

      if(empty($dataList)){
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         
         $kegiatan      = '';
         $output        = '';
         $komponen      = '';
         $dataRow       = array();
         $index         = 0;
         $rowLevel      = array();
         for ($i=0; $i < count($dataList);) { 
            if((int)$dataList[$i]['kegiatan_id'] === (int)$kegiatan && 
               (int)$dataList[$i]['output_id'] === (int)$output){
               if($dataList[$i]['komponen_id'] !== NULL){
                  // url delete
                  $urlAccept  = 'finansi_referensi|DeleteKomponen|do|html-search|'.$keyUrl.'-1|'.$valueUrl;
                  $urlReturn  = 'finansi_referensi|programKegiatan|view|html-search|'.$keyUrl.'-1|'.$valueUrl;
                  $label      = Dispatcher::Instance()->Encrypt(GTFWConfiguration::GetValue( 'language', 'program'));
                  $idEnc      = Dispatcher::Instance()->Encrypt($dataList[$i]['komponen_id']);
                  $dataName   = Dispatcher::Instance()->Encrypt($dataList[$i]['komponen_nama']);
                  $message    = 'Penghapusan Data ini akan menghapus '.GTFWConfiguration::GetValue( 'language', 'sub_kegiatan').' secara permanen.';
                  
                  $urlDelete  = Dispatcher::Instance()->GetUrl('confirm', 'confirmDelete', 'do', 'html').'&urlDelete='. $urlAccept.'&urlReturn='.$urlReturn.'&id='.$idEnc.'&label='.$label.'&dataName='.$dataName.'&message='.$message;

                  $dataRow[$index]['id']           = $dataList[$i]['komponen_id'];
                  $dataRow[$index]['kode']         = $dataList[$i]['komponen_kode'];
                  $dataRow[$index]['nama']         = $dataList[$i]['komponen_nama'];
                  $dataRow[$index]['label']        = $dataList[$i]['komponen_kode'].' &mdash; '.$dataList[$i]['komponen_nama'];
                  $dataRow[$index]['level']        = 'komponen';
                  $dataRow[$index]['class_name']   = $start % 2 <> 0 ? 'table-common-even' : '';
                  $dataRow[$index]['detail_belanja']  = $dataList[$i]['detail_belanja'];
                  $dataRow[$index]['deleteable']      = (int)$dataList[$i]['detail_belanja'] !== 0 ? 'no' : 'yes';
                  $dataRow[$index]['url_delete']      = $urlDelete;
                  $dataRow[$index]['url_edit']        = $urlEditKomponen.'&data_id='.Dispatcher::Instance()->Encrypt($dataList[$i]['komponen_id']);
                  $dataRow[$index]['url_detail_belanja'] = $urlDetailBelanja.'&komponen_id='.Dispatcher::Instance()->Encrypt($dataList[$i]['komponen_id']);
                  $start++;
               }
               $i++;
            }elseif((int)$dataList[$i]['kegiatan_id'] === (int)$kegiatan && (int)$output !== (int)$dataList[$i]['output_id']){

               // url delete
               $urlAccept  = 'finansi_referensi|DeleteOutput|do|html-search|'.$keyUrl.'-1|'.$valueUrl;
               $urlReturn  = 'finansi_referensi|programKegiatan|view|html-search|'.$keyUrl.'-1|'.$valueUrl;
               $label      = Dispatcher::Instance()->Encrypt(GTFWConfiguration::GetValue( 'language', 'kegiatan'));
               $idEnc      = Dispatcher::Instance()->Encrypt($dataList[$i]['output_id']);
               $dataName   = Dispatcher::Instance()->Encrypt($dataList[$i]['output_nama']);
               $message    = 'Penghapusan Data ini akan menghapus semua '.GTFWConfiguration::GetValue( 'language', 'sub_kegiatan').' dibawahnya';
               
               $urlDelete  = Dispatcher::Instance()->GetUrl('confirm', 'confirmDelete', 'do', 'html').'&urlDelete='. $urlAccept.'&urlReturn='.$urlReturn.'&id='.$idEnc.'&label='.$label.'&dataName='.$dataName.'&message='.$message;

               $output        = $dataList[$i]['output_id'];
               $dataRow[$index]['id']           = $dataList[$i]['output_id'];
               $dataRow[$index]['kode_sistem']  = $dataList[$i]['kegiatan_id'].'.'.$dataList[$i]['output_id'];
               $dataRow[$index]['kode']         = $dataList[$i]['output_kode'];
               $dataRow[$index]['nama']         = $dataList[$i]['output_nama'];
               $dataRow[$index]['label']        = $dataList[$i]['output_kode'].' &mdash; '.$dataList[$i]['output_nama'];
               $dataRow[$index]['level']        = 'output';
               $dataRow[$index]['class_name']   = 'table-common-even2';
               $dataRow[$index]['deleteable']   = (int)$dataList[$i]['komponen'] <> 0 ? 'no' : 'yes';
               $dataRow[$index]['url_edit']     = $urlEditOutput.'&data_id='.Dispatcher::Instance()->Encrypt($dataList[$i]['output_id']);
               $dataRow[$index]['url_delete']   = $urlDelete;
            }else{
               // url delete
               $urlAccept  = 'finansi_referensi|DeleteKegiatan|do|html-search|'.$keyUrl.'-1|'.$valueUrl;
               $urlReturn  = 'finansi_referensi|programKegiatan|view|html-search|'.$keyUrl.'-1|'.$valueUrl;
               $label      = Dispatcher::Instance()->Encrypt(GTFWConfiguration::GetValue( 'language', 'program'));
               $idEnc      = Dispatcher::Instance()->Encrypt($dataList[$i]['kegiatan_id']);
               $dataName   = Dispatcher::Instance()->Encrypt($dataList[$i]['kegiatan_nama']);
               $message    = 'Penghapusan Data ini akan menghapus semua '.GTFWConfiguration::GetValue( 'language', 'kegiatan').' dan '.GTFWConfiguration::GetValue( 'language', 'sub_kegiatan').' dibawahnya';
               
               $urlDelete  = Dispatcher::Instance()->GetUrl('confirm', 'confirmDelete', 'do', 'html').'&urlDelete='. $urlAccept.'&urlReturn='.$urlReturn.'&id='.$idEnc.'&label='.$label.'&dataName='.$dataName.'&message='.$message;

               $kegiatan                        = $dataList[$i]['kegiatan_id'];
               $dataRow[$index]['id']           = $dataList[$i]['kegiatan_id'];
               $dataRow[$index]['kode_sistem']  = $dataList[$i]['kegiatan_id'];
               $dataRow[$index]['kode']         = $dataList[$i]['kegiatan_kode'];
               $dataRow[$index]['nama']         = $dataList[$i]['kegiatan_nama'];
               $dataRow[$index]['label']        = $dataList[$i]['kegiatan_kode'].' &mdash; '.$dataList[$i]['kegiatan_nama'];
               $dataRow[$index]['level']        = 'kegiatan';
               $dataRow[$index]['class_name']   = 'table-common-even1';
               $dataRow[$index]['deleteable']   = (int)$dataList[$i]['output'] <> 0 ? 'no' : 'yes';
               $dataRow[$index]['url_edit']     = $urlEditKegiatan.'&data_id='.Dispatcher::Instance()->Encrypt($dataList[$i]['kegiatan_id']);
               $dataRow[$index]['url_delete']   = $urlDelete;
            }
            $index++;
         }
         
         foreach ($dataRow as $row) {
            $row['child_output']        = $rowLevel[$row['id']]['output'];
            $this->mrTemplate->AddVar('links_level', 'DELETEABLE', strtoupper($row['deleteable']));
            $this->mrTemplate->AddVar('links_level', 'URL_DELETE', $row['url_delete']);
            $this->mrTemplate->AddVar('level', 'LEVEL', strtoupper($row['level']));
            $this->mrTemplate->AddVar('level', 'URL_DETAIL_BELANJA', $row['url_detail_belanja']);
            if($row['id'] === NULL){
               continue;
            }
            $this->mrTemplate->AddVars('data_list', $row, '');
            $this->mrTemplate->parseTemplate('data_list', 'a');
         }
      }
   }
}
?>