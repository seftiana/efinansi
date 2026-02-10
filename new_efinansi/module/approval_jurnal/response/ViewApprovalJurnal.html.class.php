<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/approval_jurnal/response/ProcApprovalJurnal.proc.class.php';

class ViewApprovalJurnal extends HtmlResponse {

   protected $proc;
   protected $data;

   function ViewApprovalJurnal(){
      $this->proc = new ProcApprovalJurnal;
   }

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/approval_jurnal/template');
      $this->SetTemplateFile('view_approval_jurnal.html');
   }


   function ProcessRequest() {
      $messenger  = Messenger::Instance()->Receive(__FILE__);
      $mObj       = new ApprovalJurnal();
      $arrStatus  = array(
         array('id' => 'T', 'name' => 'Belum Disetujui'),
         array('id' => 'Y', 'name' => 'Sudah Disetujui')
      );
      $arrStatusKas        = array(
         array('id'=>'Y','name'=>'Ya'),
         array('id'=>'T','name'=>'Tidak')
      );
      $message             = $style = $messengerData = NULL;
      $dataPembukuan       = array();
      $arrBentukTransaksi  = $mObj->GetBentukTransaksi();
      $getdate             = getdate();
      $years               = $mObj->getRangeYears();
      $arrTipeTransaksi    = $mObj->GetComboTipeTransaksi();
      $tahunPembukuan      = $mObj->getTahunPembukuanPeriode(array('open' => true));
      $minYear             = $years['min_year'];
      $maxYear             = $years['max_year'];
      $currDay             = $getdate['mday'];
      $currMon             = $getdate['mon'];
      $currYear            = $getdate['year'];
      $queryString         = '';
      $requestData         = array();

      $requestData['start_date']    = date('Y-m-d', mktime(0,0,0, $currMon, 1, $currYear));
      $requestData['end_date']      = date('Y-m-t', mktime(0,0,0, $currMon, 1, $currYear));
      $requestData['status']        = 'T';
      $requestData['tipe']          = '';
      $requestData['kode']          = '';

      if(isset($mObj->_POST['btnSearch'])){
         $startDate_day       = (int)$mObj->_POST['start_date_day'];
         $startDate_mon       = (int)$mObj->_POST['start_date_mon'];
         $startDate_year      = (int)$mObj->_POST['start_date_year'];
         $endDate_day         = (int)$mObj->_POST['end_date_day'];
         $endDate_mon         = (int)$mObj->_POST['end_date_mon'];
         $endDate_year        = (int)$mObj->_POST['end_date_year'];
         $requestData['tipe']       = $mObj->_POST['tipe_transaksi'];
         $requestData['status']     = $mObj->_POST['status'];
         $requestData['start_date'] = date('Y-m-d', mktime(0,0,0, $startDate_mon, $startDate_day, $startDate_year));
         $requestData['end_date']   = date('Y-m-d', mktime(0,0,0, $endDate_mon, $endDate_day, $endDate_year));
         $requestData['kode']       = $mObj->_POST['kode'];
      }elseif(isset($mObj->_GET['search'])){
         $requestData['tipe']       = Dispatcher::Instance()->Decrypt($mObj->_GET['tipe']);
         $requestData['status']     = Dispatcher::Instance()->Decrypt($mObj->_GET['status']);
         $requestData['start_date'] = date('Y-m-d', strtotime(Dispatcher::Instance()->Decrypt($mObj->_GET['start_date'])));
         $requestData['end_date']   = date('Y-m-d', strtotime(Dispatcher::Instance()->Decrypt($mObj->_GET['end_date'])));
         $requestData['kode']       = Dispatcher::Instance()->Decrypt($mObj->_GET['kode']);
      }

      if(method_exists(Dispatcher::Instance(), 'getQueryString')){
         # @param array
         $queryString      = Dispatcher::instance()->getQueryString($requestData);
      }else{
         $query            = array();
         foreach ($requestData as $key => $value) {
            $query[$key]   = Dispatcher::Instance()->Encrypt($value);
         }
         $queryString      = urldecode(http_build_query($query));
      }

      $offset        = 0;
      $limit         = 20;
      $page          = 0;
      if(isset($_GET['page'])){
         $page       = (string) $_GET['page']->StripHtmlTags()->SqlString()->Raw();
         $offset     = ($page - 1) * $limit;
      }
      #paging url
      $url           = Dispatcher::Instance()->GetUrl(
        Dispatcher::Instance()->mModule,
        Dispatcher::Instance()->mSubModule,
        Dispatcher::Instance()->mAction,
        Dispatcher::Instance()->mType
      ).'&search='.Dispatcher::Instance()->Encrypt(1).'&'.$queryString;

      $destination_id = "subcontent-element";
      
      $dataList         = $mObj->getDataJurnal($offset, $limit, (array)$requestData);
      $total_data       = $mObj->getCountDataJurnal((array)$requestData);

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

      if($messenger){
         $messengerData    = $messenger[0][0];
         $pembukuanId      = $messengerData['id'];
         $tmpJurnal        = $messengerData['jurnal'];
         $message          = $messenger[0][1];
         $style            = $messenger[0][2];

         if(!empty($pembukuanId)){
            foreach ($pembukuanId as $id) {
               $dataPembukuan[$id]['id']                 = $id;
               $dataPembukuan[$id]['status_kas']         = $tmpJurnal[$id]['status_kas'];
               $dataPembukuan[$id]['bentuk_transaksi']   = $tmpJurnal[$id]['bentuk_transaksi'];
            }
         }
      }

      # GTFW Tanggal
      Messenger::Instance()->SendToComponent(
         'tanggal',
         'Tanggal',
         'view',
         'html',
         'start_date',
         array(
            $requestData['start_date'],
            $minYear,
            $maxYear,
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
            $requestData['end_date'],
            $minYear,
            $maxYear,
            false,
            false,
            false
         ),
         Messenger::CurrentRequest
      );

      # Combobox
      Messenger::Instance()->SendToComponent(
         'combobox',
         'Combobox',
         'view',
         'html',
         'tipe_transaksi',
         array(
            'tipe_transaksi',
            $arrTipeTransaksi,
            $requestData['tipe'],
            true,
            'id="cmb_tipe_transaksi"'
         ),
         Messenger::CurrentRequest
      );

      $return['data_list']          = $mObj->ChangeKeyName($dataList);
      $return['request_data']       = $requestData;
      $return['query_string']       = $queryString;
      $return['tahun_pembukuan']    = $tahunPembukuan;
      $return['status_approval']    = $arrStatus;
      $return['start']              = $offset+1;
      $return['status_kas']         = $arrStatusKas;
      $return['bentuk_transaksi']   = $arrBentukTransaksi;
      $return['message']            = $message;
      $return['style']              = $style;
      $return['data_pembukuan']     = $dataPembukuan;
      /*//proses approval mulai
      if(isset($_POST['btnApprove'])) {
         //approval
         $approve = $_POST->AsArray();
          if(empty($approve['id'])) {
               $status = "err";
               $return['msg']['message'] = 'Silakan Pilih Salah Satu Data';
               $return['msg']['status'] = 'err';
          } else {
               if($approve['approve_act'] == 'app'){
                   $add = $this->proc->db->DoAdd($approve['id']);
                     $label = 'Approval';
               }
               elseif($approve['approve_act'] == 'unapp'){
                     $add = $this->proc->db->DoUnapprove($approve['id']);
                     $label = 'Unapproval';
               }

               if($add) {
                  //berhasil
                  $return['msg']['message'] = $label.' berhasil dilakukan';
                  $return['msg']['status'] = 'ok';
               } else {
                  //gagal
                  $return['msg']['message'] = $label.' gagal dilakukan';
                  $return['msg']['status'] = 'err';
               }
          }
      }
      //proses approval selesai

      if(isset($_POST['btnFilter'])) {
         $tipe_transaksi = $_POST['tipe_transaksi']->mrVariable;
         $is_approve = $_POST['is_approve'];
         $tgl_awal  = $_POST['tanggal_awal_year']->mrVariable;
         $tgl_awal .= '-'.$_POST['tanggal_awal_mon']->mrVariable;
         $tgl_awal .= '-'.$_POST['tanggal_awal_day']->mrVariable;

         $tgl_akhir  = $_POST['tanggal_akhir_year']->mrVariable;
         $tgl_akhir .= '-'.$_POST['tanggal_akhir_mon']->mrVariable;
         $tgl_akhir .= '-'.$_POST['tanggal_akhir_day']->mrVariable;
      } elseif(isset($_GET['cari'])) {
         $tipe_transaksi = Dispatcher::Instance()->Decrypt($_GET['tipe_transaksi']);
         $is_approve = Dispatcher::Instance()->Decrypt($_GET['is_approve']);
         $tgl_awal = str_replace("|", "-", Dispatcher::Instance()->Decrypt($_GET['tgl_awal']));
         $tgl_akhir = str_replace("|", "-", Dispatcher::Instance()->Decrypt($_GET['tgl_akhir']));
      } else {
         $tipe_transaksi = 'all';
         $is_approve = 'T';
         $tgl_awal = date("Y-01-01");
         $tgl_akhir = date("Y-m-d");
      }

      $itemViewed = 20;
      $currPage = 1;
      $startRec = 0 ;
      if(isset($_GET['page'])) {
         $currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
         $startRec =($currPage-1) * $itemViewed;
      }
      $this->data = $this->proc->db->GetData($startRec,$itemViewed, $tipe_transaksi, $is_approve, $tgl_awal, $tgl_akhir);
      $totalData = $this->proc->db->GetCount($tipe_transaksi, $is_approve, $tgl_awal, $tgl_akhir);

      /*
      $akun = $this->proc->db->GetDataKodeAkun();
      foreach($akun as $value){
         print_r($value);
      }
      */

      //print_r($this->data);
      /*$tahunpencatatan = $this->proc->db->GetMinMaxTahunPencatatan();

      //echo $this->data['tanggal_awal'];
       //combo tipe transaksi
         $arr_tipe_transaksi = $this->proc->db->GetComboTipeTransaksi();
         Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 'tipe_transaksi', array('tipe_transaksi', $arr_tipe_transaksi, $tipe_transaksi, true, ' style="width:200px;" id="tahun_anggaran"'), Messenger::CurrentRequest);

       Messenger::Instance()->SendToComponent('tanggal', 'Tanggal', 'view', 'html', 'tanggal_awal',
         array($tgl_awal, $tahunpencatatan['minTahun'], $tahunpencatatan['maxTahun']), Messenger::CurrentRequest);

       Messenger::Instance()->SendToComponent('tanggal', 'Tanggal', 'view', 'html', 'tanggal_akhir',
         array($tgl_akhir, $tahunpencatatan['minTahun'], $tahunpencatatan['maxTahun']), Messenger::CurrentRequest);

         $url = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule,
               Dispatcher::Instance()->mSubModule,
               Dispatcher::Instance()->mAction,
               Dispatcher::Instance()->mType .
               '&tipe_transaksi=' . Dispatcher::Instance()->Encrypt($tipe_transaksi) .
               '&is_approve=' . Dispatcher::Instance()->Encrypt($is_approve) .
               '&tgl_awal=' . Dispatcher::Instance()->Encrypt(str_replace("-", "|", $tgl_awal)) .
               '&tgl_akhir=' . Dispatcher::Instance()->Encrypt(str_replace("-", "|", $tgl_akhir)) .
               '&cari=' . Dispatcher::Instance()->Encrypt(1)
               );

         Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top',
         array($itemViewed,$totalData, $url, $currPage), Messenger::CurrentRequest);
      //echo $startRec;
      $return['start'] = $startRec+1;
      $return['search']['is_approve'] = $is_approve;*/

      return $return;
   }

   function ParseTemplate($data = NULL) {
      $dataList         = $data['data_list'];
      $requestData      = $data['request_data'];
      $queryString      = $data['query_string'];
      $tahunPembukuan   = $data['tahun_pembukuan'];  
      $statusApproval   = $data['status_approval'];
      $start            = $data['start'];
      $arrStatusKas     = $data['status_kas'];
      $bentukTransaksi  = $data['bentuk_transaksi'];
      $dataPembukuan    = $data['data_pembukuan'];
      $message          = $data['message'];
      $style            = $data['style'];
      $urlSearch     = Dispatcher::Instance()->GetUrl(
         'approval_jurnal',
         'ApprovalJurnal',
         'view',
         'html'
      );


      foreach ($statusApproval as $status) {
         if($status['id'] == $requestData['status']){
            $status['checked']      = 'checked';
         }else{
            $status['checked']      = '';
         }
         $this->mrTemplate->AddVars('status_radio', $status);
         $this->mrTemplate->parseTemplate('status_radio', 'a');
      }

      if($requestData['status'] == 'Y'){
         $urlApprove    = Dispatcher::Instance()->GetUrl(
            'approval_jurnal',
            'deleteApprovalJurnal',
            'do',
            'json'
         ).'&'.$queryString;
         $this->mrTemplate->AddVar('content_button', 'STATUS', 'UNAPPROVAL');
      }else{
         $urlApprove    = Dispatcher::Instance()->GetUrl(
            'approval_jurnal',
            'AddApprovalJurnal',
            'do',
            'json'
         ).'&'.$queryString;
         $this->mrTemplate->AddVar('content_button', 'STATUS', 'APPROVAL');
      }

      $this->mrTemplate->AddVar('content', 'URL_SEARCH',  $urlSearch);
      $this->mrTemplate->AddVar('content', 'URL_APPROVE', $urlApprove);

      if($message){
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $message);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $style);
      }

      if(empty($dataList)){
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         $dataGrid      = array();
         $pembukuanId   = '';
         $transaksiId   = '';
         $index         = 0;
         $idx           = 0;
         $dataJurnal    = array();
         $rows          = array();

         for ($i=0; $i < count($dataList);) {
          
            if((int)$transaksiId === (int)$dataList[$i]['id']
               && (int)$pembukuanId === (int)$dataList[$i]['pembukuan_id']){
               $ks      = $pembukuanId.'.'.$transaksiId;
               $dataJurnal[$ks][$idx]['akun_id']         = $dataList[$i]['coa_id'];
               $dataJurnal[$ks][$idx]['kode']            = $dataList[$i]['coa_kode_akun'];
               $dataJurnal[$ks][$idx]['nama']            = $dataList[$i]['coa_nama_akun'];
               $dataJurnal[$ks][$idx]['sub_account']     = $dataList[$i]['sub_account'];
               $dataJurnal[$ks][$idx]['nominal_debet']   = number_format($dataList[$i]['nominal_debet'], 2, ',','.');
               $dataJurnal[$ks][$idx]['nominal_kredit']  = number_format($dataList[$i]['nominal_kredit'], 2, ',','.');
               $dataJurnal[$ks][$idx]['class_name']      = $className;
               $rows[$ks]['row_span']        += 1;
               $i++;
               $idx++;
            }else{
               unset($idx);
               $idx              = 0;
               $pembukuanId      = (int)$dataList[$i]['pembukuan_id'];
               $transaksiId      = (int)$dataList[$i]['id'];
               $kodeSistem       = $pembukuanId.'.'.$transaksiId;
               if($start % 2 <> 0){
                  $className     = 'table-common-even';
               }else{
                  $className     = '';
               }

               $tanggalPembukuanAwal   = date('Y-m-d',strtotime($tahunPembukuan[0]['awal']));
               $tanggalPembukuanAkhir  = date('Y-m-d',strtotime($tahunPembukuan[0]['akhir']));
               $tanggalTransaksi       = date('Y-m-d',strtotime($dataList[$i]['tanggal']));
               
               if($tanggalTransaksi >= $tanggalPembukuanAwal && $tanggalTransaksi <= $tanggalPembukuanAkhir) {
                  $dataJurnal[$kodeSistem][$idx]['in_periode'] = 'Y';
               } else {
                  $dataJurnal[$kodeSistem][$idx]['in_periode'] = 'T';
               }

               $dataJurnal[$kodeSistem][$idx]['id']             = $dataList[$i]['id'];
               $dataJurnal[$kodeSistem][$idx]['nomor']          = $start;
               $dataJurnal[$kodeSistem][$idx]['pembukuan_id']   = $dataList[$i]['pembukuan_id'];
               $dataJurnal[$kodeSistem][$idx]['tp_id']          = $dataList[$i]['tpp_id'];
               $dataJurnal[$kodeSistem][$idx]['kode_sistem']    = $kodeSistem;
               $dataJurnal[$kodeSistem][$idx]['referensi']      = $dataList[$i]['referensi'];
               $dataJurnal[$kodeSistem][$idx]['deskripsi']      = $dataList[$i]['catatan'];
               $dataJurnal[$kodeSistem][$idx]['tanggal']        = $dataList[$i]['tanggal'];
               $dataJurnal[$kodeSistem][$idx]['penanggung_jawab'] = $dataList[$i]['penanggung_jawab'];
               $dataJurnal[$kodeSistem][$idx]['type']          = 'parent';
               $dataJurnal[$kodeSistem][$idx]['status_approval']  = $dataList[$i]['status_approve'];
               $dataJurnal[$kodeSistem][$idx]['status_posting']   = $dataList[$i]['status_posting'];
               $dataJurnal[$kodeSistem][$idx]['jurnal_balik']     = $dataList[$i]['jurnal_balik'];
               $dataJurnal[$kodeSistem][$idx]['has_jurnal']       = strtoupper($dataList[$i]['has_jurnal']);
               $dataJurnal[$kodeSistem][$idx]['jurnal']           = $dataList[$i]['jurnal'];
               $dataJurnal[$kodeSistem][$idx]['class_name']       = $className;
               $dataJurnal[$kodeSistem][$idx]['kelompok_laporan'] = $dataList[$i]['kel_jns_nama'];
               $dataJurnal[$kodeSistem][$idx]['status_kas']       = $dataList[$i]['status_kas'];
               $rows[$kodeSistem]['row_span']      = 0;
               $index++;
               $start++;
            }
         }

         foreach ($dataJurnal as $grid) {
            foreach ($grid as $jurnal) {
               if($jurnal['type'] AND strtoupper($jurnal['type']) == 'PARENT'){
                  $jurnal['row_span']     = $rows[$jurnal['kode_sistem']]['row_span'];
                  $pembukuanId            = $jurnal['pembukuan_id'];
                  $pembukuan              = $dataPembukuan[$pembukuanId];
                  $this->mrTemplate->AddVar('data_jurnal', 'LEVEL', 'PARENT');

                  // condition status approve
                  if($jurnal['status_approval'] == 'Y'){
                     $this->mrTemplate->AddVar('status_kas', 'STATUS', 'APPROVE');
                     $this->mrTemplate->AddVar('bentuk_transaksi', 'STATUS', 'APPROVE');
                     $this->mrTemplate->AddVar('content_checkbox', 'ATTR', 'DISABLED');
                     $this->mrTemplate->AddVar('content_checkbox', 'ID', $jurnal['pembukuan_id']);

                     // Cek Tahun Pembukuan Aktif
                     // if($tahunPembukuan[0]['id'] != $jurnal['tp_id']){
                     //    $this->mrTemplate->AddVar('content_checkbox', 'DISABLED_CHECKBOX', 'disabled');
                     // }

                     // Cek Status Posting
                     if($jurnal['status_posting'] == 'Y'){
                        $this->mrTemplate->AddVar('content_checkbox', 'DISABLED_CHECKBOX', 'disabled');
                     }
                     
                     // cek tanggal transaksi berada pada tahun anggaran aktif
                     if($jurnal['in_periode'] == 'T'){
                        $this->mrTemplate->AddVar('content_checkbox', 'DISABLED_CHECKBOX', 'disabled');
                     }

                     foreach ($arrStatusKas as $kas) {
                        if($kas['id'] == $jurnal['status_kas']){
                           $this->mrTemplate->AddVar('status_kas', 'STATUS_KAS', $kas['name']);
                        }
                     }
                     $this->mrTemplate->AddVar('bentuk_transaksi', 'KELOMPOK_LAPORAN', $jurnal['kelompok_laporan']);
                  }else{
                     $this->mrTemplate->AddVar('status_kas', 'STATUS', 'UNAPPROVE');
                     $this->mrTemplate->AddVar('bentuk_transaksi', 'STATUS', 'UNAPPROVE');
                     $this->mrTemplate->AddVar('content_checkbox', 'ATTR', 'ENABLE');

                     // Cek Tahun Pembukuan Aktif
                     // if($tahunPembukuan[0]['id'] != $jurnal['tp_id']){
                     //    $this->mrTemplate->AddVar('content_checkbox', 'DISABLED_CHECKBOX', 'disabled');
                     // }
                     // cek tanggal transaksi berada pada tahun anggaran aktif
                     if($jurnal['in_periode'] == 'T'){
                        $this->mrTemplate->AddVar('content_checkbox', 'DISABLED_CHECKBOX', 'disabled');
                     }


                     foreach ($arrStatusKas as $statusKas) {
                        $statusKas['selected']  = '';
                        if($pembukuan && !empty($pembukuan)){
                           if($statusKas['id'] == $pembukuan['status_kas']){
                              $statusKas['selected']  = 'selected';
                           }
                        }
                        $this->mrTemplate->AddVars('cmb_status_kas', $statusKas);
                        $this->mrTemplate->parseTemplate('cmb_status_kas', 'a');
                     }

                     foreach ($bentukTransaksi as $bt) {
                        $bt['selected']         = '';
                        if($pembukuan && !empty($pembukuan)){
                           if($bt['id'] == $pembukuan['bentuk_transaksi']){
                              $bt['selected']   = 'selected';
                           }
                        }
                        $this->mrTemplate->AddVars('cmb_bentuk_transaksi', $bt);
                        $this->mrTemplate->parseTemplate('cmb_bentuk_transaksi', 'a');
                     }

                     $this->mrTemplate->AddVar('status_kas', 'ID', $jurnal['pembukuan_id']);
                     $this->mrTemplate->AddVar('bentuk_transaksi', 'ID', $jurnal['pembukuan_id']);
                     $this->mrTemplate->AddVar('content_checkbox', 'ID', $jurnal['pembukuan_id']);

                     if($pembukuan && !empty($pembukuan)){
                        $this->mrTemplate->AddVar('content_checkbox', 'CHECKED', 'checked');
                     }
                  }
                  $this->mrTemplate->AddVars('data_jurnal', $jurnal);
               }else{
                  $this->mrTemplate->AddVar('data_jurnal', 'LEVEL', 'DATA');
                  $this->mrTemplate->AddVars('data_jurnal', $jurnal);
               }
               $this->mrTemplate->parseTemplate('data_list', 'a');
            }
         }
      }
   }
}
?>