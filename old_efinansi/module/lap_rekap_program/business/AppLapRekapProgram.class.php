<?php

class AppLapRekapProgram extends Database
{
	protected $mSqlFile;
	public $_POST;
	public $_GET;

	function __construct($connectionNumber=0)
	{
		$this->_POST 		= is_object($_POST) ? $_POST->AsArray() : $_POST;
		$this->_GET 		= is_object($_GET) ? $_GET->AsArray() : $_GET;
		$this->mSqlFile 	= 'module/lap_rekap_program/business/applaprekapprogram.sql.php';
		parent::__construct($connectionNumber);
		//$this->mrDbEngine->debug = 1;
		//$this->SetDebugOn();
	}

   function GetUnitIdentity($id)
   {
      return $this->Open($this->mSqlQueries['get_unit_kerja_id'],array($id));
   }


	function GetData($offset, $limit, $periode, $program='', $jenis_kegiatan='', $unitkerja='', $role='')
	{
		if($jenis_kegiatan != 'all' && $jenis_kegiatan != '')
         $str_jenis_kegiatan=" AND d.subprogJeniskegId='".$jenis_kegiatan."' ";
		else
         $str_jenis_kegiatan = "";

		if($program != '')
         $str_program=" AND e.programId=$program ";
		else
         $str_program = "";


		if($unitkerja != "") {
			/**
			if($role == "OperatorUnit") {
            $str_unitkerja=" AND ((g.unitkerjaId LIKE  $unitkerja OR f.unitkerjaId LIKE $unitkerja)
				OR (g.unitkerjaParentId LIKE $unitkerja  OR f.unitkerjaParentId LIKE $unitkerja))";
				//$str_unitkerja = " AND (kegUnitkerjaId=$unitkerja OR kegUnitkerjaId
				//IN (SELECT unitkerjaId FROM unit_kerja_ref WHERE unitkerjaParentId=$unitkerja)) ";
			} else {
            $str_unitkerja=" AND ((g.unitkerjaId LIKE  $unitkerja OR f.unitkerjaId LIKE $unitkerja)
					OR (g.unitkerjaParentId LIKE $unitkerja  OR f.unitkerjaParentId LIKE $unitkerja))";
			}
			*/
			$str_unitkerja =" AND
				(
				g.unitkerjaKodeSistem LIKE
				CONCAT((
					SELECT
						unitkerjaKodeSistem
					FROM
						unit_kerja_ref
					WHERE
						unit_kerja_ref.unitkerjaId='".$unitkerja."'),'.','%')
				OR
				g.unitkerjaKodeSistem LIKE
				CONCAT((
					SELECT
						unitkerjaKodeSistem
					FROM
						unit_kerja_ref
					WHERE
						unit_kerja_ref.unitkerjaId='".$unitkerja."'))
				)
			";
		} else {
			$str_unitkerja = "";
		}
      /*
		if($unitkerja != '') {
         //$str_unitkerja=" AND kegUnitkerjaId=$unitkerja ";
         $str_unitkerja=" AND (g.unitkerjaId=$unitkerja OR g.unitkerjaParentId=$unitkerja) ";
		} else {
         $str_unitkerja = "";
      }
      */
		$sql = sprintf($this->mSqlQueries['get_data'],
						$periode,
						$str_jenis_kegiatan,
						$str_program,
						$str_unitkerja,
						$offset,
						$limit);
		//echo $sql;
		$result = $this->Open($sql, array());
		//print_r($result);
		return $result;
	}

	function GetCountData($periode, $program='', $jenis_kegiatan='', $unitkerja='', $role='')
	{
		if($jenis_kegiatan != 'all' && $jenis_kegiatan != '')
         $str_jenis_kegiatan=" AND d.subprogJeniskegId=$jenis_kegiatan ";
		else
         $str_jenis_kegiatan = "";

		if($program != '')
         $str_program=" AND e.programId=$program ";
		else
         $str_program = "";


		if($unitkerja != "") {
			/**
			if($role == "OperatorUnit") {
            $str_unitkerja=" AND (g.unitkerjaId=$unitkerja OR g.unitkerjaParentId=$unitkerja) ";
				//$str_unitkerja = " AND (kegUnitkerjaId=$unitkerja OR kegUnitkerjaId
				//IN (SELECT unitkerjaId FROM unit_kerja_ref WHERE unitkerjaParentId=$unitkerja)) ";
			} else {
            $str_unitkerja=" AND (g.unitkerjaId=$unitkerja OR g.unitkerjaParentId=$unitkerja) ";
			}
			*/
			$str_unitkerja =" AND
				(
				g.unitkerjaKodeSistem LIKE
				CONCAT((
					SELECT
						unitkerjaKodeSistem
					FROM
						unit_kerja_ref
					WHERE
						unit_kerja_ref.unitkerjaId='".$unitkerja."'),'.','%')
				OR
				g.unitkerjaKodeSistem LIKE
				CONCAT((
					SELECT
						unitkerjaKodeSistem
					FROM
						unit_kerja_ref
					WHERE
						unit_kerja_ref.unitkerjaId='".$unitkerja."'))
				)
			";
		} else {
			$str_unitkerja = "";
		}
      	/*
		if($unitkerja != '') {
         //$str_unitkerja=" AND kegUnitkerjaId=$unitkerja ";
         $str_unitkerja=" AND (g.unitkerjaId=$unitkerja OR g.unitkerjaParentId=$unitkerja) ";
		} else {
         $str_unitkerja = "";
      	}
      	*/
		$sql = sprintf($this->mSqlQueries['get_count_data'],
						$periode,
						$str_jenis_kegiatan,
						$str_program,
						$str_unitkerja);

		$result = $this->Open($sql, array());
		//echo $sql;
		if (!$result) {
			return 0;
		} else {
			return $result[0]['total'];
		}
	}

	function GetCetakData($periode, $program='', $jenis_kegiatan='', $unitkerja='',$role='')
	{
		if($jenis_kegiatan != 'all' && $jenis_kegiatan != '')
         $str_jenis_kegiatan=" AND d.subprogJeniskegId=$jenis_kegiatan ";
		else
         $str_jenis_kegiatan = "";

		if($program != '')
         $str_program=" AND e.programId=$program ";
		else
         $str_program = "";

		if($unitkerja != "") {
			/**
			if($role == "OperatorUnit") {
            $str_unitkerja=" AND (g.unitkerjaId=$unitkerja OR g.unitkerjaParentId=$unitkerja) ";
				//$str_unitkerja = " AND (kegUnitkerjaId=$unitkerja OR kegUnitkerjaId
				//IN (SELECT unitkerjaId FROM unit_kerja_ref WHERE unitkerjaParentId=$unitkerja)) ";
			} else {
            $str_unitkerja=" AND (g.unitkerjaId=$unitkerja OR g.unitkerjaParentId=$unitkerja) ";
			}
			**/
			$str_unitkerja =" AND
				(
				g.unitkerjaKodeSistem LIKE
				CONCAT((
					SELECT
						unitkerjaKodeSistem
					FROM
						unit_kerja_ref
					WHERE
						unit_kerja_ref.unitkerjaId='".$unitkerja."'),'.','%')
				OR
				g.unitkerjaKodeSistem LIKE
				CONCAT((
					SELECT
						unitkerjaKodeSistem
					FROM
						unit_kerja_ref
					WHERE
						unit_kerja_ref.unitkerjaId='".$unitkerja."'))
				)
			";
		} else {
			$str_unitkerja = "";
		}

      	$sql = sprintf($this->mSqlQueries['get_cetak_data'],
	  				 	$periode,
  						$str_jenis_kegiatan,
					  	$str_program,
					  	$str_unitkerja);
		$result = $this->Open($sql, array());
		//print_r($result);
		return $result;
	}

	function GetResume($periode, $program='', $jenis_kegiatan='', $unitkerja='', $role='')
	{
		if($jenis_kegiatan != 'all' && $jenis_kegiatan != '')
         $str_jenis_kegiatan=" AND d.subprogJeniskegId=$jenis_kegiatan ";
		else
         $str_jenis_kegiatan = "";

		if($program != '')
         $str_program=" AND e.programId=$program ";
		else
         $str_program = "";
		if($unitkerja != "") {
			/**
			if($role == "OperatorUnit") {
            $str_unitkerja=" AND (g.unitkerjaId=$unitkerja OR g.unitkerjaParentId=$unitkerja) ";
				//$str_unitkerja = " AND (kegUnitkerjaId=$unitkerja OR kegUnitkerjaId
				//IN (SELECT unitkerjaId FROM unit_kerja_ref WHERE unitkerjaParentId=$unitkerja)) ";
			} else {
            $str_unitkerja=" AND (g.unitkerjaId=$unitkerja OR g.unitkerjaParentId=$unitkerja) ";
			}
			*/
			$str_unitkerja =" AND
				(
				g.unitkerjaKodeSistem LIKE
				CONCAT((
					SELECT
						unitkerjaKodeSistem
					FROM
						unit_kerja_ref
					WHERE
						unit_kerja_ref.unitkerjaId='".$unitkerja."'),'.','%')
				OR
				g.unitkerjaKodeSistem LIKE
				CONCAT((
					SELECT
						unitkerjaKodeSistem
					FROM
						unit_kerja_ref
					WHERE
						unit_kerja_ref.unitkerjaId='".$unitkerja."'))
				)
			";
		} else {
			$str_unitkerja = "";
		}
		$sql = sprintf( $this->mSqlQueries['get_resume'],
						$periode,
						$str_jenis_kegiatan,
						$str_program,
						$str_unitkerja);

		$result = $this->Open($sql, array());
      	//echo $sql;
		//print_r($result);
		return $result;
	}

	function GetResumeKegiatan($periode, $program='', $jenis_kegiatan='', $unitkerja='', $role='')
	{
		if($jenis_kegiatan != 'all' && $jenis_kegiatan != '')
         $str_jenis_kegiatan=" AND d.subprogJeniskegId=$jenis_kegiatan ";
		else
         $str_jenis_kegiatan = "";

		if($program != '')
         $str_program=" AND e.programId=$program ";
		else
         $str_program = "";

		if($unitkerja != "") {
			$str_unitkerja =" AND
				(
				g.unitkerjaKodeSistem LIKE
				CONCAT((
					SELECT
						unitkerjaKodeSistem
					FROM
						unit_kerja_ref
					WHERE
						unit_kerja_ref.unitkerjaId='".$unitkerja."'),'.','%')
				OR
				g.unitkerjaKodeSistem LIKE
				CONCAT((
					SELECT
						unitkerjaKodeSistem
					FROM
						unit_kerja_ref
					WHERE
						unit_kerja_ref.unitkerjaId='".$unitkerja."'))
				)
			";
		} else {
			$str_unitkerja = "";
		}
		$sql = sprintf($this->mSqlQueries['get_resume_kegiatan'],
						$periode,
						$str_jenis_kegiatan,
						$str_program,
						$str_unitkerja);
		$result = $this->Open($sql, array());
		return $result;
	}

	//get combo tahun anggaran
	function GetComboTahunAnggaran() {
		$result = $this->Open($this->mSqlQueries['get_combo_tahun_anggaran'], array());
		return $result;
	}
	function GetTahunAnggaranAktif() {
		$result = $this->Open($this->mSqlQueries['get_tahun_anggaran_aktif'], array());
		return $result[0];
	}
	function GetComboJenisKegiatan() {
		$result = $this->Open($this->mSqlQueries['get_combo_jenis_kegiatan'], array());
		return $result;
	}
	function GetTahunAnggaranById($id) {
		$result = $this->Open($this->mSqlQueries['get_tahun_anggaran_by_id'], array($id));
		return $result[0];
	}
	function GetProgramById($id) {
		$result = $this->Open($this->mSqlQueries['get_program_by_id'], array($id));
      //print_r($result);
		return $result[0];
	}
	function GetUnitkerjaById($id) {
		$result = $this->Open($this->mSqlQueries['get_unitkerja_by_id'], array($id));
      //print_r($result);
		return $result[0];
	}
	function GetJenisKegiatanById($id) {
		$result = $this->Open($this->mSqlQueries['get_jenis_kegiatan_by_id'], array($id));
		return $result[0];
	}
}