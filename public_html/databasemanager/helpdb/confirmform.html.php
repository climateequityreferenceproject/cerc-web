<?php include_once 'includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Confirm Deletion | <?php echo $site_title; ?></title>
	<link rel="stylesheet" href="style.css">
  </head>
  <body class="confirm_form">
	<div class="wrapper">
    	
        <h1>Confirm Deletion</h1>
                
          <form action="" method="post">
            <p>Are you sure you want to delete <strong><?php echo $entry_title; ?></strong>?</p>
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <div class="group">
                <input type="submit" name="action" value="Yes, please delete" class="submit">
                <input type="submit" name="action" value="No, keep it" class="submit">
            </div>
          </form>
    </div>
  </body>
</html>
