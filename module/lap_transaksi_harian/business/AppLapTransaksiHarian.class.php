<?php

class AppLapTransaksiHarian extends Database {

    protected $mSqlFile = 'module/lap_transaksi_harian/business/applaptransaksiharian.sql.php';
    protected $mSaldoAwal = array();
    protected $mSaldoAwalBerjalan = array();
    protected $mSaldoBerjalan = array();

    public function __construct($connectionNumber = 0) {
        parent::__construct($connectionNumber);
    }

    public function GetCountData() {
        $result = $this->Open($this->mSqlQueries['get_count_data'], array());
        return $result[0]['total'];
    }

    public function GetData($offset, $limit, $tgl_awal, $tgl, $jenis_transaksi = '') {
        //$this->SetDebugOn();
        if ($jenis_transaksi == '' || $jenis_transaksi == 'all') {
            $flag_jt = 1;
        } else {
            $flag_jt = 0;
        }

        $result = $this->Open(
                $this->mSqlQueries['get_data'], array(
            $tgl_awal,
            $tgl,
            $jenis_transaksi,
            $flag_jt,
            $offset,
            $limit
                )
        );

        return $result;
    }

    public function GetSaldoTransaksi($coa_id, $tgl, $jenis_transaksi = '') {
        if ($jenis_transaksi == '' || $jenis_transaksi == 'all') {
            $flag_jt = 1;
        } else {
            $flag_jt = 0;
        }

        $result = $this->Open(
                $this->mSqlQueries['get_saldo_transaksi'], array(
            $coa_id,
            $tgl,
            $jenis_transaksi,
            $flag_jt
                )
        );
        return $result[0];
    }

    public function GetTotalDebetKredit($tgl_awal, $tgl, $jenis_transaksi = '') {
        if ($jenis_transaksi == '' || $jenis_transaksi == 'all') {
            $flag_jt = 1;
        } else {
            $flag_jt = 0;
        }
        $result = $this->Open(
                $this->mSqlQueries['get_total_debet_kredit'], array(
            $tgl_awal,
            $tgl,
            $jenis_transaksi,
            $flag_jt
                )
        );
        return $result[0];
    }

    public function GetDataCetak($tgl_awal, $tgl, $jenis_transaksi = '') {
        if ($jenis_transaksi == '' || $jenis_transaksi == 'all') {
            $flag_jt = 1;
        } else {
            $flag_jt = 0;
        }

        $result = $this->Open(
                $this->mSqlQueries['get_data_cetak'], array(
            $tgl_awal,
            $tgl,
            $jenis_transaksi,
            $flag_jt
                )
        );

        return $result;
    }

    public function GetMinMaxThnTrans() {
        $ret = $this->open($this->mSqlQueries['get_minmax_tahun_transaksi'], array($start, $count));

        if ($ret)
            return $ret[0];
        else {
            $now_thn = date('Y');
            $thn['minTahun'] = $now_thn - 5;
            $thn['maxTahun'] = $now_thn + 5;
            return $thn;
        }
    }

    public function GetJenisTransaksi() {
        $result = $this->Open($this->mSqlQueries['get_jenis_transaksi'], array());
        return $result;
    }

    public function GetAkunTransaksi() {
        $result = $this->Open($this->mSqlQueries['get_akun_transaksi'], array());
        return $result;
    }

    //prepare data saldo 
    private function _getTanggalPembukuanAktif() {
        $return = $this->Open($this->mSqlQueries['get_tanggal_periode_pembukuan_aktif'], array());
        if (!empty($return)) {
            $tanggal = $return[0]['tanggal_awal'];
        } else {
            $tanggal = date('Y-m-d');
        }

        return $tanggal;
    }

    public function prepareDataSaldoAwal($tanggalAwalBulan, $tanggalAkhirBulan,$jenisTransaksi) {
        $tanggalAwalTahun = $this->_getTanggalPembukuanAktif();
        $saldoAwalTahun = $this->Open($this->mSqlQueries['get_saldo_awal'], array(
            $tanggalAwalBulan, $tanggalAwalBulan
        ));

        $saldoAwalBulanBerjalan = $this->Open($this->mSqlQueries['get_saldo_awal_berjalan'], array(
            $tanggalAwalTahun, $tanggalAwalBulan,
            $jenisTransaksi, ((empty($jenisTransaksi) || $jenisTransaksi == 'all') ? 1 : 0)
        ));

        $saldoBerjalan = $this->Open($this->mSqlQueries['get_saldo_berjalan'], array(
            $tanggalAwalBulan, $tanggalAkhirBulan,
            $jenisTransaksi, ((empty($jenisTransaksi) || $jenisTransaksi == 'all') ? 1 : 0)
        ));

        //membuat array untuk saldo awal
        if (!empty($saldoAwalTahun)) {
            foreach ($saldoAwalTahun as $itemSaldo) {
                $this->mSaldoAwal[$itemSaldo['coa_id']] = $itemSaldo['saldo_awal'];
            }
        }

        //membuat array untuk saldo awal berjalan
        if (!empty($saldoAwalBulanBerjalan)) {
            foreach ($saldoAwalBulanBerjalan as $itemSaldoBerjalan) {
                $this->mSaldoAwalBerjalan[$itemSaldoBerjalan['coa_id']][$itemSaldoBerjalan['dk']] = $itemSaldoBerjalan['nominal'];
            }
        }
        //end
        //membuat array untuk saldo berjalan
        if (!empty($saldoBerjalan)) {
            foreach ($saldoBerjalan as $itemSaldo) {
                $this->mSaldoBerjalan[$itemSaldo['coa_id']][$itemSaldo['dk']] = $itemSaldo['nominal'];
            }
        }
        //end
    }

    private function _getSaldoAwal($coaId) {
        if (array_key_exists($coaId, $this->mSaldoAwal)) {
            return $this->mSaldoAwal[$coaId];
        } else {
            return 0;
        }
    }

    private function _getSaldoAwalBerjalan($coaId) {
        if (array_key_exists($coaId, $this->mSaldoAwalBerjalan)) {
            return $this->mSaldoAwalBerjalan[$coaId];
        } else {
            return null;
        }
    }

    private function _getSaldoBerjalan($coaId) {
        if (array_key_exists($coaId, $this->mSaldoBerjalan)) {
            return $this->mSaldoBerjalan[$coaId];
        } else {
            return null;
        }
    }

    public function getSaldoDebet($coaId) {
        $saldoBerjalan = $this->_getSaldoBerjalan($coaId);
        if (!empty($saldoBerjalan)) {
            return (array_key_exists('D', $saldoBerjalan) ? $saldoBerjalan['D'] : 0);
        } else {
            return 0;
        }
    }

    public function getSaldoKredit($coaId) {
        $saldoBerjalan = $this->_getSaldoBerjalan($coaId);
        if (!empty($saldoBerjalan)) {
            return (array_key_exists('K', $saldoBerjalan) ? $saldoBerjalan['K'] : 0);
        } else {
            return 0;
        }
    }

    /**
     * untuk menghitung saldo bulan terfilter (sekarang0
     * @param type $coaId
     * @param type $kelompokCoaId
     */
    public function getSaldoAkunBulanBerjalan($coaId, $kelompokCoaId) {
        $saldoDebet = $this->getSaldoDebet($coaId);
        $saldoKredit = $this->getSaldoKredit($coaId);
        $saldo = $this->_hitungSaldo($kelompokCoaId, $saldoDebet, $saldoKredit);
        return $saldo;
    }

    /**
     * untuk menghitung saldo awal dari bulan sebelum nya
     * @param type $coaId
     * @param type $kelompokCoaId
     * @return type
     */
    public function getSaldoAwalAkunBulanLalu($coaId, $kelompokCoaId) {
        $saldo = 0;
        //get saldo awal
        $jumlahSaldoAwal = $this->_getSaldoAwal($coaId);

        //get saldo berjalan
        $saldoAwalBerjalan = $this->_getSaldoAwalBerjalan($coaId);
        if (!empty($saldoAwalBerjalan)) {
            $saldoBerjalanDebet = (array_key_exists('D', $saldoAwalBerjalan) ? $saldoAwalBerjalan['D'] : 0);
            $saldoBerjalanKredit = (array_key_exists('K', $saldoAwalBerjalan) ? $saldoAwalBerjalan['K'] : 0);
            $saldo = $this->_hitungSaldo($kelompokCoaId, $saldoBerjalanDebet, $saldoBerjalanKredit);            
        }
        return ($jumlahSaldoAwal + $saldo);
    }

    /**
     * untuk debug saja
     */
    public function getAllSaldoAwal() {
        return $this->mSaldoAwal;
    }

    public function getAllSaldoAwalBerjalan() {
        return $this->mSaldoAwalBerjalan;
    }

    /**
     * end
     */
    //end

    /**
     * _hitungSaldo
     * untuk menghitung saldo berdasarkan kelompok coa
     * @param type $kelompokCoaId kelompok coa
     * @param type $debet nominal debet
     * @param type $kredit nominal kredit
     * @return number
     */
    private function _hitungSaldo($kelompokCoaId,$debet,$kredit) {
        $saldo = 0;
        switch ($kelompokCoaId) {
            case 1://aktiva
            case 5://beban
                $saldo = $debet - $kredit;
                break;
            case 2://kewajiban
            case 3://modal
            case 4://pendapatan
                $saldo = $kredit - $debet;
                break;
            default ://default
                $saldo = $debet - $kredit;
                break;
        }
        
        return $saldo;
    }

}

?>