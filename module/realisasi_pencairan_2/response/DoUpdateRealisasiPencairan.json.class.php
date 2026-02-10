<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/realisasi_pencairan_2/response/ProcessRealisasiPencairan.proc.class.php';

class DoUpdateRealisasiPencairan extends JsonResponse
{
   function ProcessRequest()
   {
      $Obj              = new ProcessRealisasiPencairan();
      $urlRedirect      = $Obj->Update();

      return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');

      /*$url_return       = Dispatcher::Instance()->GetUrl(
         'realisasi_pencairan_2',
         'realisasiPencairan',
         'view',
         'html'
      );

      if(GTFWConfiguration::GetValue( 'application', 'integrated') === true){
         $_POST      = $_POST->AsArray();
         if(isset($_POST['btnBatal'])){
            return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$url_return.'&ascomponent=1")');
         }else{
            $mak        = array();
            $statusPagu = array();
            $no         = 0;
            $data_post  = $_POST['data'];
            $unit_id    = $data_post['unit_id'];
            $th_anggar  = $data_post['ta_id'];

            foreach($_POST['KOMP'] as $k => $v):
               $mak[$no]   = $v['makId'];
               $no++;
            endforeach;
            $dataPagu   = $Obj->RealisasiPencairan->CheckPagu($mak);

            for($i = 0; $i < count($dataPagu); $i++):
               $statusPagu[$i]      = $dataPagu[$i]['paguAnggUnitBintang'];
            endfor;

            if(empty($mak) || in_array('null', $mak)):
               $err_msg    = 'Tidak ada mak dalam komponen yang di sertakan';
            else:
               if(in_array('T', $statusPagu)):
                  $err_msg    = 'Ada beberapa MAK yang belum di setujui dalam Pagu Anggaran di dalam komponen';
               else:
                  $urlRedirect = $Obj->Update();

                  return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');

               endif;
            endif;

            return array(
               'exec' => "if($('#notebox-warning').is(':visible')){ $('#notebox-warning').fadeOut('fast'); if('#content-realisasi').is(':hidden') { $('#content-realisasi').fadeIn('normal').html('".$err_msg."').removeClass().addClass('notebox-warning'); }else{ $('#content-realisasi').html('".$err_msg."').removeClass().addClass('notebox-warning'); } }else{ if('#content-realisasi').is(':hidden') { $('#content-realisasi').fadeIn('normal').html('".$err_msg."').removeClass().addClass('notebox-warning'); }else{ $('#content-realisasi').html('".$err_msg."').removeClass().addClass('notebox-warning'); } }"
            );
         }
      }else{
         $urlRedirect = $Obj->Update();

         return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');
      }*/
   }
}
?>