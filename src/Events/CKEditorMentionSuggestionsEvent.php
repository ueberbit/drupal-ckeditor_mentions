<?php

namespace Drupal\ckeditor_mentions\Events;

use Symfony\Component\EventDispatcher\Event;

/**
 * Provides an Ckeditor mentions suggestion event.
 *
 * @package Drupal\ckeditor_mentions
 */
class CKEditorMentionSuggestionsEvent extends Event implements CKEditorMentionSuggestionEventInterface {

  /**
   * The keyword used by the user.
   *
   * @var string
   */
  protected $keyword;

  /**
   * The suggestion generated using the keyword.
   *
   * @var array
   */
  protected $suggestions = [];

  /**
   * CKEditorMentionSuggestionsEvent constructor.
   *
   * @param string $keyword
   *   The keyword  used by the user.
   * @param array $suggestions
   *   Suggestions.
   */
  public function __construct(string $keyword, array $suggestions) {
    $this->keyword = $keyword;
    $this->suggestions = $suggestions;
  }

  /**
   * {@inheritDoc}
   */
  public function getKeyword(): string {
    return $this->keyword;
  }

  /**
   * {@inheritDoc}
   */
  public function getSuggestions(): array {
    return $this->suggestions;
  }

  /**
   * {@inheritDoc}
   */
  public function setSuggestions(array $suggestions) {
    $this->suggestions = $suggestions;
  }

}
