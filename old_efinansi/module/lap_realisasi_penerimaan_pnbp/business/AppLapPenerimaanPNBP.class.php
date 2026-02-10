<?php

/**
 * Laporan Penerimaan
 * data diambil dari tansaks_penerimaan_bank
 * baik dari transaksi pembayaran (gtPembayaran : pengakuan saja) atau bukan
 *
 *
 * @analyzed dyah.fajar@gamatechno.com
 * @author noor.hadi@gamatechno.com
 *
 * @modified since 20 Juli 2017
 *
 * @copyright (c) 2017, Gamatechno Indonesia
 */
class AppLapPenerimaanPNBP extends Database {

    protected $mSqlFile;
    public $_POST;
    public $_GET;
    // 0  1  2  3  4  5  6  7  8  9 10 11
    //Ja Fb Ma Ap Me Jn Jl Ag Sp Ok Nv Ds
    private $_mUmurBulan = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
    private $_namaBulan = array(
        '1' => 'Januari',
        '2' => 'Februari',
        '3' => 'Maret',
        '4' => 'April',
        '5' => 'Mei',
        '6' => 'Juni',
        '7' => 'Juli',
        '8' => 'Agustus',
        '9' => 'September',
        '10' => 'Oktober',
        '11' => 'November',
        '12' => 'Desember'
    );
    private $_nominalPerBulan = array();
    private $_nominalTotalPerBulan = array();

    public function __construct($connectionNumber = 0) {
        $this->mSqlFile = 'module/lap_realisasi_penerimaan_pnbp/business/applappenerimaanpnbp.sql.php';
        $this->_POST = is_object($_POST) ? $_POST->AsArray() : $_POST;
        $this->_GET = is_object($_GET) ? $_GET->AsArray() : $_GET;
        parent::__construct($connectionNumber);
        //$this->setdebugOn();
    }

    public function GetCountData() {
        $data = $this->Open($this->mSqlQueries['get_count_data'], array());
        if (!$data) {
            return 0;
        } else {
            return $data[0]['total'];
        }
    }

    /**
     * buildDate
     * untuk menyusun format tanggal dari form pencarian/ componen tanggal dari gtfw
     * @string $tangal parameter tanggal dari input filter/form
     * @boolean $begin jika true maka day berawal dari angka 1 jika false maka day terakhir
     *
     * @return date
     */
    private function _buildDate($tanggal, $begin = true) {
        $bulan = (int) date('n', strtotime($tanggal));
        $tahun = (int) date('Y', strtotime($tanggal));
        $hari = $this->_mUmurBulan[$bulan - 1];

        //ganti hari jika tahun kabisat  khusus bulan 02
        //atau jika status begin true atau false
        if ($begin == false) {
            if (($tahun % 4) == 0) {
                if ($bulan == 2) {
                    $hari = 29;
                }
            }
        } else {
            $hari = 1;
        }
        //end
        return date('Y-m-d', strtotime($tahun . '-' . $bulan . '-' . $hari));
    }

    public function GetDataRealisasiPNBP($tanggalAwal, $tanggalAkhir, $unitkerja, $startRec, $itemViewed) {

        $startDate = $this->_buildDate($tanggalAwal);
        $endDate = $this->_buildDate($tanggalAkhir, false);

        $result = $this->Open($this->mSqlQueries['get_data_realisasi_pnbp'], array(
            $startDate,
            $endDate,
            $unitkerja, '%',
            $unitkerja,
            $startRec,
            $itemViewed));
        return $result;
    }

    public function GetTotalRealisasiPNBP($tanggalAwal, $tanggalAkhir, $unitkerja) {
        $startDate = $this->_buildDate($tanggalAwal);
        $endDate = $this->_buildDate($tanggalAkhir, false);
        $data = array(
            'total_target_penerimaan' => 0,
            'total_realisasi' => 0
        );
        $result = $this->Open($this->mSqlQueries['get_total_realisasi_penerimaan'], array(
            $startDate,
            $endDate,
            $unitkerja, '%',
            $unitkerja
        ));
        if (!empty($result)) {
            $data = array(
                'total_target_penerimaan' => $result[0]['target_pnbp'],
                'total_realisasi' => $result[0]['total_realisasi']
            );
        }
        
        return $data;
    }

    public function PrepareDataNominalPerBulan($tanggalAwal, $tanggalAkhir, $unitkerja) {

        $startDate = $this->_buildDate($tanggalAwal);
        $endDate = $this->_buildDate($tanggalAkhir, false);

        $dataNominal = $this->Open($this->mSqlQueries['get_nominal_perbulan'], array(
            $startDate,
            $endDate,
            $unitkerja, '%',
            $unitkerja)
        );

        if (!empty($dataNominal)) {
            foreach ($dataNominal as $itemNominal) {
                $this->_nominalPerBulan[$itemNominal['unit_id']][$itemNominal['jb_id']][$itemNominal['kode']] += $itemNominal['nominal'];
                $this->_nominalTotalPerBulan[$itemNominal['kode']] += $itemNominal['nominal'];
            }
        }
    }

    public function getNominalPerBulan($unitId, $jbId, $bulan) {
        $nominal = 0;
        if (array_key_exists($unitId, $this->_nominalPerBulan)) {
            if (array_key_exists($jbId, $this->_nominalPerBulan[$unitId])) {
                if (array_key_exists($bulan, $this->_nominalPerBulan[$unitId][$jbId])) {
                    $nominal = $this->_nominalPerBulan[$unitId][$jbId][$bulan];
                }
            }
        }

        return $nominal;
    }

    
    public function getNominalTotalPerBulan($bulan) {
        $total = 0;
        if (array_key_exists($bulan, $this->_nominalTotalPerBulan)) {
                $total = $this->_nominalTotalPerBulan[$bulan];
        }

        return $total;
    }

    public function GetDataRealisasiPNBPCetak($tahunAnggaran, $unitkerja) {
        $getTahunAnggaran = $this->GetTahunAnggaran($tahunAnggaran);
        $result = $this->Open($this->mSqlQueries['get_data_realisasi_pnbp_cetak'], array(
            date('Y-m-d', strtotime($getTahunAnggaran['tanggal_awal'])),
            date('Y-m-d', strtotime($getTahunAnggaran['tanggal_akhir'])),
            $unitkerja, '%',
            $unitkerja));
        return $result;
    }

    public function GetTotalDataRealisasiPnbpPerBulan($tahunAnggaran, $unitkerja) {
        $getTahunAnggaran = $this->GetTahunAnggaran($tahunAnggaran);
        $result = $this->Open($this->mSqlQueries['get_total_data_realisasi_pnbp_perbulan'], array(
            date('Y-m-d', strtotime($getTahunAnggaran['tanggal_awal'])),
            date('Y-m-d', strtotime($getTahunAnggaran['tanggal_akhir'])),
            $unitkerja, '%',
            $unitkerja));
        return $result[0];
    }

    public function GetDataRencanaPenerimaanById($id) {
        $result = $this->Open($this->mSqlQueries['get_data_rencana_penerimaan_by_id'], array($id));
        return $result;
    }

    //get combo tahun anggaran
    public function GetComboTahunAnggaran() {
        $result = $this->Open($this->mSqlQueries['get_combo_tahun_anggaran'], array());
        return $result;
    }

    public function GetTahunAnggaranAktif() {
        $result = $this->Open($this->mSqlQueries['get_tahun_anggaran_aktif'], array());
        return $result[0];
    }

    public function GetTahunAnggaran($id) {
        $result = $this->Open($this->mSqlQueries['get_tahun_anggaran'], array($id));
        return $result[0];
    }

    /**
     * format number persen
     * jika koma maka tampilkan dengan angka dibelakang koma
     * jika tidak ada koma maka ditampilkan tanpa koma
     */
    protected function FormatNumberPersen($number = 0) {
        $snumber = number_format($number, 2, ',', '.');
        $split_snumber = explode(',', $snumber);
        if (is_array($split_snumber)) {
            if (intval($split_snumber[1]) > 0) {
                $desimal = floatval('0.' . $split_snumber[1]);
                return $split_snumber[0] + $desimal;
            } else {
                return $split_snumber[0];
            }
        } else {
            return 0;
        }
    }

    public function getRangeYear() {
        $data = $this->Open($this->mSqlQueries['set_date'], array());
        $getdate = getdate();
        $currMon = (int) $getdate['mon'];
        $currYear = (int) $getdate['year'];

        if (!empty($data)) {
            $start_date = date('Y-m-d', strtotime($data[0]['startDate']));
            $end_date = date('Y-m-d', strtotime($data[0]['endDate']));
        } else {
            $start_date = date('Y-m-d', mktime(0, 0, 0, $currMon, 1, $currYear));
            $end_date = date('Y-m-t', strtotime($start_date));
        }

        return compact('start_date', 'end_date');
    }

    public function getRangeYearAktif() {
        $data = $this->Open($this->mSqlQueries['set_date_aktif'], array());
        $getdate = getdate();
        $currMon = (int) $getdate['mon'];
        $currYear = (int) $getdate['year'];

        if (!empty($data)) {
            $start_date = date('Y-m-d', strtotime($data[0]['startDate']));
            $end_date = date('Y-m-d', strtotime($data[0]['endDate']));
        } else {
            $start_date = date('Y-m-d', mktime(0, 0, 0, $currMon, 1, $currYear));
            $end_date = date('Y-m-d', strtotime($start_date));
        }

        return compact('start_date', 'end_date');
    }

    /**
     * getHeaderBulan
     * @param type $startDate
     * @param type $endDate
     * @return string
     */
    public function getHeaderBulan($startDate, $endDate) {
        $bulan = array();
        $begin = new DateTime($startDate);
        $end = new DateTime($endDate);

        $idx = 0;
        for ($i = $begin; $begin <= $end; $i->modify('+1 month')) {
            $bulan[$idx]['nama_bulan'] = $this->_namaBulan[$i->format("n")] . ' ' . $i->format("Y");
            $bulan[$idx]['kode_bulan'] = $i->format("Y") . '-' . $i->format("n");
            $idx++;
        }

        return $bulan;
    }

}

?>