<?php
class ReferensiMak extends Database {

   protected $mSqlFile;
   public $_POST;
   public $_GET;
   public $method;
   function __construct($connectionNumber=0) {
      $this->mSqlFile   = 'module/referensi_mak/business/referensimak.sql.php';
      $this->_POST      = is_object($_POST) ? $_POST->AsArray() : $_POST;
      $this->_GET       = is_object($_GET) ? $_GET->AsArray() : $_GET;
      $this->method     = strtolower($_SERVER['REQUEST_METHOD']);
      parent::__construct($connectionNumber);
   }

   public function Count()
   {
      $return     = $this->Open($this->mSqlQueries['count'], array());

      if($return){
         return $return[0]['count'];
      }else{
         return 0;
      }
   }

   public function getData($offset, $limit, $param = array())
   {
      $return     = $this->Open($this->mSqlQueries['get_data'], array(
         '%'.$param['bas_kode'].'%',
         '%'.$param['bas_kode'].'%',
         '%'.$param['mak_kode'].'%',
         '%'.$param['mak_nama'].'%',
         $offset,
         $limit
      ));

      return self::ChangeKeyName($return);
   }

   public function GetCount(){
      $result = $this->Open($this->mSqlQueries['get_search_count'], array());
      return $result['0']['total'];
   }

   function GetCountReferensiMak($kode='', $nama=''){
      $count = $this->Open($this->mSqlQueries['get_count_referensi_mak'], array( '%'.$kode.'%', '%'.$nama.'%'));
      return $count['0']['count'];
   }
   function GetCountReferensiMakUnit($kode='', $nama=''){
      $count = $this->Open($this->mSqlQueries['get_count_referensi_mak_unit'], array( '%'.$kode.'%', '%'.$nama.'%'));
      return $count['0']['count'];
   }


   function AddReferensiMak($kode,$nama,$userId,$idpagubas,$statusAktif){
      return $this->Execute($this->mSqlQueries['insert_referensi_mak'],
	  	array($kode,$nama,$userId,$idpagubas,$statusAktif));
   }

   function UpdateReferensiMak($kode, $nama, $userId, $idpagu, $statusAktif,$id){
      return $this->Execute($this->mSqlQueries['update_referensi_mak'],
	  array($kode,$nama,$userId,$idpagu,$statusAktif,$id));
   }

   	function DeleteReferensiMakById($id){
      return $this->Execute($this->mSqlQueries['delete_referensi_mak'], array($id));
   	}

	function DeleteReferensiMakByArrayId($arrId) {
		$arrId = implode("', '", $arrId);
		$result=$this->Execute($this->mSqlQueries['delete_referensi_mak_array'], array($arrId));
		//echo $this->getLastError(); exit;
		return $result;
	}

	//pagu bas
	function GetDataKodePaguBas($offset, $limit, $keterangan) {
		$result = $this->Open($this->mSqlQueries['get_data_pagu_bas'],
			array('%'.$keterangan.'%','%'.$keterangan.'%', $offset, $limit));
		//echo $this->getLastError();
		return $result;
   	}

   	function GetCountKodePaguBas($keterangan)
	{
     	$result = $this->Open($this->mSqlQueries['get_count_pagu_bas'],
		 			array('%'.$keterangan.'%','%'.$keterangan.'%'));
     	if (!$result)
       		return 0;
     	else
       		return $result[0]['total'];
   	}
   	function GetLastMakId()
	{
		$result = $this->Open($this->mSqlQueries['get_last_mak_id'], array());
      	return $result[0]['last_id'];
	}
   //coa
   	function GetCoaMak($makId)
	{
		$result = $this->Open($this->mSqlQueries['get_coa_mak'], array($makId));
      	return $result;
	}
   	function DoAddCoaMak($coaId,$makId)
   	{

      $result = $this->Execute($this->mSqlQueries['do_add_coa_mak'],
	  			array(
	  				$coaId,
				  	$makId)
				  );

      return $result;
   	}

    function DoUpdateCoaMak($makId,$coaId,$id)
	{

      $result = $this->Execute($this->mSqlQueries['do_update_coa_mak'],
  				array(
		  			  $makId,
					  $coaId,
					  $id)
				  );
	  //$this->mdebug(1);
      return $result;
   	}

	function DoDeleteCoaMak($makId)
	{
      $result=$this->Execute($this->mSqlQueries['do_delete_coa_mak'], array($makId));
      return $result;
   	}

    // data referensi mak dari tabel finansi_ref_pagu_bas
    public function CountRefMak($kode = '', $keterangan = '')
    {
        $result = $this->Open($this->mSqlQueries['count_ref_mak'], array('%'.$kode.'%','%'.$keterangan.'%'));

        return $result[0]['count'];
    }

    public function GetRefMak($kode = '', $keterangan = '', $start, $limit)
    {
        $result = $this->Open($this->mSqlQueries['get_ref_mak'], array(
                                                                      '%'.$kode.'%',
                                                                      '%'.$keterangan.'%',
                                                                      $start,
                                                                      $limit));
        return $result;
    }

    function GetRefMakById($id) {
        return $this->Open($this->mSqlQueries['get_ref_mak_by_id'], array($id));
    }

    // get combo type bas
    public function GetComboTypeBas()
    {
        $result = $this->Open($this->mSqlQueries['get_combo_type_bas'], array());

        return $result;
    }

    public function GetLastPaguBasId()
    {
        $result = $this->Open($this->mSqlQueries['get_last_pagu_bas_id'], array());

        return $result[0]['last_id'];
    }

    public function CheckCoaMakByBasId($basId)
    {
        $result = $this->Open($this->mSqlQueries['cek_coa_mak_by_bas_id'], array($basId));

        return $result[0]['count_mak'];
    }

    public function CountBasTipeBasByBasId($basId)
    {
        $result = $this->Open($this->mSqlQueries['count_bas_tipe_bas_by_bas_id'], array($basId));

        return $result[0]['count_tipe'];
    }

    function InsertMakIntoPaguBas($kode,$parent,$nilai,$status,$keterangan)
    {
        $result = $this->Execute($this->mSqlQueries['insert_mak_into_pagu_bas'], array(
                                                                                       $kode,
                                                                                       $parent,
                                                                                       $nilai,
                                                                                       $status,
                                                                                       $keterangan
                                                                                       ));
        return $result;
    }

    function InsertIntoCoaMak($coaId,$makId){
        $result = $this->Execute($this->mSqlQueries['insert_into_coa_mak'], array($coaId,$makId));

        return $result;
    }

    function InsertBasTipe($tipeBasId,$makId){
        $result = $this->Execute($this->mSqlQueries['insert_into_bas_tipe'], array($tipeBasId,$makId));

        return $result;
    }

    public function UpdateCoaMakByBasId($coaId,$basId)
    {
        $result = $this->Execute($this->mSqlQueries['update_coa_mak_by_bas_id'], array(
                                                                                       $coaId,
                                                                                       $basId,
                                                                                       $basId
                                                                                       ));
        return $result;
    }

    public function UpdateBasTipeBasByBasId($tipeId,$basId)
    {
        $result = $this->Execute($this->mSqlQueries['update_bas_tipe_bas_by_bas_id'], array(
                                                                                            $tipeId,
                                                                                            $basId,
                                                                                            $basId
                                                                                            ));
        return $result;
    }

    public function UpdateMakByBasId($kode,$parent,$nilai_default,$status,$nama,$basId,$coa,$tipe)
    {
        $result = $this->Execute($this->mSqlQueries['update_mak_by_bas_id'], array(
                                                                                   $kode,
                                                                                   $parent,
                                                                                   $nilai_default,
                                                                                   $status,
                                                                                   $nama,
                                                                                   $basId
                                                                                   ));
        if ($result)
        {
            if($coa != ''){
                if($this->CheckCoaMakByBasId($basId) <> 0){
                    $this->UpdateCoaMakByBasId($coa,$basId);
                }else{
                    $this->InsertIntoCoaMak($coa,$basId);
                }
            }
            if($tipe != ''){
                if($this->CountBasTipeBasByBasId($basId) <> 0){
                    $this->UpdateBasTipeBasByBasId($tipe,$basId);
                }else{
                    $this->InsertBasTipe($tipe,$basId);
                }
            }


        }

        return $result;
    }

    public function DeleteCoaMakByBasId($basId)
    {
        $result = $this->Execute($this->mSqlQueries['delete_coa_mak_by_bas_id'], array($basId));

        return $result;
    }

    public function DeleteBasTipeBasByBasId($basId)
    {
        $result = $this->Execute($this->mSqlQueries['delete_bas_tipe_bas_by_bas_id'], array($basId));

        return $result;
    }

    public function DeleteMakPaguBasByBasId($basId)
    {
        $result = $this->Execute($this->mSqlQueries['delete_mak_pagu_bas_by_bas_id'], array($basId));

        if($result){
            if($this->CheckCoaMakByBasId($basId) <> 0){
                $this->DeleteCoaMakByBasId($basId);
            }
            if($this->CountBasTipeBasByBasId($basId) <> 0){
                $this->DeleteBasTipeBasByBasId($basId);
            }
        }
        return $result;
   }

   /*
    * @param string $camelCasedWord Camel-cased word to be "underscorized"
    * @param string $case case type, uppercase, lowercase
    * @return string Underscore-syntaxed version of the $camelCasedWord
    */
   public static function humanize($camelCasedWord, $case = 'upper')
   {
      switch ($case) {
         case 'upper':
            $return     = strtoupper(preg_replace('/(?<=\w)([A-Z])/', '_\1', $camelCasedWord));
            break;
         case 'lower':
            $return     = strtolower(preg_replace('/(?<=\w)([A-Z])/', '_\1', $camelCasedWord));
            break;
         case 'title':
            $return     = ucwords(preg_replace('/(?<=\w)([A-Z])/', '_\1', $camelCasedWord));
            break;
         case 'sentences':
            $return     = ucfirst(preg_replace('/(?<=\w)([A-Z])/', '_\1', $camelCasedWord));
            break;
         default:
            $return     = strtoupper(preg_replace('/(?<=\w)([A-Z])/', '_\1', $camelCasedWord));
            break;
      }
      return $return;
   }

   /*
    * @desc change key name from input data
    * @param array $input
    * @param string $case based on humanize method
    * @return array
    */
   public function ChangeKeyName($input = array(), $case = 'lower')
   {
      if(!is_array($input)){
         return $input;
      }

      foreach ($input as $key => $value) {
         if(is_array($value)){
            foreach ($value as $k => $v) {
               $array[$key][self::humanize($k, $case)] = $v;
            }
         }
         else{
            $array[self::humanize($key, $case)]  = $value;
         }
      }

      return (array)$array;
   }

   /**
    * @param string  path_info url to be parsed, default null
    * @return string parsed_url based on Dispatcher::Instance()->getQueryString();
    */
   public function _getQueryString($pathInfo = null)
   {
      $parseUrl            = is_null($pathInfo) ? parse_url($_SERVER['QUERY_STRING']) : parse_url($pathInfo);
      $explodedUrl         = explode('&', $parseUrl['path']);
      $requestData         = '';
      foreach ($explodedUrl as $path) {
         if(preg_match('/^mod=[a-zA-Z0-9_-]+/', $path)){
            continue;
         }
         if(preg_match('/^sub=[a-zA-Z0-9_-]+/', $path)){
            continue;
         }
         if(preg_match('/^act=[a-zA-Z0-9_-]+/', $path)){
            continue;
         }
         if(preg_match('/^typ=[a-zA-Z0-9_-]+/', $path)){
            continue;
         }
         if(preg_match('/^ascomponent=[a-zA-Z0-9_-]+/', $path)){
            continue;
         }
         if(preg_match('/uniqid=[a-zA-Z0-9_-]+/', $path)){
            continue;
         }

         list($key, $value)   = explode('=', $path);
         $requestData[$key]   = Dispatcher::Instance()->Decrypt($value);
      }
      if(method_exists(Dispatcher::Instance(), 'getQueryString') === true){
         $queryString         = Dispatcher::Instance()->getQueryString($requestData);
      }else{
         foreach ($requestData as $key => $value) {
            $query[$key]      = Dispatcher::Instance()->Encrypt($value);
         }
         $queryString         = urldecode(http_build_query($query));
      }
      return $queryString;
   }
}
?>