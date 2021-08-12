<?php

namespace Drupal\ckeditor_mentions\Controller;

use Drupal\ckeditor_mentions\MentionsType\MentionsTypeManagerInterface;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Route callback for matches.
 */
class CKMentionsController extends ControllerBase {

  /**
   * Mention type manager.
   *
   * @var \Drupal\ckeditor_mentions\MentionsType\MentionsTypeManagerInterface
   */
  protected $mentionsManager;

  /**
   * CKMentionsController constructor.
   *
   * @param \Drupal\ckeditor_mentions\MentionsType\MentionsTypeManagerInterface $mentionsTypeManager
   *   Mention type manager.
   */
  public function __construct(MentionsTypeManagerInterface $mentionsTypeManager) {
    $this->mentionsManager = $mentionsTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('plugin.manager.mentions_type')
    );
  }

  /**
   * Return a list of suggestions based in the keyword provided by the user.
   *
   * @param string $plugin_id
   *   The plugin id.
   * @param string $match
   *   Match value.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Json of matches.
   */
  public function getMatch(string $plugin_id, string $match = ''): JsonResponse {
    /** @var \Drupal\ckeditor_mentions\MentionsType\MentionsTypeBase $plugin */
    $plugin = $this->mentionsManager->createInstance($plugin_id, ['match' => $match]);

    return new JsonResponse($plugin->buildResponse());
  }

}
