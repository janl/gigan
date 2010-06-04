function(doc) {
  if(doc.status && doc.status != "Closed") {
    emit(doc.reporter, {title: doc.reporter + ": " + doc.title, jira_key: doc.jira_key});
  }
}
