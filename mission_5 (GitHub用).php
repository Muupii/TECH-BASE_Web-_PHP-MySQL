<?php
    date_default_timezone_set('Asia/Tokyo');

    define('DB_NAME','*****');
    define('DB_USERNAME','*****');
    define('DB_PASSWORD','*****');
    define('PDO_DSN','mysql:dbhost=localhost;dbname=' . DB_NAME . '; charset=utf8');
    
    
    
    class DB {
        public $pdo;
        function __construct(){
            try {
                $this->pdo = new PDO(PDO_DSN, DB_USERNAME, DB_PASSWORD);
                // $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                echo $e->getMessage();
                exit;
            }
        }
    
        public function createTable() {
            $sql = 'create table if not exists board (id int auto_increment PRIMARY KEY, name varchar(10), comment varchar(10), date date, password varchar(10)) engine=innodb default charset=utf8';
            $smt = $this->pdo->query($sql);
        }
    
        public function showTables() {
            $smt = $this->pdo->prepare('SHOW TABLES FROM tb210272db');
            $smt->execute();
            $result = $smt->fetchAll();
            return $result;
        }
    
        public function descTable() {
            $smt = $this->pdo->prepare('desc board');
            $smt->execute();
            return $smt->fetch(PDO::FETCH_ASSOC);
        }
    
        public function insert($name, $comment, $password) {
            $smt = $this->pdo->prepare('insert into board (name, comment, password, date) values (:name, :comment, :password, :date)');
            $smt->bindParam(':name', $name, PDO::PARAM_STR);
            $smt->bindParam(':comment', $comment, PDO::PARAM_STR);
            $smt->bindParam(':password', $password, PDO::PARAM_STR);
            $date = date("Y/m/d H:i:s");
            $smt->bindParam(':date', $date, PDO::PARAM_STR);
            $smt->execute();
        }
        
        public function selectAll() {
            foreach($this->pdo->query('select * from board') as $row) {    
                $results[] = $row;
                }
            return $results;
        }
    
        public function update($name, $comment, $password, $id) {
            $smt = $this->pdo->prepare('update board set name=:name, comment=:comment, password=:password where id = :id');
            $smt->bindParam(':name', $name, PDO::PARAM_STR);
            $smt->bindParam(':comment', $comment, PDO::PARAM_STR);
            $smt->bindParam(':password', $password, PDO::PARAM_STR);
            $smt->bindParam(':id', $id, PDO::PARAM_INT);
            $smt->execute();
        }
    
        public function delete($id) {
            $smt = $this->pdo->prepare('delete from board where id = :id');
            $smt->bindParam(':id', $id, PDO::PARAM_INT);
            $smt->execute();
        }
        
        public function getPassword($id) {
            $smt = $this->pdo->prepare('select password from board where id = :id');
            $smt->bindParam(':id', $id, PDO::PARAM_INT);
            $smt->execute();
            $result = $smt->fetch(PDO::FETCH_ASSOC);
            return $result['password'];
        }

        public function getName($id) {
            $smt = $this->pdo->prepare('select name from board where id = :id');
            $smt->bindParam(':id', $id, PDO::PARAM_INT);
            $smt->execute();
            $result = $smt->fetch(PDO::FETCH_ASSOC);
            return $result['name'];
        }

        public function getComment($id) {
            $smt = $this->pdo->prepare('select comment from board where id = :id');
            $smt->bindParam(':id', $id, PDO::PARAM_INT);
            $smt->execute();
            $result = $smt->fetch(PDO::FETCH_ASSOC);
            return $result['comment'];
        }

        public function dropTable() {
            $smt = $this->pdo->prepare('drop table if exists board;');
            $smt->execute();
        }

        public function countAll() {
            $smt = $this->pdo->prepare('select count(*) from board;');
            $smt->execute();
            $result = $smt->fetch(PDO::FETCH_ASSOC);
            return $result['count(*)'];
        }

    }
    
    $action = new DB();
    
   

    if (isset($_POST['comment']) && isset($_POST['name']) &&isset($_POST['password'])) {
        $comment = $_POST['comment'];
        $name = $_POST['name'];
        $password = $_POST['password'];
        if ($comment != "" && $name != "") {
            $date = date("Y/m/d H:i:s");

            $action->insert($name, $comment, $password);
            

        }
        
    }

    if (isset($_POST["del_num"]) && isset($_POST['del_password'])) {
        $del_num = $_POST["del_num"];
        $password = $_POST['del_password'];

        if ($password == $action->getPassword($del_num)) {
            $action->delete($del_num);
        }

    }
    
    if (isset($_POST["edited_num"]) && isset($_POST['edited_password'])) {
        $edited_num = $_POST["edited_num"];
        $edited_password = $_POST["edited_password"];
        if ($edited_password == $action->getPassword($edited_num)) {
            $edited_name = $action->getName($edited_num);
            $edited_comment = $action->getComment($edited_num);
        } else {
            $_POST["edited_num"] = null;
        }
    
    }
    
    if (isset($_POST['edit_comment']) && isset($_POST['edit_name']) && isset($_POST['password'])) {
        $edit_comment = $_POST['edit_comment'];
        $edit_name = $_POST['edit_name'];
        $edit_num = $_POST['edit_num'];
        $edit_password = $_POST['password'];
        if ($edit_comment != "" && $edit_name != "") {
            
            $action->update($edit_name, $edit_comment, $edit_password, $edit_num);

        }
        
    }

    if ($action->countAll() != 0) {
        $post_list = $action->selectAll();
    }

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Web掲示板byまっちゃん</title>
</head>
<body>

    <div style="text-align: center; margin: auto; witdh: 90%;">

        <h1>Web掲示板byまっちゃん</h1>

        <?php if (isset($_POST["edited_num"])) { ?>

            <div style="border: solid; width: 50%; padding: 5px; text-align: left; margin: 10px auto; witdh: 90%;">

                <h2 style="font-size: large;">編集フォーム</h2>

                <form action=""  method="post">

                    名前：<input type="text" value="<?php echo $edited_name; ?>" name="edit_name">

                    コメント：<input type="text" value="<?php echo $edited_comment; ?>" name="edit_comment">

                    パスワード：<input type="text" value="<?php echo $edited_password; ?>" name="password">
                    
                    <input type="hidden" value="<?php echo $edited_num; ?>" name="edit_num">

                    <input type="submit" value="編集">
                
                </form>

            </div>

        <?php } else { ?>

            <div style="border: solid; width: 50%; padding: 5px; text-align: left; margin: 10px auto; witdh: 90%;">

                <h2 style="font-size: large;">新規投稿フォーム</h2>

                <form action=""  method="post">

                    名前：<input type="text" name="name">

                    コメント：<input type="text" name="comment">

                    パスワード：<input type="text" name="password">

                    <input type="submit" value="投稿">
                
                </form>
            </div>

            <div style="border: solid; width: 50%; padding: 5px; text-align: left; margin: 10px auto; witdh: 90%;">
        
                <h2 style="font-size: large;">削除番号指定用フォーム</h2>

                <form action="" method="post">
                
                削除対象番号：<input type="text" name="del_num">

                パスワード：<input type="text" name="del_password">

                <input type="submit" value="削除">
                
                </form>

            </div>

            <div style="border: solid; width: 50%; padding: 5px; text-align: left; margin: 10px auto; witdh: 90%;">

                <h2 style="font-size: large;">編集番号指定用フォーム</h2>
                <form action="" method="post">
                
                編集対象番号：<input type="text" name="edited_num">

                パスワード確認：<input type="text" name="edited_password">

                <input type="submit" value="編集">
                
                </form>

            </div>

        <?php } ?>
        
        <h2>コメント一覧</h2>

        <?php if ($action->countAll() != 0) {
            foreach ($post_list as $post) { ?>

                <div style="border: ridge;width: 50%; padding: 5px; text-align: left; margin: 10px auto; witdh: 90%;">

                    <p style="font-weight: bold;"><?php echo "ID：" . $post['id'] . "　投稿者：" . $post['name'] . "　投稿日時：" . $post['date']; ?></p>

                    <div style="border: ridge thin; margin-bottom: 10px; width: 50%; padding: 5px;">
                    
                    <p><?php echo $post['comment']; ?></p>
                    
                    </div>
                
                </div>

        <?php }} ?>

    </div>



    
</body>
</html>
