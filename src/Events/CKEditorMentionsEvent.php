<?php

namespace Drupal\ckeditor_mentions\Events;

use Drupal\ckeditor_mentions\MentionsType\MentionsTypeInterface;
use Drupal\Core\Entity\EntityInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class provide the mention event.
 *
 * @package Drupal\ckeditor_mentions
 */
class CKEditorMentionsEvent extends Event implements CKEditorMentionEventInterface {

  /**
   * The entity.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  public $entity;

  /**
   * The users mentioned in the entity.
   *
   * @var \Drupal\user\UserInterface
   */
  public $mentionedEntity;

  /**
   * Additional information.
   *
   * @var array
   *
   * @todo Remove in 3.0, exists only for bc.
   */
  public $additionalInformation;

  /**
   * Mentions type plugin.
   *
   * @var \Drupal\ckeditor_mentions\MentionsType\MentionsTypeInterface
   */
  public $plugin;

  /**
   * CKEditorMentionsEvent constructor.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity that triggered the event.
   * @param \Drupal\Core\Entity\EntityInterface $mentioned_entity
   *   Entity mentioned in the entity.
   * @param \Drupal\ckeditor_mentions\MentionsType\MentionsTypeInterface $plugin
   *   Mentions type plugin.
   * @param array $additionalInformation
   *   Additional information.
   */
  public function __construct(EntityInterface $entity, EntityInterface $mentioned_entity, MentionsTypeInterface $plugin, array $additionalInformation = []) {
    $this->entity = $entity;
    $this->additionalInformation = $additionalInformation;
    $this->mentionedEntity = $mentioned_entity;
    $this->plugin = $plugin;
  }

  /**
   * {@inheritDoc}
   */
  public function getEntity(): EntityInterface {
    return $this->entity;
  }

  /**
   * {@inheritDoc}
   */
  public function setEntity(EntityInterface $entity) {
    $this->entity = $entity;
  }

  /**
   * {@inheritDoc}
   */
  public function getMentionedEntity(): EntityInterface {
    return $this->mentionedEntity;
  }

  /**
   * {@inheritDoc}
   */
  public function setMentionedEntity(EntityInterface $mentioned_user) {
    $this->mentionedEntity = $mentioned_user;
  }

  /**
   * {@inheritDoc}
   */
  public function getAdditionalInformation(): array {
    return $this->additionalInformation;
  }

  /**
   * {@inheritDoc}
   */
  public function setAdditionalInformation(array $additional_information) {
    $this->additionalInformation = $additional_information;
  }

  /**
   * {@inheritDoc}
   */
  public function getPlugin(): MentionsTypeInterface {
    return $this->plugin;
  }

  /**
   * {@inheritDoc}
   */
  public function setPlugin(MentionsTypeInterface $mentionsType) {
    $this->plugin = $mentionsType;
  }

}
