<?php

namespace Drupal\ckeditor_mentions\MentionsType;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Defines the mentions type manager.
 */
class MentionsTypeManager extends DefaultPluginManager implements MentionsTypeManagerInterface {

  /**
   * Constructs an MentionsTypeManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/MentionsType', $namespaces, $module_handler, 'Drupal\ckeditor_mentions\MentionsType\MentionsTypeInterface', 'Drupal\ckeditor_mentions\Annotation\MentionsType');

    $this->setCacheBackend($cache_backend, 'mentions_type');
    $this->alterInfo('mentions_type_plugin_info');
  }

  /**
   * {@inheritDoc}
   */
  public function getAllMentionsTypes() {
    $mentions_types = [];

    foreach ($this->getDefinitions() as $id => $definition) {
      $mentions_types[$id] = $definition['label'];
    }
    asort($mentions_types);

    return $mentions_types;
  }

}
