<?php

/**
 * Class DataJurnal
 * 
 * untuk menyiapkan koleksi data jurnal yang belum diposting
 * pecahaan prosess dari class AppPosting
 * 
 * @since 4 Mei 2017
 * @package posting
 * @analyzed dyah fajar n <dyah@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatecno.com>
 * 
 * 
 * @copyright (c) 2011 - 2017, Gamatechno Indonesia
 * 
 */

class DataJurnal extends Database {

    protected $mSqlFile;
    public $method;
    private $_mPembukuanRefIds;
    private $_mBukuBesar = array();
    private $_mBukuBesarHis = array();
    private $_mBukuBesarHisLabaRugi = array();

    public function __construct($connectionNumber = 0) {
        $this->mSqlFile = 'module/finansi_laporan_builder_sementara/business/data_jurnal.sql.php';
        parent::__construct($connectionNumber);
    }
    
    //prepare data jurnal untuk di posting
    /* 
     * 
     * cara menggunakan method ini :
     * 1. Definisikan class Data Jurnal
     * 2. panggil method prepareDataJurnal($date,$date)
     *    untuk menyiapkan data jurnal berupa array
     * 3. panggil method getDataBukuBesar() 
     *    untuk mendapatkan data Jurnal yang siap di posting ke buku besar
     * 4. panggil method getDataBukuBesarHis() 
     *    untuk mendapatkan data Jurnal yang siap di posting ke buku besar history
     * 5. panggil method getDataBukuBesarHisLabaRugi() 
     *    untuk mendapatkan data Laba Rugi
     */
    
    /**
     * prepareDataJurnal
     * untuk menyiapkan data jurnal sebelum di posting
     * termasuk perhitungan saldo awal dan saldo akhir
     * @param date $tanggalAwal
     * @param date $tanggalAkhir
     * return void
     * 
     */
    public function prepareDataJurnal($tanggalAwal, $tanggalAkhir, $subAccount ='') {
        $dataJurnal = $this->getJurnalPembukuan($tanggalAwal, $tanggalAkhir, $subAccount);
        //var_dump($dataJurnal);
        //read data Jurnal
        //kelompokkan ke bukubesar atau buku besar history
        if (!empty($dataJurnal)) {
            $coaId = '';
            $subAcc = '';
            $idx = 0;
            $saldo = 0;

            for ($i = 0; $i < count($dataJurnal);$i++) {
                $this->_mBukuBesarHis[$i]['coa_id'] = $dataJurnal[$i]['akun_id'];
                $this->_mBukuBesarHis[$i]['coa_nama'] = $dataJurnal[$i]['akun_nama'];
                $this->_mBukuBesarHis[$i]['sub_acc'] = $dataJurnal[$i]['sub_account'];
                $this->_mBukuBesarHis[$i]['saldo_normal'] = $dataJurnal[$i]['status_debet'];
                $this->_mBukuBesarHis[$i]['saldo_mutasi_d'] = $dataJurnal[$i]['saldo_mutasi_d'];
                $this->_mBukuBesarHis[$i]['saldo_mutasi_k'] = $dataJurnal[$i]['saldo_mutasi_k'];
                $this->_mBukuBesarHis[$i]['saldo_mutasi_dk'] = $dataJurnal[$i]['saldo_mutasi_dk'];

                if (strtoupper($dataJurnal[$i]['akun_kelompok']) == 'PENDAPATAN' OR strtoupper($dataJurnal[$i]['akun_kelompok']) == 'BIAYA') {
                    $this->_mBukuBesarHisLabaRugi[$idx]['saldo_normal'] = $dataJurnal[$i]['status_debet'];
                    $this->_mBukuBesarHisLabaRugi[$idx]['sub_acc'] = $dataJurnal[$i]['sub_account'];
                    $this->_mBukuBesarHisLabaRugi[$idx]['saldo_normal'] = $dataJurnal[$i]['status_debet'];
                    $this->_mBukuBesarHisLabaRugi[$idx]['saldo_mutasi_d'] = $dataJurnal[$i]['saldo_mutasi_d'];
                    $this->_mBukuBesarHisLabaRugi[$idx]['saldo_mutasi_k'] = $dataJurnal[$i]['saldo_mutasi_k'];
                    $this->_mBukuBesarHisLabaRugi[$idx]['saldo_mutasi_dk'] = $dataJurnal[$i]['saldo_mutasi_dk'];
                    $this->_mBukuBesarHisLabaRugi[$idx]['akun_kelompok'] = $dataJurnal[$i]['akun_kelompok'];
                    $idx++;
                }
                // }
                
            }
        }
        //End Read Data Jurnal
    }

    public function getDataBukuBesar() {
        return $this->_mBukuBesar;
    }

    public function getDataBukuBesarHis() {
        return $this->_mBukuBesarHis;
    }
    
    public function getDataBukuBesarHisLabaRugi(){
        return $this->_mBukuBesarHisLabaRugi;
    }
    //end
    
    /**
     * untuk mendapatkan data jurnal yang siap diposting ke buku besar
     * diambil dari data jurnal yang sudah di setujui
     * @param date $tanggalAwal
     * @param date $tanggalAkhir
     * @return array
     */

    public function getJurnalPembukuan($tanggalAwal, $tanggalAkhir, $subAccount) {
        // pastikan format tanggal sesuai
        $tanggal_awal   = date('Y-m-d', strtotime($tanggalAwal));
        $tanggal_akhir  = date('Y-m-d', strtotime($tanggalAkhir));
        // set default tahun periode dan tahun pembukuan
        $this->Execute($this->mSqlQueries['set_tahun_pembukuan'], array());
        $this->Execute($this->mSqlQueries['set_tahun_anggaran'], array());
        $this->Execute($this->mSqlQueries['do_set_tahun_pembukuan'], array());
        $this->Execute($this->mSqlQueries['do_set_tahun_anggaran'], array());

        $return = $this->Open($this->mSqlQueries['get_pembukuan_jurnal'], array(
            $tanggal_awal,
            $tanggal_akhir,
            $subAccount.'%',
            ($subAccount == "all" || $subAccount == "")  ? 1 : 0
        ));
        
        return $return;
    }
}
?>