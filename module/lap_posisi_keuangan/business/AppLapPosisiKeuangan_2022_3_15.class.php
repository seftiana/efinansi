<?php

class AppLapPosisiKeuangan extends Database {

    protected $mSqlFile = 'module/lap_posisi_keuangan/business/lapposisikeuangan.sql.php';

    function __construct($connectionNumber = 0) {
        parent::__construct($connectionNumber);
        //$this->mrDbEngine->debug = 1;
        $this->SetDebugOn();
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
            $addSql = "";
        }

        $newSql = sprintf($this->mSqlQueries['get_laporan_all'], '%s', '%s', '%s', '%s', $addSql);


        return $this->open($newSql, array($tglAwal, $tgl,$tglAwal, $tgl));
    }

    function GetLaporanAllDetil($tglAwal, $tgl) {
        if (GTFWConfiguration::GetValue('application', 'hide_zero_value')) {
            $addSql = " HAVING nilai > 0 ";
        } else {
            $addSql = "";
        }

        $newSql = sprintf($this->mSqlQueries['get_laporan_all_detil'], '%s', '%s', '%s', '%s', $addSql);


        return $this->open($newSql, array($tglAwal, $tgl,$tglAwal, $tgl));
    }
    
    function GetDataPenambahPerubModal($tgl) {
        $result = $this->open($this->mSqlQueries['get_penambah_perub_modal'], array($tgl));
        return $result;
    }

    function GetDataPengurangPerubModal($tgl) {
        $result = $this->open($this->mSqlQueries['get_pengurang_perub_modal'], array($tgl));
        return $result;
    }

    function GetLabaTahunLalu($tgl) {
        $userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
        $result = $this->open($this->mSqlQueries['get_laba_thn_sebelomnya'], array($tgl, $userId));
        return $result[0];
    }

    function GetSurplusTahunBerjalan($tgl) {
        $userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
        $result = $this->open($this->mSqlQueries['get_surplus_thn_berjalan'], array($tgl, $userId));
        return $result[0];
    }

}

?>