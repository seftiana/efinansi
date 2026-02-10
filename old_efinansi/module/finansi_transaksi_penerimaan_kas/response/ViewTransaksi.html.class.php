<?php
/**
* ================= doc ====================
* FILENAME     : ViewTransaksi.html.class.php
* @package     : ViewTransaksi
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-05-18
* @Modified    : 2015-05-18
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/finansi_transaksi_penerimaan_kas/business/TransaksiPenerimaanKas.class.php';

class ViewTransaksi extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/finansi_transaksi_penerimaan_kas/template/');
      $this->SetTemplateFile('view_transaksi.html');
   }

   function ProcessRequest(){
      $messenger        = Messenger::Instance()->Receive(__FILE__);
      $mObj             = new TransaksiPenerimaanKas();      
      $tahunAnggaranYear = $mObj->getTahunAnggaranYear();
      $query_string     = $mObj->_getQueryString();
      $query_string     = preg_replace('/(transaksi_id=[\d+])/', '', $query_string);
      $query_string     = preg_replace('/\&[\&]+/', '&', $query_string);
      $query_string     = preg_replace('/^[\&]/', '', $query_string);
      $query_return     = (!empty($query_string)) ? '&search=1&'.$query_string : '';
      $get_date         = getdate();
      $curr_mon         = (int)$get_date['mon'];
      $curr_day         = (int)$get_date['mday'];
      $curr_year        = (int)$get_date['year'];
      $min_year         = $tahunAnggaranYear['tahun_awal'];//$curr_year-5;
      $max_year         = $tahunAnggaranYear['tahun_khir'];//$curr_year+5;
      $message          = $style = $msg_data = NULL;
      $data_komponen    = array();
      $request_data     = array();
      $request_data['tanggal']      = date('Y-m-d', mktime(0,0,0, $curr_mon, $curr_day, $curr_year));
      //$request_data['bpkb']         = '';

      if($messenger){
         $msg_data      = $messenger[0][0];
         $message       = $messenger[0][1];
         $style         = $messenger[0][2];
         $tanggal_mon   = (int)$msg_data['tanggal_mon'];
         $tanggal_day   = (int)$msg_data['tanggal_day'];
         $tanggal_year  = (int)$msg_data['tanggal_year'];

         $request_data['id']     = $msg_data['data_id'];
         $request_data['sppu_id']   = $msg_data['sppu_id'];
         $request_data['bpkb']   = $msg_data['bpkb'];
         $request_data['coa_id_penyetor']    = $msg_data['coa_id_penyetor'];
         $request_data['nama_penyetor']      = $msg_data['nama_penyetor'];
         $request_data['rekening_penyetor']  = $msg_data['rekening_penyetor'];
         $request_data['coa_id_penerima']    = $msg_data['coa_id_penerima'];
         $request_data['nama_penerima']      = $msg_data['nama_penerima'];
         $request_data['rekening_penerima']  = $msg_data['rekening_penerima'];
         $request_data['tanggal']            = date('Y-m-d', mktime(0,0,0, $tanggal_mon, $tanggal_day, $tanggal_year));

         if(!empty($msg_data['komponen'])){
            $index      = 0;
            foreach ($msg_data['komponen'] as $komp) {
               $data_komponen[$index]['id']        = $komp['id'];
               $data_komponen[$index]['pid']       = $komp['pid'];
               $data_komponen[$index]['nama']      = $komp['keterangan'];
               $data_komponen[$index]['nominal']   = $komp['nominal'];
               $index++;
            }
         }
      }

      # GTFW Tanggal
      Messenger::Instance()->SendToComponent(
         'tanggal',
         'Tanggal',
         'view',
         'html',
         'tanggal',
         array(
            $request_data['tanggal'],
            $min_year,
            $max_year,
            false,
            false,
            false
         ),
         Messenger::CurrentRequest
      );

      $komponen['data']    = json_encode($data_komponen);
      $return     = compact('query_string', 'komponen', 'request_data', 'message', 'style', 'query_return');
      return $return;
   }

   function ParseTemplate($data = null){
      extract($data);
      $url_action       = Dispatcher::Instance()->GetUrl(
         'finansi_transaksi_penerimaan_kas',
         'SaveTransaksi',
         'do',
         'json'
      ).'&'.$query_string;
      $url_list      = Dispatcher::Instance()->GetUrl(
         'finansi_transaksi_penerimaan_kas',
         'TransaksiPenerimaanKas',
         'view',
         'html'
      ).$query_return;
      $urlPopupCoa      = Dispatcher::Instance()->GetUrl(      
         'finansi_transaksi_penerimaan_kas',
         'ReferensiCoa',
         'view',
         'html'
      );

      $url_popup_komponen  = Dispatcher::Instance()->GetUrl(
         'finansi_transaksi_penerimaan_kas',
         'ReferensiSppu',
         'view',
         'html'
      );
      $this->mrTemplate->AddVar('content', 'POPUP_REFERENSI_COA', $urlPopupCoa);
      $this->mrTemplate->addVar('content', 'URL_ACTION', $url_action);
      $this->mrTemplate->addVar('content', 'URL_LIST', $url_list);
      $this->mrTemplate->addVar('content', 'URL_POPUP_KOMPONEN', $url_popup_komponen);
      $this->mrTemplate->addVars('content', $request_data);
      $this->mrTemplate->addVars('content', $komponen, 'KOMP_');

      if($message){
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $message);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $style);
      }
   }
}
?>