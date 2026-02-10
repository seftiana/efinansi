<?php

$sql['get_count_data'] = "
   SELECT FOUND_ROWS() as total
";

$sql['get_data'] = "
	SELECT
		renterimaId           AS id,
		kodeTerimaKode        AS kode,
		kodeTerimaNama        AS nama,
		renterimaTotalTerima  AS nominal_aprove,
		SUM(realterimaTotalTerima) AS nominal_yg_sudah_dicairkan,
		(renterimaTotalTerima  - SUM(realterimaTotalTerima)) AS sisa_pencairan
		
	FROM rencana_penerimaan
		LEFT JOIN kode_penerimaan_ref
			ON kodeterimaId = renterimaKodeterimaId
		LEFT JOIN realisasi_penerimaan
			ON realrenterimaId = renterimaId
		LEFT JOIN unit_kerja_ref
			ON unitkerjaid = renterimaUnitkerjaId
	WHERE unitkerjaId = '%s'
		AND kodeterimaNama LIKE '%s' AND renterimaRpstatusId='2'
	GROUP BY renterimaId
        
	LIMIT %s, %s
	
";

/* $sql['get_data_old'] = "
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
  "; */

/*
  SELECT
  renterimaId           AS ren_id,
  kodeterimaId          AS kode_id,
  kodeTerimaKode        AS kode,
  kodeTerimaNama        AS nama,
  renterimaTotalTerima  AS rencana,
  realterimaTotalTerima AS realisasi,
  IFNULL((renterimaTotalTerima - realterimaTotalTerima),0) AS sisa
  FROM rencana_penerimaan
  LEFT JOIN kode_penerimaan_ref
  ON kodeterimaId = renterimaKodeterimaId
  LEFT JOIN realisasi_penerimaan
  ON realrenterimaId = renterimaId
  LEFT JOIN unit_kerja_ref
  ON unitkerjaid = renterimaUnitkerjaId
  WHERE unitkerjaId = ''
  AND kodeterimaNama LIKE '%%'
  LIMIT %s, %s

  /*
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
 */
?>
