<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/user_unit_kerja/business/UserUnitKerja.class.php';

class AppLapBukubesar extends Database {

    private $mUnitObj;
    protected $mSqlFile;
    protected $mUserId = NULL;
    public $_POST;
    public $_GET;

    protected $mSaldoCollect = array();
    
    public function __construct($connectionNumber = 0) {

        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', '0');

        $this->mSqlFile = 'module/lap_bukubesar/business/applapbukubesar.sql.php';
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

    public function getSubAccountCombo(){
        return $this->Open($this->mSqlQueries['get_sub_account_combobox'],array());
    }

    private function getCoaId($tglAwal, $tglAkhir){
        $result = $this->Open($this->mSqlQueries['get_coa_id_by_tanggal'],array(
            $tglAwal,
            $tglAkhir
        ));
        $coaId = array("0");

        if(!empty($result)){
            foreach($result as $value){
                $coaId[] = $value['coa_id'];
            }
        }

        return $coaId;
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

        $userId = $this->getUserId();
        $unitKerja = $this->mUnitObj->GetUnitKerjaRefUser($userId);
        $unitId = $unitKerja['id'];
        $getSaldoBerjalanRef = $this->GetSaldoBerjalan($param['end_date']);
        $coaId = $this->getCoaId(
            date('Y-m-d', strtotime($param['start_date'])),
            date('Y-m-d', strtotime($param['end_date']))
        );

        $return = $this->Open(
                $this->mSqlQueries['get_data'] . $this->mSqlQueries['get_limit'], array(
            $getSaldoBerjalanRef,
            $unitId,
            $unitId,
            $unitId,
            date('Y-m-d', strtotime($param['start_date'])),
            date('Y-m-d', strtotime($param['end_date'])),
            $coaId,
            date('Y-m-d', strtotime($param['start_date'])),
            date('Y-m-d', strtotime($param['end_date'])),
            $param['coa_id'],
            (int) (($param['coa_id'] == '' OR $param['coa_id'] === NULL) OR strtolower($param['coa_id']) == 'all'),
            '%'.$param['sub_account'].'%',
            (int)($param['sub_account'] == '' || strtolower($param['sub_account']) == 'all' ),
            $param['page'],
            $param['limit']
        ));

        return self::ChangeKeyName($return);
    }

    public function getTotalSaldo($param = array()) {
        //$this->setDebugOn();
        //$this->setDebugOn();
        $userId = $this->getUserId();
        $unitKerja = $this->mUnitObj->GetUnitKerjaRefUser($userId);
        $unitId = $unitKerja['id'];
        $getSaldoBerjalanRef = $this->GetSaldoBerjalan($param['end_date']);
        $coaId = $this->getCoaId(
            date('Y-m-d', strtotime($param['start_date'])),
            date('Y-m-d', strtotime($param['end_date']))
        );
        $dataList = $this->Open(
                $this->mSqlQueries['get_total_saldo'], array(
            $getSaldoBerjalanRef,
            $unitId,
            $unitId,
            $unitId,
            date('Y-m-d', strtotime($param['start_date'])),
            date('Y-m-d', strtotime($param['end_date'])),
            $coaId,
            date('Y-m-d', strtotime($param['start_date'])),
            date('Y-m-d', strtotime($param['end_date'])),
            $param['coa_id'],
            (int) (($param['coa_id'] == '' OR $param['coa_id'] === NULL) OR strtolower($param['coa_id']) == 'all'),
            '%'.$param['sub_account'].'%',
            (int)($param['sub_account'] == '' || strtolower($param['sub_account']) == 'all' ),
        ));


        $kodeAkun = '';
        $items = array();
        $max = sizeof($dataList);
        $nk = 0;
        $saldo = 0;
        $saldoAkhir = 0;
        $dataSaldo = array();

        $totalSaldo = array() ;

        for ($k = 0; $k < $max;) {

            if ($kodeAkun == $dataList[$k]['akun_kode']) {
                switch ($dataList[$k]['kelompok_id']) {
                    case 1://aktiva
                    case 5://beban
                        $saldo = $dataList[$k]['debet'] - $dataList[$k]['kredit'];
                        break;
                    case 2://kewajiban
                    case 3://modal
                    case 4://pendapatan
                        $saldo = $dataList[$k]['kredit'] - $dataList[$k]['debet'];
                        break;
                    default ://default
                        $saldo = $dataList[$k]['debet'] - $dataList[$k]['kredit'];
                        break;
                }
                #$saldo += $dataList[$k]['debet'];
                #$saldo -= $dataList[$k]['kredit'];
                // if($dataList[$k]['id'] == '0') {
                //     $saldoAkhir += ($dataList[$k]['saldo_awal']);
                // } else {
                    $saldoAkhir += ($saldo);
                // }                
                $dataSaldo[$dataList[$k]['akun_kode']][$dataList[$k]['tanggal_transaksi']][$dataList[$k]['nomor_referensi']][$dataList[$k]['id']] = $saldoAkhir;
                $this->mSaldoCollect[$dataList[$k]['akun_kode']]['akun_kode'] = $dataList[$k]['akun_kode'];
                $this->mSaldoCollect[$dataList[$k]['akun_kode']]['akun_nama'] = $dataList[$k]['akun_nama'];
                $this->mSaldoCollect[$dataList[$k]['akun_kode']]['nominal'] = $saldoAkhir;
                $this->mSaldoCollect[$dataList[$k]['akun_kode']]['kelompok'] = $dataList[$k]['kelompok_id'];
                
                $k++;
            } elseif ($kodeAkun != $dataList[$k]['akun_kode']) {
                $kodeAkun = $dataList[$k]['akun_kode'];
                $saldo = 0;
                $saldoAkhir = $dataList[$k]['saldo_awal'];
            }
        }
        return $dataSaldo;
    }
    

	public function GetSaldoBerjalan($tgl) {
		$tglFilter = date('Y-m-d', strtotime($tgl));
		$tglAkhir = date('Y', strtotime($tgl)).'-12-31'; 
		if($tglAkhir === $tglFilter) {
			$result = $this->open($this->mSqlQueries['get_saldo_tahun_berjalan'],array($tgl,$tgl));
			return $result[0]['trans_ref'];
		} else {
			return '';
		}
		
	}

    public function getResumeTotalJumlahSaldo(){
       return $this->mSaldoCollect;
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

    public function GetBukuBesarHis($param = array()) { 
        // $this->SetDebugOn();       
        $userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
        $unitKerja = $this->mUnitObj->GetUnitKerjaRefUser($userId);
        $unitId = $unitKerja['id'];
        // fix laporan tidak ditemukan 
        // added 14/4/2022 get saldo berjalan ref (untuk laporan)
        $getSaldoBerjalanRef = $this->GetSaldoBerjalan($param['end_date']); // get saldo berjalan ref (untuk laporan)
        //
        $coaId = $this->getCoaId(
            date('Y-m-d', strtotime($param['start_date'])),
            date('Y-m-d', strtotime($param['end_date']))
        );

        $result = $this->open($this->mSqlQueries['get_data'], array(
            $getSaldoBerjalanRef, // // get saldo berjalan ref (untuk laporan)
            $unitId,
            $unitId,
            $unitId,
            date('Y-m-d', strtotime($param['start_date'])),
            date('Y-m-d', strtotime($param['end_date'])),
            $coaId,
            date('Y-m-d', strtotime($param['start_date'])),
            date('Y-m-d', strtotime($param['end_date'])),
            $param['coa_id'],
            (int) (($param['coa_id'] == '' OR $param['coa_id'] === NULL) OR strtolower($param['coa_id']) == 'all'),
            '%'.$param['sub_account'].'%',
            (int)($param['sub_account'] == '' || strtolower($param['sub_account']) == 'all' ),
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