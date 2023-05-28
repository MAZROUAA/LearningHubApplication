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

class UserRegistration {
	private $db;
	
 

	public function __construct(DatabaseConnection $db) {
		$this->db = $db;
	}
	
public function registerUser($username, $email, $password, $role) {
	$conn = $this->db->getConnection();
	
	$stmt =$conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
if ($result->num_rows > 0) {
  // Username already exists, display alert
  echo "<script>alert('Username is already taken. Please choose a different username.');</script>";
  echo "<script>clearTextField();</script>";
  
} else {
  // Username is available, insert new user
  $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
  $stmt->bind_param("ssss", $username, $email, $password, $role);
 $execval= $stmt->execute();
  
	
	if (!$execval) {
		echo "Error: " . $stmt->error;
	} else {
		echo "Registration successfully...";
	}
  // Display success message or redirect to another page
}
	
}
}
// Usage
$database = new DatabaseConnection();
$register = new UserRegistration($database);

if (isset($_POST['signup'])) {
	$username = $_POST['username'];
	$email = $_POST['email'];
	$password = $_POST['password'];
	$role = 'User';

	$register->registerUser($username, $email, $password, $role);
}

?>


<!DOCTYPE html>
<html>

<head>
    <title>Registration Page</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f2f2f2;
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

    .left-side {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .right-side {
        flex: 1;
        padding: 20px;
        background-color: #fff;
        border-radius: 5px;
        box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.3);
    }

    img {
        max-width: 100%;
        height: auto;
        margin-bottom: 20px;
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
    </style>
	

    <script>

	function usernameValidation() {
    var regex = /^[a-zA-Z]+$/;
  var inputText = document.getElementById("username").value;
  if (!regex.test(inputText)) {
    alert("the data entered are not valid. Please enter only letters");
    document.getElementById("username").value = "";
  }
	
  }


function passwordValidation() {
  var inputText2 = document.getElementById("password").value;
  var regex = /^(?=.*\d)(?=.*[!@#$%^&*(),.?":{}|<>])(?=.*[a-zA-Z]).{5,}$/;
  if (!regex.test(inputText2)) {
    alert("Input is invalid. Please enter at least 5 characters, one numeric value, and one special character.");
    document.getElementById("password").value = "";
    return false;
  }
  return true;
}

function confirmPasswordValidation() {
        const password = document.querySelector('input[name=password]');
        const confirm = document.querySelector('input[name=confirm_password]');
        if (confirm_password.value === password.value) {
            confirm_password.setCustomValidity('');
        } else {
            confirm_password.setCustomValidity('Passwords do not match');
        }
    }

    </script>
</head>

<body>
    <h1>Registration Page</h1>
    <div class="container">
        <div class="left-side">
            <img src="image.jpg" alt="Registration Image">
        </div>
        <div class="right-side">
            <form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" onblur="usernameValidation()" required>

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>

                <label for="password">Password:</label>
                <input type="password" id="password" name="password" onblur="passwordValidation()"  required>

                <label for="confirm_password">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" onChange="confirmPasswordValidation()" required>
                <input type="submit" value="Register" name="signup"  onChange="confirmPasswordValidation()">
            </form>
        </div>
    </div>
</body>

</html>