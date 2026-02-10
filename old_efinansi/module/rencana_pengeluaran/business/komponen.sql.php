<?php
$sql['count']        = "
SELECT FOUND_ROWS() AS `count`
";

$sql['get_data']     = "
SELECT SQL_CALC_FOUND_ROWS
   komponen.kompId AS id,
   kompKode AS kode,
   LEFT(kompKode,2) AS basIdKomponen,
   komponen.kompNama AS nama,
   0 AS jumlah,
   IF(kompFormula IS NULL OR komponen.kompFormula = '', 1, komponen.kompFormula) AS formula,
   IF(kompFormulaHasil = '0' ,1, kompFormulaHasil) AS hasilFormula,
   kompNamaSatuan AS satuan,
   IFNULL(kompkegBiaya, kompHargaSatuan) AS biayaMax,
   kompIsSBU AS isSbu,
   IFNULL(kompkegBiaya, kompHargaSatuan) AS biaya,
   IF(kompDeskripsi = '', '-', kompDeskripsi) AS deskripsi,
   NULL AS rencanaPengeluaranId,
   bas.paguBasid AS basId,
   bas.paguBasKode AS basKode,
   bas.paguBasKeterangan AS basNama,
   mak.paguBasId AS makId,
   mak.paguBasKode AS makKode,
   mak.paguBasKeterangan AS makNama,
   coaId AS akunId,
   coaKodeAkun AS akunKode,
   coaNamaAkun AS akunNama,
   IFNULL(finansi_pa_komponen_unit_kerja.kompUnitNominal, 0) AS komponenNominal,
   IFNULL((IFNULL(kompkegBiaya, kompHargaSatuan)  * 1), 0) AS totalBiaya,
   kompSumberDanaId AS sumberDanaId,
   'BELUM' AS `status`
FROM
   komponen
   LEFT JOIN komponen_kegiatan
      ON kompkegKompId = kompId
      AND (kompkegKegrefId = %s OR 1 = %s)
   LEFT JOIN finansi_ref_pagu_bas AS mak
      ON mak.paguBasId = kompMakId
   LEFT JOIN finansi_ref_pagu_bas AS bas
      ON bas.paguBasId = mak.paguBasParentId
   LEFT JOIN coa
      ON coaId = kompCoaId
   LEFT JOIN finansi_pa_komponen_unit_kerja
      ON finansi_pa_komponen_unit_kerja.kompUnitKompId = komponen.kompId
      AND finansi_pa_komponen_unit_kerja.kompUnitUnitKerjaId = %s
WHERE 1 = 1
AND kompKode LIKE '%s'
AND kompNama LIKE '%s'
ORDER BY kompKode
LIMIT %s, %s
";
?>