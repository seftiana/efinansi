<?php

class PopupRencanaPenerimaan extends Database {

    protected $mSqlFile = 'module/finansi_transaksi_penerimaan_bank/business/popup_rencana_penerimaan.sql.php';

    public function __construct($connectionNumber = 0) {
        parent::__construct($connectionNumber);
        $this->SetDebugOn();
    }

    public function GetData($offset, $limit, $unitkerja, $tahunAnggaranId,$kode = '',$nama = '') {        
        $result = $this->Open($this->mSqlQueries['get_data'], array(
            $unitkerja, 
            '%' . $kode . '%', 
            '%' . $nama . '%',
            $tahunAnggaranId,
            $offset, 
            $limit
        ));
        return $result;
    }

    public function GetCountData() {
        $result = $this->Open($this->mSqlQueries['get_count_data'], array());
        if (!$result) {
            return 0;
        } else {
            return $result[0]['total'];
        }
    }
    
    public function getPeriodeTahun($param = array()) {
      $default    = array(
         'active' => false,
         'open' => false
      );
      //$options    = array_merge($default, (array)$param);
      $return     = $this->Open($this->mSqlQueries['get_periode_tahun'], array());
     
      return $return;
    }

   public function getPeriodeTahunAktifOpen()
   {
      $return     = $this->Open($this->mSqlQueries['get_periode_tahun_aktif_open'], array());

      return $return;
   }
}

?>