<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.4                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2013                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2013
 * $Id$
 *
 */
class CRM_Report_Form_Contribute_Recur extends CRM_Report_Form {

  function __construct() {
    $this->_columns = array(
      'civicrm_contact' => array(
        'dao' => 'CRM_Contact_DAO_Contact',
        'order_bys' => array(
          'sort_name' => array(
            'title' => ts("Last name, First name"),
          ),
        ),
        'fields' => array(
          'sort_name' => array(
            'title' => ts('Contact Name'),
            'no_repeat' => TRUE,
            'default' => TRUE,
          ),
          'id' => array(
            'no_display' => TRUE,
            'required' => TRUE,
          ),
        ),
      ),
      'civicrm_email' => array(
        'dao' => 'CRM_Core_DAO_Email',
        'fields' => array(
          'email' => array(
            'title' => ts('Email'),
            'no_repeat' => TRUE,
          ),
        ),
        'grouping' => 'contact-fields',
      ),
      'civicrm_phone' => array(
        'dao' => 'CRM_Core_DAO_Phone',
        'fields' => array(
          'phone' => array(
            'title' => ts('Phone'),
            'no_repeat' => TRUE,
          ),
        ),
        'grouping' => 'contact-fields',
      ),
      'civicrm_contribution' => array(
        'dao' => 'CRM_Contribute_DAO_Contribution',
        'fields' => array(
          'id' => array(
            'no_display' => TRUE,
            'required' => TRUE,
          ),
          'total_amount' => array(
            'title' => ts('Amount Contributed to Date'),
            'statistics' => array(
              'sum' => ts("Total Amount Contributed")
            ),
          ),
        ),
      ),
      'civicrm_contribution_recur' => array(
        'dao' => 'CRM_Contribute_DAO_ContributionRecur',
        'fields' => array(
          'id' => array(
            'no_display' => TRUE,
            'required' => TRUE,
          ),
          'currency' => array(
            'title' => ts("Currency"),
            'required' => TRUE,
            'no_display' => TRUE,
          ),
          'amount' => array(
            'title' => ts('Amount'),
            'default' => TRUE,
          ),
          'contribution_status_id' => array(
            'title' => ts('Contribution Status'),
          ),
          'frequency_interval' => array(
            'title' => ts('Frequency interval'),
            'default' => TRUE,
          ),
          'frequency_unit' => array(
            'title' => ts('Frequency unit'),
            'default' => TRUE,
          ),
          'installments' => array(
            'title' => ts('Installments'),
            'default' => TRUE,
          ),
          'start_date' => array(
            'title' => ts('Start Date'),
          ),
          'create_date' => array(
            'title' => ts('Create Date'),
          ),
          'modified_date' => array(
            'title' => ts('Modified Date'),
          ),
          'cancel_date' => array(
            'title' => ts('Cancel Date'),
          ),
          'end_date' => array(
            'title' => ts('End Date'),
          ),
          'next_sched_contribution_date' => array(
            'title' => ts('Next Scheduled Contribution Date'),
          ),
          'failure_count' => array(
            'title' => ts('Failure Count'),
          ),
          'failure_retry_date' => array(
            'title' => ts('Failure Retry Date'),
          ),
        ),
        'filters' => array(
          'contribution_status_id' => array(
            'title' => ts('Contribution Status'),
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => CRM_Contribute_PseudoConstant::contributionStatus(),
            'default' => array(5),
            'type' => CRM_Utils_Type::T_INT,
          ),
          'currency' => array(
            'title' => 'Currency',
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => CRM_Core_OptionGroup::values('currencies_enabled'),
            'default' => NULL,
            'type' => CRM_Utils_Type::T_STRING,
          ),
          'financial_type_id' => array(
            'title' => ts('Financial Type'),
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options'  => CRM_Contribute_PseudoConstant::financialType(),
            'type' => CRM_Utils_Type::T_INT,
          ),
          'frequency_unit' => array(
            'title' => ts('Frequency Unit'),
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' =>  CRM_Core_OptionGroup::values('recur_frequency_units'),
            'type' => CRM_Utils_Type::T_INT,
          ),
          'frequency_interval' => array(
            'title' => ts('Frequency Interval'),
            'type' => CRM_Utils_Type::T_INT,
          ),
          'installments' => array(
            'title' => ts('Installments'),
            'type' => CRM_Utils_Type::T_INT,
          ),
          'start_date' => array(
            'title' => ts('Start Date'),
            'operatorType' => CRM_Report_Form::OP_DATE,
            'type' => CRM_Utils_Type::T_TIME,
          ),
          'next_sched_contribution_date' => array(
            'title' => ts('Next Scheduled Contribution Date'),
            'operatorType' => CRM_Report_Form::OP_DATE,
            'type' => CRM_Utils_Type::T_TIME,
          ),
          'end_date' => array(
            'title' => ts('End Date'),
            'operatorType' => CRM_Report_Form::OP_DATE,
            'type' => CRM_Utils_Type::T_TIME,
          ),
          'calculated_end_date' => array(
            'title' => ts('Calculated end date (either end date or date all installments will be made)'),
            'description' => "does this work?",
            'operatorType' => CRM_Report_Form::OP_DATE,
            'pseudofield' => TRUE
          ),
        ),
      )
    );
    $this->_currencyColumn = 'civicrm_contribution_recur_currency';
    parent::__construct();
  }
  function getTemplateName() {
    return 'CRM/Report/Form.tpl' ;
  }

  function from() {
    $this->_from = "
      FROM civicrm_contact  {$this->_aliases['civicrm_contact']}
        INNER JOIN civicrm_contribution_recur   {$this->_aliases['civicrm_contribution_recur']}
          ON {$this->_aliases['civicrm_contact']}.id = {$this->_aliases['civicrm_contribution_recur']}.contact_id";
    $this->_from .= "
      LEFT JOIN civicrm_contribution  {$this->_aliases['civicrm_contribution']}
        ON {$this->_aliases['civicrm_contribution_recur']}.id = {$this->_aliases['civicrm_contribution']}.contribution_recur_id";
    $this->_from .= "
      LEFT JOIN civicrm_email  {$this->_aliases['civicrm_email']}
        ON {$this->_aliases['civicrm_contact']}.id = {$this->_aliases['civicrm_email']}.contact_id";
    $this->_from .= "
      LEFT  JOIN civicrm_phone {$this->_aliases['civicrm_phone']}
        ON ({$this->_aliases['civicrm_contact']}.id = {$this->_aliases['civicrm_phone']}.contact_id AND
       {$this->_aliases['civicrm_phone']}.is_primary = 1)";
  }

  function groupBy() {
    $this->_groupBy = "GROUP BY " . $this->_aliases['civicrm_contribution_recur'] . ".id";
  }

  function where() {
    parent::where();
    // Handle calculated end date. This can come from one of two sources:
    // Either there is a specified end date for the end_date field
    // Or, we calculate the end date based on the start date +
    // installments * intervals using the mysql date_add function, along
    // with the interval unit (e.g. DATE_ADD(start_date, INTERVAL 12 * 1 MONTH)
    $date_suffixes = array('relative', 'from', 'to');
    while(list(,$suffix) = each($date_suffixes)) {
      // Check to see if the user wants to search by calculated date.
      if(!empty($this->_params['calculated_end_date_' . $suffix])) {
        // The calculated date field is in use - spring into action
        // Gather values
        $relative = CRM_Utils_Array::value("calculated_end_date_relative", $this->_params);
        $from = CRM_Utils_Array::value("calculated_end_date_from", $this->_params);
        $to = CRM_Utils_Array::value("calculated_end_date_to", $this->_params);
        $end_date_db_alias = $this->_columns['civicrm_contribution_recur']['filters']['end_date']['dbAlias'];
        $end_date_type = $this->_columns['civicrm_contribution_recur']['filters']['end_date']['type'];
        $start_date_type = $this->_columns['civicrm_contribution_recur']['filters']['start_date']['type'];
        $frequency_unit_db_alias = $this->_columns['civicrm_contribution_recur']['filters']['frequency_unit']['dbAlias'];
        $frequency_interval_db_alias = $this->_columns['civicrm_contribution_recur']['filters']['frequency_interval']['dbAlias'];
        $installments_db_alias = $this->_columns['civicrm_contribution_recur']['filters']['installments']['dbAlias'];
        $start_date_db_alias = $this->_columns['civicrm_contribution_recur']['filters']['start_date']['dbAlias'];

        // The end date clause is simple to construct
        $end_date_clause   = $this->dateClause($end_date_db_alias, $relative, $from, $to, $end_date_type, NULL, NULL);

        // NOTE: For the calculation based on installment, there doesn't
        // seem to be a way to include the interval unit (e.g. month,
        // date, etc) as a field name - so we have to build a complex
        // OR statement instead.

        $installment_clause = '(' .
          $this->dateClause("DATE_ADD($start_date_db_alias, INTERVAL $installments_db_alias * COALESCE($frequency_interval_db_alias,1) month)",
          $relative, $from, $to, $start_date_type, NULL, NULL);
        $installment_clause .= " AND $frequency_unit_db_alias = 'month' ) OR \n";

        $installment_clause .= '(' .
          $this->dateClause("DATE_ADD($start_date_db_alias, INTERVAL $installments_db_alias * COALESCE($frequency_interval_db_alias,1) day)",
          $relative, $from, $to, $start_date_type, NULL, NULL);
        $installment_clause .= " AND $frequency_unit_db_alias = 'day' ) OR \n";

        $installment_clause .= '(' .
          $this->dateClause("DATE_ADD($start_date_db_alias, INTERVAL $installments_db_alias * COALESCE($frequency_interval_db_alias, 1) week)",
          $relative, $from, $to, $start_date_type, NULL, NULL);
        $installment_clause .= " AND $frequency_unit_db_alias = 'week' ) OR \n";

        $installment_clause .= '(' .
          $this->dateClause("DATE_ADD($start_date_db_alias, INTERVAL $installments_db_alias * COALESCE($frequency_interval_db_alias, 1) year)",
          $relative, $from, $to, $start_date_type, NULL, NULL);
        $installment_clause .= " AND $frequency_unit_db_alias = 'year' )\n";

        $this->_where .= " AND ";
        $this->_where .= "(";
        $this->_where .= "($end_date_db_alias IS NOT NULL AND $end_date_clause)\n";
        $this->_where .= " OR \n";
        $this->_where .= "($installments_db_alias IS NOT NULL AND ($installment_clause))\n";
        $this->_where .= ')';
        break;
      }
    }
  }


  function alterDisplay(&$rows) {
    $contributionStatus = CRM_Contribute_PseudoConstant::contributionStatus();
    foreach ($rows as $rowNum => $row) {
      // convert display name to links
      if (array_key_exists('civicrm_contact_sort_name', $row) &&
        CRM_Utils_Array::value('civicrm_contact_sort_name', $rows[$rowNum]) &&
        array_key_exists('civicrm_contact_id', $row)
      ) {
        $url = CRM_Utils_System::url('civicrm/contact/view',
          'reset=1&cid=' . $row['civicrm_contact_id'],
          $this->_absoluteUrl
        );
        $rows[$rowNum]['civicrm_contact_sort_name_link'] = $url;
        $rows[$rowNum]['civicrm_contact_sort_name_hover'] = ts('View Contact Summary for this Contact.');
      }

      // handle contribution status id
      if ($value = CRM_Utils_Array::value('civicrm_contribution_recur_contribution_status_id', $row)) {
        $rows[$rowNum]['civicrm_contribution_recur_contribution_status_id'] = $contributionStatus[$value];
      }
      
      if ($value = CRM_Utils_Array::value('civicrm_contribution_recur_amount', $row)) {
        $rows[$rowNum]['civicrm_contribution_recur_amount'] = CRM_Utils_Money::format($rows[$rowNum]['civicrm_contribution_recur_amount'],$rows[$rowNum]['civicrm_contribution_recur_currency']);
      }
      
    }
  }
}