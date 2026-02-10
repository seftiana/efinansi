<?php


class PopupKlpLaporan extends Database {

    public $_POST;
    public $_GET;
    
    private $_mDataKelompokLaporan;


    protected $mSqlFile = 'module/kelompok_laporan/business/popup_klp_laporan.sql.php';

    public function __construct($connectionNumber = 0) {
        parent::__construct($connectionNumber);           
        $this->_POST      = is_object($_POST) ? $_POST->AsArray() : $_POST;
        $this->_GET       = is_object($_GET) ? $_GET->AsArray() : $_GET;
    }

    public function Count() {
        $return = $this->Open($this->mSqlQueries['count'], array());

        if ($return) {
            return $return[0]['count'];
        } else {
            return 0;
        }
    }

    public function getData( $param = array()) {
        $this->_getKelompokLaporan($param);
    }
    
    public function getKlpRoot(){
        $return = $this->Open($this->mSqlQueries['get_kelompok_laporan_root'], array());
        return $return;
    }

    
    /**
     * get data kelompok laporan dan simpan dalam data array
     * Membuat array dari data hasil query
     * pastikan hasil query nya urut
     */
    private function _getKelompokLaporan($param = array()) {
        $dataArr = array();
        $data = $this->open($this->mSqlQueries['get_data'], array(                    
            $param['jns_lap'].'.%',
            $param['jns_lap']
        ));

        if (!empty($data)) {
            foreach ($data as $value) {
                $dataArr[$value['kellap_id']]['id'] = $value['kellap_id'];
                $dataArr[$value['kellap_id']]['pid'] = $value['kellap_pid'];
                $dataArr[$value['kellap_id']]['nama'] = $value['kellap_nama'];
                $dataArr[$value['kellap_id']]['level'] = $value['kellap_level'];
                $dataArr[$value['kellap_id']]['tipe'] = $value['kellap_tipe'];
                $dataArr[$value['kellap_id']]['is_tambah'] = $value['kellap_is_tambah'];
                $dataArr[$value['kellap_id']]['is_summary'] = $value['kellap_is_summary'];
                $dataArr[$value['kellap_id']]['no_urut'] = $value['kellap_order_by'];
            }
        }
        $this->_mDataKelompokLaporan = $dataArr;
    }
    

    
    /**
     * untuk melakukan pencarian di array multidimensi
     * @param Array $mdArrayData
     * @param String $key
     * @param Mixed $value (string lebih baik)
     * @return Array
     */
    private function _multidimensiSearch($mdArrayData = array(), $key = null, $value = '') {
        foreach ($mdArrayData as $k => $v) {
            if (array_key_exists($key, $v) && $v[$key] == $value) {
                $result[] = $mdArrayData[$k];
            }
        }
        
        if (!empty($result)) {
            $dataArr = array();
            foreach ($result as $value) {
                $dataArr[$value['id']]['id'] = $value['id'];
                $dataArr[$value['id']]['pid'] = $value['pid'];
                $dataArr[$value['id']]['nama'] = $value['nama'];
                $dataArr[$value['id']]['level'] = $value['level'];
                $dataArr[$value['id']]['tipe'] = $value['tipe'];  
                $dataArr[$value['id']]['is_tambah'] = $value['is_tambah'];
                $dataArr[$value['id']]['is_summary'] = $value['is_summary'];
                $dataArr[$value['id']]['no_urut'] = $value['no_urut'];
            }
            return $dataArr;
        } else {
            return null;
        }
    }

    /**
     * List kelompok laporan
     * untuk mengatur list laporan supaya urut parent dan child nya
     * rekursi
     * @param type $parentId
     * @return type
     */
    public function GetLaporan($parentId = 0,$deep = true) {
        $collecting = array();
        $data = $this->_mDataKelompokLaporan;
        //menemukan pid = $parentId
        $result = $this->_multidimensiSearch($data, 'pid', $parentId);

        if (!empty($result)) {
            foreach ($result as $k => $v) {                
                $collecting[$k]['id'] = $v['id'];
                $collecting[$k]['pid'] = $v['pid'];
                $collecting[$k]['nama'] = $v['nama'];
                $collecting[$k]['level'] = $v['level'];
                $collecting[$k]['tipe'] = $v['tipe'];
                $collecting[$k]['is_tambah'] = $v['is_tambah'];
                $collecting[$k]['is_summary'] = $v['is_summary'];
                $collecting[$k]['no_urut'] = $v['no_urut'];
                //getChild
                $getChild = $this->GetLaporan($v['id']);
                if(!empty($getChild)){
                    $collecting[$k]['is_child'] = '0';
                    if($deep == true) {
                        $collecting += $getChild;
                    }
                } else {
                    $collecting[$k]['is_child'] = '1';
                }
            }
        }
        
        return $collecting;
    }
}

?>