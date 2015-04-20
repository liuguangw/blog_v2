<?php
if (! defined ( 'APP_PATH' ))
	exit ( 'Access denied!' );
echo '<?xml version="1.0" encoding="UTF-8"?>',"\n";
?><urlset>
<?php echo $tplData->get('urlList'); ?>
</urlset>