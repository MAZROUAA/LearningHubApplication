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
      echo "
<script>alert('Connection failed: " . mysqli_connect_error() . "')</script>";
    }

    return $conn;
  }
}


class FollowCategory
{
  private $db;

  public function __construct($db)
  {
    if (empty(session_id()) && !headers_sent()) {
      session_start();
    }
    $this->db = $db;
  }

  public function follow($userID, $categoryName)
  {
    $conn = $this->db->getConnection();
    $stmt = $conn->prepare("SELECT * FROM followedcategories WHERE UserID = ? and categoryname= ? ");
    $stmt->bind_param("ss", $userID, $categoryName);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
      echo "You already follow this category.";
    } else {
      $stmt = $conn->prepare("INSERT INTO followedcategories (UserID, categoryname) VALUES (?, ?)");
      $stmt->bind_param("ss", $userID, $categoryName);
      $execval = $stmt->execute();

      if (!$execval) {
        echo "Error: " . $stmt->error;
      } else {
        echo "You are now following this category.";
      }
    }
  }

  public function unfollow($userID, $categoryName)
  {
    $conn = $this->db->getConnection();
    $stmt = $conn->prepare("SELECT * FROM followedcategories WHERE UserID = ? and categoryname= ? ");
    $stmt->bind_param("ss", $userID, $categoryName);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 0) {
      echo "You don't follow this category.";
    } else {
      $stmt = $conn->prepare("DELETE FROM followedcategories WHERE UserID= ? AND categoryname =?");
      $stmt->bind_param("ss", $userID, $categoryName);
      $execval = $stmt->execute();

      if (!$execval) {
        echo "Error: " . $stmt->error;
      } else {
        echo "You unfollowed this category.";
      }
    }
  }
}


class AddingArticle
{

  private $db;

  public function __construct($db)
  {
    $this->db = $db;
  }
  public function addArticle()
  {
    if (isset($_POST['addButton'])) {

      //Code to handle form submission goes here

      $category = $_POST['hidden_category2'];
      $content = $_POST['textarea'];

      if (!empty($content)) {

        if (strlen($content) > 1000) {
          echo "Error: Content must not exceed 1000 characters";
          return;
        }

        // Generate a unique filename for the text file
        $filename = uniqid() . '.txt';

        // Define the path where the text file will be saved
        $path = 'C:\xampp\htdocs\Articles' . $filename;
        $userID = $_SESSION['userID'];

        // Save the content to the text file and check for errors
        $result = file_put_contents($path, $content);
        if ($result === false) {
          echo "Error: Could not save file";
          return;

        }

        // Prepare the SQL statement to insert the file path into the database
        $conn = $this->db->getConnection();
        $sql = "INSERT INTO content (ContentPath, CategoryName, userID) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $path, $category, $userID);

        // Execute the SQL statement

        if ($stmt->execute() === false) {
          echo "Error: " . $stmt->error;
          return;
        }

        echo "Record created successfully";

      } else {
        echo "Error: Content is empty";
        exit;
      }

    }
  }
}

class VideoUploader
{
  private $db;

  public function __construct($db)
  {
    $this->db = $db;
  }

  public function uploadVideo()
  {
    if (isset($_POST["uploadButton"])) {
      $path = 'Videos/';

      $allowedExts = array("mp3", "mp4");

      $extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
      $userID = $_SESSION['userID'];


      if (in_array($extension, $allowedExts) && ($_FILES["file"]["size"] < 35000000)) {
        if ($_FILES["file"]["error"] > 0) {
          echo "Return Code: " . $_FILES["file"]["error"] . "<br />";
        } else {
          

          if (file_exists("VideosImported/" . $_FILES["file"]["name"])) {
            echo $_FILES["file"]["name"] . " already exists. ";
          } else {
            move_uploaded_file(
              $_FILES["file"]["tmp_name"],
              "VideosImported/" . $_FILES["file"]["name"]
            );
          
            $path = 'C:\xampp\htdocs\VideosImported' . $_FILES["file"]["name"];
            if ($extension == "mp3") {
              $type = "Record";
            }
            if ($extension == "mp4") {
              $type = "Video";
            }

            $category = $_POST['hidden_category'];

            $sql = "INSERT INTO content (ContentPath, CategoryName, Type, userID) VALUES (?, ?, ?, ?)";
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssi", $path, $category, $type, $userID);

            // Execute the SQL statement
            if ($stmt->execute() === false) {
              echo "Error: " . $stmt->error;
              return;
            }

            echo "Record created successfully";
          }
        }
      } else {
        echo "Invalid file , files must be mp3 or mp4 and with size <35 MB";
      }
    }
  }
}
$_SESSION['new_content'] = false;

class Content
{
  private $db;

  public function __construct($db)
  {
    $this->db = $db;
  }

  public function getArticlesContent()
  {

    // Retrieve file path from the database
    $sql = "SELECT c.*, u.username 
    FROM content c 
    JOIN users u ON c.userID = u.userID 
    WHERE c.Type = 'Article' AND CategoryName = 'Technology'";    
    $conn = $this->db->getConnection();
    $result = $conn->query($sql);




    if ($result->num_rows > 0) {

      // Read the contents of the text file
      while ($row = $result->fetch_assoc()) {
        $filepath = $row['ContentPath'];
        $content_id = $row['ContentID'];
        $type = $row['Type'];
        $file_contents = file_get_contents($filepath);
        echo '<div class="container">';
        echo '<div class="Publicher">';
        echo '<h1>' . $row['username'] . '</h1>';
        echo '<h3>' . $row['CategoryName'] . '</h3>';
        echo '<p>' . $file_contents . '</p>';
        echo '<p>' . $row['Type'] . '</p>';

        if ($_SESSION['role'] === 'Admin' || $_SESSION['userID'] == $row['userID']) {
          echo '<a href="delete.php?id=' . $row['ContentID'] . '&type=' . $row['Type'] . '&path=' . $row['ContentPath'] . '"><i class="fa fa-trash"></i></a>';
        }
        echo '</div>';
        echo '</div>';
      }
    } else {
      echo "";
    }
  }

  public function getVideosContent()
  {
    //$video_path2 = 'VideosImported/20200806_173554.mp4';

    // Retrieve file path from the database
    $sql = "SELECT c.*, u.username 
    FROM content c 
    JOIN users u ON c.userID = u.userID 
    WHERE c.Type = 'Video'AND CategoryName = 'Technology'";    
    $conn = $this->db->getConnection();
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
      // Read the contents of the text file
      while ($row = $result->fetch_assoc()) {
        $video_path = $row['ContentPath'];
        $filename = basename($video_path);
        $position = 14; // The position after which you want to add a forward slash after "VideosImported"
        $filename = substr($filename, 0, $position) . '/' . substr($filename, $position);
        echo '<div class="container">';
        echo '<div class="Publicher">';
        echo '<h1>' . $row['username'] . '</h1>';
        echo '<h3>' . $row['CategoryName'] . '</h3>';        
        echo '<video width="640" height="480" controls>';
        echo '<source src="' . $filename . '" type="video/mp4">';
        echo 'Your browser does not support the video tag.';
        echo '</video>';
        echo '<p>' . $row['Type'] . '</p>';
        if ($_SESSION['role'] === 'Admin' || $_SESSION['userID'] == $row['userID']) {
          echo '<a href="delete.php?id=' . $row['ContentID'] . '&type=' . $row['Type'] .  '&path=' . $filename .'"><i class="fa fa-trash"></i></a>';
        }
        echo '</div>';
        echo '</div>';
      }
    } else {
      echo "";
    }
  }
  public function getRecordsContent()
  {
    //$video_path2 = 'VideosImported/20200806_173554.mp4';

    // Retrieve file path from the database
    $sql = "SELECT c.*, u.username 
    FROM content c 
    JOIN users u ON c.userID = u.userID 
    WHERE c.Type = 'Record'AND CategoryName = 'Technology'"; 
    $conn = $this->db->getConnection();
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {

      while ($row = $result->fetch_assoc()) {
        $record_path = $row['ContentPath'];
        $filename = basename($record_path);
        $position = 14; // The position after which you want to add a forward slash after "recordsImported"
        $filename = substr($filename, 0, $position) . '/' . substr($filename, $position);
        echo '<div class="container">';
        echo '<div class="Publicher">';
        echo '<h1>' . $row['username'] . '</h1>';
        echo '<h3>' . $row['CategoryName'] . '</h3>';        
        echo '<audio controls>';
        echo '<source src="' . $filename . '" type="audio/mp3">';
        echo '</audio>';
        echo '<p>' . $row['Type'] . '</p>';
        if ($_SESSION['role'] === 'Admin' || $_SESSION['userID'] == $row['userID']) {
          echo '<a href="delete.php?id=' . $row['ContentID'] . '&type=' . $row['Type'] . '&path=' . $filename . '"><i class="fa fa-trash"></i></a>';
        }
        echo '</div>';
        echo '</div>';
      }
    } else {
      echo "";
    }
  }

}


class FollowedCategories
{
  private $db;

  public function __construct($db)
  {
    $this->db = $db;
  }

  public function getContent()
  {
    $id = $_SESSION['userID'];

    $conn = $this->db->getConnection();
    $sql = "SELECT * FROM followedcategories where UserID='$id'";
    $result = mysqli_query($conn, $sql);

    // Create an array to store the content
    $content = array();

    // Check if any rows were returned
    if (mysqli_num_rows($result) > 0) {
      // Loop through each row and add it to the content array
      while ($row = mysqli_fetch_assoc($result)) {
        $content[] = $row['categoryname'];
      }
    }

    // Store the content array in the session
    $_SESSION['content'] = $content;
  }
}


$database = new DatabaseConnection();
$followCategory = new FollowCategory($database);

if (isset($_POST['followBtn'])) {
  $followCategory->follow($_SESSION['userID'], "Technology");
}

if (isset($_POST['unfollowBtn'])) {
  $followCategory->unfollow($_SESSION['userID'], "Technology");
}


$article = new AddingArticle($database);
$article->addArticle();


$media = new VideoUploader($database);
$media->uploadVideo();

$followedCategories = new FollowedCategories($database);
$followedCategories->getContent();
?>




<!DOCTYPE html>

<html>

<head>
  <meta charset="UTF-8">
  <title>Technology </title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <style>
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

    /* CSS for the profile page */
    body {
      font-family: Arial, sans-serif;
    }

    .profile {
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .profile__image {
      width: 200px;
      height: 200px;
      border-radius: 50%;
      object-fit: cover;
      margin-bottom: 20px;
    }

    .profile__name {
      font-size: 24px;
      font-weight: bold;
      margin-bottom: 10px;
    }

    .profile__info {
      font-size: 16px;
      line-height: 1.5;
      text-align: center;
      max-width: 400px;
      margin-bottom: 20px;
    }

    .profile__info-item {
      margin-bottom: 10px;
    }

    .profile__info-label {
      font-weight: bold;
      margin-right: 10px;
    }

    input[type="submit"] {
      background-color: #4CAF50;
      color: #fff;
      padding: 10px 20px;
      border: none;
      border-radius: 3px;
      cursor: pointer;
      font-size: 16px;
      font-weight: bold;
    }

    input[type="submit"]:hover {
      background-color: #3e8e41;
    }

    .left-side {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      max-width: 800px;
    }

    .container {
      display: flex;
      flex-wrap: wrap;
      align-items: Left;
      justify-content: Left;
      border: 1px solid #ccc;
      padding: 10px;
      margin-bottom: 10px;
      margin: 0 auto;
    }

    .images {
      max-width: 100%;
      height: 5 px;
      margin-bottom: 20px;
      align: center;
    }

    .share {
      display: flex;
      flex-wrap: wrap;

      border: 1px solid #ccc;
      padding: 10px;
      margin-bottom: 10px;
      margin: 0 auto;
    }

    /* CSS for the frame */
    fieldset {
      border: 2px solid #ddd;
      padding: 10px;
      margin: 20px 0;
    }

    legend {
      font-weight: bold;
    }

    /* CSS for the overlay */
    .overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
      display: none;
      justify-content: center;
      align-items: center;
    }

    .overlay-content {
      background-color: white;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
      text-align: center;
    }

    /* Style for the pop-up */
    .popup {
      display: none;
      position: fixed;
      top: 50%;
      left: 50%;
      width: 300px;
      height: 100px;
      transform: translate(-50%, -50%);
      background-color: #fff;
      padding: 20px;
      border: 1px solid #ccc;
      z-index: 9999;
    }

    .close-button {
      position: absolute;
      top: 10px;
      right: 10px;
      cursor: pointer;
    }

    a {
      text-decoration: none;
      color: black;
    }

    follow-button {
      background-color: #007bff;
      color: #fff;
      border: none;
      padding: 10px 20px;
      font-size: 16px;
      cursor: pointer;
      margin-right: 10px;
    }

    .follow-button:hover {
      background-color: #0069d9;
    }

    unfollow-button {
      background-color: #007bff;
      color: #fff;
      border: none;
      padding: 10px 20px;
      font-size: 16px;
      cursor: pointer;
      margin-right: 10px;
    }

    .unfollow-button:hover {
      background-color: #0069d9;
    }

    .container2 {
      display: flex;
      flex-wrap: wrap;
      align-items: left;
      justify-content: left;
      border: 1px solid #ccc;
      padding: 10px;
      margin-bottom: 10px;
      margin: 0 auto;
    }


    #follow-button,
    #unfollow-button {

      background-color: #4CAF50;
      color: #fff;
      padding: 10px 20px;
      border: none;
      border-radius: 3px;
      cursor: pointer;
      font-size: 16px;
      font-weight: bold;
      color: white;
    }



    #unfollow-button::after {
      content: "";
    }

    #unfollow-button.followed::after {
      content: "";
    }

    .Publicher h1, .Publicher h3  .Publicher video {
            display: inline-block;
            margin-right: 5px;
            margin-bottom: 2px;

        }

        

        .Publicher h3 {
            color: grey;
        }
    
        .Publicher p {
        margin-top: 1px;
        }
        

  </style>
  <script>
    // JavaScript to show/hide the overlay
    function showOverlay() {
      document.querySelector('.overlay').style.display = "flex";
    }
    function hideOverlay() {
      document.querySelector('.overlay').style.display = "none";
    }
  </script>




</head>

<body>
  <div class="toolbar">
    <div class="toolbar__logo"> <a href="HomePage.php">Learning HUB</a></div>
    <div class="toolbar__menu">
      <div class="toolbar__menu-item"><a href="Language.php">Language</a></div>
      <div class="toolbar__menu-item"><a href="Mathematics.php">Mathematics</a></div>
      <div class="toolbar__menu-item"> <a href="Technology.php">Technology</a></div>
    </div>

    <div <h1>
      </h1>

      <select id="menu" onchange="window.location.href=this.value;">
        <option value=""></option>
        <option value="notification.php">notification</option>
        <option value="profile.php">profile</option>

      </select>
    </div>
    <div class="toolbar__menu-item"><a href="login.php">Log out</a></div>
  </div>
  </div>





  <div class="container">
    <div class="center">
      <img src="image.jpg" alt="Login Image">
    </div>
  </div>







  <div class="container2">
    <div>
      <h1>Technology </h1>
      <form id="myform" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
        <button id="follow-button" name="followBtn">Follow</button>
        <button id="unfollow-button" name="unfollowBtn">Unfollow</button>

      </form>



    </div>
  </div>





  <div class="share" style="align-items:left">

    <div>
      <fieldset>
        <legend></legend>
        <h1></h1>
        <p><a href="#" onclick="showPopup()">What do you to share ?</a></p>


        <div id="popup1" class="popup">
          <p>puplisher
            &nbsp; &nbsp;&nbsp;
            <select id="menu1" onchange="">
              <option value="Technology" selected>Technology</option>

            </select>
          </p>



          <button onclick="showPopup2()">Upload</button>
          <button onclick="showPopup3()">Article</button>

          <span class="close-button" onclick="document.getElementById('popup1').style.display = 'none'">&times;</span>

        </div>



        <div id="popup2" class="popup">
          <p></p>
          <span class="close-button" onclick="document.getElementById('popup2').style.display = 'none'">&times;</span>
          <form class="record" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="POST"
                        enctype="multipart/form-data">           
                        <label for="file">Choose video or record:</label>
                        <input type="file" id="file" name="file">
                        <br><br>
                        <input type="hidden" name="hidden_category" id="hidden_category" value="">
                        <input type="submit" value="Upload" name="uploadButton">
                    </form>
        </div>

        <div id="popup3" class="popup">
          <p></p>
          <span class="close-button" onclick="document.getElementById('popup3').style.display = 'none'">&times;</span>
          <label for="textarea">Enter your message:</label><br><br>
          <form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
                        <textarea id="textarea" name="textarea" rows="4" cols="50"></textarea><br>
                        <input type="hidden" name="hidden_category2" id="hidden_category2" value="">
                        <input type="submit" value="add" name="addButton">
                </div>
                </form>

        <script>
          function showPopup() {
            document.getElementById("popup1").style.display = "block";
          }

          function showPopup2() {
            document.getElementById("popup2").style.display = "block";
            var category = document.getElementById("menu1").value;
            document.getElementById("hidden_category").value = category;
          }

          function showPopup3() {
            document.getElementById("popup3").style.display = "block";
            var category = document.getElementById("menu1").value;
            document.getElementById("hidden_category2").value = category;
          }

        </script>
      </fieldset>
    </div>

  </div>


  <div class="container">

    <div class="Publicher ">
      <h1>Hasnaa Ahmed </h1>
      <h3> Technology </h3>

      <p>Technology has witnessed impressive evolution in the past few decades, which 
        has in turn transformed our lives and helped us evolve with it. Right from roadways, 
        railways, and aircraft for seamless travel to making communication effortless from any 
        part of the world, technology has contributed more than anything to help mankind live a 
        life of luxury and convenience. It is also because of technology that we know our world 
        and outer space better. Every field owes its advancement to technology, and this clearly
        indicates the importance of technology in every aspect of our lives. </p>
    </div>
  </div>
  <!--  <div class="container">
    <div class="Publicher ">
      <h1>Nada Mandour </h1>
      <h3> Technology </h3>
      <img class="images" src="technology.jpg" alt="technology Image">
    </div>-->
  </div>

  <div class="container">
    <div class="Publicher ">
      <h1>Hasnaa Ahmed </h1>
      <h3> Technology </h3>

      <p>ChatGPT is an innovative artificial intelligence (AI) language model developed by OpenAI, 
        one of the leading AI research organizations in the world. It is an advanced deep learning system
        designed to understand, analyze and generate human-like language, making it one of the most powerful 
        conversational AI tools available today.</p>
    </div>
  </div>
  <?php
  $content = new Content($database);
  $content->getArticlesContent();

  $content = new Content($database);
  $content->getVideosContent();

  $content = new Content($database);
  $content->getRecordsContent();
  ?>


</body>

</html>