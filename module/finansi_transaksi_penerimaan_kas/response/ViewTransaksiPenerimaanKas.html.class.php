<?php
/**
* ================= doc ====================
* FILENAME     : ViewTransaksiPenerimaanKas.html.class.php
* @package     : ViewTransaksiPenerimaanKas
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-05-19
* @Modified    : 2015-05-19
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/finansi_transaksi_penerimaan_kas/business/TransaksiPenerimaanKas.class.php';

class ViewTransaksiPenerimaanKas extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/finansi_transaksi_penerimaan_kas/template/');
      $this->SetTemplateFile('view_transaksi_penerimaan_kas.html');
   }

   function ProcessRequest(){
      $mObj       = new TransaksiPenerimaanKas();
      $messenger  = Messenger::Instance()->Receive(__FILE__);
      $request_data     = array();
      $query_string     = '';
      $message          = $style = null;
      $tahunAnggaranYear = $mObj->getTahunAnggaranYear();
     
      $get_date         = getdate();
      $curr_mon         = (int)$get_date['mon'];
      $curr_day         = (int)$get_date['mday'];
      $curr_year        = (int)$get_date['year'];
      $min_year         = $tahunAnggaranYear['tahun_awal'];//$curr_year-5;
      $max_year         = $tahunAnggaranYear['tahun_khir'];//$curr_year+5;
      $tahunPembukuan   = $mObj->getTahunPembukuanPeriode(array('open' => true));

      if(isset($mObj->_POST['btnSearch'])){
         $startDate_day       = (int)$mObj->_POST['start_date_day'];
         $startDate_mon       = (int)$mObj->_POST['start_date_mon'];
         $startDate_year      = (int)$mObj->_POST['start_date_year'];
         $endDate_day         = (int)$mObj->_POST['end_date_day'];
         $endDate_mon         = (int)$mObj->_POST['end_date_mon'];
         $endDate_year        = (int)$mObj->_POST['end_date_year'];
         $request_data['kode']         = trim($mObj->_POST['kode']);
         $request_data['start_date']   = date('Y-m-d', mktime(0,0,0, $startDate_mon, $startDate_day, $startDate_year));
         $request_data['end_date']     = date('Y-m-d', mktime(0,0,0, $endDate_mon, $endDate_day, $endDate_year));
      }elseif(isset($mObj->_GET['search'])){
         $request_data['kode']         = Dispatcher::Instance()->Decrypt($mObj->_GET['kode']);
         $request_data['start_date']   = date('Y-m-d', strtotime(Dispatcher::Instance()->Decrypt($mObj->_GET['start_date'])));
         $request_data['end_date']     = date('Y-m-d', strtotime(Dispatcher::Instance()->Decrypt($mObj
            ->_GET['end_date'])));
      }else{
         $request_data['kode']         = '';
         $request_data['start_date']   = date('Y-m-d', mktime(0,0,0, $curr_mon, $curr_day-7, $curr_year));
         $request_data['end_date']     = date('Y-m-d', mktime(0,0,0, $curr_mon, $curr_day, $curr_year));
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

      $offset     = 0;
      $limit      = 20;
      $page       = 0;
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

      $destination_id   = "subcontent-element";
      $data_list        = $mObj->getData($offset, $limit, $request_data);
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
         'start_date',
         array(
            $request_data['start_date'],
            $min_year,
            $max_year,
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
         'end_date',
         array(
            $request_data['end_date'],
            $min_year,
            $max_year,
            false,
            false,
            false
         ),
         Messenger::CurrentRequest
      );

      if($messenger){
         $message    = $messenger[0][1];
         $style      = $messenger[0][2];
      }
      $start         = $offset+1;
      return compact('message', 'style', 'tahunPembukuan', 'request_data', 'query_string','data_list', 'start');
   }

   function ParseTemplate($data = null){
      extract($data);
      $url_search       = Dispatcher::Instance()->GetUrl(
         'finansi_transaksi_penerimaan_kas',
         'TransaksiPenerimaanKas',
         'view',
         'html'
      );

      $url_add          = Dispatcher::Instance()->GetUrl(
         'finansi_transaksi_penerimaan_kas',
         'Transaksi',
         'view',
         'html'
      ).'&'.$query_string;

      $url_export    = Dispatcher::Instance()->GetUrl(
         'finansi_transaksi_penerimaan_kas',
         'BuktiTransaksi',
         'view',
         'xlsx'
      ).'&'.$query_string;

      $url_detail    = Dispatcher::Instance()->GetUrl(
         'finansi_transaksi_penerimaan_kas',
         'BuktiTransaksi',
         'view',
         'html'
      ).'&'.$query_string;

      $url_edit      = Dispatcher::Instance()->GetUrl(
         'finansi_transaksi_penerimaan_kas',
         'EditTransaksi',
         'view',
         'html'
      ).'&'.$query_string;


      $parseUrl      = parse_url($query_string);
      $urlExploded   = explode('&', $parseUrl['path']);
      $urlIndex      = 0;
      foreach ($urlExploded as $url) {
         list($urlKey[$urlIndex], $urlValue[$urlIndex]) = explode('=', $url);
         $patern     = '/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/';
         $patern1    = '/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})/';
         if((preg_match($patern, $urlValue[$urlIndex]) || preg_match($patern1, $urlValue[$urlIndex])) && strtotime($urlValue[$urlIndex]) !== false){
            $urlValue[$urlIndex]    = date('Y/m/d', strtotime($urlValue[$urlIndex]));
         }
         $urlIndex   += 1;
      }
      unset($urlIndex);
      $keyUrl     = implode('|', $urlKey);
      $valueUrl   = implode('|', $urlValue);

      $this->mrTemplate->AddVars('content', compact('url_search', 'url_add'));
      $this->mrTemplate->AddVars('content', $request_data);

      if(empty($data_list)){
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         $urlAccept              = 'finansi_transaksi_penerimaan_kas|DeleteTransaksi|do|json-search|'.$keyUrl.'-1|'.$valueUrl;
         $urlReturn              = 'finansi_transaksi_penerimaan_kas|TransaksiPenerimaanKas|view|html-search|'.$keyUrl.'-1|'.$valueUrl;
         $label                  = 'Transaksi Penerimaan Kas';
         $msg                    = 'Penghapusan Data ini akan menghapus Data Transaksi secara permanen.';
         foreach ($data_list as $list) {
            $list['url_delete']     = Dispatcher::Instance()->GetUrl(
               'confirm',
               'confirmDelete',
               'do',
               'html'
            ).'&urlDelete='. $urlAccept
            .'&urlReturn='.$urlReturn
            .'&id='.$list['id']
            .'&label='.$label
            .'&dataName='.$list['bpkb']. ' : '.number_format($list['nominal'], 0, ',','.')
            .'&message='.$msg;

            $list['nomor']             = $start;
            $list['tahun_pembukuan']   = $list['tp_id'];
            $list['nominal']           = number_format($list['nominal'], 2, ',','.');
            $list['class_name']        = ($start % 2 <> 0) ? 'table-common-even' : '';
            if($list['is_jurnal'] == 'Y') {
               $list['status_jurnal'] =  'Sudah';
            } else {
                $list['status_jurnal'] =  'Belum';
            }

            // Cek status jurnal & tahun pembukuan aktif
            if($list['is_jurnal_approve'] == 'Y') {
               if($list['tahun_pembukuan'] != $tahunPembukuan[0]['id']){
                  $this->mrTemplate->SetAttribute('is_jurnal_approve', 'visibility', 'hidden');
               }else{
                  $this->mrTemplate->SetAttribute('is_jurnal_approve', 'visibility', 'hidden');
               }
               $list['status_approval_jurnal'] =  '<img src="images/icons/16/tick_circle.png" alt="approve" />';

            } else {
               if($list['tahun_pembukuan'] != $tahunPembukuan[0]['id']){
                  $this->mrTemplate->SetAttribute('is_jurnal_approve', 'visibility', 'hidden');
               }else{
                  $this->mrTemplate->SetAttribute('is_jurnal_approve', 'visibility', 'visible');
               }
                $list['status_approval_jurnal'] =  '<img src="images/icons/16/icon-warning-16x16.gif" alt="warning" />';
            }
                        
            $list['url_export']  = $url_export.'&transaksi_id='.Dispatcher::Instance()->Encrypt($list['id']);
            $list['url_detail']  = $url_detail.'&transaksi_id='.Dispatcher::Instance()->Encrypt($list['id']);
            $list['url_edit']    = $url_edit.'&transaksi_id='.Dispatcher::Instance()->Encrypt($list['id']);
            $this->mrTemplate->AddVar('is_jurnal_approve','URL_DELETE',$list['url_delete']);
            $this->mrTemplate->AddVar('is_jurnal_approve','URL_EDIT',$list['url_edit']);
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