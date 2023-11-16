<?php

namespace Drupal\ckeditor_mentions\Plugin\MentionsType;

use Drupal\ckeditor_mentions\MentionsType\MentionsTypeBase;
use Drupal\Core\Database\Query\AlterableInterface;
use Drupal\node\NodeInterface;

/**
 * Provides a node mentions type.
 *
 * @MentionsType(
 *   id = "node",
 *   label = @Translation("Node"),
 *   entity_type = "node"
 * )
 */
class Node extends MentionsTypeBase {

  /**
   * {@inheritDoc}
   */
  public function getQuery(): AlterableInterface {
    return $this->entityManager
      ->getStorage($this->getPluginDefinition()['entity_type'])
      ->getQuery()
      ->condition('title', $this->getMatch(), 'CONTAINS')
      ->condition('status', NodeInterface::PUBLISHED)
      ->accessCheck(TRUE);
  }

  /**
   * {@inheritDoc}
   */
  protected function outputTemplate() {
    $output = parent::outputTemplate();
    $output['#value'] = '@{title}';
    $output['#attributes']['href'] = '{node_page}';
    return $output;
  }

  /**
   * {@inheritDoc}
   */
  protected function itemTemplate() {
    $item = parent::itemTemplate();
    $item['node'] = [
      '#type' => 'html_tag',
      '#tag' => 'strong',
      '#attributes' => ['class' => ['ckeditor-mentions']],
      '#value' => '{title}',
    ];
    return $item;
  }

}
