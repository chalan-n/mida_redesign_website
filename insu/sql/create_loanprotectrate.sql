-- =============================================
-- สร้าง Functions ใหม่ด้วย SQL SECURITY INVOKER
-- เพื่อให้ user ทุกคนสามารถเรียกใช้ได้
-- รันด้วย user root ใน Navicat
-- =============================================

-- Drop functions เดิม
DROP FUNCTION IF EXISTS `Fnc_LoanProtect_Rate`;
DROP FUNCTION IF EXISTS `Fnc_LoanProtect_age`;
DROP FUNCTION IF EXISTS `Fnc_LoanProtect_Term`;

DELIMITER $$

-- =============================================
-- Function 1: Fnc_LoanProtect_age
-- หา age bracket ที่เหมาะสม
-- =============================================
CREATE FUNCTION `Fnc_LoanProtect_age` (
    `CmpInsuID` VARCHAR(5), 
    `SexID` VARCHAR(1), 
    `sAge` INT, 
    `AppDate` VARCHAR(8)
) 
RETURNS INT(11)
DETERMINISTIC
SQL SECURITY INVOKER
BEGIN
    DECLARE LoanProtectAge INT;
    DECLARE LoanProtectAge2 INT;
    DECLARE ratedate VARCHAR(8);

    SELECT MAX(loanprotectrate.rate_date) INTO ratedate 
    FROM loanprotectrate 
    WHERE loanprotectrate.CmpInsuranceID = CmpInsuID 
      AND loanprotectrate.rate_date <= AppDate;

    SELECT MAX(loanprotectrate.age_from) INTO LoanProtectAge 
    FROM loanprotectrate 
    WHERE loanprotectrate.sexid = SexID 
      AND loanprotectrate.age_from = sAge 
      AND loanprotectrate.CmpInsuranceID = CmpInsuID 
      AND loanprotectrate.rate_date = ratedate;

    SELECT MAX(loanprotectrate.age_from) INTO LoanProtectAge2 
    FROM loanprotectrate 
    WHERE loanprotectrate.sexid = SexID 
      AND loanprotectrate.age_from < sAge 
      AND loanprotectrate.CmpInsuranceID = CmpInsuID 
      AND loanprotectrate.rate_date = ratedate;
    
    IF LoanProtectAge IS NULL OR LoanProtectAge = 0 THEN 
        SET LoanProtectAge = LoanProtectAge2; 
    END IF;

    RETURN LoanProtectAge;
END$$

-- =============================================
-- Function 2: Fnc_LoanProtect_Term
-- หา term ที่เหมาะสม
-- =============================================
CREATE FUNCTION `Fnc_LoanProtect_Term` (
    `CmpInsuID` VARCHAR(5), 
    `SexID` VARCHAR(1), 
    `term` INT, 
    `AppDate` VARCHAR(8)
) 
RETURNS INT(11)
DETERMINISTIC
SQL SECURITY INVOKER
BEGIN
    DECLARE LoanProtectTerm INT;
    DECLARE TermMonth INT;
    DECLARE ratedate VARCHAR(8);
    
    SELECT MAX(loanprotectrate.rate_date) INTO ratedate 
    FROM loanprotectrate 
    WHERE loanprotectrate.CmpInsuranceID = CmpInsuID 
      AND loanprotectrate.rate_date <= AppDate;
    
    SELECT MAX(loanprotectrate.Term_month) INTO LoanProtectTerm 
    FROM loanprotectrate 
    WHERE loanprotectrate.sexid = SexID 
      AND loanprotectrate.Term_month = term 
      AND loanprotectrate.CmpInsuranceID = CmpInsuID 
      AND loanprotectrate.rate_date = ratedate;

    IF LoanProtectTerm IS NULL THEN 
        SET LoanProtectTerm = 0; 
    ELSE 
        SET LoanProtectTerm = term;
    END IF;

    RETURN LoanProtectTerm;
END$$

-- =============================================
-- Function 3: Fnc_LoanProtect_Rate (Main Function)
-- คำนวณอัตราค่าเบี้ยประกัน
-- =============================================
CREATE FUNCTION `Fnc_LoanProtect_Rate` (
    `CmpInsuID` VARCHAR(5), 
    `SexID` VARCHAR(1), 
    `Term` INT, 
    `sAge` INT, 
    `AppDate` VARCHAR(8)
) 
RETURNS DECIMAL(10,4)
DETERMINISTIC
SQL SECURITY INVOKER
BEGIN
    DECLARE LoanRate DECIMAL(10,4);
    DECLARE LoanProtectAge INT;
    DECLARE LoanProtectTerm INT;
    DECLARE ratedate VARCHAR(8);

    SELECT MAX(loanprotectrate.rate_date) INTO ratedate 
    FROM loanprotectrate 
    WHERE loanprotectrate.CmpInsuranceID = CmpInsuID 
      AND loanprotectrate.rate_date <= AppDate;
        
    SELECT `Fnc_LoanProtect_age`(CmpInsuID, SexID, sAge, AppDate) INTO LoanProtectAge;
        
    SELECT `Fnc_LoanProtect_Term`(CmpInsuID, SexID, Term, AppDate) INTO LoanProtectTerm;

    SELECT loanprotectrate.rate INTO LoanRate 
    FROM loanprotectrate 
    WHERE loanprotectrate.sexid = SexID 
      AND loanprotectrate.Term_month = LoanProtectTerm 
      AND loanprotectrate.age_from = LoanProtectAge 
      AND loanprotectrate.CmpInsuranceID = CmpInsuID 
      AND loanprotectrate.rate_date = ratedate;
    
    IF LoanRate IS NULL THEN 
        SET LoanRate = 0.00; 
    END IF;

    RETURN LoanRate;
END$$

DELIMITER ;

-- =============================================
-- ทดสอบ Function
-- =============================================
-- SELECT Fnc_LoanProtect_Rate('03', '1', 48, 35, '20260115') AS RATE;
