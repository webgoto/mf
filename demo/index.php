<?php
require '../MF.php';
$mf = new MF();

$mf->addRoute('/', array('top', 'Demo Site'));
$mf->addRoute('/about/', array('about', 'About - Demo Site'));
$mf->addRoute('/{name:other1|other2}/', array('other', 'Other - Demo Site'));
$mf->dispatch();

?>
<!doctype html>
<html lang="ja">
<head>
<title><?php echo $mf->title; ?></title>
<meta charset="UTF-8">
<link rel="stylesheet" href="<?php echo $mf->asset_url; ?>/style.css">
</head>
<body>
<header>
	<h1>Demo Site</h1>
	<ul>
		<li><a href="<?php echo $mf->site_url; ?>/">Top</a></li>
		<li><a href="<?php echo $mf->site_url; ?>/about/">About</a></li>
		<li><a href="<?php echo $mf->site_url; ?>/other1/">Other1</a></li>
		<li><a href="<?php echo $mf->site_url; ?>/other2/">Other2</a></li>
	</ul>
</header>
<main>
<?php $path = $mf->src_path.'/'.$mf->slug.'.php'; if(file_exists($path)) include $path; ?>
</main>
<footer>
	&copy; 2016 Demo Site
</footer>
</body>
</html>

