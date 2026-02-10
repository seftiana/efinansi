<?php

/**
 * class ViewLapProgramKegiatanAsyn
 * @package lap_program_kegiatan
 * @subpackage response
 * @todo untuk menampilkan child program kegiatan secara asynchron
 * @since mei 2012
 * @copyright 2012 Gamatechno Indonesia
 * @author noor hadi <noor.hadi@gamatechno.com>
 */
 
 
require_once GTFWConfiguration::GetValue('application', 'docroot') . 
'module/lap_program_kegiatan/business/LapProgramKegiatan.class.php';

class ViewLapProgramKegiatanAsyn extends HtmlResponse
{

	protected $mLapProgramKegiatan;

	protected $data;
    
	public function __construct()
	{
		$this->mLapProgramKegiatan = new LapProgramKegiatan();
	}
    
	public function TemplateModule()
	{
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 
				'module/lap_program_kegiatan/template');
		$this->SetTemplateFile('view_program_kegiatan_asyn.html');
	}
   
    public function TemplateBase() 
    {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 
                'main/template/');
        $this->SetTemplateFile('document-common-blank.html');
        $this->SetTemplateFile('layout-common-blank.html');
    }
        
	public function ProcessRequest()
	{
       $id_parent = isset($_REQUEST['parent_node']) ? $_REQUEST['parent_node'] : 0;
       $ta = $_GET['ta'];
       $unit_id = $_GET['unit_id'];
	   $dataList = $this->mLapProgramKegiatan->GetListProgramKegiatan($id_parent,$ta,$unit_id);
       $return['data'] = $dataList;
       $return['ta'] = $ta;
       $return['unit_id']= $unit_id;
	   return $return;
	}
    
	public function ParseTemplate($data = NULL)
	{
      if(!empty($data['data']))
      {
          $count = count($data['data']);
          for($i = 0 ; $i < $count ; $i++){
            if($data['data'][$i]['tipe'] == 4){
                if($data['data'][$i]['biaya_langsung'] == 0 && $data['data'][$i]['biaya_tetap'] == 0){
                    $cek_tak_langsung_tak_tetap = ' checked="checked"';
                    $cek_tak_langsung_tetap ='';
                    $cek_langsung_tak_tetap = '';
                    $cek_langsung_tetap ='';
                    $data['data'][$i]['c_lt'] = 0;
                    $data['data'][$i]['c_ltt'] = 0;
                    $data['data'][$i]['c_tlt'] = 0;
                    $data['data'][$i]['c_tltt'] = 1;
                    $data['data'][$i]['posisi'] = 00;
                    
                } elseif($data['data'][$i]['biaya_langsung'] == 0 && $data['data'][$i]['biaya_tetap'] == 1){
                    $cek_tak_langsung_tetap = ' checked="checked"';
                    $cek_tak_langsung_tak_tetap ='';
                    $cek_langsung_tak_tetap = '';
                    $cek_langsung_tetap ='';
                    $data['data'][$i]['c_lt'] = 0;
                    $data['data'][$i]['c_ltt'] = 0;
                    $data['data'][$i]['c_tlt'] = 1;
                    $data['data'][$i]['c_tltt'] = 0;
                    $data['data'][$i]['posisi'] = 01;                    
                } elseif($data['data'][$i]['biaya_langsung'] == 1 && $data['data'][$i]['biaya_tetap'] == 0){
                    $cek_langsung_tak_tetap = ' checked="checked"';
                    $cek_tak_langsung_tak_tetap='';
                    $cek_tak_langsung_tetap ='';
                    $cek_langsung_tetap ='';                    
                    $data['data'][$i]['c_lt'] = 0;
                    $data['data'][$i]['c_ltt'] = 1;
                    $data['data'][$i]['c_tlt'] = 0;
                    $data['data'][$i]['c_tltt'] = 0;
                    $data['data'][$i]['posisi'] = 10;                    

                }else {
                    $cek_langsung_tetap = ' checked="checked"';
                    $cek_langsung_tak_tetap = '';
                    $cek_tak_langsung_tak_tetap='';
                    $cek_tak_langsung_tetap ='';
                    $data['data'][$i]['c_lt'] = 1;
                    $data['data'][$i]['c_ltt'] = 0;
                    $data['data'][$i]['c_tlt'] = 0;
                    $data['data'][$i]['c_tltt'] = 0;                    
                    $data['data'][$i]['posisi'] = 11;
                }
                $data['data'][$i]['align'] = 'center';
                $data['data'][$i]['langsung_tetap']='<input onclick="getTotalBiaya(\''.
                                                    $data['data'][$i]['id'].'\',this,'.
                                                    '\''.$data['data'][$i]['parent'].'\','.
                                                    '\''.$data['data'][$i]['up_parent'].'\','.
                                                    '\''.$data['data'][$i]['top_parent'].'\''.
                                                    ')" type="radio" name="biaya_'.
                                                    $data['data'][$i]['id'].'"  value="11" '.
                                                    $cek_langsung_tetap.'/>';
                $data['data'][$i]['langsung_tak_tetap']='<input  onclick="getTotalBiaya(\''.
                                                    $data['data'][$i]['id'].'\',this,'.
                                                    '\''.$data['data'][$i]['parent'].'\','.
                                                    '\''.$data['data'][$i]['up_parent'].'\','.
                                                    '\''.$data['data'][$i]['top_parent'].'\''.
                                                    ')" type="radio" name="biaya_'.
                                                    $data['data'][$i]['id'].'"  value="10" '.
                                                    $cek_langsung_tak_tetap.'/>';
                                                    
                $data['data'][$i]['tak_langsung_tetap']='<input onclick="getTotalBiaya(\''.
                                                    $data['data'][$i]['id'].'\',this,'.
                                                    '\''.$data['data'][$i]['parent'].'\','.
                                                    '\''.$data['data'][$i]['up_parent'].'\','.
                                                    '\''.$data['data'][$i]['top_parent'].'\''.
                                                    ')" type="radio" name="biaya_'.
                                                    $data['data'][$i]['id'].'"  value="01" '.
                                                    $cek_tak_langsung_tetap.'/>';
                $data['data'][$i]['tak_langsung_tak_tetap']='<input  onclick="getTotalBiaya(\''.
                                                    $data['data'][$i]['id'].'\',this,'.
                                                    '\''.$data['data'][$i]['parent'].'\','.
                                                    '\''.$data['data'][$i]['up_parent'].'\','.
                                                    '\''.$data['data'][$i]['top_parent'].'\''.
                                                    ')" type="radio" name="biaya_'.
                                                    $data['data'][$i]['id'].'"  value="00" '.
                                                    $cek_tak_langsung_tak_tetap.'/>';
                $data['data'][$i]['total_raw'] = 
                                ($data['data'][$i]['jumlah'] * $data['data'][$i]['biaya']);
                $data['data'][$i]['total'] =
                            number_format(($data['data'][$i]['jumlah'] * $data['data'][$i]['biaya']),'0',',','.');     
                $data['data'][$i]['biaya'] = number_format($data['data'][$i]['biaya'],0,',','.');        
            } 

            if($data['data'][$i]['tipe']== 3){
                $data['data'][$i]['align'] = 'right';
                $data['data'][$i]['f_kode'] = $data['data'][$i]['kode'];
                $data['data'][$i]['f_nama']=  $data['data'][$i]['nama'];
                $c = $this->mLapProgramKegiatan->CountNominalSubKegiatan(
                                                                $data['data'][$i]['id'],
                                                                $data['ta'],
                                                                $data['unit_id']
                                                                );
                $data['data'][$i]['langsung_tetap'] = $c['b_langsung_tetap'];
                $data['data'][$i]['langsung_tak_tetap']=  $c['b_langsung_tak_tetap'];
                $data['data'][$i]['tak_langsung_tetap']=  $c['b_tak_langsung_tetap'];
                $data['data'][$i]['tak_langsung_tak_tetap']=  $c['b_tak_langsung_tak_tetap'];
              
                $data['data'][$i]['on_expand'] = ' onclick="getNominalTotalKomponenExpand(\''.
                                        $data['data'][$i]['id'].'\');"';
                $data['data'][$i]['on_collapse'] = ' onclick="getNominalTotalKomponenCollapse(\''.
                                        $data['data'][$i]['id'].'\');"';
                                           
            }
            
            if($data['data'][$i]['tipe'] == 2){
                $data['data'][$i]['align'] = 'right';
                $data['data'][$i]['f_kode'] = '<i>'.$data['data'][$i]['kode'].'</i>';
                $data['data'][$i]['f_nama']=  '<i>'.$data['data'][$i]['nama'].'</i>';
                $c = $this->mLapProgramKegiatan->CountNominalKegiatan(
                                                                $data['data'][$i]['id'],
                                                                $data['ta'],
                                                                $data['unit_id']
                                                                );
                $data['data'][$i]['langsung_tetap'] = $c['b_langsung_tetap'];
                $data['data'][$i]['langsung_tak_tetap']=  $c['b_langsung_tak_tetap'];
                $data['data'][$i]['tak_langsung_tetap']=  $c['b_tak_langsung_tetap'];
                $data['data'][$i]['tak_langsung_tak_tetap']=  $c['b_tak_langsung_tak_tetap'];
                 
                
                $data['data'][$i]['on_collapse']=' onclick="collapseProgramKegiatan('.
                                                '\''.$data['data'][$i]['id'].'\');"';
                $data['data'][$i]['on_expand']=' onclick="expandProgramKegiatan('.
                                                '\''.$data['data'][$i]['id'].'\');"';
                
            }
            
                /** terformat **/
                $data['data'][$i]['f_langsung_tetap'] = 
                            number_format((double)$data['data'][$i]['langsung_tetap'],0,',','.');
                $data['data'][$i]['f_langsung_tak_tetap']= 
                            number_format((double)$data['data'][$i]['langsung_tak_tetap'],0,',','.');
                $data['data'][$i]['f_tak_langsung_tetap']= 
                            number_format((double)$data['data'][$i]['tak_langsung_tetap'],0,',','.');
                $data['data'][$i]['f_tak_langsung_tak_tetap']=  
                            number_format((double)$data['data'][$i]['tak_langsung_tak_tetap'],0,',','.');
                /** end **/
                
                if($this->mLapProgramKegiatan->CountListPrgramKegiatan(
                                                            $data['data'][$i]['id'],
                                                            $data['ta'],
                                                            $data['unit_id']
                                                            ) > 0){
					$this->mrTemplate->addVar('pk', 'IS_PARENT', 'YES');
					$this->mrTemplate->AddVars('pk', $data['data'][$i], '');
   					$this->mrTemplate->parseTemplate('pk', 'a');
                } else {
                    if($data['data'][$i]['tipe'] == 4){
                        $this->mrTemplate->addVar('pk', 'IS_PARENT', 'NO');
					   $this->mrTemplate->AddVars('pk', $data['data'][$i], '');
   					    $this->mrTemplate->parseTemplate('pk', 'a');
                    }
                }
                	
          }  
      }
	}
}