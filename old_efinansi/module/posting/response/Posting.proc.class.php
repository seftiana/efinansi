<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/posting/business/AppPosting.class.php';

class ProcessPosting
{
   function InsertBukuBesar() {
      $Obj           = new AppPosting();
      $urlRedirect   = Dispatcher::Instance()->GetUrl(
         'posting',
         'Posting',
         'view',
         'html'
      );

      if(isset($_POST['btnposting'])) {
         $_POST   = $_POST->AsArray();
         $tgl     = $_POST['tanggal_posting_year'].'-'.$_POST['tanggal_posting_mon'].'-'.$_POST['tanggal_posting_day'];

         $data_pembukuan      = $Obj->GetDataPembukuan($tgl);
         if(!empty($data_pembukuan)) {
            #print_r($data_pembukuan); exit;
            for($i=0; $i<count($data_pembukuan); $i++) {
               if(strtoupper($data_pembukuan[$i]['status_pembukuan']) == 'D') {
                  $debet      = $data_pembukuan[$i]['nilai'];
                  $kredit     = 0;
                  $kredit_lr  = -$data_pembukuan[$i]['nilai'];
               }elseif(strtoupper($data_pembukuan[$i]['status_pembukuan']) == 'K') {
                  $debet      = 0;
                  $kredit     = $data_pembukuan[$i]['nilai'];
                  $kredit_lr  = $data_pembukuan[$i]['nilai'];
               }

               $cek_akun_from_bb       = $Obj->CekAkunBukuBesar($data_pembukuan[$i]['coa_id']);

               if(!empty($cek_akun_from_bb['bb_id'])) {
                  if($data_pembukuan[$i]['coa_status_debet'] == 1){
                     $saldo      = $debet - $kredit;
                  }elseif($data_pembukuan[$i]['coa_status_debet'] == 0){
                     $saldo      = $kredit - $debet;
                  }

                  $saldo_awal    = $cek_akun_from_bb['saldo_akhir'];
                  $saldo_akhir   = $saldo_awal + $saldo;

                  //update buku besar, karena akun coa nya sudah ada
                  $update_bb     = $Obj->DoUpdateBukuBesar($tgl, $data_pembukuan[$i]['coa_id'], $saldo_awal, $debet, $kredit, $saldo, $saldo_akhir, $cek_akun_from_bb['bb_id']);
                  //insert buku besar hystory
                  $insert_bb_his    = $Obj->DoInsertBukuBesarHis($data_pembukuan[$i]['pembukuan_ref_id'], $data_pembukuan[$i]['pembukuan_detail_id'], $tgl, $data_pembukuan[$i]['coa_id'], $saldo_awal, $debet, $kredit, $saldo, $saldo_akhir);
               } else {
                  //insert bb disini
                  if($data_pembukuan[$i]['coa_status_debet'] == 1){
                     $saldo = $debet - $kredit;
                  }elseif($data_pembukuan[$i]['coa_status_debet'] == 0){
                     $saldo = $kredit - $debet;
                  }

                  $saldo_awal    = 0;
                  $saldo_akhir   = $saldo_awal + $saldo;
                  $insert_bb     = $Obj->DoInsertBukuBesar($tgl, $data_pembukuan[$i]['coa_id'], $saldo_awal, $debet, $kredit, $saldo, $saldo_akhir);
                  //insert buku besar hystory
                  $insert_bb_his = $Obj->DoInsertBukuBesarHis($data_pembukuan[$i]['pembukuan_ref_id'], $data_pembukuan[$i]['pembukuan_detail_id'], $tgl, $data_pembukuan[$i]['coa_id'], $saldo_awal, $debet, $kredit, $saldo, $saldo_akhir);
               }
               //update status is posting di pembukuan_referensi
               $Obj->UpdateStatusPostingPembukuanRef($data_pembukuan[$i]['pembukuan_ref_id']);

               //cek coa laba ditahan
               $list_coa_laba_rugi = $Obj->_GetCoaLabaRugi();
               for($l=0; $l<sizeof($list_coa_laba_rugi); $l++) {
                  $arr_coa[] = $list_coa_laba_rugi[$l]['coaKelompokId'];
               }
               if(in_array($data_pembukuan[$i]['coa_kelompok'], $arr_coa)) {
                  #$tes[] = $data_pembukuan[$i]['coa_id'];
                  $cek_akun_laba_rugi_from_bb = $Obj->CekAkunLabaRugiBukuBesar();
                  if(!empty($cek_akun_laba_rugi_from_bb['bb_id'])) {
                     /*$saldo_laba_rugi = $Obj->CekSaldoLabaRugi();
                     if(empty($saldo_laba_rugi))
                        $kredit_lr = $data_pembukuan[$i]['nilai'];
                     elseif($saldo_laba_rugi['labarugi'] < 0)
                        $kredit_lr = -$data_pembukuan[$i]['nilai'];
                     elseif($saldo_laba_rugi['labarugi'] >= 0)
                        $kredit_lr = -$data_pembukuan[$i]['nilai'];*/

                     $saldo_awal_lr    = $cek_akun_laba_rugi_from_bb['saldo_akhir'];
                     $saldo_akhir_lr   = $saldo_awal_lr + ($kredit_lr-0);
                     //proses insert bukubesar untuk coa laba rugi
                     $update_labarugi_bb     = $Obj->DoUpdateLabaRugiBukuBesar($tgl, $saldo_awal_lr, $debet, $kredit, $kredit-$debet, $saldo_akhir_lr, $cek_akun_laba_rugi_from_bb['bb_id']);
                     $insert_labarugi_bb_his = $Obj->DoInsertLabaRugiBukuBesarHis($data_pembukuan[$i]['pembukuan_ref_id'], $data_pembukuan[$i]['pembukuan_detail_id'], $tgl, $saldo_awal_lr, $debet, $kredit, $kredit-$debet, $saldo_akhir_lr);
                  }else{
                     /*$saldo_laba_rugi = $Obj->CekSaldoLabaRugi();
                     if(empty($saldo_laba_rugi))
                        $kredit_lr = $data_pembukuan[$i]['nilai'];
                     elseif($saldo_laba_rugi['labarugi'] < 0)
                        $kredit_lr = -$data_pembukuan[$i]['nilai'];
                     else
                        $kredit_lr = $data_pembukuan[$i]['nilai'];*/

                     $saldo_awal_lr       = 0;
                     $saldo_akhir_lr      = $saldo_awal_lr + ($kredit_lr-0);
                     $insert_labarugi_bb  = $Obj->DoInsertLabaRugiBukuBesar($tgl, $saldo_awal_lr, $debet, $kredit, $kredit-$debet, $saldo_akhir_lr);
                     //insert buku besar hystory
                     $insert_labarugi_bb_his    = $Obj->DoInsertLabaRugiBukuBesarHis(
                        $data_pembukuan[$i]['pembukuan_ref_id'],
                        $data_pembukuan[$i]['pembukuan_detail_id'],
                        $tgl,
                        $saldo_awal_lr,
                        $debet,
                        $kredit,
                        $kredit-$debet,
                        $saldo_akhir_lr
                     );
                  }


               }
            }
            #print_r($tes); exit;
            $urlRedirect = Dispatcher::Instance()->GetUrl('posting', 'Posting', 'view', 'html') . '&err=' . Dispatcher::Instance()->Encrypt('posting');
         } else {
            #echo 'sini kosong'; exit;
            $urlRedirect = Dispatcher::Instance()->GetUrl('posting', 'Posting', 'view', 'html') . '&err=' . Dispatcher::Instance()->Encrypt('nodataposting');
         }
      } else {
         $urlRedirect = Dispatcher::Instance()->GetUrl('posting', 'Posting', 'view', 'html'). '&err=' . Dispatcher::Instance()->Encrypt('noposting');
      }
      return $urlRedirect;
   }
}
?>
