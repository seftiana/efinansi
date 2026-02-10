<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/lap_transaksi_harian/business/AppLapTransaksiHarian.class.php';

require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'main/function/number_format.class.php';

class ViewLapTransaksiHarian extends HtmlResponse {

    protected $mObj;
    
    public function TemplateModule() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'module/lap_transaksi_harian/template');
        $this->SetTemplateFile('view_lap_transaksi_harian.html');
    }

    public function ProcessRequest() {
        $this->mObj = new AppLapTransaksiHarian();

        $post = $_POST->AsArray();

        if (isset($post['btncari'])) {
            $tglAwal = $post['tanggal_awal_year'] . "-" . $post['tanggal_awal_mon'] . "-" . $post['tanggal_awal_day'];
            $tgl = $post['tanggal_akhir_year'] . "-" . $post['tanggal_akhir_mon'] . "-" . $post['tanggal_akhir_day'];
            $jenis_transaksi = $post['jenis_transaksi'];
        } elseif (isset($_GET['cari'])) {
            $get_data = $_GET->AsArray();
            $tglAwal = $get_data['tgl_awal'];
            $tgl = $get_data['tgl'];
            $jenis_transaksi = $get_data['jenis_transaksi'];
        } else {
            $tglAwal = date("Y-01-01");
            $tgl = date("Y-m-d");
            $jenis_transaksi = 'all';
        }

        //tahun untuk combo
        $tahunTrans = $this->mObj->GetMinMaxThnTrans();
        Messenger::Instance()->SendToComponent(
                'tanggal', 'Tanggal', 'view', 'html', 'tanggal_awal', array(
            $tglAwal,
            $tahunTrans['minTahun'],
            $tahunTrans['maxTahun']
                ), Messenger::CurrentRequest
        );

        Messenger::Instance()->SendToComponent(
                'tanggal', 'Tanggal', 'view', 'html', 'tanggal_akhir', array(
            $tgl,
            $tahunTrans['minTahun'],
            $tahunTrans['maxTahun']
                ), Messenger::CurrentRequest
        );
        //end
        //combo jenis transaksi
        $arrJenisTransaksi = array(
            array('id' => '9', 'name' => 'Penerimaan Kas'),
            array('id' => '5', 'name' => 'Pengeluaran Kas'),
            array('id' => '8', 'name' => 'Penerimaan Bank'),
            array('id' => '10', 'name' => 'Pengeluaran Bank')
        );

        Messenger::Instance()->SendToComponent(
                'combobox', 'Combobox', 'view', 'html', 'jenis_transaksi', array(
            'jenis_transaksi',
            $arrJenisTransaksi,
            $jenis_transaksi,
            true,
            'id="jenis_transaksi"'
                ), Messenger::CurrentRequest
        );
        //end
        //view
        $itemViewed = 20;
        $currPage = 1;
        $startRec = 0;
        if (isset($_GET['page'])) {
            $currPage = (string) $_GET['page']->StripHtmlTags()->SqlString()->Raw();
            $startRec = ($currPage - 1) * $itemViewed;
        }


        $data_transaksi = $this->mObj->GetData($startRec, $itemViewed, $tglAwal, $tgl, $jenis_transaksi);
        $totalData = $this->mObj->GetCountData();
        $getTotalDK = $this->mObj->GetTotalDebetKredit($tglAwal, $tgl, $jenis_transaksi);
        
        //prepare data saldo awal
        $this->mObj->prepareDataSaldoAwal($tglAwal, $tgl,$jenis_transaksi);
        
        #echo '<pre>';
        #print_r($this->mObj->getAllSaldoAwal());
        #print_r($this->mObj->getAllSaldoAwalBerjalan());
        #echo '</pre>';
        //end
        #print_r($data_transaksi);
        $url = Dispatcher::Instance()->GetUrl(
                Dispatcher::Instance()->mModule, Dispatcher::Instance()->mSubModule, Dispatcher::Instance()->mAction, Dispatcher::Instance()->mType .
                '&tgl_awal=' . Dispatcher::Instance()->Encrypt($tglAwal) .
                '&tgl=' . Dispatcher::Instance()->Encrypt($tgl) .
                '&jenis_transaksi=' . Dispatcher::Instance()->Encrypt($jenis_transaksi) .
                '&cari=' . Dispatcher::Instance()->Encrypt(1)
        );

        Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top', array($itemViewed, $totalData, $url, $currPage), Messenger::CurrentRequest);

        $msg = Messenger::Instance()->Receive(__FILE__);
        $this->Pesan = $msg[0][1];
        $this->css = $msg[0][2];

        $return['data_transaksi'] = $data_transaksi;
        $return['totalDK'] = $getTotalDK;
        $return['start'] = $startRec + 1;

        $return['tgl_awal'] = $tglAwal;
        $return['tgl_akhir'] = $tgl;
        $return['jenis_transaksi'] = $jenis_transaksi;
        return $return;
    }

    public function ParseTemplate($data = NULL) {
        //$Obj = new AppLapTransaksiHarian();
        $tglAwal = $data['tgl_awal'];
        $tgl = $data['tgl_akhir'];
        $jenis_transaksi = $data['jenis_transaksi'];

        $this->mrTemplate->AddVar('content', 'URL_SEARCH', Dispatcher::Instance()->GetUrl('lap_transaksi_harian', 'LapTransaksiHarian', 'view', 'html'));

        $this->mrTemplate->AddVar('content', 'URL_CETAK', Dispatcher::Instance()->GetUrl('lap_transaksi_harian', 'CetakLapTransaksiHarian', 'view', 'html') .
                '&tgl_awal=' . Dispatcher::Instance()->Encrypt($tglAwal) .
                '&tgl=' . Dispatcher::Instance()->Encrypt($tgl) .
                '&jenis_transaksi=' . Dispatcher::Instance()->Encrypt($jenis_transaksi) .
                '&cetak=' . Dispatcher::Instance()->Encrypt('yes'));

        $this->mrTemplate->AddVar('content', 'URL_EXCEL', Dispatcher::Instance()->GetUrl('lap_transaksi_harian', 'ExcelLapTransaksiHarian', 'view', 'xlsx') .
                '&tgl_awal=' . Dispatcher::Instance()->Encrypt($tglAwal) .
                '&tgl=' . Dispatcher::Instance()->Encrypt($tgl) .
                '&jenis_transaksi=' . Dispatcher::Instance()->Encrypt($jenis_transaksi) .
                '&cetak=' . Dispatcher::Instance()->Encrypt('yes'));

        $this->mrTemplate->AddVar('content', 'URL_RTF', Dispatcher::Instance()->GetUrl('lap_transaksi_harian', 'RtfLapTransaksiHarian', 'view', 'html') .
                '&tgl_awal=' . Dispatcher::Instance()->Encrypt($tglAwal) .
                '&tgl=' . Dispatcher::Instance()->Encrypt($tgl) .
                '&jenis_transaksi=' . Dispatcher::Instance()->Encrypt($jenis_transaksi) .
                '&cetak=' . Dispatcher::Instance()->Encrypt('yes'));

        if (empty($data['data_transaksi'])) {
            $this->mrTemplate->AddVar('data_transaksi', 'TRANSAKSI_EMPTY', 'YES');
        } else {
            $this->mrTemplate->AddVar('data_transaksi', 'TRANSAKSI_EMPTY', 'NO');

            $no = 1;
            for ($i = 0; $i < sizeof($data['data_transaksi']); $i++) {
                if ($data['data_transaksi'][$i]['coa_kode_akun'] != $data['data_transaksi'][$i - 1]['coa_kode_akun']) {
                    $this->mrTemplate->AddVar("data_transaksi_item", "NO", '');
                    $this->mrTemplate->AddVar("data_transaksi_item", "STATUS", 'default');
                    $this->mrTemplate->AddVar("data_transaksi_item", "NO_REKENING", '<b>' . $data['data_transaksi'][$i]['coa_kode_akun'] . '</b>');
                    $this->mrTemplate->AddVar("data_transaksi_item", "CATATAN", '<b>' . $data['data_transaksi'][$i]['coa_nama_akun'] . '</b>');
                    $this->mrTemplate->AddVar("data_transaksi_item", "DEBET", '');
                    $this->mrTemplate->AddVar("data_transaksi_item", "KREDIT", '');
                    $this->mrTemplate->AddVar("data_transaksi_item", "CLASS_ROW", 'table-common-even');
                   
                    //get saldo bulan lalu
                    $saldo = $this->mObj->getSaldoAwalAkunBulanLalu($data['data_transaksi'][$i]['coa_id'], $data['data_transaksi'][$i]['coa_kelompok_id']);
                    //print_r($saldo);
                    $this->mrTemplate->AddVar("data_transaksi_item", "SALDO", NumberFormat::Accounting($saldo, 2));
                    $this->mrTemplate->parseTemplate("data_transaksi_item", "a");
                }

                $this->mrTemplate->AddVar("data_transaksi_item", "STATUS", 'default');
                $this->mrTemplate->AddVar("data_transaksi_item", "NO", $no);
                $this->mrTemplate->AddVar("data_transaksi_item", "NO_REKENING", $data['data_transaksi'][$i]['no_bpkb']);
                $this->mrTemplate->AddVar("data_transaksi_item", "CATATAN", $data['data_transaksi'][$i]['transaksi_catatan']);
                $this->mrTemplate->AddVar("data_transaksi_item", "SALDO", '');
                
                
                if ($data['data_transaksi'][$i]['transaksi_nilai_d'] != 0) {
                    $nilaiDebet = NumberFormat::Accounting($data['data_transaksi'][$i]['transaksi_nilai_d'], 2);
                } else {
                    $nilaiDebet = '';
                }
                if ($data['data_transaksi'][$i]['transaksi_nilai_k'] != 0) {
                    $nilaiKredit = NumberFormat::Accounting($data['data_transaksi'][$i]['transaksi_nilai_k'], 2);
                } else {
                    $nilaiKredit = '';
                }
                $this->mrTemplate->AddVar("data_transaksi_item", 'DEBET', $nilaiDebet);
                $this->mrTemplate->AddVar("data_transaksi_item", 'KREDIT', $nilaiKredit);
                $this->mrTemplate->parseTemplate("data_transaksi_item", "a");

                $no++;
                if ($data['data_transaksi'][$i]['coa_kode_akun'] != $data['data_transaksi'][$i + 1]['coa_kode_akun']) {
                    $this->mrTemplate->AddVar("data_transaksi_item", "STATUS", 'total');
                    $this->mrTemplate->AddVar("data_transaksi_item", "KETERANGAN", 'Sub Total');
                    
                    $debet = $this->mObj->getSaldoDebet($data['data_transaksi'][$i]['coa_id']);
                    $kredit = $this->mObj->getSaldoKredit($data['data_transaksi'][$i]['coa_id']);
                    $saldoBerjalan = $this->mObj->getSaldoAkunBulanBerjalan($data['data_transaksi'][$i]['coa_id'], $data['data_transaksi'][$i]['coa_kelompok_id']);
                    
                    if ($debet != 0)
                        $debetRp = NumberFormat::Accounting($debet, 2);
                    else
                        $debetRp = '';
                    if ($kredit != 0)
                        $kreditRp = NumberFormat::Accounting($kredit, 2);
                    else
                        $kreditRp = '';

                    $this->mrTemplate->AddVar("data_transaksi_item", "DEBET", $debetRp);
                    $this->mrTemplate->AddVar("data_transaksi_item", "KREDIT", $kreditRp);
                    $this->mrTemplate->AddVar("data_transaksi_item", "SALDO", NumberFormat::Accounting(($saldo + $saldoBerjalan), 2));
                    $this->mrTemplate->parseTemplate("data_transaksi_item", "a");
                    $no = 1;
                    $totalDebet += $debet;
                    $totalKredit += $kredit;
                    $debet = $kredit = $saldo = 0;
                }
            }
            //menampilkan grand total mutasi debet dan kredit
            $getTotalDK = $data['totalDK'];
            $this->mrTemplate->AddVar("data_transaksi_item", "STATUS", 'total');
            $this->mrTemplate->AddVar("data_transaksi_item", "KETERANGAN", 'GRAND TOTAL MUTASI DEBET KREDIT');
            $this->mrTemplate->AddVar("data_transaksi_item", "DEBET", NumberFormat::Accounting($getTotalDK['t_d'], 2));
            $this->mrTemplate->AddVar("data_transaksi_item", "KREDIT", NumberFormat::Accounting($getTotalDK['t_k'], 2));
            $this->mrTemplate->AddVar("data_transaksi_item", "SALDO", '');
            $this->mrTemplate->parseTemplate("data_transaksi_item", "a");
        }
    }

    public function date2string($date) {
        $bln = array(
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        );
        $arrtgl = explode('-', $date);
        return $arrtgl[2] . ' ' . $bln[(int) $arrtgl[1]] . ' ' . $arrtgl[0];
    }

}

?>
