-- dc50_dbos.UniversalFileH source

CREATE OR REPLACE
ALGORITHM = UNDEFINED VIEW `UniversalFileH` AS
select
    `ILI`.`acct_month` AS `acct_month`,
    min(`ILI`.`received_dt`) AS `received_dt`,
    1 AS `hd`,
    'H' AS `A`,
    concat(`ILI`.`lob_cd`, ' ') AS `B`,
    `ILI`.`license_nbr` AS `C`,
    `Lo`.`abbrev` AS `D`,
    (case
        when (right(`ILI`.`acct_month`,
        2) = '01') then convert(concat('12/',(left(`ILI`.`acct_month`, 4) - 1))
            using utf8mb3)
        else concat((right(`ILI`.`acct_month`, 2) - 1), '/', left(`ILI`.`acct_month`, 4))
    end) AS `E`,
    ((coalesce(`FSL`.`amt`, 0) + coalesce(`FSI`.`amt`, 0)) + coalesce((`FSH`.`amt` * 0.25), 0)) AS `F`,
    coalesce(`FSH`.`amt`, 0) AS `G`,
    NULL AS `H`,
    'M' AS `I`,
    date_format(`ILI`.`received_dt`, '%m/%d/%y') AS `J`,
    `Co`.`employer` AS `K`,
    NULL AS `L`,
    date_format(`Co`.`signed_dt`, '%m/%d/%y') AS `M`,
    `Co`.`address` AS `N`,
    `Co`.`city` AS `O`,
    `Co`.`st` AS `P`,
    concat(`Co`.`zip`, ' ') AS `Q`,
    'U.S.A.' AS `R`,
    NULL AS `S`,
    NULL AS `T`,
    NULL AS `U`,
    NULL AS `V`,
    NULL AS `W`,
    NULL AS `X`,
    NULL AS `Y`,
    NULL AS `Z`,
    NULL AS `AA`,
    NULL AS `AB`,
    NULL AS `AC`,
    NULL AS `AD`,
    NULL AS `AE`,
    NULL AS `AF`,
    NULL AS `AG`,
    NULL AS `AH`,
    NULL AS `AI`,
    NULL AS `AJ`,
    NULL AS `AK`,
    NULL AS `AL`,
    NULL AS `AM`,
    NULL AS `AN`,
    NULL AS `AO`,
    NULL AS `AP`,
    NULL AS `AQ`,
    NULL AS `AR`,
    NULL AS `AS`,
    NULL AS `AT`,
    NULL AS `AU`,
    NULL AS `AV`,
    NULL AS `AW`,
    coalesce(`FAD`.`amt`, 0) AS `AX`
from
    ((((((((`IntLineItems` `ILI`
left join `Lobs` `Lo` on
    ((`Lo`.`lob_cd` = `ILI`.`lob_cd`)))
left join `EmplFeeSumByMonth` `FSH` on
    (((`FSH`.`acct_month` = `ILI`.`acct_month`)
        and (`FSH`.`license_nbr` = `ILI`.`license_nbr`)
            and (`FSH`.`lob_cd` = `ILI`.`lob_cd`)
                and (`FSH`.`fee_type` = 'HR'))))
left join `EmplFeeSumByMonth` `FSL` on
    (((`FSL`.`acct_month` = `ILI`.`acct_month`)
        and (`FSL`.`license_nbr` = `ILI`.`license_nbr`)
            and (`FSL`.`lob_cd` = `ILI`.`lob_cd`)
                and (`FSL`.`fee_type` = 'LM'))))
left join `EmplFeeSumByMonth` `FSI` on
    (((`FSI`.`acct_month` = `ILI`.`acct_month`)
        and (`FSI`.`license_nbr` = `ILI`.`license_nbr`)
            and (`FSI`.`lob_cd` = `ILI`.`lob_cd`)
                and (`FSI`.`fee_type` = 'IU'))))
left join `EmplFeeSumByMonth` `FS5` on
    (((`FS5`.`acct_month` = `ILI`.`acct_month`)
        and (`FS5`.`license_nbr` = `ILI`.`license_nbr`)
            and (`FS5`.`lob_cd` = `ILI`.`lob_cd`)
                and (`FS5`.`fee_type` = 'PC'))))
left join `EmplFeeSumByMonth` `FSA` on
    (((`FSA`.`acct_month` = `ILI`.`acct_month`)
        and (`FSA`.`license_nbr` = `ILI`.`license_nbr`)
            and (`FSA`.`lob_cd` = `ILI`.`lob_cd`)
                and (`FSA`.`fee_type` = 'IN'))))
left join `EmplFeeSumByMonth` `FAD` on
    (((`FAD`.`acct_month` = `ILI`.`acct_month`)
        and (`FAD`.`license_nbr` = `ILI`.`license_nbr`)
            and (`FAD`.`lob_cd` = `ILI`.`lob_cd`)
                and (`FAD`.`fee_type` = 'AD'))))
join `ContractorAddrList` `Co` on
    ((`Co`.`license_nbr` = `ILI`.`license_nbr`)))
group by
    `ILI`.`acct_month`,
    `ILI`.`license_nbr`,
    `ILI`.`lob_cd`;