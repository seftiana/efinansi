<?php

class PopupDataLppa extends Database {

    protected $mSqlFile = 'module/finansi_transaksi_penerimaan_bank/business/popup_data_lppa.sql.php';

    public function __construct($connectionNumber = 0) {
        parent::__construct($connectionNumber);
        $this->SetDebugOn();
    }

    public function getDataLppa($offset, $limit, $unitkerja, $tahunAnggaranId,$tbankId,$kode = '',$nama = '') {        
        $result = $this->Open($this->mSqlQueries['get_lppa'], array(
            $tbankId, // untuk enable pilih
            $tahunAnggaranId,
            $unitkerja, 
            '%' . $kode . '%', 
            '%' . $kode . '%', 
            '%' . $nama . '%',
            $offset, 
            $limit
        ));
        return $result;
    }

    public function getCountDataLppa() {
        $result = $this->Open($this->mSqlQueries['get_count_data'], array());
        if (!$result) {
            return 0;
        } else {
            return $result[0]['total'];
        }
    }
    
    public function getPeriodeTahun($param = array()) {
    //   $default    = array(
    //      'active' => false,
    //      'open' => false
    //   );
      //$options    = array_merge($default, (array)$param);
      $return     = $this->Open($this->mSqlQueries['get_periode_tahun'], array());

      if(!empty($param) && isset($param['active'])) {
          if($param['active'] == true) {
            $return     = $this->Open($this->mSqlQueries['get_periode_tahun_aktif'], array());        
          }
      }      
     
      return $return;
    }

   public function getPeriodeTahunAktifOpen()
   {
      $return     = $this->Open($this->mSqlQueries['get_periode_tahun_aktif_open'], array());

      return $return;
   }
}

?>