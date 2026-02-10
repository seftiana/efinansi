<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/angsuran_detil/business/Komponen.class.php';
class ViewPopupKomponen extends HtmlResponse
{
   public function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') .
         'module/angsuran_detil/template');
      $this->SetTemplateFile('view_popup_komponen.html');
   }

   public function TemplateBase(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }

   public function ProcessRequest(){
      $mObj          = new Komponen();
      $requestData   = array();
      $queryString   = '';
      $requestData['kegiatan']   = Dispatcher::Instance()->Decrypt($mObj->_GET['kegiatan']);
      $requestData['unit_id']    = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_id']);
      if(isset($mObj->_POST['btncari'])){
         $requestData['kode']    = $mObj->_POST['kode'];
         $requestData['nama']    = $mObj->_POST['nama'];
      }elseif(isset($mObj->_GET['search'])){
         $requestData['kode']    = Dispatcher::Instance()->Decrypt($mObj->_GET['kode']);
         $requestData['nama']    = Dispatcher::Instance()->Decrypt($mObj->_GET['nama']);
      }else{
         $requestData['kode']    = '';
         $requestData['nama']    = '';
      }

      if(method_exists(Dispatcher::Instance(), 'getQueryString')){
         # @param array
         $queryString   = Dispatcher::instance()->getQueryString($requestData);
      }else{
         $query         = array();
         foreach ($requestData as $key => $value) {
            $query[$key]   = Dispatcher::Instance()->Encrypt($value);
         }
         $queryString      = urldecode(http_build_query($query));
      }

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
      ).'&search='.Dispatcher::Instance()->Encrypt(1).'&'.$queryString;

      $destination_id   = "popup-subcontent";
      $dataList         = $mObj->getData($offset, $limit, $requestData);
      $total_data       = $mObj->Count();

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

      $return['request_data']    = $requestData;
      $return['data_list']       = $dataList;
      $return['start']           = $offset+1;
      $return['query_string']    = $queryString;
      return $return;
   }

   function ParseTemplate($data = NULL) {
      $requestData   = $data['request_data'];
      $dataList      = $data['data_list'];
      $start         = $data['start'];
      $komponen      = array();
      $urlSearch     = Dispatcher::Instance()->GetUrl(
         'angsuran_detil',
         'popupKomponen',
         'view',
         'html'
      ).'&kegiatan='.Dispatcher::Instance()->Encrypt($requestData['kegiatan']).'&unit_id='.Dispatcher::Instance()->Encrypt($requestData['unit_id']);

      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);
      $this->mrTemplate->AddVars('content', $requestData);

      if(empty($dataList)){
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         foreach ($dataList as $list) {
            $komponen[$list['id']]  = (array)$list;
            $list['nomor']          = $start;
            $list['class_name']     = ($start % 2 <> 0) ? 'table-common-even' : '';
            $list['harga_satuan']   = number_format($list['biaya'], 0, ',','.');
            $this->mrTemplate->AddVars('data_list', $list);
            $this->mrTemplate->parseTemplate('data_list', 'a');
            $start++;
         }
      }

      $object['data']      = json_encode($komponen);
      $this->mrTemplate->AddVars('content', $object, 'KOMPONEN_');
   }
}
?>