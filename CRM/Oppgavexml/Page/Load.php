<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

class CRM_Oppgavexml_Page_Load extends CRM_Core_Page {

  function run() {
    $year = CRM_Utils_Request::retrieve('year', 'Integer', CRM_Core_DAO::$_nullObject, false, date('Y'));

    //retrieve the queue
    $queue = CRM_Oppgavexml_LoadQueue::singleton()->getQueue();
    $runner = new CRM_Queue_Runner([
      'title' => ts('Load oppgavexml'), //title fo the queue
      'queue' => $queue, //the queue object
      'errorMode'=> CRM_Queue_Runner::ERROR_ABORT, //abort upon error and keep task in queue
      'onEnd' => ['CRM_Oppgavexml_Page_Load', 'onEnd'], //method which is called as soon as the queue is finished
      'onEndUrl' => CRM_Utils_System::url('civicrm', 'reset=1'), //go to page after all tasks are finished
    ]);

    $this->removeExistingOppgave($year);
    $count = $this->getRelevantContactCount($year);
    $runs = (int) ceil(($count / 50));
    for($i=0; $i <= $runs; $i++) {
      $task = new CRM_Queue_Task(
        ['CRM_Oppgavexml_Page_Load', 'load'], //call back method
        [$year],
        'Processd '.($i*50).' of '.$count
      );
      //now add this task to the queue
      $queue->createItem($task);
    }

    $runner->runAllViaWeb(); // does not return
  }

  /**
   * Function to retrieve all required contacts with their total deductible amount
   *
   * @param int $year
   * @return int
   */
  function getRelevantContactCount($year) {
    $config = CRM_Oppgavexml_Config::singleton();
    $startDate = $year.'-01-01 00:00:00';
    $endDate = $year.'-12-31 23:59:59';
    $minAmount = $config->get_min_deductible_amount();

    $query = 'SELECT  a.contact_id, receive_date, SUM(total_amount - non_deductible_amount) AS deductible_amount,
    b.contact_id AS loaded_contact
    FROM civicrm_contribution a LEFT JOIN civicrm_oppgave_processed b ON a.contact_id = b.contact_id
    AND oppgave_year = %1
    WHERE receive_date BETWEEN %2 AND %3  AND contribution_status_id = %4 AND b.contact_id IS NULL
    GROUP BY a.contact_id HAVING SUM(total_amount - non_deductible_amount) >= %5';
    $params = [
      1 => [$year, 'Positive'],
      2 => [$startDate, 'String'],
      3 => [$endDate, 'String'],
      4 => [1, 'Positive'],
      5 => [$minAmount, 'Integer']];

    $dao = CRM_Core_DAO::executeQuery($query, $params);
    return $dao->N;
  }

  /**
   * Function to check if contact already exists in file for year
   *
   * @param int $contactId
   * @param int $oppgaveYear
   * @return boolean
   */
  function removeExistingOppgave($oppgaveYear) {
    $query = 'DELETE FROM civicrm_oppgave WHERE oppgave_year = %1';
    $params = [
      1 => [$oppgaveYear, 'Positive']];
    $dao = CRM_Core_DAO::executeQuery($query, $params);

    $query = "DELETE FROM civicrm_oppgave_processed WHERE oppgave_year = %1";
    CRM_Core_DAO::executeQuery($query, [1 => [$oppgaveYear, 'Integer']]);
  }

  /**
   * Handle the final step of the queue
   */
  static function onEnd(CRM_Queue_TaskContext $ctx) {
    //set a status message for the user
    CRM_Core_Session::setStatus('Loaded contacts for oppgavexml', 'Queue', 'success');
  }

  public static function load(CRM_Queue_TaskContext $ctx, $year) {
    $return = civicrm_api3('TaxDeclarationYear', 'Load', ['year' => $year, 'options' => ['limit' => 50]]);
    return true;
  }

}
