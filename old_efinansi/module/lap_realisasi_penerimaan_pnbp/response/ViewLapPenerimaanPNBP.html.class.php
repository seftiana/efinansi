<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/lap_realisasi_penerimaan_pnbp/business/AppLapPenerimaanPNBP.class.php';

require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/user_unit_kerja/business/UserUnitKerja.class.php';

require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'main/function/number_format.class.php';

class ViewLapPenerimaanPNBP extends HtmlResponse {

    var $Pesan;
    
    protected $mObj;
    
    function TemplateModule() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') .
                'module/lap_realisasi_penerimaan_pnbp/template');
        $this->SetTemplateFile('view_lap_penerimaan_pnbp.html');
    }

    function ProcessRequest() {
        $_POST = $_POST->AsArray();
        $this->mObj = new AppLapPenerimaanPNBP();
        $userUnitKerjaObj = new UserUnitKerja();

        $userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
        $role = $userUnitKerjaObj->GetRoleUser($userId);
        $unit = $userUnitKerjaObj->GetUnitKerjaUser($userId);

        $dates = $this->mObj->getRangeYear();
        $minYear = date('Y', strtotime($dates['start_date']));
        $maxYear = date('Y', strtotime($dates['end_date']));

        $datesAktif = $this->mObj->getRangeYearAktif();
        //print_r($role);
        /** if($role['role_name'] == "Administrator") { */
        if ($_POST['btncari']) {
            $this->Data['unitkerja'] = $_POST['unitkerja'];
            $this->Data['unitkerja_label'] = $_POST['unitkerja_label'];
            $unitkerja = $userUnitKerjaObj->GetSatkerUnitKerja($this->Data['unitkerja']);
            $this->Data['is_satker'] = $unitkerja['is_unit_kerja'];
            $this->Data['tanggal_awal'] = $_POST['start_date_year'] . '-' . $_POST['start_date_mon'] . '-1';
            $this->Data['tanggal_akhir'] = $_POST['end_date_year'] . '-' . $_POST['end_date_mon'] . '-1';
        } elseif ($_GET['cari'] != "") {
            $get = $_GET->AsArray();
            $this->Data['unitkerja'] = Dispatcher::Instance()->Decrypt($_GET['unitkerja']);
            $this->Data['unitkerja_label'] = Dispatcher::Instance()->Decrypt($_GET['unitkerja_label']);
            $unitkerja = $userUnitKerjaObj->GetSatkerUnitKerja($this->Data['unitkerja']);
            $this->Data['is_satker'] = $unitkerja['is_unit_kerja'];
            $this->Data['tanggal_awal'] = Dispatcher::Instance()->Decrypt($_GET['tanggal_awal']);
            $this->Data['tanggal_akhir'] = Dispatcher::Instance()->Decrypt($_GET['tanggal_akhir']);
        } else {
            $this->Data = $_POST;
            $this->Data['unitkerja'] = $unit['unit_kerja_id']; // $unit['satker_id'];
            $this->Data['unitkerja_label'] = $unit['unit_kerja_nama']; //$unit['satker_nama'];
            $this->Data['tanggal_awal'] = $datesAktif['start_date'];
            $this->Data['tanggal_akhir'] = $datesAktif['end_date'];
        }

        $this->Data['total_sub_unit_kerja'] = $userUnitKerjaObj->GetTotalSubUnitKerja($unit['unit_kerja_id']);

        # GTFW Tanggal
        Messenger::Instance()->SendToComponent(
                'tanggal', 'Tanggal', 'view', 'html', 'start_date', array(
            $this->Data['tanggal_awal'],
            $minYear,
            $maxYear,
            false,
            false,
            true
                ), Messenger::CurrentRequest
        );

        Messenger::Instance()->SendToComponent(
                'tanggal', 'Tanggal', 'view', 'html', 'end_date', array(
            $this->Data['tanggal_akhir'],
            $minYear,
            $maxYear,
            false,
            false,
            true
                ), Messenger::CurrentRequest
        );

        $itemViewed = 20;
        $currPage = 1;
        $startRec = 0;
        if (isset($_GET['page'])) {
            $currPage = (string) $_GET['page']->StripHtmlTags()->SqlString()->Raw();
            $startRec = ($currPage - 1) * $itemViewed;
        }

        $data_pnbp = $this->mObj->GetDataRealisasiPNBP(
                $this->Data['tanggal_awal'], $this->Data['tanggal_akhir'], $this->Data['unitkerja'], $startRec, $itemViewed
        );
        $totalData = $this->mObj->GetCountData();

        $total_data_pnbp_perbulan = $this->mObj->GetTotalDataRealisasiPnbpPerBulan(
                $this->Data['tahun_anggaran'], $this->Data['unitkerja']
        );
        
        //prepare data
        $this->mObj->PrepareDataNominalPerBulan($this->Data['tanggal_awal'], $this->Data['tanggal_akhir'], $this->Data['unitkerja']);
        $totalPenerimaan = $this->mObj->GetTotalRealisasiPNBP($this->Data['tanggal_awal'], $this->Data['tanggal_akhir'], $this->Data['unitkerja']);
        $url = Dispatcher::Instance()->GetUrl(
                Dispatcher::Instance()->mModule, Dispatcher::Instance()->mSubModule, Dispatcher::Instance()->mAction, Dispatcher::Instance()->mType .
                '&tahun_anggaran=' .
                Dispatcher::Instance()->Encrypt($this->Data['tahun_anggaran']) .
                '&unitkerja=' . Dispatcher::Instance()->Encrypt($this->Data['unitkerja']) .
                '&unitkerja_label=' .
                Dispatcher::Instance()->Encrypt($this->Data['unitkerja_label']) .
                '&cari=' . Dispatcher::Instance()->Encrypt(1));

        Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top', array(
            $itemViewed,
            $totalData,
            $url,
            $currPage), Messenger::CurrentRequest);

        $msg = Messenger::Instance()->Receive(__FILE__);
        $this->Pesan = $msg[0][1];
        $this->css = $msg[0][2];
        $return['tgl'] = $this->Data['tahun_anggaran'];
        $return['role_name'] = $role['role_name'];
        $return['data'] = $data_pnbp;
        $return['total_data_pnbp_perbulan'] = $total_data_pnbp_perbulan;
        $return['penerimaan'] = $tot_jumlah;
        $return['jumlah'] = $tot_terima;
        $return['start'] = $startRec + 1;
        $return['total_target_penerimaan'] = $totalPenerimaan['total_target_penerimaan'];
        $return['total_realisasi'] = $totalPenerimaan['total_realisasi'];
        print_r($totalPenerimaan);
        //
        $return['get_header_bulan'] = $this->mObj->getHeaderBulan($this->Data['tanggal_awal'], $this->Data['tanggal_akhir']);
        return $return;
    }

    function tambahNol($str = "0", $jml_char = 2) {
        while (strlen($str) < $jml_char) {
            $str = "0" . $str;
        }
        return $str;
    }

    function ParseTemplate($data = NULL) {

        /**
         * header bulan
         */
        //create header bulan

        $getHeaderBulan = $data['get_header_bulan'];
        $countColsBulan = (sizeof($getHeaderBulan));
        $this->mrTemplate->AddVar('content', 'COLSPAN_BULAN', $countColsBulan);
        foreach ($getHeaderBulan as $bulan) {
            $this->mrTemplate->AddVars('header_bulan', $bulan, '');
            $this->mrTemplate->parseTemplate('header_bulan', 'a');
        }
        /**
         * end
         */
        $userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
        $this->mrTemplate->AddVar(
            'content', 
            'URL_SEARCH', 
                Dispatcher::Instance()->GetUrl('lap_realisasi_penerimaan_pnbp', 'lapPenerimaanPNBP', 'view', 'html')
            );

        $this->mrTemplate->AddVar(
            'content', 
            'URL_RTF',
            Dispatcher::Instance()->GetUrl(
                'lap_realisasi_penerimaan_pnbp', 
                'rtfLapPenerimaanPNBP', 
                'view', 
                'html'
            ) . 
            '&tgl=' . Dispatcher::Instance()->Encrypt($data['tgl']) . 
            '&id=' . Dispatcher::Instance()->Encrypt($userId) . 
            '&unitkerja=' . Dispatcher::Instance()->Encrypt($this->Data['unitkerja_label'])
        );

        $this->mrTemplate->AddVar(
            'content', 
            'URL_CETAK', 
            Dispatcher::Instance()->GetUrl(
                'lap_realisasi_penerimaan_pnbp', 
                'cetakLapPenerimaanPNBP', 
                'view', 
                'html'
            ) .
            "&tgl=" . Dispatcher::Instance()->Encrypt($data['tgl']) . 
            "&id=" . Dispatcher::Instance()->Encrypt($userId) . 
            "&unitkerja=" . Dispatcher::Instance()->Encrypt($this->Data['unitkerja']) . 
            '&unitkerja_label=' . Dispatcher::Instance()->Encrypt($this->Data['unitkerja_label'])
        );

        $this->mrTemplate->AddVar(
            'content', 
            'URL_EXCEL', 
            Dispatcher::Instance()->GetUrl(
                'lap_realisasi_penerimaan_pnbp', 
                'excelLapPenerimaanPNBP', 
                'view', 
                'xlsx'
            ) . 
            "&tgl=" . Dispatcher::Instance()->Encrypt($data['tgl']) . 
            "&id=" . Dispatcher::Instance()->Encrypt($userId) . 
            "&unitkerja=" . Dispatcher::Instance()->Encrypt($this->Data['unitkerja']) . 
            '&unitkerja_label=' . Dispatcher::Instance()->Encrypt($this->Data['unitkerja_label'])
        );
        
        $this->mrTemplate->AddVar(
            'content', 
            'URL_RESET', 
            Dispatcher::Instance()->GetUrl('lap_realisasi_penerimaan_pnbp', 'lapPenerimaanPNBP', 'view', 'html')
        );

        $this->mrTemplate->AddVar('content', 'UNITKERJA', $this->Data['unitkerja']);
        if ($this->Data['total_sub_unit_kerja'] > 0) {
            $this->mrTemplate->AddVar('cek_unitkerja_parent', 'IS_PARENT', 'YES');
        } else {
            $this->mrTemplate->AddVar('cek_unitkerja_parent', 'IS_PARENT', 'NO');
        }
        
        $this->mrTemplate->AddVar('cek_unitkerja_parent', 'UNITKERJA_LABEL', $this->Data['unitkerja_label']);
        $this->mrTemplate->AddVar(
            'cek_unitkerja_parent', 
            'URL_POPUP_UNITKERJA', 
            Dispatcher::Instance()->GetUrl(
                'lap_realisasi_penerimaan_pnbp', 
                'popupUnitkerja', 
                'view', 
                'html'
            )
        );

        if ($this->Pesan) {
            $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
            $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
            $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
        }

        if (empty($data['data'])) {
            $this->mrTemplate->AddVar('data_grid', 'COLSPAN_EMPTY', $countColsBulan + 6);
            $this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'YES');
        } else {
            $this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'NO');

            $data_list = $data['data'];
            $kode_unit = '';
            $surplusDefisit = 0;
            $index = 0;
            $send = array();
            $number = 0;
            $no = 0;
            for ($i = 0; $i < sizeof($data_list);) {

                if ($i == 0) {
                    #$this->mrTemplate->AddVar('content', 'FIRST_NUMBER', $number);
                }
                if ($i == sizeof($data_list) - 1) {
                    #$this->mrTemplate->AddVar('content', 'LAST_NUMBER', $number);
                }

                if (($data_list[$i]['kode_unit'] == $kode_unit)) {
                    
                    $send[$index]['tipe']='item';
                    $send[$index]['unit_id'] = $data_list[$i]['idunit'];
                    $send[$index]['jb_id'] = $data_list[$i]['jb_id'];
                    $send[$index]['target_pnbp'] = number_format($data_list[$i]['target_pnbp'], 0, ',', '.');
                    $send[$index]['total_realisasi'] = number_format($data_list[$i]['total_realisasi'], 0, ',', '.');
                    $surplusDefisit = ( $data_list[$i]['total_realisasi'] - $data_list[$i]['target_pnbp']);
                    $send[$index]['surplus_defisit'] = NumberFormat::Accounting($surplusDefisit, 0);

                    $send[$index]['class_name'] = "";
                    $send[$index]['nomor'] = $no;
                    $send[$index]['class_button'] = "links";

                    $send[$index]['kode'] = "";
                    $send[$index]['nama'] = "<b>" . $data_list[$i]['jenisBiayaNama'] . "</b>";
                    $send[$index]['keterangan'] = $data_list[$i]['keterangan'];
                    $i++;
                    $no++;
                    $number++;
                
                } elseif ($data_list[$i]['kode_unit'] != $kode_unit) {
                    $kode_unit = $data_list[$i]['kode_unit'];
                    $send[$index]['tipe']='unit';
                    $send[$index]['kode'] = "<b>" . $kode_unit . "</b>";
                    $send[$index]['nama'] = "<b>" . $data_list[$i]['nama_unit'] . "</b>";
                    $send[$index]['target_pnbp'] = "";
                    $send[$index]['total_realisasi'] = "";
                    $send[$index]['surplus_defisit'] = "";
                    $send[$index]['tarif'] = "";
                    $send[$index]['nomor'] = "";                    
                    $send[$index]['class_name'] = "table-common-even1";
                    $send[$index]['class_button'] = "toolbar";
                    $send[$index]['keterangan'] = '';
                    $no = 1;
                }                

                $index++;
            }

              foreach ($send as $itemData) {
                $this->mrTemplate->clearTemplate('nominal_per_bulan');               
                foreach ($getHeaderBulan as  $bulan) {
                    if($itemData['tipe'] == 'item') {
                        $this->mrTemplate->SetAttribute('content_description', 'visibility', 'visible');
                        $this->mrTemplate->AddVar('content_description', 'DATA_KETERANGAN', $itemData['keterangan']);
                        $nominalPerBulan = $this->mObj->getNominalPerBulan($itemData['unit_id'], $itemData['jb_id'], $bulan['kode_bulan']);
                        $bulan['nominal_per_bulan']= number_format($nominalPerBulan,0,',','.');
                    } else {
                        $bulan['nominal_per_bulan']= 0;
                        $this->mrTemplate->SetAttribute('content_description', 'visibility', 'hidden');                        
                    }
                    
                    $this->mrTemplate->AddVars('nominal_per_bulan',  $bulan,'D_');
                    $this->mrTemplate->parseTemplate('nominal_per_bulan', 'a');
                }
                $this->mrTemplate->AddVars('data_item', $itemData, 'DATA_');
                $this->mrTemplate->parseTemplate('data_item', 'a');
            }
            
            $total_target = $data['total_target_penerimaan'];
            $total_real = $data['total_realisasi'];
            $total_surplus_defisit = $total_real - $total_target;
            foreach ($getHeaderBulan as  $bulan) {
                $getTotalNominalPerBulan = $this->mObj->getNominalTotalPerBulan($bulan['kode_bulan']);
                $this->mrTemplate->AddVar('total_per_bulan', 'TOTAL_PER_BULAN',number_format($getTotalNominalPerBulan,0,',','.'));
                $this->mrTemplate->parseTemplate('total_per_bulan', 'a');
            }
            
            $this->mrTemplate->AddVar('data_total', 'TOTAL_TARGET_PNBP', number_format($total_target, 0, ',', '.'));
            
            $this->mrTemplate->AddVar('data_total', 'TOTAL_TOTAL_REALISASI', number_format($total_real, 0, ',', '.'));
            $this->mrTemplate->AddVar('data_total', 'TOTAL_SURPLUS_DEFISIT', NumberFormat::Accounting($total_surplus_defisit, 0));
        }
    }

}

?>