<?php
//Database connection
require_once "pdo.php";
session_start();

//Validates the data of Position
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
}

$host = $_SERVER['HTTP_HOST'];
$ruta = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
$url = "http://$host$ruta";

//If there's no current session, it throws an error.
if (!isset($_SESSION["user_id"])) {
    die("ACCESS DENIED");
}

//If the user cancels the action, they are send back to the index.php
if (isset($_POST["cancel"])) {
    header("Location: $url/index.php");
    die();
}

//When save is post, updates the data according to the selected
//Position id. 
if (isset($_POST["save"])) {
    $position_validate = validatePos();
    if ($position_validate !== true) {
        $_SESSION["error"] = $position_validate;
        header("Location: $url/edit.php?profile_id=" . $_POST["profile_id"]);
        die();
    }

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

    $stmt = $pdo->prepare('DELETE FROM position WHERE profile_id=:pid');
    $stmt->execute(array( ':pid' => $_REQUEST['profile_id']));

    $rank = 1;
    for ($i=1; $i<=9; $i++) {
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
            ':pid' => $_REQUEST["profile_id"],
            ':rank' => $rank,
            ':year' => $year,
            ':desc' => $desc)
        );
        $rank++;
    }

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
$profile_id = $_GET["profile_id"];

$sql = "SELECT * FROM position WHERE profile_id = :profile_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(array(":profile_id" => $_GET['profile_id']));
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <h1>Editing Profile for <?php echo htmlentities($_SESSION["name"]) ?></h1>
    <?php
    if (isset($_SESSION["error"])) {
        echo('<p style="color: red;">' . $_SESSION["error"]);
        unset($_SESSION["error"]);
    }
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
        <label>Position:</label>
        <input type="button" value="+" id="plus_button">
        <br>
        <?php
        if ($rows !== false) {
            echo '<div id="position_fields">' . "\n";
        }
        foreach ($rows as $row) {
            $rank = $row["rank"];
            echo '<div id="position' . $rank . '">' . "\n";
            echo 
                '<p>Year: <input type="text" name="year' . $rank .
                '" value="' . $row["year"] . '">' . "\n";
            echo 
                '<input type="button" value="-" onclick="$(\'#position' .$rank .
                '\').remove(); fix_position(); return false;">' . "\n" . '</p>';
            echo 
                '<textarea name="desc' . $rank . '" rows="8" cols="80">' .
                $row["description"] . '</textarea>' . "\n";
            echo '</div>' . "\n";
        }  
            echo '</div>';
        ?>
        <input type="hidden" name="profile_id" value="<?php echo $profile_id ?>">
        <input type="submit" name="save" value="Save">
        <input type="submit" name="cancel" value="Cancel">
    </form>
    <script type="text/javascript" src="js/position.js"></script>
</body>
</html>