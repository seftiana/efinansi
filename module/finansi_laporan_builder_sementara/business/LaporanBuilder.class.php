<?php

/**
 * @package finansi_laporan_builder_sementara
 * 
 * untuk menampilkan data laporan aktivitas
 * @added since Agustus 2017
 * @analyzed diyah fajar <dyah@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatecno.com>
 * 
 * @copyright (c) 2009 - 2017, Gamatechno Indonesia
 */
/**
 * cara menggunakan modul ini :
 * 1. buat instance terlebih dahulu.
 * 2. panggil method setup() dan tentukan id kelompok laporan yang akan di tampilkan
 * 3. gunakan instace yang telah di buat tadi untuk mengkases method method yang tersedia
 * 
 * untuk view detail 
 * langsung panggi method getLaporanDetail
 * 
 * class ini digunakan bersama class DataBukuBesar
 * class ini digunakan olah
 * laporan aktivitas,
 * laporan arus kas
 * laporan posisi keuangan
 * (laporan keuanga)
 * 
 */
require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'main/function/date.php';

//get data buku besar
require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/finansi_laporan_builder_sementara/business/DataBukuBesar.class.php';

require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/finansi_laporan_builder_sementara/business/PerhitunganLapRef.class.php';

require_once GTFWConfiguration::GetValue('application', 'docroot') . 
        'module/finansi_laporan_builder/business/AttributeUnit.class.php';

class LaporanBuilder extends Database {

    protected $mSqlFile = 'module/finansi_laporan_builder_sementara/business/laporan_builder.sql.php';
    private $_mDataKelompokLaporan = array();
    private $_mDataSaldoCoa = array();
    private $_mTppIdAktif;
    //buku besar
    private $_mBukuBesar;
    //id kelompok laporan (hardkode)
    private $_mKelompokId;
    private $_mKelompokKodeSistem;
    //
    private $_mKlpId = array();
    //untuk kelompok laporn ref id
    private $_mDataKellapRefId = array();
    private $_mDataKellapRefSaldoCoa = array();
    private $_mPerhitunganLapRef;

    private $_mAUnit;

    function __construct($connectionNumber = 0) {
        parent::__construct($connectionNumber);
        //get data buku besar
        $this->_mBukuBesar = new DataBukuBesar;

        //get perhitungan lap ref
        $this->_mPerhitunganLapRef = new PerhitunganLapRef;

        //Atribute
        $this->_mAUnit = new AttributeUnit;
    }

    /**
     * panggil method ini sebelum menampilkan laporan
     * @param number $klpParentId id kelompok laporan root/parent
     */
    public function setup($klpParentId) {
        $this->_mKelompokId = $klpParentId;
        $this->_mTppIdAktif = $this->_mBukuBesar->getTahunPembukuanAktifId();
        $this->_mKelompokKodeSistem = $this->_getKodeSistem($klpParentId);
    }

    /**
     * _getKodeSistem
     * untuk mendapatkan kode sistem kelompok laporan
     * @param type $kellapId
     * @return string
     */
    private function _getKodeSistem($kellapId) {
        $ksData = $this->open($this->mSqlQueries['get_kode_sistem'], array($kellapId));
        if (!empty($ksData)) {
            return $ksData[0]['kode_sistem'] . '.%';
        } else {
            return '';
        }
    }

    /**
     * getKodeSistemKelompokId
     * @param type $kellapId
     * @return type
     */
    public function getKodeSistemKelompokId($kellapId) {
        return $this->_getKodeSistem($kellapId);
    }
    /**
     * untuk mengganti tppId jika dikehendaki
     * tidak menggunakan tppid aktif
     * @param type $tppId
     */
    public function setTppIdAktif($tppId) {
        $this->_mBukuBesar->setTppIdAktif($tppId);
    }

    /**
     * end
     */
    public function getKelompokId() {
        return $this->_mKelompokId;
    }

    public function getTahunPembukuanAktifId() {
        return $this->_mTppIdAktif;
    }

    public function getPeriodePembukuan() {
        $dataPeriode = array();
        $ret = $this->open($this->mSqlQueries['get_periode_pembukuan'], array());
        if (!empty($ret)) {
            return $dataPeriode = $ret[0];
        }
        return $dataPeriode;
    }

    public function getPeriodeNama($ts = false) {
        $ret = $this->open($this->mSqlQueries['get_periode_nama'], array(
            $this->_mTppIdAktif
        ));
        if (!empty($ret)) {
            if ($ts == false) {
                return $ret[0]['nama_periode'];
            } else {
                return $ret[0]['nama_periode_ts'];
            }
        } else {
            return '';
        }
    }

    function GetMinMaxThnTrans() {
        $ret = $this->open($this->mSqlQueries['get_minmax_tahun_transaksi'], array());

        if ($ret) {
            return $ret[0];
        } else {
            $now_thn = date('Y');
            $thn['minTahun'] = $now_thn - 5;
            $thn['maxTahun'] = $now_thn + 5;

            return $thn;
        }
    }

    function GetMinMaxThnPeriodePembukuan() {
        $ret = $this->open($this->mSqlQueries['get_minmax_tahun_periode_pembukuan'], array());

        if ($ret) {
            return $ret[0];
        } else {
            $now_thn = date('Y');
            $thn['minTahun'] = $now_thn - 5;
            $thn['maxTahun'] = $now_thn + 5;

            return $thn;
        }
    }

    /**
     * siapkan data laporan
     */
    public function PrepareData($tanggalAwal, $tanggalAkhir, $subAccount='') {
        //get perhitungan laporan ref
        $this->_mPerhitunganLapRef->PrepareDataLaporanRef($this->_mKelompokKodeSistem, $tanggalAwal, $tanggalAkhir);

        //
        $this->_doHitungSaldoPerKelompok($tanggalAwal, $tanggalAkhir,$subAccount, $this->_mKelompokKodeSistem);
        $this->_getSusunanLaporan($this->_mKelompokKodeSistem);

        //untuk kelompok laporan ref
        // $this->PrepareDataKelompokLaporanRef($tanggalAwal, $tanggalAkhir);
    }

    /**
     * get data kelompok laporan dan simpan dalam data array
     * Membuat array dari data hasil query
     * pastikan hasil query nya urut
     * @since 27 Maret 2017
     */
    private function _getSusunanLaporan($kodeSistem = '') {
        $dataArr = array();
        $data = $this->open($this->mSqlQueries['get_susunan_laporan'], array(
            $kodeSistem
        ));

        if (!empty($data)) {
            foreach ($data as $value) {
                $dataArr[$value['kellap_id']]['id'] = $value['kellap_id'];
                $dataArr[$value['kellap_id']]['pid'] = $value['kellap_pid'];
                $dataArr[$value['kellap_id']]['nama'] = $value['kellap_nama'];
                $dataArr[$value['kellap_id']]['level'] = $value['kellap_level'];
                $dataArr[$value['kellap_id']]['is_tambah'] = $value['kellap_is_tambah'];
                $dataArr[$value['kellap_id']]['is_summary'] = $value['kellap_is_summary'];
                $dataArr[$value['kellap_id']]['summary_detail'] = $value['kellap_summary_detail'];
                array_push($this->_mKlpId, $value['kellap_id']);
            }
        }
        $this->_mDataKelompokLaporan = $dataArr;
    }

    /**
     * untuk melakukan pencairan di array multidimensi
     * @param Array $mdArrayData
     * @param String $key
     * @param Mixed $value (string lebih baik)
     * @return Array
     * @since 27 Maret 2017
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
                $dataArr[$value['id']]['is_tambah'] = $value['is_tambah'];
                $dataArr[$value['id']]['is_summary'] = $value['is_summary'];
                $dataArr[$value['id']]['summary_detail'] = $value['summary_detail'];
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
     * @since 27 Maret 2017
     */
    public function GetLaporan($parentId = 0) {
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
                $collecting[$k]['is_tambah'] = $v['is_tambah'];
                $collecting[$k]['is_summary'] = $v['is_summary'];
                $collecting[$k]['summary_detail'] = $v['summary_detail'];

                //getChild
                $getChild = $this->GetLaporan($v['id']);
                if (!empty($getChild)) {
                    $collecting[$k]['is_child'] = '0';
                    $collecting += $getChild;
                } else {
                    $collecting[$k]['is_child'] = '1';
                    $collecting[$k]['saldo'] = $this->_getSaldoPerKelompok($v['id']) + $this->_getSaldoCoaRef($v['id']);
                }
            }
        }
        return $collecting;
    }

    private function _doHitungSummary($summaryDataRaw) {
        $dataSummary = array();
        $saldoSummary = 0;
        if (!empty($summaryDataRaw)) {
            $summaryDetail = json_decode($summaryDataRaw, true);
            if ($summaryDetail['operasiPerhitungan'] == 0) {
                $dataSummary[0] = array('id' => $summaryDetail['dataKlpLap']['id']);
            } else {
                $dataSummary = $summaryDetail['dataKlpLap'];
            }

            foreach ($dataSummary as $itemSummary) {
                $saldoSummary += $this->_getSaldoJumlahPerKelompok($itemSummary['id']);
            }
        }

        return array(
            'saldo_summary' => $saldoSummary
        );
    }

    //Perhitungan saldo Coa Per Kelompok

    private function _doHitungSaldoPerKelompok($tanggalAwal, $tanggalAkhir, $subAccount, $kodeSistem) {
        //get kelompok laporan COA      
        $dataArr = array();
        $dataArr_LAK = array();

        //get kellap ref id
        $dataKellapRefId = array();
        $dataKlpCoa = $this->open($this->mSqlQueries['get_kelompok_laporan_coa_ref_sub_account'], array(
            $kodeSistem,
            $subAccount.'%',
            ($subAccount == "all" || $subAccount == "")  ? 1 : 0
        ));

        $this->_mBukuBesar->PrepareDataBukuBesar($tanggalAwal, $tanggalAkhir, $subAccount);
        if (!empty($dataKlpCoa)) {
            //masukan perhitungan saldo coa ke kelompok laporan per item
            $hitungSaldo = 0;
            
            foreach ($dataKlpCoa as $itemValue) {
                //kumpulkan id kelompok laporan ref
                //echo $itemValue['kellap_ref'];
                if (!empty($itemValue['kellap_ref'])) {
                    //hitung jumlah dari kelompok laporan ref
                    $this->_mDataKellapRefSaldoCoa[$itemValue['kellap_id']] = $this->_mPerhitunganLapRef->getJumlahLapRef($itemValue['kellap_id']);
                    //jika ref coa di kelompok laporan ref
                    $hitungSaldo = 0;
                    //end
                } else {

                    if (($itemValue['kellap_is_saldo_awal'] == 'Y') &&
                            ($itemValue['kellap_is_mutasi_dk'] == 'T') &&
                            ($itemValue['kellap_is_mutasi_d'] == 'T') &&
                            ($itemValue['kellap_is_mutasi_k'] == 'T')) {
                        //jumlahkan saldo awal saja
                        //tahun pembukuan aktif
                        $hitungSaldo = $this->_mBukuBesar->getSaldoAwaltahunSubAcc($itemValue['kellap_coa_id'],$itemValue['kellap_sub_acc'])+
                        $this->_mBukuBesar->getSaldoAkhirTahunLalu($itemValue['kellap_coa_id'],$itemValue['kellap_sub_acc']);

                    } elseif (($itemValue['kellap_is_saldo_awal'] == 'Y') &&
                            ($itemValue['kellap_is_mutasi_dk'] == 'Y') &&
                            ($itemValue['kellap_is_mutasi_d'] == 'T') &&
                            ($itemValue['kellap_is_mutasi_k'] == 'T')) {
                        //[DK]jumlahkan saldo awal dan mutasi DK
                        //tahun pembukuan aktif
                        $hitungSaldo = $this->_mBukuBesar->getSaldoAwaltahunSubAcc($itemValue['kellap_coa_id'],$itemValue['kellap_sub_acc']) + 
                        $this->_mBukuBesar->getSaldoMutasiDKSubAcc($itemValue['kellap_coa_id'],$itemValue['kellap_sub_acc'])+
                        $this->_mBukuBesar->getSaldoAkhirTahunLalu($itemValue['kellap_coa_id'],$itemValue['kellap_sub_acc']);
                                
                    } elseif (($itemValue['kellap_is_saldo_awal'] == 'Y') &&
                            ($itemValue['kellap_is_mutasi_dk'] == 'T') &&
                            ($itemValue['kellap_is_mutasi_d'] == 'Y') &&
                            ($itemValue['kellap_is_mutasi_k'] == 'T')) {
                        //[D]jumlahkan saldo awal dan mutasi D
                        //tahun pembukuan aktif
                        $hitungSaldo = $this->_mBukuBesar->getSaldoAwaltahunSubAcc($itemValue['kellap_coa_id'],$itemValue['kellap_sub_acc']) +
                                $this->_mBukuBesar->getSaldoMutasiDKSubAcc($itemValue['kellap_coa_id'],$itemValue['kellap_sub_acc'], 'D')+
                                $this->_mBukuBesar->getSaldoAkhirTahunLalu($itemValue['kellap_coa_id'],$itemValue['kellap_sub_acc']);
                    } elseif (($itemValue['kellap_is_saldo_awal'] == 'Y') &&
                            ($itemValue['kellap_is_mutasi_dk'] == 'T') &&
                            ($itemValue['kellap_is_mutasi_d'] == 'T') &&
                            ($itemValue['kellap_is_mutasi_k'] == 'Y')) {
                        //[K]jumlahkan saldo awal dan mutasi K
                        //tahun pembukuan aktif
                        $hitungSaldo = $this->_mBukuBesar->getSaldoAwaltahunSubAcc($itemValue['kellap_coa_id'],$itemValue['kellap_sub_acc']) +
                                $this->_mBukuBesar->getSaldoMutasiDKSubAcc($itemValue['kellap_coa_id'],$itemValue['kellap_sub_acc'], 'K')+
                                $this->_mBukuBesar->getSaldoAkhirTahunLalu($itemValue['kellap_coa_id'],$itemValue['kellap_sub_acc']);
                                
                    } elseif (($itemValue['kellap_is_saldo_awal'] == 'T') &&
                            ($itemValue['kellap_is_mutasi_dk'] == 'Y') &&
                            ($itemValue['kellap_is_mutasi_d'] == 'T') &&
                            ($itemValue['kellap_is_mutasi_k'] == 'T')) {
                        //[DK]jumlahkan  mutasi DK saja
                        //tahun pembukuan aktif
                        $hitungSaldo = $this->_mBukuBesar->getSaldoMutasiDKSubAcc($itemValue['kellap_coa_id'],$itemValue['kellap_sub_acc']);
                        
                    } elseif (($itemValue['kellap_is_saldo_awal'] == 'T') &&
                            ($itemValue['kellap_is_mutasi_dk'] == 'T') &&
                            ($itemValue['kellap_is_mutasi_d'] == 'Y') &&
                            ($itemValue['kellap_is_mutasi_k'] == 'T')) {
                        //[D]jumlahkan  mutasi D saja
                        //tahun pembukuan aktif
                        $hitungSaldo = $this->_mBukuBesar->getSaldoMutasiDKSubAcc($itemValue['kellap_coa_id'],$itemValue['kellap_sub_acc'], 'D');
                        
                    } elseif (($itemValue['kellap_is_saldo_awal'] == 'T') &&
                            ($itemValue['kellap_is_mutasi_dk'] == 'T') &&
                            ($itemValue['kellap_is_mutasi_d'] == 'T') &&
                            ($itemValue['kellap_is_mutasi_k'] == 'Y')) {
                        //[K]jumlahkan  mutasi K saja
                        //tahun pembukuan aktif
                        $hitungSaldo = $this->_mBukuBesar->getSaldoMutasiDKSubAcc($itemValue['kellap_coa_id'],$itemValue['kellap_sub_acc'], 'K');
                        
                    } else {
                        $hitungSaldo = 0;
                    }
                }

                //tandai negatif atau positif
                if ($itemValue['kellap_is_positif'] == 'Y') {
                    $dataArr[$itemValue['kellap_id']] += $hitungSaldo;
                } else {
                    $dataArr[$itemValue['kellap_id']] -= $hitungSaldo;
                }
            }
        }

        $this->_mDataSaldoCoa = $dataArr;
        $this->_mDataKellapRefId = $dataKellapRefId;
    }

    public function _getSaldoCoaRef($kellapMainId) {
        $nominal = 0;
        if (array_key_exists($kellapMainId, $this->_mDataKellapRefSaldoCoa)) {
            $nominal = $this->_mDataKellapRefSaldoCoa[$kellapMainId];
        }
        return $nominal;
    }

    /**
     * _getSaldoPerKelompok
     * untnuk mendapatkan jumlah saldo per kelompok
     * 
     * @param number $kellapId
     * @return number
     */
    private function _getSaldoPerKelompok($kellapId) {
        $nominal = 0;
        if (array_key_exists($kellapId, $this->_mDataSaldoCoa)) {
            $nominal = $this->_mDataSaldoCoa[$kellapId];
        }

        return $nominal;
    }

    /**
     * getSaldoJumlahPerKelompok
     * untuk mendapatkan jumlah saldo per kelompok
     * 
     * @param number $parentId
     * @return number
     */
    private function _getSaldoJumlahPerKelompok($parentId) {
        $getLaporan = $this->GetLaporan($parentId);
        $nominal = 0;
        if (!empty($getLaporan)) {
            foreach ($getLaporan as $itemLaporan) {
                if ($itemLaporan['is_tambah'] == 'T') {
                    $nominal -= $itemLaporan['saldo'];
                } else {
                    $nominal += $itemLaporan['saldo'];
                }
            }
        } else {

            if ($this->_mDataKelompokLaporan[$parentId]['is_tambah'] == 'T') {
                $nominal -= $this->_getSaldoPerKelompok($parentId);
            } else {
                $nominal += $this->_getSaldoPerKelompok($parentId);
            }
        }
        return $nominal;
    }

    public function laporanView() {
        $getLaporan = $this->GetLaporan($this->_mKelompokId);
        foreach ($getLaporan as $k => $itemLaporan) {
            if ($itemLaporan['is_summary']) {
                $summaryCalc = $this->_doHitungSummary($itemLaporan['summary_detail']);
                //}
                $getLaporan[$k]['saldo_summary'] = $summaryCalc['saldo_summary'];
            }
        }

        return $getLaporan;
    }

    //coa detail    
    public function getLaporanDetail($tanggalAwal, $tanggalAkhir, $kellapId, $subAccount='') {
        
        $sql = str_replace(
            "[TANGGAL]", 
            "", 
            $this->mSqlQueries['get_kelompok_laporan_coa_detail_sub_account']
        );

        $dataKlpCoa = $this->open($sql, array(
            $kellapId,
            $subAccount.'%',
            ($subAccount == "all" || $subAccount == "")  ? 1 : 0
        ));

        $this->_mBukuBesar->PrepareDataBukuBesar($tanggalAwal, $tanggalAkhir,$subAccount);
        if (!empty($dataKlpCoa)) {
            //masukan perhitungan saldo coa ke detail kelompok laporan
            $hitungSaldo = 0;
            
            foreach ($dataKlpCoa as $key => $itemValue) {
                if (($itemValue['kellap_is_saldo_awal'] == 'Y') &&
                        ($itemValue['kellap_is_mutasi_dk'] == 'T') &&
                        ($itemValue['kellap_is_mutasi_d'] == 'T') &&
                        ($itemValue['kellap_is_mutasi_k'] == 'T')) {
                    //jumlahkan saldo awal saja
                    //tahun pembukuan aktif
                    $hitungSaldo = $this->_mBukuBesar->getSaldoAwaltahunSubAcc($itemValue['kellap_coa_id'],$itemValue['kellap_sub_acc'])+
                    $this->_mBukuBesar->getSaldoAkhirTahunLalu($itemValue['kellap_coa_id'],$itemValue['kellap_sub_acc']);
                    
                } elseif (($itemValue['kellap_is_saldo_awal'] == 'Y') &&
                        ($itemValue['kellap_is_mutasi_dk'] == 'Y') &&
                        ($itemValue['kellap_is_mutasi_d'] == 'T') &&
                        ($itemValue['kellap_is_mutasi_k'] == 'T')) {
                    //[DK]jumlahkan saldo awal dan mutasi DK
                    //tahun pembukuan aktif
                    $hitungSaldo = $this->_mBukuBesar->getSaldoAwaltahunSubAcc($itemValue['kellap_coa_id'],$itemValue['kellap_sub_acc']) +
                    $this->_mBukuBesar->getSaldoMutasiDKSubAcc($itemValue['kellap_coa_id'],$itemValue['kellap_sub_acc'])+
                    $this->_mBukuBesar->getSaldoAkhirTahunLalu($itemValue['kellap_coa_id'],$itemValue['kellap_sub_acc']);

                            
                } elseif (($itemValue['kellap_is_saldo_awal'] == 'Y') &&
                        ($itemValue['kellap_is_mutasi_dk'] == 'T') &&
                        ($itemValue['kellap_is_mutasi_d'] == 'Y') &&
                        ($itemValue['kellap_is_mutasi_k'] == 'T')) {
                    //[D]jumlahkan saldo awal dan mutasi D
                    //tahun pembukuan aktif
                    $hitungSaldo = $this->_mBukuBesar->getSaldoAwaltahunSubAcc($itemValue['kellap_coa_id'],$itemValue['kellap_sub_acc']) +
                    $this->_mBukuBesar->getSaldoMutasiDKSubAcc($itemValue['kellap_coa_id'],$itemValue['kellap_sub_acc'], 'D')+
                    $this->_mBukuBesar->getSaldoAkhirTahunLalu($itemValue['kellap_coa_id'],$itemValue['kellap_sub_acc']);
                            
                } elseif (($itemValue['kellap_is_saldo_awal'] == 'Y') &&
                        ($itemValue['kellap_is_mutasi_dk'] == 'T') &&
                        ($itemValue['kellap_is_mutasi_d'] == 'T') &&
                        ($itemValue['kellap_is_mutasi_k'] == 'Y')) {
                    //[K]jumlahkan saldo awal dan mutasi K
                    //tahun pembukuan aktif
                    $hitungSaldo = $this->_mBukuBesar->getSaldoAwaltahunSubAcc($itemValue['kellap_coa_id'],$itemValue['kellap_sub_acc']) +
                            $this->_mBukuBesar->getSaldoMutasiDKSubAcc($itemValue['kellap_coa_id'],$itemValue['kellap_sub_acc'], 'K')+
                            $this->_mBukuBesar->getSaldoAkhirTahunLalu($itemValue['kellap_coa_id'],$itemValue['kellap_sub_acc']);
                            
                } elseif (($itemValue['kellap_is_saldo_awal'] == 'T') &&
                        ($itemValue['kellap_is_mutasi_dk'] == 'Y') &&
                        ($itemValue['kellap_is_mutasi_d'] == 'T') &&
                        ($itemValue['kellap_is_mutasi_k'] == 'T')) {
                    //[DK]jumlahkan  mutasi DK saja
                    //tahun pembukuan aktif
                    $hitungSaldo = $this->_mBukuBesar->getSaldoMutasiDKSubAcc($itemValue['kellap_coa_id'],$itemValue['kellap_sub_acc']);

                } elseif (($itemValue['kellap_is_saldo_awal'] == 'T') &&
                        ($itemValue['kellap_is_mutasi_dk'] == 'T') &&
                        ($itemValue['kellap_is_mutasi_d'] == 'Y') &&
                        ($itemValue['kellap_is_mutasi_k'] == 'T')) {
                    //[D]jumlahkan  mutasi D saja
                    //tahun pembukuan aktif
                    $hitungSaldo = $this->_mBukuBesar->getSaldoMutasiDKSubAcc($itemValue['kellap_coa_id'],$itemValue['kellap_sub_acc'], 'D');

                } elseif (($itemValue['kellap_is_saldo_awal'] == 'T') &&
                        ($itemValue['kellap_is_mutasi_dk'] == 'T') &&
                        ($itemValue['kellap_is_mutasi_d'] == 'T') &&
                        ($itemValue['kellap_is_mutasi_k'] == 'Y')) {
                    //[K]jumlahkan  mutasi K saja
                    //tahun pembukuan aktif
                    $hitungSaldo = $this->_mBukuBesar->getSaldoMutasiDKSubAcc($itemValue['kellap_coa_id'],$itemValue['kellap_sub_acc'], 'K');

                } else {
                    $hitungSaldo = 0;
                }
                
                //tandai positif/negatid
                if ($itemValue['kellap_is_positif'] == 'Y') {
                    $dataKlpCoa[$key]['kellap_coa_saldo'] = $hitungSaldo;
                } else {
                    $dataKlpCoa[$key]['kellap_coa_saldo'] = $hitungSaldo * (-1);
                }
            }
        }

        return $dataKlpCoa;
    }

    public function getKelompokInfo($kellapId) {
        $nama = array();
        $ret = $this->open($this->mSqlQueries['get_kelompok_info'], array($kellapId));
        if (!empty($ret)) {
            $nama = $ret[0];
        }
        return $nama;
    }

    public function getDataLaporanRefDetail($kodeSistemMain, $tanggalAwal, $tanggalAkhir, $kellapId) {
        //Prepare data
        $kodeSistemMain = $kodeSistemMain.'.%';
        $this->_mPerhitunganLapRef->PrepareDataLaporanRef($kodeSistemMain,$tanggalAwal, $tanggalAkhir);      
         $getKellRefDetail = $this->open($this->mSqlQueries['get_kelompok_laporan_ref_detail'], array($kellapId));
        if(!empty($getKellRefDetail)) {
            foreach ($getKellRefDetail as $key => $itemValue) {
                  $getKellRefDetail[$key]['saldo'] = $this->_mPerhitunganLapRef->getJumlahLapRef($itemValue['kellap_main_id']);
            }
        }        
        return $getKellRefDetail;
    }
}
?>