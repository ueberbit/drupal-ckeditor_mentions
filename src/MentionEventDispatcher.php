<?php

namespace Drupal\ckeditor_mentions;

use Drupal\ckeditor\CKEditorPluginManager;
use Drupal\ckeditor_mentions\Events\CKEditorEvents;
use Drupal\ckeditor_mentions\Events\CKEditorMentionsEvent;
use Drupal\ckeditor_mentions\MentionsType\MentionsTypeManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Session\AccountInterface;
use Masterminds\HTML5;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class MentionService.
 *
 * @package Drupal\ckeditor_mentions
 * @internal
 */
class MentionEventDispatcher {

  /**
   * ConfigFactory Service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityManager;

  /**
   * Mentions type manager.
   *
   * @var \Drupal\ckeditor_mentions\MentionsType\MentionsTypeManagerInterface
   */
  protected $mentionsTypeManager;

  /**
   * Ckeditor plugin manager.
   *
   * @var \Drupal\ckeditor\CKEditorPluginManager
   */
  protected $ckeditorPluginManager;

  /**
   * MentionService constructor.
   *
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   A configuration factory instance.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type manager.
   * @param \Drupal\ckeditor_mentions\MentionsType\MentionsTypeManagerInterface $mentionsTypeManager
   *   Mentions type.
   * @param \Drupal\ckeditor\CKEditorPluginManager $ckeditor_plugin_manager
   *   Ckeditor plugin manager.
   */
  public function __construct(AccountInterface $current_user,
      ConfigFactoryInterface $config_factory,
      EventDispatcherInterface $event_dispatcher,
      EntityTypeManagerInterface $entityTypeManager,
      MentionsTypeManagerInterface $mentionsTypeManager,
      CKEditorPluginManager $ckeditor_plugin_manager) {
    $this->configFactory = $config_factory;
    $this->currentUser = $current_user;
    $this->eventDispatcher = $event_dispatcher;
    $this->entityManager = $entityTypeManager;
    $this->mentionsTypeManager = $mentionsTypeManager;
    $this->ckeditorPluginManager = $ckeditor_plugin_manager;
  }

  /**
   * Triggers the Mention Event.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Trigger the mention event.
   * @param string $event_name
   *   The name of the event.
   */
  public function dispatchMentionEvent(EntityInterface $entity, string $event_name) {
    // Load the Symfony event dispatcher object through services.
    $dispatcher = $this->eventDispatcher;
    // Creating our event class object.
    $mentioned_entities = $this->getMentionsFromEntity($entity);

    // For backward compatibility a single entity
    // is sent but with the same structure as before.
    // @todo Remove in 3.0
    foreach ($mentioned_entities as $mentioned_entity) {
      $event = new CKEditorMentionsEvent($entity, $mentioned_entity['entity'], $mentioned_entity['plugin'], $mentioned_entity);
      $dispatcher->dispatch($event_name, $event);

      $legacy_event_name = $event_name === CKEditorEvents::MENTION_FIRST ? CKEditorMentionEvent::MENTION_FIRST : CKEditorMentionEvent::MENTION_SUBSEQUENT;
      $legacy_event = new CKEditorMentionEvent($event->getEntity(), [
        'entity' => $event->getMentionedEntity(),
        'plugin' => $event->getPlugin(),
      ] + $event->getAdditionalInformation());
      $dispatcher->dispatch($legacy_event_name, $legacy_event);
    }
  }

  /**
   * Reads all the fields from an entity and return all the users mentioned.
   *
   * The array returned has this format:
   *
   * [entity_id] => [
   *    'uuid' => $uuid,
   *   'id' => $id,
   *   'field_name' => [
   *     'delta' => [
   *       0 => 0,
   *       1 => 1,
   *       2 => 2,
   *     ]
   *   ]
   * ];
   *
   * The first key is the user id, the next key is the field_name where the
   * user was mentioned and finally the deltas of the fields.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity from which will get the mentions.
   *
   * @return array
   *   The users mentioned.
   */
  public function getMentionsFromEntity(EntityInterface $entity): array {
    $mentioned_entities = [];
    // Check if some of the fields is using the CKEditor editor.
    if (!$entity instanceof FieldableEntityInterface) {
      return $mentioned_entities;
    }

    $bundle_fields = $entity->getFieldDefinitions();
    $format_using_mentions = $this->getTexformatsUsingMentions();

    foreach ($bundle_fields as $field_name => $field) {
      $field_value = $entity->get($field_name)->getValue();
      foreach ($field_value as $key => $item) {
        if (isset($item['format']) && in_array($item['format'], $format_using_mentions)) {
          foreach ($this->getMentionedEntities($item['value']) as $id => $mentioned_entity_information) {
            $mentioned_entities[$id][$field_name]['delta'][$key] = $key;
            $mentioned_entities[$id] += $mentioned_entity_information;
          }
        }
      }
    }
    return $mentioned_entities;
  }

  /**
   * Returns the list of text formats using the Mentions plugin.
   *
   * @return array
   *   An array with the editors using the mentions plugin.
   */
  public function getTexformatsUsingMentions(): array {
    $editor_using_mentions = [];
    $editors = $this->entityManager->getStorage('editor')->loadByProperties(['editor' => 'ckeditor']);
    /** @var \Drupal\ckeditor_mentions\Plugin\CKEditorPlugin\Mentions $mentions_plugin */
    $mentions_plugin = $this->ckeditorPluginManager->createInstance('mentions');
    /** @var \Drupal\editor\Entity\Editor $editor */
    foreach ($editors as $editor) {
      if ($mentions_plugin->isEnabled($editor)) {
        $editor_using_mentions[] = $editor->id();
      }
    }

    return $editor_using_mentions;
  }

  /**
   * Returns an array of the user mentioned in the text.
   *
   * @param string $field_value
   *   The field text $field_text.
   *
   * @return array
   *   An array with the uid of the user mentioned.
   */
  public function getMentionedUsers($field_value) {
    @trigger_error('MentionEventDispatcher::getMentionedUsers() is deprecated in ckeditor_mentions:2.0.0 and will be removed before ckeditor_mentions:3.0.0. Instead MentionEventDispatcher::getMentionedEntities().');
    return $this->getMentionedEntities($field_value);
  }

  /**
   * Returns an with information about mentioned entities.
   *
   * @param string $field_value
   *   The field text $field_text.
   *
   * @return array
   *   Array with information about mentioned entities.
   */
  public function getMentionedEntities(string $field_value): array {
    $mentioned_entities = [];
    $plugins = [];

    if (empty($field_value)) {
      return $mentioned_entities;
    }

    // Instantiate the HTML5 parser, but without the HTML5 namespace being
    // added to the DOM document.
    $html5 = new HTML5(['disable_html_ns' => TRUE]);
    $dom = $html5->loadHTML($field_value);

    $anchors = $dom->getElementsByTagName('a');
    foreach ($anchors as $anchor) {
      $plugin = NULL;
      $entity_id = $anchor->getAttribute('data-mention');
      $plugin_id = $anchor->getAttribute('data-plugin');

      if (empty($entity_id)) {
        continue;
      }

      /** @var \Drupal\ckeditor_mentions\MentionsType\MentionsTypeBase $plugin */
      $plugin = $plugins[$plugin_id] = $plugins[$plugin_id] ?? $this->mentionsTypeManager->createInstance($plugin_id);

      $mentioned_entities[$entity_id] = [
        'id' => $entity_id,
        'plugin' => $plugin,
        'entity' => $this->entityManager->getStorage($plugin->getPluginDefinition()['entity_type'])->load($entity_id),
      ];

      // For backward compatibility add uid.
      // @todo Remove in 3.0.
      if ($plugin->getPluginId() == 'realname') {
        $mentioned_entities[$entity_id]['uid'] = $entity_id;
      }
    }

    return $mentioned_entities;
  }

}
