<?php

class HTRealisasiPencairan extends Database {

    protected $mSqlFile = 'module/history_transaksi_realisasi/business/ht_realisasi_pencairan.sql.php';

    public function __construct($connectionNumber = 0) {
        parent::__construct($connectionNumber);
        //$this->SetDebugOn();
    }

   /**
    * [getPeriodeTahunPembukuan description]
    * @param  array
    * @return [type]
    */
   public function getTahunPembukuanPeriode($param = array())
   {
      $default    = array(
         'open' => false
      );
      $options    = array_merge($default, (array)$param);
      $return     = $this->Open($this->mSqlQueries['get_tahun_pembukuan_periode'], array(
         (int)($options['open'] === false)
      ));

      return $return;
   }

    public function GetCountData() {
        $result = $this->Open($this->mSqlQueries['get_count_data'], array());
        if (!empty($result)) {
            return $result[0]['total'];
        } else {
            return 0;
        }
    }

    public function GetData($offset, $limit, $awal, $akhir, $nomor = '', $posting = '', $mak_nama = '', $kas = '') {
        //$this->SetDebugOn();

        if ($kas == '1') {
            $fKas = '<=';
            $fKasTrue = 0;
        } elseif ($kas == '2') {
            $fKas = '>';
            $fKasTrue = 0;
        } else {
            $fKas = '=';
            $fKasTrue = 1;
        }

        if ($posting == 'all')
            $posting = '';
        if ($mak_nama != '') {
            $mak_sql = " AND mk.nomorPengajuan LIKE '%" . $mak_nama . "%' ";
        } else {
            $mak_sql = '';
        }
        $query = sprintf($this->mSqlQueries['get_data'], $awal, $akhir, '%' . $nomor . '%', '%' . $posting . '%', $fKas, $fKasTrue, $mak_sql, $offset, $limit);

        $result = $this->Open($query, array());
        //print_r($result);
        //echo sprintf($this->mSqlQueries['get_data'], $periode, $offset, $limit);
        return $result;
    }

}

?>