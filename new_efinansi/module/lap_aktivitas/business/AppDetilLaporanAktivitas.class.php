<?php

class AppKelpLaporan extends Database {

    protected $mSqlFile = 'module/lap_aktivitas/business/appDetilLaporanAktivitas.sql.php';

    function __construct($connectionNumber = 0) {
        parent::__construct($connectionNumber);
        //$this->mrDbEngine->debug = 1;
    }

    function GetDataDetilKlpLaporan($tanggal_awal, $tanggal, $id, $offset, $limit) {
        $result = $this->Open(
                $this->mSqlQueries['get_data_detil_klp_laporan'], array(
            $tanggal_awal,
            $tanggal,
            $id)
        );

        return $result;
    }

}

?>
