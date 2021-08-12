<?php

namespace Drupal\ckeditor_mentions\Events;

use Drupal\ckeditor_mentions\MentionsType\MentionsTypeInterface;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides an interface for CkeditorMentions Event.
 */
interface CKEditorMentionEventInterface {

  /**
   * Returns the reference ID.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The reference Id.
   */
  public function getEntity(): EntityInterface;

  /**
   * Sets the Entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity that triggered the event.
   */
  public function setEntity(EntityInterface $entity);

  /**
   * Returns an array with the mentioned users.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   An array with the mentioned users.
   */
  public function getMentionedEntity(): EntityInterface;

  /**
   * Sets the list of the mentioned users.
   *
   * @param \Drupal\Core\Entity\EntityInterface $mentioned_user
   *   The mentioned user ID.
   */
  public function setMentionedEntity(EntityInterface $mentioned_user);

  /**
   * Get additional information.
   *
   * @return array
   *   Additional information.
   *
   * @see \Drupal\ckeditor_mentions\MentionEventDispatcher::getMentionsFromEntity()
   */
  public function getAdditionalInformation(): array;

  /**
   * Set additional information.
   *
   * @param array $additional_information
   *   Additional information.
   */
  public function setAdditionalInformation(array $additional_information);

  /**
   * Get mentions type plugin.
   *
   * @return \Drupal\ckeditor_mentions\MentionsType\MentionsTypeInterface
   *   Plugin.
   */
  public function getPlugin(): MentionsTypeInterface;

  /**
   * Set mentions plugin.
   *
   * @param \Drupal\ckeditor_mentions\MentionsType\MentionsTypeInterface $mentionsType
   *   Mentions type.
   */
  public function setPlugin(MentionsTypeInterface $mentionsType);

}
