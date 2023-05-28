<?php

class DatabaseConnection {
	private $db;

	public function __construct() {
		session_start();
		require_once 'Database Connection.php';
		$this->db = new connect();
	}

	public function getConnection() {
		return $this->db->connection();
	}
}

class DeleteUser {
  private $db;

  public function __construct(DatabaseConnection $db) {
      $this->db = $db;
  }

  public function deleteUser($username, $email) {
      $conn = $this->db->getConnection();
	  
	   $stmt =$conn->prepare("SELECT * FROM users WHERE username = ? AND email = ? ");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();
if ($result->num_rows > 0) {
      $stmt = $conn->prepare("DELETE  FROM users WHERE username = ? AND email = ? ");
      $stmt->bind_param("ss", $username, $email);
$execval = $stmt->execute();
 echo "User deleted successfully...";
}
     
      else {
          echo "User does not exist...";
      }
  }
}

$database = new DatabaseConnection();
$user = new DeleteUser($database);

if (isset($_POST['deleteBtn'])) {
  $username = $_POST['username'];
  $email = $_POST['email'];

  $user->deleteUser($username, $email);
}

?>


<!DOCTYPE html>

<html>

<head>
    <meta charset="UTF-8">
    <title>Delete User </title>
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

    input[type="text"],
    input[type="email"],
    input[type="password"] {
        padding: 10px;
        border-radius: 3px;
        border: none;
        width: 100%;
        margin-bottom: 20px;
        box-sizing: border-box;
        background-color: #f2f2f2;
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

    label {
        display: block;
        margin-bottom: 10px;
        font-weight: bold;

    }

    h1 {
        text-align: center;
        margin-top: 50px;
        margin-bottom: 20px;
    }

    .container {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: center;
        padding: 20px;
        max-width: 800px;
        margin: 0 auto;

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
        <img class="profile__image" src="https://via.placeholder.com/200" alt="Profile Image">
        <input type="submit" value="Edit Profle picture">
        <h1 class="profile__name"><?php echo $_SESSION['username'] ; ?> (<?php echo $_SESSION['role'];?> )</h1>

        <h1>Add User Information</h1>
        <div class="container">

            <div class="right-side">
                <form action="#" method="post">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>

                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>



                    <input type="submit" name="deleteBtn" value="Delete">
                </form>
            </div>
        </div>


    </div>
    </div>
</body>

</html>