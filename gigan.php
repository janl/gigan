<?php
// Note: this code is ugly. it scrapes JIRA's XML, it must be ugly.

date_default_timezone_set("Europe/Berlin");

// customise here

$xml_file_name = "couchdb.xml";
$db = "gigan";

$couchdb_host = "127.0.0.1";
$couchdb_port = 5984;

$jira_attachment_url = "http://issues.apache.org/jira/secure/attachment/";

// stop customising
$couch = new CouchSimple(array("host" => $couchdb_host, "port" => $couchdb_port));

$ids = array();

if(!isset($_ENV["GIGAN_BOOT"])) {
  echo "Updating\n";
  // ask gigan for the last updated bug's updated timestamp

  echo "debug: getting latest update\n";
  $latest_update_result = $couch->send("GET", "/$db/_design/gigan/_view/latest-update?descending=true&limit=1");
  $latest_update_result = json_decode($latest_update_result);
  $latest_update = $latest_update_result->rows[0]->key;
  echo "debug: latest update: $latest_update\n";

  echo "debug: getting latest comment update\n";
  $latest_update_result = $couch->send("GET", "/$db/_design/gigan/_view/latest-comment-update?descending=true&limit=1");
  $latest_update_result = json_decode($latest_update_result);
  $latest_update = $latest_update_result->rows[0]->key;
  echo "debug: latest comment update: $latest_update\n";


  // read the JIRA RSS feed until it finds a date that is < that timestamp
  echo "getting the rss feed ...";
  $rss = file_get_contents("http://issues.apache.org/jira/sr/jira.issueviews:searchrequest-rss/temp/SearchRequest.xml?pid=12310780&sorter/field=issuekey&sorter/order=DESC&tempMax=50");
  echo "done\n";

  $xml = simplexml_load_string($rss, $class_name = "SimpleXMLElement", LIBXML_ERR_NONE);
  foreach($xml->channel->item AS $bug) {
    $date = strtotime((string)$bug->pubDate) . "000";
    echo "comparing $date with $latest_update\n";
    if($date <= $latest_update) {
      break;
    }
    $link = (string)$bug->link;
    $_split = explode("/", $link);
    $ids[] = $_split[count($_split)-1];
  }

  echo "getting the comments rss feed ...";
  $rss = file_get_contents("http://issues.apache.org/jira/sr/jira.issueviews:searchrequest-comments-rss/temp/SearchRequest.xml?pid=12310780&sorter/field=issuekey&sorter/order=DESC&tempMax=50");
  echo "done\n";

  $xml = simplexml_load_string($rss, $class_name = "SimpleXMLElement", LIBXML_ERR_NONE);
  foreach($xml->channel->item AS $comment) {
    $date = strtotime((string)$comment->pubDate) . "000";
    echo "comparing $date with $latest_update\n";
    if($date <= $latest_comment_update) {
      break;
    }
    $link = (string)$comment->link;
    if(preg_match("/(COUCHDB-\d+)/", $link, $matches)) {
      $ids[] = $matches[1];
    }
  }

  $ids = array_unique($ids);
  if(count($ids) == 0) {
    echo "nothing to fetch. Existing.\n";
    exit(0);
  }
} else {
  // bootstrap
  echo "Bootstrapping\n";
  $all_bugs = strip_tags(file_get_contents("http://issues.apache.org/jira/secure/IssueNavigator.jspa?reset=true&mode=hide&pid=12310780"));
  if(preg_match("/Displaying issues 1 to 50 of ([0-9]+) matching issues/", $all_bugs, $matches)) {
    $max_id = $matches[1];
    $idnrs = range(1, $max_id);
    foreach($idnrs AS $idnr) {
      $ids[] = "COUCHDB-$idnr";
    }
    echo "Getting all ids from 1 to $max_id\n";
  } else {
    echo"Couldn't find MAX_BUG_ID\n";
    exit(2);
  }
}


foreach($ids AS $id) {

  echo "fetching $id...";

  $xml = simplexml_load_file("http://issues.apache.org/jira/si/jira.issueviews:issue-xml/$id/$id.xml", $class_name = "SimpleXMLElement", LIBXML_ERR_NONE);
  echo "done\n";
  echo "parsing...";
  foreach($xml->channel->item AS $bug) {
    $json = new stdClass();
    $json->_id = (string)$bug->key;

    echo $json->_id . "...";

    $json->title = (string)$bug->title;
    $json->link = (string)$bug->link;
    $json->description = (string)$bug->description;
    $json->jira_key = (string)$bug->key["id"];
    $json->summary = (string)$bug->summary;
    $json->type = (string)$bug->type;
    $json->priority = (string)$bug->priority;
    $json->status = (string)$bug->status;
    $json->resolution = (string)$bug->resolution;
    $json->assignee->name = (string)$bug->assignee;
    $json->assignee->id = (string)$bug->assignee["id"];
    $json->reporter = (string)$bug->reporter;
    $json->created = (string)$bug->created;
    $json->updated = (string)$bug->updated;
    $json->version = (string)$bug->version;
    $json->comments = array();
    if($bug->comments->comment) {
      foreach($bug->comments->comment AS $comment) {
        $json_comment = new stdClass();
        $json_comment->id = (string)$comment["id"];
        $json_comment->author = (string)$comment["author"];
        $json_comment->created = (string)$comment["created"];
        $json_comment->comment = (string)$comment;
        $json->comments[] = $json_comment;
      }
    }

    if($bug->attachments->attachment) {
      foreach($bug->attachments->attachment AS $attachment) {
        $json_attachment = new stdClass();
        $json_attachment->id = (string)$attachment["id"];
        $json_attachment->name = (string)$attachment["name"];
        $json_attachment->size = (string)$attachment["size"];
        $json_attachment->created = (string)$attachment["created"];
        $json_attachment->content_type = "text/plain";
        $json_attachment->data = base64_encode(file_get_contents("{$jira_attachment_url}/{$json_attachment->id}/{$json_attachment->name}"));
        $json->_attachments->{$json_attachment->name} = $json_attachment;
      }
    }

    // echo json_encode($json);
    echo "done\n";
    // get rev
    $res = $couch->send("GET", "/$db/$json->_id");
    if($res) {
      $doc = json_decode($res);
      if($doc->_rev) {
        $json->_rev = $doc->_rev;
      }
      if($doc->couchdb_fields) {
        $json->couchdb_fields = $doc->couchdb_fields;
      }
    }

    // file_put_contents($json->_id . ".json", json_encode($json));
    $couch->send("PUT", "/$db/$json->_id", json_encode($json));
    echo "done\n";
    // exit();
  }
}

// classes

class CouchSimple {
  function CouchSimple($options) {
     foreach($options AS $key => $value) {
        $this->$key = $value;
     }
  } 

 function send($method, $url, $post_data = NULL) {
    $s = fsockopen($this->host, $this->port, $errno, $errstr); 
    if(!$s) {
       echo "$errno: $errstr\n"; 
       return false;
    } 

    $request = "$method $url HTTP/1.0\r\nHost: localhost\r\n"; 

    if($post_data) {
       $request .= "Content-Length: ".strlen($post_data)."\r\n\r\n"; 
       $request .= "$post_data\r\n";
    } 
    else {
       $request .= "\r\n";
    }

    fwrite($s, $request); 
    $response = ""; 

    while(!feof($s)) {
       $response .= fgets($s);
    }

    list($this->headers, $this->body) = explode("\r\n\r\n", $response); 
    return $this->body;
 }
}
?>