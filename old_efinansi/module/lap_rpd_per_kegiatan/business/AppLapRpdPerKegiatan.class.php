<?php

/**
 * class AppLapRpdPerKegiatan
 * @package lap_rpd_per_kegiatan
 * @subpackage business
 * @todo untuk menjalankan perintah query
 * @since mei 2012
 * @copyright 2012 Gamatechno Indonesia
 * @author noor hadi <noor.hadi@gamatechno.com>
 */
 
class AppLapRpd extends Database 
{

	protected $mSqlFile= 'module/lap_rpd_per_kegiatan/business/applaprpdperkegiatan.sql.php';

	public function __construct($connectionNumber=0) 
    {
		parent::__construct($connectionNumber);
		//$this->mrDbEngine->debug = 1;
		//$this->SetDebugOn();
	}
    
	public function GetDataRpd($offset, $limit, $tahun_anggaran, $unitkerja='') 
    {
        $offset = intval($offset);
        $limit = intval($limit);
    
		$data = $this->Open(
                            $this->mSqlQueries['get_rpd'], 
                                array(
                                        $tahun_anggaran,
                                        $unitkerja,'%',
                                        $unitkerja ,
                                        $offset, 
                                        $limit));
	
		return $data;
	}

	public function GetDataRpdCetak($tahun_anggaran, $unitkerjaId) 
    {
        
		$result = $this->Open($this->mSqlQueries['get_data_rpd_cetak'], 
                                array(
                                        $tahun_anggaran,
                                        $unitkerjaId,'%',
                                        $unitkerjaId ));

		
		return $result;
	}

	public function GetCountDataRpd($tahun_anggaran, $unitkerja='') 
    {

		$result = $this->Open(
                        $this->mSqlQueries['get_count_rpd'], 
                                array(
                                            $tahun_anggaran,
                                            $unitkerja,'%',
                                            $unitkerja ));
		if (!$result) {
			return 0;
		} else {
			return $result[0]['total'];
		}
	}
	//get combo tahun anggaran
	public function GetComboTahunAnggaran() {
		$result = $this->Open($this->mSqlQueries['get_combo_tahun_anggaran'], array());
		return $result;
	}
    
	public function GetTahunAnggaranAktif() {
		$result = $this->Open($this->mSqlQueries['get_tahun_anggaran_aktif'], array());
		return $result[0];
	}

	public function GetTahunAnggaranCetak($thId) {
		$result = $this->Open($this->mSqlQueries['get_tahun_anggaran_cetak'], array($thId));
		return $result[0];
	}

	public function GetUnitKerja($unirkerjaId) {
		$result = $this->Open($this->mSqlQueries['get_unit_kerja'], array($unirkerjaId));
		return $result[0];
	}

	public function GetMak($tahun_anggaran, $unitkerja='') 
    {
		$data = $this->Open(
                            $this->mSqlQueries['get_mak'], 
                                array(
                                        $tahun_anggaran,
                                        $unitkerja,'%',
                                        $unitkerja ));
		return $data;
	}
    
}