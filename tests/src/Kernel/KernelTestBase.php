<?php

namespace Drupal\Tests\ckeditor_mentions\Kernel;

use Drupal\editor\Entity\Editor;
use Drupal\filter\Entity\FilterFormat;
use Drupal\KernelTests\KernelTestBase as KernelTestBaseCore;

/**
 * Base kernel test class for ckeditor mentions module.
 *
 * @group ckeditor_mentions
 */
class KernelTestBase extends KernelTestBaseCore {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'user',
    'node',
    'filter',
    'editor',
    'ckeditor',
    'ckeditor_mentions',
  ];

  /**
   * Editor.
   *
   * @var \Drupal\editor\Entity\Editor
   */
  protected $editor;

  /**
   * Filter format.
   *
   * @var \Drupal\filter\Entity\FilterFormat
   */
  protected $format;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installSchema('system', ['sequences']);
    $this->installEntitySchema('user');
    $this->installEntitySchema('editor');
    $this->installEntitySchema('filter_format');

    $this->format = FilterFormat::create([
      'format' => 'filtered_html',
      'name' => $this->randomMachineName(),
    ]);
    $this->format->save();

    $this->editor = Editor::create([
      'editor' => 'ckeditor',
      'format' => 'filtered_html',
      'settings' => [
        'plugins' => [
          'mentions' => [
            'enable'  => TRUE,
            'mentions_type' => 'user',
            'timeout' => '500',
            'charcount' => '2',
          ],
        ],
      ],
    ]);
    $this->editor->save();
  }

}
