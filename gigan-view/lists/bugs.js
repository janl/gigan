function(head, req) {
  //!code vendor/mustache.js/mustache.js
  //!json templates

  provides("html", function() {
    send(Mustache.to_html(templates.head, {}));
    send(Mustache.to_html(templates.bugs.head, {}));

    var bug;
    while(bug = getRow()) {
      if(bug.id.substr(0, "_design".length) == "_design") {
        continue;
      }
      send(Mustache.to_html(templates.bugs.row, {
        title: bug.value.title,
        id: bug.id
      }));
    }
    send(Mustache.to_html(templates.bugs.tail, {}));
    send(Mustache.to_html(templates.foot, {}));
  });
}
