# Gigan

Parse a JIRA XML export and dump all bugs and attachments into a CouchDB database.

## Requirements

Needs PHP 5.

## Usage

Download a JIRA XML export. E.g.:

    $ curl  http://issues.apache.org/jira/sr/jira.issueviews:searchrequest-xml/temp/SearchRequest.xml?pid=12310780&sorter/field=issuekey&sorter/order=DESC&tempMax=1000 > couchdb.xml

Customise the top of gigan.php. Then run:

    $ php gigan.php


## Name

Gigan is Godzilla's archenemy. Jira is the Japanese name for Godzilla. Fuck JIRA.

