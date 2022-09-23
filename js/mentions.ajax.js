Drupal.ckeditor5DrupalMention = function (options) {
  let plugin = new ckeditor5DrupalMentionPlugin(options);

  return function (queryText) {
    return plugin.getFeedItems(queryText);
  }
}

Drupal.ckeditor5DrupalMentionUser = {
  itemRenderer(item) {
    return item.text;


    /*
    const itemElement = document.createElement('span');

    itemElement.classList.add('custom-item');
    itemElement.id = `@${item.id}`;
    itemElement.textContent = `${item.label} `;

    return itemElement;

    /*
    const usernameElement = document.createElement('span');

    usernameElement.classList.add('custom-item-username');
    usernameElement.textContent = item.id;

    itemElement.appendChild(usernameElement);

    return itemElement;
    */
  }
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
      item.id = this.options.marker + item.uuid;
      item.text = this.options.marker + item.label;
      return item;
    });
    return items;
  }
}
