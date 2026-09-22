<?php

require_once 'CRM/Core/Form.php';

/**
 * Form controller class to manage Oppgave
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 * 
 * Copyright (C) 2014 Coöperatieve CiviCooP U.A. <http://www.civicoop.org>
 * Licensed to MAF Norge <http://www.maf.no> under the  AGPL-3.0
 */
class CRM_Oppgavexml_Form_Oppgave extends CRM_Core_Form {
  
  protected $_oppgave_id = null;
  protected $_oppgave_year = null;
  protected $_donor_type_list = [];
  /**
   * Function to build the QuickForm
   * 
   * @access public
   */
  function buildQuickForm() {
    $this->set_option_lists();
    $this->add_form_elements();
    $this->assign('form_header', $this->set_form_header());
    $this->assign('elementNames', $this->get_renderable_element_names());
    parent::buildQuickForm();
  }
  /**
   * Function to perform processing before displaying form
   * 
   * @access public
   */
  function preProcess() {
    $this->_oppgave_year = CRM_Utils_Request::retrieve('year', 'Positive');
    if ($this->_action != CRM_Core_Action::ADD) {
      $this->_oppgave_id = CRM_Utils_Request::retrieve('oid', 'Positive');
    }
    $session = CRM_Core_Session::singleton();
    /*
     * if action = delete, execute delete immediately
     */
    if ($this->_action == CRM_Core_Action::DELETE) {
      CRM_Oppgavexml_BAO_Oppgave::delete_by_id($this->_oppgave_id);
      $session->setStatus('Oppgave deleted', 'Delete', 'success');
      CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/oppgavelist', 'year='.$this->_oppgave_year));
    }
  }
  /**
   * Function to set default values
   * 
   * @return array $defaults
   * @access public
   */
  function setDefaultValues() {
    $defaults = [];
    if (isset($this->_oppgave_id)) {
      $oppgave_values = CRM_Oppgavexml_BAO_Oppgave::get_values(['id' => $this->_oppgave_id]);
      foreach ($oppgave_values[$this->_oppgave_id] as $name => $value) {
        $defaults[$name] = $value;
      }
    } else {
      $defaults['oppgave_year'] = $this->_oppgave_year;
      $defaults['id'] = 0;
    }
    return $defaults;
  }
  /**
   * Function to perform post submit processing
   * 
   * @access public
   */
  function postProcess() {
    $values = $this->exportValues();
    $this->save_oppgave($values);
    parent::postProcess();
  }
  /**
   * Function to add validation rules
   * 
   * @access public
   */
  function addRules() {
    $this->addFormRule(['CRM_Oppgavexml_Form_Oppgave', 'validate_amount']);
    $this->addFormRule(['CRM_Oppgavexml_Form_Oppgave', 'validate_donor_name_empty']);
    $this->addFormRule(['CRM_Oppgavexml_Form_Oppgave', 'validate_donor_number_empty']);
    switch ($this->_action) {
      case CRM_Core_Action::ADD:
        $this->addFormRule(['CRM_Oppgavexml_Form_Oppgave', 'validate_donor_name_exists_add']);
        $this->addFormRule(['CRM_Oppgavexml_Form_Oppgave', 'validate_donor_number_exists_add']);
        break;
      case CRM_Core_Action::UPDATE:
        $this->addFormRule(['CRM_Oppgavexml_Form_Oppgave', 'validate_donor_name_exists_update']);
        $this->addFormRule(['CRM_Oppgavexml_Form_Oppgave', 'validate_donor_number_exists_update']);
        break;
    }
  }
  /**
   * Function to validate the donor_name entered empty
   *  
   * @param array $fields
   * @return array $errors or TRUE
   * @access public
   * @static
   */
  static function validate_donor_name_empty($fields) {
    if (empty($fields['donor_name'])) {
      $errors['donor_name'] = ts('Donor name can not be empty');
      return $errors;
    }
    return TRUE;
  }
  /**
   * Function to validate the donor_name entered does not exist for add
   * 
   * @param array $fields
   * @return array $errors or TRUE
   * @access public
   * @static
   */
  static function validate_donor_name_exists_add($fields) {
    $query = 'SELECT COUNT(*) AS count_oppgave FROM civicrm_oppgave WHERE '
      . 'LOWER(donor_name) = %1 AND oppgave_year = %2';
    $query_params = [
      1 => [strtolower($fields['donor_name']), 'String'],
      2 => [$fields['oppgave_year'], 'Positive']];
    $dao = CRM_Core_DAO::executeQuery($query, $query_params);
    if ($dao->fetch()) {
      if ($dao->count_oppgave > 0) {
        $errors['donor_name'] = ts('Donor name already exists in oppgave for this year');
        return $errors;
      }
    }
    return TRUE;
  }
  /**
   * Function to validate the donor_name entered does not exist for update
   * 
   * @param array $fields
   * @return array $errors or TRUE
   * @access public
   * @static
   */
  static function validate_donor_name_exists_update($fields) {
    $select_query = 'SELECT donor_name FROM civicrm_oppgave WHERE id = %1';
    $select_params = [1 => [$fields['id'], 'Positive']];
    $select_dao = CRM_Core_DAO::executeQuery($select_query, $select_params);
    if ($select_dao->fetch()) {
      if (strtolower($fields['donor_name']) != strtolower($select_dao->donor_name)) {
        $check_query = 'SELECT COUNT(*) AS count_oppgave FROM civicrm_oppgave WHERE '
          . 'LOWER(donor_name) = %1 AND oppgave_year = %2';
        $check_params = [
          1 => [strtolower($fields['donor_name']), 'String'],
          2 => [$fields['oppgave_year'], 'Positive']];
        $check_dao = CRM_Core_DAO::executeQuery($check_query, $check_params);
        if ($check_dao->fetch()) {
          if ($check_dao->count_oppgave > 0) {
            $errors['donor_name'] = ts('Donor name already exists in oppgave for this year');
            return $errors;
          }
        }
      }
    }
    return TRUE;
  }
  /**
   * Function to validate the donor_number entered does not exist for add
   * 
   * @param array $fields
   * @return array $errors or TRUE
   * @access public
   * @static
   */
  static function validate_donor_number_exists_add($fields) {
    $query = 'SELECT COUNT(*) AS count_oppgave FROM civicrm_oppgave WHERE '
      . 'donor_number = %1 AND oppgave_year = %2';
    $query_params = [
      1 => [$fields['donor_number'], 'String'],
      2 => [$fields['oppgave_year'], 'Positive']];
    $dao = CRM_Core_DAO::executeQuery($query, $query_params);
    if ($dao->fetch()) {
      if ($dao->count_oppgave > 0) {
        $errors['donor_number'] = ts('Donor number already exists in oppgave for this year');
        return $errors;
      }
    }
    return TRUE;
  }
  /**
   * Function to validate the donor_number entered does not exist for update
   * 
   * @param array $fields
   * @return array $errors or TRUE
   * @access public
   * @static
   */
  static function validate_donor_number_exists_update($fields) {
    $select_query = 'SELECT donor_number FROM civicrm_oppgave WHERE id = %1';
    $select_params = [1 => [$fields['id'], 'Positive']];
    $select_dao = CRM_Core_DAO::executeQuery($select_query, $select_params);
    if ($select_dao->fetch()) {
      if (strtolower($fields['donor_number']) != strtolower($select_dao->donor_number)) {
        $check_query = 'SELECT COUNT(*) AS count_oppgave FROM civicrm_oppgave WHERE '
          . 'donor_number = %1 AND oppgave_year = %2';
        $check_params = [
          1 => [$fields['donor_number'], 'String'],
          2 => [$fields['oppgave_year'], 'Positive']];
        $check_dao = CRM_Core_DAO::executeQuery($check_query, $check_params);
        if ($check_dao->fetch()) {
          if ($check_dao->count_oppgave > 0) {
            $errors['donor_number'] = ts('Donor number already exists in oppgave for this year');
            return $errors;
          }
        }
      }
    }
    return TRUE;
  }
  /**
   * Function to validate the donor_number entered not empty
   * 
   * @param array $fields
   * @return array $errors or TRUE
   * @access public
   * @static
   */
  static function validate_donor_number_empty($fields) {
    if (empty($fields['donor_number'])) {
      $errors['donor_number'] = ts('Donor number can not be empty');
      return $errors;
    }
    return TRUE;
  }
  /**
   * Function to validate the amount entered
   * 
   * @param array $fields
   * @return array $errors or TRUE
   * @access public
   * @static
   */
  static function validate_amount($fields) {
    $oppgave_config = CRM_Oppgavexml_Config::singleton();
    $min_amount = $oppgave_config->get_min_deductible_amount();
    $max_amount = $oppgave_config->get_max_deductible_amount();
    if ($fields['deductible_amount'] < $min_amount) {
      $errors['deductible_amount'] = ts('Amount can not be less than %1', [1 => $min_amount]);
      return $errors;
    }
    if ($fields['deductible_amount'] > $max_amount) {
      $errors['deductible_amount'] = ts('Amount can not be more than %1', [1 => $max_amount]);
      return $errors;
    }
    return TRUE;
  }
  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  protected function get_renderable_element_names() {
    /*
     * The _elements list includes some items which should not be
     * auto-rendered in the loop -- such as "qfKey" and "buttons".  These
     * items don't have labels.  We'll identify renderable by filtering on
     * the 'label'.
     */
    $element_names = [];
    foreach ($this->_elements as $element) {
      $label = $element->getLabel();
      if (!empty($label)) {
        $element_names[] = $element->getName();
      }
    }
    return $element_names;
  }
  /**
   * Function to set the form elements
   * 
   * @access protected
   */
  protected function add_form_elements() {
    $this->add('hidden', 'id', ts('OppgaveID'));
    $this->add('hidden', 'oppgave_year', ts('Year'));
    if ($this->_action != CRM_Core_Action::ADD) {
      $this->add('hidden', 'contact_id', ts('ContactID'));
    }
    $this->add('select', 'donor_type', ts('Donor Type'), $this->_donor_type_list, true);
    $this->add('text', 'donor_name', ts('Donor Name'), ['size' => CRM_Utils_Type::HUGE]);
    $this->add('text', 'donor_number', ts('Donor Number'));
    $this->add('text', 'deductible_amount', ts('Amount'));
    $this->addButtons([
      ['type' => 'next', 'name' => ts('Save'), 'isDefault' => true,],
      ['type' => 'cancel', 'name' => ts('Cancel')]]);
  }
  /**
   * Function to set the required option lists
   * 
   * @access protected
   */
  protected function set_option_lists() {
    $this->_donor_type_list = ['Person', 'Organisasjon'];
  }
  /**
   * Function to set the form header
   * 
   * @return string $form_header
   * @access protected
   */
  protected function set_form_header() {
    $form_header = 'Donor oppgave year '.$this->_oppgave_year;
    $year_data = CRM_Oppgavexml_BAO_Skatteinnberetninger::get_values(
      ['year' => $this->_oppgave_year]);
    if (!empty($year_data) && isset($year_data[$this->_oppgave_year]['status_id'])) {
      $form_header .= ' with status ' .CRM_Oppgavexml_OptionGroup::get_status_option_label(
        $year_data[$this->_oppgave_year]['status_id']);
    }
    return $form_header;
  }
  /**
   * Function to save the oppgave donor record with the last modified user
   * and last modified date
   * 
   * @param array $values
   * @access protected
   */
  protected function save_oppgave($values) {
    $fields = CRM_Oppgavexml_DAO_Oppgave::fields();
    foreach ($fields as $field) {
      if (isset($values[$field['name']])) {
        $params[$field['name']] = $values[$field['name']];
      }
    }
    $params['last_modified_date'] = date('Ymd');
    if ($values['donor_type'] == 1) {
      $params['donor_type'] = 'Organisasjon';
    } else {
      $params['donor_type'] = 'Person';
    }
    if ($params['id'] == 0) {
      unset($params['id']);
    }
    $session = CRM_Core_Session::singleton();
    $params['last_modified_user_id'] = $session->get('userID');
    CRM_Oppgavexml_BAO_Oppgave::add($params);
  }
}
