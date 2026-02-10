<?php

/**
 * class KomponenKodeAset
 * integrasi dengan database aset
 * untuk mengambil kode komponen dari database aset
 * @package komponen
 * @since 26 april 2012
 * @access public
 * @copyright 2012 Gamatechno
 * @author noor hadi <noor.hadi@gamatechno.com>
 * 
 */
 
 class KomponenKodeAset extends Database
 {
    protected $mSqlFile= 'module/komponen/business/komponenkodeaset.sql.php';
    
    public function __construct()
    {
        /**
         * ambil value $connectionNumber di application.conf
         */
        $connectionNumber = GTFWConfiguration::GetValue('application','gtaset_conn');
        parent::__construct($connectionNumber);
    }
    
    public function GetKodeAset($kode='',$nama='',$startRec,$itemViewed)
    {
        return $this->Open($this->mSqlQueries['get_kode_aset'],array(
                                            '%'.$kode.'%',
                                            '%'.$nama.'%',
                                            $startRec,
                                            $itemViewed));
    }
    
    public function GetCountKodeAset($kode='',$nama='')
    {
        $total = $this->Open($this->mSqlQueries['get_count_kode_aset'],array(
                                                '%'.$kode.'%',
                                                '%'.$nama.'%'
                                                ));
        return $total[0]['total'];
    }
    
    /**
     * fungsi IsConnected
     * @todo untuk melakukan cek koneksi dengan database
     * @since 2 Mei 2012
     * @access public
     */
    public function IsConnected()
    {
        return $this->mrDbEngine->Connect();
    }
 }