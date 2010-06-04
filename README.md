# Gigan

Easy browsing for JIRA issues.


## Requirements

Needs PHP 5. Yes, PHP. Get over it.


## Usage

First run:

Customise the top of gigan.php. Then run:

    $ GIGAN_BOOT=true php lib/gigan.php

After that, create a cronjob running `php lib/gigan.php` every X minutes (I default
to 15) to fetch the latest changes from JIRA.

If you don't feel like running that cronjob, get in touch, I'm happy to host
your JIRA's importer publicly.


## Browsing Issues

Gigan comes with a little [CouchApp][http://github.com/couchapp/couchapp] that
lets you browse your issues by a few predefined filters as well as allows you
to create your own filters.

1. Install CouchApp. Usually, that is just a `sudo easy_install -U couchapp`
2. `$ cd gigan/gigan-view`
3. `$ couchapp push . gigan`
4. Go to <http://127.0.0.1:5984/gigan/_design/gigan/_list/filters/filters>

### Creating New Filters

Create a new filter by creating a new view.

    $ cd gigan/gigan-view/views
    $ mkdir by-type
    $ mate by-type/map.js
    function(doc) {
      if(doc.type && doc.title && doc.jira_key) {
        emit(doc.type, {
          title: doc.type + ": " + doc.title,
          jira_key: doc.jira_key
        });
      }
    }
    $ couchapp push . gigan

This creates a new filter that sorts all issues by type and displays the type
along with the issue title. The important part is the call to `emit()`. The
first argument is the key to sort your issues by the second is an object that
needs to have a `title` and a `jira_key` attribute. The former is for display
the latter is for linking.

Your new filter should show up on
<http://127.0.0.1:5984/gigan/_design/gigan/_list/filters/filters> automatically.


## Name

Gigan is Godzilla's archenemy. Jira is the Japanese name for Godzilla. 
Fuck JIRA.

“Gigan was the first monster to cause Godzilla to visibly bleed.” 
  — http://en.wikipedia.org/wiki/Gigan
