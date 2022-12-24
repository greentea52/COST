<html>
<head>
    <title>database</title>
</head>
<body>
<a href="main.php">Back</a>
<?php
session_start();
if(isset($_SESSION['status'])){
    $sensor = $_POST['sensor'];

    $con = mysqli_connect("historian.cssgbcoo32nu.us-east-1.rds.amazonaws.com","admin","Password123","histdb");
    if (!$con){
        die('Could not connect: ' . mysqli_connect_errno());
    }

    if ($sensor != 'aircon'){
        
        echo "<h3>" . $sensor . "</h3>";
            $query=$con->prepare("SELECT * FROM `" . $sensor ."` ORDER BY id" . $sensor . " DESC");
            $query->execute();
            $query->store_result();
            $query->bind_result($id, $status, $time);
    
            if($query->num_rows === 0){
                echo "empty :<";
            }
            else{
                echo "<table border='1'>";
                echo "<tr>";
                echo "<th>Status</th>";
                echo "<th>Time</th>";
                echo "</tr>";                   
    
                while ($query->fetch()){
                    echo "<tr>";
                    echo "<td>" . $status . "</td>";
                    echo "<td>" . $time . "</td>";
                    echo "</tr>";
                }
    
                echo "</table>";
            }
    }
    else{
        echo "<h3>" . $sensor . "</h3>";
        $query=$con->prepare("SELECT * FROM `" . $sensor ."` ORDER BY id" . $sensor . " DESC");
        $query->execute();
        $query->store_result();
        $query->bind_result($id, $status, $detail, $time);
    
        if($query->num_rows === 0){
            echo "empty :<";
        }
        else{
            echo "<table border='1'>";
            echo "<tr>";
            echo "<th>Status</th>";
            echo "<th>Details</th>";
            echo "<th>Time</th>";
            echo "</tr>";                   
    
            while ($query->fetch()){
                echo "<tr>";
                echo "<td>" . $status . "</td>";
                echo "<td>" . $detail . " Degrees</td>";
                echo "<td>" . $time . "</td>";
                echo "</tr>";
            }
    
            echo "</table>";
        }
    }
}
else{
    header("Location:index.php");
}
?>
</body>
</html>