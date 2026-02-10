<?php

class LapNeracaLajur extends Database {

    protected $mSqlFile = 'module/lap_neraca_lajur/business/lap_neraca_lajur.sql.php';
    public $indonesianMonth = array(
        0 => array(
            'id' => 0,
            'name' => 'N/A'
        ), array(
            'id' => 1,
            'name' => 'Januari'
        ), array(
            'id' => 2,
            'name' => 'Februari'
        ), array(
            'id' => 3,
            'name' => 'Maret'
        ), array(
            'id' => 4,
            'name' => 'April'
        ), array(
            'id' => 5,
            'name' => 'Mei'
        ), array(
            'id' => 6,
            'name' => 'Juni'
        ), array(
            'id' => 7,
            'name' => 'Juli'
        ), array(
            'id' => 8,
            'name' => 'Agustus'
        ), array(
            'id' => 9,
            'name' => 'September'
        ), array(
            'id' => 10,
            'name' => 'Oktober'
        ), array(
            'id' => 11,
            'name' => 'November'
        ), array(
            'id' => 12,
            'name' => 'Desember'
        )
    );

    public function __construct($connectionNumber = 0) {
        parent::__construct($connectionNumber);
    }

    public function GetMinMaxThnTrans() {
        $ret = $this->open($this->mSqlQueries['get_minmax_tahun_transaksi'], array($start, $count));
        if ($ret)
            return $ret[0];
        else {
            $now_thn = date('Y');
            $thn['minTahun'] = $now_thn - 5;
            $thn['maxTahun'] = $now_thn + 5;
            return $thn;
        }
    }

    public function GetDataLaporan($tanggalAwal, $tanggalAkhir) {
        //$this->SetDebugOn();
        if($tanggalAwal < $tanggalAkhir) {
            $result = $this->open($this->mSqlQueries['get_data_laporan'], array(
                $tanggalAwal, $tanggalAkhir
            ));
        }
        return $result;
    }

    public function GetLevelCoa() {
        $result = $this->open($this->mSqlQueries['get_level_coa'], array());
        return $result;
    }

    public function GetHeaderKolom() {
        $result = $this->open($this->mSqlQueries['get_header_kolom'], array());
        return $result;
    }

    public function GetChildAkun($parentAkunId) {
        $result = $this->open($this->mSqlQueries['get_child_akun'], array($parentAkunId));
        return $result[0]['jml_akun'];
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
     * fungsi GetFormatAngka
     * untuk memformat angka
     * @param number $angka
     * @return String
     * @acces Public
     */
    public function SetFormatAngka($angka, $digit = 0) {
        $angka = (float) $angka;
        $str_angka = '';
        if ($angka < 0) {
            $str_angka = '(' . number_format(($angka * (-1)), $digit, ',', '.') . ')';
        } else {
            $str_angka = number_format($angka, $digit, ',', '.');
        }
        return $str_angka;
    }

    /**
     * @required indonesianMonths
     * @param String $date date format YYYY-mm-dd H:i:s, YYYY-mm-dd
     * @param String $format long, short
     * @return String  Indonesian Date
     */
    public function indonesianDate($date, $format = 'long') {
        $timeFormat = '%02d:%02d:%02d';
        $patern = '/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})/';
        $patern1 = '/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/';
        switch ($format) {
            case 'long':
                $dateFormat = '%02d %s %04d';
                break;
            case 'short':
                $dateFormat = '%02d-%s-%04d';
                break;
            default:
                $dateFormat = '%02d %s %04d';
                break;
        }

        if (preg_match($patern, $date, $matches)) {
            $year = (int) $matches[1];
            $month = (int) $matches[2];
            $day = (int) $matches[3];
            $hour = (int) $matches[4];
            $minute = (int) $matches[5];
            $second = (int) $matches[6];
            $mon = $this->indonesianMonth[$month];

            $date = sprintf($dateFormat, $day, $mon, $year);
            $time = sprintf($timeFormat, $hour, $minute, $second);
            $result = $date . ' ' . $time;
        } elseif (preg_match($patern1, $date, $matches)) {
            $year = (int) $matches[1];
            $month = (int) $matches[2];
            $day = (int) $matches[3];
            $mon = $this->indonesianMonth[$month]['name'];

            $date = sprintf($dateFormat, $day, $mon, $year);

            $result = $date;
        } else {
            $date = getdate();
            $year = (int) $date['year'];
            $month = (int) $date['mon'];
            $day = (int) $date['mday'];
            $hour = (int) $date['hours'];
            $minute = (int) $date['minutes'];
            $second = (int) $date['seconds'];
            $mon = $this->indonesianMonth[$month]['name'];

            $date = sprintf($dateFormat, $day, $mon, $year);
            $time = sprintf($timeFormat, $hour, $minute, $second);
            $result = $date . ' ' . $time;
        }

        return $result;
    }

}

?>