<html>
<head>
    <title>Reset Credentials</title>
    <script src="https://www.google.com/recaptcha/api.js"></script>
    <script>
        function onSubmit(token) {
            document.getElementById("reset_form").submit();
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

        $csrf = hash_hmac('sha256', 'reset.php', $_SESSION['randkey']);

        function regex($input,$pattern){
            $match=preg_match($pattern,$input);
            if (!$match || $match == 0){
                return "1";
            }
            return "0";
        }

        $error = "";

        if(isset($_SESSION['status'])){
            if ($_SERVER["REQUEST_METHOD"] == "POST"){ //if post is triggered
                if(hash_equals($csrf, $_POST["csrf"])){
                    if ($_POST['phonenum'] == ""){
                        $currentusername=$_POST['currentusername'];
                        $currentpassword=$_POST['currentpassword'];
                        $confirmpassword=$_POST['confirmpassword'];
                        $newusername=$_POST['newusername'];
                        $newpassword=$_POST['newpassword'];
                        $cfmnewusername=$_POST['cfmnewusername'];
                        $cfmnewpassword=$_POST['cfmnewpassword'];
    
                        $recaptcha_url = "https://www.google.com/recaptcha/api/siteverify";
                        $recaptcha_secret = "6LexEGkjAAAAAJbhUHHhUkIpke48Ni7rn_h814p-";
                        $recaptcha_response = $_POST['g-recaptcha-response'];
    
                        $recaptcha = file_get_contents($recaptcha_url . '?secret=' . $recaptcha_secret . '&response=' . $recaptcha_response);
                        $recaptchaf = json_decode($recaptcha, true);
    
                        if ($recaptchaf['success'] == 1 && $recaptchaf['score'] >= 0.5 && $recaptchaf['action'] == "reset"){                       
                            if ($currentusername != "" && $currentpassword != "" && $confirmpassword != "" && $newusername != "" && $newpassword != "" && $cfmnewusername != "" && $cfmnewpassword != ""){
                                if ($currentpassword == $confirmpassword && $newusername == $cfmnewusername && $newpassword == $cfmnewpassword){ //check old password matches
                                    if (regex($currentusername, "/^([a-zA-Z0-9]\w{1,10})$/") == "0"){
                                        $query=$con->prepare("Select `iduser`, `password` from user where `username` = ?");
                                        $query->bind_param('s', $currentusername);
                                        $query->execute();
                                        $query->store_result();
                                        $query->bind_result($iduser, $encryptedcurrentpassword);
                                        $query->fetch();
    
                                        if ($iduser == '1'){

                                            $decodedcurrentpassword = base64_decode($encryptedcurrentpassword);
                                            $iv_size = openssl_cipher_iv_length('aes-256-cbc');
                                            $iv = substr($decodedcurrentpassword, 0 ,$iv_size);
                                            $ciphertext_raw = substr($decodedcurrentpassword, $iv_size);
                                            $decryptedcurrentpassword = openssl_decrypt($ciphertext_raw, 'aes-256-cbc', $currentusername . 'secret', $options=0, $iv);

                                            if($decryptedcurrentpassword == $currentpassword){
                                                if (regex($newusername, "/^([a-zA-Z0-9]\w{1,10})$/") == "0" && regex($newpassword, "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()])[A-Za-z\d!@#$%^&*()]{8,}$/") == "0"){
                                                    $iv_size = openssl_cipher_iv_length('aes-256-cbc');
                                                    $iv = openssl_random_pseudo_bytes($iv_size);
                                                    $encrypt = openssl_encrypt($newpassword, 'aes-256-cbc', $newusername . 'secret', $option=0, $iv);
                                                    $encrypttext = $iv . $encrypt;
                                                    $encryptedpassword=base64_encode($encrypttext);
                                                    $query=$con->prepare("update user set `username` = ?, `password` = ? where `iduser` = ?");
                                                    $query->bind_param('ssi', $newusername, $encryptedpassword, $iduser);
                                                    
                                                    if ($query->execute()){
                                                        session_destroy();
                                                        header("Location:index.php");
                                                    }
                                                }
                                                else{
                                                    $error = "*username must only contain alphabets and numbers, password must contain 1 uppercase, 1 lowercase, 1 number and 1 special symbol";
                                                }
                                            }
                                            else{
                                                $error = "* Username or Password is wrong";
                                            }
                                        }
                                        else{
                                            $error = "* Username or Password is wrong";
                                        }
                                    }
                                    else{
                                        $error = "wrong credentials";
                                    }
                                }
                                else{
                                    $error = "* Confirmation doesn't match";
                                }
                            }
                            else{
                                $error = "* Every field needs to be filled";
                            }
                        }
                        else{
                            $error = "* failed captcha";
                        }
                    }
                    else{
                        $error = "* bot detected";
                    }
                }
                else{
                    $error = "* something sus going on with the csrf";
                }
            }
        }
        else{
            header("Location:index.php");
        }
    ?>
    <a href="main.php">Back</a>
    <h3>Reset Credentials</h3>
    <form id="reset_form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="POST">
        <table border="1">
            <tr>
                <td>Current Username:</td>
                <td>
                    <input type="text" name="currentusername">
                </td>
            </tr>
            <tr>
                <td>Current Password:</td>
                <td>
                    <input type="password" name="currentpassword">
                </td>
            </tr>
            <tr>
                <td>Confirm Current Password:</td>
                <td>
                    <input type="password" name="confirmpassword">
                </td>
            </tr>
            <tr>
                <td>New Username:</td>
                <td>
                    <input type="text" name="newusername">
                </td>
            </tr>
            <tr>
                <td>Confirm New Username:</td>
                <td>
                    <input type="text" name="cfmnewusername">
                </td>
            </tr>
            <tr>
                <td>New Password:</td>
                <td>
                    <input type="password" name="newpassword">
                </td>
            </tr>
            <tr>
                <td>Confirm New Password:</td>
                <td>
                    <input type="password" name="cfmnewpassword">
                </td>
            </tr>
        </table>
        <input type="text" name="phonenum" class="matrix">
        <input type="hidden" name="csrf" value="<?php echo $csrf?>">
        <span class="error"><?php echo $error; ?></span>
        <button class="g-recaptcha" data-sitekey="6LexEGkjAAAAABlHHrJFmlRPmNjEwVX9uXbb6Ev5" data-callback='onSubmit' data-action='reset'>Submit</button>
    </form>
    </body>
</html>