<?php
/**
* ================= doc ====================
* FILENAME     : ViewListSppu.html.class.php
* @package     : ViewListSppu
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-04-08
* @Modified    : 2015-04-08
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/finansi_hapus_bp/business/SppuBp.class.php';

class ViewListSppuBp extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/finansi_hapus_bp/template/');
      $this->SetTemplateFile('view_list_sppu_bp.html');
   }

   function ProcessRequest(){
      $mObj          = new SppuBp();
      $messenger     = Messenger::Instance()->Receive(__FILE__);
      $requestData   = array();
      $get_date      = getdate();
      $currMon       = (int)$get_date['mon'];
      $currDay       = (int)$get_date['mday'];
      $currYear      = (int)$get_date['year'];
      $tahun_awal    = date('Y',time())-5;
      $tahun_akhir   = date('Y', time())+5;
      $message       = $style = $messengerData = NULL;

      if(isset($mObj->_POST['btnSearch'])){
         $tglAwal_day      = (int)$mObj->_POST['tanggal_awal_day'];
         $tglAwal_mon      = (int)$mObj->_POST['tanggal_awal_mon'];
         $tglAwal_year     = (int)$mObj->_POST['tanggal_awal_year'];
         $tglAkhir_day     = (int)$mObj->_POST['tanggal_akhir_day'];
         $tglAkhir_mon     = (int)$mObj->_POST['tanggal_akhir_mon'];
         $tglAkhir_year    = (int)$mObj->_POST['tanggal_akhir_year'];
         $requestData['kode']    = $mObj->_POST['kode'];
         $requestData['nomor_bp']         = $mObj->_POST['nomor_bp'];
         $requestData['nomorPengajuan']   = $mObj->_POST['nomorPengajuan'];
         $requestData['tanggal_awal']     = date('Y-m-d', mktime(0,0,0, $tglAwal_mon, $tglAwal_day, $tglAwal_year));
         $requestData['tanggal_akhir']    = date('Y-m-d', mktime(0,0,0, $tglAkhir_mon, $tglAkhir_day, $tglAkhir_year));
      }elseif(isset($mObj->_GET['search'])){
         $requestData['kode']    = Dispatcher::Instance()->Decrypt($mObj->_GET['kode']);
         $requestData['nomor_bp']         = Dispatcher::Instance()->Decrypt($mObj->_GET['nomor_bp']);
         $requestData['nomorPengajuan']   = Dispatcher::Instance()->Decrypt($mObj->_GET['nomorPengajuan']);
         $requestData['tanggal_awal']     = date('Y-m-d', strtotime($mObj->_GET['tanggal_awal']));
         $requestData['tanggal_akhir']    = date('Y-m-d', strtotime($mObj->_GET['tanggal_akhir']));
      }else{
         $requestData['kode']          = '';
         $requestData['nomor_bp']         = '';
         $requestData['nomorPengajuan']   = '';
         $requestData['tanggal_awal']     = date('Y-m-d', mktime(0,0,0, $currMon, $currDay-7, $currYear));
         $requestData['tanggal_akhir']    = date('Y-m-d', mktime(0,0,0, $currMon, $currDay, $currYear));
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
      $url        = Dispatcher::Instance()->GetUrl(
        Dispatcher::Instance()->mModule,
        Dispatcher::Instance()->mSubModule,
        Dispatcher::Instance()->mAction,
        Dispatcher::Instance()->mType
      ).'&search='.Dispatcher::Instance()->Encrypt(1).'&'.$queryString;

      $destination_id   = "subcontent-element";
      $dataList         = $mObj->getDataSppu($offset, $limit, (array)$requestData);
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

      # GTFW Tanggal
      Messenger::Instance()->SendToComponent(
         'tanggal',
         'Tanggal',
         'view',
         'html',
         'tanggal_awal',
         array(
            $requestData['tanggal_awal'],
            $tahun_awal,
            $tahun_akhir,
            false,
            false,
            false
         ),
         Messenger::CurrentRequest
      );

      Messenger::Instance()->SendToComponent(
         'tanggal',
         'Tanggal',
         'view',
         'html',
         'tanggal_akhir',
         array(
            $requestData['tanggal_akhir'],
            $tahun_awal,
            $tahun_akhir,
            false,
            false,
            false
         ),
         Messenger::CurrentRequest
      );

      if($messenger){
         $messengerData    = $messenger[0][0];
         $message          = $messenger[0][1];
         $style            = $messenger[0][2];
      }

      $return['request_data']    = $requestData;
      $return['query_string']    = $queryString;
      $return['data_list']       = $dataList;
      $return['start']           = $offset+1;
      $return['message']         = $message;
      $return['style']           = $style;
      return $return;
   }

   function ParseTemplate($data = null){
      $queryString   = $data['query_string'];
      $dataList      = $data['data_list'];
      $start         = $data['start'];
      $requestData   = $data['request_data'];
      $message       = $data['message'];
      $style         = $data['style'];
      $urlSearch     = Dispatcher::Instance()->GetUrl(
         'finansi_hapus_bp',
         'ListSppuBp',
         'view',
         'html'
      );

      $parseUrl = parse_url($queryString);
      $urlExploded = explode('&', $parseUrl['path']);
      $urlIndex = 0;
      foreach ($urlExploded as $url) {
          list($urlKey[$urlIndex], $urlValue[$urlIndex]) = explode('=', $url);
          $patern = '/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/';
          $patern1 = '/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})/';
          if ((preg_match($patern, $urlValue[$urlIndex]) || preg_match($patern1, $urlValue[$urlIndex])) && strtotime($urlValue[$urlIndex]) !== false) {
              $urlValue[$urlIndex] = date('Y/m/d', strtotime($urlValue[$urlIndex]));
          }
          $urlIndex += 1;
      }
      unset($urlIndex);
      $keyUrl = implode('|', $urlKey);
      $valueUrl = implode('|', $urlValue);

      $this->mrTemplate->AddVar('content', 'CONTROL_LABEL', $label);
      $this->mrTemplate->AddVar('content', 'CONTROL_ACTION', $url_delete);
      $this->mrTemplate->AddVar('content', 'CONTROL_RETURN', $url_return);

      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);
      $this->mrTemplate->AddVars('content', $requestData);
      if(empty($dataList)){
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         
         foreach ($dataList as $list) {
            $list['number']         = $start;
            $list['class_name']     = ($start % 2 <> 0) ? 'table-common-even' : '';
            $list['nominal']        = number_format($list['nominal'], 2, ',','.');

            // url delete bp
            $urlAccept = 'finansi_hapus_bp|DeleteBp|do|json-search|' . $keyUrl . '-1|' . $valueUrl;
            $urlReturn = 'finansi_hapus_bp|ListSppuBp|view|html-search|' . $keyUrl . '-1|' . $valueUrl;
            $label = 'Nomor Bank Payment';
            $pesanHapus = 'Penghapusan Data ini akan menghapus Data secara permanen.';
            $list['url_hapus_bp'] = Dispatcher::Instance()->GetUrl(
                'confirm', 
                'confirmDelete', 
                'do', 
                'html'
            ) . 
            '&urlDelete=' . $urlAccept .
            '&urlReturn=' . $urlReturn .
            '&id=' .Dispatcher::Instance()->Encrypt($list['id']).
            '&label=' . $label .
            '&dataName=' .  'No.BP: '. $list['nomor_bp'] .' ( No.SPPU: '.$list['nomor'].' )'.
            '&message=' . $pesanHapus;
            
            if($list['bank_payment'] == 'Y' && $list['cash_receipt'] == 'T'){
               $list['nomor_bp']    = $list['nomor_bp'];
               $list['nomor_cr']    = '';
            }elseif($list['bank_payment'] == 'Y' && $list['cash_receipt'] == 'Y'){
               $list['nomor_bp']    = $list['nomor_bp'];
               $list['nomor_cr']    = $list['nomor_cr'];
            }else{
               $list['nomor_bp']    = '';
               $list['nomor_cr']    = '';
            }
            
            if(!empty($list['nomor_bp'])){
                if($list['is_transaksi'] == 'Ya'){
                    $this->mrTemplate->AddVar('is_hapus_bp', 'IS_HAPUS_BP', 'TRANSAKSI_YA');          
                } else {
                    $this->mrTemplate->AddVar('is_hapus_bp', 'IS_HAPUS_BP', 'YES');                     
                    $this->mrTemplate->AddVar('is_hapus_bp', 'URL_HAPUS_BP', $list['url_hapus_bp']);
                }
            } else {
                $this->mrTemplate->AddVar('is_hapus_bp', 'IS_HAPUS_BP', 'NO');          
            }
            $this->mrTemplate->AddVars('data_list', $list);
            $this->mrTemplate->parseTemplate('data_list', 'a');
            $start++;
         }
      }

      if($message){
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $message);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $style);
      }
   }
}
?>