<?php

/**
 * @package HistoryApbnp
 * @className HistoryApbnp
 * @analyst dyah fajar n <dyah@gamatechno.com>
 * @author noor hadi<noor.hadi@gamatechno.com> 
 * @copyright (c) 2015 Gamatechno Indonesia
 * 
 */

class HistoryApbnp extends Database {

    protected $mSqlFile = 'module/history_apbnp/business/app_history_apbnp.sql.php';
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

    public function GetDataMovement($param=array(), $offset, $limit) {
        //$this->SetDebugOn();
        extract($param);   
       
        if($type =='asal') {
            $asal = 0;
        } else {
            $asal = 1;
        }
        
        if($type =='tujuan') {
            $tujuan = 0;
        } else {
            $tujuan = 1;
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

        if($unit_id_asal == 'all' || $unit_id_asal == '' || empty($unit_id_asal)) {
            $flag_unit_asal = 1;
        } else {
            $flag_unit_asal = 0;
        }

        if($unit_id_tujuan == 'all' || $unit_id_tujuan == '' || empty($unit_id_tujuan)) {
            $flag_unit_tujuan = 1;
        } else {
            $flag_unit_tujuan = 0;
        }
        
        
        $result = $this->Open(
            $this->mSqlQueries['get_data_movement_optimized'].$this->mSqlQueries['get_limit'], array(
            $ta_id,
            $unit_id_asal,$flag_unit_asal,
            $unit_id_tujuan,$flag_unit_tujuan,
            '%' . trim($kode) . '%','%' . trim($kode) . '%',$asal ,
            '%' . trim($kode) . '%','%' . trim($kode) . '%', $tujuan,
            $bulan_asal,$flag_bulan_asal,
            $bulan_tujuan,$flag_bulan_tujuan,
            $offset,
            $limit
            )
        );
        /**

        $result = $this->Open(
            $this->mSqlQueries['get_data_movement'], array(
            $ta_id,
            '%' . trim($kode) . '%','%' . trim($kode) . '%',(int) ($asal == ''),
            $ta_id,
            $unit_id_asal,$flag_unit_asal,
            $bulan_asal,$flag_bulan_asal,
            $ta_id,
            '%' . trim($kode) . '%','%' . trim($kode) . '%', (int) ($tujuan == ''),
            $ta_id,
            $unit_id_tujuan,$flag_unit_tujuan,
            $bulan_tujuan,$flag_bulan_tujuan,
            $offset,
            $limit
            )
        );
         */
        return $result;
    }

    public function GetDataMovementExport($param=array()) {
        // $this->SetDebugOn();
        extract($param);   
                extract($param);   
       
        if($type =='asal') {
            $asal = 0;
        } else {
            $asal = 1;
        }
        
        if($type =='tujuan') {
            $tujuan = 0;
        } else {
            $tujuan = 1;
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

        if($unit_id_asal == 'all' || $unit_id_asal == '' || empty($unit_id_asal)) {
            $flag_unit_asal = 1;
        } else {
            $flag_unit_asal = 0;
        }

        if($unit_id_tujuan == 'all' || $unit_id_tujuan == '' || empty($unit_id_tujuan)) {
            $flag_unit_tujuan = 1;
        } else {
            $flag_unit_tujuan = 0;
        }
        
        $result = $this->Open(
            $this->mSqlQueries['get_data_movement_optimized'], array(
            $ta_id,
            $unit_id_asal,$flag_unit_asal,
            $unit_id_tujuan,$flag_unit_tujuan,
            '%' . trim($kode) . '%','%' . trim($kode) . '%',$asal ,
            '%' . trim($kode) . '%','%' . trim($kode) . '%', $tujuan,
            $bulan_asal,$flag_bulan_asal,
            $bulan_tujuan,$flag_bulan_tujuan,
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

    public function DoDeleteApbnp($id) {
        $result = true;
        $this->StartTrans();
        if (!$id) {
            $result &= false;
        }

        $result &= $this->Execute($this->mSqlQueries['do_delete_apbnp_detail'], array(
            $id
        ));

        $result &= $this->Execute($this->mSqlQueries['do_delete_apbnp'], array(
            $id
        ));
        return $this->EndTrans($result);
    }

    public function DetailApbnp($id) {
        $result = $this->Open($this->mSqlQueries['get_detail_apbnp'], array($id));

        return $result;
    }

    /**
     * untuk proses update data
     */
    public function GetComboBas() {
        $result = $this->Open($this->mSqlQueries['get_combo_bas'], array());
        return $result;
    }

    public function GetTahunAnggaranAktif() {
        $result = $this->Open($this->mSqlQueries['get_tahun_anggaran_aktif'], array());
        return $result[0];
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
   
    public function UpdateHistoryMovement($params = array()) {
        //$this->SetDebugOn();
        $result = true;
        $userId = $this->mUserId;
        $this->StartTrans();
        if (!is_array($params)) {
            $result &= false;
        }


        $result = $this->Execute($this->mSqlQueries['update_history_movement'], array(
            $params['tahunAnggaranId'],
            $params['unitKerjaId'],
            $params['kegrefId'],
            $params['unitKerjaIdTujuan'],
            $params['kegrefIdTujuan'],
            $params['nominal_movement'],
            $userId,
            $params['historyId']
        ));

        $mvID = $params['historyId'];

        //bersihkan data detail apbnp        
        $isApbnpNotEmpty = $this->GetDataApbnpDetail($mvID);
        if ($isApbnpNotEmpty == true) {
            $result &= $this->DeleteApbnpDetail($mvID);
        }

        //untuk detail belanja asal
        if (!empty($params['KOMP'])) {
            foreach ($params['KOMP'] as $komponen) {
                $result &= $this->Execute($this->mSqlQueries['insert_into_apbnp_detail'], array(
                    $mvID,
                    $komponen['rp_id'],
                    $komponen['nominal_hid'],
                    $komponen['nominal'],
                    'asal',
                    $userId
                ));
            }
        }

        //untuk detail belanja tujuan
        if (!empty($params['KOMPTUJUAN'])) {
            foreach ($params['KOMPTUJUAN'] as $komponen) {
                $result &= $this->Execute($this->mSqlQueries['insert_into_apbnp_detail'], array(
                    $mvID,
                    $komponen['rp_id'],
                    $komponen['nominal_hid'],
                    $komponen['nominal'],
                    'tujuan',
                    $userId
                ));
            }
        }
        return $this->EndTrans($result);
    }

    protected function GetDataApbnpDetail($idParent) {

        $result = $this->Open($this->mSqlQueries['get_data_apbnp_detail'], array($idParent));
        if (!empty($result)) {
            return true;
        } else {
            return false;
        }
    }

    public function DeleteApbnpDetail($id) {
        $result = $this->Execute($this->mSqlQueries['do_delete_apbnp_detail'], array($id));
        return $result;
    }

    function _dateToIndo($date) {
        $indonesian_months = array(
            'N/A',
            'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'Nopember',
            'Desember'
        );

        if (preg_match("/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})/", $date, $patch)) {
            $year = (int) $patch[1];
            $month = (int) $patch[2];
            $month = $indonesian_months[(int) $patch[2]];
            $day = (int) $patch[3];
            $hour = (int) $patch[4];
            $min = (int) $patch[5];
            $sec = (int) $patch[6];

            $return = $day . ' ' . $month . ' ' . $year;
        } elseif (preg_match("/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/", $date, $patch)) {
            $year = (int) $patch[1];
            $month = (int) $patch[2];
            $month = $indonesian_months[$month];
            $day = (int) $patch[3];

            $return = $day . ' ' . $month . ' ' . $year;
        } else {
            $return = (int) $date;
        }
        return $return;
    }

}

?>