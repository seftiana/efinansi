<?php
/**
 * @package SQL-FILE
 */
$sql['get_data']     = "
SELECT
   SQL_CALC_FOUND_ROWS
   dipaId AS id,
   dipaNomor AS kode,
   dipaTanggal AS tanggal,
   dipaNominal AS nominal,
   IF(UPPER(dipaIsAktif) = 'Y', 'ACTIVE', 'NOT_ACTIVE') AS `status`
FROM finansi_pa_dipa
WHERE 1 = 1
AND dipaNomor LIKE '%s'
ORDER BY dipaIsAktif
LIMIT %s, %s
";

$sql['count']        = "
SELECT FOUND_ROWS() AS `count`
";

$sql['check_duplicate']    = "
SELECT
   COUNT(DISTINCT dipaId) AS `count`
FROM finansi_pa_dipa
WHERE 1 = 1
AND (dipaId != '%s' OR 1 = %s)
AND (dipaNomor = '%s' OR 1 = %s)
";

$sql['do_save_dipa']       = "
INSERT INTO finansi_pa_dipa
SET dipaNomor = '%s',
   dipaTanggal = '%s',
   dipaNominal = '%s',
   dipaIsAktif = '%s',
   dipaUserId = '%s'
";

$sql['reset_status']       = "
UPDATE finansi_pa_dipa SET dipaIsAktif = 'T'
";

$sql['do_delete_dipa']     = "
DELETE
FROM finansi_pa_dipa
WHERE dipaId = '%s'
";

$sql['check_dipa_aktif']   = "
SELECT
   dipaId,
   dipaNomor,
   dipaTanggal,
   dipaNominal,
   dipaIsAktif,
   dipaTanggalUbah,
   dipaUserId
FROM finansi_pa_dipa
WHERE 1 = 1
AND dipaId = %s
AND dipaIsAktif = 'Y'
";

$sql['get_data_detail']    = "
SELECT
   dipaId AS id,
   dipaNomor AS kode,
   dipaTanggal AS tanggal,
   dipaNominal AS nominal,
   UPPER(dipaIsAktif) AS `status`,
   IF(UPPER(dipaIsAktif) = 'Y', 'ACTIVE', 'NOT_ACTIVE') AS `status_label`
FROM finansi_pa_dipa
WHERE 1 = 1
AND dipaId = %s
LIMIT 0, 1
";

$sql['do_update_dipa']  = "
UPDATE finansi_pa_dipa
SET dipaNomor = '%s',
   dipaTanggal = '%s',
   dipaNominal = '%s',
   dipaUserId = '%s'
WHERE dipaId = %s
";

$sql['set_aktif_dipa']  = "
UPDATE finansi_pa_dipa
SET dipaIsAktif = 'Y'
WHERE dipaId = '%s'
";
?>