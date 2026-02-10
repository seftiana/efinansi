<?php
   $sql['get_combo_bas'] ="
      SELECT
         paguBasId AS id,
         CONCAT(paguBasKode, ' - ',paguBasKeterangan) AS name
      FROM finansi_ref_pagu_bas
      WHERE
	      paguBasStatusAktif = 'Y'
   ";
   
    /**   
    $sql['update_komponen_anggaran'] = "
    UPDATE rencana_pengeluaran
    SET 
        rncnpengeluaranKomponenTotalAprove = (rncnpengeluaranKomponenTotalAprove+%s)
    WHERE rncnpengeluaranId = '%s'
    ";
    
    $sql['update_komponen_anggaran_asal'] = "
    UPDATE rencana_pengeluaran
    SET 
        rncnpengeluaranKomponenTotalAprove = (rncnpengeluaranKomponenTotalAprove-%s) 
    WHERE rncnpengeluaranId = '%s'
    ";
    */
    
    $sql['insert_into_history_movement'] = "
    INSERT INTO `finansi_pa_movement_history`
                (`movementHistoryId`,
                 `movementHistoryNomor`,
                 `movementHistoryTahunAnggaranId`,
                 `movementHistoryUnitKerjaIdAsal`,
                 `movementHistoryKegrefIdAsal`,
                 `movementHistoryUnitKerjaIdTujuan`,
                 `movementHistoryKegrefIdTujuan`,
                 `movementHistoryNilai`,
                 `movementHistoryTanggal`,
                 `movementHistoryTanggalUbah`,
                 `movementHistoryUserId`)
    VALUES (NULL,
            (SELECT 
                IFNULL(MAX(tmp.`movementHistoryNomor`),0)+1 AS nomor 
            FROM `finansi_pa_movement_history` AS tmp
            LIMIT 0,1),
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            NOW(),
            NOW(),
            '%s')
    ";
    
    $sql['get_last_insert_id_apbnp']     = "
        SELECT MAX(movementHistoryId) AS last_id FROM finansi_pa_movement_history
    ";
    
    $sql['insert_into_apbnp_detail']    = "
        INSERT INTO `finansi_pa_movement_history_detail`
                    (`movementHistoryDetailId`,
                     `movementHistoryDetailMovementHistoryId`,
                     `movementHistoryDetailRncnpengeluaranId`,
                     `movementHistoryDetailNilaiSemula`,
                     `movementHistoryDetailNilai`,
                     `movementHistoryDetailType`,
                     `movementHistoryDetailTanggal`,
                     `movementHistoryDetailUserId`)
        VALUES (NULL,
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                NOW(),
                '%s')
    ";
    
    $sql['delete_apbnp']    = "
        DELETE
        FROM `finansi_pa_movement_history`
        WHERE `movementHistoryId` = '%s'
    ";
    $sql['get_tahun_anggaran_aktif']="
	    SELECT
		    thanggarId as id,
		    thanggarNama as name
	    FROM
		    tahun_anggaran
	    WHERE
		    thanggarIsAktif='Y'
    ";
?>
