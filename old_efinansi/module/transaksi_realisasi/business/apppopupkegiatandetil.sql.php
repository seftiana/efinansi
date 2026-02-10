<?php
$sql['get_count_data'] = "
   SELECT FOUND_ROWS() as total
";

$sql['get_data'] = "
   SELECT
      SQL_CALC_FOUND_ROWS
      CONCAT_WS('|', kegdetId, pengrealId) as id,
      kegrefNomor as kode,
      kegrefNama as nama,
      pengrealNominalAprove AS nominal_aprove,
      IFNULL(
      (
         SELECT
            SUM(transNilai)
         FROM transaksi
         LEFT JOIN transaksi_detail_pencairan ON transId = transdtpencairanTransId
         WHERE (transdtpencairanKegdetId = kegdetId AND transdtpencairanPengrealId = pengrealId)
      ), 0) as nominal_yg_sudah_dicairkan
   FROM
      kegiatan_detail
      JOIN kegiatan_ref ON (kegrefId = kegdetKegrefId)
      JOIN kegiatan ON (kegId = kegdetKegId)
      JOIN pengajuan_realisasi ON (pengrealKegdetId = kegdetId)
   WHERE
      pengrealIsApprove='Ya'
      AND kegdetIsAprove = 'Ya'
      AND kegUnitkerjaId = %s
      AND kegrefNama LIKE '%s'
   HAVING
      (nominal_aprove - nominal_yg_sudah_dicairkan) > 0
   LIMIT %s, %s
";

$sql['get_data_old'] = "
   SELECT
      SQL_CALC_FOUND_ROWS
      CONCAT_WS('|', kegdetId, pengrealId) as id,
      kegrefNomor as kode,
      kegrefNama as nama,
      pengrealNominalAprove AS nominal_aprove,
      IFNULL(
      (
         SELECT
            SUM(transNilai)
         FROM
            transaksi
            LEFT JOIN transaksi_detail_anggaran ON transId = transdtanggarTransId
            LEFT JOIN transaksi_detail_pengembalian ON transId = transdtpengembalianTransId
         WHERE
            (transdtanggarKegdetId = kegdetId AND transdtanggarPengrealId = pengrealId) OR
            (transdtpengembalianKegdetId = kegdetId AND transdtpengembalianPengrealId = pengrealId)
      ), 0) as nominal_yg_sudah_dicairkan
   FROM
      kegiatan_detail
      JOIN kegiatan_ref ON (kegrefId = kegdetKegrefId)
      JOIN kegiatan ON (kegId = kegdetKegId)
      JOIN pengajuan_realisasi ON (pengrealKegdetId = kegdetId)
   WHERE
      pengrealIsApprove='Ya'
      AND kegdetIsAprove = 'Ya'
      AND kegUnitkerjaId = %s
      AND kegrefNama LIKE '%s'
   HAVING
      (nominal_aprove - nominal_yg_sudah_dicairkan) > 0
   LIMIT %s, %s
";
?>
