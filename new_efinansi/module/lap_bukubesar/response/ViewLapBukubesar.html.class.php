<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/lap_bukubesar/business/AppLapBukubesar.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'main/function/date.php';

class ViewLapBukubesar extends HtmlResponse {

    function TemplateModule() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'module/lap_bukubesar/template');
        $this->SetTemplateFile('view_lap_bukubesar.html');
    }

    function ProcessRequest() {
        $mObj = new AppLapBukubesar();
        $requestData = array();
        $queryString = '';
        $getdate = getdate();
        $currmon = $getdate['mon'];
        $currday = $getdate['mday'];
        $curryear = $getdate['year'];
        $tahunTrans = $mObj->GetMinMaxThnTrans();
        $minTahun = $tahunTrans['minTahun'];
        $maxTahun = $tahunTrans['maxTahun'];

        if (isset($mObj->_POST['btncari'])) {
            $tanggalAwalDay = (int) $mObj->_POST['tanggal_awal_day'];
            $tanggalAwalMon = (int) $mObj->_POST['tanggal_awal_mon'];
            $tanggalAwalYear = (int) $mObj->_POST['tanggal_awal_year'];
            $tanggalAkhirDay = (int) $mObj->_POST['tanggal_akhir_day'];
            $tanggalAkhirMon = (int) $mObj->_POST['tanggal_akhir_mon'];
            $tanggalAkhirYear = (int) $mObj->_POST['tanggal_akhir_year'];
            $requestData['coa_id'] = $mObj->_POST['coa_id'];
            $requestData['coa_nama'] = $mObj->_POST['coa_nama'];
            $requestData['start_date'] = date('Y-m-d', mktime(0, 0, 0, $tanggalAwalMon, $tanggalAwalDay, $tanggalAwalYear));
            $requestData['end_date'] = date('Y-m-d', mktime(0, 0, 0, $tanggalAkhirMon, $tanggalAkhirDay, $tanggalAkhirYear));
        } elseif (isset($mObj->_GET['search'])) {
            $requestData['coa_id'] = Dispatcher::Instance()->Decrypt($mObj->_GET['coa_id']);
            $requestData['coa_nama'] = Dispatcher::Instance()->Decrypt($mObj->_GET['coa_nama']);
            $requestData['start_date'] = date('Y-m-d', strtotime(Dispatcher::Instance()->Decrypt($mObj->_GET['start_date'])));
            $requestData['end_date'] = date('Y-m-d', strtotime(Dispatcher::Instance()->Decrypt($mObj->_GET['end_date'])));
        } else {
            $requestData['coa_id'] = '';
            $requestData['coa_nama'] = '';
            $requestData['start_date'] = date('Y-m-d', mktime(0, 0, 0, 1, 1, $curryear));
            $requestData['end_date'] = date('Y-m-t', mktime(0, 0, 0, 12, 1, $curryear));
        }


        $offset = 0;
        $limit = 20;
        $page = 0;
        if (isset($_GET['page'])) {
            $page = (string) $_GET['page']->StripHtmlTags()->SqlString()->Raw();
            $offset = ($page - 1) * $limit;
        }

        $requestData['limit'] = $limit;
        $requestData['page'] = $offset;

        if (method_exists(Dispatcher::Instance(), 'getQueryString')) {
            # @param array
            $queryString = Dispatcher::instance()->getQueryString($requestData);
        } else {
            $query = array();
            foreach ($requestData as $key => $value) {
                $query[$key] = Dispatcher::Instance()->Encrypt($value);
            }
            $queryString = urldecode(http_build_query($query));
        }

        
        $dataLaporan = $mObj->getData((array) $requestData);
        $total_data = $mObj->Count();
        $dataSaldo = $mObj->getTotalSaldo((array) $requestData);
        $resumeSaldoBukuBesar = $mObj->getResumeTotalJumlahSaldo();
        
        #paging url
        $url = Dispatcher::Instance()->GetUrl(
                        Dispatcher::Instance()->mModule, Dispatcher::Instance()->mSubModule, Dispatcher::Instance()->mAction, Dispatcher::Instance()->mType
                ) . '&search=' . Dispatcher::Instance()->Encrypt(1) . '&' . $queryString;

        $destination_id = "subcontent-element";
        #send data to pagging component
        Messenger::Instance()->SendToComponent(
                'paging', 'Paging', 'view', 'html', 'paging_top', array(
            $limit,
            $total_data,
            $url,
            $page,
            $destination_id
                ), Messenger::CurrentRequest
        );

        Messenger::Instance()->SendToComponent(
                'tanggal', 'Tanggal', 'view', 'html', 'tanggal_awal', array(
            $requestData['start_date'],
            $minTahun,
            $maxTahun,
            false,
            false,
            false
                ), Messenger::CurrentRequest);

        Messenger::Instance()->SendToComponent(
                'tanggal', 'Tanggal', 'view', 'html', 'tanggal_akhir', array(
            $requestData['end_date'],
            $minTahun,
            $maxTahun,
            false,
            false,
            false
                ), Messenger::CurrentRequest);

        $return['data_saldo'] = $dataSaldo;
        $return['resume_saldo'] = $resumeSaldoBukuBesar;
        $return['request_data'] = $requestData;
        $return['query_string'] = $queryString;
        $return['data_list'] = $dataLaporan;
        $return['start'] = $offset + 1;

        /* $post       = $_POST->AsArray();
          if(!empty($post['combo_rekening'])){
          $rekening = $post['combo_rekening'];
          $rekening_label = $post['coa_label'];
          }else{
          $rekening = '';
          $rekening_label = '';
          }
          if(!empty($post['tanggal_awal_day']))
          $tanggal_awal = $post['tanggal_awal_year'] ."-". $post['tanggal_awal_mon'] ."-". $post['tanggal_awal_day'];
          else
          $tanggal_awal = date("Y-01-01");
          if(!empty($post['tanggal_akhir_day']))
          $tanggal_akhir = $post['tanggal_akhir_year'] ."-". $post['tanggal_akhir_mon'] ."-". $post['tanggal_akhir_day'];
          else
          $tanggal_akhir = date("Y-m-d");

          if(isset($_GET['cari'])) {
          $get_data = $_GET->AsArray();
          $tanggal_awal = $get_data['tgl_awal'];
          $tanggal_akhir = $get_data['tgl_akhir'];
          $rekening = $get_data['combo_rekening'];
          $rekening_label = $get_data['coa_label'];
          }
          //tahun untuk combo



          $bbhis = $mOb        j->GetBukuBesarHis($rekening, $tanggal_awal, $tanggal_akhir);
          #print_r($bbhis);
          $return['bbhis'] = $bbhis;
          $return['rekening'] = $rekening;
          $return['rekening_label'] = $rekening_label;
          $return['tgl_awal'] = $tanggal_awal;
          $return['tgl_akhir'] = $tanggal_akhir;
          #print_r($rekening); exit; */
        return $return;
    }

    function ParseTemplate($data = NULL) {
        $dataSaldo = $data['data_saldo'];
        $resumeSaldo = $data['resume_saldo'];
        $requestData = $data['request_data'];
        $queryString = $data['query_string'];
        $dataList = $data['data_list'];
        $start = $data['start'];
        $urlSearch = Dispatcher::Instance()->GetUrl(
                'lap_bukubesar', 'LapBukubesar', 'view', 'html'
        );
        $this->mrTemplate->AddVar('content', 'URL_SEARCH', $urlSearch);
        $this->mrTemplate->AddVars('content', $requestData);

        $this->mrTemplate->AddVar('content', 'URL_CETAK', Dispatcher::Instance()->GetUrl(
                        'lap_bukubesar', 'CetakLapBukubesar', 'view', 'html'
                ) .
                '&rekening=' . Dispatcher::Instance()->Encrypt($requestData['coa_id']) .
                '&tgl_awal=' . Dispatcher::Instance()->Encrypt($requestData['start_date']) .
                '&tgl_akhir=' . Dispatcher::Instance()->Encrypt($requestData['end_date']) .
                '&cetak=' . Dispatcher::Instance()->Encrypt('yes'));

        $this->mrTemplate->AddVar('content', 'URL_EXCEL', Dispatcher::Instance()->GetUrl(
                        'lap_bukubesar', 'ExcelLapBukubesar', 'view', 'xlsx'
                ) .
                '&rekening=' . Dispatcher::Instance()->Encrypt($requestData['coa_id']) .
                '&tgl_awal=' . Dispatcher::Instance()->Encrypt($requestData['start_date']) .
                '&tgl_akhir=' . Dispatcher::Instance()->Encrypt($requestData['end_date']) .
                '&cetak=' . Dispatcher::Instance()->Encrypt('yes'));

        $this->mrTemplate->AddVar('content', 'URL_POPUP_COA', Dispatcher::Instance()->GetUrl('lap_bukubesar', 'popupCoa', 'view', 'html'));

        if (empty($dataList)) {
            $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
        } else {
            $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');

            $kodeAkun = '';
            $items = array();
            $max = sizeof($dataList);
            $nk = 0;
            $saldo = 0;
            $saldoAkhir = 0;
            
            for ($k = 0; $k < $max;) {
                    
                if ($kodeAkun == $dataList[$k]['akun_kode']){                  
                    
                    $items[$nk]['akun_kode'] = '';
                    $items[$nk]['akun_nama'] = '';
                    $items[$nk]['tanggal_jurnal_entri'] = $dataList[$k]['tanggal_jurnal_entri'];
                    $items[$nk]['sub_account'] = $dataList[$k]['sub_account'];
                    $items[$nk]['keterangan'] = $dataList[$k]['keterangan'];
                    $items[$nk]['nomor_referensi'] = $dataList[$k]['nomor_referensi'];

                    if ($dataList[$k]['debet'] >= 0) {
                        $items[$nk]['debet'] = number_format($dataList[$k]['debet'], 2, ',', '.');
                    } else {
                        $items[$nk]['debet'] = '(' . number_format(($dataList[$k]['debet'] * (-1)), 2, ',', '.') . ')';
                    }

                    if ($dataList[$k]['kredit'] >= 0) {
                        $items[$nk]['kredit'] = number_format($dataList[$k]['kredit'], 2, ',', '.');
                    } else {
                        $items[$nk]['kredit'] = '(' . number_format(($dataList[$k]['kredit'] * (-1)), 2, ',', '.') . ')';
                    }
                    //$saldo +=  $dataList[$k]['debet'];
                    //$saldo -= $dataList[$k]['kredit'];
                    //$saldoAkhir = ($dataList[$k]['saldo_awal'] + $saldo);
                    
                    if((int) $dataList[$k]['id'] == 0) {
                        $items[$nk]['is_show'] ='display:none';
                        $saldoAkhir = $dataList[$k]['saldo_awal'];
                    } else {
                        $items[$nk]['is_show'] ='';
                        //$saldoAkhir = $dataList[$k]['saldo_akhir'];
                        $saldoAkhir = $dataSaldo[$dataList[$k]['akun_kode']][$dataList[$k]['tanggal_jurnal_entri']][$dataList[$k]['nomor_referensi']][$dataList[$k]['id']];
                    }
                    
                    
                    if ($saldoAkhir >= 0) {
                        $items[$nk]['saldo_akhir'] = number_format($saldoAkhir, 2, ',', '.');
                    } else {
                        $items[$nk]['saldo_akhir'] = '(' . number_format(($saldoAkhir * (-1)), 2, ',', '.') . ')';
                    }

                    //$this->mrTemplate->AddVar('data_item_grid', 'HEADER','NO');                
                    //$this->mrTemplate->AddVars('data_item_grid', $items[$nk]);

                    if (isset($dataList[$k + 1]['akun_kode'])) {
                        $cek = $dataList[$k + 1]['akun_kode'];
                    } else {
                        $cek = null;
                    }

                    if ($kodeAkun != $cek) {

                        $this->mrTemplate->SetAttribute('jumlah', 'visibility', 'visible');
                        $this->mrTemplate->AddVar('jumlah', 'SALDO_AKHIR', $items[$nk]['saldo_akhir']);
                    } else {
                        $this->mrTemplate->SetAttribute('jumlah', 'visibility', 'hidden');
                    }
                    $this->mrTemplate->AddVar('data_item_grid', 'HEADER', 'NO');
                    $this->mrTemplate->AddVars('data_item_grid', $items[$nk]);

                    $k++;
                } elseif ($kodeAkun != $dataList[$k]['akun_kode']) {
                    $kodeAkun = $dataList[$k]['akun_kode'];
                    $saldo = 0;
                    $saldoAkhir = 0;
                    $items[$nk]['akun_kode'] = '';
                    $items[$nk]['akun_nama'] = '';
                    $items[$nk]['tanggal_jurnal_entri'] = '';
                    $items[$nk]['sub_account'] = '';
                    $items[$nk]['keterangan'] = $kodeAkun . ' - ' . $dataList[$k]['akun_nama'];
                    $items[$nk]['nomor_referensi'] = '';
                    if ($dataList[$k]['saldo_awal'] >= 0) {
                        $items[$nk]['saldo_awal'] = number_format($dataList[$k]['saldo_awal'], 2, ',', '.');
                    } else {
                        $items[$nk]['saldo_awal'] = '(' . number_format(($dataList[$k]['saldo_awal'] * (-1)), 2, ',', '.') . ')';
                    }

                    $items[$nk]['debet'] = '';
                    $items[$nk]['kredit'] = '';
                    $items[$nk]['saldo_akhir'] = '';
                    $this->mrTemplate->AddVar('data_item_grid', 'HEADER', 'YES');
                    $this->mrTemplate->AddVars('data_item_grid', $items[$nk]);
                }

                $this->mrTemplate->AddVars('data_item', $items[$nk]);
                $this->mrTemplate->parseTemplate('data_item', 'a');
                $nk++;
            }
            
            if(!empty($resumeSaldo)){
                $this->mrTemplate->AddVar('resume_saldo', 'DATA_EMPTY', 'NO');
                $nomor = 1;
                foreach($resumeSaldo as $key => $itemResumeSaldo){ 
                    $itemResumeSaldo['no'] = $nomor;
                    if($itemResumeSaldo['nominal'] >= 0){
                        $itemResumeSaldo['nominal_saldo'] = number_format($itemResumeSaldo['nominal'], 2, ',', '.');
                    } else {
                        $itemResumeSaldo['nominal_saldo'] = '(' . number_format(($itemResumeSaldo['nominal'] * (-1)), 2, ',', '.') . ')';
                    }
                    
                    $this->mrTemplate->AddVars('resume_saldo_item', $itemResumeSaldo);
                    $this->mrTemplate->parseTemplate('resume_saldo_item', 'a');
                    $nomor++;
                }
            } else {
                $this->mrTemplate->AddVar('resume_saldo', 'DATA_EMPTY', 'YES');
            }
        }
        /**
          if (empty($data['bbhis'])) {
          $this->mrTemplate->AddVar('data_lap', 'DATA_EMPTY', 'YES');
          } else {
          $this->mrTemplate->AddVar('data_lap', 'DATA_EMPTY', 'NO');
          for ($i=0; $i<count($data['bbhis']); $i++) {
          $data['bbhis'][$i]['saldo_awal'] =number_format($data['bbhis'][$i]['saldo_awal'], 2, ',', '.');
          $data['bbhis'][$i]['debet'] =number_format($data['bbhis'][$i]['debet'], 2, ',', '.');
          $data['bbhis'][$i]['kredit'] =number_format($data['bbhis'][$i]['kredit'], 2, ',', '.');
          $data['bbhis'][$i]['saldo'] =number_format($data['bbhis'][$i]['saldo'], 2, ',', '.');
          $data['bbhis'][$i]['saldo_akhir'] =number_format($data['bbhis'][$i]['saldo_akhir'], 2, ',', '.');
          if ($i % 2 != 0)
          $data['bbhis'][$i]['class_name'] = 'table-common-even';
          else
          $data['bbhis'][$i]['class_name'] = '';
          $data['bbhis'][$i]['indodate'] = IndonesianDate($data['bbhis'][$i]['bb_tanggal'], 'yyyy-mm-dd');
          $this->mrTemplate->AddVars('data_item', $data['bbhis'][$i], 'LAP_');
          $this->mrTemplate->parseTemplate('data_item', 'a');
          }
          }
         * 
         */
    }

}

?>