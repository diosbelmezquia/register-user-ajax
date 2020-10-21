<?php

namespace Drupal\serempre_test\Ajax;

use Drupal\Core\Ajax\CommandInterface;
use Drupal\Core\Ajax\CommandWithAttachedAssetsTrait;

/**
 * AJAX command for calling the jQuery replace() method.
 *
 * @ingroup ajax
 */
class UserRegisterModalCommand implements CommandInterface {

  use CommandWithAttachedAssetsTrait;

  /**
   * A CSS selector string.
   *
   * If the command is a response to a request from an #ajax form element then
   * this value can be NULL.
   *
   * @var string
   */
  protected $userId;

  /**
   * The content for the matched element(s).
   *
   * Either a render array or an HTML string.
   *
   * @var string|array
   */
  protected $userName;

  /**
   * The content for the matched element(s).
   *
   * Either a render array or an HTML string.
   *
   * @var string|array
   */
  protected $content;

  /**
   * Constructs an UserRegisterModalCommand object.
   */
  public function __construct($user_id, $user_name) {
    $this->userId = $user_id;
    $this->userName = $user_name;
    $this->content = $this->getHtmlModal();
  }

  /**
   * Implements Drupal\Core\Ajax\CommandInterface:render().
   */
  public function render() {
    return [
      'command' => 'insertModal',
      'selector' => '#myusers-user-id',
      'data' => $this->getRenderedContent(),
      'settings' => [
        'userIdResponse' => $this->userId,
      ]
    ];
  }

  /**
   * getModalHtml
   */
  public function getHtmlModal() {
    return [
      '#theme' => 'user_modal_register',
      '#user_id' => $this->userId,
      '#user_name' => $this->userName,
    ];
  }

}
