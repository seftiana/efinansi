<?php

/**
 *
 * class KelompokJenisLaporanAnggaran
 * @package kelompok_laporan_anggaran
 * @subpackage business
 * @analyst nanang ruswianto <nanang@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @since 6 september 2012
 * @copyright 2012 Gamatechno Indonesia
 * 
 */
 
class KelompokJenisLaporanAnggaran extends Database
{
	protected $mSqlFile= 'module/kelompok_laporan_anggaran/business/kelompokjenislaporananggaran.sql.php';
	
	public function __construct($connectionNumber=0) 
	{
		parent::__construct($connectionNumber);
	}
	
	public function GetJenisLaporanCombo()
	{
		$result = $this->Open($this->mSqlQueries['get_data_jenis_laporan_combo'],array());
		return $result;
	}
	
	public function GetBentukTransaksiCombo($parentId)
	{
		$result = $this->Open($this->mSqlQueries['get_bentuk_transaksi_combo'],array($parentId));
		return $result;
	}
}

