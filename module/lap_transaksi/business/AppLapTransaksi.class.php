<?php

class AppLapTransaksi extends Database {

    protected $mSqlFile = 'module/lap_transaksi/business/applaptransaksi.sql.php';

    function __construct($connectionNumber = 0) {
        parent::__construct($connectionNumber);
        //$this->mrDbEngine->debug = 1;
    }

    function GetCountData($tgl_awal, $tgl_akhir, $key, $tipeTransaksi) {

        $unitkerja_user = $this->GetUnitKerjaUser();
       
        if ($tipeTransaksi != 'all') {
            $flagTipe = 0;
        } else {
            $flagTipe = 1;
        }
        
        $result = $this->Open($this->mSqlQueries['get_count_data'], 
                array(
                        '%' . $key . '%',
                        '%' . $key . '%', 
                        $tgl_awal, $tgl_akhir, 
                        $tipeTransaksi, $flagTipe,
                        $unitkerja_user
                ));
        return $result[0]['transaksi_id'];
    }

    function GetData($offset, $limit, $tgl_awal, $tgl_akhir, $key, $tipeTransaksi) {
        $unitkerja_user = $this->GetUnitKerjaUser();
       
        if ($tipeTransaksi != 'all') {
            $flagTipe = 0;
        } else {
            $flagTipe = 1;
        }
        
        $result = $this->Open($this->mSqlQueries['get_data'], 
                    array(
                        '%' . $key . '%',
                        '%' . $key . '%', 
                        $tgl_awal, $tgl_akhir, 
                        $tipeTransaksi, $flagTipe,
                         $unitkerja_user,
                        $offset, 
                        $limit
                    ));
        #$debug = sprintf($this->mSqlQueries['get_data'], $sql_add ,$offset, $limit);
        #echo $sql; 
        return $result;
    }

    function GetTotalTransaksiNilai($tgl_awal, $tgl_akhir, $key, $tipeTransaksi) {
        $unitkerja_user = $this->GetUnitKerjaUser();
        if ($tipeTransaksi != 'all') {
            $flagTipe = 0;
        } else {
            $flagTipe = 1;
        }
                
        $result = $this->Open($this->mSqlQueries['get_total_transaksi_nilai'], 
                    array(
                        '%' . $key . '%',
                        '%' . $key . '%', 
                        $tgl_awal, $tgl_akhir, 
                        $tipeTransaksi, $flagTipe,
                         $unitkerja_user
                    ));
        return $result[0]['total'];
    }

    function GetDataCetak($tgl_awal, $tgl_akhir, $key, $tipeTransaksi) {
        $unitkerja_user = $this->GetUnitKerjaUser();
        if ($tipeTransaksi != 'all') {
            $flagTipe = 0;
        } else {
            $flagTipe = 1;
        }

        $result = $this->Open($this->mSqlQueries['get_data_cetak'],                    
                array(
                        '%' . $key . '%',
                        '%' . $key . '%', 
                        $tgl_awal, $tgl_akhir, 
                        $tipeTransaksi, $flagTipe,
                         $unitkerja_user
                    ));
        return $result;
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

    function GetUnitKerjaUser() {
        $userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
        $result = $this->open($this->mSqlQueries['get_unitkerja_for_idUser'], array($userId));
        return $result[0]['unit_kerja'];
    }

    function GetDataTipeTransaksi() {
        $result = $this->open($this->mSqlQueries['get_tipe_transaksi'], array());
        return $result;
    }

}

?>