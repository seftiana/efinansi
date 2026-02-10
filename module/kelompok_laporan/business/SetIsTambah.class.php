<?php

/**
 * 
 * 
 * @modified by noor hadi <noor.hadi@gamatechno.com>
 * menambahkan fungsi set is tambah
 * 
 * 
 * @copyright (c) 2011 - 2017, Gamatechno Indonesia
 */


class SetIsTambah extends Database {

    protected $mSqlFile;
    public $_POST;
    public $_GET;
    public $method;

    function __construct($connectionNumber = 0) {
        $this->mSqlFile = 'module/kelompok_laporan/business/set_is_tambah.sql.php';
        $this->_POST = is_object($_POST) ? $_POST->AsArray() : $_POST;
        $this->_GET = is_object($_GET) ? $_GET->AsArray() : $_GET;
        $this->method = strtoupper($_SERVER['REQUEST_METHOD']);
        parent::__construct($connectionNumber);
    }

    /**
     * 
     * added since 1 Agustus 2017
     * @author noor hadi<noor.hadi@gamatechno.com>
     * 
     */
    
    private function _getKodeSistem($id) {
        $result = $this->Open($this->mSqlQueries['get_kode_sistem'], array($id));
        if (!$result) {
            return null ;
        } else {
            return $result[0]['kode_sistem'];
        }
    }
    
    public function setIsTambah($id) {
        $result = true;
        $this->StartTrans();
        if (!$id) {
            $result &= false;
        }
        
        $result &= $this->Execute($this->mSqlQueries['do_set_is_tambah'], array('Y',$id));
        $getKodeSistem = $this->_getKodeSistem($id);
        $result &= $this->Execute($this->mSqlQueries['do_set_is_tambah_with_child'], array(
            'Y',
            $getKodeSistem.'.%'
            )
        );
        
        return $this->EndTrans($result);
    }
    
    public function unsetIsTambah($id) {
        $result = true;
        $this->StartTrans();
        if (!$id) {
            $result &= false;
        }
        
        $result &= $this->Execute($this->mSqlQueries['do_set_is_tambah'], array('T',$id));    
        $getKodeSistem = $this->_getKodeSistem($id);
        $result &= $this->Execute($this->mSqlQueries['do_set_is_tambah_with_child'], array(
            'T',
            $getKodeSistem.'.%'
            )
        );
        
        return $this->EndTrans($result);
    }
    
    public function getLaporanNama($id){
        $result = $this->Open($this->mSqlQueries['get_laporan_nama'], array($id));
        if (!$result) {
            return '-';
        } else {
            return $result[0]['nama'];
        }        
    }

    /**
     * 
     */
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

    public function getModule($pathInfo = null) {
        $module = NULL;
        if (is_null($pathInfo)) {
            $parseUrl = parse_url($_SERVER['QUERY_STRING']);
            $explodedUrl = explode('&', $parseUrl['path']);
        } else {
            $parseUrl = parse_url($pathInfo);
            $explodedUrl = explode('&', $parseUrl['query']);
        }

        foreach ($explodedUrl as $path) {
            if (preg_match('/^mod=[a-zA-Z0-9_-]+/', $path, $matches)) {
                list($key, $value) = explode('=', $matches[0]);
                $module = $value;
            }
        }

        return $module;
    }

    public function getSubModule($pathInfo = null) {
        $subModule = NULL;
        if (is_null($pathInfo)) {
            $parseUrl = parse_url($_SERVER['QUERY_STRING']);
            $explodedUrl = explode('&', $parseUrl['path']);
        } else {
            $parseUrl = parse_url($pathInfo);
            $explodedUrl = explode('&', $parseUrl['query']);
        }

        foreach ($explodedUrl as $path) {
            if (preg_match('/^sub=[a-zA-Z0-9_-]+/', $path, $matches)) {
                list($key, $value) = explode('=', $matches[0]);
                $subModule = $value;
            }
        }

        return $subModule;
    }

    public function getAction($pathInfo = null) {
        $action = NULL;
        if (is_null($pathInfo)) {
            $parseUrl = parse_url($_SERVER['QUERY_STRING']);
            $explodedUrl = explode('&', $parseUrl['path']);
        } else {
            $parseUrl = parse_url($pathInfo);
            $explodedUrl = explode('&', $parseUrl['query']);
        }

        foreach ($explodedUrl as $path) {
            if (preg_match('/^act=[a-zA-Z0-9_-]+/', $path, $matches)) {
                list($key, $value) = explode('=', $matches[0]);
                $action = $value;
            }
        }

        return $action;
    }

    public function getType($pathInfo = null) {
        $type = NULL;
        if (is_null($pathInfo)) {
            $parseUrl = parse_url($_SERVER['QUERY_STRING']);
            $explodedUrl = explode('&', $parseUrl['path']);
        } else {
            $parseUrl = parse_url($pathInfo);
            $explodedUrl = explode('&', $parseUrl['query']);
        }

        foreach ($explodedUrl as $path) {
            if (preg_match('/^typ=[a-zA-Z0-9_-]+/', $path, $matches)) {
                list($key, $value) = explode('=', $matches[0]);
                $type = $value;
            }
        }

        return $type;
    }

}

?>