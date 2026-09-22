<?php
/**
 * DAO Oppgave for tax declaration year/contact details
 * 
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * 
 * Copyright (C) 2014 Coöperatieve CiviCooP U.A. <http://www.civicoop.org>
 * Licensed to MAF Norge <http://www.maf.no> and CiviCRM under the AGPL-3.0
 */
class CRM_Oppgavexml_DAO_Oppgave extends CRM_Core_DAO {
  /**
   * static instance to hold the field values
   *
   * @var array
   * @static
   */
  static $_fields = null;
  static $_export = null;
  /**
   * empty definition for virtual function
   */
  static function getTableName() {
    return 'civicrm_oppgave';
  }
  /**
   * returns all the column names of this table
   *
   * @access public
   * @return array
   */
  static function &fields() {
    if (!(self::$_fields)) {
      self::$_fields = [
        'id' => [
          'name' => 'id',
          'type' => CRM_Utils_Type::T_INT,
          'required' => true
        ] ,
        'oppgave_year' => [
          'name' => 'oppgave_year',
          'type' => CRM_Utils_Type::T_STRING,
          'maxlength' => 4,
        ] ,
        'contact_id' => [
          'name' => 'contact_id',
          'type' => CRM_Utils_Type::T_INT,
        ],
        'donor_type' => [
          'name' => 'donor_type',
          'type' => CRM_Utils_Type::T_STRING,
          'maxlength' => 45,
        ],
        'donor_name' => [
          'name' => 'donor_name',
          'type' => CRM_Utils_Type::T_STRING,
          'maxlength' => 128,
        ],
        'donor_number' => [
          'name' => 'donor_number',
          'type' => CRM_Utils_Type::T_STRING,
          'maxlength' => 45,
        ],
        'deductible_amount' => [
          'name' => 'deductible_amount',
          'type' => CRM_Utils_Type::T_INT,
        ],
        'loaded_date' => [
          'name' => 'loaded_date',
          'type' => CRM_Utils_Type::T_DATE,
        ],
        'last_modified_date' => [
          'name' => 'last_modified_date',
          'type' => CRM_Utils_Type::T_DATE,
        ],
        'last_modified_user_id' => [
          'name' => 'last_modified_user_id',
          'type' => CRM_Utils_Type::T_INT,
        ],
        'last_exported_date' => [
          'name' => 'last_exported_date',
          'type' => CRM_Utils_Type::T_DATE,
        ],
      ];
    }
    return self::$_fields;
  }
  /**
   * Returns an array containing, for each field, the array key used for that
   * field in self::$_fields.
   *
   * @access public
   * @return array
   */
  static function &fieldKeys() {
    if (!(self::$_fieldKeys)) {
      self::$_fieldKeys = [
        'id' => 'id', 
        'oppgave_year' => 'oppgave_year',
        'contact_id' => 'contact_id',
        'donor_type' => 'donor_type',
        'donor_name' => 'donor_name',
        'donor_number' => 'donor_number',
        'deductible_amount' => 'deductible_amount',
        'loaded_date' => 'loaded_date',
        'last_modified_date' => 'last_modified_date',
        'last_modified_user_id' => 'last_modified_user_id',
        'last_exported_date' => 'last_exported_date'
      ];
    }
    return self::$_fieldKeys;
  }
  /**
   * returns the list of fields that can be exported
   *
   * @access public
   * return array
   * @static
   */
  static function &export($prefix = false)
  {
    if (!(self::$_export)) {
      self::$_export = [];
      $fields = self::fields();
      foreach($fields as $name => $field) {
        if (CRM_Utils_Array::value('export', $field)) {
          if ($prefix) {
            self::$_export['activity'] = & $fields[$name];
          } else {
            self::$_export[$name] = & $fields[$name];
          }
        }
      }
    }
    return self::$_export;
  }
}
