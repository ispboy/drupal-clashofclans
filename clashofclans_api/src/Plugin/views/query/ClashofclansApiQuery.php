<?php
namespace Drupal\clashofclans_api\Plugin\views\query;

use Drupal\views\Plugin\views\query\QueryPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\clashofclans_api\GuzzleCache;
use Drupal\views\ViewExecutable;
use Drupal\views\ResultRow;
use Drupal\Core\Form\FormStateInterface;

/**
 * Fitbit views query plugin which wraps calls to the Fitbit API in order to
 * expose the results to views.
 *
 * @ViewsQuery(
 *   id = "clashofclans_api_query",
 *   title = @Translation("ClashOfClans API"),
 *   help = @Translation("Query against the ClashOfClans API.")
 * )
 */
class ClashofclansApiQuery extends QueryPluginBase {

  private $client;
  private $location_id = '';

  public $groupby = [];

  /**
   * ClashofclansApi constructor.
   *
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param client $client
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, GuzzleCache $client) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->client = $client;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('clashofclans_api.guzzle_cache'),
    );
  }

  public function ensureTable($table, $relationship = NULL) {
    return '';
  }

  public function addField($table, $field, $alias = '', $params = array()) {
    return $field;
  }

  /**
   * {@inheritdoc}
   */
  public function execute(ViewExecutable $view) {
    $data = [];

    if (isset($this->options['url']) && isset($this->options['limit'])) {
      $url = $this->options['url'];
      $location_id = $this->getLocationId();
      $url = sprintf($url, $location_id);
      $options = ['query' => [
        'limit' => $this->options['limit'],
      ]];
      $data = $this->client->getData($url, $options);
    }

    if (isset($data['items'])) {
      $items = $data['items'];
      $index = 0;
      foreach($items as $item) {
        $row = [];
        $row['item'] = $item;
        $row['index'] = $index++;
        $view->result[] = new ResultRow($row);
      }

    }
  }

/**
 * not function, but avoid the views error.
*/
  public function addOrderBy($table, $field = NULL, $order = 'ASC', $alias = '', $params = []) {}

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['url'] = array(
      'default' => NULL,
    );
    $options['limit'] = array(
      'default' => NULL,
    );
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);
    $form['url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('URL'),
      '#size' => 48,
      '#default_value' => $this->options['url'],
      '#description' => $this->t('e.g. locations/global/rankings/clans'),
    ];
    $form['limit'] = [
      '#type' => 'number',
      '#title' => $this->t('Limit'),
      '#size' => 12,
      '#default_value' => $this->options['limit'],
      '#description' => $this->t('Limit the results.'),
      '#min' => 1,
      '#max' => 500,
    ];
  }

  public function setLocationId($id = '') {
    $this->location_id = $id;
  }

  public function getLocationId() {
    if (isset($this->location_id)) {
      return $this->location_id;
    }
  }

  //views invoke..
  public function clearFields() {
    $this->fields = array();
  }

  public function addGroupBy($clause) {
    // Only add it if it's not already in there.
    if (!in_array($clause, $this->groupby)) {
      $this->groupby[] = $clause;
    }
  }

  /**
   * Set what field the query will count() on for paging.
   */
  public function setCountField($table, $field, $alias = NULL) {
    if (empty($alias)) {
      $alias = $table . '_' . $field;
    }
    $this->count_field = [
      'table' => $table,
      'field' => $field,
      'alias' => $alias,
      'count' => TRUE,
    ];
  }  
}
