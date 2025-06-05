<?php

namespace App\Models;
use CodeIgniter\Model;

class RenewalReportModel extends Model
{
    protected $DBGroup = 'default';

    public function getReportData($from, $to, $status)
    {
        $db = \Config\Database::connect();

        $sql = "
            SELECT
                qp.id,
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
        ";

        $params = [$from, $to];

        if ($status !== 'all' && $status !== '*') {
            $sql .= " AND qp.status = ?";
            $params[] = $status;
        }

        $sql .= "
            GROUP BY
                qp.id,
                qp.quote_no,
                qp.policy_no,
                qp.loan_id,
                c.telephone,
                c.insured_name,
                c.address,
                l.scope_of_cover,
                qp.sum_insured,
                qp.premium_before_discount,
                qp.total_premium,
                l.no_claim_discount_rate,
                l.no_claim_discount_premium,
                qp.discount_rate,
                cs.total_paid,
                cs.total_outstanding,
                cs.total_claims,
                c.branch,
                c.handler_code,
                c.member_of,
                qp.loss_history,
                qp.status,
                l.group_discount_rate,
                l.group_discount_premium,
                qp.loan_period_in_month,
                c.client_no,
                qp.issue_date
        ";

        $query = $db->query($sql, $params);

        return $query->getResult();
    }
}
