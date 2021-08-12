<?php

namespace Drupal\ckeditor_mentions\MentionsType;

use Drupal\Component\Plugin\ConfigurableInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines an interface for a MentionsType plugin.
 */
interface MentionsTypeInterface extends PluginInspectionInterface, ConfigurableInterface {

  /**
   * Build response that will be processed by mentions' plugin.
   *
   * @return array
   *   Array that can will be passed as configuration for mentions plugin.
   */
  public function buildResponse(): array;

  /**
   * Build tokens for entity.
   *
   * @param array $entities
   *   Array contains information for build tokens.
   *
   * @return array
   *   Tokens array.
   */
  public function buildTokens(array $entities): array;

}
