<?php

// @codingStandardsIgnoreFile
namespace Drupal\ckeditor_mentions;

use Drupal\ckeditor_mentions\Events\CKEditorMentionEventInterface;
use Drupal\ckeditor_mentions\MentionsType\MentionsTypeInterface;
use Drupal\Core\Entity\EntityInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class provide the mention event.
 *
 * @package Drupal\ckeditor_mentions
 *
 * @deprecated in ckeditor_mentions:2.0.0 and is removed from ckeditor_mentions:3.0.0.
 * Instead use \Drupal\ckeditor_mentions\Events\CKEditorMentionEventInterface in the Event subscriber type declaration.
 */
class CKEditorMentionEvent extends Event implements CKEditorMentionEventInterface {

  /**
   * @deprecated in ckeditor_mentions:2.0.0 and is removed from ckeditor_mentions:3.0.0.
   * Instead use \Drupal\ckeditor_mentions\Events\CKEditorEvents::MENTION_FIRST.
   */
  const MENTION_FIRST = 'event.mention';

  /**
   * @deprecated in ckeditor_mentions:2.0.0 and is removed from ckeditor_mentions:3.0.0.
   * Instead use \Drupal\ckeditor_mentions\Events\CKEditorEvents::MENTION_SUBSEQUENT.
   */
  const MENTION_SUBSEQUENT = 'event.mention_subsequent';

  /**
   * CKEditorMentionEvent Event.
   *
   * @var \Drupal\ckeditor_mentions\Events\CKEditorMentionsEvent
   */
  protected $event;

  /**
   * CKEditorMentionEvent constructor.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity that triggered the event.
   * @param array $mentioned_entity
   *   The users mentioned in the entity.
   */
  public function __construct(EntityInterface $entity, array $information = []) {
    $this->event = new Events\CKEditorMentionsEvent($entity, $information['entity'], $information['plugin'], $information);
  }

  /**
   * Getter for rules integration.
   *
   * @see https://www.drupal.org/project/rules/issues/2762517
   *
   * @param string $name
   *
   * @return mixed
   *   Property of new event or null.
   */
  public function __get(string $name) {
    return $this->event->$name ?: NULL;
  }

  /**
   * Returns the reference ID.
   *
   * @return EntityInterface
   *   The reference Id.
   */
  public function getEntity(): EntityInterface {
    @trigger_error('CKEditorMentionEvent::getEntity() is deprecated in ckeditor_mentions:2.0.0 and will be removed before ckeditor_mentions:3.0.0. Instead CKEditorMentionsEvent::getEntity().');
    return $this->event->getEntity();
  }

  /**
   * Sets the Entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity that triggered the event.
   */
  public function setEntity(EntityInterface $entity) {
    @trigger_error('CKEditorMentionEvent::setEntity() is deprecated in ckeditor_mentions:2.0.0 and will be removed before ckeditor_mentions:3.0.0. Instead CKEditorMentionsEvent::setEntity().');
    $this->event->setEntity($entity);
  }

  /**
   * Returns an array with the mentioned users.
   *
   * @return array
   *   An array with the mentioned users.
   */
  public function getMentionedUsers(): array {
    @trigger_error('CKEditorMentionEvent::getMentionedUsers() is deprecated in ckeditor_mentions:2.0.0 and will be removed before ckeditor_mentions:3.0.0. Instead CKEditorMentionsEvent::getAdditionalInformation().');
    return $this->event->getAdditionalInformation();
  }

  /**
   * Sets the list of the mentioned users.
   *
   * @param array $mentioned_user
   *   The mentioned user additional information.
   *
   * @see \Drupal\ckeditor_mentions\MentionEventDispatcher::getMentionsFromEntity()
   */
  public function setMentionedUsers(array $mentioned_user) {
    @trigger_error('CKEditorMentionEvent::setMentionedUsers() is deprecated in ckeditor_mentions:2.0.0 and will be removed before ckeditor_mentions:3.0.0. Instead CKEditorMentionsEvent::setAdditionalInformation().');
    $this->event->setAdditionalInformation($mentioned_user);
  }

  /**
   * {@inheritDoc}
   */
  public function getMentionedEntity(): EntityInterface {
    return $this->event->getMentionedEntity();
  }

  /**
   * {@inheritDoc}
   */
  public function setMentionedEntity(EntityInterface $mentioned_user) {
    $this->event->setMentionedEntity($mentioned_user);
  }

  /**
   * {@inheritDoc}
   */
  public function getAdditionalInformation(): array {
    return $this->event->getAdditionalInformation();
  }

  /**
   * {@inheritDoc}
   */
  public function setAdditionalInformation(array $additional_information) {
    $this->event->setAdditionalInformation($additional_information);
  }

  /**
   * {@inheritDoc}
   */
  public function getPlugin(): MentionsTypeInterface {
    return $this->event->getPlugin();
  }

  /**
   * {@inheritDoc}
   */
  public function setPlugin(MentionsTypeInterface $mentionsType) {
    $this->event->setPlugin($mentionsType);
  }

}
