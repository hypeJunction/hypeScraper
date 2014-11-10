<?php
$body = elgg_view('page/elements/body', $vars);

// Set the content type
header("Content-type: text/html; charset=UTF-8");

$lang = get_current_language();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $lang; ?>" lang="<?php echo $lang; ?>">
	<head>
		<?php echo elgg_view('page/elements/head', $vars); ?>
	</head>
	<body>
		<div class="elgg-page elgg-page-iframe">
			<?php echo $body; ?>
		</div>
		<?php echo elgg_view('page/elements/foot'); ?>
	</body>
</html>