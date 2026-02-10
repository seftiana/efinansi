<?php

class AppUsulanKegiatan extends Database 
{
	protected $mSqlFile= 'module/usulan_kegiatan/business/appusulankegiatan.sql.php';

	public function __construct($connectionNumber=0) 
	{
		parent::__construct($connectionNumber);
		//$this->mrDbEngine->debug = 1;
		//$this->SetDebugOn();	

	}

	public function GetQueryKeren($sql,$params) 
	{
		foreach ($params as $k => $v) {
			if (is_array($v)) {
				$params[$k] = '~~' . join("~~,~~", $v) . '~~';
				$params[$k] = str_replace('~~', '\'', addslashes($params[$k]));
		
			} else {
				$params[$k] = addslashes($params[$k]);
			}
		}
		$param_serialized = '~~' . join("~~,~~", $params) . '~~';
		$param_serialized = str_replace('~~', '\'', addslashes($param_serialized));
		eval('$sql_parsed = sprintf("' . $sql . '", ' . $param_serialized . ');');
		return $sql_parsed;
	}
	
	public function GetQueryKeren2($sql,$params) 
	{
			/**
			 * Perbedaan dengan get query keren adalah dihilangkannya 
			 * fungsi addslashes pada param serialized, 
			 * sehingga query dapat menggunakan simbol quote atau double quote
			 */
		foreach ($params as $k => $v) {
			if (is_array($v)) {
				$params[$k] = '~~' . join("~~,~~", $v) . '~~';
				$params[$k] = str_replace('~~', '\'', addslashes($params[$k]));
		
			} else {
				$params[$k] = addslashes($params[$k]);
			}
		}
		$param_serialized = '~~' . join("~~,~~", $params) . '~~';
		$param_serialized = str_replace('~~', '\'', ($param_serialized));
		eval('$sql_parsed = sprintf("' . $sql . '", ' . $param_serialized . ');');
		return $sql_parsed;
	}
	

	public function GetDataUsulanKegiatan($offset, $limit, $tahun_anggaran='', 
						$unitkerjaId='', $unitkerjaKodeSistem='', $program='',$kodenama) 
	{
		if($tahun_anggaran != "") $str_tahun_anggaran = " AND programThanggarId = $tahun_anggaran ";
		else $str_tahun_anggaran = "";
		//kalau bukan pusat
			if($unitkerjaId != 1) {
			$str_unitkerja = " AND unitkerjaKodeSistem like '".$unitkerjaKodeSistem ."%' ";
		}
		//kalau pusat dan sedang memilih unit kerja lain
		else if($unitkerjaId == 1 && $unitkerjaKodeSistem != 1){
				$str_unitkerja = " AND unitkerjaKodeSistem like '".$unitkerjaKodeSistem ."%' ";
		}
		//kalau pusat dan sedang memilih unit kerja pusat --->  mengambil semua data yang ada
		else if($unitkerjaId == 1 && $unitkerjaKodeSistem == 1){
			$str_unitkerja = "";
		}
		
		if($program != "") $str_program = " AND programId = $program ";
		else $str_program = "";
		$sql = $this->GetQueryKeren2(
						$this->mSqlQueries['get_data_usulan_kegiatan'], 
						array(
								$kodenama,
								"%".$kodenama."%",
								$str_tahun_anggaran, 
								$str_unitkerja, 
								$str_program, 
								$offset, 
								$limit));
		//echo $sql;
	 // $debug = sprintf($sql, '%'.$kode.'%', '%'.$unitkerja.'%', $str_satker, $str_tipeunit, $offset, $limit);
	  //echo $debug;
	  //print_r($result);
		return $this->Open($sql, array());
	}

	public function GetCountDataUsulanKegiatan($tahun_anggaran='',$unitkerjaId='', 
						$unitkerjaKodeSistem='', $program='',$kodenama) 
	{
		if($tahun_anggaran != "") $str_tahun_anggaran = " AND programThanggarId = $tahun_anggaran ";
		else $str_tahun_anggaran = "";
		//print_r($unitkerjaKodeSistem);
		//kalau bukan pusat
		if($unitkerjaId != 1) {
			$str_unitkerja = " AND unitkerjaKodeSistem like '".$unitkerjaKodeSistem."%'";
		}
		//kalau pusat dan sedang memilih unit kerja lain
		else if($unitkerjaId == 1 && $unitkerjaKodeSistem != 1){
				$str_unitkerja = " AND unitkerjaKodeSistem like '".$unitkerjaKodeSistem ."%' ";
		}
		//kalau pusat dan sedang memilih unit kerja pusat --->  mengambil semua data yang ada
		else if($unitkerjaId == 1 && $unitkerjaKodeSistem == 1){
			$str_unitkerja = "";
		}
		
		if($program != "") $str_program = " AND programId = $program ";
		else $str_program = "";
		$sql = $this->GetQueryKeren2(
							$this->mSqlQueries['get_count_data_usulan_kegiatan'], 
							array(
									$kodenama,
									"%".$kodenama."%",
									$str_tahun_anggaran, 
									$str_unitkerja, 
									$str_program));
		//echo $sql;
		$result = $this->Open($sql, array());
		if (!$result) {
			return 0;
		} else {
			return $result[0]['total'];
		}
	}

	public function GetDataUsulanKegiatanById($id) 
	{
 		$ret = $this->Open($this->mSqlQueries['get_data_usulan_kegiatan_by_id'], array($id));
	  	return $ret[0];
	}

	public function GetUnitKerjaPimpinanById($id) 
	{
      	$ret = $this->Open($this->mSqlQueries['get_unit_kerja_pimpinan_by_id'], array($id));
	  	return $ret[0];
	}

	public function GetSatkerPimpinanById($id) 
	{
      	$ret = $this->Open($this->mSqlQueries['get_satker_pimpinan_by_id'], array($id));
	  	return $ret[0];
	}

	public function DoAddUsulanKegiatan($tahun_anggaran, $unitkerja, 
				$program, $latar_belakang, $indikator, $baseline, $final, 
				$satker_pimpinan, $unitkerja_pimpinan, $nama_pic) 
	{
		$id_user		= Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
		$result = $this->Execute(
						$this->mSqlQueries['do_add_usulan_kegiatan'], 
						array(
								$unitkerja, 
								$program, 
								$latar_belakang, 
								$indikator, 
								$baseline, 
								$final, 
								$tahun_anggaran,
								$satker_pimpinan, 
								$unitkerja_pimpinan, 
								$nama_pic,
								$id_user
								));

		//$debug = sprintf($this->mSqlQueries['do_add_usulan_kegiatan'], $unitkerja, $program, $latar_belakang, $tahun_anggaran);
		//echo $debug;
		return $result;
	}
    
    public function CheckKegiatan($unitkerja,$program,$th_anggaran)
    {
        $return = $this->Open($this->mSqlQueries['check_kegiatan'], array($unitkerja,$program, $th_anggaran));
        
        return $return[0]['kegiatan_count'];
    }
	public function DoUpdateUsulanKegiatan($tahun_anggaran, $unitkerja, $program, 
				$latar_belakang, $indikator, $baseline, $final, $satker_pimpinan, 
				$unitkerja_pimpinan, $nama_pic, $kegiatan_id) 
	{
		$id_user		= Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
		$result = $this->Execute(
						$this->mSqlQueries['do_update_usulan_kegiatan'], 
						array(
								$unitkerja, 
								$program, 
								$latar_belakang, 
								$indikator, 
								$baseline, 
								$final, 
								$tahun_anggaran, 
								$satker_pimpinan, 
								$unitkerja_pimpinan, 
								$nama_pic,
								$id_user, 
								$kegiatan_id
								));
		//$debug = sprintf($this->mSqlQueries['do_update_usulan_kegiatan'], $unitkerja, $program, $latar_belakang, $tahun_anggaran, $kegiatan_id);
		//echo $debug;
		return $result;
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

	public function DoDeleteUsulanKegiatanById($kegiatanId) 
	{
		$result=$this->Execute(
							$this->mSqlQueries['do_delete_usulankegiatan_by_id'], 
							array($kegiatanId));
		return $result;
	}
	public function DoDeleteUsulanKegiatanByArrayId($arrKegiatanId) 
	{
		$unitkerjaId = @implode("', '", $arrKegiatanId);
		$result=$this->Execute(
							$this->mSqlQueries['do_deleteusulankegiatan_by_array_id'], 
							array($arrKegiatanId));
		return $result;
	}
	
	public function GetUnit()
	{
		$userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());		
		$result = $this->Open($this->mSqlQueries['get_unit'], array($userId));
		return $result;	
	}
	
	/**
	 * Fungsi GetTotalSunUnit
	 * Untuk mendapatkan total sub unit
	 * add
	 * @since 2 Januari 2012
	 * @access public
	 */
	 public function GetTotalSubUnit($unitParentId)
	 {
	 	$total = $this->Open($this->mSqlQueries['get_total_sub_unit'],array($unitParentId));
	 	return $total[0]['total'];
	 }
/*

	function GetDataUsulanKegiatan($offset, $limit, $usulan_kegiatan='') {
		$result = $this->Open($this->mSqlQueries['get_data_usulan_kegiatan'], array('%'.$usulan_kegiatan.'%', $offset, $limit));
		return $result;
	}

	function GetCountDataUsulanKegiatan($usulan_kegiatan) {
		$result = $this->Open($this->mSqlQueries['get_count_data_usulan_kegiatan'], array('%'.$usulan_kegiatan.'%'));
		if (!$result) {
			return 0;
		} else {
			return $result[0]['total'];
		}
	}

	function GetDataUsulanKegiatanById($usulan_kegiatanId) {
		$result = $this->Open($this->mSqlQueries['get_data_usulan_kegiatan_by_id'], array($usulan_kegiatanId));
		return $result;
	}

	function GetDataUsulanKegiatanByArrayId($arrUsulanKegiatanId) {
		$usulan_kegiatanId = implode("', '", $arrUsulanKegiatanId);
		$result = $this->Open($this->mSqlQueries['get_data_usulan_kegiatan_by_array_id'], array($usulan_kegiatanId));
		return $result;
	}

//===DO==

	function DoAddUsulanKegiatan($usulan_kegiatanNama) {
		$result = $this->Execute($this->mSqlQueries['do_add_usulan_kegiatan'], array($usulan_kegiatanNama));
		return $result;
	}

	function DoUpdateUsulanKegiatan($usulan_kegiatanNama, $usulan_kegiatanId) {
		$result = $this->Execute($this->mSqlQueries['do_update_usulan_kegiatan'], array($usulan_kegiatanNama, $usulan_kegiatanId));
	  //$debug = sprintf($this->mSqlQueries['do_update_usulan_kegiatan'], $usulan_kegiatanKode, $usulan_kegiatanNama, $tipeunit, $satker, $usulan_kegiatanId);
	  //echo $debug;
		return $result;
	}

	function DoDeleteUsulanKegiatan($usulan_kegiatanId) {
		$result=$this->Execute($this->mSqlQueries['do_delete_usulan_kegiatan'], array($usulan_kegiatanId));
		return $result;
	}

	function DoDeleteUsulanKegiatanByArrayId($arrUsulanKegiatanId) {
		$usulan_kegiatanId = implode("', '", $arrUsulanKegiatanId);
		$result=$this->Execute($this->mSqlQueries['do_delete_usulan_kegiatan_by_array_id'], array($usulan_kegiatanId));
		return $result;
	}
	*/
}
