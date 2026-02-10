<?php
$sql['get_count_kegiatan_unit_kerja'] = "
   SELECT FOUND_ROWS() as total
";

$sql['get_kegiatan_unit_kerja'] = "
   SELECT
      SQL_CALC_FOUND_ROWS 
      CONCAT_WS('|', kegdetId, pengrealId) as id,
      pengrealId as peng_real_id,
      kegrefNomor as kode,
      kegrefNama as nama,
      pengrealNominalAprove AS nominal_aprove,
      kegUnitkerjaId AS unit_kerja_id,
      unitkerjaKode AS unit_kerja_kode,
      unitkerjaNama AS unit_kerja_nama
   FROM
      kegiatan_detail
      JOIN kegiatan_ref ON (kegrefId = kegdetKegrefId)
      JOIN kegiatan ON (kegId = kegdetKegId)
      JOIN pengajuan_realisasi ON (pengrealKegdetId = kegdetId)
      JOIN unit_kerja_ref ON (unitkerjaId = kegUnitkerjaId)
   WHERE
      pengrealIsApprove='Ya'
      AND kegdetIsAprove = 'Ya'
      AND pengrealIsTransaksi = '0'
      AND kegrefNama LIKE '%s'
   LIMIT %s,%s
";
?>