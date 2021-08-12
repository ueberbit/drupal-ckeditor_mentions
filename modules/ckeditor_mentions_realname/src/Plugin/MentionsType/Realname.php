<?php

namespace Drupal\ckeditor_mentions_realname\Plugin\MentionsType;

use Drupal\ckeditor_mentions\Plugin\MentionsType\User;
use Drupal\Core\Database\Query\SelectInterface;

/**
 * Provides a user mentions integrated with realname module.
 *
 * @MentionsType(
 *   id = "realname",
 *   label = @Translation("Realname"),
 *   entity_type = "user"
 * )
 */
class Realname extends User {

  /**
   * {@inheritDoc}
   */
  protected function getQuery(): SelectInterface {
    $query = $this->database->select('realname', 'rn');

    $query->leftJoin('users_field_data', 'ud', 'ud.uid = rn.uid');
    $query->fields('rn', ['uid', 'realname']);
    $query->condition('rn.realname', '%' . $query->escapeLike($this->getMatch()) . '%', 'LIKE');
    $query->isNotNull('rn.realname');
    $query->addTag($this->getPluginDefinition()['entity_type'] . '_access');
    $query->condition('ud.status', 1);

    return $query;
  }

}
