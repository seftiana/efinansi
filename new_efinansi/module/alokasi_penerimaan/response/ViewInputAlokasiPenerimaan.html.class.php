<?php

/**
 * class InputAlokasiPenerimaan
 * @package alokasi_penerimaan
 * @since 27 Maret 2012
 * @copyright 2012 Gamatechno
 */
 

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
		'module/alokasi_penerimaan/business/AlokasiPenerimaan.class.php';

class ViewInputAlokasiPenerimaan extends HtmlResponse 
{
    protected $mData;
	protected $mPesan;
    
    private $_mAlokasiPenerimaan;
    
    public function __construct()
    {
        $this->_mAlokasiPenerimaan = new AlokasiPenerimaan();
    }
    public function TemplateModule() 
    {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') .
         'module/alokasi_penerimaan/template');
        $this->SetTemplateFile('view_input_alokasi_penerimaan.html');
    }
   
    public function ProcessRequest() 
    {        
        /** 
         * untuk menyimpan session messager untuk menampilkan pesan
         */
		$msg = Messenger::Instance()->Receive(__FILE__);
		$this->mPesan = $msg[0][1];
		$this->mData = $msg[0][0];
        
        /**
         * untuk mengambil data berdasarkan id
         */
         if(isset($_REQUEST['dataId'])){
     	      $idDec = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
               $dataAlokasi = $this->_mAlokasiPenerimaan->GetById($idDec);
               $data['id_unit']=$dataAlokasi[0]['id_unit'];
               $data['id_unit_lama']=$dataAlokasi[0]['id_unit'];
               $data['nama_unit']=$dataAlokasi[0]['nama_unit'];
               $data['id_unit_pusat']=$dataAlokasi[0]['id_unit_pusat'];
               $data['id_unit_pusat_lama']=$dataAlokasi[0]['id_unit_pusat'];
               $data['nama_unit_pusat']=$dataAlokasi[0]['nama_unit_pusat'];
               $data['nama_terima']=$dataAlokasi[0]['nama_terima'];
               $data['id_kode_terima']=$dataAlokasi[0]['id_kode_terima'];
               $data['id_kode_terima_lama']=$dataAlokasi[0]['id_kode_terima'];
               $data['alokasi_unit']=$dataAlokasi[0]['alokasi_unit'];
               $data['alokasi_pusat']=$dataAlokasi[0]['alokasi_pusat'];
               $data['alokasi_operan']=$dataAlokasi[0]['alokasi_operan'];
               $data['alokasi_operan_lama']=$dataAlokasi[0]['alokasi_operan'];
               $data['alokasi_nilai_batas']=$dataAlokasi[0]['alokasi_nilai_batas'];
               $data['idDec'] = $idDec;
         } else {
               $data['id_unit'] = $this->mData['id_unit'];
               $data['id_unit_lama'] = $this->mData['id_unit_lama'];
               $data['nama_unit'] = $this->mData['nama_unit'];
               $data['id_unit_pusat'] = $this->mData['id_unit_pusat'];
               $data['id_unit_pusat_lama'] = $this->mData['id_unit_pusat_lama'];
               $data['nama_unit_pusat'] = $this->mData['nama_unit_pusat'];
               $data['nama_terima'] = $this->mData['nama_terima'];
               $data['id_kode_terima'] = $this->mData['id_kode_terima'];
               $data['id_kode_terima_lama'] = $this->mData['id_kode_terima_lama'];
               $data['alokasi_unit'] = $this->mData['alokasi_unit'];
               $data['alokasi_pusat'] = $this->mData['alokasi_pusat'];
               $data['alokasi_operan'] = $this->mData['alokasi_operan'];
               $data['alokasi_operan_lama'] = $this->mData['alokasi_operan_lama'];
               $data['alokasi_nilai_batas'] = $this->mData['alokasi_nilai_batas'];
               $data['idDec'] = $this->mData['alokasi_id'];
         }
         
       /**
         * untuk membuat combobox operan pada alokasi penerimaan
         */
        $labih_dari_label = GTFWConfiguration::GetValue('language','lebih_dari');
        $kurang_dari_label = GTFWConfiguration::GetValue('language','kurang_dari');
        $arr_operan = array(
                            array('id' => '<','name' =>' < ( '.$kurang_dari_label.' )'),
                            array('id' => '>','name' =>' > ( '.$labih_dari_label.' )'));
        
		Messenger::Instance()->SendToComponent(
								'combobox', 
								'Combobox', 
								'view', 
								'html', 
								'alokasi_operan', 
								array(
										'alokasi_operan', 
										$arr_operan, 
										$data['alokasi_operan'], 
										'false', 
										''), 
								Messenger::CurrentRequest);
                                         
        $return['data']=$data; 
        return $return;
   }

   public function ParseTemplate($data = NULL) 
   {
      /**
       * untuk menampilkan pesan
       */
       if ($this->mPesan) {
            $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->mPesan);
       }
       
      /**
       * end
       */
       
	  $this->mrTemplate->AddVar("content", "URL_POPUP_UNIT_KERJA",
                                    Dispatcher::Instance()->GetUrl(
                                                        'alokasi_penerimaan', 
                                                        'PopupUnitKerja', 
                                                        'view', 
                                                        'html')
                                );
                                
	  $this->mrTemplate->AddVar("content", "URL_POPUP_UNIT_KERJA_PUSAT",
                                    Dispatcher::Instance()->GetUrl(
                                                        'alokasi_penerimaan', 
                                                        'PopupUnitKerjaPusat', 
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
    
        $tambah_label = GTFWConfiguration::GetValue('language','tambah');
        $ubah_label = GTFWConfiguration::GetValue('language','ubah');
        if (isset($data['data']['idDec']) && !empty($data['data']['idDec'])) {
            $url="updateAlokasiPenerimaan";
            $this->mrTemplate->addVar('content','ACTION_LABEL',$ubah_label);         
        } else {	
            $url="addAlokasiPenerimaan";
            $this->mrTemplate->addVar('content','ACTION_LABEL',$tambah_label);
         
        }
	  
        $this->mrTemplate->AddVar('content', 'URL_ACTION', 
                                    Dispatcher::Instance()->GetUrl(
                                                        'alokasi_penerimaan', 
                                                        $url, 
                                                        'do', 
                                                        'html'));	
	   //$this->mrTemplate->AddVar('content', 'URL_ACTION', );
        $this->mrTemplate->addVar('content','DATA_ALOKASI_ID',$data['data']['idDec']);
        
        $this->mrTemplate->AddVar('content','DATA_ID_UNIT',$data['data']['id_unit']);
        $this->mrTemplate->AddVar('content','DATA_ID_UNIT_LAMA',$data['data']['id_unit_lama']);
        $this->mrTemplate->AddVar('content','DATA_NAMA_UNIT',$data['data']['nama_unit']);
        
        $this->mrTemplate->AddVar('content','DATA_ID_UNIT_PUSAT',$data['data']['id_unit_pusat']);
        $this->mrTemplate->AddVar('content','DATA_ID_UNIT_PUSAT_LAMA',$data['data']['id_unit_pusat_lama']);
        $this->mrTemplate->AddVar('content','DATA_NAMA_UNIT_PUSAT',$data['data']['nama_unit_pusat']);
        
        $this->mrTemplate->AddVar('content','DATA_NAMA_TERIMA',$data['data']['nama_terima']);
        $this->mrTemplate->AddVar('content','DATA_ID_KODE_TERIMA',$data['data']['id_kode_terima']);
        $this->mrTemplate->AddVar('content','DATA_ID_KODE_TERIMA_LAMA',$data['data']['id_kode_terima_lama']);
        $this->mrTemplate->AddVar('content','DATA_ALOKASI_UNIT',$data['data']['alokasi_unit']);
        $this->mrTemplate->AddVar('content','DATA_ALOKASI_PUSAT',$data['data']['alokasi_pusat']);
        $this->mrTemplate->AddVar('content','DATA_ALOKASI_OPERAN',$data['data']['alokasi_operan']);
        $this->mrTemplate->AddVar('content','DATA_ALOKASI_OPERAN_LAMA',$data['data']['alokasi_operan_lama']);
        $this->mrTemplate->AddVar('content','DATA_ALOKASI_NILAI_BATAS',$data['data']['alokasi_nilai_batas']);
        
   }
}


?>