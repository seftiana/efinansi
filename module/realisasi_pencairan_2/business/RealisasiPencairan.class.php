<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/generate_number/business/GenerateNumber.class.php';

class RealisasiPencairan extends Database {

    protected $mSqlFile;
    public $_POST;
    public $_GET;
    private $mNumber;
    public $indonesianMonth = array(
        0 => array(
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

    function __construct($connectionNumber = 0) {
        $this->mSqlFile = 'module/realisasi_pencairan_2/business/realisasi_pencairan.sql.php';
        $this->_POST = is_object($_POST) ? $_POST->AsArray() : $_POST;
        $this->_GET = is_object($_GET) ? $_GET->AsArray() : $_GET;
        parent::__construct($connectionNumber);
        $this->mNumber = new GenerateNumber($connectionNumber);
    }

    public function GetPeriodeTahun($param = array()) {
        $default = array(
            'active' => false,
            'open' => false
        );
        $options = array_merge($default, (array) $param);
        $return = $this->Open($this->mSqlQueries['get_periode_tahun'], array(
            (int) ($options['active'] === false),
            (int) ($options['open'] === false)
        ));

        return $return;
    }

    /**
     * [getSettingValue description]
     * @param  string $name [description]
     * @return String $name [description]
     */
    public function getSettingValue($name = '') {
        $return = $this->Open($this->mSqlQueries['get_setting_name'], array(
            $name
        ));

        return $return[0]['name'];
    }

    public function Count() {
        $return = $this->Open($this->mSqlQueries['count'], array());

        if ($return) {
            return $return[0]['count'];
        } else {
            return 0;
        }
    }

    public function GetDanaSisaFpa($data_id, $kegdet_id) {//$this->SetDebugOn();
        $return = $this->Open($this->mSqlQueries['get_sisa_dana_fpa'], array(
            $data_id,
            (int) ($data_id === NULL OR $data_id == ''),
            $kegdet_id
        ));

        return $return[0]['sisaDana'];
    }

    function GetData($offset, $limit, $data) {
        $return = $this->Open($this->mSqlQueries['get_data'], array(
            $data['ta_id'],
            $data['unit_id'],
            $data['unit_id'],
            $data['unit_id'],
            $data['program_id'],
            (int) ($data['program_id'] == '' OR strtolower($data['program_id']) == 'all'),
            '%' . $data['kode'] . '%',
            '%' . $data['nama'] . '%',
            $data['jenis_kegiatan'],
            (int) ($data['jenis_kegiatan'] == '' OR strtolower($data['jenis_kegiatan']) == 'all'),
            $data['bulan'],
            (int) ($data['bulan'] == '' OR strtolower($data['bulan']) == 'all'),
            '%' . $data['no_pengajuan'] . '%',
            $offset,
            $limit
        ));

        return $return;
    }

    /**
     * @description Get Data Pengajuan Realisasi Detail
     * @param Int $id
     * @return Array $return
     */
    public function GetDataPengajuanRealisasiDet($id = null) {
        $return = $this->Open($this->mSqlQueries['get_data_pengajuan_realisasi_det'], array(
            $id,
            (int) ($id === NULL OR $id == ''),
            $id,
            (int) ($id === NULL OR $id == ''),
            $id
        ));

        if ($return) {
            return $return[0];
        } else {
            return null;
        }
    }

    public function GetKomponenAnggaranPengajuanRealisasi($id = null) {
        $return = $this->Open($this->mSqlQueries['get_komponen_anggaran_pengajuan_realisasi'], array(
            $id,
            (int) ($id === NULL OR $id == ''),
            $id
        ));

        return $return;
    }
    public function GetMaxId() {
        $return = $this->Open($this->mSqlQueries['get_max_id'], array());

        if ($return) {
            return $return[0];
        } else {
            return null;
        }
    }
    
    public function DoUpdateFile($fileName, $dataId) {
        
        $result = $this->Execute($this->mSqlQueries['do_update_file'], array(
            $fileName,
            $dataId
        ));

        return $result;
    }

    /**
     * @description Do add realisasi pencairan (UMK)
     * @param Array $param; Data yang akan di simpan di simpan dalam bentuk array
     * @return Boolean $result; true or false
     */
    public function DoAddRealisasiPencairan($param = array()) {
        $userid = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
        $result = true;
        $this->StartTrans();

        $nomorPengajuan = $this->GetAutoGenerate(date('Y-m-d', strtotime($param['tanggal'])));

        $result &= $this->Execute($this->mSqlQueries['do_add'], array(
            $param['kegiatanDetailId'],
            $nomorPengajuan,
            $param['nominal'],
            ($param['keterangan'] == '') ? '-' : $param['keterangan'],
            $userid,
            date('Y-m-d', strtotime($param['tanggal']))
        ));

        $pengrealId = $this->LastInsertId();

        if (!empty($param['komponen'])) {
            foreach ($param['komponen'] as $komponen) {
                $result &= $this->Execute($this->mSqlQueries['do_add_detail'], array(
                    $pengrealId,
                    $komponen['id'],
                    $komponen['deskripsi'],
                    $komponen['nominal'],
                    date('Y-m-d', strtotime($param['tanggal'])),
                    $userid
                ));
            }
        }
        return $this->EndTrans($result);
    }

    /**
     * @description DoUpdateRealisasiPencairan
     * @param Array $param
     * @return Boolean $result
     */
    public function DoUpdateRealisasiPencairan($param = array()) {
        $userid = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
        $result = true;
        $this->StartTrans();

        if (date('Y-m-d', strtotime($param['tanggal'])) != date('Y-m-d', strtotime($param['tanggal_old']))) {
            /** digunakan apabila ubah tanggal FPA maka Generate Number lagi
            * $nomorPengajuan = $this->GetAutoGenerate(date('Y-m-d', strtotime($param['tanggal'])));
            */
            $nomorPengajuan = $param['nomorPengajuan'];
        } else {
            $nomorPengajuan = $param['nomorPengajuan'];
        }
        // delete data detail yang sudah ada
        $pengrealId = $param['id'];
        $result &= $this->Execute($this->mSqlQueries['do_delete_pengajuan_detil'], array(
            $pengrealId
        ));

        $result &= $this->Execute($this->mSqlQueries['do_update'], array(
            $param['kegiatanDetailId'],
            $nomorPengajuan,
            $param['nominal'],
            ($param['keterangan'] == '') ? '-' : $param['keterangan'],
            $userid,
            date('Y-m-d', strtotime($param['tanggal'])),
            $pengrealId
        ));

        if (!empty($param['komponen'])) {
            foreach ($param['komponen'] as $komponen) {
                $result &= $this->Execute($this->mSqlQueries['do_add_detail'], array(
                    $pengrealId,
                    $komponen['id'],
                    $komponen['deskripsi'],
                    $komponen['nominal'],
                    date('Y-m-d', strtotime($param['tanggal'])),
                    $userid
                ));
            }
        }
        return $this->EndTrans($result);
    }

    /**
     * @description DoDeleteRealisasiPencairan
     * @param Int $id
     * @return Boolean $result
     */
    public function DoDeleteRealisasiPencairan($id) {
        $result = true;
        $this->StartTrans();
        $result &= $this->Execute($this->mSqlQueries['do_delete_pengajuan_detil'], array(
            $id
        ));
        $result &= $this->Execute($this->mSqlQueries['do_delete'], array(
            $id
        ));

        return $this->EndTrans($result);
    }

    function GetDataProgram($idTa) {
        $result = $this->Open($this->mSqlQueries['get_data_program'], array($idTa));
        return $result;
    }

    function GetDataCetak($id) {
        $result = $this->Open($this->mSqlQueries['get_data_cetak'], array($id));
        return $result;
    }

    function GetTransaksiPencairan($id) {
        $result = $this->Open($this->mSqlQueries['get_transaksi_pencairan'], array($id));
        return $result;
    }

    function GetDataById($id) {
        $result = $this->Open($this->mSqlQueries['get_data_by_id'], array($id, $id, $id));
        if ($result) {
            $result[0]['total_anggaran'] = number_format($result[0]['total_anggaran'], 0, ',', '.');
            $result[0]['realisasi_nominal'] = number_format($result[0]['realisasi_nominal'], 0, ',', '.');
            $result[0]['realisasi_pencairan'] = number_format($result[0]['realisasi_pencairan'], 0, ',', '.');
            return $result[0];
        } else {
            return $result;
        }
    }

    function GetDataTahunAnggaranSekarang() {
        $result = $this->Open($this->mSqlQueries['get_ta_aktif'], array());
        if ($result)
            return $result[0];
        else
            return false;
    }

    function GetDataJenisKegiatan() {
        $result = $this->Open($this->mSqlQueries['get_data_jenis_kegiatan'], array());
        return $result;
    }

    function GetMinTahun() {
        $result = $this->Open($this->mSqlQueries['get_min_tahun'], array());
        if ($result) {
            $tgl = $result[0]['min'];
            $tgl = explode('-', $tgl);
            return $tgl[0] - 5;
        } else {
            $tgl = date("Y");
            return $tgl - 5;
        }
    }

    function GetMaxTahun() {
        $result = $this->Open($this->mSqlQueries['get_max_tahun'], array());
        if ($result) {
            $tgl = $result[0]['max'];
            $tgl = explode('-', $tgl);
            return $tgl[0] + 5;
        } else {
            $tgl = date("Y");
            return $tgl + 5;
        }
    }

    function GetCountUnitKerja($nama, $parent_id) {
        $result = $this->Open($this->mSqlQueries['get_count_unit_kerja'], array($parent_id, '%' . $nama . '%'));


        if (!$result)
            return 0;
        else
            return $result[0]['total'];
    }

    function GetUnitKerja($startRec, $itemViewed, $nama, $parent_id) {
        $ret = $this->Open($this->mSqlQueries['get_unit_kerja'], array($parent_id, '%' . $nama . '%', $startRec, $itemViewed));

        return $ret;
    }

    function GetJenisKegiatan($detailkegiatan_id) {
        $ret = $this->Open($this->mSqlQueries['get_jenis_kegiatan'], array($id));
        if ($ret)
            return $ret[0]['jenis_kegiatan'];
        else
            return $ret;
    }

    function GetKomponen($id) {
        $result = $this->Open($this->mSqlQueries['get_komponen'], array($id));
        //echo $this->getLastError();
        return $result;
    }

    function GetAutoGenerate($tanggal) {
        /*
          $formulaNoPengajuan = $this->Open($this->mSqlQueries['get_sql_formula_nomor_pengajuan'], array());
          $noPengajuan = $this->Open($formulaNoPengajuan['0']['formulaFormula'], array());
          $result = $noPengajuan[0]['number'];
         */
        $result = $this->mNumber->getNomorPengajuan($tanggal);
        return $result;
    }

    function DoAdd($data, $komponen) {
        $this->StartTrans();
        $ret = true;
        $userid = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
        $persen = $this->Open($this->mSqlQueries['get_persentase_rp_per_komponen'], array($data['kegiatandetail_id'], $data['kegiatandetail_id']));

        $arg = array(
            $data['kegiatandetail_id'],
            $data['nomor_pengajuan'],
            $data['nominal'],
            $data['keterangan'],
            $userid,
            $data['tanggal']
        );
        if (!$this->Execute($this->mSqlQueries['do_add'], $arg)) {
            $this->EndTrans(false);
            $ret = false;
        }

        $InsertID = $this->Insert_ID();
        foreach ($persen as $value) {
            $argdet = array(
                $InsertID,
                $value['rp_id'],
                ($data['nominal'] * $value['persen']) / 100,
                ($data['nominal'] * $value['persen']) / 100,
                $data['tanggal'],
                $userid
            );

            if (!$this->Execute($this->mSqlQueries['do_add_detail'], $argdet)) {
                $this->EndTrans(false);
                $ret = false;
            }
        }
        $this->EndTrans(true);
        return $ret;
    }

    function DoUpdate($data) {
        $ret = $this->Execute($this->mSqlQueries['do_update'], array(
            $data['kegiatandetail_id'],
            $data['nomor_pengajuan'],
            $data['nominal'],
            $data['keterangan'],
            $userid,
            $data['tanggal'],
            $data['id']
        ));

        return $ret;
    }

    function DoDelete($id) {
        $ret = $this->Execute($this->mSqlQueries['do_delete'], array($id));
        return $ret;
    }

    function date2string($date) {
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

    /**
     * untuk mendapatkan total sub unit
     * @since 3 Januari 2012
     */
    public function GetTotalSubUnitKerja($parentId) {
        $result = $this->Open($this->mSqlQueries['get_total_sub_unit_kerja'], array($parentId));
        return $result[0]['total'];
    }

    # untuk mendapatkan komponen anggaran yang sudah di buatkan realisasi pencairan

    public function GetKomponenAnggaranRealisasi($idRealisasi) {
        $return = $this->Open($this->mSqlQueries['get_komponen_anggaran_by_realisasi'], array($idRealisasi));

        return $return;
    }

    public function CheckTahunAnggaran($id) {
        $result = $this->Open($this->mSqlQueries['check_tahun_anggaran'], array($id));

        return $result[0];
    }

    public function GetTahunAnggaranAktif() {
        $result = $this->Open($this->mSqlQueries['get_tahun_anggaran_aktif'], array());
        return $result[0];
    }

    public function CheckPagu($mak) {
        $mak = implode(',', $mak);
        $result = $this->Open(
                $this->mSqlQueries['check_pagu_anggaran'], array(
            $mak
                )
        );

        return $result;
    }

    public function getDataPengajuanrealisasiDetail($id) {
        $return = $this->Open($this->mSqlQueries['get_pengajuan_realisasi_detail'], array(
            $id
        ));

        return self::ChangeKeyName($return);
    }

    /*
     * @param string $camelCasedWord Camel-cased word to be "underscorized"
     * @param string $case case type, uppercase, lowercase
     * @return string Underscore-syntaxed version of the $camelCasedWord
     */

    public static function humanize($camelCasedWord, $case = 'upper') {
        switch ($case) {
            case 'upper':
                $return = strtoupper(preg_replace('/(?<=\w)([A-Z])/', '_\1', $camelCasedWord));
                break;
            case 'lower':
                $return = strtolower(preg_replace('/(?<=\w)([A-Z])/', '_\1', $camelCasedWord));
                break;
            case 'title':
                $return = ucwords(preg_replace('/(?<=\w)([A-Z])/', '_\1', $camelCasedWord));
                break;
            case 'sentences':
                $return = ucfirst(preg_replace('/(?<=\w)([A-Z])/', '_\1', $camelCasedWord));
                break;
            default:
                $return = strtoupper(preg_replace('/(?<=\w)([A-Z])/', '_\1', $camelCasedWord));
                break;
        }
        return $return;
    }

    /*
     * @desc change key name from input data
     * @param array $input
     * @param string $case based on humanize method
     * @return array
     */

    public function ChangeKeyName($input = array(), $case = 'lower') {
        if (!is_array($input)) {
            return $input;
        }

        foreach ($input as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    $array[$key][self::humanize($k, $case)] = $v;
                }
            } else {
                $array[self::humanize($key, $case)] = $value;
            }
        }

        return (array) $array;
    }

    /**
     * @param string  path_info url to be parsed, default null
     * @return string parsed_url based on Dispatcher::Instance()->getQueryString();
     */
    public function _getQueryString($pathInfo = null) {
        $parseUrl = is_null($pathInfo) ? parse_url($_SERVER['QUERY_STRING']) : parse_url($pathInfo);
        $explodedUrl = explode('&', $parseUrl['path']);
        $requestData = '';
        foreach ($explodedUrl as $path) {
            if (preg_match('/^mod=[a-zA-Z0-9_-]+/', $path)) {
                continue;
            }
            if (preg_match('/^sub=[a-zA-Z0-9_-]+/', $path)) {
                continue;
            }
            if (preg_match('/^act=[a-zA-Z0-9_-]+/', $path)) {
                continue;
            }
            if (preg_match('/^typ=[a-zA-Z0-9_-]+/', $path)) {
                continue;
            }
            if (preg_match('/^ascomponent=[a-zA-Z0-9_-]+/', $path)) {
                continue;
            }
            if (preg_match('/uniqid=[a-zA-Z0-9_-]+/', $path)) {
                continue;
            }

            list($key, $value) = explode('=', $path);
            $requestData[$key] = Dispatcher::Instance()->Decrypt($value);
        }
        if (method_exists(Dispatcher::Instance(), 'getQueryString') === true) {
            $queryString = Dispatcher::Instance()->getQueryString($requestData);
        } else {
            foreach ($requestData as $key => $value) {
                $query[$key] = Dispatcher::Instance()->Encrypt($value);
            }
            $queryString = urldecode(http_build_query($query));
        }
        return $queryString;
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

    function _dateToIndo($date) {
        $indonesian_months = array(
            'N/A',
            'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'Nopember',
            'Desember'
        );

        if (preg_match("/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})/", $date, $patch)) {
            $year = (int) $patch[1];
            $month = (int) $patch[2];
            $month = $indonesian_months[(int) $patch[2]];
            $day = (int) $patch[3];
            $hour = (int) $patch[4];
            $min = (int) $patch[5];
            $sec = (int) $patch[6];

            $return = $day . ' ' . $month . ' ' . $year;
        } elseif (preg_match("/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/", $date, $patch)) {
            $year = (int) $patch[1];
            $month = (int) $patch[2];
            $month = $indonesian_months[$month];
            $day = (int) $patch[3];

            $return = $day . ' ' . $month . ' ' . $year;
        } else {
            $return = (int) $date;
        }
        return $return;
    }

}

?>