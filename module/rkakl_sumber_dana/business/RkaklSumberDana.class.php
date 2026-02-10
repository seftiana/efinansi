<?php

/**
 * RkaklSumberDana.class.php
 * @copyright 2011 gamatechno
 */

/**
 * Class RkaklSumberDana
 * untuk menjalankan query-query pada tabel sumber dana
 */
class RkaklSumberDana extends Database
{

    protected $mSqlFile = 'module/rkakl_sumber_dana/business/rkaklsumberdana.sql.php';

    public function __construct($connectionNumber = 0)
    {
        parent::__construct($connectionNumber);
    }

    public function GetCountRkaklSumberDana($sumberDanaNama = '')
    {
        $count = $this->Open($this->mSqlQueries['get_count_rkakl_sumber_dana'], 
				array('%' . $sumberDanaNama . '%'));
        return $count['0']['total'];
    }

    public function GetRkaklSumberDana($sumberDanaNama = '', $start, $limit)
    {
        return $this->Open($this->mSqlQueries['get_rkakl_sumber_dana'], 
				array('%' . $sumberDanaNama . '%', $start, $limit));
    }

    public function GetRkaklSumberDanaById($id)
    {
        return $this->Open($this->mSqlQueries['get_rkakl_sumber_dana_by_id'], array($id));
    }

    public function AddRkaklSumberDana($sumberDanaNama, $statusAktif)
    {
        return $this->Execute($this->mSqlQueries['insert_rkakl_sumber_dana'], 
				array($sumberDanaNama, $statusAktif));
    }

    public function UpdateRkaklSumberDana($sumberDanaNama, $statusAktif, $id)
    {
        return $this->Execute($this->mSqlQueries['update_rkakl_sumber_dana'], 
				array($sumberDanaNama,$statusAktif, $id));
    }

    public function DeleteRkaklSumberDanaById($id)
    {
        return $this->Execute($this->mSqlQueries['delete_rkakl_sumber_dana'], array($id));
    }

    public function DeleteRkaklSumberDanaByArrayId($arrId)
    {
        $arrId = implode("', '", $arrId);
        $result = $this->Execute($this->mSqlQueries['delete_rkakl_sumber_dana_array'],
            	array($arrId));
        //echo $this->getLastError(); exit;
        return $result;
    }
}
