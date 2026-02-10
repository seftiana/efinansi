<?php

class PaguAnggaranUnit extends Database {

	protected $mSqlFile= 'module/pagu_anggaran_unit/business/paguanggaranunit.sql.php';
	
	function __construct($connectionNumber=0) {
		parent::__construct($connectionNumber);
		//$this->mrDbEngine->debug = 1;
	}

	function GetQueryKeren($sql,$params) {
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

	function GetDataPaguAnggaranUnit($offset, $limit, $tahun_anggaran='', $satker='', $unitkerja='') 
    { 
        /**
		if($tahun_anggaran != "") $str_tahun_anggaran = " AND paguAnggUnitThAnggaranId = $tahun_anggaran ";
		else $str_tahun_anggaran = "";
		if($unitkerja != "") {
			$str_unitkerja = " AND (unitkerjaId=$unitkerja OR tempUnitId=$unitkerja) ";
		} elseif($satker != "") {
			$str_unitkerja = " AND (unitkerjaId=$satker OR tempUnitId=$satker) ";
		} else {
			$str_unitkerja = "";
		}
		*/
		$sql = sprintf($this->mSqlQueries['get_data_pagu'],
                                $tahun_anggaran, 
                                $unitkerja,'%',
                                $unitkerja, 
                                $offset, 
                                $limit);
		//echo $sql;
	 // $debug = sprintf($sql, '%'.$kode.'%', '%'.$unitkerja.'%', $str_satker, $str_tipeunit, $offset, $limit);
	  //echo $debug;
	  //print_r($result);
		return $this->Open($sql, array());
	}

	function GetCountDataPaguAnggaranUnit($tahun_anggaran='', $satker='', $unitkerja='') 
    {
        /**
		if($tahun_anggaran != "") $str_tahun_anggaran = " AND paguAnggUnitThAnggaranId = $tahun_anggaran ";
		else $str_tahun_anggaran = "";
		if($unitkerja != "") {
			$str_unitkerja = " AND (unitkerjaId=$unitkerja OR tempUnitId=$unitkerja) ";
		} elseif($satker != "") {
			$str_unitkerja = " AND (unitkerjaId=$satker OR tempUnitId=$satker) ";
		} else {
			$str_unitkerja = "";
		}
        */
		$sql = sprintf($this->mSqlQueries['get_count_data_pagu'],
                                        $tahun_anggaran,
                                        $unitkerja,'%',
                                        $unitkerja);
		//echo $sql;
		$result = $this->Open($sql, array());
		if (!$result) {
			return 0;
		} else {
			return $result[0]['total'];
		}
	}

	function GetDataPaguAnggaranUnitById($id) {
      $ret = $this->Open($this->mSqlQueries['get_data_pagu_by_id'], array($id));
	  return $ret[0];
	}
		
	function DoAddPaguAnggaranUnit($tahun_anggaran, $unitkerja, $bas, $nominal,$sumber_dana,$pagu_tersedia) {
		$result = $this->Execute($this->mSqlQueries['do_add_pagu'], array($unitkerja, $tahun_anggaran, $bas, $nominal,$sumber_dana));
		//$debug = sprintf($this->mSqlQueries['do_add_usulan_kegiatan'], $unitkerja, $program, $latar_belakang, $tahun_anggaran);
		//echo $debug;
		return $result;
	}

	function DoUpdatePaguAnggaranUnit($tahun_anggaran, $unitkerja, $bas, $nominal, $sumber_dana,$pagu_tersedia,$pagu_id) {
		$result = $this->Execute($this->mSqlQueries['do_update_pagu'], array($unitkerja, $tahun_anggaran, $bas, $nominal,$sumber_dana,$pagu_tersedia, $pagu_id));
		//$debug = sprintf($this->mSqlQueries['do_update_usulan_kegiatan'], $unitkerja, $program, $latar_belakang, $tahun_anggaran, $kegiatan_id);
		//echo $debug;
		return $result;
	}
	
	function DoCopyPaguAnggaranUnitNaik($tahun_anggaran_tujuan, $nilai_perubahan, $tahun_anggaran_asal, $unitkerja) {
		$result = $this->Execute($this->mSqlQueries['do_copy_pagu_naik'], array($tahun_anggaran_tujuan, $nilai_perubahan,$tahun_anggaran_asal, $unitkerja));
		//$debug = sprintf($this->mSqlQueries['do_copy_pagu_naik'], $tahun_anggaran_tujuan, $nilai_perubahan,$tahun_anggaran_asal, $unitkerja);
		//echo $debug;exit;
		return $result;
	}
	
	function DoCopyPaguAnggaranUnitTurun($tahun_anggaran_tujuan, $nilai_perubahan, $tahun_anggaran_asal, $unitkerja) {
		$result = $this->Execute($this->mSqlQueries['do_copy_pagu_turun'], array($tahun_anggaran_tujuan, $nilai_perubahan,$tahun_anggaran_asal, $unitkerja));
		return $result;
	}

	//get combo tahun anggaran
	function GetComboTahunAnggaran() {
		$result = $this->Open($this->mSqlQueries['get_combo_tahun_anggaran'], array());
		return $result;
	}
	
	function GetComboBas() {
		$result = $this->Open($this->mSqlQueries['get_combo_bas'], array());
		return $result;
	}

	function GetTahunAnggaranAktif() {
		$result = $this->Open($this->mSqlQueries['get_tahun_anggaran_aktif'], array());
		return $result[0];
	}
	
	function DoDeletePaguAnggaranUnitById($paguId) {
		$result=$this->Execute($this->mSqlQueries['do_delete_pagu_by_id'], array($paguId));
		return $result;
	}
	function DoDeletePaguAnggaranUnitByArrayId($arrPaguId) {
		$arrPaguId = @implode("', '", $arrPaguId);
		$result=$this->Execute($this->mSqlQueries['do_delete_pagu_by_array_id'], array($arrPaguId));
		return $result;
	}
	
   function kekata($x) 
	{
		$x = abs($x);
		$angka = array(
			"",
			"satu",
			"dua",
			"tiga",
			"empat",
			"lima",
			"enam",
			"tujuh",
			"delapan",
			"sembilan",
			"sepuluh",
			"sebelas"
		);
		$temp = "";
		
		if ($x < 12) 
		{
			$temp = " " . $angka[$x];
		}
		else 
		if ($x < 20) 
		{
			$temp = $this->kekata($x - 10) . " belas";
		}
		else 
		if ($x < 100) 
		{
			$temp = $this->kekata($x / 10) . " puluh" . $this->kekata($x % 10);
		}
		else 
		if ($x < 200) 
		{
			$temp = " seratus" . $this->kekata($x - 100);
		}
		else 
		if ($x < 1000) 
		{
			$temp = $this->kekata($x / 100) . " ratus" . $this->kekata($x % 100);
		}
		else 
		if ($x < 2000) 
		{
			$temp = " seribu" . $this->kekata($x - 1000);
		}
		else 
		if ($x < 1000000) 
		{
			$temp = $this->kekata($x / 1000) . " ribu" . $this->kekata($x % 1000);
		}
		else 
		if ($x < 1000000000) 
		{
			$temp = $this->kekata($x / 1000000) . " juta" . $this->kekata($x % 1000000);
		}
		else 
		if ($x < 1000000000000) 
		{
			$temp = $this->kekata($x / 1000000000) . " milyar" . $this->kekata(fmod($x, 1000000000));
		}
		else 
		if ($x < 1000000000000000) 
		{
			$temp = $this->kekata($x / 1000000000000) . " trilyun" . $this->kekata(fmod($x, 1000000000000));
		}
		
		return $temp;
	}
	
   function terbilang($x, $style = 4) 
	{
		
		if ($x < 0) 
		{
			$hasil = "minus " . trim($this->kekata($x));
		}
		else
		{
			$hasil = trim($this->kekata($x));
		}
		
		switch ($style) 
		{
		case 1:
			$hasil = strtoupper($hasil);
		break;
		case 2:
			$hasil = strtolower($hasil);
		break;
		case 3:
			$hasil = ucwords($hasil);
		break;
		default:
			$hasil = ucfirst($hasil);
		break;
		}
		
		return $hasil . ' Rupiah';
	}

}
?>
