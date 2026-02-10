<?php 
#doc
# package:     ViewPopupRkaklSubKegiatan
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

class ViewPopupRkaklSubKegiatan extends HtmlResponse
{
   #   internal variables
   public $Obj;
   public $_POST;
   public $_GET;
   #   Constructor
   function __construct ()
   {
      # code...
      $this->Obj     = new AppReferensi();
      $this->_POST   = $_POST->AsArray();
      $this->_GET    = $_GET->AsArray();
   }
   
   function TemplateModule(){
     $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
     'module/'.Dispatcher::Instance()->mModule.'/template/');
     $this->SetTemplateFile('view_popup_rkakl_sub_kegiatan.html');
   }
   
   function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }

   function ProcessRequest(){
      if(isset($this->_POST['btnSearch'])){
         $post['kode']     = trim($this->_POST['kode']);
      }elseif(isset($this->_GET['search'])){
         $post['kode']     = Dispatcher::Instance()->Decrypt($this->_GET['kode']);
      }else{
         $post['kode']     = '';
      }
      foreach ($post as $key => $value) {
         $query[$key]      = Dispatcher::Instance()->Encrypt($value);
      }
      $uri                 = urldecode(http_build_query($query));

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
      $dataList         = $this->Obj->GetDataKomponen($offset, $limit, $post['kode']);
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
      
      $return['post']      = $post;
      $return['start']     = $offset+1;
      $return['dataList']  = $this->Obj->ChangeKeyName($dataList);
      return $return;
   }
   
   function ParseTemplate($data = null){
      $post          = $data['post'];
      $dataList      = $data['dataList'];
      $start         = $data['start'];
      $urlSearch     = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         Dispatcher::Instance()->mSubModule,
         Dispatcher::Instance()->mAction,
         Dispatcher::Instance()->mType
      );
      
      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);
      $this->mrTemplate->AddVar('content', 'SEARCH_KODE', $post['kode']);
      if(empty($dataList)){
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         $nomor      = 0;
         foreach ($dataList as $list) {
            $list['no']          = $start+$nomor;
            $list['class_name']  = ($nomor % 2 <> 0) ? 'table-common-even' : '';
            $this->mrTemplate->AddVars('data_list', $list, 'DATA_');
            $this->mrTemplate->parseTemplate('data_list', 'a');
            $nomor++;
         }
      }
   }
}
?>