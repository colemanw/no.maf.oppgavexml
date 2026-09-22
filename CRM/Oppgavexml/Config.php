<?php
/**
 * Class following Singleton pattern for specific extension configuration
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 */
class CRM_Oppgavexml_Config {
  /*
   * singleton pattern
   */
  static private $_singleton = NULL;
  /*
   * property to hold the name of the folder where the exported xml file is put
   */
  protected $_xml_file_path = null;
  /*
   * property to hold the maximum amount deductible per contact
   */
  protected $_max_deductible_amount = null;
  /*
   * property to hold the minimum amount deductible per contact
   */
  protected $_min_deductible_amount = null;
  /*
   * property to hold the tax declaration file status
   */
  protected $_tax_declaration_status = [];
  /*
   * property for custom group and custom fields for personsnummer and organisasjonsnummer
   */
  private $_mafPersonCustomGroupTableName = NULL;
  private $_mafOrganisationCustomGroupTableName = NULL;
  private $_mafPersonNummerColumnName = NULL;
  private $_mafOrganisasjonsNummerColumnName = NULL;
  /*
   * properties to hold the information about the sending organisation
   * and the file type
   */
  protected $_sender_kilde_system = null;
  protected $_sender_organisasjonsnummer = null;
  protected $_sender_organisasjonsnavn = null;
  protected $_sender_kontakt_navn = null;
  protected $_sender_kontakt_telefon = null;
  protected $_sender_kontakt_epost = null;
  protected $_sender_kontakt_mobil = null;
  protected $_leveranse_type = null;
  /*
   * properties about the xml file and
   */
  protected $_xml_version = null;
  protected $_xml_encoding = null;
  protected $_melding_xmlns = null;
  protected $_melding_xmlns_xsi = null;
  protected $_melding_xsi_schema_location = null;
  /**
   * Constructor
   */
  function __construct() {
    $this->set_xml_file_path(); 
    $this->set_max_deductible_amount();
    $this->set_min_deductible_amount();
    $this->set_sender_info();
    $this->set_xml_headers();
    $this->setCustomGroupAndField('maf_person', '_mafPersonCustomGroupTableName', 'personnummer', '_mafPersonNummerColumnName');
    $this->setCustomGroupAndField('maf_organisation', '_mafOrganisationCustomGroupTableName', 'organisasjonsnummer', '_mafOrganisasjonsNummerColumnName');
  }

  /**
   * Getter for maf person custom group table name
   *
   * @return null
   */
  public function getMafPersonCustomGroupTableName() {
    return $this->_mafPersonCustomGroupTableName;
  }

  /**
   * Getter for maf organisation custom group table name
   *
   * @return null
   */
  public function getMafOrganisationCustomGroupTableName() {
    return $this->_mafOrganisationCustomGroupTableName;
  }

  /**
   * Getter for maf person nummer custom field column name
   *
   * @return null
   */
  public function getMafPersonNummerColumnName() {
    return $this->_mafPersonNummerColumnName;
  }

  /**
   * Getter for maf organisasjons nummer custom field column name
   * @return null
   */
  public function getMafOrganisasjonsNummerColumnName() {
    return $this->_mafOrganisasjonsNummerColumnName;
  }

  /**
   * Function to return the sender kilde system
   * 
   * @return string
   * @access public
   */
  public function get_sender_kilde_system() {
    return $this->_sender_kilde_system;
  }
  /**
   * Function to return the sender organisasjonsnummer
   * (organisation number)
   * 
   * @return string
   * @access public
   */
  public function get_sender_organisasjonsnummer() {
    return $this->_sender_organisasjonsnummer;
  }
  /**
   * Function to return the sender organisasjonsnavn 
   * (organisation name)
   * 
   * @return string
   * @access public
   */
  public function get_sender_organisasjonsnavn() {
    return $this->_sender_organisasjonsnavn;
  }
  /**
   * Function to return the sender kontakt navn
   * (contactperson name)
   * 
   * @return string
   * @access public
   */
  public function get_sender_kontakt_navn() {
    return $this->_sender_kontakt_navn;
  }
  /**
   * Function to return the sender kontakt telefon
   * (contactperson phone)
   * 
   * @return string
   * @access public
   */
  public function get_sender_kontakt_telefon() {
    return $this->_sender_kontakt_telefon;
  }
  /**
   * Function to return the sender kontakt epost
   * (contactperson email)
   * 
   * @return string
   * @access public
   */
  public function get_sender_kontakt_epost() {
    return $this->_sender_kontakt_epost;
  }
  /**
   * Function to return the sender kontakt mobil
   * (contactperson mobile)
   * 
   * @return string
   * @access public
   */
  public function get_sender_kontakt_mobil() {
    return $this->_sender_kontakt_mobil;
  }
  /**
   * Function to return the leveranse type
   * (file supplier type)
   * 
   * @return string
   * @access public
   */
  public function get_leveranse_type() {
    return $this->_leveranse_type;
  }
  /**
   * Function to return xml version for the xml file
   * 
   * @return string
   * @access public
   */
  public function get_xml_version() {
    return $this->_xml_version;
  }
  /**
   * Function to return the xml encoding for the xml file
   * 
   * @return string
   * @access public
   */
  public function get_xml_encoding() {
    return $this->_xml_encoding;
  }
  /**
   * Function to return the xmlns for the xml file
   * 
   * @return string
   * @access public
   */
  public function get_melding_xmlns() {
    return $this->_melding_xmlns;
  }
  /**
   * Function to return the xmlns:xsi for the xml file
   * 
   * @return string
   * @access public
   */
  public function get_melding_xmlns_xsi() {
    return $this->_melding_xmlns_xsi;
  }
  /**
   * Function to return the xsi:schemaLocation for the xml file
   * 
   * @return string
   * @access public
   */
  public function get_melding_xsi_schema_location() {
    return $this->_melding_xsi_schema_location;
  }
  /**
   * Function returns min deductible amount
   * 
   * @return int
   * @access public
   */
 public function get_min_deductible_amount() {
   return $this->_min_deductible_amount;
 }
  /**
   * Function returns max deductible amount
   * 
   * @return int
   * @access public
   */
  public function get_max_deductible_amount() {
    return $this->_max_deductible_amount;
  }
  /**
   * Function returns path for storing xml file
   * 
   * @return string
   * @access public
   */
  public function get_xml_file_path() {
    return $this->_xml_file_path;
  }
  /**
   * Function to return singleton object
   * 
   * @return object $_singleton
   * @access public
   * @static
   */
  public static function &singleton() {
    if (self::$_singleton === NULL) {
      self::$_singleton = new CRM_Oppgavexml_Config();
    }
    return self::$_singleton;
  }
  /**
   * Function to set the path for storign the xml file
   * 
   * @access protected
   */
  protected function set_xml_file_path() {
    $container = CRM_Extension_System::singleton()->getFullContainer();
    $resourcePath = $container->getPath('no.maf.generic').'/CRM/Generic/ConfigItems/resources/';

    $this->_xml_file_path = $container->getPath('no.maf.oppgavexml').'/export/';
  }
  /**
   * Function to set the maximum deductible amount
   * Any amount higher than this will be topped to this amount
   * 
   * @access protected
   */
  protected function set_max_deductible_amount() {
    $this->_max_deductible_amount = 40000;
  }
  /**
   * Function to set the minimum deductible amount
   * 
   * @access protected
   */
  protected function set_min_deductible_amount() {
    $this->_min_deductible_amount = 500;
  }
  /**
   * Function to set the sender information for the xml file
   * 
   * @access protected
   */
  protected function set_sender_info() {
    $this->_sender_kilde_system = 'CiviCRM';
    $this->_sender_organisasjonsnummer = '980421899';
    $this->_sender_organisasjonsnavn = 'Mission Aviation Fellowship Norge';
    $this->_sender_kontakt_navn = 'Clare Wilshaw';
    $this->_sender_kontakt_telefon = '45515358';
    $this->_sender_kontakt_mobil = '45515358';
    $this->_sender_kontakt_epost = 'clare@maf.no';
    $this->_leveranse_type = 'ordinaer';
  }
  /**
   * Function to set the xml header information
   * 
   * @access protected
   */
  protected function set_xml_headers() {
    $this->_xml_version = '1.0';
    $this->_xml_encoding = 'UTF-8';
    $this->_melding_xmlns = 'urn:ske:fastsetting:innsamling:gavefrivilligorganisasjon:v2';
    $this->_melding_xmlns_xsi = 'http://www.w3.org/2001/XMLSchema-instance';
    $this->_melding_xsi_schema_location = 'urn:ske:fastsetting:innsamling:gavefrivilligorganisasjon:v2 gavefrivilligorganisasjon_v2_0.xsd';
  }

  /**
   * Function to set custom data
   * 
   * @throws Exception when custom group or custom field not found
   */
  private function setCustomGroupAndField($customGroupName, $groupProperty, $customFieldName, $fieldProperty) {
    try {
      $customGroup = civicrm_api3('CustomGroup', 'getsingle', ['name' => $customGroupName,]);
    }
    catch (CRM_Core_Exception $ex) {
      throw new Exception('Could not find a custom group with name '.$customGroupName.' in '.__METHOD__
        .', contact your system administrator. Error from API CustomGroup getsingle : '.$ex->getMessage());
    }
    $this->$groupProperty = $customGroup['table_name'];
    try {
      $this->$fieldProperty = civicrm_api3('CustomField', 'getvalue', [
        'custom_group_id' => $customGroup['id'],
        'name' => $customFieldName,
        'return' => 'column_name',
      ]);
    }
    catch (CRM_Core_Exception $ex) {
      throw new Exception('Could not find a custom field with name '.$customFieldName.' within custom group '
        .$customGroupName.' in '.__METHOD__.', contact your system administrator. Error from API CustomField getvalue : '
        .$ex->getMessage());
    }
  }
}
