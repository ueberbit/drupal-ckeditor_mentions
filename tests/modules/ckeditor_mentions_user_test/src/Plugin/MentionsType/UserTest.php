<?php

namespace Drupal\ckeditor_mentions_user_test\Plugin\MentionsType;

use Drupal\ckeditor_mentions\Plugin\MentionsType\User;
use Drupal\Core\Database\Query\AlterableInterface;

/**
 * Provides a user mentions type.
 *
 * @MentionsType(
 *   id = "user_test",
 *   label = @Translation("User"),
 *   entity_type= "user"
 * )
 */
class UserTest extends User {

  /**
   * {@inheritDoc}
   */
  protected function getQuery(): AlterableInterface {
    $query = parent::getQuery();
    $query->accessCheck(FALSE);
    return $query;
  }

  /**
   * Set match.
   *
   * @param string $value
   *   Match.
   */
  public function setMatch(string $value): void {
    $this->configuration['match'] = $value;
  }

  /**
   * {@inheritDoc}
   */
  public function buildTokens(array $entities): array {
    $result = [];
    foreach ($entities as $id => $entity) {
      $result[$id]['id'] = $entity->id();
      $result[$id]['name'] = $entity->uuid();
      $result[$id]['user_name'] = $entity->getDisplayName();
    }

    return $result;
  }

}
