<?php

/**
 * Class PerhitunganLapRef
 * class ini untuk melakukan perhitungan 
 * dari kelompok laporan yang merefer ke
 * sub kemlompok dari laporan lain
 * 
 * @package finansi_laporan_builder
 * 
 * @added since Agustus 2017
 * @analyzed diyah fajar <dyah@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatecno.com>
 * 
 * 
 * @copyright (c) 2009 - 2017, Gamatechno Indonesia
 * 
 */
require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/tahun_pembukuan/business/PembukuanHistory.class.php';


//get data buku besar
require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/finansi_laporan_builder/business/DataBukuBesar.class.php';

class PerhitunganLapRef extends Database {

    protected $mSqlFile = 'module/finansi_laporan_builder/business/perhitungan_lap_ref.sql.php';
    private $_mTppIdAktif;
    private $_mTppIdSebelumnya;
    private $_mPembukuanHistObj;
    private $_mMainKellapRefIds = array();
    private $_mMainKellapMainRefIds = array();
    //private $_mMainKellapRefData = array();
    private $_mKellapRefIds = array();
    //private $_mKellapRefData = array();
    private $_mHitungJumlahLapRef = array();
    private $_mHitungJumlahLapRefLalu = array();
    private $_mDataLaporanRefDetail = array();
    private $_mBukuBesar;

    public function __construct($connectionNumber = 0) {
        parent::__construct($connectionNumber);
        $this->_mPembukuanHistObj = new PembukuanHistory;
        $this->_mTppIdSebelumnya = $this->_mPembukuanHistObj->getTppIdPeriodeSebelumnya();
        //get data buku besar
        $this->_mBukuBesar = new DataBukuBesar;
    }

    public function setTppIdAktif($tppId) {
        $this->_mTppIdAktif = $tppId;
    }

    public function setTppIdTahunSebelumnya($tppId) {
        $this->_mTppIdSebelumnya = $this->_mPembukuanHistObj->getTppIdPeriodeSebelumnyaById($tppId);
    }

    /**
     * setMainKellapRefId
     * untuk menentukan dan mengumpulkan kelompok laporan mana yang memiliki kellap ref id.
     * dan menyimpan nya dalam array
     * @param string $kodeSistem (kode sistem laporan pemilik kellap_ref atau 
     * yang memanggil kelompok laporan yang lain)
     * 
     */
    private function _setMainKellapRefId($kodeSistemMain) {
        $mainKellapRefIds = $this->open($this->mSqlQueries['get_kellap_ref_id'], array($kodeSistemMain));

        if (!empty($mainKellapRefIds)) {
            foreach ($mainKellapRefIds as $itemKellapRefId) {
                array_push($this->_mMainKellapRefIds, $itemKellapRefId['kellap_ref']);
                array_push($this->_mMainKellapMainRefIds, $itemKellapRefId['kellap_main']);
                #$this->_mMainKellapRefData[$itemKellapRefId['kellap_ref']] = $itemKellapRefId['kellap_main'];
            }
        }
        #echo '<pre>';
        #print_r($this->_mMainKellapRefIds);
        #print_r($this->_mMainKellapMainRefIds);
        #echo '</pre>';
    }

    /*
      private function _getKellapMainRefId($kellapRefId) {
      if(array_key_exists($kellapRefId, $this->_mMainKellapRefData)){
      return $this->_mMainKellapRefData[$kellapRefId];
      }
      }
     * 
     */

    private function _getKellapRefKodeSistem($kellapId) {
        $ksData = $this->open($this->mSqlQueries['get_kellap_ref_kode_sistem'], array(
            $kellapId
        ));

        if (!empty($ksData)) {
            return $ksData[0]['kode_sistem'];
        } else {
            return '';
        }
    }

    private function _getColectingKellapRefId() {
        $kellapRefData = $this->open($this->mSqlQueries['get_kellap_ref_data'], array(
            (empty($this->_mMainKellapRefIds) ? '' : $this->_mMainKellapRefIds),
            (empty($this->_mMainKellapRefIds) ? 1:0),
            (empty($this->_mMainKellapMainRefIds) ? '' : $this->_mMainKellapMainRefIds),
            (empty($this->_mMainKellapMainRefIds) ? 1:0)
        ));

        $this->_mDataLaporanRefDetail = $kellapRefData;

        if (!empty($kellapRefData)) {
            foreach ($kellapRefData as $itemRefData) {
                if ($itemRefData['kellap_is_summary'] == 'Y') {
                    $summaryDataRaw = $itemRefData['kellap_summary_detail'];
                    if (!empty($summaryDataRaw)) {
                        $summaryDetail = json_decode($summaryDataRaw, true);
                        if ($summaryDetail['operasiPerhitungan'] == 0) {
                            $this->_mKellapRefIds[] = array(
                                'is_sum' => 'Y',
                                'id_main' => $itemRefData['kellap_main_id'],
                                'id' => $summaryDetail['dataKlpLap']['id'],
                                'ks' => $this->_getKellapRefKodeSistem($summaryDetail['dataKlpLap']['id'])
                            );
                        } else {
                            $dataSummary = $summaryDetail['dataKlpLap'];
                            foreach ($dataSummary as $itemSummary) {
                                $this->_mKellapRefIds[] = array(
                                    'is_sum' => 'Y',
                                    'id_main' => $itemRefData['kellap_main_id'],
                                    'id' => $itemSummary['id'],
                                    'ks' => $this->_getKellapRefKodeSistem($itemSummary['id'])
                                );
                            }
                        }
                    }
                } else {
                    $this->_mKellapRefIds[] = array(
                        'is_sum' => 'T',
                        'id_main' => $itemRefData['kellap_main_id'],
                        'id' => $itemRefData['kellap_id'],
                        'ks' => $itemRefData['kellap_kode_sistem']
                    );
                }
            }
        }
        #echo '<pre>';
        #print_r($this->_mMainKellapRefIds);
        #echo '</pre>';
    }

    public function PrepareDataLaporanRef($kodeSistemMain, $tanggalAwal, $tanggalAkhir) {
        //set main kode sistem 
        $this->_setMainKellapRefId($kodeSistemMain);
        //get kellap ref id main
        $this->_getColectingKellapRefId();
        //get coa ref dari kellap ref id main
        $this->_getDataKellapRefCoa($tanggalAwal, $tanggalAkhir);
    }

    public function getKellapLapRef() {
        return $this->_mKellapRefIds;
    }

    private function _getDataKellapRefCoa($tanggalAwal, $tanggalAkhir) {
        //prepare kode sistem
        //prepare filter query
        $filterKs = '';
        $filterKsArray = array();
        $kellapRefDataCoa = array();
        foreach ($this->_mKellapRefIds as $key => $itemRefId) {

            $filterKs = "(";
            $filterKs .= "klr.kellapKodeSistem = '{$itemRefId['ks']}' ";
            $filterKs .= " OR ";
            $filterKs .= "klr.kellapKodeSistem LIKE '{$itemRefId['ks']}.%' ";
            $filterKs .= ")";
            $filterKsArray[$key] = $filterKs;

            $queryStr[$key] = str_replace(
                            array('[kellap_id_main]', '[kellap_main_is_summary]'), array("'" . $itemRefId['id_main'] . "'", "'" . $itemRefId['is_sum'] . "'"), $this->mSqlQueries['get_kellap_ref_data_coa']) . ' AND ' . $filterKs;
            //$kellapRefDataCoa = $this->open($queryStr[$key], array());   
            $kellapRefDataCoa = array_merge($kellapRefDataCoa, $this->open($queryStr[$key], array()));
        }

        #echo '<pre>';
        //print_r($queryStr);
        #print_r($this->_mKellapRefIds );
        #print_r($kellapRefDataCoa);
        #echo '</pre>';
        //hitung


        if (!empty($kellapRefDataCoa)) {
            $this->_mBukuBesar->PrepareDataBukuBesar($tanggalAwal, $tanggalAkhir);
            //masukan perhitungan saldo coa ke kelompok laporan per item
            $hitungSaldo = 0;
            $hitungSaldoBulanLalu = 0;

            foreach ($kellapRefDataCoa as $itemValue) {
                if (($itemValue['kellap_is_saldo_awal'] == 'Y') &&
                        ($itemValue['kellap_is_mutasi_dk'] == 'T') &&
                        ($itemValue['kellap_is_mutasi_d'] == 'T') &&
                        ($itemValue['kellap_is_mutasi_k'] == 'T')) {
                    //jumlahkan saldo awal saja
                    //tahun pembukuan aktif
                    $hitungSaldo = $this->_mBukuBesar->getSaldoAwaltahunSubAcc($itemValue['kellap_coa_id'],$itemValue['kellap_sub_acc'])+
                    $this->_mBukuBesar->getSaldoAkhirTahunLalu($itemValue['kellap_coa_id'],$itemValue['kellap_sub_acc']);

                    $hitungSaldoBulanLalu = $this->_mBukuBesar->getSaldoAwaltahunBulanLaluSubAcc($itemValue['kellap_coa_id'],$itemValue['kellap_sub_acc'])+
                        $this->_mBukuBesar->getSaldoAkhirTahunBulanLalu($itemValue['kellap_coa_id'],$itemValue['kellap_sub_acc']);
                } elseif (($itemValue['kellap_is_saldo_awal'] == 'Y') &&
                        ($itemValue['kellap_is_mutasi_dk'] == 'Y') &&
                        ($itemValue['kellap_is_mutasi_d'] == 'T') &&
                        ($itemValue['kellap_is_mutasi_k'] == 'T')) {
                    //[DK]jumlahkan saldo awal dan mutasi DK
                    //tahun pembukuan aktif
                    $hitungSaldo = $this->_mBukuBesar->getSaldoAwaltahunSubAcc($itemValue['kellap_coa_id'],$itemValue['kellap_sub_acc']) +
                        $this->_mBukuBesar->getSaldoMutasiDKSubAcc($itemValue['kellap_coa_id'],$itemValue['kellap_sub_acc'])+
                        $this->_mBukuBesar->getSaldoAkhirTahunLalu($itemValue['kellap_coa_id'],$itemValue['kellap_sub_acc']);

                    $hitungSaldoBulanLalu = $this->_mBukuBesar->getSaldoAwaltahunBulanLaluSubAcc($itemValue['kellap_coa_id'],$itemValue['kellap_sub_acc']) +
                        $this->_mBukuBesar->getSaldoMutasiDKBulanLaluSubAcc($itemValue['kellap_coa_id'],$itemValue['kellap_sub_acc'])+
                        $this->_mBukuBesar->getSaldoAkhirTahunBulanLalu($itemValue['kellap_coa_id'],$itemValue['kellap_sub_acc']);
                } elseif (($itemValue['kellap_is_saldo_awal'] == 'Y') &&
                        ($itemValue['kellap_is_mutasi_dk'] == 'T') &&
                        ($itemValue['kellap_is_mutasi_d'] == 'Y') &&
                        ($itemValue['kellap_is_mutasi_k'] == 'T')) {
                    //[D]jumlahkan saldo awal dan mutasi D
                    //tahun pembukuan aktif
                    $hitungSaldo = $this->_mBukuBesar->getSaldoAwaltahunSubAcc($itemValue['kellap_coa_id'],$itemValue['kellap_sub_acc']) +
                        $this->_mBukuBesar->getSaldoMutasiDKSubAcc($itemValue['kellap_coa_id'],$itemValue['kellap_sub_acc'], 'D')+
                        $this->_mBukuBesar->getSaldoAkhirTahunLalu($itemValue['kellap_coa_id'],$itemValue['kellap_sub_acc']);

                    $hitungSaldoBulanLalu = $this->_mBukuBesar->getSaldoAwaltahunBulanLaluSubAcc($itemValue['kellap_coa_id'],$itemValue['kellap_sub_acc']) +
                        $this->_mBukuBesar->getSaldoMutasiDKBulanLaluSubAcc($itemValue['kellap_coa_id'],$itemValue['kellap_sub_acc'], 'D')+
                        $this->_mBukuBesar->getSaldoAkhirTahunBulanLalu($itemValue['kellap_coa_id'],$itemValue['kellap_sub_acc']);
                } elseif (($itemValue['kellap_is_saldo_awal'] == 'Y') &&
                        ($itemValue['kellap_is_mutasi_dk'] == 'T') &&
                        ($itemValue['kellap_is_mutasi_d'] == 'T') &&
                        ($itemValue['kellap_is_mutasi_k'] == 'Y')) {
                    //[K]jumlahkan saldo awal dan mutasi K
                    //tahun pembukuan aktif
                    $hitungSaldo = $this->_mBukuBesar->getSaldoAwaltahunSubAcc($itemValue['kellap_coa_id'],$itemValue['kellap_sub_acc']) +
                        $this->_mBukuBesar->getSaldoMutasiDKSubAcc($itemValue['kellap_coa_id'],$itemValue['kellap_sub_acc'], 'K')+
                        $this->_mBukuBesar->getSaldoAkhirTahunLalu($itemValue['kellap_coa_id'],$itemValue['kellap_sub_acc']);

                    $hitungSaldoBulanLalu = $this->_mBukuBesar->getSaldoAwaltahunBulanLaluSubAcc($itemValue['kellap_coa_id'],$itemValue['kellap_sub_acc']) +
                        $this->_mBukuBesar->getSaldoMutasiDKBulanLaluSubAcc($itemValue['kellap_coa_id'],$itemValue['kellap_sub_acc'], 'K')+
                        $this->_mBukuBesar->getSaldoAkhirTahunBulanLalu($itemValue['kellap_coa_id'],$itemValue['kellap_sub_acc']);
                } elseif (($itemValue['kellap_is_saldo_awal'] == 'T') &&
                        ($itemValue['kellap_is_mutasi_dk'] == 'Y') &&
                        ($itemValue['kellap_is_mutasi_d'] == 'T') &&
                        ($itemValue['kellap_is_mutasi_k'] == 'T')) {
                    //[DK]jumlahkan  mutasi DK saja
                    //tahun pembukuan aktif
                    $hitungSaldo = $this->_mBukuBesar->getSaldoMutasiDKSubAcc($itemValue['kellap_coa_id'],$itemValue['kellap_sub_acc']);
                    $hitungSaldoBulanLalu = $this->_mBukuBesar->getSaldoMutasiDKBulanLaluSubAcc($itemValue['kellap_coa_id'],$itemValue['kellap_sub_acc']);
                } elseif (($itemValue['kellap_is_saldo_awal'] == 'T') &&
                        ($itemValue['kellap_is_mutasi_dk'] == 'T') &&
                        ($itemValue['kellap_is_mutasi_d'] == 'Y') &&
                        ($itemValue['kellap_is_mutasi_k'] == 'T')) {
                    //[D]jumlahkan  mutasi D saja
                    //tahun pembukuan aktif
                    $hitungSaldo = $this->_mBukuBesar->getSaldoMutasiDKSubAcc($itemValue['kellap_coa_id'],$itemValue['kellap_sub_acc'], 'D');
                    $hitungSaldoBulanLalu = $this->_mBukuBesar->getSaldoMutasiDKBulanLaluSubAcc($itemValue['kellap_coa_id'],$itemValue['kellap_sub_acc'], 'D');
                } elseif (($itemValue['kellap_is_saldo_awal'] == 'T') &&
                        ($itemValue['kellap_is_mutasi_dk'] == 'T') &&
                        ($itemValue['kellap_is_mutasi_d'] == 'T') &&
                        ($itemValue['kellap_is_mutasi_k'] == 'Y')) {
                    //[K]jumlahkan  mutasi K saja
                    //tahun pembukuan aktif
                    $hitungSaldo = $this->_mBukuBesar->getSaldoMutasiDKSubAcc($itemValue['kellap_coa_id'],$itemValue['kellap_sub_acc'], 'K');
                    $hitungSaldoBulanLalu = $this->_mBukuBesar->getSaldoMutasiDKBulanLaluSubAcc($itemValue['kellap_coa_id'],$itemValue['kellap_sub_acc'], 'K');
                } else {
                    $hitungSaldo = 0;
                    $hitungSaldoBulanLalu = 0;
                }


                // tandai positif atau negatif
                if ($itemValue['kellap_is_positif'] == 'Y') {
                    $hitungSaldo = ($hitungSaldo * 1);
                    $hitungSaldoBulanLalu = ($hitungSaldoBulanLalu * 1);
                } else {
                    $hitungSaldo = $hitungSaldo * (-1);
                    $hitungSaldoBulanLalu = $hitungSaldoBulanLalu * (-1);
                }
                //end
                if ($itemValue['kellap_main_is_summary'] == 'Y') {
                    if ($itemValue['kellap_is_tambah'] == 'Y') {
                        $this->_mHitungJumlahLapRef[$itemValue['kellap_main_id']] += $hitungSaldo;
                        $this->_mHitungJumlahLapRefLalu[$itemValue['kellap_main_id']] += $hitungSaldoBulanLalu;
                    } else {
                        $this->_mHitungJumlahLapRef[$itemValue['kellap_main_id']] -= $hitungSaldo;
                        $this->_mHitungJumlahLapRefLalu[$itemValue['kellap_main_id']] -= $hitungSaldoBulanLalu;
                    }
                } else {
                    $this->_mHitungJumlahLapRef[$itemValue['kellap_main_id']] += $hitungSaldo;
                    $this->_mHitungJumlahLapRefLalu[$itemValue['kellap_main_id']] += $hitungSaldoBulanLalu;
                }
            }
        }
        #echo '<pre>';
        ##print_r($this->_mHitungJumlahLapRef);
        #echo '</pre>';
    }

    public function getJumlahLapRef($kellapId) {
        if (array_key_exists($kellapId, $this->_mHitungJumlahLapRef)) {
            return $this->_mHitungJumlahLapRef[$kellapId];
        } else {
            return 0;
        }
    }

    public function getJumlahLapRefBulanLalu($kellapId) {
        if (array_key_exists($kellapId, $this->_mHitungJumlahLapRefLalu)) {
            return $this->_mHitungJumlahLapRefLalu[$kellapId];
        } else {
            return 0;
        }
    }

}
