<?php 
session_start();

require_once __DIR__ ."/db.php";
define("PASSWORD_SALT", "lZzT3ub&taF()qO!epPpzY8t@");

//Tikriname ar duomenys egzistuoja POST masyve
if (isset($_POST['vardas']) && isset($_POST['email']) && isset($_POST['message'])) {
    $vardas = trim($_POST['vardas']);
    $email = trim($_POST['email']);
    $message = trim($_POST['message']);

    //Tikriname ar duomenys nera tusti, t.y. ar buvo kas nors ivesta.
    if(!empty($vardas) && !empty($email) && !empty($message)){

        //Patikriname ar $email yra teisingas el.pasto adresas
        if(filter_var($email, FILTER_VALIDATE_EMAIL)){
            //Siunčiame el. laišką
            /*$to = "mantas.sabaliauskas.vcs@gmail.com" ;
            $subject = "Nauja žinutė ({$vardas})";
            $autorius = "Nuo: {$vardas}, {$email}";
            $zinute = htmlspecialchars($message);
            $additional_headers = [
                "From" => "mantas.sabaliauskas.vcs@gmail.com",
                "Reply-To" => $email
            ];

            mail($to, $subject, $zinute, $additional_headers);*/

            //Saugome duomenų bazėje
            
            $stmt = $mysqli->prepare("INSERT INTO `contacts`(`vardas`, `email`, `message`) VALUES(?, ?, ?)");
            $stmt->bind_param("sss", $vardas, $email, $message);
            $stmt->execute();
            if($mysqli->error){
                echo "<br>";
                echo $mysqli->error;
            } else {
                echo "<h2>Jūsų užklausa išsaugota</h2>";
            }
        } else {
            echo "El.pašto adresas yra neteisingas";
        }
    } else {
        echo "Prašome užpildyti visus laukelius";
    }
}

//Registracijos forma
if(isset($_POST['username']) && isset($_POST['email']) && isset($_POST['password']) && isset($_POST['password2']) && isset($_POST['register'])){
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $password2 = trim($_POST['password2']);

    //Patikrinam ar buvo kas nors ivesta i formos laukelius
    if(!empty($username) && !empty($email) && !empty($password) && !empty($password2)){

        //Patikriname ar $email yra teisingas el.pasto adresas
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            //Patikrinam ar slaptazodziai sutampa
            if($password === $password2){
                
                $stmt = $mysqli->prepare("SELECT `id` FROM `users` WHERE `username` = ? OR `email` = ?");
                $stmt->bind_param("ss", $username, $email);
                $stmt->execute();
                $stmt->bind_result($exists);
                $stmt->fetch();
                
                if (!$exists) {
                
                    $password = hashPassword($password);

                    $stmt = $mysqli->prepare("INSERT INTO `users`(`username`, `email`, `password`) VALUES (?, ?, ?)");
                    $stmt->bind_param("sss", $username, $email, $password);
                    $stmt->execute();

                    if ($mysqli->error) {
                        echo "<br>";
                        echo $mysqli->error;
                    } else {
                        echo "<h2>Registracija sėkminga</h2>";
                    }
                } else {
                    echo "Vartotojo vardas arba el.pasto adresas yra uzimtas";
                }
            } else {
                echo "Slaptazodziai nesutampa";
            }
        } else {
            echo "El.pašto adresas yra neteisingas";
        }
    } else {
        echo "Užpildykite visus formos laukleius";
    }
}

//Patikrinam ar Login formos duomeny yra POST metode
if(isset($_POST['email']) && isset($_POST['password']) && isset($_POST['login'])){
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    //Patikrinam ar buvo kas nors ivesta i formos laukelius
    if (!empty($email) && !empty($password)) {
        $stmt = $mysqli->prepare("SELECT `id`,`username`,`password`,`role` FROM `users` WHERE `email` = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($id, $username, $passwordHash, $role);
        $stmt->fetch();

        $password = hashPassword($password);
        if($password === $passwordHash){
            //Prisijungiam
            $_SESSION['user'] = [
                'id' => $id,
                'username' => $username,
                'email' => $email,
                'role' => $role
            ];
        } else {
            echo "Neteisingi vartotojo duomenys";
        }
    }
}

function hashPassword($password){
    return hash_hmac("sha256", PASSWORD_SALT . $password, PASSWORD_SALT);
}

//Formos duomenu paemimas is duomenu bazes
function paimtiKontaktuFormosDuomenis(){
    global $mysqli;
    $result = $mysqli->query("SELECT * FROM `contacts`");
    return $result;
}

?>