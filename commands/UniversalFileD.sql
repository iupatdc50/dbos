-- dc50_dbos.UniversalFileD source

CREATE OR REPLACE
ALGORITHM = UNDEFINED VIEW `UniversalFileD` AS
select
    `ILI`.`acct_month` AS `acct_month`,
    `ILI`.`received_dt` AS `received_dt`,
    2 AS `hd`,
    'D' AS `A`,
    concat(`ILI`.`lob_cd`, ' ') AS `B`,
    `ILI`.`license_nbr` AS `C`,
    concat(left(`Me`.`ssnumber`, 3), substr(`Me`.`ssnumber`, 5, 2), right(`Me`.`ssnumber`, 4), ' ') AS `D`,
    (case
        when (right(`ILI`.`acct_month`,
        2) = '01') then convert(concat('12/',(left(`ILI`.`acct_month`, 4) - 1))
            using utf8mb3)
        else concat((right(`ILI`.`acct_month`, 2) - 1), '/', left(`ILI`.`acct_month`, 4))
    end) AS `E`,
    ((coalesce(`CtL`.`factor`, 0.00) + coalesce(`CtI`.`factor`)) + 0.10) AS `F`,
    ((coalesce(`CtL`.`factor`, 0.00) + coalesce(`CtI`.`factor`)) + 0.10) AS `G`,
    `FSH`.`amt` AS `H`,
    0 AS `I`,
    `FSH`.`amt` AS `J`,
    NULL AS `K`,
    (case
        when (`Me`.`hq_pac` = 'T') then 'Y'
        else 'N'
    end) AS `L`,
    (((coalesce(`CtL`.`factor`, 0.00) + coalesce(`CtI`.`factor`)) + 0.10) * `FSH`.`amt`) AS `M`,
    `Me`.`last_nm` AS `N`,
    `Me`.`first_nm` AS `O`,
    0 AS `P`,
    0 AS `Q`,
    0 AS `R`,
    NULL AS `S`,
    NULL AS `T`,
    NULL AS `U`,
    case when `Ms`.member_status <> 'O' then /* show if not out of state */
    	`CtL`.`factor`
    else null end AS `V`,
#    `CtL`.`factor` AS `V`,
    NULL AS `W`,
    NULL AS `X`,
    case when `Ms`.member_status <> 'O' then /* show if not out of state */
	    `CtI`.`factor`
    else null end AS `Y`,
#   `CtI`.`factor` AS `Y`,
    case when `Ms`.member_status <> 'O' then /* show if not out of state */
	    0.25
    else null end AS `Z`,
#    0.25 AS `Z`,
    NULL AS `AA`,
    NULL AS `AB`,
    case when `Ms`.member_status <> 'O' then /* show if not out of state */
	    `FSL`.`amt`
    else null end AS `AC`,
#    `FSL`.`amt` AS `AC`,
    NULL AS `AD`,
    NULL AS `AE`,
    case when `Ms`.member_status <> 'O' then /* show if not out of state */
	    `FSI`.`amt`
    else null end AS `AF`,
#    `FSI`.`amt` AS `AF`,
    case when `Ms`.member_status <> 'O' then /* show if not out of state */
 	   (0.25 * `FSH`.`amt`)
    else null end AS `AG`,
#    (0.25 * `FSH`.`amt`) AS `AG`,
    NULL AS `AH`,
    NULL AS `AI`,
    `FS5`.`amt` AS `AJ`,
    NULL AS `AK`,
    `FSA`.`amt` AS `AL`,
    NULL AS `AM`,
    NULL AS `AN`,
    NULL AS `AO`,
    NULL AS `AP`,
    (case
        when (`CtP`.`operand` = 'H') then concat(floor((`CtP`.`factor` * 100)), '%')
        else `CtP`.`factor`
    end) AS `AQ`,
    NULL AS `AR`,
    `APF`.`fee` AS `AS`,
    NULL AS `AT`,
    NULL AS `AU`,
    NULL AS `AV`,
    NULL AS `AW`,
    NULL AS `AX`
from
    (((((((((((`IntLineItems` `ILI`
left join `CurrentMemberClasses` `MC` on
    ((`MC`.`member_id` = `ILI`.`member_id`)))
left join `FeeSumByMonth` `FSH` on
    (((`FSH`.`acct_month` = `ILI`.`acct_month`)
        and (`FSH`.`member_id` = `ILI`.`member_id`)
            and (`FSH`.`license_nbr` = `ILI`.`license_nbr`)
                and (`FSH`.`fee_type` = 'HR'))))
left join `FeeSumByMonth` `FSL` on
    (((`FSL`.`acct_month` = `ILI`.`acct_month`)
        and (`FSL`.`member_id` = `ILI`.`member_id`)
            and (`FSL`.`license_nbr` = `ILI`.`license_nbr`)
                and (`FSL`.`fee_type` = 'LM'))))
left join `FeeSumByMonth` `FSI` on
    (((`FSI`.`acct_month` = `ILI`.`acct_month`)
        and (`FSI`.`member_id` = `ILI`.`member_id`)
            and (`FSI`.`license_nbr` = `ILI`.`license_nbr`)
                and (`FSI`.`fee_type` = 'IU'))))
left join `FeeSumByMonth` `FS5` on
    (((`FS5`.`acct_month` = `ILI`.`acct_month`)
        and (`FS5`.`member_id` = `ILI`.`member_id`)
            and (`FS5`.`license_nbr` = `ILI`.`license_nbr`)
                and (`FS5`.`fee_type` = 'PC'))))
left join `FeeSumByMonth` `FSA` on
    (((`FSA`.`acct_month` = `ILI`.`acct_month`)
        and (`FSA`.`member_id` = `ILI`.`member_id`)
            and (`FSA`.`license_nbr` = `ILI`.`license_nbr`)
                and (`FSA`.`fee_type` = 'IN'))))
join `Members` `Me` on
    ((`Me`.`member_id` = `ILI`.`member_id`)))
left join `MemberStatuses` `Ms` on (`Ms`.`member_id` = `ILI`.`member_id` and `Ms`.`end_dt` is NULL) 
left join `Contributions` `CtL` on
    (((`CtL`.`lob_cd` = `ILI`.`lob_cd`)
        and (`CtL`.`contrib_type` = 'LM'))))
left join `Contributions` `CtI` on
    (((`CtI`.`lob_cd` = `ILI`.`lob_cd`)
        and (`CtI`.`contrib_type` = 'IU'))))
left join `Contributions` `CtP` on
    (((`CtP`.`lob_cd` = `ILI`.`lob_cd`)
        and (`CtP`.`contrib_type` = 'PC')
            and ((`CtP`.`wage_pct` = 0)
                or (`CtP`.`wage_pct` = `MC`.`wage_percent`)))))
left join `InitFees` `APF` on
    (((`APF`.`lob_cd` = `ILI`.`lob_cd`)
        and (`APF`.`member_class` = `MC`.`member_class`)
            and (`APF`.`end_dt` is null))));
