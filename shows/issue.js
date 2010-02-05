function(doc, req) {
  //!json templates
  //!code vendor/mustache.js/mustache.js

  provides("html", function() {
    var html = "";
    html += templates.head;
    html += Mustache.to_html(templates.issue, {
      id: doc._id,
      title: doc.title,
      created: doc.created,
      reporter: doc.reporter,
      description: doc.description,
      status: doc.status
    });
    html += templates.foot;
    return {
      body: html
    };
  });
}
