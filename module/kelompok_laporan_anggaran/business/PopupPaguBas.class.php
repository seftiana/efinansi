<?php

/**
 *
 * class PopupPaguBas
 * @package kelompok_laporan_anggaran
 * @subpackage business
 * @analyst nanang ruswianto <nanang@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @since 6 september 2012
 * @copyright 2012 Gamatechno Indonesia
 * 
 */

class PopupPaguBas extends Database 
{

	protected $mSqlFile= 'module/kelompok_laporan_anggaran/business/popuppagubas.sql.php';
   
	public function __construct($connectionNumber=0) 
	{
		parent::__construct($connectionNumber);     
	}


	public function GetDataPaguBasMak($offset, $limit, $nama) 
	{
		$result = $this->Open($this->mSqlQueries['get_data_pagu_bas_mak'], 
                array('%'.$nama.'%','%'.$nama.'%', $offset, $limit));
		return $result;
    }
   
    public function GetCountDataPaguBasMak($nama) 
    {
		$result = $this->Open($this->mSqlQueries['get_count_data_pagu_bas_mak'], 
								array('%'.$nama.'%','%'.$nama.'%'));
		if (!$result)
			return 0;
		else
			return $result[0]['total'];
	}
}
