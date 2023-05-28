<?php
$condition = true;
// Database connection
if (empty(session_id()) && !headers_sent()) {
  session_start();
}

require_once 'Database Connection.php';
$db = new connect();
$conn = $db->connection();

if (!$conn) {
  echo "<script>alert('Connection failed: " . mysqli_connect_error() . "')</script>";
}

?>

<!DOCTYPE html>

<html>

<head>
  <meta charset="UTF-8">
  <title>Profile </title>
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
  <div class="profile">
    <br /><br />
    <img class="profile__image" src="https://via.placeholder.com/200" alt="Profile Image">

    <input type="submit" value="Edit Profle picture">
    <h1 class="profile__name">
      <?php echo $_SESSION['username']; ?> (
      <?php echo $_SESSION['role']; ?> )
    </h1>

    <div class="profile__info">
      <div class="profile__info-item">
        <span class="profile__info-label">Email:</span>
        <?php echo $_SESSION['email']; ?>
      </div>

      <div class="profile__info-item">
        <span class="profile__info-label">Followed Cateogries:</span>
        <?php foreach ($_SESSION['content'] as $index => $value)
          echo "$value, ";
        ?>

      </div>

    </div>
    <br /><br />
    <div style="display: inline-block;">
      <input id="addButton" type="submit" onclick="location.href='AddUser.php'" value="Add user">
      <input id="deleteButton" type="submit" onclick="location.href='DelUser.php'" value="Delete user">
    </div>

    <script>
      var condition = <?php echo $_SESSION['role'] == 'Admin' ? 'false' : 'true'; ?>;
      var addbutton = document.getElementById("addButton");
      var deletebutton = document.getElementById("deleteButton");


      if (condition) {
        addbutton.style.visibility = "hidden";
        deletebutton.style.visibility = "hidden";

      }
    </script>

  </div>
</body>

</html>