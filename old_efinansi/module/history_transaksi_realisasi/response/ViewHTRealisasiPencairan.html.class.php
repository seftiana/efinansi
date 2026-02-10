<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/history_transaksi_realisasi/business/HTRealisasiPencairan.class.php';

class ViewHTRealisasiPencairan extends HtmlResponse {

    protected $Pesan;

    public function TemplateModule() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') .
                'module/history_transaksi_realisasi/template');
        $this->SetTemplateFile('view_ht_realisasi_pencairan.html');
    }

    public function ProcessRequest() {
        $Obj = new HTRealisasiPencairan();
        $tahunPembukuan   = $Obj->getTahunPembukuanPeriode(array('open' => true));

        if (isset($_POST['btncari'])) {
            $decMulaiTanggal = $_POST['mulai_day'];
            $decMulaiBulan = $_POST['mulai_mon'];
            $decMulaiTahun = $_POST['mulai_year'];

            $decSelesaiTanggal = $_POST['selesai_day'];
            $decSelesaiBulan = $_POST['selesai_mon'];
            $decSelesaiTahun = $_POST['selesai_year'];

            $nomor = $_POST['nomor_bukti'];

            $mak_nama = $_POST['mak_nama'];
            $posting = $_POST['posting'];
            $kas = $_POST['kas'];
        } elseif (isset($_GET['cari'])) {
            $decMulaiTanggal = Dispatcher::Instance()->Decrypt($_GET['mulai_day']);
            $decMulaiBulan = Dispatcher::Instance()->Decrypt($_GET['mulai_mon']);
            $decMulaiTahun = Dispatcher::Instance()->Decrypt($_GET['mulai_year']);

            $decSelesaiTanggal = Dispatcher::Instance()->Decrypt($_GET['selesai_day']);
            $decSelesaiBulan = Dispatcher::Instance()->Decrypt($_GET['selesai_mon']);
            $decSelesaiTahun = Dispatcher::Instance()->Decrypt($_GET['selesai_year']);

            $nomor = Dispatcher::Instance()->Decrypt($_GET['nomor_bukti']);

            $mak_nama = Dispatcher::Instance()->Decrypt($_GET['mak_nama']);
            $posting = Dispatcher::Instance()->Decrypt($_GET['posting']);
            $kas = Dispatcher::Instance()->Decrypt($_GET['kas']);
        } else {
            $decMulaiTanggal = date("01");
            $decMulaiBulan = date("01");
            $decMulaiTahun = date("Y");

            $decSelesaiTanggal = date("d");
            $decSelesaiBulan = date("m");
            $decSelesaiTahun = date("Y");

            $nomor = '';

            $mak_nama = '';
            $posting = '';
            $kas = 'all';
        }
        $mulai_selected = $decMulaiTahun . "-" . $decMulaiBulan . "-" . $decMulaiTanggal;
        $selesai_selected = $decSelesaiTahun . "-" . $decSelesaiBulan . "-" . $decSelesaiTanggal;

        //view
        $itemViewed = 20;
        $currPage = 1;
        $startRec = 0;
        if (isset($_GET['page'])) {
            $currPage = (string) $_GET['page']->StripHtmlTags()->SqlString()->Raw();
            $startRec = ($currPage - 1) * $itemViewed;
        }
        $dataDetilTransaksi = $Obj->getData(
                $startRec, $itemViewed, $mulai_selected, $selesai_selected, $nomor, $posting, $mak_nama, $kas);
        $totalData = $Obj->GetCountData();
        $statusJurnalYa = GTFWConfiguration::GetValue('language', 'status_jurnal_ya');
        $statusJurnalTidak = GTFWConfiguration::GetValue('language', 'status_jurnal_tidak');
        $arr_is_posting = array(
            array('id' => 'Y', 'name' => $statusJurnalYa),
            array('id' => 'T', 'name' => $statusJurnalTidak));
        Messenger::Instance()->SendToComponent(
                'combobox', 'Combobox', 'view', 'html', 'posting', array(
            'posting',
            $arr_is_posting,
            $posting,
            true,
            ' style="width:100px;" id="posting"'), Messenger::CurrentRequest);
        $arr_kas = array(
            array('id' => 1, 'name' => 'Kas Kecil ( Total <= 500.000 )'),
            array('id' => 2, 'name' => 'Kas Besar ( Total > 500.000 )')
        );

        Messenger::Instance()->SendToComponent(
                'combobox', 'Combobox', 'view', 'html', 'kas', array(
            'kas',
            $arr_kas,
            $kas,
            true,
            'id="kas"'), Messenger::CurrentRequest);

        $url = Dispatcher::Instance()->GetUrl(
                Dispatcher::Instance()->mModule, Dispatcher::Instance()->mSubModule, Dispatcher::Instance()->mAction, Dispatcher::Instance()->mType .
                '&mulai_day=' . Dispatcher::Instance()->Encrypt($decMulaiTanggal) .
                '&mulai_mon=' . Dispatcher::Instance()->Encrypt($decMulaiBulan) .
                '&mulai_year=' . Dispatcher::Instance()->Encrypt($decMulaiTahun) .
                '&selesai_day=' . Dispatcher::Instance()->Encrypt($decSelesaiTanggal) .
                '&selesai_mon=' . Dispatcher::Instance()->Encrypt($decSelesaiBulan) .
                '&selesai_year=' . Dispatcher::Instance()->Encrypt($decSelesaiTahun) .
                '&nomor_bukti=' . Dispatcher::Instance()->Encrypt($nomor) .
                '&posting=' . Dispatcher::Instance()->Encrypt($posting) .
                '&mak_nama=' . Dispatcher::Instance()->Encrypt($mak_nama) .
                '&kas=' . Dispatcher::Instance()->Encrypt($kas) .
                '&cari=' . Dispatcher::Instance()->Encrypt(1));

        Messenger::Instance()->SendToComponent(
                'paging', 'Paging', 'view', 'html', 'paging_top', array(
            $itemViewed,
            $totalData,
            $url,
            $currPage), Messenger::CurrentRequest);

        $msg = Messenger::Instance()->Receive(__FILE__);
        $this->Pesan = $msg[0][1];
        $this->css = $msg[0][2];

        $tahun['start'] = date("Y") - 5;
        $tahun['end'] = date("Y") + 5;

        Messenger::Instance()->SendToComponent(
                'tanggal', 'Tanggal', 'view', 'html', 'mulai', array(
            $mulai_selected,
            $tahun['start'],
            $tahun['end'], '', '',
            'mulai'), Messenger::CurrentRequest);

        Messenger::Instance()->SendToComponent(
                'tanggal', 'Tanggal', 'view', 'html', 'selesai', array(
            $selesai_selected,
            $tahun['start'],
            $tahun['end'], '', '',
            'selesai'), Messenger::CurrentRequest);

        $return['tanggal_awal'] = $mulai_selected;
        $return['tanggal_akhir'] = $selesai_selected;
        $return['tahun_pembukuan'] = $tahunPembukuan;
        $return['no_bpkb'] = $nomor;
        $return['fpa'] = $mak_nama;
        $return['is_jurnal'] =$posting;
        $return['dataDetilTransaksi'] = $dataDetilTransaksi;
        $return['start'] = $startRec + 1;
        $return['nomor'] = $nomor;
        $return['mak_nama'] = $mak_nama;
        $return['statusJurnalYa'] = $statusJurnalYa;
        $return['statusJurnalTidak'] = $statusJurnalTidak;
        return $return;
    }

    public function ParseTemplate($data = NULL) {
        $tahunPembukuan = $data['tahun_pembukuan'];

        $this->mrTemplate->AddVar('content', 'URL_SEARCH', Dispatcher::Instance()->GetUrl(
                        'history_transaksi_realisasi', 'HTRealisasiPencairan', 'view', 'html'));

        $this->mrTemplate->AddVar('content', 'URL_POPUP_MAK', Dispatcher::Instance()->GetUrl(
                        'history_transaksi_realisasi', 'popupMak', 'view', 'html'));

        //export kas kecil
        $tawal = Dispatcher::Instance()->Encrypt($data['tanggal_awal']);
        $takhir = Dispatcher::Instance()->Encrypt($data['tanggal_akhir']);
        $no_bpkb = Dispatcher::Instance()->Encrypt($data['no_bpkb']);
        $fpa = Dispatcher::Instance()->Encrypt($data['fpa']);
        $is_jurnal = Dispatcher::Instance()->Encrypt($data['is_jurnal']);
        
        $query = '&tawal='.$tawal.'&takhir='.$takhir.'&fpa='.$fpa.'&sts_jurnal='.$is_jurnal.'&no_bpkb='.$no_bpkb;
        $url_export_laporan = Dispatcher::Instance()->GetUrl(
                            'history_transaksi_realisasi', 
                            'ExportBuktiKasKecil', 
                            'view', 
                            'xlsx') . $query;
        $this->mrTemplate->AddVar('content', 'URL_EXPORT_LAPORAN', $url_export_laporan);

        $this->mrTemplate->AddVar('content', 'NOMOR_BUKTI', $data['nomor']);
        $this->mrTemplate->AddVar('content', 'URAIAN', $data['uraian']);
        $this->mrTemplate->AddVar('content', 'MAK_NAMA', $data['mak_nama']);
        $this->mrTemplate->AddVar('content', 'NO_PENGAJUAN', $data['no_pengajuan']);

        if ($this->Pesan) {
            $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
            $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
            $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
        }

        if (empty($data['dataDetilTransaksi'])) {
            $this->mrTemplate->AddVar('data', 'DATA_EMPTY', 'YES');
        } else {
            //$decPage = Dispatcher::Instance()->Decrypt($_REQUEST['page']);
            //$encPage = Dispatcher::Instance()->Encrypt($decPage);
            $this->mrTemplate->AddVar('data', 'DATA_EMPTY', 'NO');

            //untuk confirm delete
            $label = GTFWConfiguration::GetValue('language', 'transaksi_realisasi_pencairan');
            $urlDelete = 'history_transaksi_realisasi|deleteHTRealisasiPencairan|do|html';
            $urlReturn = 'history_transaksi_realisasi|HTRealisasiPencairan|view|html';
            $URLDELETE = Dispatcher::Instance()->GetUrl(
                    'history_transaksi_realisasi', 'deleteHTRealisasiPencarian', 'do', 'html');

//mulai bikin tombol delete
            #$label = "Transaksi";
            #$urlDelete = Dispatcher::Instance()->GetUrl('transaksi_realisasi', 'deleteTransaksi', 'do', 'html');
            #$urlReturn = Dispatcher::Instance()->GetUrl('transaksi_realisasi', 'detilTransaksi', 'view', 'html');
            #Messenger::Instance()->Send('confirm', 'confirmDelete', 'do', 'html', array($label, $urlDelete, $urlReturn),Messenger::NextRequest);
            #$this->mrTemplate->AddVar('content', 'URL_DELETE', Dispatcher::Instance()->GetUrl('confirm', 'confirmDelete', 'do', 'html'));

            $dataDetilTransaksi = $data['dataDetilTransaksi'];
            $arr_periode = array();
            $periode = "";
            $j = 0; #print_r($dataDetilTransaksi);
            for ($i = 0; $i < sizeof($dataDetilTransaksi); $i++) {
                $idEnc = Dispatcher::Instance()->Encrypt($dataDetilTransaksi[$i]['id']);
                if ($dataDetilTransaksi[$i]['tanggal'] != $periode) {
                    $arr_periode[$j]['periode'] = $dataDetilTransaksi[$i]['tanggal'];
                    $periode = $dataDetilTransaksi[$i]['tanggal'];
                    $j++;
                }
                #untuk cetak bkk bkm bm
                #peneriman / bkm

                #$url_cetak_bukti = Dispatcher::Instance()->GetUrl('transaksi_realisasi', 'CetakBuktiTransaksi', 'view', 'html') . '&dataId=' . $idEnc . '&tipe=bkk';
                #$dataDetilTransaksi[$i]['url_cetak_bukti'] = '<a href="javascript:void(0)" onclick="bukaPopupCetak(\''.$url_cetak_bukti.'\')" title="Cetak BKK"><img src="images/button-print.gif" alt="Cetak BKK"/></a>';

                $no = $i + $data['start'];
                $dataDetilTransaksi[$i]['number'] = $no;
                if ($i == 0)
                    $this->mrTemplate->AddVar('content', 'FIRST_NUMBER', $no);
                if ($i == sizeof($dataDetilTransaksi) - 1)
                    $this->mrTemplate->AddVar('content', 'LAST_NUMBER', $no);

                if ($no % 2 != 0)
                    $dataDetilTransaksi[$i]['class_name'] = 'table-common-even';
                else
                    $dataDetilTransaksi[$i]['class_name'] = '';

                $dataDetilTransaksi[$i]['nominal_label'] = number_format($dataDetilTransaksi[$i]['nominal'], 2, ',', '.');

                if($dataDetilTransaksi[$i]['nominal'] <= 500000){
                    $dataDetilTransaksi[$i]['url_cetak'] = Dispatcher::Instance()->GetUrl(
                                    'history_transaksi_realisasi', 'FormCetakTransaksi', 'view', 'html') .
                            '&dataId=' . $idEnc;
                    $dataDetilTransaksi[$i]['label_cetak'] = 'Cetak Bukti Kas Kecil';
                }else{
                    $dataDetilTransaksi[$i]['url_cetak'] = Dispatcher::Instance()->GetUrl(
                                    'history_transaksi_realisasi', 'FormCetakBKK', 'view', 'html') .
                            '&dataId=' . $idEnc .
                            '&tipe=bkk';
                    $dataDetilTransaksi[$i]['label_cetak'] = 'Cetak Bukti Kas Besar';
                }

                if ($dataDetilTransaksi[$i]['is_jurnal'] == "Y") {
                    $dataDetilTransaksi[$i]['url_edit'] = '';
                    $dataDetilTransaksi[$i]['url_delete'] = '';
                    $dataDetilTransaksi[$i]['status_jurnal'] = $data['statusJurnalYa'];
                } else {
                    $dataDetilTransaksi[$i]['status_jurnal'] = $data['statusJurnalTidak'];
                    $url_deletee = Dispatcher::Instance()->GetUrl(
                                    'confirm', 'confirmDelete', 'do', 'html') .
                            '&urlDelete=' . $urlDelete .
                            '&urlReturn=' . $urlReturn .
                            '&id=' . Dispatcher::Instance()->Encrypt(
                                    $dataDetilTransaksi[$i]['id']) .
                            '&label=' . $label .
                            '&dataName=' . $dataDetilTransaksi[$i]['kkb'];

                    $url_edit = Dispatcher::Instance()->GetUrl(
                                    'history_transaksi_realisasi', 'HTFormRealisasiPencairan', 'view', 'html') .
                            '&dataId=' . $idEnc;

                    // Cek Tahun Pembukuan Aktif
                    if($tahunPembukuan[0]['id'] == $dataDetilTransaksi[$i]['tp_id']){
                        $dataDetilTransaksi[$i]['url_edit'] = '<a class="xhr dest_subcontent-element" ' .
                                'href="' . $url_edit .
                                '" title="Edit">' .
                                '<img src="images/button-edit.gif" alt="Edit"/></a>';

                        $dataDetilTransaksi[$i]['url_delete'] = '<a class="xhr dest_subcontent-element" ' .
                                'onClick="javascript: ' .
                                'return showBoxConfirmDelete("' .
                                $dataDetilTransaksi[$i]['id'] .
                                '", "' .
                                $dataDetilTransaksi[$i]['kkb'] .
                                '", "' . $URLDELETE .
                                '");" href="' . $url_deletee .
                                '" title="Hapus">' .
                                '<img src="images/button-delete.gif" alt="Hapus"/></a>';
                    }

                    #$this->mrTemplate->AddVar("data_item", "IDDELETE", $data['fasilitas'][$i]['fasilitas_id']);
                    $dataDetilTransaksi[$i]['iddelete'] = $dataDetilTransaksi[$i]['id'];
                    #$this->mrTemplate->AddVar("data_item", "URLDELETE", $URLDELETE);
                    $dataDetilTransaksi[$i]['urldelete'] = $URLDELETE;
                }

                $this->mrTemplate->AddVars('data_item', $dataDetilTransaksi[$i], 'DATA_');
                $this->mrTemplate->parseTemplate('data_item', 'a');
            }

            for ($j = 0; $j < sizeof($arr_periode); $j++) {
                $this->mrTemplate->AddVar('periode_item', 'LIST_PERIODE', $arr_periode[$j]['periode']);
                $this->mrTemplate->parseTemplate('periode_item', 'a');
            }
        }
    }

}

?>