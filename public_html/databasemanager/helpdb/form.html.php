<?php 
include_once 'includes/helpers.inc.php'; 
if (is_readable('../../config.php')) {  // load global config file
    require_once('../../config.php');
} else {
    die("Cannot read config.php file. If this is a new installation, locate the config.php.new file, enter the required information, and rename if config.php.");
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title><?php htmlout($pageTitle); ?> | <?php echo $site_title; ?></title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cloud.tinymce.com/stable/tinymce.min.js?apiKey=<?php echo $TinyMCE_APIKey; ?>"></script>    
    <script>tinymce.init({ 
            selector:'textarea' ,
            min_height: 300 ,
            menubar: false ,
            plugins: 'lists link anchor image spellchecker charmap paste table',
            toolbar1: 'cut copy paste | bold italic underline | subscript superscript | formatselect removeformat | undo redo ' ,
            toolbar2: 'bullist numlist | outdent indent | link unlink anchor image | spellchecker | charmap | pastetext | table' ,
            statusbar: false
            });
    </script>
  </head>
  <body class="entry_form">
      <div class="wrapper">
      
        <h1><?php htmlout($pageTitle); ?></h1>
      
        <form action="?<?php htmlout($action); ?>" method="post" class="group">
          <fieldset>
            <label for="entry_title"><span>Entry title </span>
            <input id="entry_title" name="entry_title" value="<?php htmlout($entry_title); ?>"></label>
        
            <?php if ($code_id == '') {
				echo '<label for="code_id"><span>Short identifier <br />no spaces, please </span>
            <input id="code_id" name="code_id" value="';
				htmlout($code_id);
				echo '" ></label>';
			} else { 
				// if the entry already exists, don't allow the code_id to be edited
				echo ' <input type="hidden" name="code_id" value="' . $code_id . '">';
			} ?>
            
            <!-- include in Scorecard glossary / or not-->
            <?php
            if ($sc_gloss == 1) {
                $sc_checked = 'checked="checked"';
            } else {
                $sc_checked = '';
            }
            ?>
            <label for="sc_gloss"><span>Include in Scorecard Glossary </span>
            <input type="checkbox" id="sc_gloss" name="sc_gloss" value="1" <?php echo $sc_checked; ?>></label>
            
            <!-- include in Calculator glossary / or not-->
            <?php
            if ($calc_gloss == 1) {
                $calc_checked = 'checked="checked"';
            } else {
                $calc_checked = '';
            }
            ?>
            <label for="calc_gloss"><span>Include in Calculator Glossary </span>
            <input type="checkbox" id="calc_gloss" name="calc_gloss" value="1" <?php echo $calc_checked; ?>></label>
        
            <label for="entry_text"><span>Entry text </span>
            <textarea id="entry_text" name="entry_text" rows="10" cols="40" ><?php htmlout($entry_text); ?></textarea></label>
          </fieldset>
          
          <input type="hidden" name="id" value="<?php htmlout($id); ?>">
          <input type="submit" class="submit" value="<?php htmlout($button); ?>">
        </form>
        <p><a href=".">Return to Search and Manage Entries</a></p>
    </div>
  </body>
</html>
