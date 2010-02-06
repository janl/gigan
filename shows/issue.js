function(doc, req) {
  //!json templates
  //!code vendor/mustache.js/mustache.js

  provides("html", function() {
    var html = "";
    html += templates.head;

    doc.has_comments = false;
    doc.has_attachments = false;
    doc.attachments = "";
    for(var name in doc._attachments) {
      var view = doc._attachments[name];
      view.name = name;
      view._id = doc._id;
      doc.attachments += Mustache.to_html(templates.attachment, view);
      doc.has_attachments = true
    }

    doc.show_comments = "";
    doc.comments.forEach(function(comment) {
      doc.has_comments = true;
      doc.show_comments += Mustache.to_html(templates.comment, comment);
    });

    html += Mustache.to_html(templates.issue, doc);
    html += templates.foot;
    return {
      body: html
    };
  });
}
