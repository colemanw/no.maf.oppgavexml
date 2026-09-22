<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

class CRM_Oppgavexml_LoadQueue {

  const QUEUE_NAME = 'no.maf.oppgavexml.load.queue';

  private $queue;

  static $singleton;
  /**
   * @return CRM_Queuehowto_Helper
   */
  public static function singleton() {
    if (!self::$singleton) {
      self::$singleton = new CRM_Oppgavexml_LoadQueue();
    }
    return self::$singleton;
  }

  private function __construct() {
    $this->queue = CRM_Queue_Service::singleton()->create([
      'type' => 'Sql',
      'name' => self::QUEUE_NAME,
      'reset' => true, //do not flush queue upon creation
    ]);
  }

  public function getQueue() {
    return $this->queue;
  }

}
