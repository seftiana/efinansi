<?php

class Kegiatan extends Database 
{

	protected $mSqlFile= 'module/program_kegiatan/business/kegiatan.sql.php';

	public function __construct($connectionNumber=0) 
	{
		parent::__construct($connectionNumber);
		#$this->SetDebugOn();
	}
//==GET==

	public function GetJenisKegiatan()
	{
		return $this->Open($this->mSqlQueries['get_jenis_kegiatan'],array());
	}

	public function GetProgram($tahunId)
	{
		return $this->Open($this->mSqlQueries['get_data_program'],array($tahunId));
	}

	public function GetTahunAnggaranById($id)
	{
		$result = $this->Open($this->mSqlQueries['get_tahun_anggaran_by_id'],array($id));
		return $result['0'];
	}

    public function GetData ($offset, $limit, $data) 
	{
		if($data['program_id']=='')
			$result = $this->Open($this->mSqlQueries['get_data'], array('%'.$data['kode'].'%','%'.$data['nama'].'%',$offset,$limit));
		else
			$result = $this->Open($this->mSqlQueries['get_data_where_program_id'], array($data['program_id'],'%'.$data['kode'].'%','%'.$data['nama'].'%',$offset,$limit));

		//$this->mdebug(1);
		return $result;
	}

	public function GetCount ($data) 
	{
		if($data['program_id']=='')
			$result = $this->Open($this->mSqlQueries['get_count_data'], array('%'.$data['kode'],'%'.$data['nama'].'%'));
		else
			$result = $this->Open($this->mSqlQueries['get_count_data_where_program_id'], array($data['program_id'],'%'.$data['kode'].'%','%'.$data['nama'].'%'));

		if (!$result)
			return 0;
		else
			return $result[0]['total'];
	}

	public function GetDataById($Id) 
	{
		$result = $this->Open($this->mSqlQueries['get_data_by_id'], array($Id));
		return $result;
	}

	public function GetDataWhereTA($offset,$limit,$data) 
	{
		$result = $this->Open($this->mSqlQueries['get_data_where_ta'], array($data['ta_id'],'%'.$data['nama'].'%',$offset,$limit));
		return $result;
	}

	public function GetCountDataWhereTA($data) 
	{
		$result = $this->Open($this->mSqlQueries['get_count_data_where_ta'], array($data['ta_id'],'%'.$data['nama'].'%'));
		if (!$result)
			return 0;
		else
			return $result[0]['total'];
	}

	public function GetDataTahunAnggaran($idaktif) 
	{
		if(trim($idaktif)=='') {
			$id = $this->Open($this->mSqlQueries['get_ta_aktif'],array());
			if($id) {
				$idaktif = $id[0]['id'];
			}
		}
		$result = $this->Open($this->mSqlQueries['get_data_ta'],array());
		return $result;
	}
   
	public function GetKodeSelanjutnya($programId,$idJenis) 
	{
		$result = $this->Open($this->mSqlQueries['get_kode_selanjutnya'],array($programId,$idJenis));
		return $result[0];
	}



//===DO==

	public function DoAdd($program_id,$kode,$nama,$idJenis,$kode_label,$rkakl_output_id) 
	{
		$rkakl_output_id = ($rkakl_output_id == '' || $rkakl_output_id == 0) ? NULL : $rkakl_output_id;
		$result = $this->Execute($this->mSqlQueries['do_add'], 
													array(
															$program_id,
															$kode,$nama,
															$idJenis,
															$kode_label,
														    $rkakl_output_id
														  ));
		return $result;
	}

	public function DoUpdate($data) 
	{ 
		$data['rkakl_output_id'] = ($data['rkakl_output_id'] =='' || $data['rkakl_output_id'] ==0) ? NULL : $data['rkakl_output_id'];
		$result = $this->Execute($this->mSqlQueries['do_update'], 
										array(
												$data['program'],
												$data['kode'], 
												$data['nama'],
												$data['jenisId'],
												$data['kode_label'],
												$data['rkakl_output_id'],
												$data['id']
											));

		return $result;
	}

	public function DoDelete($Id) 
	{
		$Id=str_replace(",","','",$Id);
		$result=$this->Execute($this->mSqlQueries['do_delete'], array($Id));
		return $result;
	}
}
?>