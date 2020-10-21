<?php

namespace Drupal\serempre_test\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\serempre_test\ImportUsersBatch;


/**
 * Configure cron settings for this site.
 *
 * @internal
 */
class UserImport extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'serempre_test_user_import';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['csv_file'] = [
      '#type' => 'managed_file',
      '#name' => 'csv_file',
      '#title' => $this->t('Upload CSV file'),
      '#upload_validators' => [
        'file_validate_extensions' => ['csv']
      ],
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Import'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $file_storage = \Drupal::entityTypeManager()->getStorage('file');

    // Get user manager.
    $user_manager = \Drupal::service('serempre_test.user_manager');

    foreach($form_state->getValue('csv_file') as $fid) {
      $file = $file_storage->load($fid);
      $data = file_get_contents($file->getFileUri());
      $data_result = str_getcsv($data);

      if (!empty($data_result)) {
        // Build batch operations, one per revision.
        $operations = [];
        foreach ($data_result as $index => $name) {
          // Eliminar espacion en blanco al inicio y final del nombre.
          $name = trim($name);
          // Validar y guardar user en basedatos.
          if (ctype_alpha($name) && strlen($name) <= 5) {
            $operations[] = [
              [ImportUsersBatch::class, 'createUserBatch'],
              [$name],
            ];
          }
        }
        $batch = [
          'title' => t('Import users from CSV file.'),
          'operations' => $operations,
          'init_message' => t('Starting creating users'),
          'progress_message' => t('Created @current of @total users. Estimated time: @estimate.'),
          'finished' => [ImportUsersBatch::class, 'finish'],
        ];
        batch_set($batch);
      }
    }

  }

}
