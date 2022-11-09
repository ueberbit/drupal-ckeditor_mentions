<?php

namespace Drupal\ckeditor_mentions\Plugin\CKEditor5Plugin;

use Drupal\ckeditor\CKEditorPluginBase;
use Drupal\ckeditor\CKEditorPluginConfigurableInterface;
use Drupal\ckeditor\CKEditorPluginContextualInterface;
use Drupal\ckeditor\CKEditorPluginCssInterface;
use Drupal\ckeditor5\Plugin\CKEditor5PluginConfigurableInterface;
use Drupal\ckeditor5\Plugin\CKEditor5PluginConfigurableTrait;
use Drupal\ckeditor5\Plugin\CKEditor5PluginDefault;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\SubformState;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\editor\EditorInterface;
use Drupal\editor\Entity\Editor;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines the "mentions" plugin.
 *
 * @CKEditor5Plugin(
 *   id = "ckeditor_mentions_mentions",
 *   ckeditor5 = @CKEditor5AspectsOfCKEditor5Plugin(
 *   plugins = {
 *     "mention.Mention"
 *   }
 *   ),
 *   drupal = @DrupalAspectsOfCKEditor5Plugin(
 *     label = @Translation("Mentions"),
 *     library = "ckeditor_mentions/ckeditor.plugin.mention",
 *     elements = {
 *      "<span>"
 *       }
 *   )
 * )
 */
class Mentions extends CKEditor5PluginDefault implements CKEditor5PluginConfigurableInterface, ContainerFactoryPluginInterface {

  use CKEditor5PluginConfigurableTrait;

  const DEFAULT_PLUGIN = 'user';

  /**
   * Mentions Plugin Manager.
   *
   * @var \Drupal\ckeditor_mentions\MentionsType\MentionsTypeManager
   */
  protected $mentionsPluginManager;

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->mentionsPluginManager = $container->get('plugin.manager.mentions_type');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getConfig(Editor $editor) {
    $settings = $editor->getSettings()['plugins']['mentions'];

    $type = $settings['mentions_type'];
    /** @var \Drupal\ckeditor_mentions\MentionsType\MentionsTypeBase $plugin */
    $plugin = $this->mentionsPluginManager->createInstance($type);

    $config = [
      'mentions' => [
        [
          'throttle' => $settings['timeout'],
          'minChars' => $settings['charcount'],
          'feed' => '/ckeditor-mentions/ajax/' . $type . '/{encodedQuery}',
          'itemTemplate' => $plugin->getItemTemplate(),
          'outputTemplate' => $plugin->getOutputTemplate(),
          'marker' => $settings['marker'],
        ],
      ],
    ];

    if ($settings['use_advanced_pattern']) {
      $config['mentions'][0]['pattern'] = $settings['advanced_pattern'];
    }
    elseif ($settings['pattern']) {
      $config['mentions'][0]['pattern'] = $this->generatePattern($settings);
    }

    return $config;
  }

  /**
   * Generate regex pattern that will be used to match queries.
   *
   * @var array $settings
   *   Plugin settings.
   *
   * @return string
   *   Pattern.
   */
  protected function generatePattern(array $settings): string {
    $min_chars = $settings['charcount'];
    $pattern = $settings['marker'] . $settings['pattern'];

    if ($min_chars) {
      $pattern .= '{' . $min_chars . ',}';
    }
    else {
      $pattern .= '*';
    }

    $pattern .= '$';

    return $pattern;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $settings = $this->getConfiguration();

    $form['enable'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Mentions'),
      '#default_value' => !empty($settings['enable']) ? $settings['enable'] : FALSE,
      '#attributes' => ['data-use-mentions' => TRUE],
    ];

    $default_plugin_id = !empty($settings['mentions_type']) ? $settings['mentions_type'] : self::DEFAULT_PLUGIN;

    $form['mentions_type'] = [
      '#type' => 'select',
      '#options' => $this->mentionsPluginManager->getAllMentionsTypes(),
      '#default_value' => $default_plugin_id,
      '#description' => $this->t('Choose the mentions plugin'),
    ];

    $form['timeout'] = [
      '#type' => 'number',
      '#min' => 1,
      '#title' => $this->t('Timeout (milliseconds)'),
      '#description' => $this->t('Enter time in milliseconds for mentions script to stop checking for matches.'),
      '#default_value' => !empty($settings['timeout']) ? $settings['timeout'] : 500,
    ];

    $form['advanced'] = [
      '#type' => 'details',
      '#parents' => ['editor', 'settings', 'plugins', 'ckeditor_mentions_mentions'],
      '#tree' => TRUE,
      '#title' => $this->t('Advanced settings'),
      '#open' => FALSE,
    ];

    $form['advanced']['marker'] = [
      '#type' => 'textfield',
      '#maxlength' => '1',
      '#title' => $this->t('Marker'),
      '#description' => $this->t('The character that should trigger autocompletion.'),
      '#default_value' => !empty($settings['marker']) ? $settings['marker'] : '@',
      '#states' => [
        'visible' => [
          ':input[data-use-advanced]' => ['checked' => FALSE],
        ],
        'required' => [
          ':input[data-use-advanced]' => ['checked' => FALSE],
          ':input[data-use-mentions]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['advanced']['charcount'] = [
      '#type' => 'number',
      '#min' => 0,
      '#title' => $this->t('Character Count'),
      '#description' => $this->t('Enter minimum number of characters that must be typed to trigger mention match.'),
      '#default_value' => !empty($settings['charcount']) ? $settings['charcount'] : 2,
      '#states' => [
        'visible' => [
          ':input[data-use-advanced]' => ['checked' => FALSE],
        ],
      ],
    ];

    $form['advanced']['use_advanced_pattern'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use advanced pattern?'),
      '#default_value' => !empty($settings['use_advanced_pattern']) ? $settings['use_advanced_pattern'] : FALSE,
      '#attributes' => ['data-use-advanced' => TRUE],
    ];

    $form['advanced']['pattern'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Pattern'),
      '#description' => $this->t('The pattern used to match queries. <br /> Leave empty to use default CKeditor pattern. <br/> <em>Note: you should not include marker and min characters in pattern, they will be automatically generated. Use advanced pattern for this.</em><br /> Example: Pattern to match Cyrillic characters: <code>[а-яА-Я]</code>'),
      '#default_value' => !empty($settings['pattern']) ? $settings['pattern'] : '',
      '#states' => [
        'visible' => [
          ':input[data-use-advanced]' => ['checked' => FALSE],
        ],
      ],
    ];

    $form['advanced']['advanced_pattern'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Advanced pattern'),
      '#default_value' => !empty($settings['advanced_pattern']) ? $settings['advanced_pattern'] : '',
      '#description' => $this->t('Be careful! You should include marker and characters count in this pattern by yourself! <br /> Example: Pattern to match two words with space in between. <code>@([_a-zA-Z0-9À-ž]{2,}\s?)([_a-zA-Z0-9À-ž]*)?$</code>'),
      '#states' => [
        'visible' => [
          ':input[data-use-advanced]' => ['checked' => TRUE],
        ],
        'required' => [
          ':input[data-use-advanced]' => ['checked' => TRUE],
          ':input[data-use-mentions]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['mentions_type']['#element_validate'][] = [$this, 'isRequired'];

    return $form;
  }

  /**
   * Check if plugin is enabled.
   *
   * @param array $element
   *   The Form element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The FormState Object.
   */
  public function isRequired(array $element, FormStateInterface $form_state) {
    $settings = $this->getMentionsStateValues($form_state);
    $parents = $element['#parents'];
    $element_name = array_pop($parents);

    if ($settings['enable'] && !$settings[$element_name]) {
      $form_state->setError($element, $this->t('@type field is required.', ['@type' => $element_name]));
    }
  }

  /**
   * Get current values from form state.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   *
   * @return array
   *   Values
   */
  protected function getMentionsStateValues(FormStateInterface $form_state) {
    return $form_state instanceof SubformState ?
      $form_state->getCompleteFormState()
        ->getValue(['editor', 'settings', 'plugins', 'ckeditor_mentions_mentions']) :
      $form_state->getValue(['editor', 'settings', 'plugins', 'ckeditor_mentions_mentions']);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'enable' => FALSE,
      'mentions_type' => self::DEFAULT_PLUGIN,
      'timeout' => 500,
      'marker' => '@',
      'charcount' => 2,
      'use_advanced_pattern' => FALSE,
      'pattern' => '',
      'advanced_pattern' => '',
    ];
  }

  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    // TODO: Implement validateConfigurationForm() method.
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    $this->configuration['enable'] = (bool) $form_state->getValue('enable');
    $this->configuration['mentions_type'] = $form_state->getValue('mentions_type');
    $this->configuration['timeout'] = (int) $form_state->getValue('timeout');
    $this->configuration['marker'] = $form_state->getValue('marker');
    $this->configuration['charcount'] = (int) $form_state->getValue('charcount');
    $this->configuration['use_advanced_pattern'] = (bool) $form_state->getValue('use_advanced_pattern');
    $this->configuration['pattern'] = $form_state->getValue('pattern');
    $this->configuration['advanced_pattern'] = $form_state->getValue('advanced_pattern');
  }

  public function getDynamicPluginConfig(array $static_plugin_config, EditorInterface $editor): array {
    if (!$this->configuration['enable']) {
      return [];
    }

    $options = [
      'feeds' => [
        [
          'marker' => $this->configuration['marker'],
          'feed' => [
            'func' => [
              'name' => 'Drupal.ckeditor5DrupalMention',
              'args' => [
                [
                  'type' => 'user',
                  'url' => Url::fromRoute('ckeditor_mentions.ajax_callback', ['plugin_id' => 'user', 'match' => '--match--'])->setAbsolute()->toString(),
                  'marker' => $this->configuration['marker'],
                ],
              ],
              'invoke' => TRUE,
            ],
          ],
          'itemRenderer' => [
            'func' => [
              'name' => 'Drupal.ckeditor5DrupalMentionUser.itemRenderer',
            ],
          ],
          'minimumCharacters' => $this->configuration['charcount'],
          'drupalMentionsType' => 'user',
        ],
      ],
    ];

    return ['mention' => $options];
  }

}
