<?php

class AppLapAktivitas extends Database {

    protected $mSqlFile = 'module/lap_aktivitas/business/lapaktifitas.sql.php';

    function __construct($connectionNumber = 0) {
        parent::__construct($connectionNumber);
        //$this->mrDbEngine->debug = 1;
    }

    function GetMinMaxThnTrans() {
        $ret = $this->open($this->mSqlQueries['get_minmax_tahun_transaksi'], array($start, $count));
        if ($ret) {
            return $ret[0];
        } else {
            $now_thn = date('Y');
            $thn['minTahun'] = $now_thn - 5;
            $thn['maxTahun'] = $now_thn + 5;
            return $thn;
        }
    }

    function GetLaporanAll($tglAwal, $tgl) {

        if (GTFWConfiguration::GetValue('application', 'hide_zero_value')) {
            $addSql = " HAVING nilai > 0 ";
        } else {
            $addSql = " ";
        }

        $newSql = sprintf($this->mSqlQueries['get_laporan_all'], '%s', '%s', $addSql);

        $result = $this->open($newSql, array($tglAwal, $tgl));
        return $result;
    }

    function GetLaporanAllDetil($tglAwal, $tgl) {

        if (GTFWConfiguration::GetValue('application', 'hide_zero_value')) {
            $addSql = " HAVING nilai > 0 ";
        } else {
            $addSql = " ";
        }

        $newSql = sprintf($this->mSqlQueries['get_laporan_all_detil'], '%s', '%s', $addSql);

        $result = $this->open($newSql, array($tglAwal, $tgl));
        return $result;
    }

}

?>