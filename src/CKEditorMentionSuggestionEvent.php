<?php

// @codingStandardsIgnoreFile
namespace Drupal\ckeditor_mentions;

use Drupal\ckeditor_mentions\Events\CKEditorMentionSuggestionEventInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class CKEditorMentionSuggestionEvent.
 *
 * @package Drupal\ckeditor_mentions
 *
 * @deprecated in ckeditor_mentions:2.0.0 and is removed from ckeditor_mentions:3.0.0.
 * Instead use \Drupal\ckeditor_mentions\Events\CKEditorMentionSuggestionEventInterface in the Event subscriber type declaration.
 */
class CKEditorMentionSuggestionEvent extends Event implements CKEditorMentionSuggestionEventInterface {

  /**
   * @deprecated in ckeditor_mentions:2.0.0 and is removed from ckeditor_mentions:3.0.0.
   * Instead use \Drupal\ckeditor_mentions\Events\CKEditorEvents::SUGGESTION.
   */
  const SUGGESTION = 'ckeditor_mentions.suggestion';

  /**
   * Event CKEditorMentionSuggestionEvent.
   *
   * @var \Drupal\ckeditor_mentions\Events\CKEditorMentionSuggestionsEvent
   */
  protected $event;

  /**
   * CKEditorMentionSuggestionEvent constructor.
   *
   * @param string $keyword
   *   The keyword  used by the user.
   */
  public function __construct(string $keyword) {
    $this->event = new Events\CKEditorMentionSuggestionsEvent($keyword, []);
  }

  /**
   * Return the keyword searched by the user.
   *
   * @return string
   *   The keyword.
   */
  public function getKeyword(): string {
    @trigger_error('CKEditorMentionSuggestionEvent::getKeyword() is deprecated in ckeditor_mentions:2.0.0 and will be removed before ckeditor_mentions:3.0.0. Instead CKEditorMentionSuggestionsEvent::getKeyword().');
    return $this->event->getKeyword();
  }

  /**
   * Return the array of suggestion generated using the keyword.
   *
   * @return array
   *   Suggestion list.]
   */
  public function getSuggestions(): array {
    @trigger_error('CKEditorMentionSuggestionEvent::getSuggestions() is deprecated in ckeditor_mentions:2.0.0 and will be removed before ckeditor_mentions:3.0.0. Instead CKEditorMentionSuggestionsEvent::getSuggestions().');
    return $this->event->getSuggestions();
  }

  /**
   * The suggestion list.
   *
   * @param array $suggestions
   *   The suggestion list.
   *
   * @deprecated in ckeditor_mentions:2.0.0 and is removed from ckeditor_mentions:3.0.0.
   */
  public function setSuggestions(array $suggestions) {
    @trigger_error('CKEditorMentionSuggestionEvent::setSuggestions() is deprecated in ckeditor_mentions:2.0.0 and will be removed before ckeditor_mentions:3.0.0. Instead CKEditorMentionSuggestionsEvent::setSuggestions().');
    $this->event->setSuggestions($suggestions);
  }

}
