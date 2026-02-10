<?php

/**
 * class ViewCetakProgramKegiatan
 * @package lap_program_kegiatan
 * @subpackage response
 * @todo untuk menampilkan laporan cetak program kegiatan
 * @since mei 2012
 * @copyright 2012 Gamatechno Indonesia
 * @author noor hadi <noor.hadi@gamatechno.com>
 */
 

require_once GTFWConfiguration::GetValue('application', 'docroot') . 
    'module/lap_program_kegiatan/business/LapProgramKegiatan.class.php';

class ViewCetakProgramKegiatan extends HtmlResponse
{
    protected $mLapProgramKegiatan;

	public function __construct()
	{
		$this->mLapProgramKegiatan = new LapProgramKegiatan();
	}
    
    public function TemplateModule() 
    {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') . 
            'module/lap_program_kegiatan/template');
        $this->SetTemplateFile('view_cetak_program_kegiatan.html');
    }

    public function TemplateBase() 
    {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-print.html');
      $this->SetTemplateFile('layout-common-print.html');
    }
    
    public function ProcessRequest() 
    {
            $_POST = $_POST->AsArray();
            
            //buat array
            $x=0;
            for($i = 0;$i < sizeof($_POST['pk']['id']); $i++){
                
                if(($_POST['pk']['tipe'][$i] ==  4) &&
                        ($_POST['status_expand_'.$_POST['parent_'.$_POST['pk']['id'][$i]]] == 1) && 
                        ($_POST['status_expand_'.$_POST['up_parent_'.$_POST['pk']['id'][$i]]] == 1) && 
                        ($_POST['status_expand_'.$_POST['top_parent_'.$_POST['pk']['id'][$i]]] == 1)){
                    $b =   $_POST['biaya_'. $_POST['pk']['id'][$i]];
                    
                    $data_program_kegiatan[$x]['id'] = $_POST['pk']['id'][$i];
                    $data_program_kegiatan[$x]['kode'] = $_POST['pk']['kode'][$i];
                    $data_program_kegiatan[$x]['nama'] = $_POST['pk']['nama'][$i];
                    $data_program_kegiatan[$x]['unit_nama'] =$_POST['unit_nama_'.$_POST['pk']['id'][$i]];    
                    $data_program_kegiatan[$x]['blt'] =($b == 11) ? $_POST['komponen_'.$_POST['pk']['id'][$i]]:'0';
                    $data_program_kegiatan[$x]['bltt'] =($b == 10) ? $_POST['komponen_'.$_POST['pk']['id'][$i]]:'0';
                    $data_program_kegiatan[$x]['btlt'] =($b == 01) ? $_POST['komponen_'.$_POST['pk']['id'][$i]]:'0';
                    $data_program_kegiatan[$x]['btltt'] =($b == 00) ? $_POST['komponen_'.$_POST['pk']['id'][$i]]:'0';
                    $data_program_kegiatan[$x]['kuantitas'] =$_POST['kuantitas_'.$_POST['pk']['id'][$i]];
                    $data_program_kegiatan[$x]['biaya_nilai_satuan'] = 
                                                        $_POST['biaya_nilai_satuan_'.$_POST['pk']['id'][$i]];
                    $data_program_kegiatan[$x]['jumlah'] = $_POST['komponen_'.$_POST['pk']['id'][$i]];
                    $data_program_kegiatan[$x]['tipe'] =$_POST['pk']['tipe'][$i];  
                 
                }elseif(($_POST['pk']['tipe'][$i] ==  3) &&
                        ($_POST['status_expand_'.$_POST['parent_'.$_POST['pk']['id'][$i]]] == 1) && 
                        ($_POST['status_expand_'.$_POST['up_parent_'.$_POST['pk']['id'][$i]]] == 1)){
                            
                    $data_program_kegiatan[$x]['id'] =$_POST['pk']['id'][$i];
                    $data_program_kegiatan[$x]['kode'] = $_POST['pk']['kode'][$i];
                    $data_program_kegiatan[$x]['nama'] = $_POST['pk']['nama'][$i];
                    $data_program_kegiatan[$x]['unit_nama'] =$_POST['unit_nama_'.$_POST['pk']['id'][$i]];
                    $data_program_kegiatan[$x]['blt'] = $_POST['total_komp_blt_'.$_POST['pk']['id'][$i]];
                    $data_program_kegiatan[$x]['bltt'] = $_POST['total_komp_bltt_'.$_POST['pk']['id'][$i]];
                    $data_program_kegiatan[$x]['btlt'] = $_POST['total_komp_btlt_'.$_POST['pk']['id'][$i]];
                    $data_program_kegiatan[$x]['btltt'] = $_POST['total_komp_btltt_'.$_POST['pk']['id'][$i]];
                    $data_program_kegiatan[$x]['kuantitas'] = '';
                    $data_program_kegiatan[$x]['biaya_nilai_satuan'] = '';
                    $data_program_kegiatan[$x]['jumlah'] = '';                    
                    $data_program_kegiatan[$x]['tipe'] =$_POST['pk']['tipe'][$i];                            
                }elseif(($_POST['pk']['tipe'][$i] ==  2) &&
                        ($_POST['status_expand_'.$_POST['parent_'.$_POST['pk']['id'][$i]]] == 1)){
                    $data_program_kegiatan[$x]['id'] =$_POST['pk']['id'][$i];
                    $data_program_kegiatan[$x]['kode'] = $_POST['pk']['kode'][$i];
                    $data_program_kegiatan[$x]['nama'] = $_POST['pk']['nama'][$i];
                    $data_program_kegiatan[$x]['unit'] ='';                                
                    $data_program_kegiatan[$x]['blt'] = $_POST['total_komp_blt_'.$_POST['pk']['id'][$i]];
                    $data_program_kegiatan[$x]['bltt'] = $_POST['total_komp_bltt_'.$_POST['pk']['id'][$i]];
                    $data_program_kegiatan[$x]['btlt'] = $_POST['total_komp_btlt_'.$_POST['pk']['id'][$i]];
                    $data_program_kegiatan[$x]['btltt'] = $_POST['total_komp_btltt_'.$_POST['pk']['id'][$i]];
                    $data_program_kegiatan[$x]['kuantitas'] = '';
                    $data_program_kegiatan[$x]['biaya_nilai_satuan'] = '';
                    $data_program_kegiatan[$x]['jumlah'] = '';
                    $data_program_kegiatan[$x]['tipe'] =$_POST['pk']['tipe'][$i];
                }elseif($_POST['pk']['tipe'][$i] ==  1){
                    $data_program_kegiatan[$x]['id'] =$_POST['pk']['id'][$i];
                    $data_program_kegiatan[$x]['kode'] = $_POST['pk']['kode'][$i];
                    $data_program_kegiatan[$x]['nama'] = $_POST['pk']['nama'][$i];
                    $data_program_kegiatan[$x]['unit'] ='';                                
                    $data_program_kegiatan[$x]['blt'] = $_POST['total_komp_blt_'.$_POST['pk']['id'][$i]];
                    $data_program_kegiatan[$x]['bltt'] = $_POST['total_komp_bltt_'.$_POST['pk']['id'][$i]];
                    $data_program_kegiatan[$x]['btlt'] = $_POST['total_komp_btlt_'.$_POST['pk']['id'][$i]];
                    $data_program_kegiatan[$x]['btltt'] = $_POST['total_komp_btltt_'.$_POST['pk']['id'][$i]];
                    $data_program_kegiatan[$x]['kuantitas'] = '';
                    $data_program_kegiatan[$x]['biaya_nilai_satuan'] = '';
                    $data_program_kegiatan[$x]['jumlah'] = '';
                    $data_program_kegiatan[$x]['tipe'] =$_POST['pk']['tipe'][$i];
                }else{
                    continue;
                }
                $x++;
              
            }
            
            $return['tb_lt'] = $_POST['tb_lt'];
            $return['tb_ltt'] = $_POST['tb_ltt'];
            $return['tb_tlt'] = $_POST['tb_tlt'];
            $return['tb_tltt'] = $_POST['tb_tltt'];
            $return['list_data_cetak'] = $data_program_kegiatan;
            $return['unit_nama'] = $_POST['unit_nama'];
            $return['th_anggar_nama'] = $_POST['th_anggar_nama'];
            //echo '<pre>';
            //print_r($data_program_kegiatan);
            //echo '<pre>';
            return $return;
    }
    
    public function ParseTemplate($data = NULL) 
    {
         $this->mrTemplate->addVar('content','UNIT_NAMA', $data['unit_nama']);    
         $this->mrTemplate->addVar('content','TH_ANGGAR_NAMA', $data['th_anggar_nama']);
  		if (empty($data['list_data_cetak'])) {
			$this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'YES');
		} else {
            $this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'NO');
            $ldc = $data['list_data_cetak'];
            $no = 1;
            for ($i=0; $i<sizeof($ldc);$i++) {
                
                $ldc[$i]['blt'] =number_format($ldc[$i]['blt'],0,',','.');
                $ldc[$i]['bltt'] =number_format($ldc[$i]['bltt'],0,',','.');
                $ldc[$i]['btlt'] =number_format($ldc[$i]['btlt'],0,',','.');
                $ldc[$i]['btltt'] = number_format($ldc[$i]['btltt'],0,',','.');
                
                if($ldc[$i]['tipe'] == 4){
                    $ldc[$i]['biaya_nilai_satuan'] = number_format($ldc[$i]['biaya_nilai_satuan'],0,',','.');
                    $ldc[$i]['jumlah'] = number_format($ldc[$i]['jumlah'],0,',','.');
                }
                
                if($ldc[$i]['tipe'] == 1){
                    $ldc[$i]['kode'] = '<strong>'.$ldc[$i]['kode'].'</strong>';
                    $ldc[$i]['nama'] = '<strong>'.$ldc[$i]['nama'].'</strong>';
                    $ldc[$i]['blt'] = '<strong>'.$ldc[$i]['blt'].'</strong>';
                    $ldc[$i]['bltt'] = '<strong>'.$ldc[$i]['bltt'].'</strong>';
                    $ldc[$i]['btlt'] = '<strong>'.$ldc[$i]['btlt'].'</strong>';
                    $ldc[$i]['btltt'] = '<strong>'.$ldc[$i]['btltt'].'</strong>';
                    $ldc[$i]['nomor'] = '<strong>'.$no.'</strong>';
                    $no++;    
                }
                if($ldc[$i]['tipe'] == 2){
                    $ldc[$i]['kode'] = '<strong><i>'.$ldc[$i]['kode'].'</i></strong>';
                    $ldc[$i]['nama'] = '<strong><i>'.$ldc[$i]['nama'].'</i></strong>';
                    $ldc[$i]['blt'] = '<strong><i>'.$ldc[$i]['blt'].'</i></strong>';
                    $ldc[$i]['bltt'] = '<strong><i>'.$ldc[$i]['bltt'].'</i></strong>';
                    $ldc[$i]['btlt'] = '<strong><i>'.$ldc[$i]['btlt'].'</i></strong>';
                    $ldc[$i]['btltt'] = '<strong><i>'.$ldc[$i]['btltt'].'</i></strong>';
                }
                if($ldc[$i]['tipe'] == 3){
                    $ldc[$i]['kode'] = '<i>'.$ldc[$i]['kode'].'</i>';
                    $ldc[$i]['nama'] = '<i>'.$ldc[$i]['nama'].'</i>';
                    $ldc[$i]['blt'] = '<i>'.$ldc[$i]['blt'].'</i>';
                    $ldc[$i]['bltt'] = '<i>'.$ldc[$i]['bltt'].'</i>';
                    $ldc[$i]['btlt'] = '<i>'.$ldc[$i]['btlt'].'</i>';
                    $ldc[$i]['btltt'] = '<i>'.$ldc[$i]['btltt'].'</i>';
                }
                
                                
                $this->mrTemplate->AddVars('data_item', $ldc[$i], 'DATA_');
				$this->mrTemplate->parseTemplate('data_item', 'a');
            }
            
            $this->mrTemplate->addVar('data_grid','TOTAL_BLT', number_format($data['tb_lt'],0,',','.'));
            $this->mrTemplate->addVar('data_grid','TOTAL_BLTT',number_format($data['tb_ltt'],0,',','.'));
            $this->mrTemplate->addVar('data_grid','TOTAL_BTLT',number_format($data['tb_tlt'],0,',','.'));
            $this->mrTemplate->addVar('data_grid','TOTAL_BTLTT',number_format($data['tb_tltt'],0,',','.'));
                
		}        
    }
}