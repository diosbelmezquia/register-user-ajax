<?php

namespace Drupal\serempre_test;

/**
 * Methods for generate short url in a batch.
 */
class ImportUsersBatch {

  /**
   * Implements callback_batch_operation().
   *
   * Generate short url for the node with $nid.
   *
   * @param $nid
   *   The node ID.
   * @param array $context
   *   An array of contextual key/values.
   */
  public static function createUserBatch($name, &$context) {
    if (empty($context['results'])) {
      $context['results']['user_created'] = 0;
    }
    // Get user manager.
    $user_manager = \Drupal::service('serempre_test.user_manager');

    if (!$user_manager->userExists($name)) {
      $user_manager->createUser($name);
      $context['results']['user_created']++;
    }
  }

  /**
   * Finish batch for url shorter.
   *
   * @param bool $success
   *   Indicate that the batch API tasks were all completed successfully.
   * @param array $results
   *   An array of all the results that were updated in update_do_one().
   * @param array $operations
   *   A list of the operations that had not been completed by the batch API.
   */
  public static function finish($success, array $results, array $operations) {
    $messenger = \Drupal::messenger();
    $logger = \Drupal::logger('user_created');

    if ($success) {
      $message_seccess = $results['user_created'] .' '. t('users created.');
      $logger->notice($message_seccess);
      $messenger->addMessage($message_seccess);
    }
    else {
      // An error occurred.
      // $operations contains the operations that remained unprocessed.
      $error_operation = reset($operations);
      $message = t('An error occurred while processing %error_operation with arguments: @arguments', [
        '%error_operation' => $error_operation[0],
        '@arguments' => print_r($error_operation[1], TRUE)
      ]);
      $logger->error($message);
      $messenger->addError($message);
    }
  }

}
