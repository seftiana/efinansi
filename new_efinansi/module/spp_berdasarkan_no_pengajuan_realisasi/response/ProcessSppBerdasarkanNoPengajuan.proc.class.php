<?php

/** 
 * 
 * @package realisasi_pencairan_2
 * @sub_package response
 * @class ProcessSppBerdasarkanNoPengajuan
 * @description untuk menangani proses pembuatan sppt berdasarkan nomor pengajuan
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @analyst nanang ruswianto <nanang@gamatechno.com>
 * @copyright 2013 Gamatechno Indonesia
 * 
 */
 
 
require_once GTFWConfiguration::GetValue( 'application', 'docroot').
	'module/realisasi_pencairan_2/business/SppBerdasarkanNoPengajuan.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 
	'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ProcessSppBerdasarkanNoPengajuan
{
	protected $_POST;
	protected $_GET;
	protected $method;
	protected $mUserUnitKerja;
	protected $mData  = array();
	
	public $mDBObj;
	public $cssDone   = "notebox-done";
	public $cssFail   = "notebox-warning";
   
	public $pageInput;
	public $pageView;
   
	protected $decId;
	protected $encId;
   
	public function __construct()
	{
		if(is_object($_POST)){
			$this->_POST   = $_POST->AsArray();
		}else{
			$this->_POST   = $_POST;
		}
		
		if(is_object($_GET)){
			$this->_GET    = $_GET->AsArray();
		}else{
			$this->_GET    = $_GET->AsArray();
		}
      
		$this->method = $_SERVER['REQUEST_METHOD'];
		$this->mDBObj = new SppBerdasarkanNoPengajuan();
		$this->mUserUnitKerja = new UserUnitKerja();
      
		$this->pageInput = Dispatcher::Instance()->GetUrl(
														'realisasi_pencairan_2',
														'CreateSppBerdasarkanNoPengajuan',
														'view',
														'html'
													);
													
		$this->pageView      = Dispatcher::Instance()->GetUrl(
														'realisasi_pencairan_2',
														'SppBerdasarkanNoPengajuan',
														'view',
														'html'
													);
      
		$this->decId         = Dispatcher::Instance()->Decrypt($this->_GET['id']);
		$this->encId         = Dispatcher::Instance()->Encrypt($this->decId);
		
		if(strtolower($this->method) === 'post'){
			$tanggalDay    = (int)$this->_POST['tanggal_day'];
			$tanggalMon    = (int)$this->_POST['tanggal_mon'];
			$tanggalYear   = (int)$this->_POST['tanggal_year']; 
			$this->_POST['tanggal'] = date('Y-m-d', mktime(0,0,0, $tanggalMon, $tanggalDay, $tanggalYear));	

		}
	}
   
	public function check()
	{
		if(strtolower($this->method) === 'post'){
			if(isset($this->_POST['btnsimpan'])){
				if($this->_POST['program_id'] == ''){
					$err[]      = 'Program Belum Dipilih';
				}					
				if($this->_POST['nomor_spp_no_pengajuan'] == ''){
					$err[]      = 'Nomor SPPT Harus Diisi';
				}		
				if(empty($this->_POST['pengajuan'])){
					$err[]      = 'Nomor Pengajuan Belum Diisi';
				}		
				

				if(isset($err)){
					$result['return']    = false;
					$result['message']   = $err[0];
				}else{
					$result['return']    = true;
					$result['message']   = null;
				}

				return (array)$result;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
   
	public function Add()
	{
		$check	= $this->check();

		if($check === false){
			return $this->pageView;
		}

		if($check['return'] === true){
			$process       = $this->mDBObj->Add($this->_POST);
			if($process === true){
				Messenger::Instance()->Send(
						Dispatcher::Instance()->mModule,
						'SppBerdasarkanNoPengajuan',
						'view',
						'html',
						array(
							$this->_POST,
							'Proses pembuatan SPP berhasil dijalankan',
							$this->cssDone
						),
						Messenger::NextRequest
				);
				
				return $this->pageView;
				
			} else {
				Messenger::Instance()->Send(
						Dispatcher::Instance()->mModule,
						'CreateSppBerdasarkanNoPengajuan',
						'view',
						'html',
						array(
							$this->_POST,
							'Proses pembuatan SPP gagal dijalankan',
							$this->cssFail
						),
						Messenger::NextRequest
					);
					
				return $this->pageInput.'&id='.$this->encId ;
			}
		} else {
				Messenger::Instance()->Send(
						Dispatcher::Instance()->mModule,
						'CreateSppBerdasarkanNoPengajuan',
						'view',
						'html',
						array(
							$this->_POST,
							$check['message'],
							$this->cssFail
						),
						Messenger::NextRequest
				);
			return $this->pageInput.'&id='.$this->encId ;
      }
   }
   
	public function Update()
	{
		
		$check  = $this->check();
		if($check === false){
			return $this->pageView;
		}
		
		if($check['return'] === true){
			$process = $this->mDBObj->Update($this->_POST);
			if($process === true){
				Messenger::Instance()->Send(
								Dispatcher::Instance()->mModule,
								'SppBerdasarkanNoPengajuan',
								'view',
								'html',
								array(
									$this->_POST,
									'Proses update SPP berhasil dijalankan',
									$this->cssDone
								),
								Messenger::NextRequest
					);
					
				return $this->pageView;
            
			} else {
				Messenger::Instance()->Send(
								Dispatcher::Instance()->mModule,
								'CreateSppBerdasarkanNoPengajuan',
								'view',
								'html',
								array(
									$this->_POST,
									'Proses update SPP gagal dijalankan',
									$this->cssDone
								),
								Messenger::NextRequest
				);
				
				return $this->pageInput.'&id='.$this->encId ;
			}
      
		} else {
			Messenger::Instance()->Send(
							Dispatcher::Instance()->mModule,
							'CreateSppBerdasarkanNoPengajuan',
							'view',
							'html',
							array(
								$this->_POST,
								$check['message'],
								$this->cssFail
							),
							Messenger::NextRequest
			);
			
			return $this->pageInput.'&id='.$this->encId ;
			
		}
	}
	
	public function Delete()
	{
      # $this->inputModule = 'inputRencanaPengeluaranNonRutin';
      if(isset($this->_POST['idDelete'])){
         $process  = $this->mDBObj->Delete($this->_POST['idDelete']);
         if($process === true){
            Messenger::Instance()->Send(
               Dispatcher::Instance()->mModule,
			   'SppBerdasarkanNoPengajuan',
               'view',
               'html',
               array(
                  $this->_POST,
                  'Proses penghapusan data berhasil',
                  $this->cssDone
               ),
               Messenger::NextRequest
            );
         }else{
            Messenger::Instance()->Send(
                Dispatcher::Instance()->mModule,
			   'SppBerdasarkanNoPengajuan',
               'view',
               'html',
               array(
                  $this->_POST,
                  'Proses penghapusan data gagal',
                  $this->cssFail
               ),
               Messenger::NextRequest
            );
         }
      }

      return $this->pageView;
	}
}

?>