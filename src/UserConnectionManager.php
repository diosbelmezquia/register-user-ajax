<?php

namespace Drupal\serempre_test;

use Drupal\Core\Database\Connection;

/**
 * Class SourceContentManager.
 *
 * @package Drupal\snpc_web_services
 */
class UserConnectionManager {

  /**
   * The database service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The database service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  const USER_TABLE = 'myusers';

  /**
   * Constructs a UserConnectionManager object.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * Provide the administration overview page.
   *
   * @return array
   *   A renderable array of the administration overview page.
   */
  public function createUser(string $name, bool $get_id = FALSE) {
    if ($this->tableExists(static::USER_TABLE)) {
      $query = $this->database->insert(static::USER_TABLE);
      $query->fields([
        'name' => $name,
      ])->execute();
    }
    // Return user id.
    if ($get_id) {
      return $this->getUserIdbyname($name);
    }
  }

  /**
   * Provide the administration overview page.
   *
   * @return array
   *   A renderable array of the administration overview page.
   */
  public function getUserIdbyname(string $name) {
    if ($this->tableExists(static::USER_TABLE)) {
      $result = $this->database->query("SELECT id FROM {". static::USER_TABLE ."} WHERE name = :name", [':name' => $name])->fetchAll();
      // Get user id.
      if (!empty($result)) {
        return $result[0]->id;
      }
    }
    return NULL;
  }

  /**
   * Provide the administration overview page.
   *
   * @return array
   *   A renderable array of the administration overview page.
   */
  public function userExists(string $name) {
    if ($this->tableExists(static::USER_TABLE)) {
      $result = $this->database->query("SELECT id FROM {". static::USER_TABLE ."} WHERE name = :name", [':name' => $name])->fetchAll();
    }
    return !empty($result);
  }

  /**
   * Provide the administration overview page.
   *
   * @return array
   *   A renderable array of the administration overview page.
   */
  public function tableExists(string $table) {
    $schema = $this->database->schema();
    return $schema->tableExists($table);
  }

  /**
   * Provide the administration overview page.
   *
   * @return array
   *   A renderable array of the administration overview page.
   */
  public function getAllUsers() {
    $query = $this->database->select(static::USER_TABLE, 'user');
    $query->fields('user', ['id', 'name']);
    $query->orderBy('user.id', 'DESC');
    $result = $query->execute()->fetchAll();
    return $result;
  }

  /**
   * Provide the administration overview page.
   *
   * @return array
   *   A renderable array of the administration overview page.
   */
  public function createUserLogger(string $type) {
    $user_log_table = 'serempre_access_log';
    if ($this->tableExists($user_log_table)) {
      $query = $this->database->insert($user_log_table);
      $query->fields([
        'date' => \Drupal::time()->getCurrentTime(),
        'uid'  => \Drupal::currentUser()->id(),
        'ip'   => \Drupal::request()->getClientIp(),
        'log_type' => $type,
      ])->execute();
    }
  }

}
