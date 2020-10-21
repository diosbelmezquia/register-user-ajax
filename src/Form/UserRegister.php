<?php

namespace Drupal\serempre_test\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\MessageCommand;
use Drupal\serempre_test\Ajax\UserRegisterModalCommand;


/**
 * Configure cron settings for this site.
 *
 * @internal
 */
class UserRegister extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'serempre_test_user_register';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#attached']['library'][] = 'serempre_test/serempre_test.user_validate';

    $form['user_name'] = [
      '#type' => 'textfield',
      '#title' => t('User name'),
      '#maxlength' => 5,
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save user'),
      '#ajax' => [
        'callback' => '::submitFormAjax',
      ],
    ];

    return $form;
  }

  /**
   * Ajax callback for "Submit" button.
   *
   * This callback is called regardless of what happens in validation and
   * submission processing. It needs to return the content that will be used to
   * replace the DOM element identified by the '#ajax' properties 'wrapper' key.
   *
   * @return array
   *   Renderable array (the box element)
   */
  public function submitFormAjax(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $user_name = $form_state->getValue('user_name');

    // Get user manager.
    $user_manager = \Drupal::service('serempre_test.user_manager');

    $error = ['type' => 'error'];

    // Check requierd field.
    if (empty($user_name)) {
      $response->addCommand(new MessageCommand("Campo obligatorio", NULL, $error));
      return $response;
    }
    // Check just letters.
    if (!ctype_alpha($user_name)) {
      $response->addCommand(new MessageCommand("Solo letras akkii", NULL, $error));
      return $response;
    }
    // Check if user exists.
    if ($user_manager->userExists($user_name)) {
      $response->addCommand(new MessageCommand($this->t('El usuario :user_name ya existe.', [':user_name' => $user_name]), NULL, $error));
      return $response;
    }

    $user_id = $user_manager->createUser($user_name, TRUE);

    $response->addCommand(new UserRegisterModalCommand($user_id, $user_name));
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {}

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {}

}
