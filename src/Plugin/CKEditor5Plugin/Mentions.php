<?php

namespace Drupal\ckeditor_mentions\Plugin\CKEditor5Plugin;

use Drupal\ckeditor5\Plugin\CKEditor5PluginConfigurableInterface;
use Drupal\ckeditor5\Plugin\CKEditor5PluginConfigurableTrait;
use Drupal\ckeditor5\Plugin\CKEditor5PluginDefault;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\editor\EditorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines the "mentions" plugin.
 *
 * @CKEditor5Plugin(
 *   id = "ckeditor_mentions_mentions",
 *   ckeditor5 = @CKEditor5AspectsOfCKEditor5Plugin(
 *     plugins = {
 *       "mention.Mention"
 *     }
 *   ),
 *   drupal = @DrupalAspectsOfCKEditor5Plugin(
 *     label = @Translation("Mentions"),
 *     library = "ckeditor_mentions/ckeditor.plugin.mention",
 *     elements = {
 *       "<a>",
 *       "<a class data-mention data-mention-uuid data-entity-id data-entity-uuid data-plugin href>"
 *     }
 *   )
 * )
 */
class Mentions extends CKEditor5PluginDefault implements CKEditor5PluginConfigurableInterface, ContainerFactoryPluginInterface {

  use CKEditor5PluginConfigurableTrait;

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
    $settings = $this->configuration;

    foreach ($this->mentionsPluginManager->getAllMentionsTypes() as $mentionsType => $mentionsLabel) {
      $form['plugins'][$mentionsType]['#type'] = 'fieldset';
      $form['plugins'][$mentionsType]['enable'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Enable Mentions: %type', ['%type' => $mentionsLabel]),
        '#default_value' => !empty($settings['plugins'][$mentionsType]['enable']) ? $settings['plugins'][$mentionsType]['enable'] : FALSE,
        '#attributes' => ['data-use-mentions--' . $mentionsType => TRUE],
      ];

      $form['plugins'][$mentionsType]['timeout'] = [
        '#type' => 'number',
        '#min' => 1,
        '#title' => $this->t('Timeout (milliseconds)'),
        '#description' => $this->t('Enter time in milliseconds for mentions script to stop checking for matches.'),
        '#default_value' => !empty($settings['plugins'][$mentionsType]['timeout']) ? $settings['plugins'][$mentionsType]['timeout'] : 500,
        '#states' => [
          'visible' => [
            ':input[data-use-mentions--' . $mentionsType . ']' => ['checked' => TRUE],
          ],
        ]
      ];

      $form['plugins'][$mentionsType]['marker'] = [
        '#type' => 'textfield',
        '#maxlength' => '1',
        '#title' => $this->t('Marker'),
        '#description' => $this->t('The character that should trigger autocompletion.'),
        '#default_value' => !empty($settings['plugins'][$mentionsType]['marker']) ? $settings['plugins'][$mentionsType]['marker'] : '@',
        '#states' => [
          'visible' => [
            ':input[data-use-advanced--' . $mentionsType . ']' => ['checked' => FALSE],
            ':input[data-use-mentions--' . $mentionsType . ']' => ['checked' => TRUE],
          ],
          'required' => [
            ':input[data-use-advanced--' . $mentionsType . ']' => ['checked' => FALSE],
            ':input[data-use-mentions--' . $mentionsType . ']' => ['checked' => TRUE],
          ],
        ],
      ];

      $form['plugins'][$mentionsType]['charcount'] = [
        '#type' => 'number',
        '#min' => 0,
        '#title' => $this->t('Character Count'),
        '#description' => $this->t('Enter minimum number of characters that must be typed to trigger mention match.'),
        '#default_value' => !empty($settings['plugins'][$mentionsType]['charcount']) ? $settings['plugins'][$mentionsType]['charcount'] : 2,
        '#states' => [
          'visible' => [
            ':input[data-use-advanced--' . $mentionsType . ']' => ['checked' => FALSE],
            ':input[data-use-mentions--' . $mentionsType . ']' => ['checked' => TRUE],
          ],
        ],
      ];

      $form['plugins'][$mentionsType]['use_advanced_pattern'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Use advanced pattern?'),
        '#default_value' => !empty($settings['plugins'][$mentionsType]['use_advanced_pattern']) ? $settings['plugins'][$mentionsType]['use_advanced_pattern'] : FALSE,
        '#attributes' => ['data-use-advanced--' . $mentionsType => TRUE],
        '#states' => [
          'visible' => [
            ':input[data-use-mentions--' . $mentionsType . ']' => ['checked' => TRUE],
          ],
        ],
      ];

      $form['plugins'][$mentionsType]['pattern'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Pattern'),
        '#description' => $this->t('The pattern used to match queries. <br /> Leave empty to use default CKeditor pattern. <br/> <em>Note: you should not include marker and min characters in pattern, they will be automatically generated. Use advanced pattern for this.</em><br /> Example: Pattern to match Cyrillic characters: <code>[а-яА-Я]</code>'),
        '#default_value' => !empty($settings['plugins'][$mentionsType]['pattern']) ? $settings['plugins'][$mentionsType]['pattern'] : '',
        '#states' => [
          'visible' => [
            ':input[data-use-advanced--' . $mentionsType . ']' => ['checked' => FALSE],
            ':input[data-use-mentions--' . $mentionsType . ']' => ['checked' => TRUE],
          ],
        ],
      ];

      $form['plugins'][$mentionsType]['advanced_pattern'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Advanced pattern'),
        '#default_value' => !empty($settings['plugins'][$mentionsType]['advanced_pattern']) ? $settings['plugins'][$mentionsType]['advanced_pattern'] : '',
        '#description' => $this->t('Be careful! You should include marker and characters count in this pattern by yourself! <br /> Example: Pattern to match two words with space in between. <code>@([_a-zA-Z0-9À-ž]{2,}\s?)([_a-zA-Z0-9À-ž]*)?$</code>'),
        '#states' => [
          'visible' => [
            ':input[data-use-advanced--' . $mentionsType . ']' => ['checked' => TRUE],
            ':input[data-use-mentions--' . $mentionsType . ']' => ['checked' => TRUE],
          ],
          'required' => [
            ':input[data-use-advanced--' . $mentionsType . ']' => ['checked' => TRUE],
            ':input[data-use-mentions--' . $mentionsType . ']' => ['checked' => TRUE],
          ],
        ],
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'plugins' => [],
    ];
  }

  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    // TODO: Implement validateConfigurationForm() method.
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    $configuration = [];

    foreach ($this->mentionsPluginManager->getAllMentionsTypes() as $mentionsType => $mentionsLabel) {
      $mentionsTypeConfiguration = $form_state->getValue([
        'plugins',
        $mentionsType
      ]);
      if ($mentionsTypeConfiguration['enable']) {
        $mentionsTypeConfiguration['id'] = $mentionsType;
        $mentionsTypeConfiguration['enable'] = (bool) $mentionsTypeConfiguration['enable'];
        $mentionsTypeConfiguration['charcount'] = (int) $mentionsTypeConfiguration['charcount'];
        $mentionsTypeConfiguration['timeout'] = (int) $mentionsTypeConfiguration['timeout'];
        $mentionsTypeConfiguration['use_advanced_pattern'] = (bool) $mentionsTypeConfiguration['use_advanced_pattern'];
        $configuration[$mentionsType] = $mentionsTypeConfiguration;
      }
    }

    $this->configuration['plugins'] = $configuration;
  }

  public function getDynamicPluginConfig(array $static_plugin_config, EditorInterface $editor): array {
    $options = [];

    foreach ($this->configuration['plugins'] as $settings) {
      $options['feeds'][] = [
        'marker' => $settings['marker'],
        'feed' => [
          'func' => [
            'name' => 'Drupal.ckeditor5DrupalMention',
            'args' => [
              [
                'type' => $settings['id'],
                'url' => Url::fromRoute('ckeditor_mentions.ajax_callback', [
                  'plugin_id' => $settings['id'],
                  'match' => '--match--',
                ])->setAbsolute()->toString(),
                'marker' => $settings['marker'],
              ],
            ],
            'invoke' => TRUE,
          ],
        ],
        'itemRenderer' => [
          'func' => [
            'name' => 'Drupal.ckeditor5DrupalMentionItemRenderer',
          ],
        ],
        'minimumCharacters' => $settings['charcount'],
        'drupalMentionsType' => $settings['id'],
      ];
    }

    if (!$options) {
      return [];
    }

    return ['mention' => $options];
  }

}
