<?php
$id = $news->clearInt($_GET['id']);
if ($id) {
	$result = $news->deleteNews($id);
	if (!$result) {
		$errorMsg = "Произошла ошибка при удалении новости";		
	}
	else {
		header('Location: news.php');
		exit();
	}
}