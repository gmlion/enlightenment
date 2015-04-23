<?php
error_reporting(2048);
function dbConnection() {
    $mysqli = mysqli_connect("localhost", "EnlightenmUqu91", "@[NMJC_/i|pS", "Enlightenment");
    if (mysqli_connect_errno($mysqli)) {
        /*echo "Failed to connect to MySQL: " . mysqli_connect_error();*/
        echo json_encode(false);
    } else return $mysqli;
}


switch($_REQUEST['group']) {
    case ('tea'):
        $connection = dbConnection();
        $query = $connection->query('SELECT presences FROM activities WHERE name="tea"');
        $row = $query->fetch_assoc();
        $presences = $row['presences'];
        $presences--;
        $statement = $connection->prepare('UPDATE activities SET presences=? WHERE name="tea"');
        $statement->bind_param('i', $presences);
        $statement->execute();
        echo json_encode($presences);
        break;
    default:
        break;
}

?>