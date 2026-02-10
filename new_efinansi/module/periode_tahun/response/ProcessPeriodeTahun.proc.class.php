<?php
/**
* @module periode_tahun
* @author Rendi Kasigi,
* @mail to rkasigi@gmail.com
* @copyright 2008&copy;Gamatechno
*/

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
		'module/periode_tahun/business/PeriodeTahun.class.php';

class ProcessPeriodeTahun 
{

    protected $msg;
    protected $data;
    protected $moduleName = 'periode_tahun';
    
    public  $PeriodeTahun;

    public function __construct()
    {
        # constructor
        if(isset($_POST['data'])) {
            if(is_object($_POST['data'])){  
                $this->data=$_POST['data']->AsArray(); 
            }else{
                $this->data=$_POST['data'];
            }
            
            $this->data['periodetahun']['tanggal_awal']     = $_POST['periodetahun_tanggal_awal_year'].'-'.
            $_POST['periodetahun_tanggal_awal_mon'].'-'.$_POST['periodetahun_tanggal_awal_day'];
            $this->data['periodetahun']['tanggal_akhir']    = $_POST['periodetahun_tanggal_akhir_year'].'-'.$_POST['periodetahun_tanggal_akhir_mon'].'-'.$_POST['periodetahun_tanggal_akhir_day'];
            $this->data['periodetahun']['tanggal']['awal']['day']       = (string) $_POST['periodetahun_tanggal_awal_day'];
            $this->data['periodetahun']['tanggal']['awal']['month']     = (string) $_POST['periodetahun_tanggal_awal_mon'];
            $this->data['periodetahun']['tanggal']['awal']['year']      = (string) $_POST['periodetahun_tanggal_awal_year'];

            $this->data['periodetahun']['tanggal']['akhir']['day']      = (string)$_POST['periodetahun_tanggal_akhir_day'];
            $this->data['periodetahun']['tanggal']['akhir']['month']    = (string)$_POST['periodetahun_tanggal_akhir_mon'];
            $this->data['periodetahun']['tanggal']['akhir']['year']     = (string) $_POST['periodetahun_tanggal_akhir_year'];
        }
        
        $this->PeriodeTahun = new PeriodeTahun();
    }

    public function Add () 
    {
        //$Obj = new PeriodeTahun();
        if(isset($_POST['btnsimpan'])){
            //kalo yang diklik tombol simpan
            //debug($this->data,1);
            if($this->validation('Penambahan')){
                //debug($this->data,1);
                $aktifrenstra=$this->PeriodeTahun->GetRenstraAktif();

                if(($aktifrenstra[0]['id_renstra'] != $this->data['periodetahun']['renstra']) && ($this->data['periodetahun']['is_aktif'] == 'Y')) {
                    $this->msg      = 'Penambahan data gagal dilakukan';
                    $urlRedirect    = $this->generateUrl('err');
                }else{
                    $add=$this->PeriodeTahun->DoAdd( 
                        $this->data['periodetahun']['nama'],
                        $this->data['periodetahun']['is_aktif'],
                        $this->data['periodetahun']['tanggal_awal'],
                        $this->data['periodetahun']['tanggal_akhir'],
                        $this->data['periodetahun']['renstra'],
                        $this->data['periodetahun']['is_open']
                    );
                    
                    if($add) {
                        $this->msg='Penambahan data berhasil dilakukan';
                        $urlRedirect=$this->generateUrl('msg');
                    } else {
                        $this->msg='Penambahan data gagal dilakukan';
                        $urlRedirect=$this->generateUrl('err');
                    }
                }
            } else { 
                //echo $this->msg;exit;
                $urlRedirect = $this->generateUrl('err');
            }

        } else {
        //kalo yang ditekan tombol balik
            $urlRedirect    = Dispatcher::Instance()->GetUrl(
                $this->moduleName, 
                'periodeTahun', 
                'view', 
                'html'
            ) ;
        }
        return $urlRedirect;
    }

    public function Delete () 
    {
         //if(isset($_GET['grp'])) {
        if(isset($_POST['idDelete'])) {
            $grp=Dispatcher::Instance()->Decrypt($_GET['grp']);
            $grp=$_POST['idDelete'];

            if(!$this->PeriodeTahun->isAktif($grp)) {
                $del=$this->PeriodeTahun->DoDelete($grp);
                if($del) {
                    $this->msg      = 'Penghapusan data berhasil dilakukan';
                    $urlRedirect    = $this->generateUrl('msg',true);
                } else {
                    $this->msg      = 'Penghapusan data gagal dilakukan';
                    $urlRedirect    = $this->generateUrl('err',true);
                }
            } else {
                $this->msg      = 'Periode Tahun dalam Kondisi Aktif tidak bisa dilakukan penghapusan data';
                $urlRedirect    = $this->generateUrl('err',true);
            }
        } else {
            $this->msg      = 'Penghapusan data gagal dilakukan';
            $urlRedirect    = $this->generateUrl('err',true);
        }
        return $urlRedirect;
    }

    public function Update() 
    {
        //$periodetahunObj = new PeriodeTahun();
        if(isset($_POST['btnsimpan'])){
            if($this->validation('Perubahan')) {  
                $this->data['periodetahun']['id']   = Dispatcher::Instance()->Decrypt(
                    $this->data['periodetahun']['id']
                );
                
                if(($this->data['periodetahun']['is_aktif'] != 'Y')){
                    $validaktif = !$this->PeriodeTahun->isAktif($this->data['periodetahun']['id']);
                }else{
                    $validaktif = true;
                }
                  
                if($validaktif) {
                    //nambahi kalo ternyata dia sedang aktif terus dideaktif melalui proses pengeditan yo gak boleh, karna nantinya jadi gk ada yang aktif
                    $aktifrenstra=$this->PeriodeTahun->GetRenstraAktif();

                    if($aktifrenstra[0]['id_renstra'] == $this->data['periodetahun']['renstra']) {
                        $update=$this->PeriodeTahun->DoUpdate(  
                            $this->data['periodetahun']['id'],
                            $this->data['periodetahun']['nama'],
                            $this->data['periodetahun']['is_aktif'],
                            $this->data['periodetahun']['tanggal_awal'],
                            $this->data['periodetahun']['tanggal_akhir'],  
                            $this->data['periodetahun']['renstra'], 
                            $this->data['periodetahun']['is_open']
                        );
                        if($update) {
                            $this->msg      = 'Perubahan data berhasil dilakukan'; 
                            $urlRedirect    = $this->generateUrl('msg');  
                        } else {
                            $this->msg      = 'Perubahan data gagal dilakukan silahkan ulangi lagi'; 
                            $urlRedirect    = $this->generateUrl('err');  
                        }
                    } else {
                        $this->msg  = 'Perubahan data gagal, renstra yang dipilih tidak aktif sehingga tidak dapat melakukan set aktif'; 
                        $urlRedirect = $this->generateUrl('err');
                    }
                } else {
                    $this->data['periodetahun']['is_aktif'] = 'Y';
                    $this->msg      = 'Data dalam kondisi aktif, tidak bisa dilakukan proses deaktif dari menu ini'; 
                    $urlRedirect    = $this->generateUrl('err'); 
                }
            } else { 
                $urlRedirect    = $this->generateUrl('err');
            }  
        } else {
        //kalo yang ditekan tombol balik
            $urlRedirect    = Dispatcher::Instance()->GetUrl(
                $this->moduleName, 
                'periodeTahun', 
                'view', 
                'html'
            ) ;
        }
        return $urlRedirect;
    }

    public function setAktif(&$type,&$msg) 
    {
        if(isset($_GET['grp'])) {
            //$periodetahunObj = new PeriodeTahun();
            $grp=Dispatcher::Instance()->Decrypt($_GET['grp']);
            $aktifrenstra=$this->PeriodeTahun->GetRenstraAktif();
            $idrenstra=$this->PeriodeTahun->GetRenstraById($grp);
            if($aktifrenstra[0]['id_renstra'] == $idrenstra[0]['renstra_id']) {
                $set=$this->PeriodeTahun->DoSetAktif(
                    $grp, 
                    $aktifrenstra[0]['id_renstra']
                );
                if($set) {
                    $msg    = "Data berhasil diset aktif";
                    $type   = 'msg';
                    $rtn    = true;
                } else {
                    $msg    = "Maaf, gagal melakukan set aktif";
                    $type   = 'err';
                    $rtn    = false;
                }
            } else {
                $msg    = "Maaf, gagal melakukan set aktif, rencana strategis untuk periode ini tidak aktif";
                $type   = 'err';
                $rtn    = false;
            }

        } else { //kalo gak diset yaudah balikin lagi ajah ke halaman utama
            $msg    = "Maaf, Data yang akan diset tidak ditemukan";
            $type   = 'err';
            $rtn    = false;
        }
          
        return $rtn;
    }

    public function validation($action) 
    {
        $this->msg='';
        if(!isset($_POST['data'])) { 
            //kalo gak ada data yang di POST apa yang mau di  validasi
            $this->msg=$action.' data gagal dilakukan ';
            return false;
        }
        if(isset($this->$data['periodetahun']['id']) && (trim($this->data['periodetahun']['id']) != '')){
            $this->data['periodetahun']['id']   = Dispatcher::Instance()->Decrypt(
                trim($this->data['periodetahun']['id'])
            );
        }
        if(!isset($this->data['periodetahun']['nama']) || trim($this->data['periodetahun']['nama']) == ''){
            $this->msg.='Nama Periode Tahun Tidak Boleh Kosong <br />';
        }
        if(!checkdate($this->data['periodetahun']['tanggal']['awal']['month'],
            $this->data['periodetahun']['tanggal']['awal']['day'],
            $this->data['periodetahun']['tanggal']['awal']['year']
          )
        ){
            $this->msg.=' Tanggal awal tidak valid <br />';
        }
         
        if(!checkdate($this->data['periodetahun']['tanggal']['akhir']['month'],
            $this->data['periodetahun']['tanggal']['akhir']['day'],
            $this->data['periodetahun']['tanggal']['akhir']['year']
          )
        ){
            $this->msg.=' Tanggal akhir tidak valid <br />';
        }
        
        $rens_tgl_awal  = $this->data['periodetahun']['renstra_tgl_awal'];
        $rens_bln_awal  = $this->data['periodetahun']['renstra_bln_awal'];
        $rens_thn_awal  = $this->data['periodetahun']['renstra_thn_awal'];
        
        $rens_tgl_akhir  = $this->data['periodetahun']['renstra_tgl_akhir'];
        $rens_bln_akhir  = $this->data['periodetahun']['renstra_bln_akhir'];
        $rens_thn_akhir  = $this->data['periodetahun']['renstra_thn_akhir'];
        
        $periode_awal   = date_format(date_create($this->data['periodetahun']['tanggal']['awal']['year'].'-'.$this->data['periodetahun']['tanggal']['awal']['month'].'-'.$this->data['periodetahun']['tanggal']['awal']['day']), 'Y-m-d');
        $periode_akhir  = date_format(date_create($this->data['periodetahun']['tanggal']['akhir']['year'].'-'.$this->data['periodetahun']['tanggal']['akhir']['month'].'-'.$this->data['periodetahun']['tanggal']['akhir']['day']), 'Y-m-d');
        
        $renstra_tgl_awal   = date_format(date_create($rens_thn_awal.'-'.$rens_bln_awal.'-'.$rens_tgl_awal), 'Y-m-d');
        $renstra_tgl_akhir  = date_format(date_create($rens_thn_akhir.'-'.$rens_bln_akhir.'-'.$rens_tgl_akhir), 'Y-m-d');
        
        if($periode_awal < $renstra_tgl_awal OR $periode_akhir > $renstra_tgl_akhir){
            $this->msg  .= 'Tahun periode tidak berada dalam range periode renstra yang dipilih';
        }
        
        if($periode_awal > $periode_akhir){
            $this->msg  .= 'Range periode yang Anda pilih tidak sesuai';
        }
        
        //debug($this->data);

         /*
        if($this->data['periodetahun']['tanggal_buka'] >= $this->data['periodetahun']['tanggal_tutup'])
         $this->msg.='Periode tahun awal tidak boleh lebih besar dari periode tahun akhir <br />'; 
        */
        // echo $this->msg;
        if($this->msg==''){
            return true;
        }else{ 
            return false;
        }
    }

    public function generateUrl($type,$isHome=false)
    {
        //parameter isHome ditujukan bahwa url diredirect ke home module apapun bentuk pesannya
        if(isset($_GET['grp'])){
            $grp='&grp='.Dispatcher::Instance()->Encrypt($this->data['periodetahun']['id']);
        }else{
            $grp='';
        }

        if($type=='msg' || $isHome ){
            $submodule='periodeTahun';
        }else{
            $submodule='inputPeriodeTahun';
        }

        Messenger::Instance()->Send(
            $this->moduleName, 
            $submodule, 
            'view', 
            'html', 
            array(
                $this->data,
                $type,
                $this->msg
            ),
            Messenger::NextRequest
        );
        $urlRedirect = Dispatcher::Instance()->GetUrl($this->moduleName, $submodule, 'view', 'html').$grp;
        return $urlRedirect;
    }

    public function parsingUrl($file) 
    {
        $msg = Messenger::Instance()->Receive($file);

        if(!empty($msg)) {
            $tmp['data']            = $msg[0][0];
            $tmp['msg']['action']   = $msg[0][1];
            $tmp['msg']['message']  = $msg[0][2];
            return $tmp;
        } else {
            return false;
        } 
    }

    public function GetTanggalIndonesia($tanggal) 
    {
        $blnarr     = array();
        $blnarr[1]  = "Januari";
        $blnarr[2]  = "Februari";
        $blnarr[3]  = "Maret";
        $blnarr[4]  = "April";
        $blnarr[5]  = "Mei";
        $blnarr[6]  = "Juni";
        $blnarr[7]  = "Juli";
        $blnarr[9]  = "September";
        $blnarr[8]  = "Agustus";
        $blnarr[10] = "Oktober";
        $blnarr[11] = "November";
        $blnarr[12] = "Desember";

        $tanggal    = explode("-",$tanggal);
        return $tanggal[2]." ".$blnarr[intval($tanggal[1])]." ".$tanggal[0];
    }
}
?>
