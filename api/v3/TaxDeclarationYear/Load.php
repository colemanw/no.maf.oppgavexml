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
  $spec['year']['api.required'] = 1;
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
  oppgavexml_validateParams($params);

  $options = _civicrm_api3_get_options_from_params($params);
  $limit = 1000;
  if (isset($options['limit'])) {
    $limit = $options['limit'];
  }
  $dao = oppgavexml_getRelevantContacts($params['year'], $limit);

  if ($dao->N == 0) {
    oppgavexml_resetProcessed($params['year']);
    $returnValues = ['All contacts for tax year '.$params['year'].' processed'];
    $logger->logMessage('Info', 'No more contacts to be processed found for year '.$params['year']);
  } else {
    $returnValues = ['Batch of '.$limit.' contacts for tax year '.$params['year'].' processed, you need to do more runs!'];
    $logger->logMessage('Info', 'Batch of '.$limit.' contacts processed for year '.$params['year']);
  }
  while ($dao->fetch()) {
    oppgavexml_setProcessed($params['year'], $dao->contact_id);
    $logger->logMessage('Info', 'Contact '.$dao->contact_id.' with total deductible amount '.$dao->deductible_amount
      .' read, will now be checked');
    oppgavexml_createContactOppgave($params, $dao, $logger);
  }
  oppgavexml_createSkatteinnberetninger($params);
  $return = civicrm_api3_create_success($returnValues, $params, 'TaxDeclarationYear', 'Load');
  $return['count'] = $dao->N;
  return $return;
}
/**
 * Function to retrieve all required contacts with their total deductible amount
 * 
 * @param int $year
 * @return object $dao
 */
function oppgavexml_getRelevantContacts($year, $limit=1000) {
  $config = CRM_Oppgavexml_Config::singleton();
  $startDate = $year.'-01-01 00:00:00';
  $endDate = $year.'-12-31 23:59:59';
  $minAmount = $config->get_min_deductible_amount();

  $query = 'SELECT  a.contact_id, receive_date, SUM(total_amount - non_deductible_amount) AS deductible_amount,
    b.contact_id AS loaded_contact
    FROM civicrm_contribution a LEFT JOIN civicrm_oppgave_processed b ON a.contact_id = b.contact_id
    AND oppgave_year = %1
    WHERE receive_date BETWEEN %2 AND %3  AND contribution_status_id = %4 AND b.contact_id IS NULL
    GROUP BY a.contact_id HAVING SUM(total_amount - non_deductible_amount) >= %5 LIMIT %6';
  $params = [
    1 => [$year, 'Positive'],
    2 => [$startDate, 'String'],
    3 => [$endDate, 'String'],
    4 => [1, 'Positive'],
    5 => [$minAmount, 'Integer'],
    6 => [$limit, 'Integer']];

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
function oppgavexml_createContactOppgave($params, $dao, $logger) {
  oppgavexml_removeExistingContactInOppgave($dao->contact_id, $params['year']);
  $createParams = oppgavexml_setOppgaveParams($params['year'], $dao, $logger);
  if (!empty($createParams)) {
    $query = 'INSERT INTO civicrm_oppgave (oppgave_year, contact_id, donor_type, '
      . 'donor_name, donor_number, deductible_amount, loaded_date) '
      . 'VALUES(%1, %2, %3, %4, %5, %6, %7)';
    CRM_Core_DAO::executeQuery($query, $createParams);
    $logger->logMessage('Info', 'Contact '.$dao->contact_id.' added to tax file civicrm_oppgave for '.$params['year']);
  }
}
/**
 * Function to check if contact already exists in file for year
 * 
 * @param int $contactId
 * @param int $oppgaveYear
 * @return boolean
 */
function oppgavexml_removeExistingContactInOppgave($contactId, $oppgaveYear) {
  $query = 'DELETE FROM civicrm_oppgave WHERE contact_id = %1 '
    . 'AND oppgave_year = %2';
  $params = [
    1 => [$contactId, 'Positive'],
    2 => [$oppgaveYear, 'Positive']];
  $dao = CRM_Core_DAO::executeQuery($query, $params);
}

/**
 * Function to create params list for create oppgave
 * 
 * @param int $year
 * @param object $dao
 * @param object $logger
 * @return array $params
 */
function oppgavexml_setOppgaveParams($year, $dao, $logger) {
  $params = [];
  $config = CRM_Oppgavexml_Config::singleton();
  $minAmount = $config->get_min_deductible_amount();
  $maxAmount = $config->get_max_deductible_amount();
  if ($dao->deductible_amount >= $minAmount) {
    if ($dao->deductible_amount > $maxAmount) {
      $dao->deductible_amount = $maxAmount;
    }
    $logger->logMessage('Info', 'Deductible Amount set to '.$dao->deductible_amount.' for contact '.$dao->contact_id
      .', year '.$year);
    $donorData = oppgavexml_getDonorData($dao->contact_id);
    if (oppgavexml_validateData($donorData) == TRUE) {
      $params = [
        1 => [$year, 'Positive'],
        2 => [$dao->contact_id, 'Positive'],
        3 => [$donorData['type'], 'String'],
        4 => [$donorData['name'], 'String'],
        5 => [$donorData['number'], 'String'],
        6 => [$dao->deductible_amount, 'Money'],
        7 => [date('Ymd'), 'Date']];
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
function oppgavexml_getDonorData($contactId) {
  $params = ['id' => $contactId];
  try {
    $contactData = civicrm_api3('Contact', 'Getsingle', $params);
    $donorData = oppgavexml_pullDonorData($contactData);
  } catch (CRM_Core_Exception $ex) {
    $donorData = [];
  }
  return $donorData;
}
/**
 * Function to pull donor data from api contact getsingle result array
 * 
 * @param array $contactData
 * @return array $donorData
 */
function oppgavexml_pullDonorData($contactData) {
  $donorData = [];
  $donorData['name'] = $contactData['display_name'];
  $customData = oppgavexml_getCustomData($contactData['id'], $contactData['contact_type']);
  $donorData['number'] = $customData['nummer'];
  switch ($contactData['contact_type']) {
    case 'Individual':
      $donorData['type'] = 'Person';
      break;
    case 'Household':
      $donorData['type'] = 'Husholdning';
    break;
    case 'Organization':
      $donorData['type'] = 'Organisasjon';
    break;
  }
  return $donorData;
}
/**
 * Function to get custom data for person/organisasjonsnummer
 * 
 * @param int $contactId
 * @param string $contactType
 * @return array $customData
 */
function oppgavexml_getCustomData($contactId, $contactType) {
  $config = CRM_Oppgavexml_Config::singleton();
  // action depends on contact type
  if ($contactType == 'Organization') {
    $tableName = $config->getMafOrganisationCustomGroupTableName();
    $columnName = $config->getMafOrganisasjonsNummerColumnName();
  } else {
    $tableName = $config->getMafPersonCustomGroupTableName();
    $columnName = $config->getMafPersonNummerColumnName();
  }
  $query = ' SELECT '.$columnName.' FROM '.$tableName.' WHERE entity_id = %1';
  $params = [1 => [$contactId, 'Positive']];
  $dao = CRM_Core_DAO::executeQuery($query, $params);
  if ($dao->fetch()) {
    $customData['nummer'] = $dao->$columnName;
  } else {
    $customData['nummer'] = '';
  }
  return $customData;
}
/**
 * Function to update the status of the tax declaration year
 * 
 * @param array $params
 */
function oppgavexml_createSkatteinnberetninger($params) {
  $query = 'REPLACE INTO civicrm_skatteinnberetninger (year, status_id) VALUES(%1, %2)';
  $queryParams = [
    1 => [$params['year'], 'Positive'],
    2 => [2, 'Positive']];
  CRM_Core_DAO::executeQuery($query, $queryParams);
}
/**
 * Function to validate incoming params
 * 
 * @param array $params
 * @throws CRM_Core_Exception when no param year found
 * @throws CRM_Core_Exception when param year is empty
 * @throws CRM_Core_Exception when param year is not 4 digits long
 * @throws CRM_Core_Exception when param year is not numeric
 */
function oppgavexml_validateParams(&$params) {
  if (!array_key_exists('year', $params)) {
    throw new CRM_Core_Exception('Year is a mandatory param but is not found in passed params');
  }
  if (empty($params['year'])) {
    throw new CRM_Core_Exception('Param year can not be empty');
  }
  if (strlen($params['year']) != 4) {
    throw new CRM_Core_Exception('Year has to have 4 digits');
  }
  if (!is_numeric($params['year'])) {
    throw new CRM_Core_Exception('Year has to be numeric');
  }
}
/**
 * Function to set processed for contacts and year
 *
 * @param int $year
 * @param int $contactId
 */
function oppgavexml_setProcessed($year, $contactId) {
  $query = "INSERT INTO civicrm_oppgave_processed (contact_id, oppgave_year) VALUES(%1, %2)";
  $params = [
    1 => [$contactId, 'Integer'],
    2 => [$year, 'Integer']
  ];
  CRM_Core_DAO::executeQuery($query, $params);
}

function oppgavexml_resetProcessed($year) {
  $query = "DELETE FROM civicrm_oppgave_processed WHERE oppgave_year = %1";
  CRM_Core_DAO::executeQuery($query, [1 => [$year, 'Integer']]);
}

/**
 * Function to check if the contact has a personsnummer or organisationsnummer
 *
 * @param $donorData
 * @return boolean
 */
function oppgavexml_validateData($donorData) {
  if (empty($donorData['number'])) {
    return FALSE;
  } else {
    return TRUE;
  }
}
