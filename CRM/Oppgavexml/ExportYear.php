<?php
/**
 * Class to export year to xml file
 * as far as the default Case Relations are concerned for PUM
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * 
 * Copyright (C) 2014 Coöperatieve CiviCooP U.A. <http://www.civicoop.org>
 * Licensed to MAF Norge <http://www.maf.no> under the  AGPL-3.0
 */
class CRM_Oppgavexml_ExportYear extends CRM_Oppgavexml_Config {
  protected $_tax_year = null;
  protected $_xml_file_name = null;
  protected $_xml = null;
  protected $_total_count = null;
  protected $_total_sum = null;
  /**
   * Function to export year to xml file
   * 
   * @param type $year
   * @access public
   */
  public function export_year($year) {
    $this->_tax_year = $year;
    $this->_total_count = 0;
    $this->_total_sum = 0;
    $new_referanse = CRM_Oppgavexml_BAO_Skatteinnberetninger::get_new_referanse($this->_tax_year);
    $this->_xml = new SimpleXMLElement('<?xml version="'.$this->_xml_version
      .'" encoding="'.$this->_xml_encoding.'"?>'
      . '<melding xmlns="'.$this->_melding_xmlns.'" xmlns:xsi="'.$this->_melding_xmlns_xsi
      .'" xsi:schemaLocation="'.$this->_melding_xsi_schema_location.'"></melding>');
    $leveranse = $this->_xml->addChild('leveranse');
    $leveranse->addChild('kildesystem', $this->_sender_kilde_system);
    $this->add_oppgavegiver($leveranse);
    $leveranse->addChild('inntektsaar', $this->_tax_year);
    $leveranse->addChild('oppgavegiversLeveranseReferanse', $new_referanse);
    $leveranse->addChild('leveransetype', $this->_leveranse_type);
    $this->add_donor_lines($leveranse);
    $this->add_footer($leveranse);
    $this->set_file_name();
    $this->_xml->asXML($this->_xml_file_name);
    $this->set_exported_date();
  }
  protected function set_file_name() {
    $this->_xml_file_name = $this->_xml_file_path.'skatteinnberetninger_'.$this->_tax_year.'.xml';
  }
  /**
   * Function to add footer to XML object
   * 
   * @param object $leveranse
   * @access protected
   */
  protected function add_footer(&$leveranse) {
    $oppgaveoppsummering = $leveranse->addChild('oppgaveoppsummering');
    $oppgaveoppsummering->addChild('antallOppgaver', $this->_total_count);
    $oppgaveoppsummering->addChild('sumBeloep', $this->_total_sum);
  }
  /**
   * Function to retrieve donor lines and add each one to xml object
   * 
   * @param object $leveranse
   * @access protected
   */
  protected function add_donor_lines(&$leveranse) {
    $params = [
      'oppgave_year' => $this->_tax_year];
    $donors = CRM_Oppgavexml_BAO_Oppgave::get_values($params);
    foreach ($donors as $donor) {
      if (empty($donor['last_exported_date'])) {
        if (!empty($donor['donor_number'])) {
          $this->write_oppgave_element($donor, $leveranse);
        }
      }
    }
  }
  /**
   * Function to add oppgave element for donor
   * 
   * @param array $donor
   * @param object $leveranse
   * @access protected
   */
  protected function write_oppgave_element($donor, &$leveranse) {
    $oppgave = $leveranse->addChild('oppgave');
    $eier = $oppgave->addChild('oppgaveeier');
    if ($donor['donor_type'] == 'Organisasjon') {
      $eier->addChild('organisasjonsnummer', $donor['donor_number']);
    } else {
      $eier->addChild('foedselsnummer', $donor['donor_number']);      
    }
    $eier->addChild('navn', $donor['donor_name']);
    $oppgave->addChild('beloep', $donor['deductible_amount']);
    $this->_total_count++;
    $this->_total_sum += $donor['deductible_amount'];
  }
  /**
   * Function to add the oppgavegiver details to the xml
   * 
   * @param object $leveranse
   * @access protected
   */
  protected function add_oppgavegiver(&$leveranse) {
    $oppgavegiver = $leveranse->addChild('oppgavegiver');
    $oppgavegiver->addChild('organisasjonsnummer', $this->_sender_organisasjonsnummer);
    $oppgavegiver->addChild('organisasjonsnavn', $this->_sender_organisasjonsnavn);
    $kontakt = $oppgavegiver->addChild('kontaktinformasjon');
    $kontakt->addChild('navn', $this->_sender_kontakt_navn);
    $kontakt->addChild('telefonnummer', $this->_sender_kontakt_telefon);
    $kontakt->addChild('varselEpostadresse', $this->_sender_kontakt_epost);
    $kontakt->addChild('varselSmsMobilnummer', $this->_sender_kontakt_mobil);
  }
  /**
   * Function to retrieve the xml file name
   * 
   * @return string $this->_xml_file_name
   * @access public
   */
  public function get_file_name() {
    return $this->_xml_file_name;
  }
  /**
   * Function to write the header with the sending org info
   * 
   * @access protected
   */
  protected function write_header() {
    $leveranse = $this->_xml_object->addChild('leveranse');
    $leveranse->addChild('kildesystem', $this->_sender_kilde_system);
    $this->_xml_object->addChild('oppgavegiver');
    $this->_xml_object->addChild();
  }
  protected function set_exported_date() {
    $export_date = date('Ymd');
    $query = 'UPDATE civicrm_oppgave SET last_exported_date = %1
      WHERE oppgave_year = %2 AND last_exported_date = NULL';
    $params = [
      1 => [$export_date, 'Date'],
      2 => [$this->_tax_year, 'Positive']
    ];
    CRM_Core_DAO::executeQuery($query, $params);
  }
}
