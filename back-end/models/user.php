<?php
class User
{
    private $SAVE_QUERY =
    'INSERT INTO user (username, email, password, first_name, last_name, fn, speciality, in_alumni)' .
        ' VALUES (:username, :email, :password, :firstName, :lastName, :fn, :speciality, :inAlumni)';

    private $CHECK_QUERY = 'SELECT id FROM user WHERE username = :username or email = :email or fn = :fn';

    private static $GET_BY_ID_QUERY = 'SELECT * FROM user WHERE id = :id';
    private static $GET_BY_USERNAME_QUERY = 'SELECT * FROM user WHERE username = :username';
    private $id;
    private $username;
    private $email;
    private $password;
    private $firstName;
    private $lastName;
    private $fn;
    private $speciality;
    private $inAlumni;
    private $dateCreated;

    public function __construct(
        $username,
        $email,
        $password,
        $firstName,
        $lastName,
        $fn,
        $speciality,
        $inAlumni,
        $id = null,
        $dateCreated = null
    ) {
        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->fn = $fn;
        $this->speciality = $speciality;
        $this->inAlumni = $inAlumni;
        $this->dateCreated = $dateCreated;
    }

    public function toJson(
        $detailInformation = false,
        $sensitiveInformation = false
    ) {
        $result = [
            'username' => $this->username,
            'email' => $this->email,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'fn' => $this->fn,
            'speciality' => $this->speciality,
            'inAlumni' => $this->inAlumni,
        ];
        if ($detailInformation) {
            $result['id'] = $this->id;
            $result['dateCreated'] = $this->dateCreated;
        }
        if ($sensitiveInformation) {
            $result['password'] = $this->password;
        }

        return $result;
    }

    // Getters
    public function getId()
    {
        return $this->id;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function getLastName()
    {
        return $this->lastName;
    }

    public function getFn()
    {
        return $this->fn;
    }

    public function getSpeciality()
    {
        return $this->speciality;
    }

    public function getInAlumni()
    {
        return $this->inAlumni;
    }

    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    // Setters
    public function setId($id)
    {
        $this->id = $id;
    }

    public function setUsername($username)
    {
        $this->username = $username;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    public function setFn($fn)
    {
        $this->fn = $fn;
    }

    public function setSpeciality($speciality)
    {
        $this->speciality = $speciality;
    }

    public function setInAlumni($inAlumni)
    {
        $this->inAlumni = $inAlumni;
    }

    public function setDateCreated($dateCreated)
    {
        $this->dateCreated = $dateCreated;
    }

    // Database methods
    public function save($connection)
    {
        $statement = $connection->prepare($this->SAVE_QUERY);
        try {
            $statement->execute($this->toJson(false, true));
            return true;
        } catch (PDOException $e) {
            echo $e->getMessage();
            return false;
        }
    }

    public static function getById($connection, $id)
    {
        try {
            $statement = $connection->prepare(User::$GET_BY_ID_QUERY);
            $statement->execute(['id' => $id]);

            $userData = $statement->fetch(PDO::FETCH_OBJ);
            if (!$userData) {
                return null;
            }

            return new User(
                $userData->username,
                $userData->email,
                $userData->password,
                $userData->first_name,
                $userData->last_name,
                $userData->fn,
                $userData->speciality,
                $userData->in_alumni,
                $userData->id,
                $userData->date_created
            );
        } catch (PDOException $err) {
            echo $err->getMessage();
        }

        return null;
    }

    public static function getByUsername($connection, $username)
    {
        $statement = $connection->prepare(User::$GET_BY_USERNAME_QUERY);
        $statement->execute(['username' => $username]);
        $userData = $statement->fetch(PDO::FETCH_OBJ);

        if (!$userData) {
            return null;
        }

        return new User(
            $userData->username,
            $userData->email,
            $userData->password,
            $userData->first_name,
            $userData->last_name,
            $userData->fn,
            $userData->speciality,
            $userData->in_alumni,
            $userData->id,
            $userData->date_created
        );
    }

    public function userExists($connection, $hasId = false)
    {
        $statement = $connection->prepare($this->CHECK_QUERY);
        $statement->execute([
            'username' => $this->getUsername(),
            'email' => $this->getEmail(),
            'fn' => $this->getFn(),
        ]);
        return $hasId
            ? $statement->rowCount() > 0 && $statement->fetch(PDO::FETCH_OBJ)->id != $this->getId()
            : $statement->rowCount() > 0;
    }
}
