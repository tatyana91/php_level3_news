<?php
$news_items = $news->getNews();
if ($news_items === false) {
	$errorMsg = "Произошла ошибка при выводе новостной ленты";
}
else if (!count($news_items)) {
	echo "<div>Новостей нет</div>";
}
else {
	foreach ($news_items as $news_item) {
		echo "
		<div class='news__item'>
			<div class='news__date'>".date('d.m.Y H:i', $news_item['datetime'])."</div>
			<div class='news__category'>Категория: ".$news_item['category']."</div>
			<div class='news__title'>".$news_item['title']."</div>			
			<div class='news__description'>".$news_item['description']."</div>
			<div class='news__source'>Источник: <a href='".$news_item['source']."' target=_blank>".$news_item['source']."</a></div>
			<div class='news__options'>
				<a class='btn news__btn' href='news.php?act=show&id=".$news_item['id']."'>Подробнее</a>
				<a class='btn news__btn' href='news.php?act=delete&id=".$news_item['id']."'
					onclick='return confirm(\"Вы уверены, что хотите удалить эту новость?\")'>Удалить новость</a>
			</div>
		</div>";
	}
}