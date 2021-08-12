<?php

namespace Drupal\ckeditor_mentions\MentionsType;

use Drupal\ckeditor_mentions\Events\CKEditorMentionSuggestionsEvent;
use Drupal\ckeditor_mentions\Events\CKEditorEvents;
use Drupal\ckeditor_mentions\Exception\MatchIsMissingException;
use Drupal\Component\Plugin\PluginBase;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Query\AlterableInterface;
use Drupal\Core\Database\Query\SelectInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\RendererInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Provides a base implementation for a MentionType plugin.
 */
abstract class MentionsTypeBase extends PluginBase implements MentionsTypeInterface, ContainerFactoryPluginInterface {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityManager;

  /**
   * Database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * Renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * {@inheritDoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entityTypeManager,
    Connection $database,
    EventDispatcherInterface $eventDispatcher,
    RendererInterface $renderer
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityManager = $entityTypeManager;
    $this->database = $database;
    $this->eventDispatcher = $eventDispatcher;
    $this->renderer = $renderer;
    $this->configuration = $configuration + $this->defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('database'),
      $container->get('event_dispatcher'),
      $container->get('renderer')
    );
  }

  /**
   * {@inheritDoc}
   */
  public function buildTokens(array $entities): array {
    $result = [];
    foreach ($entities as $id => $entity) {
      if ($entity instanceof EntityInterface) {
        $result[$id]['id'] = $entity->id();
        $result[$id]['name'] = $entity->uuid();
      }

    }
    return $result;
  }

  /**
   * Get match value.
   *
   * @return string
   *   Match value.
   */
  public function getMatch() {
    return $this->configuration['match'];
  }

  /**
   * Add required tags and metadata to the query.
   *
   * @param \Drupal\Core\Database\Query\AlterableInterface $query
   *   Query to preprocess.
   */
  protected function preprocessQuery(AlterableInterface $query) {
    $query->addTag('ckeditor_mentions_' . $this->getPluginId());
    $query->addMetaData('plugin', $this);
  }

  /**
   * Build the query.
   *
   * @return \Drupal\Core\Database\Query\AlterableInterface
   *   The query.
   */
  protected function buildQuery(): AlterableInterface {
    if (!isset($this->configuration['match'])) {
      throw new MatchIsMissingException();
    }

    $query = $this->getQuery();
    $this->preprocessQuery($query);

    return $query;
  }

  /**
   * Get the query for plugin.
   *
   * @return \Drupal\Core\Database\Query\AlterableInterface
   *   Query.
   */
  abstract protected function getQuery(): AlterableInterface;

  /**
   * {@inheritDoc}
   */
  public function buildResponse(): array {
    $query = $this->buildQuery();

    $result = $query instanceof SelectInterface
      ? $query->execute()->fetchCol()
      : $query->execute();

    if (empty($result)) {
      return $result;
    }

    $users = $this->entityManager
      ->getStorage($this->getPluginDefinition()['entity_type'])
      ->loadMultiple($result);

    $tokens = $this->buildTokens($users);

    return $this->dispatchSuggestionsEvent($tokens);
  }

  /**
   * Dispatch suggestion event.
   *
   * @param array $suggestions
   *   Suggestions.
   *
   * @return array
   *   Suggestions.
   */
  protected function dispatchSuggestionsEvent(array $suggestions): array {
    $suggestion_event = new CKEditorMentionSuggestionsEvent($this->getMatch(), $suggestions);
    $this->eventDispatcher->dispatch(CKEditorEvents::SUGGESTION, $suggestion_event);
    return $suggestion_event->getSuggestions();
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration(): array {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = NestedArray::mergeDeep($this->defaultConfiguration(), $configuration);
  }

  /**
   * {@inheritDoc}
   */
  public function defaultConfiguration(): array {
    $item_template = $this->itemTemplate();
    $output_template = $this->outputTemplate();
    return [
      'match' => '',
      'output_template' => is_array($output_template) ? current($this->renderer->renderPlain($output_template)) : $output_template,
      'item_template' => is_array($item_template) ? current($this->renderer->renderPlain($item_template)) : $item_template,
    ];
  }

  /**
   * Template of markup to be inserted as the autocomplete item gets committed.
   *
   * Use any token defined during static::buildTokens.
   * You can also return simple string contains the markup e.g.
   * @code
   *   '<a data-id={id} href={path}>{name}</a>'
   * @endcode
   * Note. Always place data-mention and data-plugin attributes!
   *
   * @see \Drupal\ckeditor_mentions\Plugin\MentionsType\User::outputTemplate()
   * @see \Drupal\ckeditor_mentions\Plugin\MentionsType\User::buildTokens()
   *
   * @todo Make output and item templates configurable.
   *   Create the configuration entity for it?
   *
   * @return array|string
   *   Markup to be displayed.
   */
  protected function outputTemplate() {
    return [
      '#type' => 'html_tag',
      '#tag' => 'a',
      '#attributes' => [
        // Important: do not remove data-* attributes.
        'data-mention' => '{id}',
        'data-plugin' => $this->getPluginId(),
      ],
    ];
  }

  /**
   * Set template of markup to be inserted as an item of suggestion list.
   *
   * Use any token defined during static::buildTokens.
   *
   * You can also return simple string contains the markup e.g.
   * @code
   *   '<li data-mention={id} data-plugin= . $this->getPluginId() . >{name}</li>'
   * @endcode
   * Note. Always place data-id attribute!
   * Item always should be wrapped in <li> tag!
   *
   * @see \Drupal\ckeditor_mentions\Plugin\MentionsType\User::itemTemplate()
   * @see \Drupal\ckeditor_mentions\Plugin\MentionsType\User::buildTokens()
   *
   * @return array|string
   *   Markup to be displayed.
   */
  protected function itemTemplate() {
    return [
      '#type' => 'html_tag',
      '#tag' => 'li',
      '#attributes' => [
        // Important: do not remove data-* attributes.
        'data-id' => '{id}',
      ],
    ];
  }

  /**
   * Get output template.
   *
   * @return string
   *   Template.
   */
  public function getOutputTemplate(): string {
    return $this->configuration['output_template'];
  }

  /**
   * Get item template.
   *
   * @return string
   *   Template.
   */
  public function getItemTemplate(): string {
    return $this->configuration['item_template'];
  }

}
