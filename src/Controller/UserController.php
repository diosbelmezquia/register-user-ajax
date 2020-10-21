<?php

namespace Drupal\serempre_test\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Drupal\Core\Url;
use Drupal\serempre_test\UserConnectionManager;

/**
 * Returns responses for System routes.
 */
class UserController extends ControllerBase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $userManager;

  /**
   * Constructs a UserController object.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   */
  public function __construct(Connection $database, UserConnectionManager $userManager) {
    $this->database = $database;
    $this->userManager = $userManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('serempre_test.user_manager')
    );
  }

  /**
   * Provide the administration overview page.
   *
   * @return array
   *   A renderable array of the administration overview page.
   */
  public function showUsers() {
    $download_excel = \Drupal::request()->query->get('download');
    if ($download_excel) {
      $this->downloadExcel();
    }

    $query = $this->database->select('myusers', 'u');
    $query->fields('u', ['id', 'name']);
    $query->orderBy('u.id', 'DESC');
    $pager = $query->extend('Drupal\Core\Database\Query\PagerSelectExtender')->limit(3);
    $result = $pager->execute()->fetchAll();

    $rows = [];
    foreach ($result as $value) {
      $rows[] = [
        // Cells.
        $value->id,
        $value->name,
      ];
    }

    $build['user_listing'] = [
      '#header' => [$this->t('Id'), $this->t('Name')],
      '#type' => 'table',
      '#rows' => $rows,
      '#empty' => $this->t('There are no user to display.'),
    ];

    $build['pager'] = [
      '#type' => 'pager',
    ];

    $options = [
      'query' => ['download' => 'excel'],
    ];

    if (!empty($result)) {
      $build['download_excel'] = [
        '#title' => $this->t('Download Excel'),
        '#type' => 'link',
        '#url' => Url::fromRoute('serempre_test.admin_user_query', [], $options),
      ];
    }

    return $build;
  }

  /**
   * Provide the administration overview page.
   *
   * @return array
   *   A renderable array of the administration overview page.
   */
  private function downloadExcel() {
    $users = $this->userManager->getAllUsers();

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    foreach ($users as $index => $user) {
      $index++;
      $sheet->setCellValue("A$index", $user->id);
      $sheet->setCellValue("B$index", $user->name);
    }

    $writer = IOFactory::createWriter($spreadsheet, 'Xls');
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment; filename=\"users.Xls\"");
    $writer->save('php://output');
  }

}
