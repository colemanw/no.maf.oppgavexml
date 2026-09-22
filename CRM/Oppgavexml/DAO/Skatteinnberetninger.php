<?php
/**
 * DAO Skatteinnberetninger for tax declaration years
 * 
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * 
 * Copyright (C) 2014 Coöperatieve CiviCooP U.A. <http://www.civicoop.org>
 * Licensed to MAF Norge <http://www.maf.no> and CiviCRM under the AGPL-3.0
 */
class CRM_Oppgavexml_DAO_Skatteinnberetninger extends CRM_Core_DAO {
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
    return 'civicrm_skatteinnberetninger';
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
        'year' => [
          'name' => 'year',
          'type' => CRM_Utils_Type::T_STRING,
          'maxlength' => 4,
          'required' => true
        ] ,
        'status_id' => [
          'name' => 'status_id',
          'type' => CRM_Utils_Type::T_INT,
        ] ,
        'last_referanse' => [
          'name' => 'last_referanse',
          'type' => CRM_Utils_Type::T_STRING,
        ]
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
        'year' => 'year', 
        'status_id' => 'status_id',
        'last_referanse' => 'last_referanse'
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
