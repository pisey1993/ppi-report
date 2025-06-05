<?php

namespace App\Models;

use CodeIgniter\Model;
use Config\Database;

class PlacementModel extends Model
{
    public function getMotorPlacementData(string $from, string $to): array
    {
        $db = Database::connect();

        $sql = "
            SELECT
                qp.quote_no,
                qp.policy_no,
                qp.loan_id,
                c.telephone,
                c.insured_name,
                c.address,
                l.scope_of_cover,
                GROUP_CONCAT(DISTINCT i.beneficiary) AS beneficiary,
                GROUP_CONCAT(DISTINCT i.plate_number) AS plate_number,
                MIN(i.insurance_period_from) AS insurance_period_from,
                MAX(i.insurance_period_to) AS insurance_period_to,
                qp.sum_insured,
                qp.premium_before_discount,
                qp.total_premium,
                l.no_claim_discount_rate,
                l.no_claim_discount_premium,
                qp.discount_rate,
                cs.total_paid AS ClaimsPaid,
                cs.total_outstanding AS OutStandingClaims,
                cs.total_claims AS TotalClaims,
                c.branch,
                c.handler_code,
                c.member_of,
                GROUP_CONCAT(DISTINCT i.mark_model) AS mark_model,
                GROUP_CONCAT(DISTINCT i.cubic_capacity) AS cubic_capacity,
                GROUP_CONCAT(DISTINCT i.engine_no) AS engine_no,
                GROUP_CONCAT(DISTINCT i.chassis_no) AS chassis_no,
                GROUP_CONCAT(DISTINCT i.seat) AS seat,
                GROUP_CONCAT(DISTINCT i.gross_weight) AS gross_weight,
                '' AS renewalStatus,
                qp.loss_history,
                qp.status,
                MIN(i.date_of_birth) AS date_of_birth,
                MAX(i.age) AS age,
                l.group_discount_rate,
                l.group_discount_premium,
                qp.loan_period_in_month,
                ROUND((((qp.loan_period_in_month * 30) - DATEDIFF(MAX(i.insurance_period_to), MIN(i.insurance_period_from))) / 30), 2) AS LoanInMonthPeriod,
                SUM(i.total_item) AS total_item,
                c.client_no,
                qp.issue_date
            FROM quote_policies qp
            INNER JOIN dncns d ON qp.id = d.quote_policy_id
            INNER JOIN clients c ON qp.client_id = c.id
            INNER JOIN locations l ON qp.id = l.quote_policy_id
            INNER JOIN items i ON l.id = i.location_id
            LEFT JOIN (
                SELECT
                    rac.quote_policy_id,
                    ROUND(SUM(rac.paid), 2) AS total_paid,
                    ROUND(SUM(rac.outstanding_after_paid), 2) AS total_outstanding,
                    ROUND(SUM(COALESCE(rac.paid, 0) + COALESCE(rac.outstanding_after_paid, 0)), 2) AS total_claims
                FROM register_all_claims rac
                GROUP BY rac.quote_policy_id
            ) cs ON cs.quote_policy_id = qp.id
            WHERE qp.issue_date BETWEEN ? AND ?
            GROUP BY qp.id
            ORDER BY qp.issue_date DESC
        ";

        return $db->query($sql, [$from, $to])->getResultArray();
    }

    public function getCHCPlacementData(string $from, string $to): array
    {
        $db = Database::connect();

        $sql = "
    SELECT
        qp.policy_type AS `PolicyType`,
        qp.policy_no AS `PolicyNo`,
        qp.endorsement_th AS `EndorsementNo`,
        qp.dncn_no AS `DebitNoteNo`,

        i.premium_after_discount AS `PremiumAfterDiscount`,
        i.sum_insured AS `SumInsured`,
        SUM(CAST(JSON_UNQUOTE(JSON_EXTRACT(i.additional_premium, '$.ri_premium')) AS DECIMAL(10,2))) AS RI_Premium,
        ris.camre_share AS `CamReShare`,
        ris.camre_si AS `CamReSI`,
        ris.camre_tax AS `CamReTax`,
        ris.camre_com_premium AS `CamRePPICom`,
        ris.camre_net_premium AS `CamReNetPremium`,
        ris.camre_com AS `CamReVolunteerShare`,
        ris.camre_com AS `CamReVolunteerPPICom`,
        ris.camre_net_premium AS `CamReVolunteerNetPremium`,

        ris.treaty_name AS `QuotaTreatyName`,
        ris.treaty_quota_share AS `TreatyQuotaShare`,
        ris.treaty_quota_si AS `TreatyQuotaSI`,
        ris.treaty_quota_com_premium AS `TreatyQuotaGrossPremium`,
        ris.treaty_quota_tax AS `TreatyQuotaTax`,
        ris.treaty_quota_net_premium AS `TreatyQuotaNetPremium`,

        ris.treaty_surplus_share AS `TreatySurplusShare`,
        ris.treaty_surplus_si AS `TreatySurplusSI`,
        ris.treaty_surplus_com_premium AS `TreatySurplusGrossPremium`,
        ris.treaty_surplus_tax AS `TreatySurplusTax`,
        ris.treaty_surplus_net_premium AS `TreatySurplusNetPremium`,

        ris.fac_share AS `FacultativeShare`,
        ris.fac_si AS `FacultativeSI`,
        ris.fac_com_premium AS `FacultativeGrossPremium`,
        ris.fac_tax AS `FacultativeTax`,
        ris.fac_net_premium AS `FacultativeNetPremium`,

        ris.ppi_share AS `PPIShare`,
        ris.ppi_si AS `PPISI`,
        ris.ppi_com_premium AS `PPIGrossPremium`,
        ris.ppi_tax AS `PPITax`,
        ris.ppi_net_premium AS `PPINetPremium`,

        JSON_UNQUOTE(JSON_EXTRACT(l.additional_column, '$.room_board_90days_price')) AS `RoomAndBoard`,
        JSON_UNQUOTE(JSON_EXTRACT(l.additional_column, '$.intensive_care_unit_25day_sprice')) AS `ICU`,
        JSON_UNQUOTE(JSON_EXTRACT(l.additional_column, '$.hospital_supplies_service_price')) AS `HospitalSupplies`,
        JSON_UNQUOTE(JSON_EXTRACT(l.additional_column, '$.surgical_fee')) AS `SurgicalFee`,
        JSON_UNQUOTE(JSON_EXTRACT(l.additional_column, '$.in_hospital')) AS `InHospitalDoctorFee`,
        JSON_UNQUOTE(JSON_EXTRACT(l.additional_column, '$.pre_hospital')) AS `PreHospitalization`,
        JSON_UNQUOTE(JSON_EXTRACT(l.additional_column, '$.post_hospitalization')) AS `PostHospitalization`,
        JSON_UNQUOTE(JSON_EXTRACT(l.additional_column, '$.supplemental_accident_expenses')) AS `SupplementalAccidentExpenses`,
        JSON_UNQUOTE(JSON_EXTRACT(l.additional_column, '$.government_hospital_daily_cash_allowance')) AS `GovtHospitalDailyCash`,
        JSON_UNQUOTE(JSON_EXTRACT(l.additional_column, '$.funeral_burial_cremation')) AS `FuneralBurialCremation`,
        JSON_UNQUOTE(JSON_EXTRACT(l.additional_column, '$.allowance_food_meal')) AS `FoodMealAllowance`,
        JSON_UNQUOTE(JSON_EXTRACT(l.additional_column, '$.ambulance_fee')) AS `AmbulanceFee`,

        qp.prepared_by AS `PreparedBy`,
        qp.create_by AS `CreatedBy`,

        cs.total_paid AS `ClaimsPaid`,
        cs.total_outstanding AS `OutStandingClaims`,
        cs.total_claims AS `TotalClaims`

    FROM items i
    LEFT JOIN quote_policies qp ON i.quote_policy_id = qp.id
    LEFT JOIN re_insurance_shares ris ON qp.policy_no = ris.policy_no -- Changed join condition
    LEFT JOIN locations l ON i.location_id = l.id

    LEFT JOIN (
        SELECT
            rac.quote_policy_id,
            ROUND(SUM(rac.paid), 2) AS total_paid,
            ROUND(SUM(rac.outstanding_after_paid), 2) AS total_outstanding,
            ROUND(SUM(COALESCE(rac.paid, 0) + COALESCE(rac.outstanding_after_paid, 0)), 2) AS total_claims
        FROM register_all_claims rac
        GROUP BY rac.quote_policy_id
    ) cs ON cs.quote_policy_id = qp.id

    WHERE qp.policy_type = 'Healthcare'
      AND qp.issue_date BETWEEN '2024-11-01' AND '2024-12-31' -- Fixed date range as per new query
    GROUP BY
        qp.id, i.id, ris.id, l.id, i.additional_premium -- Added i.additional_premium to GROUP BY
    ORDER BY qp.policy_no;
    ";

        return $db->query($sql, [$from, $to])->getResultArray();
    }

    public function getPAPlacementData(string $from, string $to): array
    {
        $db = Database::connect();

        $sql = "
    SELECT
        qp.policy_type AS `Quotation::PolicyType`,
        qp.policy_no AS `Certificate::PolicyNo`,
        qp.loan_id AS `Quotation::LoanNo`,
        qp.endorsement_th AS `EndorsementNo`,
        qp.dncn_no AS `DebitNotePremium 3::DebitNoteNo`,
        qp.insurance_period_from AS `Quotation::InsurancePeriodFrom`,
        qp.insurance_period_to AS `Quotation::InsurancePeriodTo`,
        qp.insurance_period_from AS `EffectiveDate`,
        items.endorsement_type AS `EndorsementType`,
        clients.branch AS `Quotation::IndividualGroup`,
        clients.insured_name AS `Client::InsuredName`,
        clients.member_of AS `Client::Memberof`,
        clients.branch AS `Client::Branch`,
        clients.address AS `Client::AddressForView`,
        items.item_no AS `ItemNo`,
        items.brand AS `Name`,
        items.occupancy AS `Gender`,
        items.item_type AS `Job`,
        items.occupancy AS `WorkClassification`,
        qp.issue_date AS `IssuedDate`,
        qp.issue_date AS `IssuedDateForSearch`,
        items.sum_insured AS `SI_DeathPermanentDisablement`,
        NULL AS `SI_AccidentalMedicalExpenses`,
        items.sum_insured AS `TotalSI`,
        items.premium_after_discount AS `PremiumAfterDiscount`,
        re_insurance_shares.camre_share AS `InsuranceSecurity 2::CamReShare`,
        re_insurance_shares.camre_com AS `CamReSI`,
        re_insurance_shares.camre_max_si AS `CamReGrossPremium`,
        re_insurance_shares.camre_tax AS `CamReTax`,
        re_insurance_shares.ppi_share AS `InsuranceSecurity 2::CamRePPICom`,
        re_insurance_shares.ppi_com AS `CamRePPICom`,
        re_insurance_shares.ppi_net_premium AS `CamReNetPremium`,
        re_insurance_shares.treaty_name AS `InsuranceSecurity 2::QuotaTreatyName`,
        re_insurance_shares.treaty_quota_share AS `InsuranceSecurity 2::TreatyQuotaShare`,
        re_insurance_shares.treaty_quota_max_si AS `QuotaSI`,
        re_insurance_shares.treaty_quota_com AS `QuotaGrossPremium`,
        re_insurance_shares.treaty_quota_tax AS `QuotaTax`,
        re_insurance_shares.treaty_quota_net_premium AS `QuotaNetPremium`,
        re_insurance_shares.treaty_quota_com_premium AS `InsuranceSecurity 2::TreatyPPICom`,
        re_insurance_shares.treaty_quota_net_premium AS `QuotaPPICom`,
        re_insurance_shares.treaty_surplus_share AS `InsuranceSecurity 2::TreatySurplusShare`,
        re_insurance_shares.treaty_surplus_si AS `PWSSurplusSI`,
        re_insurance_shares.treaty_surplus_com_premium AS `PWSSurplusGrossPremium`,
        re_insurance_shares.treaty_surplus_tax AS `PWSSurplusTax`,
        re_insurance_shares.treaty_surplus_net_premium AS `PWSSurplusNetPremium`,
        re_insurance_shares.treaty_surplus_com AS `PWSSurplusPPICom`,
        re_insurance_shares.ppi_share AS `InsuranceSecurity 2::PPIShare`,
        re_insurance_shares.ppi_si AS `PPISI`,
        re_insurance_shares.ppi_com_premium AS `PPIGrossPremium`,
        re_insurance_shares.ppi_tax AS `PPITax`,
        re_insurance_shares.ppi_net_premium AS `PPINetPremium`,
        re_insurance_shares.fac_share AS `InsuranceSecurity 2::FacultativeShare`,
        re_insurance_shares.fac_si AS `FacultativeSI`,
        re_insurance_shares.fac_com_premium AS `FacultativeGrossPremium`,
        re_insurance_shares.fac_tax AS `FacultativeTax`,
        re_insurance_shares.fac_com AS `InsuranceSecurity 2::FacultativePPICom`,
        re_insurance_shares.fac_net_premium AS `FacultativePPICom`,
        re_insurance_shares.fac_net_premium AS `FacultativeNetPremium`,
        qp.handler_code AS `Quotation::HandlerCodeForPreview`,
        qp.prepared_by AS `Quotation::PreparedBy`,
        qp.checked_by AS `Quotation::CheckedBy`,
        NULL AS `Quotation::CheckBy2`,
        NULL AS `Quotation::CheckBy3`,
        qp.create_by AS `Quotation::Created By`,
        NULL AS `Certificate::Prepared By`,
        NULL AS `Certificate::Checked By`,
        NULL AS `Certificate::CheckedBy2`,
        NULL AS `Certificate::CheckedBy3`,
        NULL AS `Certificate::CreatedBy`,
        SUM(register_all_claims.total_paid_for_this_claims) AS `ClaimsPaid`,
        SUM(register_all_claims.outstanding_after_paid) AS `ClaimsOutstanding`
    FROM items
    LEFT JOIN quote_policies AS qp ON items.quote_policy_id = qp.id
    LEFT JOIN policy_types ON policy_types.id = qp.policy_type_id
    LEFT JOIN clients ON qp.client_id = clients.id
    LEFT JOIN locations ON items.location_id = locations.id
    LEFT JOIN coverages ON items.location_id = coverages.location_id
    LEFT JOIN re_insurance_shares ON qp.policy_no = re_insurance_shares.policy_no
    LEFT JOIN register_all_claims ON qp.id = register_all_claims.quote_policy_id
    WHERE qp.policy_type = 'Personal Accident' AND qp.issue_date BETWEEN '$from' AND '$to'
    GROUP BY
        qp.policy_type,
        qp.policy_no,
        qp.loan_id,
        qp.endorsement_th,
        qp.dncn_no,
        qp.insurance_period_from,
        qp.insurance_period_to,
        items.endorsement_type,
        clients.branch,
        clients.insured_name,
        clients.member_of,
        clients.address,
        items.item_no,
        items.brand,
        items.occupancy,
        items.item_type,
        qp.issue_date,
        items.sum_insured,
        items.premium_after_discount,
        re_insurance_shares.camre_share,
        re_insurance_shares.camre_com,
        re_insurance_shares.camre_max_si,
        re_insurance_shares.camre_tax,
        re_insurance_shares.ppi_share,
        re_insurance_shares.ppi_com,
        re_insurance_shares.ppi_net_premium,
        re_insurance_shares.treaty_name,
        re_insurance_shares.treaty_quota_share,
        re_insurance_shares.treaty_quota_max_si,
        re_insurance_shares.treaty_quota_com,
        re_insurance_shares.treaty_quota_tax,
        re_insurance_shares.treaty_quota_net_premium,
        re_insurance_shares.treaty_quota_com_premium,
        re_insurance_shares.treaty_surplus_share,
        re_insurance_shares.treaty_surplus_si,
        re_insurance_shares.treaty_surplus_com_premium,
        re_insurance_shares.treaty_surplus_tax,
        re_insurance_shares.treaty_surplus_net_premium,
        re_insurance_shares.treaty_surplus_com,
        re_insurance_shares.fac_share,
        re_insurance_shares.fac_si,
        re_insurance_shares.fac_com_premium,
        re_insurance_shares.fac_tax,
        re_insurance_shares.fac_com,
        qp.handler_code,
        qp.prepared_by,
        qp.checked_by,
        qp.create_by
    ORDER BY qp.policy_no;

    ";

        return $db->query($sql, [$from, $to])->getResultArray();
    }

    public function getTravelPlacementData(string $from, string $to): array
    {
        $db = Database::connect();

        $sql = "
    SELECT
    quote_policies.policy_type AS PolicyType,
    quote_policies.endorsement_th AS EndorsementNo,
    quote_policies.insurance_period_from AS InsurancePeriodFrom,
    quote_policies.insurance_period_to AS InsurancePeriodTo,
    quote_policies.issue_date AS EffectiveDate,
    quote_policies.endorsement_insurance_case AS EndorsementType,

    clients.insured_name AS InsuredName,
    clients.address AS AddressForView,

    items.beneficiary AS Name,
    items.date_of_birth AS DOB,
    items.passport_no AS PassportNo,
    items.full_address AS Address,

    quote_policies.plan_selected AS PlanSelected,
    quote_policies.territorial_limit AS CountryVisitedRegion,

    items.sum_insured AS SIForPlacement,
    items.premium_after_discount AS PremiumAfterDiscount,

    quote_policies.issue_date AS IssuedDate,
    DATE_FORMAT(quote_policies.issue_date, '%Y-%m-%d') AS IssuedDateForSearch,

    re_insurance_shares.camre_si AS CamReSI,
    re_insurance_shares.year_share AS UnderwritingYear,
    re_insurance_shares.camre_share AS CamReShare,
    re_insurance_shares.camre_sub_premium AS CamReGrossPremium,
    re_insurance_shares.camre_tax AS CamReTax,
    re_insurance_shares.camre_com AS InsuranceSecurityCamRePPICom,
    re_insurance_shares.camre_net_premium AS CamReNetPremium,

    re_insurance_shares.treaty_name AS QuotaTreatyName,
    re_insurance_shares.treaty_quota_share AS TreatyQuotaShare,
    re_insurance_shares.treaty_quota_si AS QuotaSI,
    re_insurance_shares.treaty_quota_sub_premium AS QuotaGrossPremium,
    re_insurance_shares.treaty_quota_tax AS QuotaTax,
    re_insurance_shares.treaty_quota_com AS TreatyPPICom,
    re_insurance_shares.treaty_quota_net_premium AS QuotaNetPremium,

    re_insurance_shares.ppi_share AS PPIShare,
    re_insurance_shares.ppi_si AS PPISI,
    re_insurance_shares.ppi_sub_premium AS PPIGrossPremium,
    re_insurance_shares.ppi_tax AS PPITax,
    re_insurance_shares.ppi_net_premium AS PPINetPremium,

    quote_policies.prepared_by AS PreparedBy,
    quote_policies.checked_by AS CheckedBy,
    quote_policies.checked_by_list AS CheckedBy2,
    quote_policies.approved_by AS CheckedBy3,

    quote_policies.loss_history AS ClaimsPaid,
    quote_policies.no_claim_discount_premium AS OutStandingClaims,
    quote_policies.total_premium AS TotalClaims

FROM items
LEFT JOIN quote_policies ON items.quote_policy_id = quote_policies.id
LEFT JOIN clients ON quote_policies.client_id = clients.id
LEFT JOIN re_insurance_shares ON quote_policies.policy_no = re_insurance_shares.policy_no
LEFT JOIN (
    SELECT
        rac.quote_policy_id,
        ROUND(SUM(rac.paid), 2) AS total_paid,
        ROUND(SUM(rac.outstanding_after_paid), 2) AS total_outstanding,
        ROUND(SUM(COALESCE(rac.paid, 0) + COALESCE(rac.outstanding_after_paid, 0)), 2) AS total_claims
    FROM register_all_claims rac
    GROUP BY rac.quote_policy_id
) cs ON cs.quote_policy_id = quote_policies.id

WHERE quote_policies.policy_type = 'Travel'
  AND quote_policies.issue_date BETWEEN '$from' AND '$to';

    ";

        return $db->query($sql, [$from, $to])->getResultArray();
    }

    public function getFIRPlacementData(string $from, string $to): array
    {
        $db = Database::connect();

        $builder = $db->query("
        SELECT DISTINCT
            qp.policy_type AS `Quotation::PolicyType`,
            qp.policy_no AS `PolicyNo`,
            DATE_FORMAT(qp.insurance_period_from, '%Y-%m-%d') AS `Quotation::InsurancePeriodFrom`,
            DATE_FORMAT(qp.insurance_period_to, '%Y-%m-%d') AS `Quotation::InsurancePeriodTo`,
            DATE_FORMAT(qp.issue_date, '%Y-%m-%d') AS `EffectiveDate`,
            i.endorsement_type AS `EndorsementType`,

            qp.insured_name AS `Client::InsuredName`,
            qp.address AS `Client::AddressForView`,

            qp.occupation_of_risk AS `Policy_FIR_SI_Location_Placement::Occupation`,
            l.occupation AS `Policy_FIR_SI_Location_Placement::OccupationNote`,
            l.location_name AS `Policy_FIR_SI_Location_Placement::LocationName`,
            l.location_number AS `LocationNumber`,
            l.full_address AS `Policy_FIR_SI_Location_Placement::FullAddress`,
            l.commune_code AS `Policy_FIR_SI_Location_Placement::CommnuneCode`,
            l.district_code AS `Policy_FIR_SI_Location_Placement::DistricCode`,
            l.postal_code AS `CityDistrictCommunePlacement::PostalCode`,

            i.liability_of_risk AS `Interest Insured`,

            l.class AS `Policy_FIR_SI_Location_Placement::Class`,
            l.code AS `Policy_FIR_SI_Location_Placement::Code`,
            l.rate AS `Policy_FIR_SI_Location_Placement::Rate`,
            l.hazard AS `Policy_FIR_SI_Location_Placement::Hazard`,

            l.deductible AS `Quote_FIR::DeductibleList`,
            l.deductible_rate AS `Quote_FIR::DeductibleRate`,
            qp.discount AS `Quote_FIR::Discount`,
            qp.discount_rate AS `Quote_FIR::DiscountRate`,

            i.item_no AS `ItemNumber`,
            i.additional_column AS `Quote_FIR::AdditionalPerilCode`,
            i.additional_column AS `Quote_FIR::AdditionalPeril`, -- you can split these if separate fields exist

            l.additional_premium AS `Quotation::LimitOfFlood`,

            ris.year_share AS `InsuranceSecurity 8::UnderwritingYear`,

            DATE_FORMAT(qp.issue_date, '%Y-%m-%d') AS `IssuedDate`,
            DATE_FORMAT(qp.issue_date, '%Y-%m-%d') AS `IssuedDateForSearch`,

            i.sum_insured AS `SIForPlacement`,
            i.premium_after_discount AS `PremiumLast`,

            ris.camre_share AS `InsuranceSecurity 8::CamReShare`,
            ris.camre_si AS `CamReSI`,
            ris.camre_sub_premium AS `CamReGrossPremium`,
            ris.camre_tax AS `CamReTax`,
            ris.camre_com AS `InsuranceSecurity 8::CamRePPICom`,
            ris.camre_com AS `CamRePPICom`,
            ris.camre_net_premium AS `CamReNetPremium`,

            ris.treaty_name AS `InsuranceSecurity 8::QuotaTreatyName`,
            ris.treaty_quota_share AS `InsuranceSecurity 8::TreatyQuotaShare`,
            ris.treaty_quota_si AS `TreatyQuotaSI`,
            ris.treaty_quota_sub_premium AS `TreatyQuotaGrossPremium`,
            ris.treaty_quota_tax AS `TreatyQuotaTax`,
            ris.treaty_quota_com AS `InsuranceSecurity 8::TreatyPPICom`,
            ris.treaty_quota_com AS `TreatyQuotaPPICom`,
            ris.treaty_quota_net_premium AS `TreatyQuotaNetPremium`,

            ris.treaty_surplus_share AS `InsuranceSecurity 8::TreatySurplusShare`,
            ris.treaty_surplus_si AS `TreatySurplusSI`,
            ris.treaty_surplus_sub_premium AS `TreatySurplusGrossPremium`,
            ris.treaty_surplus_tax AS `TreatySurplusTax`,
            ris.treaty_surplus_com AS `InsuranceSecurity 8::TreatySurplusPPICom`,
            ris.treaty_surplus_net_premium AS `TreatySurplusNetPremium`,

            ris.fac_share AS `InsuranceSecurity 8::FacultativeShare`,
            ris.fac_si AS `FacultativeSI`,
            ris.fac_sub_premium AS `FacultativeGrossPremium`,
            ris.fac_tax AS `FacultativeTax`,
            ris.fac_com AS `InsuranceSecurity 8::FacultativePPICom`,
            ris.fac_com AS `FacultativePPICom`,
            ris.fac_net_premium AS `FacultativeNetPremium`,

            ris.ppi_share AS `InsuranceSecurity 8::PPIShare`,
            ris.ppi_si AS `PPISI`,
            ris.ppi_sub_premium AS `PPIGrossPremium`,
            ris.ppi_tax AS `PPITax`,
            ris.ppi_net_premium AS `PPINetPremium`,

            qp.prepared_by AS `Quotation::PreparedBy`,
            qp.checked_by AS `Quotation::CheckedBy`,
            '' AS `Quotation::CheckBy2`,
            '' AS `Quotation::CheckBy3`,
            '' AS `Certificate::Prepared By`,
            '' AS `Certificate::Checked By`,
            '' AS `Certificate::CheckedBy2`,
            '' AS `Certificate::CheckedBy3`,

            qp.member_of AS `Quotation::MemberOf`,

            cs.total_paid AS `TotalPaid`,
            cs.total_outstanding AS `TotalOutstanding`,
            cs.total_claims AS `TotalClaims`

        FROM locations l
        LEFT JOIN items i ON l.id = i.location_id
        LEFT JOIN quote_policies qp ON i.quote_policy_id = qp.id
        LEFT JOIN re_insurance_shares ris ON qp.policy_no = ris.policy_no
        LEFT JOIN (
            SELECT
                rac.quote_policy_id,
                ROUND(SUM(rac.paid), 2) AS total_paid,
                ROUND(SUM(rac.outstanding_after_paid), 2) AS total_outstanding,
                ROUND(SUM(COALESCE(rac.paid, 0) + COALESCE(rac.outstanding_after_paid, 0)), 2) AS total_claims
            FROM register_all_claims rac
            GROUP BY rac.quote_policy_id
        ) cs ON cs.quote_policy_id = qp.id

        WHERE qp.policy_type_id = 25
          AND qp.policy_no <> ''
          AND qp.issue_date BETWEEN ? AND ?
    ", [$from, $to]);

        return $builder->getResultArray();
    }

    public function getCARPlacementData(string $from, string $to): array
    {
        $db = Database::connect();

        $sql = "
    SELECT
        qp.policy_type AS PolicyType,
        qp.endorsement_th AS EndorsementNo,
        qp.insurance_period_from AS InsurancePeriodFrom,
        qp.insurance_period_to AS InsurancePeriodTo,
        qp.issue_date AS EffectiveDate,
        qp.endorsement_insurance_case AS EndorsementType,

        clients.insured_name AS InsuredName,
        locations.principal AS Principal,
        clients.address AS AddressForView,
        locations.contractor_description AS ContractorDescription,
        locations.project_name AS ProjectName,
        locations.location_of_risk AS LocationOfRisk,
        locations.maintenance_period AS MaintenancePeriod,
        locations.maintenance_period_from AS MaintenancePeriodFrom,
        locations.maintenance_period_to AS MaintenancePeriodTo,

        qp.issue_date AS IssuedDate,
        DATE_FORMAT(qp.issue_date, '%Y-%m-%d') AS IssuedDateForSearch,

        locations.total_sum_insured AS SIForPlacement,
        locations.premium_after_discount AS PremiumAfterDiscount,

        ris.year_share AS UnderwritingYear,
        ris.camre_share AS CamReShare,
        ris.camre_max_si AS CamReMaxSI,
        ris.treaty_name AS TreatyName,
        ris.treaty_quota_share AS TreatyQuotaShare,
        ris.treaty_quota_max_si AS TreatyQuotaMaxSI,
        ris.ppi_share AS PPIShare,
        ris.ppi_max_si AS PPIMaxSI,
        ris.treaty_surplus_max_si AS TreatySurplusMaxSI,

        ris.camre_com AS CamReCom,
        ris.treaty_quota_com AS TreatyQuotaCom,
        ris.treaty_surplus_com AS TreatySurplusCom,
        ris.fac_com AS FacCom,
        ris.camre_tax AS CamReTax,
        ris.treaty_quota_tax AS TreatyQuotaTax,
        ris.treaty_surplus_tax AS TreatySurplusTax,
        ris.ppi_tax AS PPITax,
        ris.fac_tax AS FacTax,

        ris.camre_sub_premium AS CamReSubPremium,
        ris.treaty_quota_sub_premium AS TreatyQuotaSubPremium,
        ris.treaty_surplus_sub_premium AS TreatySurplusSubPremium,
        ris.ppi_sub_premium AS PPISubPremium,
        ris.fac_sub_premium AS FacSubPremium,

        ris.camre_com_premium AS CamReComPremium,
        ris.treaty_quota_com_premium AS TreatyQuotaComPremium,
        ris.treaty_surplus_com_premium AS TreatySurplusComPremium,
        ris.ppi_com_premium AS PPIComPremium,
        ris.fac_com_premium AS FacComPremium,

        ris.camre_tax_premium AS CamReTaxPremium,
        ris.treaty_quota_tax_premium AS TreatyQuotaTaxPremium,
        ris.treaty_surplus_tax_premium AS TreatySurplusTaxPremium,
        ris.ppi_tax_premium AS PPITaxPremium,
        ris.fac_tax_premium AS FacTaxPremium,

        ris.camre_net_premium AS CamReNetPremium,
        ris.treaty_quota_net_premium AS TreatyQuotaNetPremium,
        ris.treaty_surplus_net_premium AS TreatySurplusNetPremium,
        ris.ppi_net_premium AS PPINetPremium,
        ris.fac_net_premium AS FacNetPremium,

        ris.fac_share AS FacShare,
        ris.ref_total_si AS RefTotalSI,
        ris.ref_total_premium AS RefTotalPremium,

        ris.camre_si AS CamReSI,
        ris.treaty_quota_si AS TreatyQuotaSI,
        ris.treaty_surplus_si AS TreatySurplusSI,
        ris.ppi_si AS PPISI,
        ris.fac_si AS FacSI,

        ris.treaty_surplus_share AS TreatySurplusShare,
        ris.ppi_com AS PPICom,

        ris.camre_default_share AS CamReDefaultShare,
        ris.treaty_quota_default_share AS TreatyQuotaDefaultShare,
        ris.ppi_default_share AS PPIDefaultShare,

        locations.project_name,
        locations.location_of_risk,
        locations.total_sum_insured,
        locations.premium_after_discount

    FROM quote_policies qp
    LEFT JOIN clients ON qp.client_id = clients.id
    LEFT JOIN locations ON locations.quote_policy_id = qp.id
    LEFT JOIN policy_types ON qp.policy_type_id = policy_types.id
    LEFT JOIN re_insurance_shares ris ON qp.policy_no = ris.policy_no
    LEFT JOIN (
        SELECT
            rac.quote_policy_id,
            ROUND(SUM(rac.paid), 2) AS total_paid,
            ROUND(SUM(rac.outstanding_after_paid), 2) AS total_outstanding,
            ROUND(SUM(COALESCE(rac.paid, 0) + COALESCE(rac.outstanding_after_paid, 0)), 2) AS total_claims
        FROM register_all_claims rac
        GROUP BY rac.quote_policy_id
    ) cs ON cs.quote_policy_id = qp.id

    WHERE policy_types.class = 'Engineering'
      AND qp.issue_date BETWEEN ? AND ?
    ";

        return $db->query($sql, [$from, $to])->getResultArray();
    }


    public function getIHCPlacementData(string $from, string $to): array
    {
        $db = \Config\Database::connect();

        $sql = "
        SELECT
            qp.policy_type AS `Quotation::PolicyType`,
            qp.policy_no AS `Certificate::PolicyNo`,
            qp.endorsement_th AS `EndorsementNo`,
            items.premium_after_discount AS `DebitNotePremium`,
            qp.dncn_no AS `DebitNoteNo`,
            items.premium_after_discount AS `PremiumAfterDiscount`,
            reins.camre_share AS `RIPremium`,
            items.sum_insured AS `InsuranceSecurity 2::CamReShare`,
            reins.camre_share AS `CamReSI`,
            reins.camre_share AS `CamReGrossPremium`,
            reins.camre_tax AS `CamReTax`,
            reins.camre_com_premium AS `InsuranceSecurity 2::CamRePPICom`,
            reins.camre_com_premium AS `CamRePPICom`,
            reins.camre_net_premium AS `CamReNetPremium`,
            reins.camre_com AS `InsuranceSecurity 2::CamReVolunteerShare`,
            reins.camre_si AS `CamReVolunteerSI`,
            reins.camre_com AS `CamReVolunteerGrossPremium`,
            reins.camre_tax AS `CamReVolunteerTax`,
            reins.camre_com AS `InsuranceSecurity 2::CamReVolunteerPPICom`,
            reins.camre_com AS `CamReVolunteerPPICom`,
            reins.camre_net_premium AS `CamReVolunteerNetPremium`,
            reins.treaty_name AS `InsuranceSecurity 2::QuotaTreatyName`,
            reins.treaty_quota_share AS `InsuranceSecurity 2::TreatyPPICom`,
            reins.treaty_quota_share AS `InsuranceSecurity 2::TreatyQuotaShare`,
            reins.treaty_quota_si AS `TreatyQuotaSI`,
            reins.treaty_quota_com_premium AS `TreatyQuotaGrossPremium`,
            reins.treaty_quota_tax AS `TreatyQuotaTax`,
            reins.treaty_quota_com_premium AS `TreatyQuotaPPICom`,
            reins.treaty_quota_net_premium AS `TreatyQuotaNetPremium`,
            reins.treaty_surplus_share AS `InsuranceSecurity 2::TreatySurplusShare`,
            reins.treaty_surplus_si AS `TreatySurplusSI`,
            reins.treaty_surplus_com_premium AS `TreatySurplusGrossPremium`,
            reins.treaty_surplus_tax AS `TreatySurplusTax`,
            reins.treaty_surplus_com_premium AS `TreatySurplusPPICom`,
            reins.treaty_surplus_net_premium AS `TreatySurplusNetPremium`,
            reins.fac_share AS `InsuranceSecurity 2::FacultativeShare`,
            reins.fac_si AS `FacultativeSI`,
            reins.fac_com_premium AS `FacultativeGrossPremium`,
            reins.fac_tax AS `FacultativeTax`,
            reins.fac_com_premium AS `InsuranceSecurity 2::FacultativePPICom`,
            reins.fac_com_premium AS `FacultativePPICom`,
            reins.fac_net_premium AS `FacultativeNetPremium`,
            reins.ppi_share AS `InsuranceSecurity 2::PPIShare`,
            reins.ppi_si AS `PPISI`,
            reins.ppi_com_premium AS `PPIGrossPremium`,
            reins.ppi_tax AS `PPITax`,
            reins.ppi_net_premium AS `PPINetPremium`,
            items.additional_column AS `Quote_Healthcare::OutpatientLimit`,
            '' AS `Quote_Healthcare::MedicalCheckup`,
            '' AS `Quote_Healthcare::Vaccination`,
            '' AS `Quote_Healthcare::OpticalBenefit`,
            '' AS `Quote_Healthcare::DentalCare`,
            '' AS `Quote_Healthcare::Maternity`,
            '' AS `Quote_Healthcare::ComplicationsOfPregnancy`,
            '' AS `Quote_Healthcare::AccidentalDamageToNaturalTeeth`,
            qp.prepared_by AS `Quotation::PreparedBy`,
            qp.checked_by AS `Quotation::CheckedBy`,
            '' AS `Quotation::CheckBy2`,
            '' AS `Quotation::CheckBy3`,
            qp.create_by AS `Quotation::Created By`,
            qp.prepared_by AS `Certificate::Prepared By`,
            qp.checked_by AS `Certificate::Checked By`,
            '' AS `Certificate::CheckedBy2`,
            '' AS `Certificate::CheckedBy3`,
            qp.create_by AS `Certificate::CreatedBy`,
            cs.total_paid,
            cs.total_outstanding,
            cs.total_claims
        FROM quote_policies qp
        LEFT JOIN items ON items.quote_policy_id = qp.id
        LEFT JOIN re_insurance_shares reins ON items.id = reins.ref_id
        LEFT JOIN (
            SELECT
                quote_policy_id,
                ROUND(SUM(paid), 2) AS total_paid,
                ROUND(SUM(outstanding_after_paid), 2) AS total_outstanding,
                ROUND(SUM(COALESCE(paid, 0) + COALESCE(outstanding_after_paid, 0)), 2) AS total_claims
            FROM register_all_claims
            GROUP BY quote_policy_id
        ) cs ON cs.quote_policy_id = qp.id
        LEFT JOIN clients c ON qp.client_id = c.id
        WHERE qp.policy_type = 'International Healthcare'
            AND qp.policy_no <> ''
            AND qp.insurance_period_to BETWEEN ? AND ?
            AND c.member_of LIKE '%'
            AND qp.status LIKE '%'
    ";

        return $db->query($sql, [$from, $to])->getResultArray();
    }


    public function getPARPlacementData(string $from, string $to): array
    {
        $db = Database::connect();

        $sql = "
    SELECT
        qp.policy_type AS `Quotation::PolicyType`,
        qp.policy_no AS `PolicyNo`,
        DATE_FORMAT(qp.insurance_period_from, '%Y-%m-%d') AS `InsurancePeriodFrom`,
        DATE_FORMAT(qp.insurance_period_to, '%Y-%m-%d') AS `InsurancePeriodTo`,
        DATE_FORMAT(qp.issue_date, '%Y-%m-%d') AS `EffectiveDate`,
        i.endorsement_type AS `EndorsementType`,

        qp.insured_name AS `Client::InsuredName`,
        qp.address AS `Client::AddressForView`,

        qp.occupation_of_risk AS `Occupation`,
        l.occupation AS `OccupationNote`,
        l.location_name AS `LocationName`,
        l.location_number AS `LocationNumber`,
        l.full_address AS `FullAddress`,
        l.commune_code AS `CommnuneCode`,
        l.district_code AS `DistricCode`,
        l.postal_code AS `PostalCode`,

        i.liability_of_risk AS `Interest Insured`,

        l.class AS `Class`,
        l.code AS `Code`,
        l.rate AS `Rate`,
        l.hazard AS `Hazard`,

        l.deductible AS `DeductibleList`,
        l.deductible_rate AS `DeductibleRate`,
        qp.discount AS `Discount`,
        qp.discount_rate AS `DiscountRate`,

        i.item_no AS `ItemNumber`,
        i.additional_column AS `AdditionalPerilCode`,

        l.additional_premium AS `LimitOfFlood`,

        ris.year_share AS `UnderwritingYear`,

        DATE_FORMAT(qp.issue_date, '%Y-%m-%d') AS `IssuedDate`,
        DATE_FORMAT(qp.issue_date, '%Y-%m-%d') AS `IssuedDateForSearch`,

        i.sum_insured AS `SIForPlacement`,
        i.premium_after_discount AS `PremiumLast`,

        -- CamRe
        ris.camre_share AS `CamReShare`,
        ris.camre_si AS `CamReSI`,
        ris.camre_sub_premium AS `CamReGrossPremium`,
        ris.camre_tax AS `CamReTax`,
        ris.camre_com AS `CamRePPICom`,
        ris.camre_net_premium AS `CamReNetPremium`,

        -- HannoverRe
        ris.treaty_quota_share AS `HannoverReShare`,
        ris.treaty_quota_si AS `HannoverReSI`,
        ris.treaty_quota_sub_premium AS `HannoverReGrossPremium`,
        ris.treaty_quota_tax AS `HannoverReTax`,
        ris.treaty_quota_com AS `InsuranceSecurity 2::HannoverRePPICom`,
        ris.treaty_quota_com AS `HannoverRePPICom`,
        ris.treaty_quota_net_premium AS `HannoverReNetPremium`,

        -- Facultative
        ris.fac_share AS `FacultativeShare`,
        ris.fac_si AS `FacultativeSI`,
        ris.fac_sub_premium AS `FacultativeGrossPremium`,
        ris.fac_tax AS `FacultativeTax`,
        ris.fac_com AS `FacultativePPICom`,
        ris.fac_net_premium AS `FacultativeNetPremium`,

        -- PPI
        ris.ppi_share AS `PPIShare`,
        ris.ppi_si AS `PPISI`,
        ris.ppi_sub_premium AS `PPIGrossPremium`,
        ris.ppi_tax AS `PPITax`,
        ris.ppi_net_premium AS `PPINetPremium`,

        qp.prepared_by AS `Quotation::PreparedBy`,
        qp.checked_by AS `Quotation::CheckedBy`,
        '' AS `Quotation::CheckBy2`,
        '' AS `Quotation::CheckBy3`,
        '' AS `Certificate::Prepared By`,
        '' AS `Certificate::Checked By`,
        '' AS `Certificate::CheckedBy2`,
        '' AS `Certificate::CheckedBy3`,

        qp.member_of AS `Quotation::MemberOf`,

        -- Claim Summary
        cs.total_paid AS `total_paid`,
        cs.total_outstanding AS `total_outstanding`,
        cs.total_claims AS `total_claims`

    FROM items i
    LEFT JOIN quote_policies qp ON i.quote_policy_id = qp.id
    LEFT JOIN locations l ON i.location_id = l.id
    LEFT JOIN re_insurance_shares ris ON qp.policy_no = ris.policy_no
    LEFT JOIN (
        SELECT
            rac.quote_policy_id,
            ROUND(SUM(rac.paid), 2) AS total_paid,
            ROUND(SUM(rac.outstanding_after_paid), 2) AS total_outstanding,
            ROUND(SUM(COALESCE(rac.paid, 0) + COALESCE(rac.outstanding_after_paid, 0)), 2) AS total_claims
        FROM register_all_claims rac
        GROUP BY rac.quote_policy_id
    ) cs ON cs.quote_policy_id = qp.id

    WHERE qp.policy_type = 'Property All Risk'
      AND qp.policy_no <> ''
      AND qp.issue_date BETWEEN ? AND ?
    ";

        return $db->query($sql, [$from, $to])->getResultArray();
    }


    public function getPIPlacementData(string $from, string $to): array
    {
        $db = Database::connect();

        $sql = "
    SELECT DISTINCT
    qp.policy_type AS `PolicyType`,
    qp.policy_no AS `PolicyNo`,
    qp.endorsement_th AS `EndorsementNo`,
    DATE_FORMAT(qp.insurance_period_from, '%Y-%m-%d') AS `InsurancePeriodFrom`,
    DATE_FORMAT(qp.insurance_period_to, '%Y-%m-%d') AS `InsurancePeriodTo`,
    DATE_FORMAT(qp.issue_date, '%Y-%m-%d') AS `EffectiveDate`,
    i.endorsement_type AS `EndorsementType`,
    c.insured_name AS `InsuredName`,
    c.address AS `AddressForView`,
    JSON_UNQUOTE(JSON_EXTRACT(i.additional_column, '$.occupation')) AS TypeOfRisk,

    (
        SELECT COUNT(DISTINCT l2.id)
        FROM locations l2
        JOIN items i2 ON l2.id = i2.location_id
        WHERE i2.quote_policy_id = i.quote_policy_id
    ) AS `NumberOfLocation`,

    i.item_no AS `LocationNo`,
    JSON_UNQUOTE(JSON_EXTRACT(i.additional_column, '$.location')) AS LocationName,
    JSON_UNQUOTE(JSON_EXTRACT(i.additional_column, '$.occupation')) AS Occupation,
    DATE_FORMAT(l.insurance_period_from, '%Y-%m-%d') AS `RetroactiveDate`,
    l.geographical_limit AS `TerritorialLimit`,
    qp.handler_code AS `HandlerCodeForPreview`,
    qp.note AS `Remark`,
    DATE_FORMAT(qp.issue_date, '%Y-%m-%d') AS `IssuedDate`,
    DATE_FORMAT(qp.issue_date, '%Y-%m-%d') AS `IssuedDateForSearch`,

    ris.year_share AS `UnderwritingYear`,
    i.liability_of_risk AS `LiabilityOfRisk1`,
    NULL AS `LiabilityOfRisk2`,
    NULL AS `LiabilityOfRiskDetail2`,

    i.sum_insured AS `SI`,
    i.premium_after_discount AS `PremiumAfterDiscount`,

    ris.camre_share AS `CamReShare`,
    ris.camre_si AS `CamReSI`,
    ris.camre_sub_premium AS `CamReGrossPremium`,
    ris.camre_tax AS `CamReTax`,
    ris.camre_com AS `CamRePPICom`,
    ris.camre_net_premium AS `CamReNetPremium`,

    ris.treaty_name AS `QuotaTreatyName`,
    ris.treaty_quota_share AS `TreatyQuotaShare`,
    ris.treaty_quota_si AS `TreatyQuotaSI`,
    ris.treaty_quota_sub_premium AS `TreatyQuotaGrossPremium`,
    ris.treaty_quota_tax AS `TreatyQuotaTax`,
    ris.treaty_quota_com AS `TreatyPPICom`,
    ris.treaty_quota_net_premium AS `TreatyQuotaNetPremium`,

    ris.treaty_surplus_share AS `TreatySurplusShare`,
    ris.treaty_surplus_si AS `TreatySurplusSI`,
    ris.treaty_surplus_sub_premium AS `TreatySurplusGrossPremium`,
    ris.treaty_surplus_tax AS `TreatySurplusTax`,
    ris.treaty_surplus_com AS `TreatySurplusPPICom`,
    ris.treaty_surplus_net_premium AS `TreatySurplusNetPremium`,

    ris.fac_share AS `FacultativeShare`,
    ris.fac_si AS `FacultativeSI`,
    ris.fac_sub_premium AS `FacultativeGrossPremium`,
    ris.fac_tax AS `FacultativeTax`,
    ris.fac_com AS `FacultativePPICom`,
    ris.fac_net_premium AS `FacultativeNetPremium`,

    ris.ppi_share AS `PPIShare`,
    ris.ppi_si AS `PPISI`,
    ris.ppi_sub_premium AS `PPIGrossPremium`,
    ris.ppi_tax AS `PPITax`,
    ris.ppi_net_premium AS `PPINetPremium`,

    cs.total_paid,
    cs.total_outstanding,
    cs.total_claims,

    qp.prepared_by AS `PreparedBy`,
    qp.checked_by AS `CheckedBy`,
    '' AS `CheckBy2`,
    '' AS `CheckBy3`,
    '' AS `Certificate::Prepared By`,
    '' AS `Certificate::Checked By`,
    '' AS `Certificate::CheckedBy2`,
    '' AS `Certificate::CheckedBy3`

FROM items AS i
LEFT JOIN locations AS l ON l.id = i.location_id
LEFT JOIN quote_policies AS qp ON i.quote_policy_id = qp.id
LEFT JOIN clients AS c ON qp.client_id = c.id
LEFT JOIN re_insurance_shares ris ON qp.policy_no = ris.policy_no
LEFT JOIN (
    SELECT
        rac.quote_policy_id,
        ROUND(SUM(rac.paid), 2) AS total_paid,
        ROUND(SUM(rac.outstanding_after_paid), 2) AS total_outstanding,
        ROUND(SUM(COALESCE(rac.paid, 0) + COALESCE(rac.outstanding_after_paid, 0)), 2) AS total_claims
    FROM register_all_claims rac
    GROUP BY rac.quote_policy_id
) cs ON cs.quote_policy_id = qp.id

WHERE qp.policy_type_id IN (12)
  AND qp.policy_no <> ''
  AND qp.issue_date BETWEEN '$from' AND '$to';

    ";

        return $db->query($sql, [$from, $to])->getResultArray();
    }

    public function getPLPlacementData(string $from, string $to): array
    {
        $db = Database::connect();

        $sql = "
    SELECT DISTINCT
    quote_policies.policy_type AS `PolicyType`,
    quote_policies.endorsement_th AS EndorsementNo,
    quote_policies.insurance_period_from AS `InsurancePeriodFrom`,
    quote_policies.insurance_period_to AS `InsurancePeriodTo`,
    quote_policies.issue_date AS EffectiveDate,
    quote_policies.endorsement_insurance_case AS EndorsementType,

    clients.insured_name AS `Client::InsuredName`,
    clients.address AS `Client::AddressForView`,

    locations.geographical_limit AS `Quote_PL::Territorial Limit`,
    JSON_UNQUOTE(JSON_EXTRACT(items.additional_column, '$.location')) AS LocationOfRisk,
    JSON_UNQUOTE(JSON_EXTRACT(items.additional_column, '$.occupation')) AS Occupation,
    JSON_UNQUOTE(JSON_EXTRACT(items.additional_si, '$.sum_insured_1')) AS LiabilityOfRisk1,
    JSON_UNQUOTE(JSON_EXTRACT(items.additional_column, '$.remark_1')) AS LiabilityOfRiskDetails1,
    JSON_UNQUOTE(JSON_EXTRACT(items.additional_si, '$.sum_insured_2')) AS LiabilityOfRisk2,
    JSON_UNQUOTE(JSON_EXTRACT(items.additional_column, '$.remark_2')) AS LiabilityOfRiskDetails2,
    locations.premium_after_discount AS PremiumEndorseAfterDiscount,

    quote_policies.issue_date AS IssuedDate,
    DATE_FORMAT(quote_policies.issue_date, '%Y-%m-%d') AS IssuedDateForSearch,

    locations.total_sum_insured AS TotalSI,
    locations.total_sum_insured AS SIForPlacement,
    locations.premium_after_discount AS PremiumForPlacement,

    re_insurance_shares.camre_share AS `CamReShare`,
    re_insurance_shares.camre_si AS CamReSI,
    re_insurance_shares.camre_sub_premium AS CamReGrossPremium,
    re_insurance_shares.camre_tax AS CamReTax,
    re_insurance_shares.camre_com AS CamRePPICom,
    re_insurance_shares.camre_net_premium AS CamReNetPremium,

    re_insurance_shares.treaty_name AS `QuotaTreatyName`,
    re_insurance_shares.treaty_quota_share AS `TreatyQuotaShare`,
    re_insurance_shares.treaty_quota_si AS TreatyQuotaSI,
    re_insurance_shares.treaty_quota_sub_premium AS TreatyQuotaGrossPremium,
    re_insurance_shares.treaty_quota_tax AS TreatyQuotaTax,
    re_insurance_shares.treaty_quota_com AS TreatyQuotaPPICom,
    re_insurance_shares.treaty_quota_net_premium AS TreatyQuotaNetPremium,

    re_insurance_shares.treaty_surplus_share AS `TreatySurplusShare`,
    re_insurance_shares.treaty_surplus_si AS TreatySurplusSI,
    re_insurance_shares.treaty_surplus_sub_premium AS TreatySurplusGrossPremium,
    re_insurance_shares.treaty_surplus_tax AS TreatySurplusTax,
    re_insurance_shares.treaty_surplus_com AS TreatySurplusPPICom,
    re_insurance_shares.treaty_surplus_net_premium AS TreatySurplusNetPremium,

    re_insurance_shares.fac_share AS `FacultativeShare`,
    re_insurance_shares.fac_si AS FacultativeSI,
    re_insurance_shares.fac_sub_premium AS FacultativeGrossPremium,
    re_insurance_shares.fac_tax AS FacultativeTax,
    re_insurance_shares.fac_com AS FacultativePPICom,
    re_insurance_shares.fac_net_premium AS FacultativeNetPremium,

    re_insurance_shares.ppi_share AS `PPIShare`,
    re_insurance_shares.ppi_si AS PPISI,
    re_insurance_shares.ppi_sub_premium AS PPIGrossPremium,
    re_insurance_shares.ppi_tax AS PPITax,
    re_insurance_shares.ppi_net_premium AS PPINetPremium,

    cs.total_paid,
    cs.total_outstanding,
    cs.total_claims,

    quote_policies.prepared_by AS `PreparedBy`,
    quote_policies.checked_by AS `CheckedBy`,
    '' AS `Quotation::CheckBy2`,
    '' AS `Quotation::CheckBy3`,

    '' AS `Certificate::Prepared By`,
    '' AS `Certificate::Checked By`,
    '' AS `Certificate::CheckedBy2`,
    '' AS `Certificate::CheckedBy3`

FROM items
LEFT JOIN quote_policies ON items.quote_policy_id = quote_policies.id
LEFT JOIN clients ON quote_policies.client_id = clients.id
LEFT JOIN locations ON items.location_id = locations.id
LEFT JOIN re_insurance_shares ON quote_policies.policy_no = re_insurance_shares.policy_no
LEFT JOIN policy_types ON quote_policies.policy_type_id = policy_types.id

LEFT JOIN (
    SELECT
        quote_policy_id,
        ROUND(SUM(paid), 2) AS total_paid,
        ROUND(SUM(outstanding_after_paid), 2) AS total_outstanding,
        ROUND(SUM(COALESCE(paid, 0) + COALESCE(outstanding_after_paid, 0)), 2) AS total_claims
    FROM register_all_claims
    GROUP BY quote_policy_id
) cs ON cs.quote_policy_id = quote_policies.id

WHERE quote_policies.policy_type = 'Public Liability'
  AND quote_policies.issue_date BETWEEN '$from' AND '$to';

    ";

        return $db->query($sql, [$from, $to])->getResultArray();
    }

    public function getWCPlacementData(string $from, string $to): array
    {
        $db = Database::connect();

        $sql = "
    SELECT
    quote_policies.policy_type AS `Quotation::PolicyType`,
    quote_policies.policy_no AS `Certificate::PolicyNo`,
    quote_policies.loan_id AS `Quotation::LoanNo`,
    quote_policies.endorsement_th AS `EndorsementNo`,
    quote_policies.dncn_no AS `DebitNotePremium 3::DebitNoteNo`,
    quote_policies.insurance_period_from AS `Quotation::InsurancePeriodFrom`,
    quote_policies.insurance_period_to AS `Quotation::InsurancePeriodTo`,
    quote_policies.insurance_period_from AS `EffectiveDate`,
    items.endorsement_type AS `EndorsementType`,
    clients.branch AS `Quotation::IndividualGroup`,
    clients.insured_name AS `Client::InsuredName`,
    clients.member_of AS `Client::Memberof`,
    clients.branch AS `Client::Branch`,
    clients.address AS `Client::AddressForView`,
    items.item_no AS `ItemNo`,
    items.brand AS `Name`,
    items.occupancy AS `Gender`,
    items.item_type AS `Job`,
    items.occupancy AS `WorkClassification`,
    quote_policies.issue_date AS `IssuedDate`,
    quote_policies.issue_date AS `IssuedDateForSearch`,
    items.sum_insured AS `SI_DeathPermanentDisablement`,
    NULL AS `SI_AccidentalMedicalExpenses`,
    items.sum_insured AS `TotalSI`,
    items.premium_after_discount AS `PremiumAfterDiscount`,
    re_insurance_shares.camre_share AS `InsuranceSecurity 2::CamReShare`,
    re_insurance_shares.camre_com AS `CamReSI`,
    re_insurance_shares.camre_max_si AS `CamReGrossPremium`,
    re_insurance_shares.camre_tax AS `CamReTax`,
    re_insurance_shares.ppi_share AS `InsuranceSecurity 2::CamRePPICom`,
    re_insurance_shares.ppi_com AS `CamRePPICom`,
    re_insurance_shares.ppi_net_premium AS `CamReNetPremium`,
    re_insurance_shares.treaty_name AS `InsuranceSecurity 2::QuotaTreatyName`,
    re_insurance_shares.treaty_quota_share AS `InsuranceSecurity 2::TreatyQuotaShare`,
    re_insurance_shares.treaty_quota_max_si AS `QuotaSI`,
    re_insurance_shares.treaty_quota_com AS `QuotaGrossPremium`,
    re_insurance_shares.treaty_quota_tax AS `QuotaTax`,
    re_insurance_shares.treaty_quota_net_premium AS `QuotaNetPremium`,
    re_insurance_shares.treaty_quota_com_premium AS `InsuranceSecurity 2::TreatyPPICom`,
    re_insurance_shares.treaty_quota_net_premium AS `QuotaPPICom`,
    re_insurance_shares.treaty_surplus_share AS `InsuranceSecurity 2::TreatySurplusShare`,
    re_insurance_shares.treaty_surplus_si AS `PWSSurplusSI`,
    re_insurance_shares.treaty_surplus_com_premium AS `PWSSurplusGrossPremium`,
    re_insurance_shares.treaty_surplus_tax AS `PWSSurplusTax`,
    re_insurance_shares.treaty_surplus_net_premium AS `PWSSurplusNetPremium`,
    re_insurance_shares.treaty_surplus_com AS `PWSSurplusPPICom`,
    re_insurance_shares.ppi_share AS `InsuranceSecurity 2::PPIShare`,
    re_insurance_shares.ppi_si AS `PPISI`,
    re_insurance_shares.ppi_com_premium AS `PPIGrossPremium`,
    re_insurance_shares.ppi_tax AS `PPITax`,
    re_insurance_shares.ppi_net_premium AS `PPINetPremium`,
    re_insurance_shares.fac_share AS `InsuranceSecurity 2::FacultativeShare`,
    re_insurance_shares.fac_si AS `FacultativeSI`,
    re_insurance_shares.fac_com_premium AS `FacultativeGrossPremium`,
    re_insurance_shares.fac_tax AS `FacultativeTax`,
    re_insurance_shares.fac_com AS `InsuranceSecurity 2::FacultativePPICom`,
    re_insurance_shares.fac_net_premium AS `FacultativePPICom`,
    re_insurance_shares.fac_net_premium AS `FacultativeNetPremium`,
    quote_policies.handler_code AS `Quotation::HandlerCodeForPreview`,
    quote_policies.prepared_by AS `Quotation::PreparedBy`,
    quote_policies.checked_by AS `Quotation::CheckedBy`,
    NULL AS `Quotation::CheckBy2`,
    NULL AS `Quotation::CheckBy3`,
    quote_policies.create_by AS `Quotation::Created By`,
    NULL AS `Certificate::Prepared By`,
    NULL AS `Certificate::Checked By`,
    NULL AS `Certificate::CheckedBy2`,
    NULL AS `Certificate::CheckedBy3`,
    NULL AS `Certificate::CreatedBy`,
    cs.total_paid,
    cs.total_outstanding,
    cs.total_claims
FROM items
LEFT JOIN quote_policies ON items.quote_policy_id = quote_policies.id
LEFT JOIN policy_types ON policy_types.id = quote_policies.policy_type_id
LEFT JOIN clients ON quote_policies.client_id = clients.id
LEFT JOIN locations ON items.location_id = locations.id
LEFT JOIN coverages ON items.location_id = coverages.location_id
LEFT JOIN re_insurance_shares ON quote_policies.policy_no = re_insurance_shares.policy_no
LEFT JOIN (
    SELECT
        quote_policy_id,
        ROUND(SUM(paid), 2) AS total_paid,
        ROUND(SUM(outstanding_after_paid), 2) AS total_outstanding,
        ROUND(SUM(COALESCE(paid, 0) + COALESCE(outstanding_after_paid, 0)), 2) AS total_claims
    FROM register_all_claims
    GROUP BY quote_policy_id
) cs ON cs.quote_policy_id = quote_policies.id
WHERE quote_policies.policy_type = 'Workmen Compensation'
  AND quote_policies.issue_date BETWEEN '$from' AND '$to'
GROUP BY
    quote_policies.policy_type,
    quote_policies.policy_no,
    quote_policies.loan_id,
    quote_policies.endorsement_th,
    quote_policies.dncn_no,
    quote_policies.insurance_period_from,
    quote_policies.insurance_period_to,
    items.endorsement_type,
    clients.branch,
    clients.insured_name,
    clients.member_of,
    clients.address,
    items.item_no,
    items.brand,
    items.occupancy,
    items.item_type,
    quote_policies.issue_date,
    items.sum_insured,
    items.premium_after_discount,
    re_insurance_shares.camre_share,
    re_insurance_shares.camre_com,
    re_insurance_shares.camre_max_si,
    re_insurance_shares.camre_tax,
    re_insurance_shares.ppi_share,
    re_insurance_shares.ppi_com,
    re_insurance_shares.ppi_net_premium,
    re_insurance_shares.treaty_name,
    re_insurance_shares.treaty_quota_share,
    re_insurance_shares.treaty_quota_max_si,
    re_insurance_shares.treaty_quota_com,
    re_insurance_shares.treaty_quota_tax,
    re_insurance_shares.treaty_quota_net_premium,
    re_insurance_shares.treaty_quota_com_premium,
    re_insurance_shares.treaty_surplus_share,
    re_insurance_shares.treaty_surplus_si,
    re_insurance_shares.treaty_surplus_com_premium,
    re_insurance_shares.treaty_surplus_tax,
    re_insurance_shares.treaty_surplus_net_premium,
    re_insurance_shares.treaty_surplus_com,
    re_insurance_shares.fac_share,
    re_insurance_shares.fac_si,
    re_insurance_shares.fac_com_premium,
    re_insurance_shares.fac_tax,
    re_insurance_shares.fac_com,
    quote_policies.handler_code,
    quote_policies.prepared_by,
    quote_policies.checked_by,
    quote_policies.create_by,
    cs.total_paid,
    cs.total_outstanding,
    cs.total_claims
ORDER BY quote_policies.policy_no;

    ";

        return $db->query($sql, [$from, $to])->getResultArray();
    }
}
