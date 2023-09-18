<?php

namespace Drupal\ckeditor_mentions_entity\Entity;

use Drupal\ckeditor_mentions_entity\MentionInterface;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\user\EntityOwnerTrait;

/**
 * Defines the mention entity class.
 *
 * @ContentEntityType(
 *   base_table = "mention",
 *   entity_keys = {
 *     "id" = "id",
 *     "langcode" = "langcode",
 *     "owner" = "uid",
 *     "uuid" = "uuid",
 *   },
 *   handlers = {
 *     "views_data" = "Drupal\views\EntityViewsData",
 *   },
 *   id = "mention",
 *   label = @Translation("Mention"),
 *   label_collection = @Translation("Mentions"),
 *   label_count = @PluralTranslation(
 *     plural = "@count mentions",
 *     singular = "@count mention",
 *   ),
 *   label_plural = @Translation("mentions"),
 *   label_singular = @Translation("mention")
 * )
 */
class Mention extends ContentEntityBase implements MentionInterface {

  use EntityOwnerTrait;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    /** @var \Drupal\Core\Field\BaseFieldDefinition[] $fields */
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += static::ownerBaseFieldDefinitions($entity_type);

    $fields['parent'] = BaseFieldDefinition::create('dynamic_entity_reference')
      ->setDescription(t('The entity where the mention was made.'))
      ->setLabel(t('Parent entity'))
      ->setRequired(TRUE);

    $fields['target'] = BaseFieldDefinition::create('dynamic_entity_reference')
      ->setDescription(t('The mentioned entity.'))
      ->setLabel(t('Mentioned entity'))
      ->setRequired(TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setDescription(t('The time that the mention was created.'))
      ->setLabel(t('Mentioned on'));

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getParentEntity() {
    return $this->get('parent')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getTargetEntity() {
    return $this->get('target')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    return $this->set('created', $timestamp);
  }

  /**
   * {@inheritdoc}
   */
  public function setParentEntity(ContentEntityInterface $entity) {
    return $this->set('parent', $entity);
  }

  /**
   * {@inheritdoc}
   */
  public function setTargetEntity(ContentEntityInterface $entity) {
    return $this->set('target', $entity);
  }

}
