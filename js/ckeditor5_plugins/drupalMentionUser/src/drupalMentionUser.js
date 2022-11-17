/**
 * @file This is what CKEditor refers to as a master (glue) plugin. Its role is
 * just to load the “editing” and “UI” components of this Plugin. Those
 * components could be included in this file, but
 *
 * I.e, this file's purpose is to integrate all the separate parts of the plugin
 * before it's made discoverable via index.js.
 */
// cSpell:ignore simpleboxediting simpleboxui

// The contents of SimpleBoxUI and SimpleBox editing could be included in this
// file, but it is recommended to separate these concerns in different files.
import {Plugin} from 'ckeditor5/src/core';

export default class DrupalMentionUser extends Plugin {

  init() {
    const editor = this.editor;
    const mentionTypeToMakerMap = {};
    const markerToMentionTypeMap = {};
    editor.config.get('mention').feeds.forEach((i) => {
      if (i.drupalMentionsType && i.marker) {
        mentionTypeToMakerMap[i.drupalMentionsType] = i.marker;
        markerToMentionTypeMap[i.marker] = i.drupalMentionsType;
      }
    });

    // The upcast converter will convert view <a class="mention" href="" data-plugin="">
    // elements to the model 'mention' text attribute.
    editor.conversion.for('upcast').elementToAttribute({
      view: {
        name: 'a',
        key: 'data-mention',
        classes: 'mention',
        attributes: {
          href: true,
          'data-plugin': true,
        }
      },
      model: {
        key: 'mention',
        value: viewItem => {
          // BC-Layer for older mentions from ckeditor4.
          let mentionId = viewItem.getAttribute('data-mention');
          let mentionPlugin = viewItem.getAttribute('data-plugin');
          let mentionMarker = mentionTypeToMakerMap[mentionPlugin] ?? false;
          if (mentionPlugin !== null && mentionId !== null && mentionMarker && mentionId.charAt(0) !== mentionMarker) {
            viewItem._setAttribute('data-mention', mentionMarker + mentionId);
          }
          // End BC-Layer

          // The mention feature expects that the mention attribute value
          // in the model is a plain object with a set of additional attributes.
          // In order to create a proper object use the toMentionAttribute() helper method:
          const mentionAttribute = editor.plugins.get('Mention').toMentionAttribute(viewItem, {
            // Add any other properties that you need.
            link: viewItem.getAttribute('href'),
            entity_type: viewItem.getAttribute('data-entity-type'),
            entity_uuid: viewItem.getAttribute('data-entity-uuid'),
            mention_uuid: viewItem.getAttribute('data-mention-uuid'),
            plugin: viewItem.getAttribute('data-plugin'),
          });

          return mentionAttribute;
        }
      },
      converterPriority: 'high'
    });

    // Downcast the model 'mention' text attribute to a view <a> element.
    editor.conversion.for('downcast').attributeToElement({
      model: 'mention',
      view: (modelAttributeValue, {writer}) => {
        // Do not convert empty attributes (lack of value means no mention).
        if (!modelAttributeValue) {
          return;
        }

        return writer.createAttributeElement('a', {
          class: 'mention',
          'data-mention': modelAttributeValue.id,
          'data-mention-uuid': modelAttributeValue.mention_uuid ?? null,
          'data-entity-type': modelAttributeValue.entity_type ?? null,
          'data-entity-uuid': modelAttributeValue.entity_uuid ?? null,
          'data-plugin': modelAttributeValue.plugin ?? null,
          'href': modelAttributeValue.link ?? null,
        }, {
          // Make mention attribute to be wrapped by other attribute elements.
          priority: 20,
          // Prevent merging mentions together.
          id: modelAttributeValue.uid
        });
      },
      converterPriority: 'high'
    });
  }

  /**
   * @inheritdoc
   */
  static get pluginName() {
    return 'drupalMentionUser';
  }

}
