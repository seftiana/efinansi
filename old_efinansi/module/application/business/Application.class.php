<?php
/**
 * 
 * @class application
 * @package application
 * @description untuk kebutuhan aplikasi
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @since December 2013
 * @copyright 2013 Gamatechno Indonedia
 * 
 */



class Application extends Database
{
	private static $_mInstance = NULL;
	
	private $_mRestServiceAddress;
	
	protected $mSqlFile;
	

	public function __construct ($connectionNumber=0)
	{
		$this->mSqlFile = 'module/application/business/application.sql.php';		
		parent::__construct($connectionNumber);
	}	
	
	
	/**
	 * @method RestClient
	 * @description untuk menjalankan Restclient	 
	 * @param array $data untuk data yang akan di kirim melalui service
	 * @param string $method metode kirim data yang digunakan (post,get)
	 * @access private
	 * @return array
	 */
	private function _RestClient($data = array(),$method = 'post')
	{

		/**
		 * include file rest client
		 */
		require_once Configuration::Instance()->GetValue('application', 'gtfw_base') . 
					'main/lib/gtfw/rest/RestClient.class.php';
					
		$restClientObj = new RestClient();
		
		$queryString = Dispatcher::Instance()->getQueryString($data);
		$restClientObj->SetPath($this->_mRestServiceAddress);	
		
		if($method == 'get') {						
			$restClientObj->SetGet($queryString);
		} else {
			$restClientObj->SetPost($queryString);
		}
		
		$response = $restClientObj->send($queryString);
		return $response['gtfwResult'];
		
	}
	
	/**
	 * @method GetRestServiceAddress
	 * @description untuk mendapatkan alamat service
	 * @access public
	 * @return string
	 */
	public function GetRestServiceAddress()
	{
		return $this->_mRestServiceAddress;	
	}
	
	/**
	 * @method GetRestServiceAddress
	 * @description untuk set alamat service
	 * @param string $applicationId
	 * @param string $modulAddress
	 * @param string $applicationOwner default gt
	 * @access public
	 * @return string
	 */
	public function SetRestServiceAddress($applicationId,$moduleAddress='',$applicationOwner='gt')
	{
		$result = $this->Open($this->mSqlQueries['get_application_service_address'], 
									array(
										$applicationOwner,
										$applicationId
									)
							);
		if(!empty($result) && (!empty($result['0']['url_address']))){
			$this->_mRestServiceAddress =  trim($result['0']['url_address']).trim($moduleAddress);
		} else {
			$this->_mRestServiceAddress = NULL;
		}	
	}
	
	/**
     * @function SendRestDataDB
     * @description untuk mengirimkan data ke penyedia service
     * @param array $data
     * @param bool $dbResult untuk status keberhasilan query database
     * @param string $action default value ='add'
     * 
     * @since 1 October 2013
     * @access protected
     * @return mix
     * 
     */ 
	public function SendRestDataDB($data,$dbResult,$method = 'post')
    {
		$result['status'] = NULL;
			
		/**
		 * untuk keperluan cek url service
		 */			
		if(!empty($this->_mRestServiceAddress)){
			//set metode kirim data
				
			/**
			 * cek keberhasilan query database jika berhasil melakukan 
			 * query simpan edit delete maka kirim data ke service
			 * jika tidak maka gagal kirim dan simpan ke database
			 */
			if($dbResult === true) {
				/**
				 * mekanisme kirim data melalui service
				 */					
				$result = $this->_RestClient($data,$method);
			} else {
				/**
				 * gagal data tidak terkirim
				 */
				$result['status'] = 'dataFailed';
			}	
				
		} else {
			/**
			 * alamat service belum di set
			 */
			$result['status'] = 'urlNotSet';
		}
		
		return $result;
	}
	
		
	/**
	 * @method GetSettingValue
	 * @description untuk mendapatkan nilai parameter yang tersimpan di tabel setting
	 * @param string $paramName nama paremeter
	 * @access public
	 * @return string
	 */
	public function GetSettingValue($paramName)
	{
		$result = $this->Open($this->mSqlQueries['get_setting_value'], 
									array(
										$paramName
									)
							);
		if(!empty($result)){
			return $result['0']['value'];
		} else {
			return NULL;
		}			
	}
	
	/**
	 * @method Instanace
	 * @description untuk membuat instance class Application 
	 * tanpa harus menggunakan operator new
	 * @access public
	 * @return class
	 */
	public static function Instance()
	{
		if(!isset(self::$_mInstance) ){
			self::$_mInstance = new Application();
		}
		
		return self::$_mInstance;
	}

}

?>