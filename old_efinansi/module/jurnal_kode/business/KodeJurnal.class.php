<?php

class KodeJurnal extends Database {

    protected $mSqlFile = 'module/jurnal_kode/business/kode_jurnal.sql.php';

    function __construct($connectionNumber = 0) {
        parent::__construct($connectionNumber);
        //$this->mrDbEngine->debug = 1;
    }

    function GetData($offset, $limit, $nama = '') {
        return $this->Open($this->mSqlQueries['get_data'], array(
                    '%' . $nama . '%',
                    '%' . $nama . '%',
                    $offset,
                    $limit
        ));
    }

    function GetCountData($nama) {
        $result = $this->Open($this->mSqlQueries['get_count_data'], array(
            '%' . $nama . '%',
            '%' . $nama . '%'
        ));

        if (!$result) {
            return 0;
        } else {
            return $result[0]['total'];
        }
    }

    function GetDataById($id) {
        $result = $this->Open($this->mSqlQueries['get_data_by_id'], array($id));
        return $result;
    }

//===DO==

    function DoAddData($kode, $nama, $detilkode, $statusAktif,$idJB,$namaJB,$mc) {
        $bkkAdded = $this->Execute($this->mSqlQueries['do_add_data'], array(
            $kode, 
            $nama, 
            $statusAktif,
            $idJB,
            $namaJB,
            $mc
        ));
        
        $result = $this->LastInsertId();
        if ($result != "") {
            $jurkodeid = $result;
            $result = true;
            if (is_array($detilkode)) {
                for ($i = 0; $i < count($detilkode['tambah']); $i++) {
                    $coaid = $detilkode['tambah'][$i]['id'];
                    $isdebet = $detilkode['tambah'][$i]['isdebet'];
                    $result = $this->Execute($this->mSqlQueries['do_add_detil_jurnal'], array($jurkodeid, $coaid, $isdebet));
                }
            }
        }
        return $result;
    }

    function DoUpdateData($kode, $nama, $id, $detilkode, $statusAktif,$idJB,$namaJB,$mc) {
        $result = $this->Execute($this->mSqlQueries['do_update_data'], array(
            $kode, 
            $nama, 
            $statusAktif,
            $idJB,
            $namaJB,
            $mc,
            $id
        ));
        
        //$debug = sprintf($this->mSqlQueries['do_update_gaji'], $gajiKode, $gajiNama, $tipeunit, $satker, $gajiId);
        //echo $debug;

        if ($result) {
            if (is_array($detilkode)) {
                $jurkodeid = $id;
                for ($i = 0; $i < count($detilkode['tambah']); $i++) {
                    $coaid = $detilkode['tambah'][$i]['id'];
                    $isdebet = $detilkode['tambah'][$i]['isdebet'];
                    $result = $this->Execute($this->mSqlQueries['do_add_detil_jurnal'], array($jurkodeid, $coaid, $isdebet));
                }
            }
        }
        return $result;
    }

    function DoDeleteData($id) {
        $result = $this->Execute($this->mSqlQueries['do_delete_data'], array($id));
        return $result;
    }

    function DoDeleteDataByArrayId($arrId) {
        $id = implode("', '", $arrId);
        $result = $this->Execute($this->mSqlQueries['do_delete_data_by_array_id'], array($id));
        return $result;
    }

}

?>