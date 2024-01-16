<?php

/*
    include - will include the file everytime you run the program.
    include_once - will include the file once only.

    require - will require or include the file, if not found it will stop the script
    require_once - will require once or include once the file, if not found it will stop the script
*/ 

require_once "Database.php";

class User extends Database
{
    public function store($request)
    {
        $first_name = $request['first_name'];
        $last_name  = $request['last_name'];
        $username   = $request['username'];
        $password   = $request['password'];

        $password = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (first_name, last_name, username, password)
                VALUES ('$first_name', '$last_name', '$username', '$password')";

        if ($this->conn->query($sql)){
            header('location: ../views');   // go to index.php or the login page
            exit;                           // same as die
        } else {
            die('Error creating the user: ' . $this->conn->error);
        }
    }


    public function login($request)
    {
        $username = $request['username'];
        $password = $request['password'];

        $sql = "SELECT * FROM users WHERE username = '$username'";

        $result = $this->conn->query($sql);

        # Check the username
        if ($result->num_rows == 1){
            $user = $result->fetch_assoc();
            // $user = ['id' => 1, 'first_name' => 'Rene', 'last_name' => 'Okamura', 'username' => 'rene'];

            // $user['id'] get value 1
            // $user['first_name'] get value 'Rene'
            // $user['last_name'] get value 'Okamura'

            if (password_verify($password, $user['password'])){
                # Create session variables for future use.
                session_start();
                $_SESSION['id']         = $user['id'];
                $_SESSION['username']   = $user['username'];
                $_SESSION['full_name']  = $user['first_name'] . " " . $user['last_name']; 

                header('location: ../views/dashboard.php');
                exit;
            } else {
                die('Password is incorrect');
            }
        } else {
            die('Username not found.');
        }
    }


    public function logout()
    {
        session_start();
        session_unset();
        session_destroy();

        header('location: ../views');
        exit;
    }


    public function getAllUsers()
    {
        $sql = "SELECT id, first_name, last_name, username, photo FROM users";

        if ($result = $this->conn->query($sql)){
            return $result;
        } else {
            die('Error retrieving all users: ' . $this->conn->error);
        }
    }


    public function getUser()
    {
        $id = $_SESSION['id'];

        $sql = "SELECT first_name, last_name, username, photo FROM users WHERE id = $id";

        if ($result = $this->conn->query($sql)){
            return $result->fetch_assoc();
            // ['id' => 1, 'first_name' => 'Rene', 'last_name' => 'Okamura', 'username' => 'rene', 'photo' => NULL];
        } else {
            die('Error retrieving the user: ' . $this->conn->error);
        }
    }


    public function update($request, $files)
    {
        session_start();
        $id         = $_SESSION['id'];
        $first_name = $request['first_name'];
        $last_name  = $request['last_name'];
        $username   = $request['username'];
        $photo      = $files['photo']['name']; // holds the name of the photo
        $tmp_photo  = $files['photo']['tmp_name']; // holds the file from the temporary storage
        // ['photo'] is the name of the form input file
        // ['name'] is the name of the file
        // ['tmp_name'] is the temporary storage of the file

        $sql = "UPDATE users SET first_name = '$first_name', last_name = '$last_name', username = '$username' WHERE id = $id";

        if ($this->conn->query($sql)){
            $_SESSION['username']   = $username;
            $_SESSION['full_name']  = "$first_name $last_name";

            # If there is an uploaded photo, save it to the db and save the file to images folder.
            if ($photo){
                $sql = "UPDATE users SET photo = '$photo' WHERE id = $id";
                $destination = "../assets/images/$photo";

                // Save the image name to db
                if ($this->conn->query($sql)){
                    // Save the file to images folder
                    if (move_uploaded_file($tmp_photo, $destination)){
                        header('location: ../views/dashboard.php');
                        exit;
                    } else {
                        die('Error moving the photo.');
                    }
                } else {
                    die('Error uploading photo: ' . $this->conn->error);
                }
            }
            header('location: ../views/dashboard.php');
            exit;
        } else {
            die('Error updating your account: ' . $this->conn->error);
        }
    }


    public function delete()
    {
        session_start();
        $id = $_SESSION['id'];

        $sql = "DELETE FROM users WHERE id = $id";

        if ($this->conn->query($sql)){
            $this->logout();
        } else {
            die('Error deleting your account: ' . $this->conn->error);
        }
    }


}

?>