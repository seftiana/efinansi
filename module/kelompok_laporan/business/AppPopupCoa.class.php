<?php


class AppPopupCoa extends Database {

    public $_POST;
    public $_GET;
    
    protected $mSqlFile = 'module/kelompok_laporan/business/apppopupcoa.sql.php';

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

    public function getDataCoa($offset, $limit, $param = array()) {
        $return = $this->Open($this->mSqlQueries['get_data_coa'], array(
            '%' . $param['kode'] . '%',
            '%' . $param['nama'] . '%',
            $offset,
            $limit
        ));

        return $return;
    }

}

?>