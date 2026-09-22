<?php
/**
 * BAO Oppgave for dealing with tax declaration contact records
 * 
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * 
 * Copyright (C) 2014 Coöperatieve CiviCooP U.A. <http://www.civicoop.org>
 * Licensed to MAF Norge <http://www.maf.no> and CiviCRM under the AGPL-3.0
 */
class CRM_Oppgavexml_BAO_Oppgave extends CRM_Oppgavexml_DAO_Oppgave {
  /**
   * Function to get values
   * 
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @return array $result found rows with data
   * @access public
   * @static
   */
  public static function get_values($params) {
    $result = [];
    $oppgave = new CRM_Oppgavexml_BAO_Oppgave();
    if (!empty($params)) {
      $fields = self::fields();
      foreach ($params as $key => $value) {
        if (isset($fields[$key])) {
          $oppgave->$key = $value;
        }
      }
    }
    $oppgave->find();
    while ($oppgave->fetch()) {
      $row = [];
      self::storeValues($oppgave, $row);
      $result[$row['id']] = $row;
    }
    return $result;
  }

  /**
   * Function to add or update oppgave
   * 
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @param array $params 
   * @return array $result
   * @access public
   * @static
   * @throws Exception when params invalid
   */
  public static function add($params) {
    $result = [];
    if (empty($params)) {
      throw new Exception('Params can not be empty when adding or updating a tax declaration contact record');
    }
    $oppgave = new CRM_Oppgavexml_BAO_Oppgave();
    $fields = self::fields();
    foreach ($params as $key => $value) {
      if (isset($fields[$key])) {
        $oppgave->$key = $value;
      }
    }
    $oppgave->save();
    self::storeValues($oppgave, $result);
    return $result;
  }
  /**
   * Function to delete a donor oppgave record by id
   * 
   * @param int $oppgave_id
   * @throws Exception when oppgave_id is empty
   */
  public static function delete_by_id($oppgave_id) {
    if (empty($oppgave_id)) {
      throw new Exception('oppgave_id can not be empty when attempting to delete a donor oppgave');
    }
    $oppgave = new CRM_Oppgavexml_BAO_Oppgave();
    $oppgave->id = $oppgave_id;
    $oppgave->delete();
    return;
  }
  /**
   * Function to count the number of records for contact_id
   * 
   * @param int $contact_id
   * @return int
   */
  public static function get_contact_count($contact_id) {
    $oppgave = new CRM_Oppgavexml_BAO_Oppgave();
    $oppgave->contact_id = $contact_id;
    return $oppgave->count();
  }
}
