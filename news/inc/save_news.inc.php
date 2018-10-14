<?php
$title = $news->clearStr($_POST['title']);
$category = $news->clearInt($_POST['category']);
$description = $news->clearStr($_POST['description']);
$text = $news->clearStr($_POST['text']);
$source = $news->clearStr($_POST['source']);

if (!$title || !$category || !$description || !$source|| !$text){
    $errorMsg = "Заполните все поля формы!";
}
else {
    $result = $news->saveNews($title, $category, $description, $text, $source);
	if (!$result) {
		$errorMsg = 'Произошла ошибка при добавлении новости';
	}
	else {
		header('Location: news.php');
		exit();
	}
}
