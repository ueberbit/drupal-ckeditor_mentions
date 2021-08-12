<?php

namespace Drupal\ckeditor_mentions\Events;

/**
 * Provides interface for CKEditorMention Suggestion Event.
 *
 * @package Drupal\ckeditor_mentions
 */
interface CKEditorMentionSuggestionEventInterface {

  /**
   * Return the keyword searched by the user.
   *
   * @return string
   *   The keyword.
   */
  public function getKeyword(): string;

  /**
   * Return the array of suggestion generated using the keyword.
   *
   * @return array
   *   Suggestion list.
   */
  public function getSuggestions(): array;

  /**
   * The suggestion list.
   *
   * @param array $suggestions
   *   The suggestion list.
   */
  public function setSuggestions(array $suggestions);

}
