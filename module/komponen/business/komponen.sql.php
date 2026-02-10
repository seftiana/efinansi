<?php
$sql['count']        = "
SELECT FOUND_ROWS() AS `count`
";

$sql['get_data']     = "
SELECT
   SQL_CALC_FOUND_ROWS kompId AS id,
   kompKode AS kode,
   kompNama AS nama,
   kompNamaSatuan AS satuan,
   IF(kompDeskripsi IS NULL OR kompDeskripsi = '', '-', kompDeskripsi) AS deskripsi,
   IF(kompFormula IS NULL OR kompFormula = '', 1, kompFormula) AS formula,
   kompHargaSatuan AS hargaSatuan,
   paguBasKode,
   paguBasKode AS makKode,
   paguBasKeterangan AS makNama,
   IFNULL(sumberdanaNama, '-') AS sumberDana,
   IFNULL(komp.count, 0) AS komponen,
   c.`coaKodeAkun` AS kodeCoa,
   c.`coaNamaAkun` AS namaCoa
FROM
   komponen
   LEFT JOIN finansi_ref_pagu_bas
      ON paguBasId = kompMakId
   LEFT JOIN finansi_ref_sumber_dana
      ON sumberdanaId = kompSumberDanaId
   LEFT JOIN coa c
      ON c.`coaId` = komponen.`kompCoaId`  
   LEFT JOIN (SELECT
      kompkegKompId AS id,
      COUNT(kompkegKompId) AS `count`
   FROM komponen_kegiatan
   JOIN kegiatan_ref
      ON kegrefId = kompkegKegrefId
   GROUP BY kompkegKompId) AS komp ON komp.id = kompId
WHERE 1 = 1
   AND (kompNama LIKE '%s' OR kompKode LIKE '%s')
ORDER BY kompKode + 0 DESC
LIMIT %s, %s
";

$sql['get_jenis_komponen'] = "
   SELECT
      satkompNama AS `id`,
      satkompNama AS `name`
   FROM
      satuan_komponen
";

$sql['get_limit_komponen'] = "
     SELECT
         kompId,
         kompNama,
         kompNamaSatuan,
         kompDeskripsi,
         kompFormula,
         kompHargaSatuan,
       paguBasKode,
       paguBasKeterangan,
       sumberdanaNama
      FROM komponen
     LEFT JOIN finansi_ref_pagu_bas ON paguBasId = kompMakId
     LEFT JOIN finansi_ref_sumber_dana ON sumberdanaId = kompSumberDanaId
      WHERE kompNama LIKE '%s' OR kompKode LIKE '%s'
      ORDER BY /*kompNamaSatuan,kompNama*/ kompId DESC
      LIMIT %d,%d
";
$sql['get_excel_komponen'] = "
     SELECT
         kompId as `id`,
         kompNama as `nama`,
         kompNamaSatuan as `satuan`,
         kompDeskripsi as `deskripsi`,
         kompFormula as `formula`,
         kompHargaSatuan as `harga_satuan`
      FROM komponen
      ORDER BY kompNama
";

$sql['jumlah_list_komponen'] = "
     SELECT
         COUNT(*) AS jumlah
      FROM komponen
      WHERE kompNama LIKE '%s'
";

$sql['get_komponen_from_id'] = "
      SELECT
         kompId,
         kompKode,
         kompNama,
         kompNamaSatuan,
      kompHargaSatuan,
      kompDeskripsi,
      kompFormula,
      kompIsSBU,
      kompMakId,
      paguBasKode,
      kompIsLangsung,
      kompIsTetap,
      coaId,
      coaKodeAkun,
      coaNamaAkun,
      kompSumberDanaId,
      sumberdanaNama,
        kompKodeAset,
        kompIsPengadaan
      FROM komponen
      LEFT JOIN coa ON coaId = kompCoaId
      LEFT JOIN finansi_ref_pagu_bas ON paguBasId = kompMakId
      LEFT JOIN finansi_ref_sumber_dana ON sumberdanaId = kompSumberDanaId
      WHERE kompId = '%s'
";

$sql['insert_komponen'] = "
INSERT INTO komponen SET kompKode = (SELECT LPAD(IFNULL(MAX(komp.kompKode+0)+1, 1), 4, 0)
FROM komponen AS komp),
kompNama = '%s',
kompNamaSatuan = '%s',
kompDeskripsi = '%s',
kompFormula = '%s',
kompCoaId = '%s',
kompHargaSatuan = '%s',
kompIsSBU = '%s',
kompMakId = '%s',
kompSumberDanaId = '%s',
kompIsLangsung = '%s',
kompIsTetap = '%s',
kompFormulaHasil = '%s',
kompKodeAset = '%s',
kompIsPengadaan = '%s'
";

$sql['update_komponen'] = "
UPDATE komponen SET kompNama = '%s',
   kompNamaSatuan = '%s',
   kompDeskripsi = '%s',
   kompFormula = '%s',
   kompCoaId = '%s',
   kompHargaSatuan = '%s',
   kompIsSBU = '%s',
   kompMakId = '%s',
   kompSumberDanaId = '%s',
   kompIsLangsung = '%s',
   kompIsTetap = '%s',
   kompFormulaHasil = '%s',
   kompKodeAset = '%s',
   kompIsPengadaan = '%s'
WHERE kompId = '%s'
";

$sql['delete_komponen'] = "
DELETE FROM komponen WHERE kompId IN(%s)
";

//untuk popup coa----
$sql['get_count_kode_penerimaan'] = "
SELECT
   COUNT(coaId) AS total
FROM
   coa a
WHERE
  a.coaKodeAkun  LIKE %s AND a.coaNamaAkun LIKE %s
  AND
  (select count(coaId) from coa where coaParentAkun = a.coaId) =0
";

$sql['get_data_kode_penerimaan'] = "
SELECT
   coaId       AS id,
   coaKodeAkun    AS kode,
   coaNamaAkun    AS nama
FROM coa a
WHERE
   a.coaKodeAkun  LIKE %s AND a.coaNamaAkun LIKE %s
AND
(select count(coaId) from coa where coaParentAkun = a.coaId) =0
ORDER BY coaKodeAkun
LIMIT %s, %s
";
// end utk popup coa--

//ambil data MAK
$sql['get_data_mak'] =
   "SELECT
      paguBasId AS id_mak,
      paguBasKode AS kode_mak,
      paguBasKeterangan AS nama_mak
   FROM
      finansi_ref_pagu_bas
   WHERE
      ( paguBasKeterangan LIKE %s
   OR
      paguBasKode LIKE %s )
      AND paguBasStatusAktif ='Y'
      AND paguBasParentId <> 0
   ORDER BY paguBasKode ASC
   LIMIT %s, %s
   ";

$sql['get_count_mak'] =
   "SELECT
      COUNT(paguBasId) AS total
   FROM
      finansi_ref_pagu_bas
   WHERE
      (paguBasKeterangan LIKE %s
   OR
      paguBasKode LIKE %s )
      AND paguBasStatusAktif ='Y'
      AND paguBasParentId <> 0
   ";

//popup sumber dana
$sql['get_data_sumber_dana'] =
   "SELECT
      sumberdanaId AS id_sumber_dana,
      sumberdanaNama AS nama_sumber_dana
   FROM
      finansi_ref_sumber_dana
   WHERE
      (sumberdanaNama LIKE %s AND isAktif ='Y')
      AND isAktif ='Y'
   ORDER BY sumberdanaNama ASC
   LIMIT %s, %s
   ";

$sql['get_count_sumber_dana'] =
   "SELECT
      COUNT(sumberdanaId) AS total
   FROM
      finansi_ref_sumber_dana
   WHERE
      (sumberdanaNama LIKE %s AND isAktif ='Y')
      AND isAktif ='Y'
   ";
?>
