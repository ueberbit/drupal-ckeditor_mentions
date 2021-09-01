<?php

namespace Drupal\ckeditor_mentions_events_test\EventSubscriber;

use Drupal\ckeditor_mentions\Events\CKEditorEvents;
use Drupal\ckeditor_mentions\Events\CKEditorMentionsEvent;
use Drupal\ckeditor_mentions\Events\CKEditorMentionSuggestionsEvent;
use Drupal\ckeditor_mentions_events_test\Exception\SuggestionSuccessTestException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Test events.
 */
class CkeditorMentionsSuggestionSubscriberTest implements EventSubscriberInterface {

  const TEST_SUGGESTION_KEYWORD = 'test_suggestion_event';
  const TEST_SUGGESTION_EXPECT = [
    'user_name' => 'Tested Name',
    'id' => 999,
  ];

  const TEST_MENTIONED_USER_NAME = 'Kekwinko Pesto';

  /**
   * Change suggestions.
   *
   * @param \Drupal\ckeditor_mentions\Events\CKEditorMentionSuggestionsEvent $event
   *   The suggestion event.
   */
  public function onSuggestion(CKEditorMentionSuggestionsEvent $event) {
    if ($event->getKeyword() === self::TEST_SUGGESTION_KEYWORD) {
      $event->setSuggestions(self::TEST_SUGGESTION_EXPECT);
    }
  }

  /**
   * Change suggestions.
   *
   * @param \Drupal\ckeditor_mentions\Events\CKEditorMentionsEvent $event
   *   The mentions' event.
   */
  public function onMentions(CKEditorMentionsEvent $event) {
    if ($event->getMentionedEntity()->getDisplayName() === self::TEST_MENTIONED_USER_NAME) {
      throw new SuggestionSuccessTestException();
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[CKEditorEvents::SUGGESTION][] = ['onSuggestion', -512];
    $events[CKEditorEvents::MENTION_FIRST][] = ['onMentions', -512];

    return $events;
  }

}
