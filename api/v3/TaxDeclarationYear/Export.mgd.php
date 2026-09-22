<?php
// This file declares a managed database record of type "Job".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// http://wiki.civicrm.org/confluence/display/CRMDOC42/Hook+Reference
return [
  0 => 
  [
    'name' => 'Cron:TaxDeclarationYear.Export',
    'entity' => 'Job',
    'params' => 
    [
      'version' => 3,
      'name' => 'Skatteinnberetninger Export',
      'description' => 'Create XML file for Skatteinberetninger',
      'run_frequency' => 'Daily',
      'api_entity' => 'TaxDeclarationYear',
      'api_action' => 'Export',
      'parameters' => 'year = [2013, year to be exported] required',
      'is_active' => 0
    ],
  ],
];
