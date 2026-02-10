<?php

/**
 * @package lap_realisasi_penerimaan_pnbp_pusat
 * Class AppLapPenerimaanPNBPPusat
 * @since 27 Maret 2012
 * @copyright 2012 Gamatechno
 */
 
 
class AppLapPenerimaanPNBPPusat extends Database 
{

	protected $mSqlFile= 'module/lap_realisasi_penerimaan_pnbp_pusat/business/applappenerimaanpnbppusat.sql.php';
    
    /**
     * unit pusat
     */
    protected $unitPusat = 1;

	public function __construct($connectionNumber=0) 
    {
		parent::__construct($connectionNumber);
	    //$this->setdebugOn();
	}

	public function GetCountData($tahun_anggaran) 
    {
    	$sql = sprintf($this->mSqlQueries['get_count_data'], 
                                $tahun_anggaran, 
                                $tahun_anggaran,
                                $this->unitPusat,'%',
                                $this->unitPusat
                                );
		$data = $this->Open($sql, array());
		if (!$data) {
			return 0;
		} else {
			return $data[0]['total'];
		}
	}


	public function GetDataRealisasiPNBP($tahunAnggaran,$startRec=0,$itemViewed=0) 
    {
		//$this->SetDebugOn();
      $sql = sprintf($this->mSqlQueries['get_data_realisasi_pnbp'], 
									$tahunAnggaran, 
									$this->unitPusat,'%', 
									$this->unitPusat,
                                    $startRec,
                                    $itemViewed);
		$data = $this->Open($sql, array());
		return $data;
	}

	public function GetDataRealisasiPNBPCetak($tahunAnggaran) 
    {
      $sql = sprintf($this->mSqlQueries['get_data_realisasi_pnbp_cetak'], 
									$tahunAnggaran, 
									$this->unitPusat,'%', 
									$this->unitPusat);
		$data = $this->Open($sql, array());
		return $data;
	}    
    public function GetTotalDataRealisasiPNBPPerBulan($tahunAnggaran)
    {
        $result = $this->Open($this->mSqlQueries['get_total_realisasi_pnbp_perbulan'],
                                array(
                                        $tahunAnggaran,
                                        $this->unitPusat,'%', 
									    $this->unitPusat
                                        ));
        return $result[0];
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
    
    public function GetUnitKerjaPusat()
    {
        $result = $this->Open($this->mSqlQueries['get_unit_kerja_pusat'],array($this->unitPusat));
        return $result[0]['nama'];
    }

}