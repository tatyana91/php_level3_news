<?php
class NewsDB implements INewsDB{
	private const DB_NAME = '../news.db';
	private const LOG_FILE = '../logs/sql_errors.log';
	private $_db;
	private const RSS_NAME = 'rss.xml';
	private const RSS_TITLE = 'Последние новости';
	private const RSS_LINK = 'http://php3.loc/news/news.php';

    function __construct(){
		$this->_db = new SQLite3(self::DB_NAME);
		if (!file_exists(self::DB_NAME) || !filesize(self::DB_NAME)) {
			try {
				$sql = "CREATE TABLE msgs(
							id INTEGER PRIMARY KEY AUTOINCREMENT,
							title TEXT,
							category INTEGER,
							description TEXT,
							text TEXT,
							source TEXT,
							datetime INTEGER)";
				if (!$this->_db->exec($sql)){
					throw new Exception('Не удалось создать таблицу новостей');
				}
				
				$sql = "CREATE TABLE category(
							id INTEGER PRIMARY KEY AUTOINCREMENT,
							name TEXT)";
				if (!$this->_db->exec($sql)){
					throw new Exception('Не удалось создать таблицу категорий');
				}
				
				$sql = "INSERT INTO category(name)
							SELECT 'Политика' as name
							UNION SELECT 'Культура' as name
							UNION SELECT 'Спорт' as name";
				if (!$this->_db->exec($sql)){
					throw new Exception('Не удалось заполнить таблицу категорий');
				}
			}
			catch(Exception $e) {
				$error = "\"".$this->_db->lastErrorMsg()."\" in file ".$e->getFile()." on line ".$e->getLine()." : ".$e->getMessage();				
				$this->logError($error);
				
				die($e->getMessage());
			}
		}
    }

	function logError($error){
		$log_error = date('d.m.Y H:i:s', time())." ";
		$log_error .= $error;
		$log_error .= "\n";
		file_put_contents(self::LOG_FILE, $log_error, FILE_APPEND);
	}

	function __get($name){
		if ($name == 'db') {
			return $this->_db;
		}
		else {
            throw new Exception('Ошибка доступа к свойству $name!');
        }
	}

    function __set($name, $value){
        throw new Exception('Ошибка доступа к свойству $name!');
    }

	function __destruct(){
		unset($this->_db);
	}
	
	function clearInt($value){
		return abs((int)$value);
	}
	
	function clearStr($value){
		$value = trim($value);
		$value = $this->_db->escapeString($value);
		return $value;
	}

	function saveNews($title, $category, $description, $text, $source){
		$sql = "INSERT INTO msgs (title, category, description, text, source, datetime) VALUES (:title, :category, :description, :text, :source, :datetime)";
		$datetime = time();
		$stmt = $this->_db->prepare($sql);
		$stmt->bindParam(':title', $title, SQLITE3_TEXT);
		$stmt->bindParam(':category', $category, SQLITE3_INTEGER);
		$stmt->bindParam(':description', $description, SQLITE3_TEXT);
		$stmt->bindParam(':text', $text, SQLITE3_TEXT);
		$stmt->bindParam(':source', $source, SQLITE3_TEXT);
		$stmt->bindParam(':datetime', $datetime, SQLITE3_INTEGER);
		if (!$stmt->execute()) {
			return false;
		}

		$this->createRss();
		return true;
	}

	function db2Arr($data){
		$arr = array();
		while ($row = $data->fetchArray(SQLITE3_ASSOC)){
			$arr[] = $row;
		}
		return $arr;
	}
	
	function getNews(){
		$sql = "SELECT 
					msgs.id as id, 
					title, 
					category.name as category, 
					description,
					text,
					source, 
					datetime 
				FROM msgs, category 
				WHERE category.id = msgs.category 
				ORDER BY msgs.id DESC";
		$items = $this->_db->query($sql);
		if (!$items) {
			return false;
		}
		return $this->db2Arr($items);
	}
	
	function getNewsItem($id){
		$sql = "SELECT 
					title, 
					category.name as category,
					text, 
					source, 
					datetime 
				FROM msgs, category 
				WHERE 
					category.id = msgs.category 
					AND msgs.id = :id";
		$stmt = $this->_db->prepare($sql);		
		$stmt->bindParam(':id', $id, SQLITE3_INTEGER);
		$item = $stmt->execute();
		if (!$item){
			return false;
		}
		return $item->fetchArray(SQLITE3_ASSOC);
	}
	
	function deleteNews($id){
		$sql = "DELETE FROM msgs WHERE id = :id";
		$stmt = $this->_db->prepare($sql);
		$stmt->bindParam(':id', $id, SQLITE3_INTEGER);
		if (!$stmt->execute()) {
			return false;
		}

        $this->createRss();
		return true;
	}
	
	function getCategories(){
		$sql = "SELECT id, name
				FROM category				
				ORDER BY name ASC";
		$items = $this->_db->query($sql);
		if (!$items) {
			return false;
		}
		return $this->db2Arr($items);
	}

	function createRss(){
        $dom = new DOMDocument();
        $dom->formatOutput = true;
        $dom->preserveWhiteSpace = false;

        $rss = $dom->createElement('rss');
        $version = $dom->createAttribute("version");
        $version->value = '2.0';
        $rss->appendChild($version);

        $channel = $dom->createElement('channel');

        $title = $dom->createElement('title', self::RSS_TITLE);
        $link = $dom->createElement('link', self::RSS_LINK);

        $channel->appendChild($title);
        $channel->appendChild($link);

        $news_items = $this->getNews();
        foreach($news_items as $news_item){
            $item = $dom->createElement('item');

            $title = $dom->createElement('title', $news_item['title']);
            $link = $dom->createElement('link', htmlentities($news_item['source']));

            $description = $dom->createElement('description');
            $cdata = $dom->createCDATASection($news_item['description']);
            $description->appendChild($cdata);

            $text = $dom->createElement('text');
            $cdata = $dom->createCDATASection($news_item['text']);
            $text->appendChild($cdata);

            $pubDate = $dom->createElement('pubDate', date('d.m.Y H:i', $news_item['datetime']));
            $category = $dom->createElement('category', $news_item['category']);

            $item->appendChild($title);
            $item->appendChild($link);
            $item->appendChild($description);
            $item->appendChild($text);
            $item->appendChild($pubDate);
            $item->appendChild($category);

            $channel->appendChild($item);
        }

        $dom->appendChild($channel);
        $dom->save(self::RSS_NAME);
    }
}