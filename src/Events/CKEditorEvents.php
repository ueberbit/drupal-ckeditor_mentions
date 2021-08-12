<?php

namespace Drupal\ckeditor_mentions\Events;

/**
 * Contains all events thrown by ckeditor_mentions.
 */
final class CKEditorEvents {

  /**
   * The MENTION_FIRST event.
   *
   * The event occurs when the user was mentioned for the first time.
   *
   * @var string
   */
  const MENTION_FIRST = 'event.mention';

  /**
   * The MENTION_SUBSEQUENT event.
   *
   * The event occurs when the user was mentioned at all except first time.
   *
   * @var string
   */
  const MENTION_SUBSEQUENT = 'event.mention_subsequent';

  /**
   * The SUGGESTION event.
   *
   * The event occurs before suggestions are display to user.
   *
   * @var string
   */
  const SUGGESTION = 'ckeditor_mentions.suggestion';

}
