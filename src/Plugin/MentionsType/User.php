<?php

namespace Drupal\ckeditor_mentions\Plugin\MentionsType;

use Drupal\ckeditor_mentions\MentionsType\MentionsTypeBase;
use Drupal\Core\Database\Query\AlterableInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a user mentions type.
 *
 * @MentionsType(
 *   id = "user",
 *   label = @Translation("User"),
 *   entity_type= "user"
 * )
 */
class User extends MentionsTypeBase {

  /**
   * Module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandler
   */
  protected $moduleHandler;

  /**
   * The path to the placeholder image.
   *
   * @var string
   */
  protected $placeholderImage;

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->moduleHandler = $container->get('module_handler');
    $instance->placeholderImage = base_path() . $instance->moduleHandler->getModule('ckeditor_mentions')->getPath() . '/img/placeholder.png';
    return $instance;
  }

  /**
   * {@inheritDoc}
   */
  protected function getQuery(): AlterableInterface {
    $query = $this->entityManager
      ->getStorage($this->getPluginDefinition()['entity_type'])
      ->getQuery()
      ->accessCheck(TRUE);

    $or = $query->orConditionGroup()
      ->condition('name', $this->getMatch(), 'CONTAINS')
      ->condition('mail', $this->getMatch(), 'CONTAINS');

    return $query->condition($or);
  }

  /**
   * {@inheritDoc}
   */
  public function buildTokens(array $entities): array {
    $response_array = parent::buildTokens($entities);

    // The image style to use.
    // Move style type into configuration?
    /** @var \Drupal\image\Entity\ImageStyle $style */
    $style = $this->entityManager->getStorage('image_style')->load('mentions_icon');

    foreach ($entities as $id => $user) {
      $user_image_url = NULL;
      if ($user->hasField('user_picture') && !$user->user_picture->isEmpty() && $style) {
        $user_image_url = $style->buildUrl($user->user_picture->entity->getFileUri());
      }

      // All the array keys are considered as tokens for item/output templates.
      // @see static::itemTemplate
      // @see static::outputTemplate
      $response_array[$id] += [
        'user_name' => $user->getDisplayName(),
        'email' => $user->getEmail(),
        'avatar' => $user_image_url ?? $this->placeholderImage,
        'user_page' => $user->toUrl()->toString(),
      ];
    }

    return array_values($response_array);
  }

  /**
   * {@inheritDoc}
   */
  protected function outputTemplate() {
    $output = parent::outputTemplate();
    $output['#value'] = '@{user_name}';
    $output['#attributes']['href'] = '{user_page}';
    return $output;
  }

  /**
   * {@inheritDoc}
   */
  protected function itemTemplate() {
    $item = parent::itemTemplate();
    $item['image'] = [
      '#type' => 'html_tag',
      '#tag' => 'img',
      '#attributes' => [
        'src' => '{avatar}',
        'class' => ['photo'],
      ],
      'name' => [
        '#type' => 'html_tag',
        '#tag' => 'strong',
        '#attributes' => ['class' => ['ckeditor-mentions']],
        '#value' => '{user_name}',
      ],
    ];
    return $item;
  }

}
