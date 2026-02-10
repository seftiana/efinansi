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
'module/finansi_sppu/business/Sppu.class.php';

class ViewListSppu extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/finansi_sppu/template/');
      $this->SetTemplateFile('view_list_sppu.html');
   }

   function ProcessRequest(){
      $mObj             = new Sppu();
      $messenger        = Messenger::Instance()->Receive(__FILE__);
      $requestData      = array();
      $get_date         = getdate();
      $currMon          = (int)$get_date['mon'];
      $currDay          = (int)$get_date['mday'];
      $currYear         = (int)$get_date['year'];
      $tahun_awal       = date('Y',time())-5;
      $tahun_akhir      = date('Y', time())+5;
      $message          = $style = $messengerData = NULL;
      $tahunPembukuan   = $mObj->getTahunPembukuanPeriode(array('open' => true));

      if(isset($mObj->_POST['btnSearch'])){
         $tglAwal_day      = (int)$mObj->_POST['tanggal_awal_day'];
         $tglAwal_mon      = (int)$mObj->_POST['tanggal_awal_mon'];
         $tglAwal_year     = (int)$mObj->_POST['tanggal_awal_year'];
         $tglAkhir_day     = (int)$mObj->_POST['tanggal_akhir_day'];
         $tglAkhir_mon     = (int)$mObj->_POST['tanggal_akhir_mon'];
         $tglAkhir_year    = (int)$mObj->_POST['tanggal_akhir_year'];
         $requestData['kode']    = $mObj->_POST['kode'];
         $requestData['nomorPengajuan']   = $mObj->_POST['nomorPengajuan'];
         $requestData['nomorBp']          = $mObj->_POST['nomorBp'];
         $requestData['tanggal_awal']     = date('Y-m-d', mktime(0,0,0, $tglAwal_mon, $tglAwal_day, $tglAwal_year));
         $requestData['tanggal_akhir']    = date('Y-m-d', mktime(0,0,0, $tglAkhir_mon, $tglAkhir_day, $tglAkhir_year));
      }elseif(isset($mObj->_GET['search'])){
         $requestData['kode']    = Dispatcher::Instance()->Decrypt($mObj->_GET['kode']);
         $requestData['nomorPengajuan']   = Dispatcher::Instance()->Decrypt($mObj->_GET['nomorPengajuan']);
         $requestData['nomorBp']   = Dispatcher::Instance()->Decrypt($mObj->_GET['nomorBp']);
         $requestData['tanggal_awal']     = date('Y-m-d', strtotime($mObj->_GET['tanggal_awal']));
         $requestData['tanggal_akhir']    = date('Y-m-d', strtotime($mObj->_GET['tanggal_akhir']));
      }else{
         $requestData['kode']          = '';
         $requestData['nomorPengajuan']   = '';
         $requestData['nomorBp']   = '';
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
      $return['tahun_pembukuan'] = $tahunPembukuan;
      $return['data_list']       = $dataList;
      $return['start']           = $offset+1;
      $return['message']         = $message;
      $return['style']           = $style;
      return $return;
   }

   function ParseTemplate($data = null){
      $queryString      = $data['query_string'];
      $dataList         = $data['data_list'];
      $tahunPembukuan   = $data['tahun_pembukuan'];
      $start            = $data['start'];
      $requestData      = $data['request_data'];
      $message          = $data['message'];
      $style            = $data['style'];
      $urlSearch        = Dispatcher::Instance()->GetUrl(
         'finansi_sppu',
         'ListSppu',
         'view',
         'html'
      );

      $urlSppu       = Dispatcher::Instance()->GetUrl(
         'finansi_sppu',
         'Sppu',
         'view',
         'html'
      );

      $urlExportExcel   = Dispatcher::Instance()->GetUrl(
         'finansi_sppu',
         'ExportExcelSppu',
         'view',
         'xlsx'
      );

      $urlExportBp      = Dispatcher::Instance()->GetUrl(
         'finansi_sppu',
         'ExportExcelBp',
         'view',
         'xlsx'
      );

      $urlExportCr   = Dispatcher::Instance()->GetUrl(
         'finansi_sppu',
         'ExportExcelCr',
         'view',
         'xlsx'
      );

      $urlDetail     = Dispatcher::Instance()->GetUrl(
         'finansi_sppu',
         'DetailSppu',
         'view',
         'html'
      ).'&'.$queryString;

      $urlEdit       = Dispatcher::Instance()->GetUrl(
         'finansi_sppu',
         'EditSppu',
         'view',
         'html'
      ).'&'.$queryString;

      $urlCetak       = Dispatcher::Instance()->GetUrl(
         'finansi_sppu',
         'KonfirmasiCetak',
         'view',
         'html'
      ).'&'.$queryString;

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
        
      # delete
      $label         = "Surat Pencairan Pengeluaran Uang (SPPU)";
      $url_delete    = Dispatcher::Instance()->GetUrl(
         'finansi_sppu',
         'DeleteSppu',
         'do',
         'json'
      ).'&'.$queryString;
      $url_return    = Dispatcher::Instance()->GetUrl(
         'finansi_sppu',
         'ListSppu',
         'view',
         'html'
      ).'&search=1&'.$queryString;
      Messenger::Instance()->Send(
         'confirm',
         'confirmDelete',
         'do',
         'html',
      array(
         $label,
         $url_delete,
         $url_return
      ),
      Messenger::NextRequest
      );

      $this->mrTemplate->AddVar('content', 'CONTROL_LABEL', $label);
      $this->mrTemplate->AddVar('content', 'CONTROL_ACTION', $url_delete);
      $this->mrTemplate->AddVar('content', 'CONTROL_RETURN', $url_return);
      $this->mrTemplate->AddVar(
         'content',
         'URL_DELETE',
         Dispatcher::Instance()->GetUrl(
            'confirm',
            'confirmDelete',
            'do',
            'html'
         )
      );

      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);
      $this->mrTemplate->AddVars('content', $requestData);
      $this->mrTemplate->AddVar('content', 'URL_ADD', $urlSppu);
      if(empty($dataList)){
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         
         foreach ($dataList as $list) {
            $list['number']         = $start;
            $list['class_name']     = ($start % 2 <> 0) ? 'table-common-even' : '';
            $list['nominal']        = number_format($list['nominal'], 2, ',','.');
            $list['url_detail']     = $urlDetail.'&data_id='.Dispatcher::Instance()->Encrypt($list['id']);
            $list['url_edit']       = $urlEdit.'&data_id='.Dispatcher::Instance()->Encrypt($list['id']);
            $list['url_export']     = $urlExportExcel.'&data_id='.Dispatcher::Instance()->Encrypt($list['id']);
            $list['export_bp']      = $urlExportBp.'&data_id='.Dispatcher::Instance()->Encrypt($list['id']);
            $list['export_cr']      = $urlExportCr.'&data_id='.Dispatcher::Instance()->Encrypt($list['id']);
            
            $list['url_cetak_bp']      = $urlCetak.'&data_id='.Dispatcher::Instance()->Encrypt($list['id']).
                                         '&tipe='.Dispatcher::Instance()->Encrypt(1);
            $list['url_cetak_cr']      = $urlCetak.'&data_id='.Dispatcher::Instance()->Encrypt($list['id']).
                                         '&tipe='.Dispatcher::Instance()->Encrypt(2);
            // url delete bp
            $urlAccept = 'finansi_sppu|DeleteBp|do|json-search|' . $keyUrl . '-1|' . $valueUrl;
            $urlReturn = 'finansi_sppu|ListSppu|view|html-search|' . $keyUrl . '-1|' . $valueUrl;
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
 
            if($list['is_transaksi'] == 'Ya'){ // Cek transaksi
               //if($list['tp_id'] != $tahunPembukuan[0]['id']){ // Cek Tahun Pembukuan Aktif
               //   $list['is_disabled'] = ' disabled="disabled"';
               //   $this->mrTemplate->AddVar('is_transaksi', 'IS_TRANSAKSI', 'YES'); 
               //   // $this->mrTemplate->AddVar('is_transaksi', 'DISPLAY_EDIT', 'display: none;'); 
               //   // $this->mrTemplate->AddVar('data_list', 'DISPLAY_BP', 'display: none;');   
               //}else{
               //   $list['is_disabled'] = ' disabled="disabled"';
               //   $this->mrTemplate->AddVar('is_transaksi', 'IS_TRANSAKSI', 'YES');  
               //}
               $list['is_disabled'] = ' disabled="disabled"';
               $this->mrTemplate->AddVar('is_transaksi', 'IS_TRANSAKSI', 'YES');  
            } else {
               // if($list['tp_id'] != $tahunPembukuan[0]['id']){
                  // $list['is_disabled'] = ' disabled="disabled"';
                  // $this->mrTemplate->AddVar('is_transaksi', 'IS_TRANSAKSI', 'BELUM'); 
                  // // $this->mrTemplate->AddVar('is_transaksi', 'DISPLAY_EDIT', 'display: none;'); 
                  // // $this->mrTemplate->AddVar('data_list', 'DISPLAY_BP', 'display: none;');   
               // }else{
                  // $list['is_disabled'] = '';
                  // $this->mrTemplate->AddVar('is_transaksi', 'IS_TRANSAKSI', 'BELUM');
               // }
               $list['is_disabled'] = '';
               $this->mrTemplate->AddVar('is_transaksi', 'IS_TRANSAKSI', 'BELUM');
                $this->mrTemplate->AddVar('is_transaksi', 'URL_EDIT', $list['url_edit']);
            }
            
            /* Untuk menampilkan button hapus BP dari SPPU */

            // if(!empty($list['nomor_bp'])){
            //     if($list['is_transaksi'] == 'Ya'){
            //         $this->mrTemplate->AddVar('is_hapus_bp', 'IS_HAPUS_BP', 'TRANSAKSI_YA');       
            //     } else {
            //         $this->mrTemplate->AddVar('is_hapus_bp', 'IS_HAPUS_BP', 'YES');                     
            //         $this->mrTemplate->AddVar('is_hapus_bp', 'URL_HAPUS_BP', $list['url_hapus_bp']);
            //     }
            // } else {
            //     $this->mrTemplate->AddVar('is_hapus_bp', 'IS_HAPUS_BP', 'NO');          
            // }

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