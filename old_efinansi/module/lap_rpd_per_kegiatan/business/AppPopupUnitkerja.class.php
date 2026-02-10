<?php

class AppPopupUnitkerja extends Database
{

   protected $mSqlFile= 'module/lap_rpd_per_kegiatan/business/apppopupunitkerja.sql.php';

   function __construct($connectionNumber=0)
   {
      parent::__construct($connectionNumber);
      //$this->SetDebugOn();
   }

   public function Count()
   {
      $return     = $this->Open($this->mSqlQueries['count'], array());

      if($return){
         return (int)$return[0]['count'];
      }else{
         return 0;
      }
   }

	function GetDataUnitkerja($offset, $limit, $kode='', $unitkerja='', $tipeunit='', $parentId){
		$return      = $this->Open($this->mSqlQueries['get_data_unitkerja'], array(
         '%'.$kode.'%',
         '%'.$unitkerja.'%',
         $tipeunit,
         (int)(strtolower($tipeunit) == 'all' OR $tipeunit == ''),
         $parentId,
         $parentId,
         $parentId,
         $offset,
         $limit
      ));

      return $return;
	}

	function GetCountDataUnitkerja ($kode='', $unitkerja='', $tipeunit='', $parentId)
    {
		$result = $this->Open(
                            $this->mSqlQueries['get_count_data_unitkerja'],
                                    array(
                                            $parentId,'%',
                                            $parentId,
                                            '%'.$kode.'%',
                                            '%'.$unitkerja.'%',
                                            '%'.$tipeunit.'%',));

		if (!$result) {
			return 0;
		} else {
			return $result[0]['total'];
		}
	}
	function GetDataTipeUnit($unitkerjaId = NULL)
    {
		$result = $this->Open($this->mSqlQueries['get_data_tipe_unit'], array());
		return $result;
	}
}
?>
