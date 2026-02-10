<?php

/**
  @ClassName : GenerateNumber
  @Copyright : PT Gamatechno Indonesia
  @Analyzed By : Nanang Ruswianto <nanang@gamatechno.com>
  @Author By : Dyan Galih <galih@gamatechno.com>
  @Version : 0.1
  @StartDate : 2009-12-29
  @LastUpdate : 2009-12-29
  @Description :
 */
class GenerateNumber extends Database {

    protected $mSqlFile;
    //kode untuk transaksi
    private $_mKodeBP = '5'; //Pengeluaran bank
    private $_mKodeBR = '0'; //Penerimaan bank
    private $_mKodeCP = '7'; //Pengeluaran kas
    private $_mKodeCR = '6'; //Penerimaan kas

    public function __construct($connectionNumber = 0) {
        $this->mSqlFile = 'module/generate_number/business/generate_number.sql.php';
        parent::__construct($connectionNumber);
    }

    /**
     * GetGenerateNumber
     * Fungsi ini untuk men-generate number sesuai dengan formula dalam tabel finansi_ref_formula
     * @param String $code formula kode
     * @param Array $params parameter yang digunakan sebagai masukan untuk query dalam formula
     * @access public
     */
    public function GetGenerateNumber($code, $params = array()) {
        $result = $this->Open($this->mSqlQueries['get_sql_generate_number'], array($code));
        $result = $this->open($result['0']['formatNumberFormula'], $params);
        return $result['0']['number'];
    }

    /**
     * GetNoBuktiTransaksi
     * Fungsi ini untuk men-generate no bukti transaksi berdasarkan transReferensi
     * @param Number $tipeTransaksi Tipe transaksi misal : 1,5 => BKM,2,4=>BKK,3=>BM, 100=>SPJ dll
     * @param Number $unitKerjaId unitkerjaID
     * @param Number $bulan nomor bulan misal : jan => 01, Feb => 02 dst
     * @param Number $tahun tahun misal : 2012,2013,2014 dst
     * @since januari 2012
     * @access public
     */
    public function GetNoBuktiTransaksi($tipeTransaksi, $unitKerjaId, $bulan = '', $tahun = '') {

        switch ($tipeTransaksi) {
            case "1":
            case "5":
                $identifier = "BKM";
                break;
            case "2":
            case "4":
                $identifier = "BKK";
                break;
            case "3":
                $identifier = "BM";
                break;
            case "100":
                $identifier = "SPJ";
                break;
            default:
                $identifier = "Bxx";
                break;
        }

        $bulan = empty($bulan) ? date('m') : $bulan;
        $tahun = empty($tahun) ? date('Y') : $tahun;
        $params = array(
            $identifier,
            $identifier,
            $identifier,
            $bulan,
            $tahun,
            $identifier,
            $bulan,
            $tahun,
            //$bulan,
            //$tahun,
            $identifier,
            '%',
            $bulan,
            $tahun,
            $unitKerjaId
        );
        return $this->GetGenerateNumber('NO_TRANS_GENERATOR', $params);
    }

    public function getTransReferenceCR($tanggal) {
        $result = $this->Open($this->mSqlQueries['get_trans_reference_cr'], array(
            $tanggal,
            $tanggal,
            $this->_mKodeCR,
            $tanggal,
            $tanggal,
            $this->_mKodeCR,
            '%',
            $tanggal,
            $tanggal
        ));
        return $result['0']['nomorCr'];
    }

    public function getTransReferenceBP($tanggal) {
        $result = $this->Open($this->mSqlQueries['get_trans_reference_bp'], array(
            $tanggal,
            $tanggal,
            $this->_mKodeBP,
            $tanggal,
            $tanggal,
            $this->_mKodeBP,
            '%',
            $tanggal,
            $tanggal
        ));
        return $result['0']['nomorBp'];
    }

    public function getTransReferenceBR($tanggal) {
        $result = $this->Open($this->mSqlQueries['get_trans_reference_br'], array(
            $tanggal,
            $tanggal,
            $this->_mKodeBR,
            $tanggal,
            $tanggal,
            $this->_mKodeBR,
            '%',
            $tanggal,
            $tanggal
        ));
        return $result['0']['nomorBr'];
    }

    public function getTransReferenceCP($tanggal) {
        $result = $this->Open($this->mSqlQueries['get_trans_reference_cp'], array(
            $tanggal,
            $tanggal,
            $this->_mKodeCP,
            $tanggal,
            $tanggal,
            $this->_mKodeCP,
            '%',
            $tanggal,
            $tanggal
        ));
        return $result['0']['nomorCp'];
    }

    public function getTransReferenceCPKasKecil($tanggal) {
        $result = $this->Open($this->mSqlQueries['get_trans_reference_cp_kas_kecil'], array(
            $tanggal,
            $tanggal,
            $tanggal,
            $tanggal,
            '%',
            $tanggal,
            $tanggal,
            $tanggal,
            $tanggal
        ));
        return $result['0']['nomorCpKasKecil'];
    }
    
    public function getTransReference($tipe, $unitId, $date = null, $identifier = NULL) {
        $getdate = getdate();
        $currYear = (int) $getdate['year'];
        $currMon = (int) $getdate['mon'];
        $currDay = (int) $getdate['mday'];

        $parseDate = date_parse($date);
        if (!empty($parseDate['errors'])) {
            $tanggal = date('Y-m-d', mktime(0, 0, 0, $currMon, $currDay, $currYear));
        } else {
            $tanggal = date('Y-m-d', strtotime($date));
        }

        if (is_null($identifier)) {
            switch ((int) $tipe) {
                case 1:
                    $identifier = 'BKM';
                    break;
                case 2:
                    $identifier = 'BKK';
                    break;
                case 3:
                    $identifier = 'BKK';
                    break;
                case 4:
                    $identifier = 'BKK';
                    break;
                case 5:
                    $identifier = 'BKM';
                    break;
                case 6:
                    $identifier = 'BKK';
                    break;
                case 10:
                    $identifier = 'BKM';
                    break;
                default:
                    $identifier = NULL;
                    break;
            }
        }
        $this->Execute($this->mSqlQueries['set_generate_number'], array());
        $query = $this->Open($this->mSqlQueries['get_sql_generate_number'], array(
            'DO_SET_TRANS_REFERENCES'
        ));
        $msqlQuery = $query[0]['formatNumberFormula'];
        $params = array(
            strtoupper($identifier),
            strtoupper($identifier),
            $tanggal,
            $tanggal,
            strtoupper($identifier),
            $tanggal,
            $tanggal,
            $unitId
        );
        if (is_null($msqlQuery)) {
            $execute = $this->Execute($this->mSqlQueries['do_set_query'], (array) $params);
        } else {
            $execute = $this->Execute($msqlQuery, (array) $params);
        }

        $return = $this->Open($this->mSqlQueries['get_generated_number'], array());
        $generatedNumber = $return[0]['GENERATED_NUMBER'];

        return $generatedNumber;
    }

    // generate number dari Bank
    public function getNomorBpBank($tanggal) {
        
        $result = $this->Open($this->mSqlQueries['get_nomor_bp_bank'], array(
            $tanggal,
            $tanggal,
            $this->_mKodeBP,
            $tanggal,
            $tanggal,
            $this->_mKodeBP,
            '%',
            $tanggal,
            $tanggal
        ));
        return $result[0]['nomorBp'];
    }

    public function getNomorCRBank($tanggal) {
        $result = $this->Open($this->mSqlQueries['get_nomor_cr_bank'], array(
            $tanggal,
            $tanggal,
            $this->_mKodeCR,
            $tanggal,
            $tanggal,
            $this->_mKodeCR,
            '%',
            $tanggal,
            $tanggal
        ));
    }
        
    //end
    
    // // generate number dari sppu
    // cr dan bp        
    public function getNomorCR($tanggal) {
        $result = $this->Open($this->mSqlQueries['get_nomor_cr'], array(
            $tanggal,
            $tanggal,
            $this->_mKodeCR,
            $tanggal,
            $tanggal,
            $this->_mKodeCR,
            '%',
            $tanggal,
            $tanggal
        ));
        return $result[0]['nomorCr'];
    }

    public function cekNomorBpSppu($tanggal) {
        $result = $this->Open($this->mSqlQueries['cek_nomor_bp_sppu'], array(
            $tanggal,
            $tanggal,
            $this->_mKodeBP,
            '%',
            $tanggal,
            $tanggal
        ));
        return $result[0]['nomorBp'];
    }

    public function cekNomorBpBank($tanggal) {
        $result = $this->Open($this->mSqlQueries['cek_nomor_bp_bank'], array(
            $tanggal,
            $tanggal,
            $this->_mKodeBP,
            '%',
            $tanggal,
            $tanggal
        ));
        return $result[0]['nomorBp'];
    }
    
    public function getNomorBp($tanggal) {
        $result = $this->Open($this->mSqlQueries['get_nomor_bp'], array(
            $tanggal,
            $tanggal,
            $this->_mKodeBP,
            $tanggal,
            $tanggal,
            $this->_mKodeBP,
            '%',
            $tanggal,
            $tanggal
        ));
        return $result[0]['nomorBp'];
    }
    
    /**
     * getNomorPengajuan
     * @description untuk menggerate nomor FPA
     * @param date $tanggal
     * @return string
     */
    public function getNomorPengajuan($tanggal) {
        $result = $this->Open($this->mSqlQueries['get_nomor_pengajuan'], array(
            $tanggal,
            $tanggal,
            '%%',
            $tanggal,
            $tanggal
        ));
        return $result[0]['nomorPengajuan'];
    }

}

?>