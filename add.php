<?php
//Database Connection
require_once "pdo.php";
session_start();

//Validates the year
function validatePos()
{
    for ($i=1; $i<=9; $i++) {
        if (! isset($_POST['year'.$i]) ) { 
            continue;
        }
        if (! isset($_POST['desc'.$i]) ) { 
            continue;
        }

        $year = $_POST['year'.$i];
        $desc = $_POST['desc'.$i];

        if (strlen($year) == 0 || strlen($desc) == 0 ) {
            return "All fields are required";
        }

        if (! is_numeric($year) ) {
            return "Position year must be numeric";
        }
    }
    return true;
}//End of ValidatePos

$host = $_SERVER['HTTP_HOST'];
$ruta = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
$url = "http://$host$ruta";

//If there's no current session active, the access is denied
if (!isset($_SESSION["user_id"])) {
    die("ACCESS DENIED");
}

//If the action is canceled, return to de index.php page
if (isset($_POST["cancel"])) {
    header("Location: $url/index.php");
    die();
}

if (isset($_POST["add"])) {
    //if validatePos returns true, then the process can continue
    //otherwise it will die.  
    $position_validate = validatePos();
    if ($position_validate !== true) {
        $_SESSION["error"] = $position_validate;
        header("Location: $url/add.php");
        die();
    }

    //All the fields need to be fulfilled otherwise it will throw an error. 
    if (strlen($_POST["first_name"]) < 1
        || strlen($_POST["last_name"]) < 1
        || strlen($_POST["email"]) < 1
        || strlen($_POST["headline"]) < 1
        || strlen($_POST["summary"]) < 1
    ) {
        $_SESSION["error"] = "All fields are required";
        header("Location: $url/add.php");
        die();
    }

    //Email validation, if it not caontains the character @, it will take it
    //as an error. 
    if (strpos($_POST["email"], "@") === false) {
        $_SESSION["error"] = "Email address must contain @";
        header("Location: $url/add.php");
        die();
    }
    $stmt = $pdo->prepare(
        'INSERT INTO profile
        (user_id, first_name, last_name, email, headline, summary)
        VALUES ( :uid, :fn, :ln, :em, :he, :su)'
    );

    //If everything is succesfull, it will add the corresponding value to the 
    //table in the database. 
    $stmt->execute(
        array(
        ':uid' => $_SESSION['user_id'],
        ':fn' => $_POST['first_name'],
        ':ln' => $_POST['last_name'],
        ':em' => $_POST['email'],
        ':he' => $_POST['headline'],
        ':su' => $_POST['summary'])
    );

    $profile_id = $pdo->lastInsertId();
    $rank = 1;
    for ($i = 1; $i <= 9; $i++) {
        if (! isset($_POST['year'.$i]) ) {
            continue;
        }
        if (! isset($_POST['desc'.$i]) ) {
            continue;
        }
        $year = $_POST["year" . $i];
        $desc = $_POST["desc" . $i];
        $stmt = $pdo->prepare(
            'INSERT INTO position
            (profile_id, rank, year, description)
            VALUES ( :pid, :rank, :year, :desc)'
        );
        $stmt->execute(
            array(
            ':pid' => $profile_id,
            ':rank' => $rank,
            ':year' => $year,
            ':desc' => $desc)
        );
        $rank++;
    }
    
    $_SESSION["success"] = "Profile added";
    header("Location: $url/index.php");
    die();
}//End of "add"

//End of php
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.2.1.js" integrity="sha256-DZAnKJ/6XZ9si04Hgrsxu/8s717jcIzLy3oi35EouyE=" crossorigin="anonymous"></script>
    <title>Antonio Manilla Maldonado</title>
</head>
<body">
    <h1>Adding Profile for <?php echo htmlentities($_SESSION["name"]) ?></h1>
    <?php
    if (isset($_SESSION["error"])) {
        echo('<p style="color: red;">' . $_SESSION["error"]);
        unset($_SESSION["error"]);
    }
    ?>
    <form method="post">
        <label>First Name:</label>
        <input type="text" name="first_name">
        <br>
        <label>Last Name:</label>
        <input type="text" name="last_name">
        <br>
        <label>Email:</label>
        <input type="text" name="email">
        <br>
        <label>Headline:</label>
        <br>
        <input type="text" name="headline">
        <br>
        <label>Summary:</label>
        <br>
        <textarea
            name="summary"
            cols="100"
            rows="20"
            style="resize: none;"
        >
        </textarea>
        <br>
        <label>Position:</label>
        <input type="button" value="+" id="plus_button">
        <br>
        <div id="position_fields"></div>
        <input type="submit" name="add" value="Add">
        <input type="submit" name="cancel" value="Cancel">
    </form>
    <script type="text/javascript" src="js/position.js"></script>
</body>
</html>

