<?php 
#doc
# package:     ViewPopupReferensiMak
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

class ViewPopupReferensiMak extends HtmlResponse
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
     $this->SetTemplateFile('view_popup_referensi_mak.html');
   }
   
   function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }

   function ProcessRequest(){
      if(isset($this->_POST['btnSearch'])){
         $post['bas_kode']    = trim($this->_POST['bas_kode']);
         $post['kode']        = trim($this->_POST['kode']);
      }elseif(isset($this->_GET['search'])){
         $post['bas_kode']    = Dispatcher::Instance()->Decrypt($this->_GET['bas_kode']);
         $post['kode']        = Dispatcher::Instance()->Decrypt($this->_GET['kode']);
      }else{
         $post['bas_kode']    = '';
         $post['kode']        = '';
      }

      foreach ($post as $key => $value) {
         $query[$key]         = Dispatcher::Instance()->Encrypt($value);
      }
      $uri                    = urldecode(http_build_query($query));

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
      ).'&search='.Dispatcher::Instance()->Encrypt(1);
      
      $destination_id         = "popup-subcontent";
      $dataList               = $this->Obj->GetDataMak($offset, $limit, $post);
      $total_data             = $this->Obj->Count();
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
      
      $return['post']         = $post;
      $return['start']        = $offset+1;
      $return['dataList']     = $this->Obj->ChangeKeyName($dataList);
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
      );
      
      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);
      $this->mrTemplate->AddVar('content', 'SEARCH_KODE', $post['kode']);
      $this->mrTemplate->AddVar('content', 'BAS_KODE', $post['bas_kode']);

      if(empty($dataList)){
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         $dataLists     = array();
         $i             = 0;
         $index         = 0;
         $bas           = 0;
         
         for($i = 0; $i < count($dataList);){
            if($dataList[$i]['bas_id'] == $bas){
               $dataLists[$index]['id']      = $dataList[$i]['id'];
               $dataLists[$index]['kode']    = $dataList[$i]['kode'];
               $dataLists[$index]['nama']    = $dataList[$i]['nama'];
               $dataLists[$index]['nomor']   = $start+$i;
               $dataLists[$index]['class_name'] = ($i % 2 <> 0) ? 'table-common-even' : '';
               $i++;
            }else{
               $bas                          = $dataList[$i]['bas_id'];
               $dataLists[$index]['id']      = $dataList[$i]['bas_id'];
               $dataLists[$index]['kode']    = $dataList[$i]['bas_kode'];
               $dataLists[$index]['nama']    = $dataList[$i]['bas_nama'];
               $dataLists[$index]['link']    = 'display: none;';
               $dataLists[$index]['class_name'] = 'table-common-even1';
               $dataLists[$index]['row_style']  = 'font-weight: bold;';
            }
            $index++;
         }

         foreach ($dataLists as $list) {
            $this->mrTemplate->AddVars('data_list', $list, 'DATA_');
            $this->mrTemplate->parseTemplate('data_list', 'a');
         }
      }
   }
}
?>