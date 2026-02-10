<?php

/**
 * class ViewPopupKodeAset
 * popup untuk menampilkan data integrasi dengan database aset / kode aset
 * @package komponen
 * @since 26 april 2012
 * @access public
 * @copyright 2012 Gamatechno
 * @author noor hadi <noor.hadi@gamatechno.com>
 *  
 */

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
		'module/komponen/business/KomponenKodeAset.class.php';

class ViewPopupKodeAset extends HtmlResponse 
{
    
	protected $kodeAsetsObj;

	public function TemplateModule() 
    {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
				'module/komponen/template');
		$this->SetTemplateFile('view_popup_kode_aset.html');
	}
   
    public function TemplateBase() 
    {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 
	  			'main/template/');
        $this->SetTemplateFile('document-common-popup.html');
        $this->SetTemplateFile('layout-common-popup.html');
    }
	
    public function ProcessRequest() 
    {
		$this->kodeAsetsObj = new KomponenKodeAset();
        /**
         * cek koneksi database dengan gtAset
         */
        if($this->kodeAsetsObj->IsConnected()){
		  if($_POST || isset($_GET['cari'])) {
			if(isset($_POST['kode'])) {
				$kode = $_POST['kode'];
			} elseif(isset($_GET['kode'])) {
				$kode = Dispatcher::Instance()->Decrypt($_GET['kode']);
			} else {
				$kode = '';
			}
		  
			if(isset($_POST['nama'])) {
				$nama = $_POST['nama'];
			} elseif(isset($_GET['nama'])) {
				$nama = Dispatcher::Instance()->Decrypt($_GET['nama']);
			} else {
				$nama = '';
			}
		  }
		
	       //view
		  $totalData = $this->kodeAsetsObj->GetCountKodeAset($kode,$nama);
		  $itemViewed = 20;
		  $currPage = 1;
		  $startRec = 0 ;
		  if(isset($_GET['page'])) {
			$currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
			$startRec =($currPage-1) * $itemViewed;
		  }
		  $dataKodeAset = $this->kodeAsetsObj->GetKodeAset($kode,$nama,$startRec,$itemViewed);
        
		  $url = Dispatcher::Instance()->GetUrl(
								Dispatcher::Instance()->mModule, 
								Dispatcher::Instance()->mSubModule, 
								Dispatcher::Instance()->mAction, 
								Dispatcher::Instance()->mType . 
								'&kode=' . Dispatcher::Instance()->Encrypt($kode) . 
								'&nama=' . Dispatcher::Instance()->Encrypt($nama) . 
								'&cari=' . Dispatcher::Instance()->Encrypt(1));

           $dest = "popup-subcontent";

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
										$currPage, 
										$dest), 
								Messenger::CurrentRequest);

				
		  $return['dataKodeAset'] = $dataKodeAset;
		  $return['start'] = $startRec+1;

		  $return['search']['kode'] = $kode;
		  $return['search']['nama'] = $nama;
        }
        return $return;
	}
	
	public function ParseTemplate($data = NULL) 
    {
        /**
         * jika koneksi database dengan aset gagal maka data tidak ditampilkan
         */
        if(!$this->kodeAsetsObj->IsConnected()){
            $this->mrTemplate->AddVar('integrasi_aset', 'INTEGRASI', 'NO');
        } else {
            $this->mrTemplate->AddVar('integrasi_aset', 'INTEGRASI', 'YES');
            $search = $data['search'];
            $this->mrTemplate->AddVar('isi', 'KODE', $search['kode']);
            $this->mrTemplate->AddVar('isi', 'NAMA', $search['nama']);
            $this->mrTemplate->AddVar('isi', 'URL_SEARCH', 
				Dispatcher::Instance()->GetUrl(
						'komponen', 
						'PopupKodeAset', 
						'view', 
						'html'));
            /**				
            if($this->Pesan) {
			     $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			     $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
			     $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
            }
            */
            if (empty($data['dataKodeAset'])) {
			     $this->mrTemplate->AddVar('data_aset', 'ASET_EMPTY', 'YES');
            } else {
			
			     $this->mrTemplate->AddVar('data_aset', 'ASET_EMPTY', 'NO');
			     $dataKodeAset = $data['dataKodeAset'];

			     for ($i=0; $i<sizeof($dataKodeAset); $i++) {
                        $no = $i+$data['start'];
				        $dataKodeAset[$i]['number'] = $no;
				        $dataKodeAset[$i]['link'] = str_replace("'","\'",$dataKodeAset[$i]['kode']);
				        $this->mrTemplate->AddVars('data_aset_item', $dataKodeAset[$i], 'ASET_');
				        $this->mrTemplate->parseTemplate('data_aset_item', 'a');	 
			     }
            }
        }		
	}
}