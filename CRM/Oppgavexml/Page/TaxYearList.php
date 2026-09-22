<?php
/**
 * Page TaxYearList to list all skatteinnberetninger tax declaration years
 * 
 * @author Erik Hommel <erik.hommel@civicoop.org>
 * 
 * Copyright (C) 2014 Coöperatieve CiviCooP U.A. <http://www.civicoop.org>
 * Licensed to MAF Norge <http://www.maf.no> under the  AGPL-3.0
 */
require_once 'CRM/Core/Page.php';

class CRM_Oppgavexml_Page_TaxYearList extends CRM_Core_Page {
  
  function run() {
    $this->set_page_configuration();
    $skatteinnberetninger = $this->get_skatteinnberetninger();
    $this->assign('skatteinnberetninger', $skatteinnberetninger);
    $this->assign('current_year', date('Y'));
    parent::run();
  }
  /**
   * Function to set actions for row
   * 
   * @param int $tax_year
   * @param int $status_id
   * @return array
   * @access protected
   */
  protected function set_row_actions($tax_year, $status_id) {
    $page_actions = [];
    $status_label = CRM_OppgaveXml_OptionGroup::get_status_option_label($status_id);
    $manage_url = CRM_Utils_System::url('civicrm/oppgavelist', 'year='.$tax_year.'&cid=&dt=');
    $reload_url = CRM_Utils_System::url('civicrm/oppgave/load', 'year='.$tax_year);
    $delete_url = CRM_Utils_System::url('civicrm/skatteinnberetninger', 'action=delete&year='.$tax_year, true);
    if ($status_label == ' New') {
      $page_actions[] = '<a class="action-item" title="Delete" href="'.$delete_url.'">Delete</a>';      
    } else {
      $page_actions[] = '<a class="action-item" title="Manage" href="'.$manage_url.'">Manage</a>';
    }
    $page_actions[] = '<a class="action-item" title="Reload" href="'.$reload_url.'">Reload</a>';
    return $page_actions;
  }
  /**
   * Function to set the page configuration initially
   * 
   * @access protected
   */
  protected function set_page_configuration() {
    CRM_Utils_System::setTitle(ts('Manage Tax Declaration Years'));
  }
  /**
   * Function to get skatteinnberetninger
   * 
   * @return array $skatteinnberetninger
   * @access protected
   */
  protected function get_skatteinnberetninger() {
    $skatteinnberetninger = CRM_Oppgavexml_BAO_Skatteinnberetninger::get_values([]);
    foreach ($skatteinnberetninger as $key => $values) {
      $skatteinnberetninger[$key]['status'] = CRM_Oppgavexml_OptionGroup::get_status_option_label($values['status_id']);
      $skatteinnberetninger[$key]['actions'] = $this->set_row_actions($key, $values['status_id']);
    }
    return $skatteinnberetninger;
  }
}
