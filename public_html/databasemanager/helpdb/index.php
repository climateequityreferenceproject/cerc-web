<?php
if (isset($_GET['debug']) && $_GET['debug'] == 'yes') {
    ini_set('display_errors',1); 
    error_reporting(E_ALL);
}
// stripslashes("$entry_text"); used for ADD AN ENTRY and UPDATE AN ENTRY (correctly??) 
// Is this needed?? TinyMCE says needed for code. Doesn't seem to be hurting anything, 
// but I leave this note in case a problem arises. -TKB

include_once 'includes/magicquotes.inc.php';

// saving this line for reference in scorecard and calculator, both in subdirectories of gdrights.org
// include_once $_SERVER['DOCUMENT_ROOT'] . '/helpdb/includes/db_gdrs_help.inc.php';

$site_title = "GDRs Help Documentation for Scorecard and Calculator";

// Collect data for a new entry
// Arrive here via Add link from entries.html.php or searchform.html.php
if (isset($_GET['add']))
{
  $pageTitle   = 'New Entry';
  $action      = 'addform';
  $entry_title = '';
  $code_id     = '';
  $entry_text  = '';
  $id          = '';
  $sc_gloss    = '1';
  $calc_gloss  = '1';
  $button      = 'Add entry';

  include 'includes/db_gdrs_help.inc.php';

  include 'form.html.php';
  exit();
}


// ADD AN ENTRY to the db, using data entered via form.html.php
if (isset($_GET['addform']))
//if (isset($_POST['entry_text']))
{
  include 'includes/db_gdrs_help.inc.php';
  try
  {
    $sql = 'INSERT INTO entry SET
        code_id     = :code_id,
        entry_title = :entry_title,
        entry_text  = :entry_text,
        sc_gloss    = :sc_gloss,
        calc_gloss  = :calc_gloss,
        entry_date  = CURDATE()';
    $s = $pdo->prepare($sql);
    $s->bindValue(':code_id', $_POST['code_id']);
    $s->bindValue(':entry_title', $_POST['entry_title']);
    $s->bindValue(':entry_text', stripslashes($_POST['entry_text']));
    $s->bindValue(':sc_gloss', isset($_POST['sc_gloss']) ? 1 : 0);
    $s->bindValue(':calc_gloss', isset($_POST['calc_gloss']) ? 1 : 0);
    $s->execute();
  }
  catch (PDOException $e)
  {
    $error = 'Error adding submitted entry: ' . $e->getMessage();
    include 'error.html.php';
    exit();
  }

  header('Location: .');
  exit();
}


// Prepare to edit an entry
// Arrive here via a particular entry's Edit button in entries.html.php
if (isset($_POST['action']) and $_POST['action'] == 'Edit')
{
  include 'includes/db_gdrs_help.inc.php';
  try
  {
    $sql = 'SELECT id, code_id, entry_title, entry_text, sc_gloss, calc_gloss FROM entry WHERE id = :id';
    $s = $pdo->prepare($sql);
    $s->bindValue(':id', $_POST['id']);
    $s->execute();
  }
  catch (PDOException $e)
  {
    $error = 'Error fetching entry details.';
    include 'error.html.php';
    exit();
  }
  $row = $s->fetch(PDO::FETCH_ASSOC);

  $pageTitle   = 'Edit Entry';
  $action      = 'editform';
  $entry_title = $row['entry_title'];
  $code_id     = $row['code_id'];
  $entry_text  = $row['entry_text'];
  $id          = $row['id'];
  $sc_gloss    = $row['sc_gloss'];
  $calc_gloss  = $row['calc_gloss'];
  $button      = 'Update entry';

  include 'form.html.php';
  exit();
}

// UPDATE AN ENTRY in the db, using data entered via form.html.php
if (isset($_GET['editform']))
{
  include 'includes/db_gdrs_help.inc.php';
  try
  {
    $sql = 'UPDATE entry SET
        code_id     = :code_id,
        entry_title = :entry_title,
        entry_text  = :entry_text,
        sc_gloss    = :sc_gloss,
        calc_gloss  = :calc_gloss,
        mod_date    = CURDATE()
        WHERE id    = :id';
    $s = $pdo->prepare($sql);
    $s->bindValue(':id', $_POST['id']);
    $s->bindValue(':code_id', $_POST['code_id']);
    $s->bindValue(':entry_title', $_POST['entry_title']);
    $s->bindValue(':entry_text', stripslashes($_POST['entry_text']));
    $s->bindValue(':sc_gloss', isset($_POST['sc_gloss']) ? 1 : 0);
    $s->bindValue(':calc_gloss', isset($_POST['calc_gloss']) ? 1 : 0);
    $s->execute();
  }
  catch (PDOException $e)
  {
    $error = 'Error updating submitted entry.';
    include 'error.html.php';
    exit();
  }

  header('Location: .');
  exit();
}


// Ask user to confirm they want to delete an entry whose Delete button was just clicked via entries.html.php
if (isset($_POST['action']) and $_POST['action'] == 'Delete')
{
  include 'includes/db_gdrs_help.inc.php';
  try
  {
    $sql = 'SELECT id, entry_title FROM entry WHERE id = :id';
    $s = $pdo->prepare($sql);
    $s->bindValue(':id', $_POST['id']);
    $s->execute();
  }
  catch (PDOException $e)
  {
    $error = 'Error fetching entry details.';
    include 'error.html.php';
    exit();
  }
  $row = $s->fetch(PDO::FETCH_ASSOC);

  $entry_title = $row['entry_title'];
  $id          = $row['id'];
	
  include 'confirmform.html.php';
  exit();
}


// DELETE an entry from the db after 'Yes, please delete' button was clicked via confirmform.html.php
// TODO add confirmation screen before this step
if (isset($_POST['action']) and $_POST['action'] == 'Yes, please delete')
{
  include 'includes/db_gdrs_help.inc.php';
  try
  {
    $sql = 'DELETE FROM entry WHERE id = :id';
    $s = $pdo->prepare($sql);
    $s->bindValue(':id', $_POST['id']);
    $s->execute();
  }
  catch (PDOException $e)
  {
    $error = 'Error deleting entry: ' . $e->getMessage();
    include 'error.html.php';
    exit();
  }

  header('Location: .');
  exit();
}


// List entries found from searchform.html.php, or return after deciding not to delete
// but it seems to be working OK
// TODO also search entry_title and maybe code_id, not just entry_text

if ((isset($_GET['action']) and $_GET['action'] == 'search') or (isset($_POST['action']) and $_POST['action'] == 'No, keep it'))
{
  include 'includes/db_gdrs_help.inc.php';

  // The basic SELECT statement
  $select = 'SELECT id, code_id, entry_title, entry_text, sc_gloss, calc_gloss';
  $from   = ' FROM entry';
  $where  = ' WHERE TRUE';

  $placeholders1 = array();
  $placeholders2 = array();

  if ($_GET['text'] != '') // Some search text was specified
  {
    $have_search_text = true;
    $where .= " AND";
    
    $where1 = " (code_id LIKE :code_id";
    $placeholders1[':code_id'] = '%' . $_GET['text'] . '%';
    $placeholders2[':code_id'] = $placeholders1[':code_id'];

    $where1 .= " OR entry_title LIKE :entry_title)";
    $placeholders1[':entry_title'] = '%' . $_GET['text'] . '%';
    $placeholders2[':entry_title'] = $placeholders1[':entry_title'];
    
    $where2 = " entry_text LIKE :entry_text";
    $placeholders2[':entry_text'] = '%' . $_GET['text'] . '%';
    
    $where2 .= " AND NOT" . $where1;

  }
  
  $order_by = ' ORDER BY entry_title';
  
  // Find in title or code first, and alphabetize by title

  try
  {
    $sql = $select . $from . $where . $where1 . $order_by;
    $s = $pdo->prepare($sql);
    $s->execute($placeholders1);
  }
  catch (PDOException $e)
  {
    $error = 'Error fetching entries.';
    include 'error.html.php';
    exit();
  }

  //// ... store results of query in an array ...
  foreach ($s as $row)
  {
    $entries[] = array(
  	  'id'          => $row['id'], 
	  'code_id'     => $row['code_id'], 
	  'sc_gloss'    => $row['sc_gloss'], 
	  'calc_gloss'  => $row['calc_gloss'], 
	  'entry_title' => $row['entry_title'], 
	  'entry_text'  => $row['entry_text']);
  }
  
  // Next, find it in text, and add (alphabetized separately)
  if ($have_search_text) {
    try
    {
        $sql = $select . $from . $where . $where2 . $order_by;
        $s = $pdo->prepare($sql);
        $s->execute($placeholders2);
    }
    catch (PDOException $e)
    {
        $error = 'Error fetching entries.';
        include 'error.html.php';
        exit();
    }

    //// ... store results of query in an array ...
    foreach ($s as $row)
    {
        $entries[] = array(
            'id'          => $row['id'], 
            'code_id'     => $row['code_id'], 
            'sc_gloss'    => $row['sc_gloss'], 
            'calc_gloss'  => $row['calc_gloss'], 
            'entry_title' => $row['entry_title'], 
            'entry_text'  => $row['entry_text']);
    }
  }

  include 'entries.html.php';
  exit();
}

// Display search form
include 'includes/db_gdrs_help.inc.php';
include 'searchform.html.php';


// Collect all entries from the db via a query, ...
//include 'includes/db_gdrs_help.inc.php';
//try
//{
//  $sql = 'SELECT id, code_id, entry_title, entry_text, sc_gloss, calc_gloss FROM entry';
//  $result = $pdo->query($sql);
//}
//catch (PDOException $e)
//{
//  $error = 'Error fetching entries: ' . $e->getMessage();
//  include 'error.html.php';
//  exit();
//}
//
//// ... store results of query in an array ...
//foreach ($result as $row)
//{
//  $entries[] = array(
//  'id'          => $row['id'], 
//	'code_id'     => $row['code_id'], 
//	'sc_gloss'    => $row['sc_gloss'], 
//	'calc_gloss'  => $row['calc_gloss'], 
//	'entry_title' => $row['entry_title'], 
//	'text'        => $row['entry_text']);
//}
//
//// ... and output the list of entries
//include 'entries.html.php';
