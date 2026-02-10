<?php

class AppPopupCoa extends Database
{

    protected $mSqlFile = 'module/referensi_mak/business/apppopupcoa.sql.php';

    function __construct($connectionNumber = 0)
    {
        parent::__construct($connectionNumber);
    }


    //untuk popup coa----
    function GetDataCoa($offset, $limit, $kode, $nama)
    {
        $result = $this->Open($this->mSqlQueries['get_data_coa'], array('%' . $kode .
            '%', '%' . $nama . '%', $offset, $limit));
        return $result;
    }

    function GetCountCoa($kode, $nama)
    {
        $result = $this->Open($this->mSqlQueries['get_count_coa'], array('%' . $kode .
            '%', '%' . $nama . '%'));
        if (!$result)
            return 0;
        else
            return $result[0]['total'];
    }
    //end untuk popup coa----
}
?>
