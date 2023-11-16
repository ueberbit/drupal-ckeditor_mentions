Drupal.ckeditor5DrupalMention = function (options) {
  let plugin = new ckeditor5DrupalMentionPlugin(options);

  return function (queryText) {
    return plugin.getFeedItems(queryText);
  }
}

Drupal.ckeditor5DrupalMentionItemRenderer = function (item) {
  return item.text;
};

class ckeditor5DrupalMentionPlugin {
  constructor(options) {
    this.options = options;
  }

  getFeedItems(queryText) {
    return new Promise(resolve => {
      setTimeout(async () => {
        let itemsToDisplay = fetch(this.options.url.replace('--match--', queryText))
          .then((response) => response.json())
          .then((data) => this.prepareData(data));

        resolve(itemsToDisplay);
      }, 100);
    })
  }

  prepareData(items) {
    items.forEach((item) => {
      item.id = this.options.marker + item.entity_id;
      item.text = this.options.marker + item.label;
      item.link = item.url ?? "";
      item.plugin = this.options.type;
      return item;
    });
    return items;
  }
}
