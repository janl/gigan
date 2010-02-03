function(doc) {
  function is_design(doc) {
    return doc._id.substr(0, "_design".length) == "_design";
  }

  if(!is_design(doc)) {
    return;
  }

  if(!doc.views) {
    return;
  }

  for(var idx in doc.views) {
    if(doc.views.hasOwnProperty(idx)) {
      if(idx != "filters") {
        // don't include myself
        emit(idx);
      }
    }
  }
}
