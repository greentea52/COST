<html>
    <head>
        <title>Main</title>
    </head>
    <body>
        <?php 
        session_start();
        if(isset($_SESSION['status'])){

            function table($sensor){
                $con = mysqli_connect("historian.cssgbcoo32nu.us-east-1.rds.amazonaws.com","admin","Password123","histdb");
                if (!$con){
                    die('Could not connect: ' . mysqli_connect_errno());
                }

                echo "<h3>" . $sensor . "</h3>";
                $query=$con->prepare("SELECT * FROM `" . $sensor ."` ORDER BY id" . $sensor . " DESC LIMIT 5");
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
                    echo "<form action='database.php' method='POST'><input type='hidden' name='sensor' value ='". $sensor . "'><input type='submit' value='Show full table'></form>";
                }
            }

            function aircon($sensor){
                $con = mysqli_connect("historian.cssgbcoo32nu.us-east-1.rds.amazonaws.com","admin","Password123","histdb");
                if (!$con){
                    die('Could not connect: ' . mysqli_connect_errno());
                }

                echo "<h3>" . $sensor . "</h3>";
                $query=$con->prepare("SELECT * FROM `" . $sensor ."` ORDER BY id" . $sensor . " DESC LIMIT 5");
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
                    echo "<form action='database.php' method='POST'><input type='hidden' name='sensor' value ='". $sensor . "'><input type='submit' value='Show full table'></form>";
                }
            }           

            aircon("aircon");
            table("curtain");
            table("door");
            table("doorlock");
            table("light");
            table("windows");

        }
        else{
            header("Location:index.php");
        }
        ?>
        <a href="reset.php">Reset Credentials</a>
        <a href="logout.php">Logout</a>
    </body>
</html>