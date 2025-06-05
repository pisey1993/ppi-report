<?php

namespace App\Models;

use CodeIgniter\Model;
use Config\Database;

class QuoteModel extends Model
{

    public function getData(string $from, string $to): array
    {
        $db = Database::connect();

        $sql = "SELECT DISTINCT
    quote_policies.policy_type AS `PolicyType`,
    quote_policies.quote_no AS `QuoteNo`,
    quote_policies.policy_no AS `PolicyNo`,
    quote_policies.renew_from_policy_no AS `RenewFromPolicyNo`,
    quote_policies.insurance_period_to AS `Quotation 4::InsurancePeriodTo`,
    clients.insured_name AS `Client::InsuredName`,
    locations.total_sum_insured AS `TotalSI`,
    items.premium_before_discount AS `Premium`,
    quote_policies.premium_after_discount AS `Certificate::TotalPremium`,
    quote_policies.handler_code AS `HandlerCodeForPreview`,
    DATE_FORMAT(quote_policies.insurance_period_from, '%d-%m-%Y') AS `InsurancePeriodFrom`,
    DATE_FORMAT(quote_policies.insurance_period_to, '%d-%m-%Y') AS `InsurancePeriodTo`,
    quote_policies.issue_date AS `IssuedDate`,
    clients.type AS `Client::Type`,
    clients.address AS `Client::AddressForView`,
    quote_policies.status AS `QuoteStatusForDisplay`,
    '' AS `LossHistory`,
    clients.member_of AS `Client::Memberof`,
    quote_policies.handler_code,
    
    CASE 
        WHEN quote_policies.insurance_period_to < CURDATE() THEN 'Inactive'
        ELSE 'Active'
    END AS `PolicyStatus`,
    quote_policies.commission_to_broker AS `CommissionToBroker`,
    quote_policies.prepared_by AS `PreparedBy`
FROM quote_policies
LEFT JOIN clients ON quote_policies.client_id = clients.id
LEFT JOIN items ON items.quote_policy_id = quote_policies.id
LEFT JOIN locations ON locations.quote_policy_id = quote_policies.id
WHERE quote_policies.issue_date BETWEEN ? AND ?
ORDER BY quote_policies.issue_date DESC;

    ";

        return $db->query($sql, [$from, $to])->getResultArray();
    }
}
