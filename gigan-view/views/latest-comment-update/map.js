function(doc) {
  function munge(timestamp) {
    var date = new Date(timestamp);
    return date.getTime();
  }
  if(doc.comments) {
    doc.comments.forEach(function(comment) {
      emit(munge(comment.created), {title: doc.title, jira_key: doc.jira_key});
    });
  }
}