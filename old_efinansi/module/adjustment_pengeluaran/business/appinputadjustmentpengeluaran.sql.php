<?php

//===GET===

$sql['get_informasi'] = "
SELECT
	kegUnitkerjaId AS unit_kerja_id,
	thanggarNama AS tahun_anggaran_label,
	programNama AS program_label,
	subprogNama AS kegiatan_label,
	kegrefNama AS subkegiatan_label,
	(if(tempUnitNama IS NULL,unitkerjaNama,tempUnitNama)) AS satker_nama,
	(if(tempUnitNama IS NULL,'-',unitkerjaNama)) AS unit_kerja_nama,
	unitkerjaParentId as is_unit_kerja
FROM 
	kegiatan_detail
	JOIN kegiatan_ref ON (kegrefId = kegdetKegrefId)
	JOIN sub_program ON (subprogId = kegrefSubprogId)
	JOIN program_ref ON (programId = subprogProgramId)
	JOIN tahun_anggaran ON (thanggarId = programThanggarId)
	JOIN kegiatan ON (kegId = kegdetKegId)
    JOIN unit_kerja_ref ON (unitkerjaId = kegUnitkerjaId)
		LEFT JOIN 
				(SELECT 
					unitkerjaId AS tempUnitId,
					unitkerjaKode AS tempUnitKode,
					unitkerjaNama AS tempUnitNama,
					unitkerjaParentId AS tempParentId
				FROM unit_kerja_ref 
				WHERE unitkerjaParentId = 0) tmpUnitKerja ON(unitkerjaParentId=tempUnitId)
WHERE
	kegdetId=%s
";

$sql['get_count_data'] = "
SELECT
		COUNT(*) as total
	FROM
		rencana_pengeluaran
	WHERE	  
	  rncnpengeluaranKegdetId=%s 
";

$sql['get_data'] = "
SELECT   
	   rncnpengeluaranId as id,
	   rncnpengeluaranKomponenKode as kode,
	   rncnpengeluaranKomponenNama as nama,
	   rncnpengeluaranKomponenNominal * IFNULL(kompFormulaHasil,1) as nominal_usulan,
	   rncnpengeluaranSatuan as satuan_usulan,
	   IFNULL(kompFormulaHasil,1) as hasil_formula,
	   rncnpengeluaranSatuan * rncnpengeluaranKomponenNominal * IFNULL(kompFormulaHasil,1) as jumlah_usulan,
	   rncnpengeluaranKomponenNominalAprove * IFNULL(kompFormulaHasil,1) as nominal_setuju,
	   rncnpengeluaranSatuanAprove as satuan_setuju,
	   rncnpengeluaranSatuanAprove * rncnpengeluaranKomponenNominalAprove * IFNULL(kompFormulaHasil,1) as jumlah_setuju,
	   rncnpengeluaranKomponenDeskripsi as deskripsi,
	   rncnpengeluaranIsAprove as approval
	FROM
		rencana_pengeluaran
		LEFT JOIN kegiatan_detail ON (kegdetId = rncnpengeluaranKegdetId)
		LEFT JOIN komponen ON kompKode = rncnpengeluaranKomponenKode
		/*LEFT JOIN kegiatan_ref ON (kegrefId = kegdetId)*/
		/*LEFT JOIN komponen_kegiatan ON (kompkegKegrefId = kegrefId)*/
	WHERE	  
	  rncnpengeluaranKegdetId=%s AND rncnpengeluaranIsAprove='Ya' 
	ORDER BY
	  rncnpengeluaranKomponenKode
";

$sql['get_history_is_exist'] = "
SELECT   
     COUNT(rncnpengeluaranhisId) as jml
	FROM
		rencana_pengeluaran_history
	WHERE	  
	  rncnpengeluaranhisId IN ('%s');
";

$sql['do_update_detil_approval'] = 
   "UPDATE rencana_pengeluaran
   SET
      rncnpengeluaranKomponenNominalAprove=%s,
      rncnpengeluaranSatuanAprove=%s
   WHERE 
      rncnpengeluaranId=%s
";

$sql['do_add_history'] = 
   "INSERT INTO 
      rencana_pengeluaran_history
      (
      `rncnpengeluaranhisId`,
      `rncnpengeluaranhisKegdetId`,
      `rncnpengeluaranhisKomponenKode`,
      `rncnpengeluaranhisKomponenNama`,
      `rncnpengeluaranhisSatuan`,
      `rncnpengeluaranhisNamaSatuan`,
      `rncnpengeluaranhisKomponenNominal`,
      `rncnpengeluaranhisKomponenDeskripsi`,
      `rncnpengeluaranhisSatuanAprove`,
      `rncnpengeluaranhisKomponenNominalAprove`,
      `rncnpengeluaranhisIsAprove`)
      SELECT 
        rncnpengeluaranId,
        rncnpengeluaranKegdetId,
        rncnpengeluaranKomponenKode,
        rncnpengeluaranKomponenNama,
        rncnpengeluaranSatuan,
        rncnpengeluaranNamaSatuan,
        rncnpengeluaranKomponenNominal,
        rncnpengeluaranKomponenDeskripsi,
        rncnpengeluaranSatuanAprove,
        rncnpengeluaranKomponenNominalAprove,
        rncnpengeluaranIsAprove
      FROM 
        rencana_pengeluaran 
      WHERE rncnpengeluaranId IN ('%s')
";



?>
