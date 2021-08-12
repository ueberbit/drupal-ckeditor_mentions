<?php

namespace Drupal\ckeditor_mentions\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Mentions Type annotation object.
 *
 * Plugin Namespace: Plugin\MentionsType.
 *
 * @Annotation
 */
class MentionsType extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the action plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

  /**
   * Entity type id that plugin is created for.
   *
   * @var string
   */
  public $entity_type;

}
