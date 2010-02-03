function(head, req) {
  //!code vendor/mustache.js/mustache.js
  //!json templates

  provides("html", function() {
    send(Mustache.to_html(templates.head, {}));
    send(Mustache.to_html(templates.bugs.head, {}));

    var bug;
    while(bug = getRow()) {
      send(Mustache.to_html(templates.bugs.row, {
        title: bug.value,
        id: bug.id
      }));
    }
    send(Mustache.to_html(templates.bugs.tail, {}));
    send(Mustache.to_html(templates.foot, {}));
  });
}