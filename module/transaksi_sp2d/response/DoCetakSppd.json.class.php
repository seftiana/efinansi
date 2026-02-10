<?php
#doc
# package:     DoCetakSppd
# scope:       PUBLIC
# @created     ucil.619
# @Author      Eko Susilo
# @Created     2012-09-05
# @Modified    2012-09-05
# @Analysts    Nanang Ruswianto
# @copyright   Copyright (c) 2012 Gamatechno
#/doc
require_once GTFWConfiguration::GetValue('application','docroot').
'module/transaksi_sp2d/business/Sppd.class.php';

class DoCetakSppd extends JsonResponse
{
   public $obj;
   public $url_return;
   public $css_warning  = 'notebox-warning';
   public $css_done     = 'notebox-done';
   
   function __construct()
   {
      $this->obj           = new Sppd();
      $this->url_return    = Dispatcher::Instance()->GetUrl(
         'transaksi_sp2d',
         'DaftarTransaksi',
         'view',
         'html'
      );
      
   }
   
   function ProcessRequest(){
      $_POST      = $_POST->AsArray();
      if(isset($_POST['btnbalik'])){
         return array( 
            'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$this->url_return.'&ascomponent=1")'
         );
         
      }else{
         $data['ta_id']       = $_POST['ta_id'];
         $data['trans_id']    = $_POST['trans_id'];
         $data['spm_id']      = $_POST['spm_id'];
         $data['data_id']     = $_POST['data_id'];
         $data['sppdNominal'] = $_POST['sppdNominal'];
         $data['sppd_kpd']    = $_POST['kepada'];
         $data['sppd_norek']  = $_POST['sppd_norek'];
         $data['sppd_npwp']   = $_POST['npwp'];
         $data['sppd_bank']   = $_POST['sppd_bank'];
         $data['keterangan']  = $_POST['keterangan'];
         $sppd_tanggal        = empty($_POST['sppd_tanggal']) ? date('Y-m-d') : $_POST['sppd_tanggal']; 
         $data['sppd_tgl']    = date_format(date_create($sppd_tanggal), 'Y-m-d');
         
         $satkerNomor         = GTFWConfiguration::GetValue('organization','satker_no');
         $nssNomor            = GTFWConfiguration::GetValue('organization','nss');
         $unitKode            = $_POST['unit_kode'];
         $data['sppd_nomor']  = $_POST['sppd_nomor'];
         //$data['sppd_nomor']  = $this->obj->GenerateNomor($nssNomor, $satkerNomor , $unitKode);
         
         // check jika sudah mencetak halaman maka proses akan mengubah data yang sudah ada
         if(!empty($data['data_id']))
         {
            $simpan_sppd         = $this->obj->DoUpdateSp2d($data);
         }
         else
         {
            $simpan_sppd         = $this->obj->DoInsertSp2d($data);
         }
         
         if($simpan_sppd === true)
         {
            // jika proses pembuatan sppd berhasil, 
            // keluar pesan sukses dan muncul halaman untuk cetak
            $last_id          = empty($data['data_id']) ? $this->obj->GetLastId() : $data['data_id'];
            $pesan            = 'Proses pembuatan Surat Perintah Pencairan Dana berhasil dilaksanakan';
            $style            = $this->css_done;
            
            // generate url cetak sp2d 
            $url_cetak           = Dispatcher::Instance()->GetUrl(
               'transaksi_sp2d',
               'CetakSp2d',
               'view',
               'html'
            ).'&data_id='.$last_id.'&transId='.$data['trans_id'].'&spmId='.$data['spm_id'];
            
            return array( 
               'exec' => '$("input[name=data_id]").val("'.$last_id.'"); $("input[name=sppd_nomor]").val("'.$data['sppd_nomor'].'"); $("label#sppd-nomor").html("'.$data['sppd_nomor'].'"); $("input").removeAttr("disabled"); $("input[type=submit]").removeAttr("disabled"); if($("#warning").is(":hidden")){ $("#warning").removeClass(); $("#warning").show().addClass("'.$style.'").html("'.$pesan.'"); } else { $("#warning").removeClass(); $("#warning").fadeOut().fadeIn().addClass("'.$style.'").html("'.$pesan.'"); } var width  = 940; var height = 600; var left   = (screen.width  - width)/2; var top    = (screen.height - height)/2; var params = "width="+width+", height="+height; params += ", top="+top+", left="+left; params += ", directories=no"; params += ", location=no"; params += ", menubar=no"; params += ", resizable=no"; params += ", scrollbars=yes"; params += ", status=no"; params += ", toolbar=no"; newwin=window.open("'.$url_cetak.'","cetak SP2D", params); if (window.focus) { newwin.focus(); }'
            );
         }
         else
         {
            // jika gagal
            $pesan            = 'Proses pembuatan Surat Perintah Pencairan Dana gagal dilaksanakan'.$simpan_sppd;
            $style            = $this->css_warning;
            
            return array( 
               'exec' => '$("input[name=data_id]").val("'.$last_id.'"); $("input[name=sppd_nomor]").val("'.$data['sppd_nomor'].'"); $("label#sppd-nomor").html("'.$data['sppd_nomor'].'"); $("input").removeAttr("disabled"); $("input[type=submit]").removeAttr("disabled"); if($("#warning").is(":hidden")){ $("#warning").removeClass(); $("#warning").show().addClass("'.$style.'").html("'.$pesan.'"); } else { $("#warning").removeClass(); $("#warning").fadeOut().fadeIn().addClass("'.$style.'").html("'.$pesan.'"); } '
            );
         }
         
      }
   }
}
?>
