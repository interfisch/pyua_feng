<?php
/**
 * @category Elk
 * @package Elk_Export
 * @copyright 2013 u+i interact GmbH & Co. KG (http://www.uandi.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Elk_Export_CouponController extends Mage_Core_Controller_Front_Action
{

	public function exportAction()
	{
		$sql =	"SELECT " .
                " s.rule_id as id," .
				" s.name as name, " .
				" s.times_used as benutzt, " .
				" c.code as code " .
				"FROM " .
				" salesrule as s " .
				"LEFT JOIN " .
				" salesrule_coupon as c ON c.rule_id = s.rule_id";

		$results = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($sql);

        $orderSql = "SELECT applied_rule_ids as rule, grand_total as amount " .
               "FROM sales_flat_order " .
               "WHERE applied_rule_ids <> '' AND state <> 'canceled';";

        $orderQuery = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($orderSql);

        $ruleAmount = array();

        foreach($orderQuery as $row) {
            foreach(explode(',', $row['rule']) as $rule_id) {
                if(!isset($ruleAmount[(int) $rule_id])) {
                    $ruleAmount[(int) $rule_id] = 0;
                }

                $ruleAmount[(int) $rule_id] += (float) $row['amount'];
            }
        }

		$headline = array('name', 'benutzt', 'code', 'umsatz');
		$rows = array($headline);

		foreach ($results as $result) {
            if(isset($ruleAmount[(int) $result['id']])) {
                $amount = $ruleAmount[(int) $result['id']];
            } else {
                $amount = 0;
            }

            array_push($rows, array($result['name'], $result['benutzt'], $result['code'] ?: '', number_format($amount, 2, ',', '')));
		}

		$this->getResponse()
			->clearHeaders()
			->setHeader('Content-Type', 'text/csv')
			->setBody($this->writeCsv($rows));
	}

	private function writeCsv(array $data, $seperator = ";", $quote = '"') {
		$lines = array();
		foreach($data as $line) {
			$lines[] = $quote . implode($quote.$seperator.$quote, array_map(function($value) {
				return utf8_decode(print_r($value, true));
			}, (array) $line)) . $quote;
		}

		return implode("\n", $lines);
	}
}
