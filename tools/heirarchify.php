<?php
/**
 * Convert the flat divisions JSON data into structured data.
 *
 * Reads from a file (first argument) and writes to standard out.
 *
 * Example use:
 *
 *    php heirarchify.php divisions.json > heirarchy.json
 */

/**
 * A sample, complete record for a unit might look like:
 *
 * {
 *   "department": "Community Services",
 *   "division": "Branch reports direct to CS DM",
 *   "branch": "Finance, Systems and Administration",
 *   "unit": "Finance and Space Planning",
 *   "order": 30,
 *   "mailcode": "C3",
 *   "newmailcode": null
 * }
 *
 * The desired output will look something like:
 * [
 *     {
 *       "type": "department",
 *       "name": "Community Services",
 *       "children" : [
 *         {
 *         "type": "division",
 *         "name": "Branch reports direct to CS DM",
 *         "children":
 *           [
 *             {
 *               "name": "Communications",
 *               "type": "branch",
 *               "children": [
 *                 {
 *                   "name" : "foobar"
 *                   "type":  "unit",
 *                 },
 *                 {...}
 *               ],
 *             },
 *             {...}
 *           ]
 *         },
 *         {...}
 *       ],
 *       "order": 100,
 *       "mailcode": "CM1",
 *     },
 *     {...}
 *   ]
 *
 */

$json = new JsonDivistionsFromFile($argv[1]);
$records = $json->records();

$converter = new OrganizationConverter($records);

// Output the new structure.
echo(json_encode($converter->newStructure()));

/**
 * Convert from the flat structure to one with heirarchy.
 */
class OrganizationConverter
{

  private $structured;

  function __construct($records)
  {
    // The new data structure.
    $this->structured = new Organization('government', 'Yukon', 0, NULL);

    // Loop through all of the records.
    // Department must be set, all other levels are optional.
    foreach ($records as $row) {

      // Deal with departments.
      if ($row->department) {
        if (!$this->structured->hasChild($row->department)) {
          $this->structured->newChildFromRecord($row);
        }
        $department = $this->structured->getChild($row->department);

        // Deal with divisions.
        if (isset($row->division)) {
          if (!$department->hasChild($row->division)) {
            $department->newChildFromRecord($row);
          }

          // Deal with branches.
          if (isset($row->branch)) {
            $division = $department->getChild($row->division);
            if (!$division->hasChild($row->branch)) {
              $division->newChildFromRecord($row);
            }

            // Deal with units.
            if (isset($row->unit)) {
              $branch = $division->getChild($row->branch);
              if (!$branch->hasChild($row->unit)) {
                $branch->newChildFromRecord($row);
              }
            }
          }
        }
      }
    }
  }

  public function newStructure()
  {
    // TODO this should be sorted.
    return $this->structured;
  }
}

/**
 * JsonDivistionsFromFile
 */
class JsonDivistionsFromFile
{

  function __construct($filename)
  {
    // Get file data as structure.
    if (empty($filename)) {
      echo "first argument must be a JSON file";
      exit(1);
    }
    if (!is_readable($filename)) {
      echo "cannot read the file " . $filename;
      exit(1);
    }
    $file_content = file_get_contents($filename);
    if ($file_content === FALSE) {
      echo "Unable to get content of " . $filename;
      exit(1);
    }
    $this->json = json_decode($file_content);
    if ($this->json === FALSE) {
      echo "Unable to parse JSON";
      exit(1);
    }
  }

  public function records()
  {
    return $this->json->divisions;
  }
}

/**
 * Recursive structure to represent an orgazational unit and it's children.
 */
class Organization
{
  private $childType;

  function __construct($type, $name, $order=NULL, $mailcode=NULL)
  {
      $this->type = $type;
      // Some names have whitespace.
      $this->name = $name;
      $this->order = $order;
      $this->mailcode = $mailcode;
      $this->childType = $this->childType();
  }

  /**
   * Create a new child based on a divisions.json record.
   */
  public function newChildFromRecord($record)
  {
    if ($this->childType) {
      $name = $record->{$this->childType};
      $this->children[] = new Organization($this->childType(), $name, $record->order, $record->mailcode);
    }
  }

  private function childType(){
    $children_type = array(
      'government' => 'department',
      'department' => 'division',
      'division'   => 'branch',
      'branch'     => 'unit',
    );
    if (isset($children_type[$this->type])) {
      return $children_type[$this->type];
    }
  }

  function hasChild($name)
  {
      if (!isset($this->children)) {
        return FALSE;
      }
      foreach ($this->children as $child) {
        if ($child->name == $name) {
          return TRUE;
        }
      }
      return FALSE;
  }

  public function getChild($name)
  {
      if (!isset($this->children)) {
        return NULL;
      }
      foreach ($this->children as $child) {
        if ($child->name == $name) {
          return $child;
        }
      }
      // Should this be an exception?
      return NULL;
  }

  public function addChild($type, $name)
  {
    if (!isset($this->children)) {
      $this->children = array();
    }
    $this->children[] = new Organization($type, $name);
  }
}
