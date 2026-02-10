<?php

/**
 * class ViewAlokasiPenerimaan
 * @package alokasi_penerimaan
 * @since 27 Maret 2012
 * @copyright 2012 Gamatechno
 */ 

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
        'module/alokasi_penerimaan/business/AlokasiPenerimaan.class.php';
        
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
	'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewAlokasiPenerimaan extends HtmlResponse
{
    protected $mPesan;
    protected $mCss;
    
    public function TemplateModule() 
    {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
                'module/alokasi_penerimaan/template');
        $this->SetTemplateFile('view_alokasi_penerimaan.html');
    } 
           
    public function ProcessRequest()
    {
        $_POST = $_POST->AsArray();
		$userUnitKerjaObj = new UserUnitKerja();
		$userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
		$unit = $userUnitKerjaObj->GetUnitKerjaUser($userId);
        /**
         * untuk proses pencarian
         */
   	    if($_POST['btncari']){
            $kode = $_POST['kode'];
			$nama = $_POST['nama'];
            $id_unit = $_POST['id_unit'];
            $nama_unit = $_POST['nama_unit'];
		} elseif (isset($_GET['cari'])) {
		    $kode = Dispatcher::Instance()->Decrypt($_GET['kode']);
            $nama = Dispatcher::Instance()->Decrypt($_GET['nama']);
            $id_unit = Dispatcher::Instance()->Decrypt($_GET['id_unit']);
            $nama_unit = Dispatcher::Instance()->Decrypt($_GET['nama_unit']);
        } else {
		   $kode ='';
           $nama ='';
           $id_unit = $unit['unit_kerja_id'];
    	   $nama_unit = $unit['unit_kerja_nama'];
		}
        /**
         * untuk menyimpann data message
         */
   	    $msg = Messenger::Instance()->Receive(__FILE__);
		$this->mPesan = $msg[0][1];
		$this->mCss = $msg[0][2];
        
        $AlokasiPenerimaan = new AlokasiPenerimaan();
        
        $itemViewed = 20;
        $currPage = 1;
        $startRec = 0 ;
        if(isset($_GET['page'])) {
            $currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();  
            $startRec =($currPage-1) * $itemViewed;
        }
        
        $totalData = $AlokasiPenerimaan->GetCountData($kode,$nama,$id_unit);
        
        /**
         * url untuk sistem pagging
         */
        $url = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, 
               Dispatcher::Instance()->mSubModule, 
               Dispatcher::Instance()->mAction, 
               Dispatcher::Instance()->mType
               );
        if(!empty($kode)){
            $url .= '&kode='.Dispatcher::Instance()->Encrypt($kode);
        }
        if(!empty($nama)){
            $url .= '&nama='.Dispatcher::Instance()->Encrypt($nama);
        }
        $url .= '&id_unit='.Dispatcher::Instance()->Encrypt($id_unit).
                '&nama_unit='.Dispatcher::Instance()->Encrypt($nama_unit).
                '&cari=' . Dispatcher::Instance()->Encrypt(1);
		
        /**
         * memanggil komponen pagging
         */	   
        Messenger::Instance()->SendToComponent(
                                        'paging', 
                                        'Paging', 
                                        'view', 
                                        'html', 
                                        'paging_top',
                                        array(
                                                $itemViewed,
                                                $totalData, 
                                                $url, 
                                                $currPage),
                                        Messenger::CurrentRequest);
                                          
        $return['dataAlokasi'] = $AlokasiPenerimaan->GetAlokasiPenerimaan($startRec,$itemViewed,$kode,$nama,$id_unit);
        $return['start'] = $startRec+1;
        $return['search']['kode'] = $kode;
        $return['search']['nama'] = $nama;
        $return['search']['id_unit'] = $id_unit;
        $return['search']['nama_unit'] = $nama_unit;
        $return['total_sub_unit'] = $userUnitKerjaObj->GetTotalSubUnitKerja($unit['unit_kerja_id']);
        return $return;
    }
    
    public function ParseTemplate($data  = NULL)
    {
        $search = $data['search'];
  
        /**
         * mengisi varable template
         */
  		if($data['total_sub_unit'] > 0){
				$this->mrTemplate->AddVar('cek_unitkerja_parent', 'IS_PARENT','YES');
		} else {
				$this->mrTemplate->AddVar('cek_unitkerja_parent', 'IS_PARENT', 'NO');
		}
        
        $this->mrTemplate->AddVar('cek_unitkerja_parent', 'SEARCH_NAMA_UNIT', $search['nama_unit']);
        $this->mrTemplate->AddVar('cek_unitkerja_parent', "URL_POPUP_UNIT_KERJA",
                                    Dispatcher::Instance()->GetUrl(
                                                        'alokasi_penerimaan', 
                                                        'PopupUnitKerja', 
                                                        'view', 
                                                        'html')
                                );                    
		$this->mrTemplate->AddVar('content', 'SEARCH_KODE', $search['kode']);
        $this->mrTemplate->AddVar('content', 'SEARCH_NAMA', $search['nama']);
        $this->mrTemplate->AddVar('content', 'SEARCH_ID_UNIT', $search['id_unit']);
	   	$this->mrTemplate->AddVar('content', 'URL_RESET', 
                                Dispatcher::Instance()->GetUrl(
			   	                                         'alokasi_penerimaan', 
			   	                                         'AlokasiPenerimaan', 
  	                                                     'view', 
  	                                                     'html'));
        
	   	$this->mrTemplate->AddVar('content', 'URL_SEARCH', 
                                Dispatcher::Instance()->GetUrl(
			   	                                         'alokasi_penerimaan', 
			   	                                         'AlokasiPenerimaan', 
  	                                                     'view', 
  	                                                     'html'));
                                 
	   	$this->mrTemplate->AddVar('content', 'URL_ADD', 
                                Dispatcher::Instance()->GetUrl(
                                                        'alokasi_penerimaan', 
                                                        'inputAlokasiPenerimaan', 
                                                        'view', 
                                                        'html'));
                                                        
	   $this->mrTemplate->AddVar("content", "URL_POPUP_UNIT_KERJA",
                                    Dispatcher::Instance()->GetUrl(
                                                        'alokasi_penerimaan', 
                                                        'PopupUnitKerja', 
                                                        'view', 
                                                        'html')
                                );
        $this->mrTemplate->AddVar("content", "URL_POPUP_KODE_PENERIMAAN",
                                    Dispatcher::Instance()->GetUrl(
                                                        'alokasi_penerimaan', 
                                                        'PopupKodePenerimaan', 
                                                        'view', 
                                                        'html')
                                );
        /**
         * untuk menampilkan pesan/message di halaman view / utama
         */
        if($this->mPesan) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->mPesan);
			$this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->mCss);
		}
        
		/**
         * untuk mekanisme hapus banyak
		 */
		$label = GTFWConfiguration::GetValue('language','alokasi_penerimaan');
		$urlDelete = Dispatcher::Instance()->GetUrl(
					'alokasi_penerimaan',
					'deleteAlokasiPenerimaan', 
					'do', 
					'html');
		$urlReturn = Dispatcher::Instance()->GetUrl(
					'alokasi_penerimaan',
					'AlokasiPenerimaan', 
					'view', 
					'html');
		Messenger::Instance()->Send(
					'confirm', 
					'confirmDelete', 
					'do', 
					'html', array($label, $urlDelete, $urlReturn),
		Messenger::NextRequest);
			
		$this->mrTemplate->AddVar('content', 'URL_DELETE', 
					Dispatcher::Instance()->GetUrl('confirm', 'confirmDelete', 'do', 'html'));
		/**
		 * end
		 */
         
         /**
          * untuk hapus satu persatu
          */
          $urlSingleDelete = 'alokasi_penerimaan|deleteAlokasiPenerimaan|do|html';
          $urlSingleReturn = 'alokasi_penerimaan|AlokasiPenerimaan|view|html';
         /**
          * end
          */
        /**
         * menampilkan data alokasi penerimaan
         */
        if(empty($data['dataAlokasi'])){
            $this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'YES');
        } else {
            $this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'NO');
            for($i = 0; $i < count($data['dataAlokasi']);$i++){
                $no = $i+$data['start'];
                if($i == 0) $this->mrTemplate->AddVar('content', 'FIRST_NUMBER', $no);				
				if($i == count($data['dataAlokasi'])-1) {
					$this->mrTemplate->AddVar('content', 'LAST_NUMBER', $no);
				}
                $data['dataAlokasi'][$i]['nomor'] = $no;
                $idEnc =Dispatcher::Instance()->Encrypt($data['dataAlokasi'][$i]['alokasi_id']);
                $data['dataAlokasi'][$i]['url_edit']= Dispatcher::Instance()->GetUrl(
                                                        'alokasi_penerimaan', 
                                                        'inputAlokasiPenerimaan', 
                                                        'view', 
                                                        'html').
                                                        '&dataId='.$idEnc;
                                                                    
                $data['dataAlokasi'][$i]['url_delete']= Dispatcher::Instance()->GetUrl(
   															'confirm', 
														    'confirmDelete', 
															'do', 
															'html') . 
															'&urlDelete='. $urlSingleDelete.
															'&urlReturn='.$urlSingleReturn .
            												'&id='.
															Dispatcher::Instance()->Encrypt(
																$data['dataAlokasi'][$i]['alokasi_id']).
            												'&label='.$label.
															'&dataName='.$data['dataAlokasi'][$i]['nama_unit'].
                                                            ' - '.$data['dataAlokasi'][$i]['nama_terima'];
                                                        
                /**
                 * untuk label hapus data
                 */
                $data['dataAlokasi'][$i]['nama_unit_hidden'] =$data['dataAlokasi'][$i]['nama_unit'];
               	if($i > 0){ 
						if($data['dataAlokasi'][$i - 1]['id_unit'] == $data['dataAlokasi'][$i]['id_unit']){
						  $data['dataAlokasi'][$i]['nama_unit']='';
                          $data['dataAlokasi'][$i]['kode_unit']='';
						} 
				}
                $this->mrTemplate->AddVars('data_alokasi', $data['dataAlokasi'][$i], 'DATA_');
                $this->mrTemplate->parseTemplate('data_alokasi', 'a'); 
            }
        }
        /**
         * end
         */
        

    }
}

?>