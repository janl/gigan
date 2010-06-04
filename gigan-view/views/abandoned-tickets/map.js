function(doc) {
  if((!doc.comments || doc.comments.length == 0) && doc.status == "Open") {
    emit(doc.jira_key, {title: doc.title, jira_key: doc.jira_key});
  }
}
