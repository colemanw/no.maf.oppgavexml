<?php
/**
 * BAO Skatteinnberetninger for dealing with tax declaration years
 * 
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * 
 * Copyright (C) 2014 Coöperatieve CiviCooP U.A. <http://www.civicoop.org>
 * Licensed to MAF Norge <http://www.maf.no> and CiviCRM under the AGPL-3.0
 */
class CRM_Oppgavexml_BAO_Skatteinnberetninger extends CRM_Oppgavexml_DAO_Skatteinnberetninger {
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
    $skatteinnberetninger = new CRM_Oppgavexml_BAO_Skatteinnberetninger();
    if (!empty($params)) {
      $fields = self::fields();
      foreach ($params as $key => $value) {
        if (isset($fields[$key])) {
          $skatteinnberetninger->$key = $value;
        }
      }
    }
    $skatteinnberetninger->find();
    while ($skatteinnberetninger->fetch()) {
      $row = [];
      self::storeValues($skatteinnberetninger, $row);
      $result[$row['year']] = $row;
    }
    return $result;
  }
  /**
   * Function to add or update skatteinnberetninger
   * 
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   * @param array $params 
   * @return array $result
   * @access public
   * @static
   */
  public static function add($params) {
    $result = [];
    if (empty($params)) {
      throw new Exception('Params can not be empty when adding or updating a tax declaration year');
    }
    $skatteinnberetninger = new CRM_Oppgavexml_BAO_Skatteinnberetninger();
    $fields = self::fields();
    foreach ($params as $key => $value) {
      if (isset($fields[$key])) {
        $skatteinnberetninger->$key = $value;
      }
    }
    $skatteinnberetninger->save();
    self::storeValues($skatteinnberetninger, $result);
    return $result;
  }
  /**
   * Function to get and save the last referanse for the year
   * 
   * @param int $year
   * @return string $referanse
   * @access public
   * @static
   */
  public static function get_new_referanse($year) {
    $referanse = '';
    $skatteinnberetninger = new CRM_Oppgavexml_BAO_Skatteinnberetninger();
    $skatteinnberetninger->year = $year;
    if ($skatteinnberetninger->find(true)) {
      $skatteinnberetninger->last_referanse++;
      $last_referanse = $skatteinnberetninger->last_referanse;
      //$skatteinnberetninger->update($year);
      $update = 'UPDATE '.$skatteinnberetninger->getTableName().
        ' SET last_referanse = %1 WHERE year = %2';
      $params = [
        1 => [$last_referanse, 'Positive'],
        2 => [$year, 'Positive']];
      CRM_Core_DAO::executeQuery($update, $params);
      $oppgave_config = CRM_Oppgavexml_Config::singleton();
      $referanse = $oppgave_config->get_sender_organisasjonsnummer().'-'.$year.'-'.$last_referanse;
    }
    return $referanse;
  }
}
