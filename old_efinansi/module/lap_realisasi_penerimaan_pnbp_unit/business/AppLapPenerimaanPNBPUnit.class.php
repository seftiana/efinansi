<?php

/**
 * @package lap_realisasi_penerimaan_pnbp_unit
 * Class AppLapPenerimaanPNBPUnit
 * @since 27 Maret 2012
 * @copyright 2012 Gamatechno
 */


class AppLapPenerimaanPNBPUnit extends Database
{

	protected $mSqlFile;
   public $_POST;
   public $_GET;

	public function __construct($connectionNumber=0)
   {
      $this->mSqlFile   = 'module/lap_realisasi_penerimaan_pnbp_unit/business/applappenerimaanpnbpunit.sql.php';
      $this->_POST      = is_object($_POST) ? $_POST->AsArray() : $_POST;
      $this->_GET       = is_object($_GET) ? $_GET->AsArray() : $_GET;
		parent::__construct($connectionNumber);
	    //$this->setdebugOn();
	}

	public function GetCountData($tahun_anggaran, $unitkerja='')
    {
    	$sql = sprintf($this->mSqlQueries['get_count_data'],
                                $tahun_anggaran,
                                $tahun_anggaran,
                                $unitkerja,'%',
                                $unitkerja
                                );
		$data = $this->Open($sql, array());
		if (!$data) {
			return 0;
		} else {
			return $data[0]['total'];
		}
	}

	public function GetDataRealisasiPNBP($tahunAnggaran, $unitkerja,$startRec=0,$itemViewed=0)
    {
        $sql = sprintf($this->mSqlQueries['get_data_realisasi_pnbp'],
									$tahunAnggaran,
									$unitkerja,'%',
									$unitkerja,
                                    $startRec,
                                    $itemViewed);
		$data = $this->Open($sql, array());
		return $data;
	}

	public function GetDataRealisasiPNBPCetak($tahunAnggaran, $unitkerja)
    {
        $sql = sprintf($this->mSqlQueries['get_data_realisasi_pnbp_cetak'],
									$tahunAnggaran,
									$unitkerja,'%',
									$unitkerja);
		$data = $this->Open($sql, array());
		return $data;
	}

    public function GetTotalDataRealisasiPNBPPerBulan($tahunAnggaran, $unitkerja)
    {
        $sql = sprintf($this->mSqlQueries['get_total_data_realisasi_pnbp_per_bulan'],
									$tahunAnggaran,
									$unitkerja,'%',
									$unitkerja);
		$data = $this->Open($sql, array());
		return $data[0];

    }
	//get combo tahun anggaran
	public function GetComboTahunAnggaran()
    {
		$result = $this->Open($this->mSqlQueries['get_combo_tahun_anggaran'], array());
		return $result;
	}

	public function GetTahunAnggaranAktif()
    {
		$result = $this->Open($this->mSqlQueries['get_tahun_anggaran_aktif'], array());
		return $result[0];
	}

	public function GetTahunAnggaran($id)
    {
		$result = $this->Open($this->mSqlQueries['get_tahun_anggaran'], array($id));
		return $result[0];
	}


}