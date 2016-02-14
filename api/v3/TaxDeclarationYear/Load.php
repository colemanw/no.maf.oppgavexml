<?php
set_time_limit(0);
/**
 * TaxDeclarationYear.Load API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_tax_declaration_year_load_spec(&$spec) {
  $spec['year']['api_required'] = 1;
}
/**
 * TaxDeclarationYear.Load API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 */
function civicrm_api3_tax_declaration_year_load($params) {
  $logger = new CRM_Oppgavexml_OppgaveLogger();
  validateParams($params);
  $dao = getRelevantContacts($params['year']);

  if ($dao->N == 0) {
    resetProcessed($params['year']);
    $returnValues = array('All contacts for tax year '.$params['year'].' processed');
    $logger->logMessage('Info', 'No more contacts to be processed found for year '.$params['year']);
  } else {
    $returnValues = array('Batch of 1000 contacts for tax year '.$params['year'].' processed, you need to do more runs!');
    $logger->logMessage('Info', 'Batch of 1000 contacts processed for year '.$params['year']);
  }
  while ($dao->fetch()) {
    setProcessed($params['year'], $dao->contact_id);
    $logger->logMessage('Info', 'Contact '.$dao->contact_id.' with total deductible amount '.$dao->deductible_amount
      .' read, will now be checked');
    createContactOppgave($params, $dao, $logger);
  }
  createSkatteinnberetninger($params);
  return civicrm_api3_create_success($returnValues, $params, 'TaxDeclarationYear', 'Load');
}
/**
 * Function to retrieve all required contacts with their total deductible amount
 * 
 * @param int $year
 * @return object $dao
 */
function getRelevantContacts($year) {
  $config = CRM_Oppgavexml_Config::singleton();
  $startDate = $year.'-01-01 00:00:00';
  $endDate = $year.'-12-31 23:59:59';
  $minAmount = $config->get_min_deductible_amount();

  $query = 'SELECT  a.contact_id, receive_date, SUM(total_amount - non_deductible_amount) AS deductible_amount,
    b.contact_id AS loaded_contact
    FROM civicrm_contribution a LEFT JOIN civicrm_oppgave_processed b ON a.contact_id = b.contact_id
    AND oppgave_year = %1
    WHERE receive_date BETWEEN %2 AND %3  AND contribution_status_id = %4 AND b.contact_id IS NULL
    GROUP BY a.contact_id HAVING SUM(total_amount - non_deductible_amount) >= %5 LIMIT 1000';
  $params = array(
    1 => array($year, 'Positive'),
    2 => array($startDate, 'String'),
    3 => array($endDate, 'String'),
    4 => array(1, 'Positive'),
    5 => array($minAmount, 'Integer'));

  $dao = CRM_Core_DAO::executeQuery($query, $params);
  return $dao;
}
/**
 * Function to create contact oppgave if params valid
 * 
 * @param array $params
 * @param object $dao
 * @param object $logger
 */
function createContactOppgave($params, $dao, $logger) {
  if (!contactExistsInOppgave($dao->contact_id, $params['year'])) {
    $createParams = setOppgaveParams($params['year'], $dao, $logger);
    if (!empty($createParams)) {
      $query = 'INSERT INTO civicrm_oppgave (oppgave_year, contact_id, donor_type, '
        . 'donor_name, donor_number, deductible_amount, loaded_date) '
        . 'VALUES(%1, %2, %3, %4, %5, %6, %7)';
      CRM_Core_DAO::executeQuery($query, $createParams);
      $logger->logMessage('Info', 'Contact '.$dao->contact_id.' added to tax file civicrm_oppgave for '.$params['year']);
    }
  } else {
    $logger->logMessage('Info', 'Contact '.$dao->contact_id.' already exists in tax file civicrm_oppgave for '.$params['year']
      .', contact ignored');
  }
}
/**
 * Function to check if contact already exists in file for year
 * 
 * @param int $contactId
 * @param int $oppgaveYear
 * @return boolean
 */
function contactExistsInOppgave($contactId, $oppgaveYear) {
  $query = 'SELECT COUNT(*) AS donor_count FROM civicrm_oppgave WHERE contact_id = %1 '
    . 'AND oppgave_year = %2';
  $params = array(
    1 => array($contactId, 'Positive'),
    2 => array($oppgaveYear, 'Positive'));
  $dao = CRM_Core_DAO::executeQuery($query, $params);
  if ($dao->fetch()) {
    if ($dao->donor_count > 0) {
      return TRUE;
    }
  }
  return FALSE;
}
/**
 * Function to create params list for create oppgave
 * 
 * @param int $year
 * @param object $dao
 * @param object $logger
 * @return array $params
 */
function setOppgaveParams($year, $dao, $logger) {
  $params = array();
  $config = CRM_Oppgavexml_Config::singleton();
  $minAmount = $config->get_min_deductible_amount();
  $maxAmount = $config->get_max_deductible_amount();
  if ($dao->deductible_amount >= $minAmount) {
    if ($dao->deductible_amount > $maxAmount) {
      $dao->deductible_amount = $maxAmount;
    }
    $logger->logMessage('Info', 'Deductible Amount set to '.$dao->deductible_amount.' for contact '.$dao->contact_id
      .', year '.$year);
    $donorData = getDonorData($dao->contact_id);
    if (validateData($donorData) == TRUE) {
      $params = array(
        1 => array($year, 'Positive'),
        2 => array($dao->contact_id, 'Positive'),
        3 => array($donorData['type'], 'String'),
        4 => array($donorData['name'], 'String'),
        5 => array($donorData['number'], 'String'),
        6 => array($dao->deductible_amount, 'Money'),
        7 => array(date('Ymd'), 'Date'));
    } else {
      $logger->logMessage('Error', 'No person/organisasjonsnummer found for contact '.$dao->contact_id.', year '.$year);
    }
  }
  return $params;
}
/**
 * Function to get the donor data
 * @param int $contactId
 * @return array $donorData
 */
function getDonorData($contactId) {
  $params = array('id' => $contactId);
  try {
    $contactData = civicrm_api3('Contact', 'Getsingle', $params);
    $donorData = pullDonorData($contactData);
  } catch (CiviCRM_API3_Exception $ex) {
    $donorData = array();
  }
  return $donorData;
}
/**
 * Function to pull donor data from api contact getsingle result array
 * 
 * @param array $contactData
 * @return array $donorData
 */
function pullDonorData($contactData) {
  $donorData = array();
  $donorData['name'] = $contactData['display_name'];
  $customData = getCustomData($contactData['id']);
  switch ($contactData['contact_type']) {
    case 'Individual':
      $donorData['type'] = 'Person';
      $donorData['number'] = $customData['personsnummer'];
      break;
    case 'Household':
      $donorData['type'] = 'Husholdning';
      $donorData['number'] = $customData['personsnummer'];
    break;
    case 'Organization':
      $donorData['type'] = 'Organisasjon';
      $donorData['number'] = $customData['organisasjonsnummer'];
    break;
  }
  return $donorData;
}
/**
 * Function to get custom data for person/organisasjonsnummer
 * 
 * @param int $contactId
 * @return array $customData
 */
function getCustomData($contactId) {
  $config = CRM_Oppgavexml_Config::singleton();
  $tableName = $config->get_contact_custom_group_table();
  $columnPerson = $config->get_personsnummer_custom_field_column();
  $columnOrg = $config->get_organisasjonsnummer_custom_field_column();
  $query = ' SELECT * FROM '.$tableName.' WHERE entity_id = %1';
  $params = array(1 => array($contactId, 'Positive'));
  $dao = CRM_Core_DAO::executeQuery($query, $params);
  if ($dao->fetch()) {
    $customData['personsnummer'] = $dao->$columnPerson;
    $customData['organisasjonsnummer'] = $dao->$columnOrg;
  } else {
    $customData['personsnummer'] = '';
    $customData['organisasjonsnummer'] = '';
  }
  return $customData;
}
/**
 * Function to update the status of the tax declaration year
 * 
 * @param array $params
 */
function createSkatteinnberetninger($params) {
  $query = 'REPLACE INTO civicrm_skatteinnberetninger (year, status_id) VALUES(%1, %2)';
  $queryParams = array(
    1 => array($params['year'], 'Positive'),
    2 => array(2, 'Positive'));
  CRM_Core_DAO::executeQuery($query, $queryParams);
}
/**
 * Function to validate incoming params
 * 
 * @param array $params
 * @throws API_Exception when no param year found
 * @throws API_Exception when param year is empty
 * @throws API_Exception when param year is not 4 digits long
 * @throws API_Exception when param year is not numeric
 */
function validateParams(&$params) {
  if (!array_key_exists('year', $params)) {
    throw new API_Exception('Year is a mandatory param but is not found in passed params');
  }
  if (empty($params['year'])) {
    throw new API_Exception('Param year can not be empty');
  }
  if (strlen($params['year']) != 4) {
    throw new API_Exception('Year has to have 4 digits');
  }
  if (!is_numeric($params['year'])) {
    throw new API_Exception('Year has to be numeric');
  }
}
/**
 * Function to set processed for contacts and year
 *
 * @param int $year
 * @param int $contactId
 */
function setProcessed($year, $contactId) {
  $query = "INSERT INTO civicrm_oppgave_processed (contact_id, oppgave_year) VALUES(%1, %2)";
  $params = array(
    1 => array($contactId, 'Integer'),
    2 => array($year, 'Integer')
  );
  CRM_Core_DAO::executeQuery($query, $params);
}

function resetProcessed($year) {
  $query = "DELETE FROM civicrm_oppgave_processed WHERE oppgave_year = %1";
  CRM_Core_DAO::executeQuery($query, array(1 => array($year, 'Integer')));
}

/**
 * Function to check if the contact has a personsnummer or organisationsnummer
 *
 * @param $donorData
 * @return boolean
 */
function validateData($donorData) {
  if (empty($donorData['number'])) {
    return FALSE;
  } else {
    return TRUE;
  }
}