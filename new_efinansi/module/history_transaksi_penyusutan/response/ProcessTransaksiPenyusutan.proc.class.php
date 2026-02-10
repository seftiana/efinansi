<?php
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/transaksi_penyusutan/business/AppTransaksiPenyusutan.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/transaksi_penyusutan/business/AppTransaksiPenyusutanAsper.class.php';

class ProcessTransaksiPenyusutan
{
   var $_POST;
   var $Obj;
   var $pageView;
   var $pageInput;

   //css hanya dipake di view
   var $cssDone = "notebox-done";
   var $cssFail = "notebox-warning";
   var $cssAlert = "notebox-alert";
   var $return;
   var $decId;
   var $encId;
   function __construct()
   {
      $this->Obj = new AppTransaksiPenyusutan();
      $this->ObjAsper = new AppTransaksiPenyusutanAsper();
      $this->_POST = $_POST->AsArray();
      $this->decId = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
      $this->encId = Dispatcher::Instance()->Encrypt($this->decId);
      $this->pageView = Dispatcher::Instance()->GetUrl('transaksi_penyusutan', 'TransaksiPenyusutan', 'view', 'html');
      $this->pageViewJson = Dispatcher::Instance()->GetUrl('transaksi_penyusutan', 'TransaksiPenyusutan', 'view', 'html', true);
      $this->pageDetil = Dispatcher::Instance()->GetUrl('transaksi_penyusutan', 'DetilTransaksiPenyusutan', 'view', 'html');
   }
   function Check()
   {

      if (isset($_POST['btnsimpan']))
      {

         //tidak perlu check no_kkb karena itu auto generate dari sistem nomor nya

         return true;

         if ($this->decId != '')
         {
            $cek = $this->Obj->CekTransaksiUpdate($this->_POST['no_kkb'], $this->decId);
         }
         else
         {
            $cek = $this->Obj->CekTransaksi($this->_POST['no_kkb']);
         }

         if ($cek === false)
         {

            return "exists";
         }

         return true;
      }

      return false;
   }
   function AddPenyusutan($kib_id)
   {
#      $this->ObjAsper->SetDebugOn();

      #$arr_dt_penyusutan = $this->ObjAsper->GetDetailDataPenyusutan($kib_id);
      $periode_penyusutan = $this->_POST['periode_penyusutan_year'] . "-" . $this->_POST['periode_penyusutan_mon'] . "-01";

      $insert_penyusutan_mst = $this->ObjAsper->DoAddPenyusutanMst($periode_penyusutan, $this->_POST['ba_penyusutan'], $this->_POST['catatan_transaksi']);
      $id = $this->ObjAsper->LastInsertId();

      if ($this->ObjAsper->AffectedRows())
      {
         $penyusutan = $this->ObjAsper->DoAddPenyusutanDetil($kib_id);
      }

      if ($this->ObjAsper->AffectedRows()) $arr_dt_penyusutan = $this->ObjAsper->GetDataPenyusutan($id);

      return $arr_dt_penyusutan;
   }
   function Add($jsonReturn = false)
   {
      $cek = $this->Check();

      if ($cek === true)
      {
         $arrData['kib_id'] = $this->_POST['kib_id'];
         $arrData['transUnitkerjaId'] = $this->Obj->GetUserUnitKerja();
         $arrData['transReferensi'] = $this->Obj->GetSqlNomorPenyusutan();
         $arrData['transUserId'] = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
         $arrData['transDueDate'] = $this->_POST['periode_penyusutan_year'] . "-" . $this->_POST['periode_penyusutan_mon'] . "-01";
         $arrData['transTanggal'] = $this->_POST['tanggal_transaksi_year'] . "-" . $this->_POST['tanggal_transaksi_mon'] . "-" . $this->_POST['tanggal_transaksi_day'];
         $arrData['transCatatan'] = $this->_POST['catatan_transaksi'];
         $arrData['transPenanggungJawabNama'] = $this->_POST['penanggung_jawab'];
         $arrData['transNilai'] = $this->_POST['nominal_penyusutan_hidden'];

         if ($this->_POST['skenario'] == "auto")
         {
            $arrData['transIsJurnal'] = "Y";
         }
         else
         {
            $arrData['transIsJurnal'] = "T";
         }

         //insert penyusutan ke db asper
         $kib = $this->ObjAsper->CekKib($arrData['kib_id']);
         $this->Obj->StartTrans();
         $this->ObjAsper->StartTrans();
         $penyusutan = $this->AddPenyusutan($arrData['kib_id']);

         if (!empty($penyusutan))
         {
            $id_transaksi = $this->Obj->DoAddTransaksi($arrData);

            if (!empty($id_transaksi)) $transaksi = true;

            if ($id_transaksi != "")
            {

               //insert ke transaksi_detail_penyusutan
               $trans_penyusutan = true;

               for ($p = 0;$p < sizeof($penyusutan);$p++)
               {

                  if ($trans_penyusutan) $trans_penyusutan = $this->Obj->DoAddTransaksiDetilPenyusutan($id_transaksi, $penyusutan[$p]['id_inv_barang'], $arrData['kib_id'], $penyusutan[$p]['brg_nama'], $arrData['transTanggal'], $arrData['transDueDate'], $penyusutan[$p]['penyusutanDetNilaiPenyusutan']);
               }

               //insert ke table file, tidak wajib
               $files = $_FILES['file_attach'];

               if (!empty($files))
               {

                  for ($i = 0;$i < sizeof($files['name']);$i++)
                  {

                     if (!$files['name'][$i]) continue;
                     $ext = end(explode(".", $files['name'][$i]));
                     $namafile[] = date("Y-m-d_H-i-s_") . substr(md5(microtime()) , 0, 3) . "." . $ext;
                  }

                  if (!empty($namafile))
                  {
                     $transaksi_detil_gaji = $this->Obj->DoAddTransaksiFile($id_transaksi, $namafile, "file/transaksi/");

                     for ($i = 0;$i < sizeof($namafile);$i++)
                     {

                        if (!$files['name'][$i]) continue;
                        @move_uploaded_file($files['tmp_name'][$i], "file/transaksi/" . $namafile[$i]);
                     }
                  }
               }

               //skenario

               if ($this->_POST['skenario'] == "auto")
               {

                  //KETERANGAN DIISI KOSONG
                  $id_pembukuan = $this->Obj->DoAddPembukuan($id_transaksi, $arrData['transUserId']);
                  $detil_pembukuan = $this->Obj->DoAddPembukuanDetil($id_pembukuan, $this->_POST['nominal_penyusutan_hidden'], $this->_POST['skenario_list']);
               }
               Messenger::Instance()->Send('transaksi_penyusutan', 'TransaksiPenyusutan', 'view', 'html', array(
                  $this->_POST,
                  'Penambahan Data Transaksi Penyusutan Berhasil Dilakukan Dengan Nomor Transaksi ' . $arrData['transReferensi'],
                  $this->cssDone
               ) , Messenger::NextRequest);
            } //tutup dulu


         }
         $addData = $transaksi && $trans_penyusutan;
         $this->Obj->EndTrans($addData);
         $this->ObjAsper->EndTrans($addData);
      }
      elseif ($cek === "exists")
      {

         //echo "666";
         Messenger::Instance()->Send('transaksi_penyusutan', 'TransaksiPenyusutan', 'view', 'html', array(
            $this->_POST,
            'Transaksi Dengan Nomor <b>' . ($arrData['transReferensi']) . '</b> Sudah Dibuat',
            $this->cssFail
         ) , Messenger::NextRequest);
      }
      else
      {

         //gagal masukin data
         Messenger::Instance()->Send('transaksi_penyusutan', 'TransaksiPenyusutan', 'view', 'html', array(
            $this->_POST,
            'Gagal Menambah Data',
            $this->cssFail
         ) , Messenger::NextRequest);
      }

      //exit();

      if ($jsonReturn == true)
      return $this->pageViewJson;
      else
      return $this->pageView;
   }
   function Update()
   {
      $cek = $this->Check();
      $cek = true;

      #print_r($this->_POST); exit;

      if ($cek === true)
      {
         $arrData['transUnitkerjaId'] = $this->Obj->GetUserUnitKerja();
         $arrData['transReferensi'] = $this->_POST['no_kkb'];
         $arrData['transUserId'] = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
         $arrData['transTanggal'] = $this->_POST['tanggal_transaksi_year'] . "-" . $this->_POST['tanggal_transaksi_mon'] . "-" . $this->_POST['tanggal_transaksi_day'];
         $arrData['periode_penyusutan'] = $this->_POST['periode_penyusutan_year'] . "-" . $this->_POST['periode_penyusutan_mon'] . "-01";
         $arrData['transCatatan'] = $this->_POST['catatan_transaksi'];
         $arrData['transNilai'] = $this->_POST['nominal_penyusutan_hidden'];
         $arrData['transPenanggungJawabNama'] = $this->_POST['penanggung_jawab'];

         if ($this->_POST['skenario'] == "auto")
         {
            $arrData['transIsJurnal'] = "Y";
         }
         else
         {
            $arrData['transIsJurnal'] = "T";
         }
         $arrData['transId'] = $this->_POST['id_trans'];

         //echo "<pre>";
         //print_r($this->_POST);

         //print_r($_FILES);

         //echo "</pre>";

         //exit();

         //echo "<pre>";

         $upd_transaksi = $this->Obj->DoUpdateTransaksi($arrData);

         //echo $arrData['transId'];

         if ($upd_transaksi == true)
         {

            //jika jenis transaksi == Anggaran, insert MAK ke table transaksi_detail_anggaran
            //relasi dengan detil anggaran adalah 1-1,

            /*kasusnya :



            1. bukan anggaran ~ bukan anggaran //tidak usah dibikin
            2. bukan anggaran ~ anggaran
            3. anggaran ~ bukan anggaran
            4. anggaran ~ anggaran

            */

            //             if(!$this->_POST['mak_lama'] && $this->_POST['mak']) {
            //                //kasus 2, insert

            //                $this->Obj->DoAddTransaksiDetilAnggaran($arrData['transId'], $this->_POST['mak']);

            //             } elseif($this->_POST['mak_lama'] && !$this->_POST['mak']) {

            //                //kasus 3, hapus

            //                $this->Obj->DoDeleteTransaksiDetilAnggaran($this->_POST['mak_lama']);

            //             } elseif($this->_POST['mak_lama'] && $this->_POST['mak'] && $this->_POST['mak_lama'] != $this->_POST['mak']) {

            //                //kasus 4, update

            //                $this->Obj->DoUpdateTransaksiDetilAnggaran($this->_POST['mak_lama_id'], $this->_POST['mak']);

            //             }

            /*



            kasusnya :
            1. gambar dihapus
            2. gambar ditambah
            */

            //kasus 1

            if (!empty($this->_POST['file_attach_delete']))
            {
               $this->Obj->DoDeleteTransaksiFile($this->_POST['file_attach_delete']);

               for ($i = 0;$i < sizeof($this->_POST['file_attach_delete']);$i++)
               {
                  @unlink("file/transaksi/" . $this->_POST['file_attach_delete_nama'][$i]);
               }
            }

            //kasus 2
            //insert ke table file, tidak wajib

            $files = $_FILES['file_attach'];

            if (!empty($files))
            {

               for ($i = 0;$i < sizeof($files['name']);$i++)
               {

                  if (!$files['name'][$i]) continue;
                  $ext = end(explode(".", $files['name'][$i]));
                  $namafile[] = date("Y-m-d_H-i-s_") . substr(md5(microtime()) , 0, 3) . "." . $ext;
               }

               if (!empty($namafile))
               {
                  $transaksi_detil_gaji = $this->Obj->DoAddTransaksiFile($arrData['transId'], $namafile, "file/transaksi/");

                  for ($i = 0;$i < sizeof($namafile);$i++)
                  {

                     if (!$files['name'][$i]) continue;
                     @move_uploaded_file($files['tmp_name'][$i], "file/transaksi/" . $namafile[$i]);
                  }
               }
            }

            //skenario

            if ($this->_POST['skenario'] == "auto")
            {

               //KETERANGAN DIISI KOSONG
               $id_pembukuan = $this->Obj->DoAddPembukuan($arrData['transId'], $arrData['transUserId']);
               $detil_pembukuan = $this->Obj->DoAddPembukuanDetil($id_pembukuan, $this->_POST['nominal'], $this->_POST['skenario_list']);
            }
            Messenger::Instance()->Send('transaksi_penyusutan', 'TransaksiPenyusutan', 'view', 'html', array(
               $this->_POST,
               'Perubahan Data Transaksi Penyusutan Berhasil Dilakukan',
               $this->cssDone
            ) , Messenger::NextRequest);
         }

         //}

      }
      elseif ($cek == "exists")
      {

         //echo "666";
         //echo "exist";

         Messenger::Instance()->Send('transaksi_penyusutan', 'TransaksiPenyusutan', 'view', 'html', array(
            $this->_POST,
            'Transaksi Dengan Nomor <b>' . ($this->_POST['no_kkb']) . '</b> Sudah Dibuat',
            $this->cssFail
         ) , Messenger::NextRequest);

         return $this->pageView . '&dataId=' . $this->encId;
      }
      else
      {

         //gagal masukin data
         //echo "gagal";

         Messenger::Instance()->Send('transaksi_penyusutan', 'TransaksiPenyusutan', 'view', 'html', array(
            $this->_POST,
            'Gagal Merubah Data Transaksi Penyusutan',
            $this->cssFail
         ) , Messenger::NextRequest);

         return $this->pageView . '&dataId=' . $this->encId;
      }

      //echo "adflj";

      return $this->pageView;
   }
   function Delete()
   {
      $arrId = $this->_POST['idDelete'];

      #print_r($this->_POST); exit;
      #$deleteArrData = $this->Obj->DoDeleteDataByArrayId($arrId);

      $deleteArrData = $this->Obj->DoDeleteDataById($arrId);

      if ($deleteArrData === true)
      {
         Messenger::Instance()->Send('transaksi_penyusutan', 'DetilTransaksiPenyusutan', 'view', 'html', array(
            $this->_POST,
            'Penghapusan Data Berhasil Dilakukan',
            $this->cssDone
         ) , Messenger::NextRequest);
      }
      else
      {

         //jika masuk disini, berarti PASTI ada salah satu atau lebih data yang gagal dihapus
         /*for($i=0;$i<sizeof($arrId);$i++) {



         $deleteData = false;
         $deleteData = $this->Obj->DoDeleteDataById($arrId[$i]);
         if($deleteData === true) $sukses += 1;
         else $gagal += 1;
         }*/
         Messenger::Instance()->Send('transaksi_penyusutan', 'DetilTransaksiPenyusutan', 'view', 'html', array(
            $this->_POST,
            $gagal . ' Data Tidak Dapat Dihapus.',
            $this->cssFail
         ) , Messenger::NextRequest);
      }

      return $this->pageDetil;
   }
}
?>