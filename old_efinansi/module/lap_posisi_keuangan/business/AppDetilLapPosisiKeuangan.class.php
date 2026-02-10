<?php

class AppKelpLaporan extends Database {

    protected $mSqlFile = 'module/lap_posisi_keuangan/business/appDetilLapPosisiKeuangan.sql.php';

    function __construct($connectionNumber = 0) {
        parent::__construct($connectionNumber);
        //$this->mrDbEngine->debug = 1;
    }

    function GetError() {
        $errno = mysql_errno();
        if ($errno == "1451") {
            $return = "Terdapat data lain yang menggunakan data ini.";
        }
        return $return;
    }

    function GetDataDetilKlpLaporan($tanggalAwal, $tanggal, $id, $offset, $limit) {
 
        $result = $this->Open($this->mSqlQueries['get_data_detil_klp_laporan'], 
                array($tanggalAwal, $tanggal,$tanggalAwal, $tanggal, $id, $offset, $limit));

        return $result;
    }

}

?>
