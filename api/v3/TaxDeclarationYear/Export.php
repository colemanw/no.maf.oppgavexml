<?php
set_time_limit(0);
/**
 * TaxDeclarationYear.Export API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 * 
 * Copyright (C) 2014 Coöperatieve CiviCooP U.A. <http://www.civicoop.org>
 * Licensed to MAF Norge <http://www.maf.no> under the  AGPL-3.0
 */
function _civicrm_api3_tax_declaration_year_export_spec(&$spec) {
  $spec['year']['api.required'] = 1;
}

/**
 * TaxDeclarationYear.Export API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws CRM_Core_Exception
 */
function civicrm_api3_tax_declaration_year_export($params) {
  validate_params($params);
  $xml = new CRM_Oppgavexml_ExportYear();
  $xml->export_year($params['year']);
  $file_name = $xml->get_file_name();
  /*
   * set status of year to exported
   */
  $query = ' UPDATE civicrm_skatteinnberetninger SET status_id = 3 WHERE year = %1';
  $query_params = [1 => [$params['year'], 'Positive']];
  CRM_Core_DAO::executeQuery($query, $query_params);
  $return_values = ['File succesfully exported to '.$file_name];
  return civicrm_api3_create_success($return_values, $params, 'TaxDeclarationYear', 'Export');
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
function validate_params($params) {
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
