<?php
	define('RSS_URL', 'http://php3.loc/news/rss.xml');
	define('FILE_NAME', 'news.xml');
	
	function download(){
		$download = false;
		if (!file_exists(FILE_NAME)) {
			$download = true;
		}
		else {
			$file_time = filemtime(FILE_NAME);
			if ((time() - $file_time) >= 120000){
				$download = true;
			}
		}
		
		if ($download) {
			$rss = file_get_contents(RSS_URL);
			file_put_contents(FILE_NAME, $rss);
		}	
	}
	
	download();
?>
<!DOCTYPE html>

<html>
<head>
	<title>Новостная лента</title>
	<meta charset="utf-8" />
	<style>
		table {width: 100%; border-collapse: collapse;}
		table th, table td {border: 1px solid #bebebe; padding: 3px;}
	</style>
</head>
<body>

<h1>Последние новости</h1>
<table>
	<tr>
		<th>Заголовок</th>
		<th>Ссылка</th>
		<th>Описание</th>
		<th>Текст</th>
		<th>Дата публикации</th>
		<th>Категория</th>
	</tr>
<?php
	$sxml = simplexml_load_file(FILE_NAME);
	foreach ($sxml->item as $item) {
		?>
		<tr>
			<td><?=$item->title?></td>
			<td><a href="<?=$item->link?>" target="_blank"><?=$item->link?></td>
			<td><?=$item->description?></td>
			<td><?=$item->text?></td>
			<td><?=$item->pubDate?></td>
			<td><?=$item->category?></td>
		</tr>
		<?
	}
?>
</table>
</body>
</html>