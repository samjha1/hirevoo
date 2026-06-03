-- ═══════════════════════════════════════════════════════════════════
-- Hirevo synthetic catalog import (10,000 job goals + 20,000 jobs)
-- Generated: 2026-06-02 23:36:51
--
-- Import order (phpMyAdmin / mysql CLI):
--   1. 00_setup_catalog_employer.sql
--   2. job_roles_part_01.sql … job_roles_part_20.sql
--   3. employer_jobs_part_01.sql … employer_jobs_part_40.sql
--
-- Or run: php database/scripts/import-synthetic-catalog-sql.php
--
-- Catalog employer login: catalog-employer@hirevo.com
-- Default password: ChangeMeCatalog!  (change immediately on production)
-- ═══════════════════════════════════════════════════════════════════

SET FOREIGN_KEY_CHECKS=0;
SET NAMES utf8mb4;

SOURCE 00_setup_catalog_employer.sql;
SOURCE job_roles_part_01.sql;
SOURCE job_roles_part_02.sql;
SOURCE job_roles_part_03.sql;
SOURCE job_roles_part_04.sql;
SOURCE job_roles_part_05.sql;
SOURCE job_roles_part_06.sql;
SOURCE job_roles_part_07.sql;
SOURCE job_roles_part_08.sql;
SOURCE job_roles_part_09.sql;
SOURCE job_roles_part_10.sql;
SOURCE job_roles_part_11.sql;
SOURCE job_roles_part_12.sql;
SOURCE job_roles_part_13.sql;
SOURCE job_roles_part_14.sql;
SOURCE job_roles_part_15.sql;
SOURCE job_roles_part_16.sql;
SOURCE job_roles_part_17.sql;
SOURCE job_roles_part_18.sql;
SOURCE job_roles_part_19.sql;
SOURCE job_roles_part_20.sql;
SOURCE employer_jobs_part_01.sql;
SOURCE employer_jobs_part_02.sql;
SOURCE employer_jobs_part_03.sql;
SOURCE employer_jobs_part_04.sql;
SOURCE employer_jobs_part_05.sql;
SOURCE employer_jobs_part_06.sql;
SOURCE employer_jobs_part_07.sql;
SOURCE employer_jobs_part_08.sql;
SOURCE employer_jobs_part_09.sql;
SOURCE employer_jobs_part_10.sql;
SOURCE employer_jobs_part_11.sql;
SOURCE employer_jobs_part_12.sql;
SOURCE employer_jobs_part_13.sql;
SOURCE employer_jobs_part_14.sql;
SOURCE employer_jobs_part_15.sql;
SOURCE employer_jobs_part_16.sql;
SOURCE employer_jobs_part_17.sql;
SOURCE employer_jobs_part_18.sql;
SOURCE employer_jobs_part_19.sql;
SOURCE employer_jobs_part_20.sql;
SOURCE employer_jobs_part_21.sql;
SOURCE employer_jobs_part_22.sql;
SOURCE employer_jobs_part_23.sql;
SOURCE employer_jobs_part_24.sql;
SOURCE employer_jobs_part_25.sql;
SOURCE employer_jobs_part_26.sql;
SOURCE employer_jobs_part_27.sql;
SOURCE employer_jobs_part_28.sql;
SOURCE employer_jobs_part_29.sql;
SOURCE employer_jobs_part_30.sql;
SOURCE employer_jobs_part_31.sql;
SOURCE employer_jobs_part_32.sql;
SOURCE employer_jobs_part_33.sql;
SOURCE employer_jobs_part_34.sql;
SOURCE employer_jobs_part_35.sql;
SOURCE employer_jobs_part_36.sql;
SOURCE employer_jobs_part_37.sql;
SOURCE employer_jobs_part_38.sql;
SOURCE employer_jobs_part_39.sql;
SOURCE employer_jobs_part_40.sql;

SET FOREIGN_KEY_CHECKS=1;
