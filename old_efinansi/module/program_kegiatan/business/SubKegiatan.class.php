<?php

class SubKegiatan extends Database 
{
    protected $mSqlFile= 'module/program_kegiatan/business/subkegiatan.sql.php';

    public function __construct($connectionNumber=0) 
    {
        parent::__construct($connectionNumber);
    }


    //==GET==
    public function GetTahunAnggaranAktif()
    {
		$result = $this->Open($this->mSqlQueries['get_tahun_anggaran_aktif'],array());
		return $result['0']['thanggarId'];
	}

	public function GetTahunAnggaranById($id)
    {
		$result = $this->Open($this->mSqlQueries['get_tahun_anggaran_by_id'],array($id));
		return $result['0'];
	}

    public function GetCountDataWhereTA($idProg,$idTahun, $id)
    {
		if($idProg=="all")
			$idProg="";
		$arrCount = $this->Open($this->mSqlQueries['get_keg_count'],
                                                    array('%'.$idProg.'%',$idTahun, '%'.$id.'%', '%'.$id.'%'));
		return $arrCount['0']['count'];
    }

	public function GetDataWhereTa($start, $count, $idProg,$idTahun, $id)
    {
		if($idProg=="all")
			$idProg="";
		return $this->Open($this->mSqlQueries['get_program_pop_up'],
                                                    array(
                                                            '%'.$idProg.'%',
                                                            $idTahun, 
                                                            '%'.$id.'%', 
                                                            '%'.$id.'%', 
                                                            $start, 
                                                            $count));
	}

    public function GetDataTahunAnggaran()
    {
      return $this->Open($this->mSqlQueries['get_tahun_anggaran'], array());
    }

    public function GetDataProgram($id)
    {
      return $this->Open($this->mSqlQueries['get_program'], array($id));
    }

    public function GetDataKegiatanCountAll ($id)
    {
      $result = $this->Open($this->mSqlQueries['get_data_kegiatan_count_all'], array($id));
      return $result[0]['count'];
    }

    function GetData ($offset, $limit, $idTahun, $programId, $kegiatan, 
                            $idJenisKegiatan, $subKegiatanId, $subKegiatan) 
    {
        if($programId=='')
	       $programId = 'all';
        if($idJenisKegiatan=='')
            $idJenisKegiatan = 'all';

        $result = $this->Open($this->mSqlQueries['get_data'], 
                                array(
                                        $kegiatan, 
                                        (int)($kegiatan==''), 
                                        $idJenisKegiatan, 
                                        (int)($idJenisKegiatan=='all'), 
                                        $idTahun, 
                                        $programId,
                                        (int)($programId=='all'), 
                                        '%'.$subKegiatanId.'%', 
                                        '%'.$subKegiatan.'%', 
                                        $offset, 
                                        $limit));

        return $result;
   }

   public function GetCount ($idTahun, $programId, $kegiatan, $idJenisKegiatan, $subKegiatanId, $subKegiatan) 
   {
	   if($programId=='')
	       $programId = 'all';
        if($idJenisKegiatan=='')
            $idJenisKegiatan = 'all';

	#printf($this->mSqlQueries['get_count_data'], $idTahun, 
    //'%'.$programId.'%', '%'.$kegiatan.'%', '%'.$idJenisKegiatan.'%', 
    //'%'.$subKegiatanId.'%', '%'.$subKegiatan.'%');
	 $result = $this->Open($this->mSqlQueries['get_count_data'], 
                                            array(
                                                    $kegiatan, 
                                                    (int)($kegiatan==''), 
                                                    $idJenisKegiatan, 
                                                    (int)($idJenisKegiatan=='all'), 
                                                    $idTahun, 
                                                    $programId,
                                                    (int)($programId=='all'), 
                                                    '%'.$subKegiatanId.'%', 
                                                    '%'.$subKegiatan.'%'));

     if (!$result)
       return 0;
     else
       return $result[0]['total'];
   }

   public function GetDataById($Id) 
   {

      $result = $this->Open($this->mSqlQueries['get_data_by_id'], array($Id));
	  //$x = sprintf($this->mSqlQueries['get_data_by_id'], $Id);
	  //echo $x;
      return $result;
   }

   public function GetDataJenisKegiatan()
   {
      $result = $this->Open($this->mSqlQueries['get_data_jenis_kegiatan'], array($Id));
      return $result;
   }

   public function GetMaxNomor() 
   {
     $result = $this->Open($this->mSqlQueries['get_max_nomor'], array());
	 if($result)
	    $x = $result[0]['max'];
	 else
	    $x=1;

    return $x;
   }
   
   public function GetKodeSelanjutnya($subprogId) 
   {
      $result = $this->Open($this->mSqlQueries['get_kode_selanjutnya'],array($subprogId));
      //$a = sprintf($this->mSqlQueries['get_kode_selanjutnya'], $subprogId);
     // echo $a;
     // print_r($result);
	   return $result[0];
   }

  //===DO==
   /**
    * fungsi DoAdd
    * @todo untuk menyimpan data sub kegiatan ke tabel kegiatan_ref,
    * tabel relasi finansi_pa_kegiatan_ik, tabel relasi finansi_pa_kegiatan_ref_unit_kerja
    * @param Array $data data array dari form input
    * @modified by noor hadi <noor.hadi@gamatechno.com>
    * @since 14 Juni 2012
    * @return bool
    */     
   public function DoAdd($data) 
   {    
        $this->StartTrans();
        /**
         * proses simpan data sub kegiatan ke tabel kegiatan_ref
         */
        $result =  $this->Execute($this->mSqlQueries['do_add'], 
                                                        array(
                                                                $data['kode'],
                                                                $data['kegiatan_id'],
					                                            $data['nama'],
                                                                $data['kode_label'],
                                                                empty($data['rkakl_subkegiatan']) ? NULL : 
                                                                    $data['rkakl_subkegiatan']
					                                       ));	  	  
        $kegRefId = $this->LastInsertId();
        
        /**
         * proses simpan data ke tabel relasi finansi_pa_kegiatan_ref_unit_kerja
         */	  
        if($result) {
	     if (!empty($data['unitkerjaid'])){
            foreach ($data['unitkerjaid'] as $val){
	           $result = $this->Execute($this->mSqlQueries['do_input_unit_kerja_ref'], 
                                  array($kegRefId,$val));
            }
          }
        }
	  
        /**
         * proses simpan data ke tabel finansi_pa_kegiatan_ik 
         */
       if($result){	     		 
		 if(!empty($data['ik_id'])) {
            $userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
		    foreach($data['ik_id'] as $val){
			   $result = $this->Execute($this->mSqlQueries['do_input_ik_ref'], array($kegRefId,$val,$userId));
			}
		 } 
	  } 
      
	  $this->EndTrans($result);	  
	  return $result;      
   }

   /**
    * fungsi DoUpdate
    * @todo untuk update data sub kegiatan ke tabel kegiatan_ref,
    * tabel relasi finansi_pa_kegiatan_ik, tabel relasi finansi_pa_kegiatan_ref_unit_kerja
    * @param Array $data data array dari form input
    * @modified by noor hadi <noor.hadi@gamatechno.com>
    * @since 14 Juni 2012
    * @return bool
    */       
   public function DoUpdate($data) 
   {
      $this->StartTrans();
      /**
       * proses update data sub kegiatan ke tabel kegiatan_ref
       */
       $result = $this->Execute($this->mSqlQueries['do_update'],
                                                    array(
                                                            $data['kode'],
                                                            $data['kegiatan_id'],
                                                            $data['nama'],
                                                            $data['kode_label'],
                                                            empty($data['rkakl_subkegiatan']) ? NULL : 
                                                                $data['rkakl_subkegiatan'],
						                                    $data['id']
						                                  ));
      /**
       * proses simpan data ke tabel relasi finansi_pa_kegiatan_ref_unit_kerja
       */	  
       if($result){
          if (!empty($data['unitkerjaid'])){
             /**
              * cek isi tabel finansi_pa_kegiatan_ref_unit_kerja
              */
            if($this->GetCountUnitKerjaRef($data['id']) > 0){
                /**
                 * jika ada data maka hapus dulu
                 */
                $result = $this->Execute($this->mSqlQueries['do_delete_unit_kerja_ref_by_kegref'],
                                                            array($data['id'])); 
            }
             foreach ($data['unitkerjaid'] as $val){
	               $result = $this->Execute($this->mSqlQueries['do_input_unit_kerja_ref'], 
                                  array($data['id'],$val));
             }   
          } else {
            /**
             * jika input cek unit kerja kosong maka cek tabel finansi_pa_kegiatan_ref_unit_kerja
             */
            if($this->GetCountUnitKerjaRef($data['id']) > 0){
                /**
                 * jika ada data maka hapus data
                 */
                $result = $this->Execute($this->mSqlQueries['do_delete_unit_kerja_ref_by_kegref'],
                                                            array($data['id']));
            }
          }
       }
      /**
       * proses simpan data ke tabel finansi_pa_kegiatan_ik 
       */
       if($result){	     		 
		 if(!empty($data['ik_id'])) {
             /**
              * cek isi tabel finansi_pa_kegiatan_ik
              */
		    if($this->GetCountDataIK($data['id']) > 0){
                /**
                 * jika ada data maka hapus dulu
                 */
                $result = $this->Execute($this->mSqlQueries['do_delete_data_ik_by_kegref'],array($data['id']));
		    }  
            $userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
		    foreach($data['ik_id'] as $val){
			   $result = $this->Execute($this->mSqlQueries['do_input_ik_ref'], array($data['id'],$val,$userId));
			}
		 } else {
            if($this->GetCountDataIK($data['id']) > 0){
		      $result = $this->Execute($this->mSqlQueries['do_delete_data_ik_by_kegref'],array($data['id']));
		    }
		 }
	  }       
      $this->EndTrans($result);
      return $result;
   }

   /**
    * fungsi DoDelete
    * @todo untuk hapus data sub kegiatan dari tabel kegiatan_ref,
    * tabel relasi finansi_pa_kegiatan_ik, tabel relasi finansi_pa_kegiatan_ref_unit_kerja
    * @param Number $kegrefId Kegiatan Ref ID
    * @modified by noor hadi <noor.hadi@gamatechno.com>
    * @since 14 Juni 2012
    * @return bool
    */           
   public function DoDelete($kegrefId) 
   {
      $this->StartTrans();
      $result = true;
      if($this->GetCountUnitKerjaRef($kegrefId) > 0){
        $result = $this->Execute($this->mSqlQueries['do_delete_unit_kerja_ref_by_kegref'],array($kegrefId));
      }        
      if($result){
        if($this->GetCountDataIK($kegrefId) > 0){
            $result = $this->Execute($this->mSqlQueries['do_delete_data_ik_by_kegref'],array($kegrefId));
        }            
      }
      if($result){
        $result = $this->Execute($this->mSqlQueries['do_delete'], array($kegrefId));  
      }        
      $this->EndTrans($result);
      return $result;
   }

   
   /**
    * fungsi GetCountUnitKerjRef
    * @todo untuk melakukan cek isi data tabel relasi unit kerja dengan kegiatan ref
    * @param Number $kegregid
    * @return number
    */
   public function GetCountUnitKerjaRef($kegrefid)
   {
   		$result = $this->Open($this->mSqlQueries['get_count_unit_kerja_ref'], array($kegrefid));
 		return $result[0]['jumlah'];
   }

   /**
    * fungsi GetListUnitKerja
    * untuk mendapatkan data unit kerja dan jumlah unit kerja yang 
    * berada pada tabel finansi_pa_kegiatan_unit_kerja
    * @param number $kegrefId : id kegiatan
    * @return array
    */   
   public function GetListUnitKerja($kegrefId)
   {
    	$result = $this->Open($this->mSqlQueries['get_unit_kerja_kegiatan'], array($kegrefId));
 		return $result;
   }
      
   /**
    * fungsi GetlistDataIK
    * @todo untuk menampilkan data Indikator kegiatan dari tabel finansi_pa_ref_ik 
    * beserta status cek box
    * @param Number $kegrefId Kegiatan Ref ID
    * @since 14 Juni 2012
    * @author noor hadi <noor.hadi@gamatechno.com>
    * @copyright 2012 Gamatechno Indonesia
    * @return Array
    */    
   public function GetListDataIK($kegrefId)
   {
    	$result = $this->Open($this->mSqlQueries['get_data_ik'], array($kegrefId));
 		return $result;    
   }
   
   /**
    * fungsi GetCountDataIK
    * @todo untuk melakukan cek isi data tabel finansi_pa_kegiatan_ik
    * @param Number $kegrefid
    * @return number
    */
   public function GetCountDataIK($kegrefid)
   {
   		$result = $this->Open($this->mSqlQueries['get_count_data_ik'], array($kegrefid));
 		return $result[0]['jumlah'];
   }
   
}
