<?php

class DatabaseConnection
{
    private $db;

    public function __construct()
    {
        if (empty(session_id()) && !headers_sent()) {
            session_start();
        }
        require_once 'Database Connection.php';
        $this->db = new connect();
    }

    public function getConnection()
    {
        $conn = $this->db->connection();
        if (!$conn) {
            echo "<script>alert('Connection failed: " . mysqli_connect_error() . "')</script>";
        }

        return $conn;
    }
}

class FollowedCategories
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getContent($cat)
    {

        // Retrieve file path from the database
        $sql = "SELECT * FROM content WHERE CategoryName='$cat'";
        $conn = $this->db->getConnection();
        $result = $conn->query($sql);

        if ($result) {
          // If the query returns a result, use PHP to output a script tag that sets the display property of the alert element to "block"
          echo '<script> document.getElementById("alert").style.display = "block"; </script>';
        }


        if ($result->num_rows > 0) {
            // Read the contents of the text file
            while ($row = $result->fetch_assoc()) {
                $userID = $row['userID'];
                $sql2 = "SELECT username FROM users WHERE userID=$userID";
                $filepath = $row['ContentPath'];
			        	$content_id = $row['ContentID'];
                $type = $row['Type'];
                $result2 = $conn->query($sql2);
                $row2 = $result2->fetch_assoc();
                echo '<div class="notification">';
                echo '<div class="notification__title">New ' . $row['Type'] .' added!</div>';
                echo '<div class="notification__publicher">' . $row2['username'] . '</div>';
                echo '<div class="notification__Cateogry">' . $row['CategoryName'] .'</div>';
                echo '<br/>';
                echo '<div class="notification__message">' . $row['ContentPath'] .'</div>';

               

				if ($_SESSION['role'] === 'Admin'|| $_SESSION['userID']==$row['userID']) {
                    echo '<a href="delete.php?id=' . $row['ContentID'] . '&type=' . $row['Type'] . '"><i class="fa fa-trash"></i></a>';
                }
                echo '</div>';
                echo '</div>';
            }
        } else {
            echo "";
        }
    }

  
}


// Usage:
$database = new DatabaseConnection();


?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Notification Page</title>
  <style>
    /* CSS for the notification page */
    body {
      font-family: Arial, sans-serif;
    }

    .notification {
      border: 1px solid #ccc;
      background-color: #f2f2f2;
      padding: 10px;
      margin-bottom: 10px;
    }

    .notification__title {
      font-size: 16px;
      font-weight: bold;
      margin-bottom: 5px;
    }

    .notification__message {
      font-size: 14px;
      margin-bottom: 5px;
    }

    .notification__timestamp {
      font-size: 12px;
      color: #999;
    }
	/* CSS for the toolbar */
    .toolbar {
      background-color: #f2f2f2;
      height: 50px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0 20px;
    }

    .toolbar__logo {
      font-size: 24px;
      font-weight: bold;
    }

    .toolbar__menu {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .toolbar__menu-item {
      margin-right: 20px;
      cursor: pointer;
    }

    .toolbar__menu-item:hover {
      text-decoration: underline;
    }
	a {
    text-decoration: none;
    color: black;
  }
  </style>
</head>
<body>
<div class="toolbar">
 <div class="toolbar__logo"> <a href="HomePage.php">Learning HUB</a></div>
    <div class="toolbar__menu">
      <div class="toolbar__menu-item"><a href="Language.php">Language</a></div>
      <div class="toolbar__menu-item"><a href="Mathematics.php">Mathematics</a></div>
      <div class="toolbar__menu-item"> <a href="Technology.php">Technology</a></div>
	  </div>
	  
	<div  
<h1></h1>
 
  

  <!-- Include a div element with an id that you can use to reference it in JavaScript -->
<div id="alert" style="display: none;">
  New data has been added!
</div>


  <select id="menu" onchange="window.location.href=this.value;">
    <option value=""></option>
    <option value="notification.php">notification</option>
    <option value="profile.php">profile</option>
    
  </select>
  </div>
	  
	  <div class="toolbar__menu-item"><a href="login.php">Log out</a></div>
    </div>
  </div>
  

  <h1>Notifications</h1>

  

  <?php
    $content = new FollowedCategories($database);
    if($_SESSION['content'] !=null) {
   foreach ($_SESSION['content'] as $index => $value)  {
   
    $content->getContent($value);
   }
  }
  ?>
  
  

  
  

</body>
</html>