<?php
/**
 * Page Oppgavelist to list all donor oppgaves for a year or a contact
 * 
 * @author Erik Hommel <erik.hommel@civicoop.org>
 * 
 * Copyright (C) 2014 Coöperatieve CiviCooP U.A. <http://www.civicoop.org>
 * Licensed to MAF Norge <http://www.maf.no> under the  AGPL-3.0
 */
require_once 'CRM/Core/Page.php';

class CRM_Oppgavexml_Page_OppgaveList extends CRM_Core_Page {
  
  protected $_display_context = null;
  protected $_request_contact_id = null;
  protected $_request_tax_year = null;
  protected $_request_donor_type = null;
  /**
   * Standard run function created when generating page with Civix
   * 
   * @access public
   */
  function run() {
    $this->set_page_configuration();
    $display_oppgaves = $this->get_oppgaves();
    $this->assign('donor_type_options', ['All', 'Husholdning', 'Organisasjon', 'Person']);
    $this->assign('oppgaves', $display_oppgaves);
    parent::run();
  }
  /**
   * Function to get the data from civicrm_oppgave
   * 
   * @return array $oppgaves
   * @access protected
   */
  protected function get_oppgaves() {
    $params = $this->set_filter_params();
    $oppgaves = CRM_Oppgavexml_BAO_Oppgave::get_values($params);
    foreach ($oppgaves as $oppgave_id => $oppgave) {
      $oppgaves[$oppgave_id]['actions'] = $this->set_row_actions($oppgave);
      if (!empty($oppgave['last_modified_user_id'])) {
        $oppgaves[$oppgave_id]['last_modified_user'] = $this->get_contact_name($oppgave['last_modified_user_id']);
      }
    }
    return $oppgaves;
  }
  protected function get_contact_name($contact_id) {
    $params = [
      'id' => $contact_id,
      'return' => 'display_name'
    ];
    $name = civicrm_api3('Contact', 'Getvalue', $params);
    return $name;
  }
  /**
   * Function to set the row action urls and links for each row
   * 
   * @param int $oppgave
   * @return array $page_actions
   * @access protected
   */
  protected function set_row_actions($oppgave) {
    $page_actions = [];
    $edit_url = CRM_Utils_System::url('civicrm/oppgave', 'action=update&oid='.
      $oppgave['id'].'&year='.$oppgave['oppgave_year']);
    $page_actions[] = '<a class="action-item" title="Edit" href="'.$edit_url.'">Edit</a>';
    $delete_url = CRM_Utils_System::url('civicrm/oppgave', 'action=delete&oid='.
      $oppgave['id'].'&year='.$oppgave['oppgave_year']);
    $page_actions[] = '<a class="action-item" title="Delete" href="'.$delete_url.'">Delete</a>';
    return $page_actions;
  }
  /**
   * Function to set the params for the retrieval of oppgaves based on
   * properties loaded from request
   * 
   * @return array $params
   * @access protected
   */
  protected function set_filter_params() {
    $params = [];
    if (!empty($this->_request_contact_id)) {
      $params['contact_id'] = $this->_request_contact_id;
    }
    if (!empty($this->_request_donor_type)) {
      $params['donor_type'] = $this->_request_donor_type;
    }
    if (!empty($this->_request_tax_year)) {
      $params['oppgave_year'] = $this->_request_tax_year;
    }
    return $params;
  }
  /**
   * Function to set the page configuration
   * 
   * @access protected
   */
  protected function set_page_configuration() {
    CRM_Utils_System::setTitle(ts('Donoroppgave'));
    $this->retrieve_request_params();
    $this->assign('display_type', $this->_display_context);
    $this->assign('add_url', CRM_Utils_System::url('civicrm/oppgave', 
      'action=add&cid='.$this->_request_contact_id.'&year='.
      $this->_request_tax_year, true));  
    $this->assign('baase', CRM_Utils_System::url('civicrm/oppgavelist', 
      'cid='.$this->_request_contact_id.'&year='.$this->_request_tax_year, true));  
    $session = CRM_Core_Session::singleton();
    $session->pushUserContext(CRM_Utils_System::url('civicrm/oppgavelist', 'year='.$this->_request_tax_year));
  }
  /**
   * Function to retrieve the params from the request and strore them in
   * properties
   * 
   * @access protected
   */
  protected function retrieve_request_params() {
    $snippet = CRM_Utils_Request::retrieve('snippet', 'Positive');
    $this->_request_contact_id = CRM_Utils_Request::retrieve('cid', 'Positive');
    $this->_request_tax_year  = CRM_Utils_Request::retrieve('year', 'Positive');
    $this->_request_donor_type = CRM_Utils_Request::retrieve('dt', 'String');
    if (empty($this->_request_contact_id)) {
      $this->assign('request_contact_id', 'All');    
    } else {
      $this->assign('request_contact_id', $this->_request_contact_id);
    }
    if (empty($this->_request_donor_type)) {
      $this->assign('request_donor_type', 'All');
    } else {
      $this->assign('request_donor_type', $this->_request_donor_type);
    }
    $this->assign('request_tax_year', $this->_request_tax_year);
    if (!empty($snippet)) {
      $this->_display_context = 'contact';
    } else {
      $this->_display_context = 'year';
    }
  }
}
