<?php

namespace Drupal\ckeditor_mentions\Plugin\CKEditorPlugin;

use Drupal\ckeditor\CKEditorPluginBase;
use Drupal\ckeditor\CKEditorPluginConfigurableInterface;
use Drupal\ckeditor\CKEditorPluginContextualInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\SubformState;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\editor\Entity\Editor;
use Drupal\ckeditor\CKEditorPluginCssInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines the "mentions" plugin.
 *
 * @CKEditorPlugin(
 *   id = "mentions",
 *   label = @Translation("Mentions"),
 *   module = "ckeditor_mentions"
 * )
 */
class Mentions extends CKEditorPluginBase implements CKEditorPluginConfigurableInterface, CKEditorPluginContextualInterface, CKEditorPluginCssInterface, ContainerFactoryPluginInterface {

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
  public function getDependencies(Editor $editor) {
    return ['autocomplete', 'textmatch', 'ajax', 'xml', 'textwatcher'];
  }

  /**
   * {@inheritdoc}
   */
  public function getButtons() {
    return [];
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
  public function getFile() {
    return 'libraries/ckeditor/plugins/mentions/plugin.js';
  }

  /**
   * {@inheritdoc}
   */
  public function isEnabled(Editor $editor) {
    $settings = $editor->getSettings();

    return (isset($settings['plugins']['mentions']['enable']) && $settings['plugins']['mentions']['enable']);
  }

  /**
   * {@inheritdoc}
   */
  public function getCssFiles(Editor $editor) {
    return [];
  }

  /**
   * {@inheritDoc}
   */
  public function getLibraries(Editor $editor) {
    return [
      'ckeditor_mentions/ckeditor_mentions',
    ] + parent::getLibraries($editor);
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state, Editor $editor) {
    $settings = $editor->getSettings()['plugins']['mentions'] ?? [];
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
      '#parents' => ['editor', 'settings', 'plugins', 'mentions'],
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
        ->getValue(['editor', 'settings', 'plugins', 'mentions']) :
      $form_state->getValue(['editor', 'settings', 'plugins', 'mentions']);
  }

}
