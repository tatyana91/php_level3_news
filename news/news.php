<?php
function __autoload($class){
    include("classes/$class.class.php");
}

$news = new NewsDB();
$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	include('inc/save_news.inc.php');
}

if (isset($_GET['act']) && $_GET['act'] == 'delete') {
	include('inc/delete_news.inc.php');
}

if (isset($_GET['act']) && $_GET['act'] == 'rss'){
    $news->createRss();
    header('Location: rss.xml');
    exit();
}
?>
<!DOCTYPE html>
<html lang="ru">
	<head>
		<title>Новостная лента</title>
		<meta charset="utf-8" />
		<link rel="stylesheet" href="css/style.css">
	</head>
	<body>
		<h1>Новостная лента</h1>
        <?php
        if ($errorMsg) {
            ?>
            <div class="error"><?=$errorMsg?></div>
            <?
        }

        if (isset($_GET['act']) && $_GET['act'] == 'show') {
			?>
			<a class='btn' href='news.php'>Последние новости</a>
			<?
			$id = $news->clearInt($_GET['id']);
			if ($id) {
				$news_item = $news->getNewsItem($id);
				if ($news_item) {
					?>
					<h2><?=$news_item['title']?></h2>	
					<div class='news__item'>
						<div class='news__date'><?=date('d.m.Y H:i', $news_item['datetime'])?></div>
						<div class='news__category'>Категория: <?=$news_item['category']?></div>							
						<div class='news__text'><?=$news_item['text']?></div>
						<div class='news__source'>Источник: <a href='<?=$news_item['source']?>' target=_blank><?=$news_item['source']?></a></div>					
					</div>
					<?
				}
				else {
					?>
					<div>Новость не найдена!</div>
					<?
				}
			}
			else {
				?>
				<div>Новость не найдена!</div>
				<?
			}
		}
		else if (isset($_GET['act']) && $_GET['act'] == 'add') {
            $title = '';
            $category = '';
            $description = '';
            $text = '';
            $source = '';
			?>
			<a class='btn' href='news.php'>Последние новости</a>
			<h2>Добавить новость</h2>
			<form action="" method="post" class="add-news-form">
				<div class="add-news-form__title">Заголовок новости:</div>
				<input class="add-news-form__input" type="text" name="title" value="<?=$title?>" autocomplete="off"/>
				<div class="add-news-form__title">Выберите категорию:</div>
				<select class="add-news-form__select" name="category">
					<option value="0">не выбрано</option>				
					<?
					$cats = $news->getCategories();
					foreach ($cats as $cat) {
						$selected = ($category == $cat['id']) ? 'selected' : '';
						?>
						<option value="<?=$cat['id']?>" <?=$selected?>><?=$cat['name']?></option>
						<?
					}
					?>				
				</select>			
				<div class="add-news-form__title">Краткое описание новости:</div>
				<input class="add-news-form__input" type="text" name="description" value="<?=$description?>" autocomplete="off"/>
				<div class="add-news-form__title">Текст новости:</div>
				<textarea class="add-news-form__textarea" name="text" cols="50" rows="5" autocomplete="off"><?=$text?></textarea>
				<div class="add-news-form__title">Источник:</div>
				<input class="add-news-form__input" type="text" name="source" value="<?=$source?>" autocomplete="off"/>			
				<input type="submit" class="btn add-news-form__btn" value="Добавить" />
			</form>
			<?
		}
		else {
			?>
			<a class='btn' href='news.php?act=add'>Добавить новость</a>
			<h2>Последние новости</h2>
            <a class='btn rss-btn' href='news.php?act=rss' target="_blank">RSS</a>
			<div class="news">
			<?php
				include('inc/get_news.inc.php');
			?>
			</div>
			<?
		}
		?>		
	</body>
</html>