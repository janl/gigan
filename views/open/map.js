function(doc) {
  if(doc.jira_key && doc.title && doc.status && doc.status == "Open") {
    emit(doc.jira_key, doc.title);
  }
}
