<?php

/**
 * ================= doc ====================
 * FILENAME     : LaporanPosisiKeuangan.class.php
 * @package     : LaporanPosisiKeuangan
 * scope        : PUBLIC
 * @Author      : Eko Susilo
 * @Created     : 2015-02-26
 * @Modified    : 2015-02-26
 * @Analysts    : Dyah Fajar N
 * @copyright   : Copyright (c) 2012 Gamatechno
 * ================= doc ====================
 */
class LaporanPosisiKeuangan extends Database {
    # internal variables

    protected $mSqlFile;
    public $_POST;
    public $_GET;

    # Constructor

    function __construct($connectionNumber = 0) {
        $this->mSqlFile = 'module/lap_posisi_keuangan_sementara/business/laporan_posisi_keuangan.sql.php';
        $this->_POST = is_object($_POST) ? $_POST->AsArray() : $_POST;
        $this->_GET = is_object($_GET) ? $_GET->AsArray() : $_GET;
        parent::__construct($connectionNumber);
    }

    public function getRangeYear() {
        $return = $this->Open($this->mSqlQueries['get_range_year'], array());
        return self::ChangeKeyName($return[0]);
    }

    public function Count() {
        $return = $this->Open($this->mSqlQueries['get_data_laporan'], array());

        if ($return) {
            return $return[0]['count'];
        } else {
            return 0;
        }
    }

    public function getDataLaporan($param = array()) {
        $dataLaporan = array();
        $return = $this->Open($this->mSqlQueries['get_data_laporan'], array(
            date('Y-m-d', strtotime($param['start_date'])),
            date('Y-m-d', strtotime($param['end_date'])), 
            date('Y-m-d', strtotime($param['start_date'])),
            date('Y-m-d', strtotime($param['end_date']))
        ));
        
        if(!empty($return)){
            $saveAttayId = array();
            $index = 0;
            foreach ($return as $itemData) {
                //chageId kellap id menjadi index array
                if(!array_key_exists($itemData['kellap_id'], $saveAttayId)){
                    $saveAttayId[$itemData['kellap_id']] = $index;
                    $index++;
                }
                if(array_key_exists($itemData['kellap_id'], $saveAttayId)){
                    $dataLaporan[$saveAttayId[$itemData['kellap_id']]]['kellap_jns_id'] = $itemData['kellap_jns_id'];
                    $dataLaporan[$saveAttayId[$itemData['kellap_id']]]['kellap_jns_nama'] = $itemData['kellap_jns_nama'];
                    $dataLaporan[$saveAttayId[$itemData['kellap_id']]]['kellap_id'] = $itemData['kellap_id'];
                    $dataLaporan[$saveAttayId[$itemData['kellap_id']]]['kellap_nama'] = $itemData['kellap_nama'];
                    switch ($itemData['coa_kelompok_id']) {
                        case '2': /*pasiva*/
                        case '3':/*modal*/
                        case '4':/*pendapatan*/
                            $saldo = $itemData['nominal_kredit'] - $itemData['nominal_debet'];
                            break;
                        case '1': /*aktiva*/
                        case '5': /*beban*/
                        default :
                            $saldo = $itemData['nominal_debet'] - $itemData['nominal_kredit'];
                            break;
                    }
                    $dataLaporan[$saveAttayId[$itemData['kellap_id']]]['nominal'] += $saldo;                    
                }
            }
        }

        return $dataLaporan;
    }

    public function getDataDetail($param = array()) {$this->SetDebugOn();
        $return = $this->Open($this->mSqlQueries['get_detail_laporan'], array(
            date('Y-m-d', strtotime($param['start_date'])),
            date('Y-m-d', strtotime($param['end_date'])),           
            date('Y-m-d', strtotime($param['start_date'])),
            date('Y-m-d', strtotime($param['end_date'])),
             $param['id']
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

}

?>