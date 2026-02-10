<?php
/**
* ================= doc ====================
* FILENAME     : ViewReferensiSppu.html.class.php
* @package     : ViewReferensiSppu
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-05-20
* @Modified    : 2015-05-20
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/finansi_transaksi_penerimaan_kas/business/AppReferensi.class.php';

class ViewReferensiSppu extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/finansi_transaksi_penerimaan_kas/template/');
      $this->SetTemplateFile('view_referensi_sppu.html');
   }

   function ProcessRequest(){
      $mObj       = new AppReferensi;
      $request_data  = array();
      $query_string  = '';

      if(isset($mObj->_POST['btnSearch'])){
         $request_data['kode']      = trim($mObj->_POST['kode']);
      }elseif(isset($mObj->_GET['search'])){
         $request_data['kode']      = Dispatcher::Instance()->Decrypt($mObj->_GET['kode']);
      }else{
         $request_data['kode']      = '';
      }

      if(method_exists(Dispatcher::Instance(), 'getQueryString')){
         # @param array
         $query_string     = Dispatcher::instance()->getQueryString($request_data);
      }else{
         $query            = array();
         foreach ($request_data as $key => $value) {
            $query[$key]   = Dispatcher::Instance()->Encrypt($value);
         }
         $query_string     = urldecode(http_build_query($query));
      }

      $offset         = 0;
      $limit          = 20;
      $page           = 0;
      if(isset($_GET['page'])){
         $page    = (string) $_GET['page']->StripHtmlTags()->SqlString()->Raw();
         $offset  = ($page - 1) * $limit;
      }
      #paging url
      $url        = Dispatcher::Instance()->GetUrl(
        Dispatcher::Instance()->mModule,
        Dispatcher::Instance()->mSubModule,
        Dispatcher::Instance()->mAction,
        Dispatcher::Instance()->mType
      ).'&search='.Dispatcher::Instance()->Encrypt(1).'&'.$query_string;

      $destination_id   = "popup-subcontent";
      $data_list        = $mObj->getReferensiKomponen($offset, $limit, $request_data);
      $total_data       = $mObj->Count();
      
      $data_sppu_item     = $mObj->getDetailSppuItem($offset, $limit, $request_data);
      
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

      $start      = $offset+1;
      $sppu_item_json = json_encode($data_sppu_item['data_grid']);
      
      return compact('request_data', 'query_string', 'data_list', 'start','sppu_item_json');
   }

   function ParseTemplate($data = null){
      extract($data);
      $referensi        = array();
      $url_search       = Dispatcher::Instance()->GetUrl(
         'finansi_transaksi_penerimaan_kas',
         'ReferensiSppu',
         'view',
         'html'
      );

      $this->mrTemplate->AddVars('content', $request_data);
      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $url_search);

      if(empty($data_list)){
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         foreach ($data_list as $list) {
            $referensi[$list['id']] = $list;
            $list['nomor']       = $start;
            $list['nominal_f']       = number_format( $list['nominal'],0,',','.');
            $list['class_name']  = ($start % 2 <> 0) ? 'table-common-even' : '';
            $this->mrTemplate->AddVars('data_list', $list);
            $this->mrTemplate->parseTemplate('data_list', 'a');
            $start++;
         }
      }

      $object['data']      = json_encode($referensi);
      $this->mrTemplate->AddVars('content', $object, 'KOMPONEN_');
      
      $object['item']      = $sppu_item_json;
      $this->mrTemplate->AddVars('content', $object, 'SPPU_');      
   }
}

?>