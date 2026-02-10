<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/user_unit_kerja/business/UserUnitKerja.class.php';

class AppLapBukubesar extends Database {

    private $mUnitObj;
    protected $mSqlFile;
    protected $mUserId = NULL;
    public $_POST;
    public $_GET;

    public function __construct($connectionNumber = 0) {
        $this->mSqlFile = 'module/lap_bukubesar_sementara/business/applapbukubesar.sql.php';
        $this->_POST = is_object($_POST) ? $_POST->AsArray() : $_POST;
        $this->_GET = is_object($_GET) ? $_GET->AsArray() : $_GET;
        $this->mUnitObj = new UserUnitKerja();
        parent::__construct($connectionNumber);
    }

    private function setUserId() {
        if (class_exists('Security')) {
            $this->mUserId = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
        }
    }

    public function getUserId() {
        $this->setUserId();

        return (int) $this->mUserId;
    }

    /**
     * @package Count
     * @description Count data sql SQL_CALC_FOUND_ROWS
     * @return INT COUNT
     */
    public function Count() {
        $return = $this->Open($this->mSqlQueries['count'], array());
        if ($return) {
            return $return[0]['count'];
        } else {
            return 0;
        }
    }

    /**
     * @package get data buku besar
     * @param Array $param
     * @param start_date, end_date, coa_id
     * @return Array $return; data buku besar
     */
    public function getData($param = array()) {
        //$this->setDebugOn();
        $userId = $this->getUserId();
        $unitKerja = $this->mUnitObj->GetUnitKerjaRefUser($userId);
        $unitId = $unitKerja['id'];
        $return = $this->Open($this->mSqlQueries['get_data_buku_besar_sementara'] . $this->mSqlQueries['get_limit'], array(           
           //$unitId,
           // $unitId,
           // $unitId,
            $param['coa_id'],
            (int) (($param['coa_id'] == '' OR $param['coa_id'] === NULL) OR strtolower($param['coa_id']) == 'all'),
            date('Y-m-d', strtotime($param['start_date'])),
            date('Y-m-d', strtotime($param['end_date'])),
            $param['coa_id'],
            (int) (($param['coa_id'] == '' OR $param['coa_id'] === NULL) OR strtolower($param['coa_id']) == 'all'),
            $param['page'],
            $param['limit']
        ));

        return self::ChangeKeyName($return);
    }

    public function getTotalSaldo($param = array()) {
        //$this->setDebugOn();
        $userId = $this->getUserId();
        $unitKerja = $this->mUnitObj->GetUnitKerjaRefUser($userId);
        $unitId = $unitKerja['id'];
        $dataList = $this->Open($this->mSqlQueries['get_data_buku_besar_sementara'], array(   
            //$unitId,
            //$unitId,
            //$unitId,
             $param['coa_id'],
            (int) (($param['coa_id'] == '' OR $param['coa_id'] === NULL) OR strtolower($param['coa_id']) == 'all'),           
            date('Y-m-d', strtotime($param['start_date'])),
            date('Y-m-d', strtotime($param['end_date'])),
            $param['coa_id'],
            (int) (($param['coa_id'] == '' OR $param['coa_id'] === NULL) OR strtolower($param['coa_id']) == 'all')
        ));


        $kodeAkun = '';
        $items = array();
        $max = sizeof($dataList);
        $nk = 0;
        $saldo = 0;
        $saldoAkhir = 0;
        $dataSaldo = array();
        $totalSaldo = 0;
        for ($k = 0; $k < $max;) {

            if ($kodeAkun == $dataList[$k]['akun_kode']) {

                $saldo += $dataList[$k]['debet'];
                $saldo -= $dataList[$k]['kredit'];

                $saldoAkhir = ($dataList[$k]['saldo_awal'] + $saldo);
                $dataSaldo[$dataList[$k]['akun_kode']][$dataList[$k]['tanggalJurnalEntri']][$dataList[$k]['nomorReferensi']][$dataList[$k]['id']] = $saldoAkhir;
                $totalSaldo += $saldoAkhir;
                $k++;
            } elseif ($kodeAkun != $dataList[$k]['akun_kode']) {
                $kodeAkun = $dataList[$k]['akun_kode'];
                $saldo = 0;
                $saldoAkhir = 0;
            }
        }
        //return self::ChangeKeyName($return);
        return array( 'data_saldo' => $dataSaldo,'total_saldo' => $totalSaldo);
    }

    public function GetRekeningCoa() {
        $result = $this->open($this->mSqlQueries['get_rekening_coa'], array());
        return $result;
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

    public function GetBukuBesarHis($rekening, $tgl_awal, $tgl_akhir) {
        //$this->SetDebugOn();
        $userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
        $unitKerja = $this->mUnitObj->GetUnitKerjaRefUser($userId);
        $unitId = $unitKerja['id'];
        $result = $this->open($this->mSqlQueries['get_data_buku_besar_sementara'], array( 
            //$unitId,
            //$unitId,
            //$unitId,            
            $rekening,
            (int) (($rekening == '' OR $rekening === NULL) OR strtolower($rekening) == 'all'),
            $tgl_awal,
            $tgl_akhir,
            $rekening,
            (int) (($rekening == '' OR $rekening === NULL) OR strtolower($rekening) == 'all')
        ));

        return self::ChangeKeyName($result);
    }

    public function GetInfoCoa($coa_id) {
        $result = $this->open($this->mSqlQueries['get_info_coa'], array($coa_id));
        return $result[0];
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

}

?>