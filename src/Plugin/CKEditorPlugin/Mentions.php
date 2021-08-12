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
    $settings = $editor->getSettings();

    $type = $settings['plugins']['mentions']['mentions_type'];
    /** @var \Drupal\ckeditor_mentions\MentionsType\MentionsTypeBase $plugin */
    $plugin = $this->mentionsPluginManager->createInstance($type);

    return [
      'mentions' => [
        [
          'throttle' => $settings['plugins']['mentions']['timeout'],
          'minChars' => $settings['plugins']['mentions']['charcount'],
          'feed' => '/ckeditor-mentions/ajax/' . $type . '/{encodedQuery}',
          'itemTemplate' => $plugin->getItemTemplate(),
          'outputTemplate' => $plugin->getOutputTemplate(),
        ],
      ],
    ];
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
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state, Editor $editor) {
    $settings = $editor->getSettings();
    $form['enable'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Mentions'),
      '#default_value' => !empty($settings['plugins']['mentions']['enable']) ? $settings['plugins']['mentions']['enable'] : FALSE,
    ];

    $default_plugin_id = !empty($settings['plugins']['mentions']['mentions_type']) ? $settings['plugins']['mentions']['mentions_type'] : self::DEFAULT_PLUGIN;

    $form['mentions_type'] = [
      '#type' => 'select',
      '#options' => $this->mentionsPluginManager->getAllMentionsTypes(),
      '#default_value' => $default_plugin_id,
      '#description' => $this->t('Choose the mentions plugin'),
    ];

    $form['charcount'] = [
      '#type' => 'number',
      '#min' => 0,
      '#title' => $this->t('Character Count'),
      '#description' => $this->t('Enter minimum number of characters that must be typed to trigger mention match.'),
      '#default_value' => !empty($settings['plugins']['mentions']['charcount']) ? $settings['plugins']['mentions']['charcount'] : 2,
    ];

    $form['timeout'] = [
      '#type' => 'number',
      '#min' => 1,
      '#title' => $this->t('Timeout (milliseconds)'),
      '#description' => $this->t('Enter time in milliseconds for mentions script to stop checking for matches.'),
      '#default_value' => !empty($settings['plugins']['mentions']['timeout']) ? $settings['plugins']['mentions']['timeout'] : 500,
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
    if ($settings['enable'] && !$settings['mentions_type']) {
      $form_state->setError($element, $this->t('Mentions type field is required.'));
    }
  }

  /**
   * Get current values from from state.
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
