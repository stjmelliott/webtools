-- export_script.sql (DB2 CLP) — TEMPLATE
-- Place this file at one of:
--   1) C:\clients\midway\webtools\invoice_export\back_end\export_script.sql
--   2) C:\MidwayExport\export_script.sql
-- Or set $INVEXP_CONFIG['paths']['export_sql'] to your exact path.

-- Example: run your EXPORT and then (optionally) mark rows as exported.
-- NOTE: Adjust paths, schema, and SELECT to match your environment.

-- EXPORT to today's CSV (if you prefer DB2-side export):
-- EXPORT TO "C:\trax_invoice_export\SHWN_MDYT_MT_YYYY-MM-DD.csv"
--   OF DEL MODIFIED BY NOCHARDEL COLDEL, METHOD L (ALL)
--   MESSAGES "C:\clients\midway\webtools\log\db2_export_messages.log"
--   SELECT ... FROM TMWIN.TLORDER ... ;

-- OPTIONAL: mark rows as exported with date (uncomment only when ready)
-- UPDATE TMWIN.TLORDER
--    SET USER10 = 'EXPORTED ' || VARCHAR_FORMAT(CURRENT_DATE, 'YYYY-MM-DD')
--  WHERE CURRENT_STATUS IN ('APPRVD','BILLD')
--    AND CUSTOMER = 'SHERBILLTO'
--    AND (USER10 IS NULL OR USER10 NOT LIKE 'EXPORTED %');

COMMIT;
