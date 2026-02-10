<?php
/**
* @module lap_bukubesar
* @author Rendi Kasigi,
* @mail to rkasigi@gmail.com
* @copyright 2008-2011 Gamatechno
*/

class AppPopupCoa extends Database 
{

   protected $mSqlFile= 'module/lap_bukubesar/business/apppopupcoa.sql.php';
   
   public function __construct($connectionNumber=0) 
   {
      parent::__construct($connectionNumber);     
   }
//==GET==   
  
  	public function GetDataCoa($offset, $limit, $kode, $nama)
    {
        $result = $this->Open($this->mSqlQueries['get_data_coa'], array('%' . $kode .
            '%', '%' . $nama . '%', $offset, $limit));
        return $result;
    }
   
   	public function GetCountCoa($kode, $nama)
    {
        $result = $this->Open($this->mSqlQueries['get_count_coa'], array('%' . $kode .
            '%', '%' . $nama . '%'));
        if (!$result)
            return 0;
        else
            return $result[0]['total'];
    }
   

}