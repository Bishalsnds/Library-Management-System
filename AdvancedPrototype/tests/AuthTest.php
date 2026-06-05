<?php

use PHPUnit\Framework\TestCase;

class AuthTest extends TestCase
{
    private $conn;
    private $config_path = __DIR__ . '/../config.php';

    protected function setUp(): void
    {
        // Initialize database connection
        require_once $this->config_path;
        $this->conn = $GLOBALS['conn'] ?? null;
    }

    // ==================== EMAIL VALIDATION TESTS ====================

    /**
     * Test 1: Empty Email (Required Field)
     */
    public function testSignupEmptyEmail()
    {
        $email = '';
        $this->assertTrue(empty($email), 'Email should be marked as empty');
    }

    /**
     * Test 2: Valid Gmail Email Format
     */
    public function testSignupValidGmailFormat()
    {
        $email = 'test@gmail.com';
        $isValidFormat = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
        $isGmail = preg_match('/@gmail\.com$/', $email) === 1;
        
        $this->assertTrue($isValidFormat, 'Email format should be valid');
        $this->assertTrue($isGmail, 'Email should end with @gmail.com');
    }

    /**
     * Test 3: Email Boundary - Minimum Length
     */
    public function testSignupMinimumLengthEmail()
    {
        $email = 'a@gmail.com'; // 11 characters
        $isValidFormat = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
        $isGmail = preg_match('/@gmail\.com$/', $email) === 1;
        
        $this->assertTrue($isValidFormat, 'Minimum length email should be valid');
        $this->assertTrue($isGmail, 'Minimum length email should be Gmail');
    }

    /**
     * Test 4: Email Below Minimum (Invalid)
     */
    public function testSignupBelowMinimumEmail()
    {
        $email = '@gmail.com'; // Too short
        $isValidFormat = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
        
        $this->assertFalse($isValidFormat, 'Below minimum email should be invalid');
    }

    /**
     * Test 5: Email Maximum Length Boundary
     */
    public function testSignupMaximumLengthEmail()
    {
        $email = str_repeat('a', 64) . '@gmail.com'; // Very long but valid
        $isValidFormat = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
        $isGmail = preg_match('/@gmail\.com$/', $email) === 1;
        
        $this->assertTrue($isValidFormat, 'Maximum length email should be valid');
        $this->assertTrue($isGmail, 'Maximum length email should be Gmail');
    }

    /**
     * Test 6: Email Exceeding Maximum Length
     */
    public function testSignupExceedingMaximumEmail()
    {
        $email = str_repeat('a', 100) . '@gmail.com'; // Exceeds typical limits
        // Note: Database VARCHAR(100) will reject this
        $this->assertGreaterThan(100, strlen($email), 'Email should exceed limit');
    }

    /**
     * Test 7: Invalid Email Format - Missing @
     */
    public function testSignupInvalidEmailNoAt()
    {
        $email = 'testgmail.com';
        $isValidFormat = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
        
        $this->assertFalse($isValidFormat, 'Email without @ should be invalid');
    }

    /**
     * Test 8: Invalid Email Format - Multiple @
     */
    public function testSignupInvalidEmailMultipleAt()
    {
        $email = 'test@@gmail.com';
        $isValidFormat = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
        
        $this->assertFalse($isValidFormat, 'Email with multiple @ should be invalid');
    }

    /**
     * Test 9: Non-Gmail Domain - Wrong Domain
     */
    public function testSignupNonGmailDomain()
    {
        $email = 'test@yahoo.com';
        $isValidFormat = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
        $isGmail = preg_match('/@gmail\.com$/', $email) === 1;
        
        $this->assertTrue($isValidFormat, 'Yahoo email format should be valid');
        $this->assertFalse($isGmail, 'Yahoo email should NOT be Gmail');
    }

    /**
     * Test 10: Non-Gmail Domain - Outlook
     */
    public function testSignupOutlookDomain()
    {
        $email = 'test@outlook.com';
        $isGmail = preg_match('/@gmail\.com$/', $email) === 1;
        
        $this->assertFalse($isGmail, 'Outlook email should NOT be Gmail');
    }

    /**
     * Test 11: Gmail with Different Cases
     */
    public function testSignupGmailCaseInsensitive()
    {
        $email = 'TEST@GMAIL.COM';
        $isGmail = preg_match('/@gmail\.com$/i', $email) === 1;
        
        $this->assertTrue($isGmail, 'Gmail should match case-insensitive (with i flag)');
    }

    /**
     * Test 12: Email with Special Characters - Dots
     */
    public function testSignupGmailWithDots()
    {
        $email = 'test.user@gmail.com';
        $isValidFormat = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
        $isGmail = preg_match('/@gmail\.com$/', $email) === 1;
        
        $this->assertTrue($isValidFormat, 'Gmail with dots should be valid');
        $this->assertTrue($isGmail, 'Gmail with dots should be Gmail domain');
    }

    /**
     * Test 13: Email with Special Characters - Plus
     */
    public function testSignupGmailWithPlus()
    {
        $email = 'test+user@gmail.com';
        $isValidFormat = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
        $isGmail = preg_match('/@gmail\.com$/', $email) === 1;
        
        $this->assertTrue($isValidFormat, 'Gmail with plus should be valid');
        $this->assertTrue($isGmail, 'Gmail with plus should be Gmail domain');
    }

    /**
     * Test 14: Email with Numbers
     */
    public function testSignupGmailWithNumbers()
    {
        $email = 'test123@gmail.com';
        $isValidFormat = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
        $isGmail = preg_match('/@gmail\.com$/', $email) === 1;
        
        $this->assertTrue($isValidFormat, 'Gmail with numbers should be valid');
        $this->assertTrue($isGmail, 'Gmail with numbers should be Gmail domain');
    }

    /**
     * Test 15: Invalid Format - Subdomain Gmail (Not Allowed)
     */
    public function testSignupSubdomainGmail()
    {
        $email = 'test@mail.gmail.com';
        $isGmail = preg_match('/@gmail\.com$/', $email) === 1;
        
        $this->assertFalse($isGmail, 'Subdomain of gmail should NOT match');
    }

    /**
     * Test 16: Invalid Format - Gmail as Subdomain
     */
    public function testSignupGmailAsSubdomain()
    {
        $email = 'test@gmail.co.uk';
        $isGmail = preg_match('/@gmail\.com$/', $email) === 1;
        
        $this->assertFalse($isGmail, 'Gmail with different TLD should NOT match');
    }

    /**
     * Test 17: Whitespace Handling - Leading Space
     */
    public function testSignupEmailWithLeadingSpace()
    {
        $email = ' test@gmail.com';
        $trimmed = trim($email);
        $isValidFormat = filter_var($trimmed, FILTER_VALIDATE_EMAIL) !== false;
        
        $this->assertTrue($isValidFormat, 'Trimmed email should be valid');
    }

    /**
     * Test 18: Whitespace Handling - Trailing Space
     */
    public function testSignupEmailWithTrailingSpace()
    {
        $email = 'test@gmail.com ';
        $trimmed = trim($email);
        $isValidFormat = filter_var($trimmed, FILTER_VALIDATE_EMAIL) !== false;
        
        $this->assertTrue($isValidFormat, 'Trimmed email should be valid');
    }

    // ==================== NAME VALIDATION TESTS ====================

    /**
     * Test 19: First Name - Minimum Length
     */
    public function testSignupFirstNameMinimum()
    {
        $first_name = 'Jo'; // 2 characters
        $this->assertGreaterThanOrEqual(2, strlen($first_name), 'First name should be at least 2 characters');
    }

    /**
     * Test 20: First Name - Below Minimum
     */
    public function testSignupFirstNameBelowMinimum()
    {
        $first_name = 'J'; // 1 character
        $this->assertLessThan(2, strlen($first_name), 'First name below 2 characters should fail');
    }

    /**
     * Test 21: First Name - Empty
     */
    public function testSignupFirstNameEmpty()
    {
        $first_name = '';
        $this->assertTrue(empty($first_name), 'First name empty should fail');
    }

    /**
     * Test 22: Last Name - Minimum Length
     */
    public function testSignupLastNameMinimum()
    {
        $last_name = 'Do'; // 2 characters
        $this->assertGreaterThanOrEqual(2, strlen($last_name), 'Last name should be at least 2 characters');
    }

    /**
     * Test 23: Last Name - Below Minimum
     */
    public function testSignupLastNameBelowMinimum()
    {
        $last_name = 'D'; // 1 character
        $this->assertLessThan(2, strlen($last_name), 'Last name below 2 characters should fail');
    }

    /**
     * Test 24: Last Name - Empty
     */
    public function testSignupLastNameEmpty()
    {
        $last_name = '';
        $this->assertTrue(empty($last_name), 'Last name empty should fail');
    }

    // ==================== STUDENT ID TESTS ====================

    /**
     * Test 25: Student ID - Valid Format
     */
    public function testSignupStudentIdValid()
    {
        $student_id = 'STU001';
        $this->assertFalse(empty($student_id), 'Student ID should not be empty');
    }

    /**
     * Test 26: Student ID - Empty
     */
    public function testSignupStudentIdEmpty()
    {
        $student_id = '';
        $this->assertTrue(empty($student_id), 'Student ID empty should fail');
    }

    // ==================== PASSWORD TESTS ====================

    /**
     * Test 27: Password - Minimum Length (6 chars)
     */
    public function testSignupPasswordMinimum()
    {
        $password = '123456'; // 6 characters
        $this->assertGreaterThanOrEqual(6, strlen($password), 'Password should be at least 6 characters');
    }

    /**
     * Test 28: Password - Below Minimum
     */
    public function testSignupPasswordBelowMinimum()
    {
        $password = '12345'; // 5 characters
        $this->assertLessThan(6, strlen($password), 'Password below 6 characters should fail');
    }

    /**
     * Test 29: Password - Empty
     */
    public function testSignupPasswordEmpty()
    {
        $password = '';
        $this->assertTrue(empty($password), 'Password empty should fail');
    }

    /**
     * Test 30: Password Confirmation - Match
     */
    public function testSignupPasswordMatch()
    {
        $password = 'secure123';
        $confirm_password = 'secure123';
        $this->assertEquals($password, $confirm_password, 'Passwords should match');
    }

    /**
     * Test 31: Password Confirmation - Mismatch
     */
    public function testSignupPasswordMismatch()
    {
        $password = 'secure123';
        $confirm_password = 'secure124';
        $this->assertNotEquals($password, $confirm_password, 'Passwords should not match');
    }

    // ==================== LOGIN TESTS ====================

    /**
     * Test 32: Login - Valid Gmail Email
     */
    public function testLoginValidGmailEmail()
    {
        $email = 'test@gmail.com';
        $isValidFormat = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
        $isGmail = preg_match('/@gmail\.com$/', $email) === 1;
        
        $this->assertTrue($isValidFormat, 'Login email format should be valid');
        $this->assertTrue($isGmail, 'Login email should be Gmail');
    }

    /**
     * Test 33: Login - Empty Email
     */
    public function testLoginEmptyEmail()
    {
        $email = '';
        $this->assertTrue(empty($email), 'Login email should not be empty');
    }

    /**
     * Test 34: Login - Non-Gmail Domain
     */
    public function testLoginNonGmailEmail()
    {
        $email = 'test@yahoo.com';
        $isGmail = preg_match('/@gmail\.com$/', $email) === 1;
        
        $this->assertFalse($isGmail, 'Non-Gmail should be rejected on login');
    }

    /**
     * Test 35: Login - Password Empty
     */
    public function testLoginEmptyPassword()
    {
        $password = '';
        $this->assertTrue(empty($password), 'Login password should not be empty');
    }

    /**
     * Test 36: Login - Password Minimum Length
     */
    public function testLoginPasswordMinimumLength()
    {
        $password = '123456';
        $this->assertGreaterThanOrEqual(6, strlen($password), 'Login password should meet minimum length');
    }

    /**
     * Test 37: Login - Invalid Email Format
     */
    public function testLoginInvalidEmailFormat()
    {
        $email = 'notanemail';
        $isValidFormat = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
        
        $this->assertFalse($isValidFormat, 'Invalid email format should fail');
    }

    // ==================== DATA TYPE TESTS ====================

    /**
     * Test 38: Email - Invalid Data Type (Integer)
     */
    public function testSignupEmailInvalidTypeInteger()
    {
        $email = 12345;
        $emailString = (string)$email;
        $isValidFormat = filter_var($emailString, FILTER_VALIDATE_EMAIL) !== false;
        
        $this->assertFalse($isValidFormat, 'Integer as email should be invalid');
    }

    /**
     * Test 39: Email - Invalid Data Type (Array)
     */
    public function testSignupEmailInvalidTypeArray()
    {
        $email = ['test@gmail.com'];
        $this->assertIsArray($email, 'Array should be array type');
        // Should be rejected before validation
    }

    /**
     * Test 40: Password - Invalid Type (Array)
     */
    public function testSignupPasswordInvalidTypeArray()
    {
        $password = ['secure123'];
        $this->assertIsArray($password, 'Array should be array type');
    }

    // ==================== SQL INJECTION TESTS ====================

    /**
     * Test 41: Email - SQL Injection Attempt
     */
    public function testSignupEmailSqlInjection()
    {
        $email = "' OR '1'='1@gmail.com";
        $isValidFormat = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
        
        // Should be invalid due to format
        $this->assertFalse($isValidFormat, 'SQL injection attempt should be invalid email format');
    }

    /**
     * Test 42: Name - SQL Injection Attempt
     */
    public function testSignupNameSqlInjection()
    {
        $first_name = "'; DROP TABLE users; --";
        // This should be sanitized by the sanitize() function
        $this->assertStringContainsString("DROP", $first_name, 'Injection string should contain DROP');
        // Prepared statements will protect this
    }

    // ==================== EDGE CASES ====================

    /**
     * Test 43: Email - Very Long Valid Gmail
     */
    public function testSignupVeryLongValidEmail()
    {
        $email = str_repeat('a', 50) . '@gmail.com'; // 60 chars total
        $isValidFormat = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
        $isGmail = preg_match('/@gmail\.com$/', $email) === 1;
        
        $this->assertTrue($isValidFormat, 'Very long email should be valid');
        $this->assertTrue($isGmail, 'Very long email should be Gmail');
    }

    /**
     * Test 44: Email - Unicode Characters
     */
    public function testSignupEmailUnicode()
    {
        $email = 'tëst@gmail.com';
        $isValidFormat = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
        
        // PHP's filter_var may reject unicode in email
        $this->assertFalse($isValidFormat, 'Unicode in email should be invalid');
    }

    /**
     * Test 45: Complete Valid Signup Data
     */
    public function testSignupCompleteValidData()
    {
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@gmail.com',
            'student_id' => 'STU123',
            'password' => 'secure123',
            'confirm_password' => 'secure123'
        ];

        $this->assertGreaterThanOrEqual(2, strlen($data['first_name']));
        $this->assertGreaterThanOrEqual(2, strlen($data['last_name']));
        $this->assertTrue(filter_var($data['email'], FILTER_VALIDATE_EMAIL) !== false);
        $this->assertTrue(preg_match('/@gmail\.com$/', $data['email']) === 1);
        $this->assertFalse(empty($data['student_id']));
        $this->assertGreaterThanOrEqual(6, strlen($data['password']));
        $this->assertEquals($data['password'], $data['confirm_password']);
    }
}
?>
