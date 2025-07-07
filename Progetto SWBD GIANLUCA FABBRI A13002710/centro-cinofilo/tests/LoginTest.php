<?php
use PHPUnit\Framework\TestCase;

class LoginTest extends TestCase
{
    private $conn;

    protected function setUp(): void
    {
        putenv('APP_ENV=testing');
        $this->conn = require __DIR__ . '/../config.php';
    }

    public function testLoginUtenteValido()
    {
        $email = 'gianluca.fabbri@utente.it';
        $password = 'gianluca';

        $stmt = $this->conn->prepare("SELECT * FROM utenti WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        $this->assertEquals(1, $result->num_rows, "L'utente non è stato trovato.");

        $utente = $result->fetch_assoc();
        $this->assertTrue(password_verify($password, $utente['password']), "La password non è corretta.");
    }

    public function testLoginEmailInesistente()
    {
        $email = 'email.fake@nonesiste.it';

        $stmt = $this->conn->prepare("SELECT * FROM utenti WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        $this->assertEquals(0, $result->num_rows, "L'email inesistente ha restituito un utente.");
    }

    public function testLoginPasswordErrata()
    {
        $email = 'gianluca.fabbri@utente.it';
        $passwordErrata = 'acaso';

        $stmt = $this->conn->prepare("SELECT * FROM utenti WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        $this->assertEquals(1, $result->num_rows, "L'utente non è stato trovato.");
        $utente = $result->fetch_assoc();

        $this->assertFalse(password_verify($passwordErrata, $utente['password']), "La password errata è stata accettata.");
    }
}
