<?php

namespace Drupal\Tests\ckeditor_mentions\Kernel;

use Drupal\ckeditor_mentions\Events\CKEditorEvents;
use Drupal\ckeditor_mentions_events_test\EventSubscriber\CkeditorMentionsSuggestionSubscriberTest as TestSubscriber;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\user\Entity\User;

/**
 * @coversDefaultClass \Drupal\ckeditor_mentions\MentionEventDispatcher
 *
 * @group ckeditor_mentions
 */
class CkeditorMentionsEventsTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['ckeditor_mentions_events_test'];

  /**
   * Dispatcher.
   *
   * @var \Drupal\ckeditor_mentions\MentionEventDispatcher
   */
  protected $dispatcher;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->dispatcher = $this->container->get('ckeditor_mentions.mention_event_dispatcher');
  }

  /**
   * {@inheritDoc}
   */
  public function register(ContainerBuilder $container) {
    $container
      ->register('ckeditor_mentions_events_test.mention_event_subscriber', TestSubscriber::class)
      ->addTag('event_subscriber');
    parent::register($container);
  }

  /**
   * @covers ::getMentionedEntities
   */
  public function testMentionedEntities() {
    $user = User::create([
      'name' => 'Kekwinko Pesto',
    ]);
    $user->save();
    $id = $user->id();
    $search_string = "<p><a data-mention=\"$id\" data-plugin=\"user\" href=\"/user/3\">@Kekwinko Pesto</a></p>";

    $entities = $this->dispatcher->getMentionedEntities($search_string);

    $this->assertNotEmpty($entities);
    $this->assertEqual($entities[$id]['entity']->id(), $user->id());

    $search_string = "<p><a data-mention=\"$id\" data-plugin=\"not_existing\" href=\"/user/3\">@Kekwinko Pesto</a></p>";
    $this->expectException(PluginNotFoundException::class);
    $this->dispatcher->getMentionedEntities($search_string);
  }

  /**
   * @covers ::getTexformatsUsingMentions
   */
  public function testTestFormatUsingMentions() {
    $formats = $this->dispatcher->getTexformatsUsingMentions();
    $this->assertEqual([$this->format->id()], $formats);

    $setting = $this->editor->getSettings();
    $setting['plugins']['mentions']['enable'] = FALSE;
    $this->editor->setSettings($setting);
    $this->editor->save();
    $formats = $this->dispatcher->getTexformatsUsingMentions();
    $this->assertEmpty($formats);
  }

  /**
   * @covers ::dispatchMentionEvent
   */
  public function testDispatch() {
    $user = User::create([
      'name' => TestSubscriber::TEST_MENTIONED_USER_NAME,
    ]);
    $user->save();
    $id = $user->id();

    $this->enableModules(['node', 'field', 'text']);
    $this->installEntitySchema('node');
    $this->installConfig(['field', 'node']);

    $bundle = NodeType::create([
      'type' => 'bundle_test',
      'label' => 'Bundle Test',
    ]);
    $bundle->save();
    node_add_body_field($bundle);

    $body = "<p><a data-mention=\"$id\" data-plugin=\"user\" href=\"/user/3\">@Kekwinko Pesto</a></p>";
    $node = Node::create([
      'type' => $bundle->id(),
      'title' => 'test',
      'body' => [
        'value' => $body,
        'format' => $this->format->id(),
      ],
    ]);

    $this->dispatcher->dispatchMentionEvent($node, CKEditorEvents::MENTION_FIRST);
    $this->assertEqual($node->getTitle(), TestSubscriber::TEST_MENTIONED_USER_NAME);
  }

  /**
   * Test suggestion event.
   */
  public function testSuggestion() {
    $this->enableModules(['ckeditor_mentions_user_test']);
    $user = User::create([
      'name' => TestSubscriber::TEST_SUGGESTION_KEYWORD,
    ]);
    $user->save();

    $plugin_manager = $this->container->get('plugin.manager.mentions_type');
    /** @var \Drupal\ckeditor_mentions_user_test\Plugin\MentionsType\UserTest $plugin */
    $plugin = $plugin_manager->createInstance('user_test', [
      'match' => TestSubscriber::TEST_SUGGESTION_KEYWORD,
    ]);
    $response = $plugin->buildResponse();

    $this->assertEqual($response, TestSubscriber::TEST_SUGGESTION_EXPECT);
  }

}
