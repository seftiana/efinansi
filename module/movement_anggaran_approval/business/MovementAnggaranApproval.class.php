<?php

/**
 * @package HistoryApbnp
 * @className HistoryApbnp
 * @analyst dyah fajar n <dyah@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com> 
 * @copyright (c) 2015 Gamatechno Indonesia
 * 
 */

class MovementAnggaranApproval extends Database {

    protected $mSqlFile = 'module/movement_anggaran_approval/business/movement_anggaran_approval.sql.php';
    protected $mUserId;
    public $_POST;
    public $_GET;
    public $indonesianMonth = array(
        0 => array(
            'id' => 1,
            'name' => 'Januari'
        ), array(
            'id' => 2,
            'name' => 'Februari'
        ), array(
            'id' => 3,
            'name' => 'Maret'
        ), array(
            'id' => 4,
            'name' => 'April'
        ), array(
            'id' => 5,
            'name' => 'Mei'
        ), array(
            'id' => 6,
            'name' => 'Juni'
        ), array(
            'id' => 7,
            'name' => 'Juli'
        ), array(
            'id' => 8,
            'name' => 'Agustus'
        ), array(
            'id' => 9,
            'name' => 'September'
        ), array(
            'id' => 10,
            'name' => 'Oktober'
        ), array(
            'id' => 11,
            'name' => 'November'
        ), array(
            'id' => 12,
            'name' => 'Desember'
        )
    );

    public function __construct($connectionNumber = 0) {
        parent::__construct($connectionNumber);
        $this->mUserId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
        $this->_POST = is_object($_POST) ? $_POST->AsArray() : $_POST;
        $this->_GET = is_object($_GET) ? $_GET->AsArray() : $_GET;
    }

    public function GetData($komponenTujuan, $offset, $limit) {
        //$this->setDebugOn();
        if ($komponenAsal != '' AND $komponenTujuan != '') {
            $and = ' AND ';
        } else {
            $and = ' OR ';
        }
        $sql = sprintf($this->mSqlQueries['get_data'], '%s', '%s', '%d', '%d');

        $return = $this->Open($sql, array('%' . $komponenTujuan . '%',
            '%' . $komponenTujuan . '%',
            $offset,
            $limit));

        return $return;
    }

    public function CountData($komponenTujuan) {
        $sql = sprintf($this->mSqlQueries['count_data'], '%s', '%s');

        $return = $this->Open($sql, array('%' . $komponenTujuan . '%',
            '%' . $komponenTujuan . '%'));

        return $return[0]['total_data'];
    }

    public function GetDataMovement($param=array(), $offset, $limit) {
        //$this->SetDebugOn();
        extract($param);   
        if ($type == 'asal') {
            $asal = '1';
            $tujuan = '';
        } else {
            $asal = '';
            $tujuan = '1';
        }

        if($bulan_asal == 'all' || $bulan_asal == '' || empty($bulan_asal)) {
            $flag_bulan_asal = 1;
        } else {
            $flag_bulan_asal = 0;
        }

        if($bulan_tujuan == 'all' || $bulan_tujuan == '' || empty($bulan_tujuan)) {
            $flag_bulan_tujuan = 1;
        } else {
            $flag_bulan_tujuan = 0;
        }
        
        
        $result = $this->Open(
            $this->mSqlQueries['get_data_movement'], array(
            $ta_id,
            $ta_id,
            '%' . trim($kode) . '%','%' . trim($kode) . '%',(int) ($asal == ''),
            '%' . trim($kode) . '%','%' . trim($kode) . '%', (int) ($tujuan == ''),
            $ta_id,
            $ta_id,
            $bulan_asal,$flag_bulan_asal,
            $bulan_tujuan,$flag_bulan_tujuan,
            $offset,
            $limit
            )
        );

        return $result;
    }
    
    function GetCountMovement() {
        $result = $this->Open(
                $this->mSqlQueries['get_count_movement'], array()
        );

        if ($result) {
            return $result[0]['total'];
        } else {
            return 0;
        }
    }

    public function DetailApbnp($id) {
        $result = $this->Open($this->mSqlQueries['get_detail_apbnp'], array($id));

        return $result;
    }

    public function Approve($status, $id) {
        if ($status === 'Ya') {
            $cekData = $this->Open($this->mSqlQueries['get_total_row_history'], array($id));            
            if($cekData[0]['total_row_history'] == 0){
                $result = $this->Execute($this->mSqlQueries['approve_ya'], array($id));
            } else {
                $result = true;
            }   
        } elseif ($status === 'Tidak') {
            $result = $this->Execute($this->mSqlQueries['approve_tidak'], array($id));
        }

        return $result;
    }


   public function GetPeriodeTahun($param = array())
   {
      $default       = array(
         'active' => false,
         'open' => false
      );
      $option        = array_merge($default, (array)$param);
      $return        = $this->Open($this->mSqlQueries['get_periode_tahun'], array(
         (int)($option['active'] === false),
         (int)($option['open'] === false)
      ));

      return $return;
   }
   

}

?>