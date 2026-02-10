<?php 
#doc
# package:     ViewPopupOutputKegiatan
# scope:       PUBLIC
# @created     ucil.619
# @Author      Eko Susilo
# @Created     2013-09-23
# @Modified    2013-09-23
# @Analysts    
# @copyright   Copyright (c) 2012 Gamatechno
#/doc
require_once GTFWConfiguration::GetValue('application','docroot').
'module/'.Dispatcher::Instance()->mModule.'/business/AppReferensi.class.php';

class ViewPopupOutputKegiatan extends HtmlResponse
{
   #   internal variables
   private $mObj;
   protected $_POST;
   protected $_GET;
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
   
   function TemplateModule(){
     $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
     'module/'.Dispatcher::Instance()->mModule.'/template/');
     $this->SetTemplateFile('view_popup_output_kegiatan.html');
   }
   
   function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }

   function ProcessRequest(){
      $taId                = Dispatcher::Instance()->Decrypt($this->_GET['taId']);
      $requestData         = array();
      $requestData['taId'] = Dispatcher::Instance()->Decrypt($this->_GET['taId']);
      if(isset($this->_POST['btnSearch'])){
         $requestData['kode_kegiatan']    = trim($this->_POST['kode_kegiatan']);
         $requestData['kode']             = trim($this->_POST['kode']);
      }elseif(isset($this->_GET['search'])){
         $requestData['kode_kegiatan']    = Dispatcher::Instance()->Decrypt($this->_GET['kode_kegiatan']);
         $requestData['kode']             = Dispatcher::Instance()->Decrypt($this->_GET['kode']);
      }else{
         $requestData['kode_kegiatan']    = '';
         $requestData['kode']             = '';
      }

      foreach ($requestData as $key => $value) {
         $query[$key]         = Dispatcher::Instance()->Encrypt($value);
      }
      $queryString            = urldecode(http_build_query($query));

      $offset        = 0;
      $limit         = 20;
      $page          = 0;
      $total_data    = $this->mObj->GetCountOutput((array)$requestData);
      if(isset($_GET['page'])){
         $page       = (string) $_GET['page']->StripHtmlTags()->SqlString()->Raw();
         $offset     = ($page - 1) * $limit;
      }
      #paging url
      $dataList      = $this->mObj->GetDataOutput($offset, $limit, (array)$requestData);
      $url           = Dispatcher::Instance()->GetUrl(
        Dispatcher::Instance()->mModule,
        Dispatcher::Instance()->mSubModule,
        Dispatcher::Instance()->mAction,
        Dispatcher::Instance()->mType
      ).'&search='.Dispatcher::Instance()->Encrypt(1).'&'.$queryString;
      
      $destination_id = "popup-subcontent";
      
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
      
      $return['data_list']    = $this->mObj->ChangeKeyName($dataList);
      $return['request_data'] = $requestData;
      $return['start']        = $offset+1;
      return $return;
   }
   
   function ParseTemplate($data = null){
      $dataList      = $data['data_list'];
      $requestData   = $data['request_data'];
      $start         = $data['start'];
      $urlSearch     = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         Dispatcher::Instance()->mSubModule,
         Dispatcher::Instance()->mAction,
         Dispatcher::Instance()->mType
      ).'&'.self::__getQueryString();
      
      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);
      $this->mrTemplate->AddVars('content', $requestData, '');

      if(empty($dataList)){
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         
         $kegiatan      = '';
         $index         = 0;
         $dataLists     = array();

         for ($i=0; $i < count($dataList);) { 
            if((int)$dataList[$i]['kegiatan_id'] === (int)$kegiatan){
               $dataLists[$index]['id']         = $dataList[$i]['output_id'];
               $dataLists[$index]['kode']       = $dataList[$i]['output_kode'];
               $dataLists[$index]['nama']       = $dataList[$i]['output_nama'];
               $dataLists[$index]['keg_id']     = $dataList[$i]['kegiatan_id'];
               $dataLists[$index]['keg_kode']   = $dataList[$i]['kegiatan_kode'];
               $dataLists[$index]['keg_nama']   = $dataList[$i]['kegiatan_nama'];
               $dataLists[$index]['nomor']      = $start;
               $dataLists[$index]['class_name'] = ($start % 2 <> 0) ? 'table-common-even' : '';
               $dataLists[$index]['type']       = 'child';
               $i++;
               $start++;
            }else{
               $kegiatan         = (int)$dataList[$i]['kegiatan_id'];
               $dataLists[$index]['id']      = $dataList[$i]['kegiatan_id'];
               $dataLists[$index]['kode']    = $dataList[$i]['kegiatan_kode'];
               $dataLists[$index]['nama']    = $dataList[$i]['kegiatan_nama'];
               $dataLists[$index]['class_name'] = 'table-common-even1';
               $dataLists[$index]['style']   = 'font-weight: bold;';
               $dataLists[$index]['type']    = 'parent';
            }

            $index++;
         }

         foreach ($dataLists as $list) {
            $this->mrTemplate->AddVars('status', $list, '');
            $this->mrTemplate->AddVar('status', 'TYPE', strtoupper($list['type']));
            $this->mrTemplate->AddVars('data_list', $list, '');
            $this->mrTemplate->parseTemplate('data_list', 'a');
         }
      }
   }

   /**
    * @param string  path_info url to be parsed, default null
    * @return string parsed_url based on Dispatcher::Instance()->getQueryString();
    */
   public function __getQueryString($pathInfo = null)
   {
      $parseUrl            = is_null($pathInfo) ? parse_url($_SERVER['QUERY_STRING']) : parse_url($pathInfo);
      $explodedUrl         = explode('&', $parseUrl['path']);
      foreach ($explodedUrl as $path) {
         if(preg_match('/^mod=[a-zA-Z0-9_-]+/', $path)){
            continue;
         }
         if(preg_match('/^sub=[a-zA-Z0-9_-]+/', $path)){
            continue;
         }
         if(preg_match('/^act=[a-zA-Z0-9_-]+/', $path)){
            continue;
         }
         if(preg_match('/^typ=[a-zA-Z0-9_-]+/', $path)){
            continue;
         }
         if(preg_match('/^ascomponent=[a-zA-Z0-9_-]+/', $path)){
            continue;
         }
         if(preg_match('/uniqid=[a-zA-Z0-9_-]+/', $path)){
            continue;
         }
   
         list($key, $value)   = explode('=', $path);
         $requestData[$key]   = Dispatcher::Instance()->Decrypt($value);
      }
      if(method_exists(Dispatcher::Instance(), 'getQueryString') === true){
         $queryString         = Dispatcher::Instance()->getQueryString($requestData);
      }else{
         foreach ($requestData as $key => $value) {
            $query[$key]      = Dispatcher::Instance()->Encrypt($value);
         }
         $queryString         = urldecode(http_build_query($query));
      }
      return $queryString;
   }
}
?>