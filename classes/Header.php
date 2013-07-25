<?php

class Header {

	private $title;
	private $db;

	function __construct($title) {
		$this->db = new Database();
		$this->title = $title;
	}

	function draw() {

	?>

<!DOCTYPE html>
	<head>
		<meta charset="utf-8">
		<title><?php echo $this->title; ?></title>
		<link rel="stylesheet" href="css/style.css">
	</head>
	<body>

	<a href="index.php">Home</a> | 
	<?php if (isset($_SESSION['username'])) { ?>
	<a href="profile.php">Profile</a> | 
	<a href="logout.php">Logout [<?php echo $_SESSION['username']; ?>]</a>
	<?php } else { ?>
	<a href="login.php">Login</a>
	<?php } ?>

	<?php

	}

	function go($location = "index.php") {
    	if (strtolower($location) == "default" || strtolower($location) == "index.php") {
    		header("Location: index.php");
    	} else {
    		header("Location: " . $location);
    	}
    }
}
?>