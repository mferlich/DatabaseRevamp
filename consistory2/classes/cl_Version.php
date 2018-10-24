<?php

class Version
{
        var $id = 0;
        var $vid = 0;
        var $lastname = "";
        var $firstname = "";
        var $nickname = "";
        var $gender = "";
        var $occupation = "";
        var $origin = "";
        var $residence = "";
        var $birthdate = "";
        var $deathdate = "";
        var $spouse = "";
        var $parents = "";
        var $children = "";
        var $relations = "";
        var $annotation = "";
        var $exists = false;
        var $modified_by = "";
        var $modified_date = NULL;

function Version(&$db, $vid=0)
{
  if ($vid) {
     $q = "SELECT * FROM " . VERSION_TABLE . " WHERE versionId=$vid";
     $result = $db->query($q);
     if ($version = $db->fetch_assoc($result)){
        $this->vid = $vid;
        $this->id = $version['recordId'];
        $this->lastname = $version['lastname'];
        $this->firstname = $version['firstname'];
        $this->nickname = $version['nickname'];
        $this->gender = $version['gender'];
        $this->occupation = $version['occupation'];
        $this->origin = $version['origin'];
        $this->residence = $version['residence'];
        $this->birthdate = $version['birthdate'];
        $this->deathdate = $version['deathdate'];
        $this->spouse = $version['spouse'];
        $this->parents = $version['parents'];
        $this->children = $version['children'];
        $this->relations = $version['relations'];
        $this->annotation = $version['annotation'];
        $this->exists = true;
        $this->modified_date = $version['modified_date'];


        $modifier_query = "select user_id, lastname, firstname from users ORDER BY user_id";
        if ($modifier_result = $db->query($modifier_query)) {
           $count = 0;
           $modifiers = array();
           while ($modifier = $db->fetch_assoc($modifier_result)) {
               $count++;
               $modifiers[$count] = $modifier['firstname'] . " " . $modifier['lastname'];
           }
        }
        $this->modified_by = (!empty($modifiers[$version['modified_by']])) ? $modifiers[$version['modified_by']] : "Not Recorded";
      }
   }
 }
} // end constructor 'Version'


