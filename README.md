CONTENTS OF THIS FILE
---------------------

* Introduction
* Requirements
* Installation
* Configuration

INTRODUCTION
------------

A module that adds support for CKEditor Mentions plugin.

REQUIREMENTS
------------

Install CKEditor plugins

* With Composer (recommended)

  1. Add this [*installer
   path*](https://getcomposer.org/doc/faqs/how-do-i-install-a-package-to-a-custom-path-for-my-framework.md) to your
   composer json file:
   `"{ROOT_PROJECT_DIRECTORY}/libraries/ckeditor/plugins/{$name}": ["vendor:ckeditor"],`
  2. Add following [repositories](https://getcomposer.org/doc/05-repositories.md) to composer.json
  ```
  {
      "type": "package",
      "package": {
          "name": "ckeditor/mentions",
          "version": "4.14.1",
          "type": "drupal-library",
          "dist": {
              "url": "https://download.ckeditor.com/mentions/releases/mentions_4.14.1.zip",
              "type": "zip"
          }
      }
  },
  {
      "type": "package",
      "package": {
          "name": "ckeditor/autocomplete",
          "version": "4.14.1",
          "type": "drupal-library",
          "dist": {
              "url": "https://download.ckeditor.com/autocomplete/releases/autocomplete_4.14.1.zip",
              "type": "zip"
          }
      }
  },
  {
      "type": "package",
      "package": {
          "name": "ckeditor/textmatch",
          "version": "4.14.1",
          "type": "drupal-library",
          "dist": {
              "url": "https://download.ckeditor.com/textmatch/releases/textmatch_4.14.1.zip",
              "type": "zip"
          }
      }
  },
  {
      "type": "package",
      "package": {
          "name": "ckeditor/textwatcher",
          "version": "4.14.1",
          "type": "drupal-library",
          "dist": {
              "url": "https://download.ckeditor.com/textwatcher/releases/textwatcher_4.14.1.zip",
              "type": "zip"
          }
      }
  },
  {
      "type": "package",
      "package": {
          "name": "ckeditor/ajax",
          "version": "4.14.1",
          "type": "drupal-library",
          "dist": {
              "url": "https://download.ckeditor.com/ajax/releases/ajax_4.14.1.zip",
              "type": "zip"
          }
      }
  },
  {
      "type": "package",
      "package": {
          "name": "ckeditor/xml",
          "version": "4.14.1",
          "type": "drupal-library",
          "dist": {
              "url": "https://download.ckeditor.com/xml/releases/xml_4.14.1.zip",
              "type": "zip"
          }
      }
  }
  ```
  3. Run `composer require ckeditor/mentions ckeditor/autocomplete ckeditor/textmatch ckeditor/textwatcher ckeditor/ajax ckeditor/xml`
* Manually.

  1. Download the [Full "dev" package for CKEditor](https://github.com/ckeditor/ckeditor-dev/archive/latest.zip).
  2. Unzip the package and place its contents into
     `DRUPAL_ROOT/libraries/ckeditor`.
  3. Clear the cache

* Manually.

  1. Download the following plugins:

  * [XML](http://ckeditor.com/cke4/addon/xml)
  * [Autocomplete](http://ckeditor.com/cke4/addon/autocomplete)
  * [Textwatcher](http://ckeditor.com/cke4/addon/textwatcher)
  * [Text Match](https://ckeditor.com/cke4/addon/textmatch)
  * [Ajax](http://ckeditor.com/cke4/addon/ajax)

  2. Unzip and place the contents for each plugin in the following
     directory:

  * `DRUPAL_ROOT/libraries/ckeditor/plugins/PLUGIN_NAME`

  3. Clear the cache


INSTALLATION
------------

* Install as you would normally install a contributed Drupal module. Visit
  https://www.drupal.org/docs/8/extending-drupal-8/installing-drupal-8-modules
  for further information.


CONFIGURATION
-------------

- Go to the 'Text formats and editors' configuration page: `/admin/config/content/formats/manage/{format-id}`, and enable desired mentions type, in CKEditor plugin settings.
