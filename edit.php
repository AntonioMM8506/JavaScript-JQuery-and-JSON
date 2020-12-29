<?php
//Database connection
require_once "pdo.php";
session_start();

$host = $_SERVER['HTTP_HOST'];
$ruta = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
$url = "http://$host$ruta"; 

if (!isset($_SESSION["user_id"])) {
    die("Not logged in");
}

if (isset($_POST["cancel"])) {
    header("Location: $url/index.php");
    die();
}

if (isset($_POST["save"])) {
    if (strlen($_POST["first_name"]) < 1
        || strlen($_POST["last_name"]) < 1
        || strlen($_POST["email"]) < 1
        || strlen($_POST["headline"]) < 1
        || strlen($_POST["summary"]) < 1
    ) {
        $_SESSION["error"] = "All fields are required";
        header("Location: $url/edit.php?profile_id=" . $_POST["profile_id"]);
        die();
    }

    if (strpos($_POST["email"], "@") === false) {
        $_SESSION["error"] = "Email address must contain @";
        header("Location: $url/edit.php?profile_id=" . $_POST["profile_id"]);
        die();
    }
    $sql 
        = "UPDATE profile
        SET
        first_name = :fn,
        last_name = :ln,
        email = :em,
        headline = :he,
        summary = :su
        WHERE
        profile_id = :profile_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(
        array(
        ':profile_id' => $_POST['profile_id'],
        ':fn' => $_POST['first_name'],
        ':ln' => $_POST['last_name'],
        ':em' => $_POST['email'],
        ':he' => $_POST['headline'],
        ':su' => $_POST['summary'])
    );
    $_SESSION["success"] = "Profile updated";
    header("Location: $url/index.php");
    die();
}

if (! isset($_GET['profile_id']) ) {
    $_SESSION['error'] = "Missing profile_id";
    header("Location: $url/index.php");
    die();
}

$sql = "SELECT * FROM profile WHERE profile_id = :profile_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(array(":profile_id" => $_GET['profile_id']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row === false ) {
    $_SESSION['error'] = 'Bad value for profile_id';
    header('Location: index.php');
    die();
}

$fn = htmlentities($row["first_name"]);
$ln = htmlentities($row["last_name"]);
$em = htmlentities($row["email"]);
$he = htmlentities($row["headline"]);
$su = htmlentities($row["summary"]);
$profile_id = $row["profile_id"];

//End of php
?>


<!DOCTYPE html>
<html>
<head>
    <title>Antonio Manilla Maldonado</title>
</head>
<body style="font-family: Helvetica">
    <h1>Editing Profile for <?php echo htmlentities($_SESSION["name"]) ?></h1>
    
    <?php
    //php ---------------------------------------------------------------
    if (isset($_SESSION["error"])) {
        echo('<p style="color: red;">' . $_SESSION["error"]);
        unset($_SESSION["error"]);
    }
    // End of php -------------------------------------------------------
    ?>


    <form method="post">
        <label>First Name:</label>
        <input type="text" name="first_name" value="<?php echo $fn ?>">
        <br>
        <label>Last Name:</label>
        <input type="text" name="last_name" value="<?php echo $ln ?>">
        <br>
        <label>Email:</label>
        <input type="text" name="email" value="<?php echo $em ?>">
        <br>
        <label>Headline:</label>
        <br>
        <input type="text" name="headline" value="<?php echo $he ?>">
        <br>
        <label>Summary:</label>
        <br>
        <textarea
            name="summary"
            cols="100"
            rows="20"
            style="resize: none;"
        >
        <?php echo $su ?>
        </textarea>
        <br>
        <input type="hidden" name="profile_id" value="<?php echo $profile_id ?>">
        <input type="submit" name="save" value="Save">
        <input type="submit" name="cancel" value="Cancel">
    </form>
</body>
</html>