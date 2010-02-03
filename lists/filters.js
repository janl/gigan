function(head, req) {
  //!code vendor/mustache.js/mustache.js
  //!json templates

  provides("html", function() {
    send(Mustache.to_html(templates.head, {}));
    send(Mustache.to_html(templates.index.head, {}));
    
    var filter;
    while(filter = getRow()) {
      send(Mustache.to_html(templates.index.row, {
        title: filter.key,
        link: filter.key
      }));
    }
    send(Mustache.to_html(templates.index.tail, {}));
    send(Mustache.to_html(templates.foot, {}));
  });
}
