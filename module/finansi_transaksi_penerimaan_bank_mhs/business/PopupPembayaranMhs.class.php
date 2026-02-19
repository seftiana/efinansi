<?php

/**
 * ================= doc ====================
 * FILENAME     : PopupPembayaranMhs.class.php
 * @package     : finansi_transaksi_penerimaan_bank_mhs
 * scope        : PUBLIC
 * @Author      : noor hadi
 * @Created     : 2015-04-24
 * @Modified    : 2015-04-24
 * @Analysts    : Dyah Fajar N
 * @copyright   : Copyright (c) 2016 Gamatechno
 * ================= doc ====================
 */
require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/rest/business/RestDb.class.php';

require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/user_unit_kerja/business/UserUnitKerja.class.php';

class PopupPembayaranMhs extends Database {

    protected $mSqlFile;
    public $_POST;
    public $_GET;
    private $appPembayaran = 520;
    private $appPembayaranPasca = 521;

    public function __construct($connectionNumber = 1) {
        $this->mSqlFile = 'module/finansi_transaksi_penerimaan_bank_mhs/business/popup_pembayaran_mhs.sql.php';
        $this->_POST = is_object($_POST) ? $_POST->AsArray() : $_POST;
        $this->_GET = is_object($_GET) ? $_GET->AsArray() : $_GET;
        parent::__construct($connectionNumber);
    }

    public function getTipe() {
        return array(            
            array('id' => 'pengakuan', 'name' => 'Pengakuan'),
            array('id' => 'pengakuan_depmasuk', 'name' => 'Pengakuan Deposit Masuk'),
            array('id' => 'piutang', 'name' => 'Piutang')
        );
    }

    public function getRangeYear() {
        $result = $this->Open($this->mSqlQueries['get_range_year'], array());
        if (empty($result)) {
            $getdate = getdate();
            $currYear = (int) $getdate['year'];
            $currMon = (int) $getdate['mon'];
            $currDay = (int) $getdate['mday'];
            $result = array(
                array(
                    'minYear' => $currYear,
                    'maxYear' => $currYear,
                    'tanggalAwal' => $currYear . '-' . $currMon . '-' . $currDay,
                    'tanggalAkhir' => $currYear . '-' . $currMon . '-' . $currDay
                )
            );
        }
        $return = $result;
        return self::ChangeKeyName($return[0]);
    }

    public function getTypeUnit() {
        $return = $this->Open($this->mSqlQueries['get_type_unit'], array());
        return $return;
    }

    public function Count() {
        $return = $this->Open($this->mSqlQueries['count'], array());

        if ($return) {
            return $return[0]['count'];
        } else {
            return 0;
        }
    }


    /**
     * @package GetDataPeriodePembayaran
     */
    public function getPeriodePembayaranReguler() {
        $module = 'services';
        $subModule = 'ReferensiPeriode';
        $action = $type = 'rest';
        // RestDb::Instance()->setDebugOn();
        RestDb::Instance()->setApplication($this->appPembayaran);
        RestDb::Instance()->setModule($module);
        RestDb::Instance()->setSubModule($subModule);
        RestDb::Instance()->setAction($action);
        RestDb::Instance()->setType($type);
        $return = RestDb::Instance()->SendNull('post');
        $result['status'] = $return['status'];
        $result['data_list'] = self::ChangeKeyName($return['data']);
        return $result;
    }    
    
    public function getPeriodePembayaran() {
        return $this->getPeriodePembayaranReguler();
    }

    public function getDataPembayaran($param = array(), $method = 'post') {
        $result = null;
        if ($param['tipe_pembayaran'] == 'pengakuan') {
            $result =  $this->getDataPembayaranPengakuan($param, $method);
        } elseif($param['tipe_pembayaran'] == 'piutang') {
            $result = $this->getDataPembayaranPiutang($param, $method);
        } else {
            $result = $this->getDataPembayaranDepositMasuk($param, $method);
        }

        return $result;
    }

    public function getDataPembayaranDepositMasuk($param = array(), $method = 'post'){
        $module = 'services';
        $subModule = 'TransaksiPengakuanDepositMasuk';
        $action = $type = 'rest';
        
        $url['module'] = $module;
        $url['sub_module'] = $subModule;
        $url['type'] = $type;
        $url['action'] = $action;
        
        $result['tipe_pembayaran'] = $param['tipe_pembayaran'];
        $resultReguler  = $this->getServiceDataPembayaran($param, $url, $method, $this->appPembayaran);
        //diubah ke 1 koneksi saja (reguler dan pasca sudah disamakan)
        $resultPasca    = null;//$this->getServiceDataPembayaran($param, $url, $method, $this->appPembayaranPasca);
        
        if(!empty($resultReguler['data_list']) && !empty($resultPasca['data_list'])) {
            $result['data_list'] = array_merge($resultReguler['data_list'],$resultPasca['data_list']);  
        } else {
            
            if(!empty($resultReguler['data_list'])) {
                $result['data_list'] = $resultReguler['data_list'];
            } elseif(!empty ($resultPasca['data_list'])) {                
                $result['data_list'] = $resultPasca['data_list'];
            } else {
                $result['data_list'] = array();
            }
        }
        
        $result['jbIds'] = null;
        return $result;
    }
    
    public function getDataPembayaranPiutang($param = array(), $method = 'post') {
        $module = 'services';
        $subModule = 'TransaksiPiutang';
        $action = $type = 'rest';
        
        $url['module'] = $module;
        $url['sub_module'] = $subModule;
        $url['type'] = $type;
        $url['action'] = $action;
        
        $result['tipe_pembayaran'] = $param['tipe_pembayaran'];
        $resultReguler  = $this->getServiceDataPembayaran($param, $url, $method, $this->appPembayaran);
        //diubah ke satu koneksi -> reguler dan pasca sudah disamanal
        $resultPasca    = null;//$this->getServiceDataPembayaran($param, $url, $method, $this->appPembayaranPasca);


        if(!empty($resultReguler['data_list']) && !empty($resultPasca['data_list'])) {
            $result['data_list'] = array_merge($resultReguler['data_list'],$resultPasca['data_list']);  
        } else {
            
            if(!empty($resultReguler['data_list'])) {
                $result['data_list'] = $resultReguler['data_list'];
            } elseif(!empty ($resultPasca['data_list'])) {                
                $result['data_list'] = $resultPasca['data_list'];
            } else {
                $result['data_list'] = array();
            }
        }
        
        $jbId = array();
        $piutangUrut = null;
        if (!empty($result['data_list'])) {
            $piutang = NULL;
            $piutangJumlah = NULL;
            $piutangProdiJumlah = NULL;
            //$piutangPotJumlah = NULL;
            foreach ($result['data_list'] as $value) {
                if (!in_array($value['id_jenis_biaya'], $jbId)) {
                    array_push($jbId, $value['id_jenis_biaya']);
                }
                                
                $value['potongan'] = 0 ;
                
                //jumlah piutang per prodi 
                $piutangProdiJumlah[(int) $value['prodi']]['nominal'] += $value['nominal'];
                
                //jumlah piutang per jenis biaya
                $piutangJumlah[(int) $value['prodi']][(int) $value['id_jenis_biaya']]['nominal'] += $value['nominal'];
                //$piutangPotJumlah[(int) $value['prodi']]['nominal_pot'] += $value['potongan'];
                
                $piutang[(int) $value['prodi']]['data_jb'][] = array(
                    'prodi' => (int) $value['prodi'],
                    'nama_prodi' => $value['nama_prodi'],
                    'jenis_biaya_id' => $value['id_jenis_biaya'],
                    'jenis_biaya' => $value['jenis_biaya'],
                    'nominal' => $value['nominal'],
                    'potongan' => 0,//$value['potongan'],
                    'penanggung_jawab' => $value['penanggung_jawab'],
                    'keterangan' => $value['keterangan'],
                    'id_detil' => $value['id_detil']
                );
            }

                $index = 0;
                foreach ($piutang as $value) {
                    if (is_array($value['data_jb']) && !empty($value['data_jb'])) {
                        foreach ($value['data_jb'] as $jb) {
                            $piutangUrut[$index]['prodi'] = (int) $jb['prodi'];
                            $piutangUrut[$index]['nama_prodi'] = (empty($jb['nama_prodi']) ? '-' : $jb['nama_prodi']);
                            $piutangUrut[$index]['jenis_biaya_id'] = $jb['jenis_biaya_id'];
                            $piutangUrut[$index]['jenis_biaya'] = $jb['jenis_biaya'];
                            $piutangUrut[$index]['nominal'] = (int) $jb['nominal'];
                            $piutangUrut[$index]['potongan'] = (int) $jb['potongan'];
                            $piutangUrut[$index]['total_prodi_nominal'] = $piutangJumlah[(int) $jb['prodi']]['nominal'];
                            $piutangUrut[$index]['total_nominal'] = $piutangJumlah[(int) $jb['prodi']][(int) $jb['jenis_biaya_id']]['nominal'];
                            //$piutangUrut[$index]['total_potongan'] = $piutangPotJumlah[(int) $jb['prodi']]['nominal_pot'];
                            $piutangUrut[$index]['penanggung_jawab'] =  $jb['penanggung_jawab'];
                            $piutangUrut[$index]['keterangan'] =  $jb['keterangan'];
                            $piutangUrut[$index]['id_detail'] =  $jb['id_detil'];
                            $piutangUrut[$index]['tipe'] = 'piutang';
                            $index++;
                        }
                    }
                }            
        }

        $result['jbIds'] = $jbId;
        $result['data_list'] = $piutangUrut;
        return $result;
    }

    /**
     * @package GetDataPembayaran dari applikasi pembayaran
     * @param  array  $param [tanggal_awal, tanggal_akhir]
     * @return Array  Data Pembayaran
     */
    public function getDataPembayaranPengakuan($param = array(), $method = 'post') {
        $module = 'services';
        $subModule = 'TransaksiPembayaranCoa';
        $action = $type = 'rest';
        
        $url['module'] = $module;
        $url['sub_module'] = $subModule;
        $url['type'] = $type;
        $url['action'] = $action;
        
        $result['tipe_pembayaran'] = $param['tipe_pembayaran'];
        $resultReguler  = $this->getServiceDataPembayaran($param, $url, $method, $this->appPembayaran);
        //diubah ke satu koneksi -> reguler dan pasca sudah disamanal
        $resultPasca    = null;//$this->getServiceDataPembayaran($param, $url, $method, $this->appPembayaranPasca);
  
        if(!empty($resultReguler['data_list']['pengakuan']) && !empty($resultPasca['data_list']['pengakuan'])) {
            $result['data_list']['pengakuan'] = array_merge($resultReguler['data_list']['pengakuan'],$resultPasca['data_list']['pengakuan']);
			
        } else {
            
            if(!empty($resultReguler['data_list']['pengakuan'])) {
                $result['data_list']['pengakuan'] = $resultReguler['data_list']['pengakuan'];
            } else if(!empty ($resultPasca['data_list']['pengakuan'])) {                
                $result['data_list']['pengakuan'] = $resultPasca['data_list']['pengakuan'];
            } else {
                $result['data_list'] = array();
            }
        }
         // echo'<pre>';
		// print_r($param);
		// print_r($resultReguler);
		// echo '</pre>';
  
		$jbId = array();
        $pengakuanUrut = null;

        if (!empty($result['data_list'])) { 
            
            if (!empty($result['data_list']['pengakuan'])) {
                $pengakuan = null;
                $pengakuanProdiJumlah = null;
                $pengakuanProdiPotJumlah  = null;
                $pengakuanProdiDepJumlah = null;
                $pengakuanJumlah = null;
                $pengakuanPotJumlah  = null;
                $pengakuanDepJumlah = null;
                
               

                $index = 0;

                foreach ($result['data_list']['pengakuan'] as $value) {
					array_push($jbId, $value['coa_id']);
					$pengakuanUrut[$index]['prodi'] = (int) $value['prodi'];
					// $pengakuanUrut[$index]['nama_prodi'] = (empty($value['nama_prodi']) ? '-' : $value['nama_prodi']);
					// $pengakuanUrut[$index]['jenis_biaya_id'] = $value['jenis_biaya_id'];
					// $pengakuanUrut[$index]['jenis_biaya'] = $value['jenis_biaya'];
					$pengakuanUrut[$index]['nominal'] = (int) $value['nominal'];
					$pengakuanUrut[$index]['potongan'] = (int) $value['potongan'];
					$pengakuanUrut[$index]['penggunaan_deposit'] = (int) $value['penggunaan_deposit'];
					$pengakuanUrut[$index]['nama_biaya'] = $value['nama_biaya'];

					$pengakuanUrut[$index]['id'] = $value['id'];
					$pengakuanUrut[$index]['coa_id'] = $value['coa_id'];
					$pengakuanUrut[$index]['tipe'] = 'pengakuan';
					$index++;
                  
               
                }

            }
        }
        $result['jbIds'] = $jbId;
        $result['data_list']['pengakuan'] = $pengakuanUrut;
        return $result;
    }

    public function doUpdateStatusPembayaran($param = array(), $status = 0) {
        $module = 'services';
        switch (strtolower($param['type'])) {
            case 'piutang':
                $subModule = 'FlagTransaksiPiutang';
                break;
            case 'pengakuan':
                $subModule = 'FlagTransaksiPengakuan';
                break;
            case 'pengakuan_depmasuk':
                $subModule = 'FlagTransaksiPengakuanDepositMasuk';
                break;
            default:
                $subModule = 'FlagTransaksiPengakuan';
                break;
        }
        $action = $type = 'rest';
        $param['isTransaksi'] = $status;
        // RestDb::Instance()->setDebugOn();
        RestDb::Instance()->setApplication($this->appPembayaran);
        RestDb::Instance()->setModule($module);
        RestDb::Instance()->setSubModule($subModule);
        RestDb::Instance()->setAction($action);
        RestDb::Instance()->setType($type);
        //var_dump($param);
        $return = RestDb::Instance()->Send((array) $param, 'post');
        return $return;
    }

    public function getData($offset, $limit, $param = array()) {
        $userId = null;
        if (class_exists('Security')) {
            $userId = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
        }
        $mUnitObj = new UserUnitKerja();
        $unitKerja = $mUnitObj->GetUnitKerjaRefUser($userId);
        $return = $this->Open($this->mSqlQueries['get_data'], array(
            $param['tipe'],
            (int) ($param['tipe'] == '' or strtolower($param['tipe']) == 'all'),
            $offset,
            $limit
        ));

        return $return;
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
 * integrasi ke pembayaran mahasiswa pasca
 */    
    
    /**
     * @package GetDataPeriodePembayaran
     */
    public function getPeriodePembayaranPasca() {
        $module = 'services';
        $subModule = 'ReferensiPeriode';
        $action = $type = 'rest';
        // RestDb::Instance()->setDebugOn();
        RestDb::Instance()->setApplication($this->appPembayaranPasca);
        RestDb::Instance()->setModule($module);
        RestDb::Instance()->setSubModule($subModule);
        RestDb::Instance()->setAction($action);
        RestDb::Instance()->setType($type);
        $return = RestDb::Instance()->SendNull('post');
        $result['status'] = $return['status'];
        $result['data_list'] = self::ChangeKeyName($return['data']);
        return $result;
    }

    public function getServiceDataPembayaran($param,$url,$method,$pembayaranId){
        $module     =  $url['module'];
        $subModule  =  $url['sub_module'];
        $type       =  $url['type'];
        $action     =  $url['action'];
        RestDb::Instance()->setApplication($pembayaranId);
        RestDb::Instance()->setModule($module);
        RestDb::Instance()->setSubModule($subModule);
        RestDb::Instance()->setAction($action);
        RestDb::Instance()->setType($type);
        $return = RestDb::Instance()->Send((array) $param, $method);

        $result['status'] = $return['status'];
        $result['data_list'] = self::ChangeKeyName($return['data']); 
        if(empty($result['data_list'])){
            $result['data_list'] = array();
        }
        return $result;
    }
/**
 * end
 */

}

?>