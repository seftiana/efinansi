<?php
#doc
# package:     ViewPopupKegiatan
# scope:       PUBLIC
# @created     ucil.619
# @Author      Eko Susilo
# @Created     2013-09-16
# @Modified    2013-09-16
# @Analysts    
# @copyright   Copyright (c) 2012 Gamatechno
#/doc
require_once GTFWConfiguration::GetValue('application','docroot').
'module/'.Dispatcher::Instance()->mModule.'/business/AppReferensi.class.php';

class ViewPopupKegiatan extends HtmlResponse
{
   #   internal variables
   protected $_POST;
   protected $_GET;
   private $mObj;
   #   Constructor
   function __construct ()
   {
      $this->mObj          = new AppReferensi();
      if(is_object($_POST)){
         $this->_POST      = $_POST->AsArray();
      }else{
         $this->_POST      = $_POST;
      }
      if(is_object($_GET)){
         $this->_GET       = $_GET->AsArray();
      }else{
         $this->_GET       = $_GET;
      }
   }
   
   function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }

   function TemplateModule(){
     $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
     'module/'.Dispatcher::Instance()->mModule.'/template/');
     $this->SetTemplateFile('view_popup_kegiatan.html');
   }
   
   function ProcessRequest(){
      $requestData            = array();
      $requestData['ta_id']   = Dispatcher::Instance()->Decrypt($this->_GET['ta_id']);
      if(isset($this->_POST['btnSearch'])){
         $requestData['kode'] = trim($this->_POST['kode']);
      }elseif(isset($this->_GET['search'])){
         $requestData['kode'] = Dispatcher::Instance()->Decrypt($this->_GET['kode']);
      }else{
         $requestData['kode'] = '';
      }

      foreach ($requestData as $key => $value) {
         $query[$key]         = Dispatcher::Instance()->Encrypt($value);
      }
      $queryString            = urldecode(http_build_query($query));

      $offset           = 0;
      $limit            = 20;
      $page             = 0;
      if(isset($_GET['page'])){
         $page          = (string) $_GET['page']->StripHtmlTags()->SqlString()->Raw();
         $offset        = ($page - 1) * $limit;
      }
      #paging url
      $url              = Dispatcher::Instance()->GetUrl(
        Dispatcher::Instance()->mModule,
        Dispatcher::Instance()->mSubModule,
        Dispatcher::Instance()->mAction,
        Dispatcher::Instance()->mType
      ).'&search='.Dispatcher::Instance()->Encrypt(1).'&'.$queryString;
      
      $dataList         = $this->mObj->GetDataProgramRef($offset, $limit, (array)$requestData);
      $total_data       = $this->mObj->GetCountProgramRef((array)$requestData);
      $destination_id   = "popup-subcontent";
      
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
      
      $return['query_string'] = $queryString;
      $return['request_data'] = $requestData;
      $return['data_list']    = $this->mObj->ChangeKeyName($dataList);
      $return['start']        = $offset+1;
      return $return;
   }
   
   function ParseTemplate($data = null){
      $requestData         = $data['request_data'];
      $queryString         = $data['query_string'];
      $start               = $data['start'];
      $dataList            = $data['data_list'];
      $urlSearch           = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         Dispatcher::Instance()->mSubModule,
         Dispatcher::Instance()->mAction,
         Dispatcher::Instance()->mType
      ).'&'.$queryString;
      
      $this->mrTemplate->ADdVar('content', 'URL_SEARCH', $urlSearch);
      $this->mrTemplate->AddVars('content', $requestData, '');

      if(empty($dataList)){
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         foreach ($dataList as $list) {
            $list['nomor']       = $start;
            $list['class_name']  = ($start % 2 <> 0) ? 'table-common-even' : '';
            $this->mrTemplate->AddVars('data_list', $list, '');
            $this->mrTemplate->parseTemplate('data_list', 'a');
            $start++;
         }
      }
   }
}
?>