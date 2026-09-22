<?php

require_once 'oppgavexml.civix.php';

/**
 * Implementation of hook_civicrm_config
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function oppgavexml_civicrm_config(&$config) {
  _oppgavexml_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function oppgavexml_civicrm_xmlMenu(&$files) {
  _oppgavexml_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function oppgavexml_civicrm_install() {
  return _oppgavexml_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function oppgavexml_civicrm_uninstall() {
  return _oppgavexml_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function oppgavexml_civicrm_enable() {
  CRM_Oppgavexml_OptionGroup::create_status_option_group();
  return _oppgavexml_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function oppgavexml_civicrm_disable() {
  return _oppgavexml_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function oppgavexml_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _oppgavexml_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function oppgavexml_civicrm_managed(&$entities) {
  return _oppgavexml_civix_civicrm_managed($entities);
}

/**
 * Implementation of hook_civicrm_caseTypes
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function oppgavexml_civicrm_caseTypes(&$caseTypes) {
  _oppgavexml_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implementation of hook_civicrm_alterSettingsFolders
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function oppgavexml_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _oppgavexml_civix_civicrm_alterSettingsFolders($metaDataFolders);
}
/**
 * Implementation of hook civicrm_navigationMenu
 * 
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 */
function oppgavexml_civicrm_navigationMenu( &$params ) {
  $tax_year_list = [
    'name'          =>  ts('Skatteinnberetninger'),
    'url'           =>  CRM_Utils_System::url('civicrm/taxyearlist', '', true),
    'permission'    => 'administer CiviCRM',
  ];
  _oppgavexml_civix_insert_navigation_menu($params, 'Contributions', $tax_year_list);
}
/**
 * Implementation of hook civicrm_tabs to add a tab for Arsoppgave
 * 
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_tabs
 */
function oppgavexml_civicrm_tabs(&$tabs, $contact_id) {
  $oppgave_count = CRM_Oppgavexml_BAO_Oppgave::get_contact_count($contact_id);
  $oppgave_url = CRM_Utils_System::url('civicrm/oppgavelist','snippet=1&cid='.$contact_id);
  $tabs[] = [
    'id'    => 'contact_oppgave',
    'url'       => $oppgave_url,
    'title'     => 'Årsoppgave',
    'weight'    => 99,
    'count'     => $oppgave_count];
}
