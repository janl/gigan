function(doc) {
  function munge(timestamp) {
    var date = new Date(timestamp);
    return date.getTime();
  }
  if(doc.updated && doc.title && doc.jira_key) {
    emit(munge(doc.updated), {title: doc.title, jira_key: doc.jira_key});
  }
}