<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
    'module/program_kegiatan/response/ProcessSubKegiatan.proc.class.php';

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
    'module/program_kegiatan/business/PopupIk.class.php';
    
class ViewInputSubKegiatan extends HtmlResponse 
{

	protected $SubKegiatan;
    //protected $IK;
	
	public function __construct()
	{
		$this->SubKegiatan = new SubKegiatan;
	}
    
    function TemplateModule() 
    {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') .
         'module/program_kegiatan/template');
      $this->SetTemplateFile('input_sub_kegiatan.html');
    }
   
    function ProcessRequest() 
    {
      if (isset($_GET['idTahun']))
      {
         if($_GET['idTahun']!='')
		 {
		 $idTahun=Dispatcher::Instance()->Decrypt($_GET['idTahun']);
		 }else
		 {$idTahun='';}
      }
	  
	  //$subkegiatanObj =  new SubKegiatan();
      //$ik = new PopupIk();
      //$data_ik = $ik->GetData();
      	 
      $jeniskegiatan_selected=''; //inisialisasi combo box 	  
	  
	  	 
	  if(isset($_GET['grp'])) { //action pengeditan
	    $grp=Dispatcher::Instance()->Decrypt($_GET['grp']);	         				
		$return['grp']=$grp;
		$subkegiatanObj = new SubKegiatan();
        $data = $this->SubKegiatan->GetDataById($grp);		

		if(sizeof($data) > 0 ) {
	     $return['subkegiatan']=$data[0];
		 $return['subkegiatan']['id'] = Dispatcher::Instance()->Encrypt($return['subkegiatan']['id']);
		 //$jeniskegiatan_label= $return['subkegiatan']['jeniskegiatan_label'];
		 $return['grp']= Dispatcher::Instance()->Encrypt($return['subkegiatan']['id']);
	    }			
	  }    
	        
	    $processsubKegiatan= new ProcessSubKegiatan();
		$tmp=$processsubKegiatan->parsingUrl(__FILE__);	
		
		if(isset($tmp['data'])) {
		  $return['subkegiatan']=$tmp['data']['subkegiatan'];
          if(trim($return['subkegiatan']['id']) != '')		  
             $return['grp'] = Dispatcher::Instance()->Encrypt($return['subkegiatan']['id']);
		  
		  //$jeniskegiatan_label= $return['subkegiatan']['jeniskegiatan_label'];
		}

		if(isset($_GET['idTahun']))
			$idTahun = $_GET['idTahun'];
		
		$return['tahun_anggaran'] = $this->SubKegiatan->GetTahunAnggaranById($idTahun);
		//$idProgram = $data['0']['program_nomor'];
		#print_r($data);
      //INI HASIL DARI PROSES SETELAH KEGIATAN DIPILIH
      if(trim($_GET['kegiatan_id']) != "") { 
         //$idProgram = Dispatcher::Instance()->Decrypt($_GET['programId']);
         $return['subkegiatan']['kegiatan_nama'] = Dispatcher::Instance()->Decrypt($_GET['kegiatan_nama']);
         $return['subkegiatan']['kegiatan_id'] = Dispatcher::Instance()->Decrypt($_GET['kegiatan_id']);
         $return['subkegiatan']['program_label'] = Dispatcher::Instance()->Decrypt($_GET['program_label']);
         $return['kode_selanjutnya'] = $this->SubKegiatan->GetKodeSelanjutnya($return['subkegiatan']['kegiatan_id']);
		
      } else {
		   //$idProgram = $data['0']['program_nomor'];
      }
      //print_r($return);
      /*
		$arrProgram = $subkegiatanObj->GetDataProgram($idTahun);
		Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 'data[program][programId]', 
	     array('data[program][programId]', $arrProgram, $idProgram, 'true', ' style="width:380px;" '), 
		 Messenger::CurrentRequest);
       */
		$return['subkegiatan']['unit_kerja_ref']= $this->SubKegiatan->GetUnitKerjaRef();
		
		if(isset($tmp['msg']))
		  $return['msg']=$tmp['msg'];       
		 
	    $return['idTahun']=$idTahun;   
         //$return['data_ik'] = $data_ik;
        return $return;
    }

    function ParseTemplate($data = NULL) 
    {     
      //debug($data);
      if (isset ($data['msg'])) {	     
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $data['msg']['message']);
      }
      
      
      if (isset($data['grp'])) {
         $url="updateSubKegiatan";
         $tambah="Ubah";         
      } else {	
         $url="addSubKegiatan";
         $tambah="Tambah";	  
         
      }
		#print_r($data);
        $popup_ik   = Dispatcher::Instance()->GetUrl('program_kegiatan','PopupIk','view','html');
        $this->mrTemplate->AddVar('content','POPUP_IK',$popup_ik);
        
		$this->mrTemplate->AddVar('content', 'POPUP_KEGIATAN', 
                    Dispatcher::Instance()->GetUrl( 'program_kegiatan', 
                                                    'kegiatan', 
                                                    'popup', 
                                                    'html'). 
                                                    '&idTahun='.$data['idTahun']);
                                                    
		$this->mrTemplate->AddVar('content', 'POPUP_RKAKL_SUBKEGIATAN', 
                    Dispatcher::Instance()->GetUrl( 'program_kegiatan', 
                                                    'popupRkaklSubKegiatan', 
                                                    'view', 
                                                    'html'));
                                                    
        $this->mrTemplate->AddVar('content', 'TAHUN_ID', $data['tahun_anggaran']['id']);
		$this->mrTemplate->AddVar('content', 'TAHUN_NAME', $data['tahun_anggaran']['name']);
        $this->mrTemplate->AddVar('content', 'SEARCH_SUBKEGIATAN_PROGRAMID', $data['subkegiatan']['kegiatan_id']);
	    $this->mrTemplate->AddVar('content', 'SEARCH_KEGIATAN_NAMA', $data['subkegiatan']['kegiatan_nama']);	  
	    $this->mrTemplate->AddVar('content', 'DATA_SUBKEGIATAN_ID', $data['subkegiatan']['id'] );	  
	    $this->mrTemplate->AddVar('content', 'DATA_SUBKEGIATAN_KODE', $data['subkegiatan']['kode'] );
	    $this->mrTemplate->AddVar('content', 'DATA_SUBKEGIATAN_NAMA', $data['subkegiatan']['nama']);
	    $this->mrTemplate->AddVar('content', 'DATA_SUBKEGIATAN_KODE_LABEL',$data['subkegiatan']['kode_label'] );	
	    $this->mrTemplate->AddVar('content', 'DATA_SUBKEGIATAN_RKAKL', $data['subkegiatan']['rkakl_subkegiatan'] );
	    $this->mrTemplate->AddVar('content', 'DATA_SUBKEGIATAN_RKAKL_LABEL', 
                    $data['subkegiatan']['rkakl_subkegiatan_label']);	  
	    $this->mrTemplate->AddVar('content', 'PROGRAM_LABEL', $data['subkegiatan']['program_label']);
        $this->mrTemplate->AddVar('content', 'IK_ID', $data['subkegiatan']['ik_id']);
        $this->mrTemplate->AddVar('content', 'IK_NAMA', $data['subkegiatan']['ik_nama']);	  
        if(trim($data['subkegiatan']['program_label']) == '') {
            $this->mrTemplate->AddVar('content', 'PROGRAM_LABEL_DISPLAY', "none");	  
        } else {
            $this->mrTemplate->AddVar('content', 'PROGRAM_LABEL_DISPLAY', "");	  
        }
        if($data['kode_selanjutnya'])	
            $this->mrTemplate->AddVar('content', 'DATA_KODE_SELANJUTNYA', "(kode selanjutnya : " . 
                        $data['kode_selanjutnya']['nomor'] . ")");
            $this->mrTemplate->AddVar('content', 'URL_ACTION', 
                            Dispatcher::Instance()->GetUrl('program_kegiatan', $url, 'do', 'html'));
            // $this->mrTemplate->AddVar('content', 'URL_VIEW', 
            //Dispatcher::Instance()->GetUrl('program', 'program', 'view', 'html') );

        //list indikator kegiatan
        /**
            if(!empty($data['data_ik'])){
                
                for($k = 0 ; $k < sizeof($data['data_ik']); $k++){
   					$this->mrTemplate->AddVars('ik_ref',$data['data_ik'][$k], 'IK_');
					$this->mrTemplate->parseTemplate('ik_ref', 'a');
                }   
            }
          */
        $this->ListDataIK($data['subkegiatan']['id']);  
        //end list
        //add list unit kerja ref
        $this->ListUnitKerjaRef($data['subkegiatan']['id']);	
        //end unit kerja ref
   }

   /**
    * fungsi ListUnitKerjaRef
    * @param $kegrefid = id dari kegiatan ref ID (kegrefid)
    * @todo menampilkan list unit kerja ref
    */
   function ListUnitKerjaRef($kegrefId)
   {
   		//$kegrefid = $data['subkegiatan']['id'];
        
		$dataUnit = $this->SubKegiatan->GetListUnitKerja($kegrefId);
		if(count($dataUnit)>0){
		      $str ='';
   			foreach($dataUnit as $key => $value){
				//jika unitkerjaId berada pada tabel finansi_pa_kegiatan_ref_unit_kerja maka 
				//centang cekbox
				
				if($data['subkegiatan']['unitkerjaid'][$value['unitkerja_id']]){
				    $cek ='checked="checked"';
					//$this->mrTemplate->AddVar('unit_kerja_ref', 'UK_CHECKED', 'checked="checked"');	
				}else{
					if($value['total'] > 0 && !isset($data['subkegiatan']['checkbox'])) {
						//$this->mrTemplate->AddVar('unit_kerja_ref', 'UK_CHECKED','checked="checked"');
                        $cek ='checked="checked"';
					}else { 
						//$this->mrTemplate->AddVar('unit_kerja_ref', 'UK_CHECKED', '');
                        $cek ='';
					}
				}					
				if($value['unitkerja_parent'] == 0){
				    $str .= '<input type="checkbox" name="data[subkegiatan][unitkerjaid]['.
                            $value['unitkerja_id'].']" class="CheckBoxFW_parent" value="'.
                            $value['unitkerja_id'].'" '.$cek.'   /> '.
                            $value['unitkerja_nama'].'<br />';
				    
				    /**
					$this->mrTemplate->addVar('unit_kerja_ref', 'UK_PARENT', 'YES');
					$this->mrTemplate->AddVars('unit_kerja_ref', $value, 'UK_');
   					$this->mrTemplate->parseTemplate('unit_kerja_ref', 'a');
                    */
   				} else {
                    $str .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.
				            '<input type="checkbox" name="data[subkegiatan][unitkerjaid]['.
                            $value['unitkerja_id'].']" class="CheckBoxFW_child" value="'.
                            $value['unitkerja_id'].'" '.$cek.' /> '.
                            $value['unitkerja_nama'].'<br />';  				     
   				 
   				     /**
  					$this->mrTemplate->addVar('unit_kerja_ref', 'UK_PARENT', 'NO');
					$this->mrTemplate->AddVars('unit_kerja_ref', $value, 'UK_');
					$this->mrTemplate->parseTemplate('unit_kerja_ref', 'a');
                    */
   				}
			$this->mrTemplate->addVar('content', 'LIST_UNIT_KERJA', $str);	
	       }
           
      }		  
   }
   /**
    * fungsi List Indikator Kegiatan
    * @param $kegrefId = id dari kegiatan ref ID
    * @todo untuk menampilkan indikator kegiatan
    * @since 14 Juni 2012
    */
    function ListDataIK($kegrefId)
    {
        $dataIK = $this->SubKegiatan->GetListDataIK($kegrefId);
          if(!empty($dataIK)){
                
                for($k = 0 ; $k < sizeof($dataIK); $k++){
                    if($dataIK[$k]['total'] > 0){
                        $dataIK[$k]['cek']='checked="checked"';
                    } else {
                        $dataIK[$k]['cek']='';
                    }
   					$this->mrTemplate->AddVars('ik_ref',$dataIK[$k], 'IK_');
					$this->mrTemplate->parseTemplate('ik_ref', 'a');
                }   
            }
    }   
   
}