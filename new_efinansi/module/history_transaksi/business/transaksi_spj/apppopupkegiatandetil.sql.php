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
      SUM(pengrealNominalAprove) AS nominal_aprove,
      IFNULL(
      (
         SELECT
            SUM(transNilai)
         FROM
            transaksi
            JOIN pembukuan_referensi ON prTransId = transId AND prIsPosting = 'Y'
            LEFT JOIN transaksi_detail_pencairan ON transId = transdtpencairanTransId
            LEFT JOIN transaksi_detail_pengembalian ON transId = transdtpengembalianTransId
            LEFT JOIN transaksi_detail_realisasi ON transId = transdtrealisasiRealTransId
         WHERE
            ((transdtpencairanKegdetId = kegdetId AND transdtpencairanPengrealId = pengrealId AND transdtpengembalianTransId IS NULL) OR
            (transdtpengembalianKegdetId = kegdetId AND transdtpengembalianPengrealId = pengrealId AND transdtpencairanId IS NULL)) AND
            transdtrealisasiId IS NULL
      ), 0) as nominal_yg_sudah_dicairkan,
      IFNULL(
      (
         SELECT
            SUM(transNilai)
         FROM
            transaksi
            LEFT JOIN transaksi_detail_spj ON transId = transdtspjTransId
         WHERE
            transdtspjKegdetId = kegdetId
      ), 0) as nominal_yg_sudah_dispjkan
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
   GROUP BY
      kegdetId
   HAVING
      nominal_yg_sudah_dicairkan >=0 
   LIMIT %s, %s
";
?>