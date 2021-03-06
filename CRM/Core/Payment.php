<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.5                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2014                                |
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
 * @copyright CiviCRM LLC (c) 2004-2014
 * $Id$
 *
 */

abstract class CRM_Core_Payment {

  /**
   * How are we getting billing information?
   *
   * FORM   - we collect it on the same page
   * BUTTON - the processor collects it and sends it back to us via some protocol
   */
  CONST
    BILLING_MODE_FORM = 1,
    BILLING_MODE_BUTTON = 2,
    BILLING_MODE_NOTIFY = 4;

  /**
   * Which payment type(s) are we using?
   *
   * credit card
   * direct debit
   * or both
   *
   */
  CONST
    PAYMENT_TYPE_CREDIT_CARD = 1,
    PAYMENT_TYPE_DIRECT_DEBIT = 2;

  /**
   * Subscription / Recurring payment Status
   * START, END
   *
   */
  CONST
    RECURRING_PAYMENT_START = 'START',
    RECURRING_PAYMENT_END = 'END';

  /**
   * We only need one instance of this object. So we use the singleton
   * pattern and cache the instance in this variable
   *
   * @var object
   * @static
   */
  static private $_singleton = NULL;

  protected $_paymentProcessor;

  /**
   * @var CRM_Core_Form
   */
  protected $_paymentForm = NULL;

  /**
   * Singleton function used to manage this object
   *
   * @param string  $mode the mode of operation: live or test
   * @param array  $paymentProcessor the details of the payment processor being invoked
   * @param object $paymentForm deprecated - avoid referring to this if possible. If you have to use it document why as this is scary interaction
   * @param boolean $force            should we force a reload of this payment object
   *
   * @return CRM_Core_Payment
   * @static
   *
   */
  static function &singleton($mode = 'test', &$paymentProcessor, &$paymentForm = NULL, $force = FALSE) {
    // make sure paymentProcessor is not empty
    // CRM-7424
    if (empty($paymentProcessor)) {
      return CRM_Core_DAO::$_nullObject;
    }

    $cacheKey = "{$mode}_{$paymentProcessor['id']}_" . (int)isset($paymentForm);

    if (!isset(self::$_singleton[$cacheKey]) || $force) {
      $config = CRM_Core_Config::singleton();
      $ext = CRM_Extension_System::singleton()->getMapper();
      if ($ext->isExtensionKey($paymentProcessor['class_name'])) {
        $paymentClass = $ext->keyToClass($paymentProcessor['class_name'], 'payment');
        require_once ($ext->classToPath($paymentClass));
      }
      else {
        $paymentClass = 'CRM_Core_' . $paymentProcessor['class_name'];
        if (empty($paymentClass)) {
          throw new CRM_Core_Exception('no class provided');
        }
        require_once (str_replace('_', DIRECTORY_SEPARATOR, $paymentClass) . '.php');
      }

      //load the object.
      self::$_singleton[$cacheKey] = $paymentClass::singleton($mode, $paymentProcessor);
    }

    //load the payment form for required processor.
    //if ($paymentForm !== NULL) {
      //self::$_singleton[$cacheKey]->setForm($paymentForm);
    //}

    return self::$_singleton[$cacheKey];
  }

  /**
   * @param array $params
   *
   * @return mixed
   */
  public static function logPaymentNotification($params) {
    $message = 'payment_notification ';
    if (!empty($params['processor_name'])) {
      $message .= 'processor_name=' . $params['processor_name'];
    }
    if (!empty($params['processor_id'])) {
      $message .= 'processor_id=' . $params['processor_id'];
    }

    $log = new CRM_Utils_SystemLogger();
    $log->alert($message, $_REQUEST);
  }

  /**
   * Check if capability is supported
   * @param string $capability e.g BackOffice, LiveMode, FutureRecurStartDate
   *
   * @return bool
   */
  public function supports($capability) {
    $function = 'supports' . ucfirst($capability);
    if (method_exists($this, $function)) {
      return $this->$function();
    }
    return FALSE;
  }

  /**
   * Are back office payments supported - e.g paypal standard won't permit you to enter a credit card associated with someone else's login
   * The intention is to support off-site (other than paypal) & direct debit but that is not all working yet so to reach a 'stable' point we disable
   * @return bool
   */
  protected function supportsBackOffice() {
    if ($this->_paymentProcessor['billing_mode'] == 4 || $this->_paymentProcessor['payment_type'] != 1) {
      return FALSE;
    }
    else {
      return TRUE;
    }
  }

  /**
   * Are back office payments supported - e.g paypal standard won't permit you to enter a credit card associated with someone else's login
   * @return bool
   */
  protected function supportsLiveMode() {
    return TRUE;
  }

  /**
   * Should the first payment date be configurable when setting up back office recurring payments
   * We set this to false for historical consistency but in fact most new processors use tokens for recurring and can support this
   * @return bool
   */
  protected function supportsFutureRecurStartDate() {
    return FALSE;
  }

  /**
   * Setter for the payment form that wants to use the processor
   *
   * @param CRM_Core_Form $paymentForm
   *
   */
  function setForm(&$paymentForm) {
    $this->_paymentForm = $paymentForm;
  }

  /**
   * Getter for payment form that is using the processor
   *
   * @return CRM_Core_Form  A form object
   */
  function getForm() {
    return $this->_paymentForm;
  }

  /**
   * Getter for accessing member vars
   *
   * @param string $name
   *
   * @return null
   */
  function getVar($name) {
    return isset($this->$name) ? $this->$name : NULL;
  }

  /**
   * Get name for the payment information type
   *
   * @return string
   */
  public function getPaymentTypeName() {
    return $this->_paymentProcessor['payment_type'] == 1 ? 'credit_card' : 'direct_debit';
  }

  /**
   * Get label for the payment information type
   *
   * @return string
   */
  public function getPaymentTypeLabel() {
    return $this->_paymentProcessor['payment_type'] == 1 ? 'Credit Card' : 'Direct Debit';
  }

  /**
   * Get array of fields that should be displayed on the payment form
   * @todo make payment type an option value & use it in the function name - currently on debit & credit card work
   * @return array
   * @throws CiviCRM_API3_Exception
   */
  public function getPaymentFormFields() {
    if ($this->_paymentProcessor['billing_mode'] == 4) {
      return array();
    }
    return $this->_paymentProcessor['payment_type'] == 1 ? $this->getCreditCardFormFields() : $this->getDirectDebitFormFields();
  }

  /**
   * Get array of fields that should be displayed on the payment form for credit cards
   *
   * @return array
   */
  protected function getCreditCardFormFields() {
    return array(
      'credit_card_type',
      'credit_card_number',
      'cvv2',
      'credit_card_exp_date',
    );
  }

  /**
   * Get array of fields that should be displayed on the payment form for direct debits
   *
   * @return array
   */
  protected function getDirectDebitFormFields() {
    return array(
      'account_holder',
      'bank_account_number',
      'bank_identification_number',
      'bank_name',
    );
  }

  /**
   * Return an array of all the details about the fields potentially required for payment fields
   * Only those determined by getPaymentFormFields will actually be assigned to the form
   *
   * @return array field metadata
   */
  public function getPaymentFormFieldsMetadata() {
    //@todo convert credit card type into an option value
    $creditCardType = array('' => ts('- select -')) + CRM_Contribute_PseudoConstant::creditCard();
    return array(
      'credit_card_number' => array(
        'htmlType' => 'text',
        'name' => 'credit_card_number',
        'title' => ts('Card Number'),
        'cc_field' => TRUE,
        'attributes' => array(
          'size' => 20,
          'maxlength' => 20,
          'autocomplete' => 'off'
        ),
        'is_required' => TRUE,
      ),
      'cvv2' => array(
        'htmlType' => 'text',
        'name' => 'cvv2',
        'title' => ts('Security Code'),
        'cc_field' => TRUE,
        'attributes' => array(
          'size' => 5,
          'maxlength' => 10,
          'autocomplete' => 'off'
        ),
        'is_required' => CRM_Core_BAO_Setting::getItem(CRM_Core_BAO_Setting::CONTRIBUTE_PREFERENCES_NAME,
          'cvv_backoffice_required',
          NULL,
          1
        ),
        'rules' => array(
          array(
            'rule_message' => ts('Please enter a valid value for your card security code. This is usually the last 3-4 digits on the card\'s signature panel.'),
            'rule_name' => 'integer',
            'rule_parameters' => NULL,
        )),
      ),
      'credit_card_exp_date' => array(
        'htmlType' => 'date',
        'name' => 'credit_card_exp_date',
        'title' => ts('Expiration Date'),
        'cc_field' => TRUE,
        'attributes' => CRM_Core_SelectValues::date('creditCard'),
        'is_required' => TRUE,
        'rules' => array(
          array(
            'rule_message' => ts('Card expiration date cannot be a past date.'),
            'rule_name' => 'currentDate',
            'rule_parameters' => TRUE,
          )),
      ),
      'credit_card_type' => array(
        'htmlType' => 'select',
        'name' => 'credit_card_type',
        'title' => ts('Card Type'),
        'cc_field' => TRUE,
        'attributes' => $creditCardType,
        'is_required' => FALSE,
      ),
      'account_holder' => array(
        'htmlType' => 'text',
        'name' => 'account_holder',
        'title' => ts('Account Holder'),
        'cc_field' => TRUE,
        'attributes' => array(
          'size' => 20,
          'maxlength' => 34,
          'autocomplete' => 'on'
        ),
        'is_required' => TRUE,
      ),
      //e.g. IBAN can have maxlength of 34 digits
      'bank_account_number' => array(
        'htmlType' => 'text',
        'name' => 'bank_account_number',
        'title' => ts('Bank Account Number'),
        'cc_field' => TRUE,
        'attributes' => array(
          'size' => 20,
          'maxlength' => 34,
          'autocomplete' => 'off'
        ),
        'rules' => array(
          array(
            'rule_message' => ts('Please enter a valid Bank Identification Number (value must not contain punctuation characters).'),
            'rule_name' => 'nopunctuation',
            'rule_parameters' => NULL,
        )),
        'is_required' => TRUE,
      ),
      //e.g. SWIFT-BIC can have maxlength of 11 digits
      'bank_identification_number' => array(
        'htmlType' => 'text',
        'name' => 'bank_identification_number',
        'title' => ts('Bank Identification Number'),
        'cc_field' => TRUE,
        'attributes' => array(
          'size' => 20,
          'maxlength' => 11,
          'autocomplete' => 'off'
        ),
        'is_required' => TRUE,
        'rules' => array(
          array(
            'rule_message' => ts('Please enter a valid Bank Identification Number (value must not contain punctuation characters).'),
            'rule_name' => 'nopunctuation',
            'rule_parameters' => NULL,
        )),
      ),
      'bank_name' => array(
        'htmlType' => 'text',
        'name' => 'bank_name',
        'title' => ts('Bank Name'),
        'cc_field' => TRUE,
        'attributes' => array(
          'size' => 20,
          'maxlength' => 64,
          'autocomplete' => 'off'
        ),
        'is_required' => TRUE,

      )
    );
  }

  /**
   * This function collects all the information from a web/api form and invokes
   * the relevant payment processor specific functions to perform the transaction
   *
   * @param  array $params assoc array of input parameters for this transaction
   *
   * @return array the result in an nice formatted array (or an error object)
   * @abstract
   */
  abstract function doDirectPayment(&$params);

  /**
   * Process payment - this function wraps around both doTransferPayment and doDirectPayment
   * it ensures an exception is thrown & moves some of this logic out of the form layer and makes the forms more agnostic
   *
   * @param array $params
   *
   * @param $component
   *
   * @throws CRM_Core_Exception
   */
  public function doPayment(&$params, $component) {
    if ($this->_paymentProcessor['billing_mode'] == 4) {
      $result = $this->doTransferCheckout($params, $component);
    }
    else {
      $result = $this->doDirectPayment($params, $component);
    }
    if (is_a($result, 'CRM_Core_Error')) {
      throw new CRM_Core_Exception(CRM_Core_Error::getMessages($result));
    }
  }

  /**
   * This function checks to see if we have the right config values
   *
   * @return string the error message if any
   * @public
   */
  abstract function checkConfig();

  /**
   * @param $paymentProcessor
   *
   * @return bool
   */
  static function paypalRedirect(&$paymentProcessor) {
    if (!$paymentProcessor) {
      return FALSE;
    }

    if (isset($_GET['payment_date']) &&
      isset($_GET['merchant_return_link']) &&
      CRM_Utils_Array::value('payment_status', $_GET) == 'Completed' &&
      $paymentProcessor['payment_processor_type'] == "PayPal_Standard"
    ) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Page callback for civicrm/payment/ipn
   * @public
   */
  static function handleIPN() {
    self::handlePaymentMethod(
      'PaymentNotification',
      array(
        'processor_name' => @$_GET['processor_name'],
        'processor_id' => @$_GET['processor_id'],
        'mode' => @$_GET['mode'],
      )
    );
  }

  /**
   * Payment callback handler. The processor_name or processor_id is passed in.
   * Note that processor_id is more reliable as one site may have more than one instance of a
   * processor & ideally the processor will be validating the results
   * Load requested payment processor and call that processor's handle<$method> method
   *
   * @public
   * @param $method
   * @param array $params
   */
  static function handlePaymentMethod($method, $params = array()) {
    if (!isset($params['processor_id']) && !isset($params['processor_name'])) {
      CRM_Core_Error::fatal("Either 'processor_id' or 'processor_name' param is required for payment callback");
    }
    self::logPaymentNotification($params);

    // Query db for processor ..
    $mode = @$params['mode'];

    $sql = "SELECT ppt.class_name, ppt.name as processor_name, pp.id AS processor_id
              FROM civicrm_payment_processor_type ppt
        INNER JOIN civicrm_payment_processor pp
                ON pp.payment_processor_type_id = ppt.id
               AND pp.is_active
               AND pp.is_test = %1";
    $args[1] = array($mode == 'test' ? 1 : 0, 'Integer');

    if (isset($params['processor_id'])) {
      $sql .= " WHERE pp.id = %2";
      $args[2] = array($params['processor_id'], 'Integer');
      $notfound = "No active instances of payment processor ID#'{$params['processor_id']}'  were found.";
    }
    else {
      $sql .= " WHERE ppt.name = %2";
      $args[2] = array($params['processor_name'], 'String');
      $notfound = "No active instances of the '{$params['processor_name']}' payment processor were found.";
    }

    $dao = CRM_Core_DAO::executeQuery($sql, $args);

    // Check whether we found anything at all ..
    if (!$dao->N) {
      CRM_Core_Error::fatal($notfound);
    }

    $method = 'handle' . $method;
    $extension_instance_found = FALSE;

    // In all likelihood, we'll just end up with the one instance returned here. But it's
    // possible we may get more. Hence, iterate through all instances ..

    while ($dao->fetch()) {
      // Check pp is extension
      $ext = CRM_Extension_System::singleton()->getMapper();
      if ($ext->isExtensionKey($dao->class_name)) {
        $paymentClass = $ext->keyToClass($dao->class_name, 'payment');
        require_once $ext->classToPath($paymentClass);
      }
      else {
        // Legacy or extension as module instance
        if (empty($paymentClass)) {
          $paymentClass = 'CRM_Core_' . $dao->class_name;

        }
      }

      $paymentProcessor = CRM_Financial_BAO_PaymentProcessor::getPayment($dao->processor_id, $mode);

      // Should never be empty - we already established this processor_id exists and is active.
      if (empty($paymentProcessor)) {
        continue;
      }

      // Instantiate PP
      $processorInstance = $paymentClass::singleton($mode, $paymentProcessor);

      // Does PP implement this method, and can we call it?
      if (!method_exists($processorInstance, $method) ||
        !is_callable(array($processorInstance, $method))
      ) {
        // on the off chance there is a double implementation of this processor we should keep looking for another
        // note that passing processor_id is more reliable & we should work to deprecate processor_name
        continue;
      }

      // Everything, it seems, is ok - execute pp callback handler
      $processorInstance->$method();
      $extension_instance_found = TRUE;
    }

    if (!$extension_instance_found) CRM_Core_Error::fatal(
      "No extension instances of the '{$params['processor_name']}' payment processor were found.<br />" .
      "$method method is unsupported in legacy payment processors."
    );

    // Exit here on web requests, allowing just the plain text response to be echoed
    if ($method == 'handlePaymentNotification') {
      CRM_Utils_System::civiExit();
    }
  }

  /**
   * Check whether a method is present ( & supported ) by the payment processor object.
   *
   * @param  string $method method to check for.
   *
   * @return boolean
   * @public
   */
  function isSupported($method = 'cancelSubscription') {
    return method_exists(CRM_Utils_System::getClassName($this), $method);
  }

  /**
   * @param int $entityID
   * @param null $entity
   * @param string $action
   *
   * @return string
   */
  function subscriptionURL($entityID = NULL, $entity = NULL, $action = 'cancel') {
    // Set URL
    switch ($action) {
      case 'cancel' :
        $url = 'civicrm/contribute/unsubscribe';
        break;
      case 'billing' :
        //in notify mode don't return the update billing url
        if (!$this->isSupported('updateSubscriptionBillingInfo')) {
          return NULL;
        }
        $url = 'civicrm/contribute/updatebilling';
        break;
      case 'update' :
        $url = 'civicrm/contribute/updaterecur';
        break;
    }

    $session       = CRM_Core_Session::singleton();
    $userId        = $session->get('userID');
    $contactID     = 0;
    $checksumValue = '';
    $entityArg     = '';

    // Find related Contact
    if ($entityID) {
      switch ($entity) {
        case 'membership' :
          $contactID = CRM_Core_DAO::getFieldValue("CRM_Member_DAO_Membership", $entityID, "contact_id");
          $entityArg = 'mid';
          break;

        case 'contribution' :
          $contactID = CRM_Core_DAO::getFieldValue("CRM_Contribute_DAO_Contribution", $entityID, "contact_id");
          $entityArg = 'coid';
          break;

        case 'recur' :
          $sql = "
    SELECT con.contact_id
      FROM civicrm_contribution_recur rec
INNER JOIN civicrm_contribution con ON ( con.contribution_recur_id = rec.id )
     WHERE rec.id = %1
  GROUP BY rec.id";
          $contactID = CRM_Core_DAO::singleValueQuery($sql, array(1 => array($entityID, 'Integer')));
          $entityArg = 'crid';
          break;
      }
    }

    // Add entity arguments
    if ($entityArg != '') {
      // Add checksum argument
      if ($contactID != 0 && $userId != $contactID) {
        $checksumValue = '&cs=' . CRM_Contact_BAO_Contact_Utils::generateChecksum($contactID, NULL, 'inf');
      }
      return CRM_Utils_System::url($url, "reset=1&{$entityArg}={$entityID}{$checksumValue}", TRUE, NULL, FALSE, TRUE);
    }

    // Else login URL
    if ($this->isSupported('accountLoginURL')) {
      return $this->accountLoginURL();
    }

    // Else default
    return $this->_paymentProcessor['url_recur'];
  }
}
