function(doc) {
  if(!doc.status
    || (doc.status != "Closed" && doc.status != "Invalid")
    && doc._attachments
    && doc.title
    && doc.jira_key) {
      emit(doc.jira_key, {title: doc.title, jira_key: doc.jira_key});
  }
}
