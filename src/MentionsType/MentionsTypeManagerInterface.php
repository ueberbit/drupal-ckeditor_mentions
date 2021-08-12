<?php

namespace Drupal\ckeditor_mentions\MentionsType;

use Drupal\Component\Plugin\PluginManagerInterface;

/**
 * Provides an interface for a mentions type manager.
 */
interface MentionsTypeManagerInterface extends PluginManagerInterface {

  /**
   * Gets all mentions types.
   *
   * @return array
   *   Returns an array of all mentions type labels keyed by plugin ID.
   */
  public function getAllMentionsTypes();

}
