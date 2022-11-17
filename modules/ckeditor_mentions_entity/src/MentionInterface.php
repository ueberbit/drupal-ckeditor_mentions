<?php

namespace Drupal\ckeditor_mentions_entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Interface defining the mention entity type.
 */
interface MentionInterface extends ContentEntityInterface, EntityOwnerInterface {

  /**
   * Gets the node creation timestamp.
   *
   * @return int
   *   Creation timestamp of the node.
   */
  public function getCreatedTime();

  /**
   * Gets the entity where the mention was made.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity where the mention was made.
   */
  public function getParentEntity();

  /**
   * Gets the mentioned entity.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The mentioned entity.
   */
  public function getTargetEntity();

  /**
   * Sets the node creation timestamp.
   *
   * @param int $timestamp
   *   The node creation timestamp.
   *
   * @return $this
   */
  public function setCreatedTime($timestamp);

  /**
   * Sets the entity where the mention was made.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity where the mention was made.
   *
   * @return $this
   */
  public function setParentEntity(ContentEntityInterface $entity);

  /**
   * Sets the mentioned entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The mentioned entity.
   *
   * @return $this
   */
  public function setTargetEntity(ContentEntityInterface $entity);

}
