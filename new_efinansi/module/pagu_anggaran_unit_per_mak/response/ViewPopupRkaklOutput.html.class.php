<?php 
#doc
# package:     ViewPopupKegiatan
# scope:       PUBLIC
# @created     ucil.619
# @Author      Eko Susilo
# @Created     2013-03-18
# @Modified    2013-03-18
# @Analysts    Nanang Ruswianto
# @copyright   Copyright (c) 2012 Gamatechno
#/doc
require_once GTFWConfiguration::GetValue('application','docroot').
'module/'.Dispatcher::Instance()->mModule.'/business/AppReferensi.class.php';

class ViewPopupRkaklOutput extends HtmlResponse
{
   #   internal variables
   public $Obj;
   public $_POST;
   public $_GET;
   #   Constructor
   function __construct ()
   {
      $this->Obj     = new AppReferensi();
      $this->_POST   = $_POST->AsArray();
      $this->_GET    = $_GET->AsArray();
   }
   
   function TemplateModule(){
     $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
     'module/'.Dispatcher::Instance()->mModule.'/template/');
     $this->SetTemplateFile('view_popup_rkakl_output.html');
   }
   
   function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }

   function ProcessRequest(){
      $post['programId']      = $this->_GET['id'];

      if(isset($this->_POST['btnSearch'])){
         $post['kode_kegiatan']  = trim($this->_POST['kode_kegiatan']);
         $post['kode_output']    = trim($this->_POST['kode_output']);
      }elseif(isset($this->_GET['search'])){
         $post['kode_kegiatan']  = Dispatcher::Instance()->Decrypt($this->_GET['kode_kegiatan']);
         $post['kode_output']    = Dispatcher::Instance()->Decrypt($this->_GET['kode_output']);
      }else{
         $post['kode_kegiatan']  = '';
         $post['kode_output']    = '';
      }

      foreach ($post as $key => $value) {
         $query[$key]            = Dispatcher::Instance()->Encrypt($value);
      }
      $uri                       = urldecode(http_build_query($query));

      $offset         = 0;
      $limit          = 20;
      $page           = 0;
      if(isset($_GET['page'])){
         $page    = (string) $_GET['page']->StripHtmlTags()->SqlString()->Raw();
         $offset  = ($page - 1) * $limit;
      }
      #paging url
      $url    = Dispatcher::Instance()->GetUrl(
        Dispatcher::Instance()->mModule,
        Dispatcher::Instance()->mSubModule,
        Dispatcher::Instance()->mAction,
        Dispatcher::Instance()->mType
      ).'&search='.Dispatcher::Instance()->Encrypt(1).'&'.$uri;
      
      $destination_id   = "popup-subcontent";
      $dataList         = $this->Obj->GetDataOutput(
         $offset, 
         $limit, 
         $post['kode_output'], 
         $post['kode_kegiatan'], 
         $post['programId']
      );
      $total_data       = $this->Obj->Count();
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
      

      $return['post']            = $post;
      $return['start']           = $offset+1;
      $return['dataList']        = $this->Obj->ChangeKeyName($dataList);
      return $return;
   }
   
   function ParseTemplate($data = null){
      $post          = $data['post'];
      
      $start         = $data['start'];
      $dataList      = $data['dataList'];
      $urlSearch     = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         Dispatcher::Instance()->mSubModule,
         Dispatcher::Instance()->mAction,
         Dispatcher::Instance()->mType
      ).'&id='.$post['programId'];
      
      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);
      $this->mrTemplate->AddVar('content', 'KEGIATAN_KODE', $post['kode_kegiatan']);
      $this->mrTemplate->AddVar('content', 'OUTPUT_KODE', $post['kode_output']);

      if(empty($dataList)){
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         $i          = 0;
         $index      = 0;
         $dataLists  = array();
         $programId  = '';
         $kegiatanId = '';
         for($i = 0; $i < count($dataList);){
            if($programId == $dataList[$i]['program_id'] && $kegiatanId == $dataList[$i]['kegiatan_id']){
               // output
               $dataLists[$index]['id']      = $dataList[$i]['id'];
               $dataLists[$index]['kode']    = $dataList[$i]['kode'];
               $dataLists[$index]['nama']    = $dataList[$i]['nama'];
               $dataLists[$index]['kegiatan_id']   = $dataList[$i]['kegiatan_id'];
               $dataLists[$index]['kegiatan_kode'] = $dataList[$i]['kegiatan_kode'];
               $dataLists[$index]['kegiatan_nama'] = $dataList[$i]['kegiatan_nama'];
               $dataLists[$index]['nomor']         = $start+$i;
               $dataLists[$index]['class_name']    = ($i % 2 <> 0) ? 'table-common-even' : '';
               $dataLists[$index]['type']    = 'output';
               $i++;
            }elseif($programId == $dataList[$i]['program_id'] && $kegiatanId != $dataList[$i]['kegiatan_id']){
               $kegiatanId                   = $dataList[$i]['kegiatan_id'];
               $dataLists[$index]['id']      = $dataList[$i]['kegiatan_id'];
               $dataLists[$index]['kode']    = $dataList[$i]['kegiatan_kode'];
               $dataLists[$index]['nama']    = $dataList[$i]['kegiatan_nama'];
               $dataLists[$index]['type']    = 'kegiatan';
               $dataLists[$index]['class_name'] = 'table-common-even2';
               $dataLists[$index]['row_style']  = 'font-weight: bold;';
            }elseif($programId != $dataList[$i]['program_id']){
               $programId                    = $dataList[$i]['program_id'];
               $dataLists[$index]['id']      = $dataList[$i]['program_id'];
               $dataLists[$index]['kode']    = $dataList[$i]['program_kode'];
               $dataLists[$index]['nama']    = $dataList[$i]['program_nama'];
               $dataLists[$index]['type']    = 'program';
               $dataLists[$index]['class_name'] = 'table-common-even1';
               $dataLists[$index]['row_style']  = 'font-weight: bold;';
               $dataLists[$index]['link_style'] = 'display: none;';
            }

            $index++;
         }
         foreach ($dataLists as $list) {
            $this->mrTemplate->AddVar('data_tipe', 'TYPE', strtoupper($list['type']));
            $this->mrTemplate->AddVar('data_tipe', 'ID', $list['id']);
            $this->mrTemplate->AddVar('data_tipe', 'KODE', $list['kode']);
            $this->mrTemplate->AddVar('data_tipe', 'NAMA', $list['nama']);
            $this->mrTemplate->AddVar('data_tipe', 'KEGIATAN_ID', $list['kegiatan_id']);
            $this->mrTemplate->AddVar('data_tipe', 'KEGIATAN_KODE', $list['kegiatan_kode']);
            $this->mrTemplate->AddVar('data_tipe', 'KEGIATAN_NAMA', $list['kegiatan_nama']);
            $this->mrTemplate->AddVars('data_list', $list, 'DATA_');
            $this->mrTemplate->parseTemplate('data_list', 'a');
         }
      }
   }
}
?>