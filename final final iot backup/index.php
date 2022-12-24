<html>
<head>
    <title>Login Page</title>
    <script src="https://www.google.com/recaptcha/api.js"></script>
    <script>
        function onSubmit(token) {
            document.getElementById("login_form").submit();
        }
    </script>  
    <style>
        .matrix{
            display:none;
        }
        .error{
            color: red;
        }
    </style>
</head>
<body>
    <?php
        $con = mysqli_connect("historian.cssgbcoo32nu.us-east-1.rds.amazonaws.com","admin","Password123","histdb");
        if (!$con){
            die('Could not connect: ' . mysqli_connect_errno());
        }

        session_start();
        if (empty($_SESSION['randkey'])){
            $_SESSION['randkey'] = bin2hex(random_bytes(32));
        }

        $csrf =hash_hmac('sha256', 'index.php', $_SESSION['randkey']);

        function regex($input,$pattern){
            $match=preg_match($pattern,$input);
            if(!$match || $match == 0){
                return "1";
            }
            return "0";
        }

        $error = "";

        if (isset($_SESSION['status'])){
            header("Location:main.php");
        }
        else{
            if ($_SERVER["REQUEST_METHOD"] == "POST"){
                if(hash_equals($csrf, $_POST["csrf"])){
                    if ($_POST['phonenum'] == ""){
                        $username=$_POST['username'];
                        $password=$_POST['password'];
    
                        $recaptcha_url = "https://www.google.com/recaptcha/api/siteverify";
                        $recaptcha_secret = "6LexEGkjAAAAAJbhUHHhUkIpke48Ni7rn_h814p-";
                        $recaptcha_response = $_POST['g-recaptcha-response'];
    
                        $recaptcha = file_get_contents($recaptcha_url . '?secret=' . $recaptcha_secret . '&response=' . $recaptcha_response);
                        $recaptchaf = json_decode($recaptcha, true);
    
                        if ($recaptchaf['success'] == 1 && $recaptchaf['score'] >= 0.5 && $recaptchaf['action'] == "login"){
                            if ($username != "" && $password != ""){
                                if (regex($username, '/^([a-zA-Z0-9]\w{1,10})$/') == "0" && regex($password,'/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()])[A-Za-z\d!@#$%^&*()]{8,}$/') == "0"){
                                    $query=$con->prepare("Select `iduser`, `password` from user where `username` = ?");
                                    $query->bind_param('s', $username);
                                    $query->execute();
                                    $query->store_result();
                                    $query->bind_result($iduser, $encryptedcurrentpassword);
                                    $query->fetch();
    
                                    if ($iduser == '1'){
                                        $decodedcurrentpassword = base64_decode($encryptedcurrentpassword);
                                        $iv_size = openssl_cipher_iv_length('aes-256-cbc');
                                        $iv = substr($decodedcurrentpassword, 0 ,$iv_size);
                                        $ciphertext_raw = substr($decodedcurrentpassword, $iv_size);
                                        $decryptedcurrentpassword = openssl_decrypt($ciphertext_raw, 'aes-256-cbc', $username . 'secret', $options=0, $iv);

                                        if ($decryptedcurrentpassword == $password){
                                            $_SESSION['status'] = "loggedin";
                                            header("Location:main.php");
                                        }
                                        else{
                                            $error = "login failed";
                                        }
                                    }
                                    else{
                                        $error = "login failed";
                                    }   
                                }   
                                else{
                                    $error = "login failed";
                                }
                            }
                            else {
                                $error = "login failed";
                            }
                        }   
                        else{
                            $error = "login failed";
                        }
                    }
                    else {
                        $error = "login failed";
                    }   
                }
                else{
                    $error = "login failed";
                }
            }
        }
    ?>
    <h3>login</h3>
    <form id= "login_form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="POST">
        <table border="1">
            <tr>
                <td>Username:</td>
                <td>
                    <input type="text" name="username">
                </td>
            </tr>
            <tr>
                <td>Password:</td>
                <td>
                    <input type="password" name="password">
                </td>
            </tr>
        </table>
        <input type="text" name="phonenum" class="matrix">
        <input type="hidden" name="csrf" value="<?php echo $csrf?>">
        <span class= "error"><?php echo $error; ?></span>
        <button class="g-recaptcha" data-sitekey="6LexEGkjAAAAABlHHrJFmlRPmNjEwVX9uXbb6Ev5" data-callback='onSubmit' data-action='login'>Login</button>
    </form>
</body>
</html>