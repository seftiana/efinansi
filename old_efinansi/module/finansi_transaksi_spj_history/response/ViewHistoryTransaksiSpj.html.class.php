<?php
/**
* ================= doc ====================
* FILENAME     : ViewHistoryTransaksiSpj.html.class.php
* @package     : ViewHistoryTransaksiSpj
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-04-24
* @Modified    : 2015-04-24
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/finansi_transaksi_spj_history/business/HistoryTransaksiSpj.class.php';

class ViewHistoryTransaksiSpj extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/finansi_transaksi_spj_history/template/');
      $this->SetTemplateFile('view_history_transaksi_spj.html');
   }

   function ProcessRequest(){
      $messenger     = Messenger::Instance()->Receive(__FILE__);
      $mObj          = new HistoryTransaksiSpj();
      $mUnitObj      = new UserUnitKerja();
      $message       = $style = $messengerData = NULL;
      $requestData   = array();
      $queryString   = '';
      $userId        = $mObj->getUserId();
      $setDate       = $mObj->setDate();
      extract($setDate);   // min_year, max_year
      $getdate       = getdate();
      $curr_year     = (int)$getdate['year'];
      $curr_mon      = (int)$getdate['mon'];
      $curr_day      = (int)$getdate['mday'];
      $arrPeriodeTahun     = $mObj->getTahunPeriode();
      $periodeTahun        = $mObj->getTahunPeriode(array(
         'active' => true
      ));
      $arrTahunPembukuan   = $mObj->getTahunPembukuan();
      $tahunPembukuan      = $mObj->getTahunPembukuan(true);
      $unitKerja           = $mUnitObj->GetUnitKerjaRefUser($userId);
      $requestData['ta_id']      = $periodeTahun[0]['id'];
      $requestData['tp_id']      = $tahunPembukuan[0]['id'];
      $requestData['tanggal_awal']  = date('Y-m-d', mktime(0,0,0, $curr_mon, 1, $curr_year));
      $requestData['tanggal_akhir'] = date('Y-m-t', mktime(0,0,0, $curr_mon, 1, $curr_year));
      $requestData['unit_id']       = $unitKerja['id'];
      $requestData['unit_nama']     = $unitKerja['nama'];
      $requestData['mak_id']        = '';
      $requestData['mak_nama']      = '';
      $requestData['kode']          = '';

      if(isset($mObj->_POST['btnSearch'])){
         $startDate_day    = (int)$mObj->_POST['start_date_day'];
         $startDate_mon    = (int)$mObj->_POST['start_date_mon'];
         $startdate_year   = (int)$mObj->_POST['start_date_year'];
         $endData_day      = (int)$mObj->_POST['end_date_day'];
         $endDate_mon      = (int)$mObj->_POST['end_date_mon'];
         $endDate_year     = (int)$mObj->_POST['end_date_year'];
         $requestData['ta_id']      = $mObj->_POST['periode_tahun'];
         $requestData['unit_id']    = $mObj->_POST['unit_id'];
         $requestData['unit_nama']  = $mObj->_POST['unit_nama'];
         $requestData['mak_id']     = $mObj->_POST['mak_id'];
         $requestData['mak_nama']   = $mObj->_POST['mak_nama'];
         $requestData['kode']       = $mObj->_POST['kode'];
         $requestData['tanggal_awal']  = date('Y-m-d', mktime(0,0,0, $startDate_mon, $startDate_day, $startdate_year));
         $requestData['tanggal_akhir'] = date('Y-m-d', mktime(0,0,0, $endDate_mon, $endData_day, $endDate_year));
      }elseif(isset($mObj->_GET['search'])){
         $requestData['ta_id']      = Dispatcher::Instance()->Decrypt($mObj->_GET['ta_id']);
         $requestData['unit_id']    = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_id']);
         $requestData['unit_nama']  = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_nama']);
         $requestData['mak_id']     = Dispatcher::Instance()->Decrypt($mObj->_GET['mak_id']);
         $requestData['mak_nama']   = Dispatcher::Instance()->Decrypt($mObj->_GET['mak_nama']);
         $requestData['kode']       = Dispatcher::Instance()->Decrypt($mObj->_GET['kode']);
         $requestData['tanggal_awal']  = date('Y-m-d', strtotime(Dispatcher::Instance()->Decrypt($mObj->_GET['tanggal_awal'])));
         $requestData['tanggal_akhir'] = date('Y-m-d', strtotime(Dispatcher::Instance()->Decrypt($mObj->_GET['tanggal_akhir'])));
      }

      foreach ($arrPeriodeTahun as $ta) {
         if((int)$ta['id'] === (int)$requestData['ta_id']){
            $requestData['ta_nama'] = $ta['nama'];
         }
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
      $total_data     = total_data;
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

      $destination_id   = "subcontent-element";
      $dataList         = $mObj->getDataTransaksi($offset, $limit, $requestData);
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
      # Combobox
      Messenger::Instance()->SendToComponent(
         'combobox',
         'Combobox',
         'view',
         'html',
         'periode_tahun',
         array(
            'periode_tahun',
            $arrPeriodeTahun,
            $requestData['ta_id'],
            false,
            'id="cmb_periode_tahun"'
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
            $requestData['tanggal_awal'],
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
            $requestData['tanggal_akhir'],
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

      $return['request_data']    = $requestData;
      $return['unit_kerja']      = $unitKerja;
      $return['query_string']    = $queryString;
      $return['data_list']       = $dataList;
      $return['start']           = $offset+1;
      $return['message']         = $message;
      $return['style']           = $style;
      return $return;
   }

   function ParseTemplate($data = null){
      $message       = $data['message'];
      $style         = $data['style'];
      $dataList      = $data['data_list'];
      $start         = $data['start'];
      $unitKerja     = $data['unit_kerja'];
      $requestData   = $data['request_data'];
      $queryString   = $data['query_string'];
      $urlSearch     = Dispatcher::Instance()->GetUrl(
         'finansi_transaksi_spj_history',
         'HistoryTransaksiSpj',
         'view',
         'html'
      );

      $urlPopupUnitkerja   = Dispatcher::Instance()->GetUrl(
         'finansi_transaksi_spj_history',
         'PopupUnitKerja',
         'view',
         'html'
      );

      $urlPopupRealisasi   = Dispatcher::Instance()->GetUrl(
         'finansi_transaksi_spj_history',
         'PopupReferensiKegiatan',
         'view',
         'html'
      );

      $urlDetail           = Dispatcher::Instance()->GetUrl(
         'finansi_transaksi_spj_history',
         'DetailTransaksi',
         'view',
         'html'
      );

      $urlEdit             = Dispatcher::Instance()->GetUrl(
         'finansi_transaksi_spj_history',
         'EditTransaksiSpj',
         'view',
         'html'
      ).'&'.$queryString;

      $urlCetakTransaksi   = Dispatcher::Instance()->GetUrl(
         'finansi_transaksi_spj_history',
         'FormCetakTransaksi',
         'view',
         'html'
      ).'&'.$queryString;

      $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);
      $this->mrTemplate->AddVar('content', 'URL_POPUP_REALISASI', $urlPopupRealisasi);
      $this->mrTemplate->AddVar('data_unit', 'LEVEL', strtoupper($unitKerja['status']));
      $this->mrTemplate->AddVar('data_unit', 'unit_id', $requestData['unit_id']);
      $this->mrTemplate->AddVar('data_unit', 'unit_nama', $requestData['unit_nama']);
      $this->mrTemplate->AddVar('data_unit', 'URL_POPUP_UNIT', $urlPopupUnitkerja);
      $this->mrTemplate->AddVars('content', $requestData);

      $parseUrl      = parse_url($queryString);
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

      if(empty($dataList)){
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         $urlAccept              = 'finansi_transaksi_spj_history|DeleteTransaksi|do|json-search|'.$keyUrl.'-1|'.$valueUrl;
         $urlReturn              = 'finansi_transaksi_spj_history|HistoryTransaksiSpj|view|html-search|'.$keyUrl.'-1|'.$valueUrl;
         $labelDelete            = GTFWConfiguration::GetValue('language', 'history_transaksi_spj');
         $messageDelete          = 'Penghapusan Data ini akan menghapus Data Transaksi secara permanen.';
         foreach ($dataList as $list) {
            $list['url_delete']     = Dispatcher::Instance()->GetUrl(
               'confirm',
               'confirmDelete',
               'do',
               'html'
            ).'&urlDelete='. $urlAccept
            .'&urlReturn='.$urlReturn
            .'&id='.$list['id']
            .'&label='.$labelDelete
            .'&dataName='.$list['kkb']
            .'&message='.$messageDelete;

            $this->mrTemplate->clearTemplate('link_jurnal');
            $list['nomor']       = $start;
            $list['url_detail']  = $urlDetail.'&trans_id='.Dispatcher::Instance()->Encrypt($list['id']);
            $list['url_edit']    = $urlEdit.'&trans_id='.Dispatcher::Instance()->Encrypt($list['id']);
            $list['url_cetak_transaksi']  = $urlCetakTransaksi.'&trans_id='.Dispatcher::Instance()->Encrypt($list['id']);
            if($list['nominal'] < 0){
               $list['nominal']  = '('.number_format(abs($list['nominal']), 2, ',','.').')';
            }else{
               $list['nominal']  = number_format($list['nominal'], 2, ',','.');
            }

            switch ($list['status_jurnal']) {
               case 'Y':
                  $this->mrTemplate->AddVar('link_jurnal', 'STATUS', 'YES');
                  break;
               case 'T':
                  $this->mrTemplate->AddVar('link_jurnal', 'STATUS', 'NO');
                  $this->mrTemplate->AddVar('link_jurnal', 'URL_EDIT', $list['url_edit']);
                  $this->mrTemplate->AddVar('link_jurnal', 'URL_DELETE', $list['url_delete']);
                  break;
               default:
                  $this->mrTemplate->AddVar('link_jurnal', 'STATUS', 'NO');
                  break;
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