<?php

namespace Drupal\Tests\ckeditor_mentions\Kernel;

use Drupal\user\Entity\User;

/**
 * @coversDefaultClass \Drupal\ckeditor_mentions\Plugin\MentionsType\User
 *
 * @group ckeditor_mentions
 */
class CkeditorMentionsUserTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['ckeditor_mentions_user_test'];

  /**
   * @covers ::buildResponse
   */
  public function testUser() {
    $user_1 = User::create([
      'name' => 'Jhon Doe',
    ]);
    $user_1->save();
    $user_2 = User::create([
      'name' => 'Jhon Derm',
    ]);
    $user_2->save();
    $user_3 = User::create([
      'name' => 'Jewish Kontrator',
    ]);
    $user_3->save();

    $plugin_manager = $this->container->get('plugin.manager.mentions_type');
    /** @var \Drupal\ckeditor_mentions_user_test\Plugin\MentionsType\UserTest $plugin */
    $plugin = $plugin_manager->createInstance('user_test', [
      'match' => 'Jho',
    ]);

    $response = $plugin->buildResponse();

    $expected = [];
    /** @var \Drupal\user\Entity\User $user */
    foreach ([$user_1, $user_2] as $user) {
      $expected[$user->id()] = [
        'id' => $user->id(),
        'name' => $user->uuid(),
        'user_name' => $user->getDisplayName(),
      ];
    }
    $this->assertIdentical($response, $expected);

    $plugin->setMatch('Kip');
    $response = $plugin->buildResponse();

    $this->assertEmpty($response);
  }

  /**
   * @covers ::preprocessQuery
   */
  public function testQueryAlter() {
    $user = User::create([
      'name' => 'Jhon Doe',
    ]);
    $user->save();

    $plugin_manager = $this->container->get('plugin.manager.mentions_type');
    /** @var \Drupal\ckeditor_mentions_user_test\Plugin\MentionsType\UserTest $plugin */
    $plugin = $plugin_manager->createInstance('user_test', [
      'match' => 'test_query_alter',
    ]);
    $response = $plugin->buildResponse();

    $this->assertNotEmpty($response);
    $this->assertEqual(current($response)['id'], $user->id());
  }

}
