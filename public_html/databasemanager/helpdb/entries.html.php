<?php include_once 'includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>List of Entries | <?php echo $site_title; ?></title>
    
	<link rel="stylesheet" href="style.css">
  </head>
  <body class="entry_list">
	<div class="wrapper">
    	
        <h1>List of Entries</h1>
        
        <h3><a href="?add">Add New Help Entry &raquo;</a></h3>

        <p><a href=".">Return to Search and Manage Entries</a></p>
        
        <p>Below are the entries resulting from your search. If you searched with no criteria, or if you decided against deleting an entry, this should be all the entries in the database.</p>
        
        <?php foreach ($entries as $entry): ?>
          <form action="" method="post">
              <div id="<?php htmlout($entry['code_id']); ?>" class="entry group">
                <h3>
                    <?php htmlout($entry['entry_title']); ?>
                </h3>
                <p class="code_id">
                	<?php htmlout($entry['code_id']); 
					 
					if ($entry['sc_gloss']) {
                        echo '<br />Include in Scorecard Glossary';
                    }
					if ($entry['calc_gloss']) {
                        echo '<br />Include in Calculator Glossary';
                    }?>
                </p>

                <p>
                    <?php echo $entry['entry_text']; ?>
                    <input type="hidden" name="id" value="<?php echo $entry['id']; ?>">
                    <div class="group">
	                    <input type="submit" name="action" value="Edit" class="submit">
    	                <input type="submit" name="action" value="Delete" class="submit">
                    </div>
                </p>
              </div>
          </form>
        <?php endforeach; ?>

    </div>
  </body>
</html>
