<?php

use Validoo\Validator;

class ValidatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * INTEGER TESTS
     */

    public function testIntegerInput()
    {
        $rules = array(
            'test' => array('integer')
        );
        $inputs = array(
            'test' => 15
        );
        $validator = Validator::validate($inputs, $rules);
        $this->assertTrue($validator->isSuccess());
    }

    public function testIntegerStringInput()
    {
        $rules = array(
            'test' => array('integer')
        );
        $inputs = array(
            'test' => "15"
        );
        $validator = Validator::validate($inputs, $rules);
        $this->assertTrue($validator->isSuccess());
    }

    public function testFloatInput()
    {
        $rules = array(
            'test' => array('integer')
        );
        $inputs = array(
            'test' => 15.5
        );
        $validator = Validator::validate($inputs, $rules);
        $this->assertFalse($validator->isSuccess());
    }

    public function testStringInput()
    {
        $rules = array(
            'test' => array('integer')
        );
        $inputs = array(
            'test' => "test12"
        );
        $validator = Validator::validate($inputs, $rules);
        $this->assertFalse($validator->isSuccess());
    }

    public function testHexadecimalIntegerInput()
    {
        $rules = array(
            'test' => array('integer')
        );
        $inputs = array(
            'test' => 0x1A
        );
        $validator = Validator::validate($inputs, $rules);
        $this->assertTrue($validator->isSuccess());
    }

    public function testNegativeIntegerInput()
    {
        $rules = array(
            'test' => array('integer')
        );
        $inputs = array(
            'test' => -15
        );
        $validator = Validator::validate($inputs, $rules);
        $this->assertTrue($validator->isSuccess());
    }

    public function testOctalNumberInput()
    {
        $rules = array(
            'test' => array('integer')
        );
        $inputs = array(
            'test' => 0123
        );
        $validator = Validator::validate($inputs, $rules);
        $this->assertTrue($validator->isSuccess());
    }

    public function testVeryBigInput()
    {
        $rules = array(
            'test' => array('integer')
        );
        $inputs = array(
            'test' => 9E19
        );
        $validator = Validator::validate($inputs, $rules);
        $this->assertFalse($validator->isSuccess());
    }

    public function testVerySmallInput()
    {
        $rules = array(
            'test' => array('integer')
        );
        $inputs = array(
            'test' => -9E19
        );
        $validator = Validator::validate($inputs, $rules);
        $this->assertFalse($validator->isSuccess());
    }

    public function testEmptyInput()
    {
        $rules = array(
            'test' => array('integer')
        );
        $inputs = array(
            'test' => ''
        );
        $validator = Validator::validate($inputs, $rules);
        $this->assertFalse($validator->isSuccess());
    }

    public function testNullInput()
    {
        $rules = array(
            'test' => array('integer')
        );
        $inputs = array(
            'test' => null
        );
        $validator = Validator::validate($inputs, $rules);
        $this->assertFalse($validator->isSuccess());
    }

    /**
     * ALPHA TESTS
     */

    public function testAlphaInput()
    {
        $rules = ['test' => ['alpha']];
        $inputs = array('test' => 'ABCDE');
        $validator = Validator::validate($inputs, $rules);
        $this->assertTrue($validator->isSuccess());
    }

    public function testAlphaNumericInput()
    {
        $rules = ['test' => ['alpha']];
        $inputs = array('test' => 'ABCDE123');
        $validator = Validator::validate($inputs, $rules);
        $this->assertFalse($validator->isSuccess());
    }

    public function testNonAlphaInput()
    {
        $rules = ['test' => ['alpha']];
        $inputs = array('test' => 'ABCDE123?!@');
        $validator = Validator::validate($inputs, $rules);
        $this->assertFalse($validator->isSuccess());
    }

    /**
     * EMAIL TESTS
     */

    public function testValidEmail()
    {
        $rules = ['test' => ['email']];
        $inputs = array('test' => 'geliscan@gmail.com');
        $validator = Validator::validate($inputs, $rules);
        $this->assertTrue($validator->isSuccess());
    }

    public function testInvalidEmail()
    {
        $rules = ['test' => ['email']];
        $inputs = array('test' => 'Validoo');
        $validator = Validator::validate($inputs, $rules);
        $this->assertFalse($validator->isSuccess());
    }

    public function testEmptyInputEmail()
    {
        $rules = ['test' => ['email']];
        $inputs = array(
            'test' => ''
        );
        $validator = Validator::validate($inputs, $rules);
        $this->assertFalse($validator->isSuccess());
    }

    public function testNullInputEmail()
    {
        $rules = ['test' => ['email']];
        $inputs = array(
            'test' => null
        );
        $validator = Validator::validate($inputs, $rules);
        $this->assertFalse($validator->isSuccess());
    }

    /**
     * ERROR FILE TESTS
     */

    public function testDefaultErrorFileInCurrentDirectory()
    {
        $validator = Validator::validate([], ['name' => ['required']]);
        $this->assertEquals($validator->getErrors(), ['name field is required']);
    }

    public function testDefaultErrorFileInALevelAboveDirectory()
    {
        chdir("..");
        $validator = Validator::validate([], ['name' => ['required']]);
        $this->assertEquals($validator->getErrors(), ['name field is required']);
    }

    /**
     * EQUALS TESTS
     */

    public function testValidInputs()
    {
        $rules = ['test1' => ['equals(:test2)']];
        $inputs = array(
            'test1' => 'foo',
            'test2' => 'foo'
        );
        $validator = Validator::validate($inputs, $rules);
        $this->assertTrue($validator->isSuccess());
    }

    public function testInvalidInputs()
    {
        $rules = ['test1' => ['equals(:test2)']];
        $inputs = array(
            'test1' => 'foo',
            'test2' => 'foo2'
        );
        $validator = Validator::validate($inputs, $rules);
        $this->assertFalse($validator->isSuccess());
    }

    public function testNullParameterNameInputs()
    {
        $rules = ['test1' => ['equals(:test2)']];
        $inputs = array(
            'test1' => 'foo'
        );
        $validator = Validator::validate($inputs, $rules);
        $this->assertFalse($validator->isSuccess());
    }

    public function testEmptyParameterNameInputs()
    {
        $rules = ['test1' => ['equals(:test2)']];
        $inputs = array(
            'test1' => 'foo',
            'test2' => ''
        );
        $validator = Validator::validate($inputs, $rules);
        $this->assertFalse($validator->isSuccess());
    }

    /**
     * IP TESTS
     */

    public function testValidIPv4Input()
    {
        $rules = ['test' => ['ip']];
        $inputs = array(
            'test' => "89.250.130.65"
        );
        $validator = Validator::validate($inputs, $rules);
        $this->assertTrue($validator->isSuccess());
    }

    public function testValidFormatInvalidIPInput()
    {
        $rules = ['test' => ['ip']];
        $inputs = array(
            'test' => "89.300.130.65"
        );
        $validator = Validator::validate($inputs, $rules);
        $this->assertFalse($validator->isSuccess());
    }

    public function testValidIPv6Input()
    {
        $rules = ['test' => ['ip']];
        $inputs = array(
            'test' => "2a03:2880:10:1f02:face:b00c::25"
        );
        $validator = Validator::validate($inputs, $rules);
        $this->assertTrue($validator->isSuccess());
    }

    public function testInvalidFormatInput()
    {
        $rules = ['test' => ['ip']];
        $inputs = array(
            'test' => "Simple Validator"
        );
        $validator = Validator::validate($inputs, $rules);
        $this->assertFalse($validator->isSuccess());
    }

    /**
     * MULTIPLE PARAMETERS TESTS
     */

    public function testValidValidation()
    {
        $testData = [
            'test1' => 'test data 1',
            'test2' => 'test data 2',
            'test3' => 'test data 3'
        ];
        $rules = array(
            'test1' => array(
                'rule1(:test2,3,:test3)' => function ($input, $test2, $value, $test3) {
                    if (($input == "test data 1") && ($value == 3) && ($test2 == "test data 2") && ($test3 == "test data 3"))
                        return true;
                    return false;
                }
            )
        );
        $validator = Validator::validate($testData, $rules);
        $this->assertTrue($validator->isSuccess());
    }

    public function testInvalidValidation()
    {
        $testData = [
            'test1' => 'test data 1',
            'test2' => 'test data 2',
            'test3' => 'test data 3'
        ];
        $rules = array(
            'test1' => array(
                'rule1(:test2,3,:test3)' => function ($input, $test2, $value, $test3) {
                    if (($input == "test data 1") && ($value == 3) && ($test2 == "test data 1") && ($test3 == "test data 1"))
                        return true;
                    return false;
                }
            )
        );
        $naming = array(
            'test2' => 'Test 2'
        );
        $validator = Validator::validate($testData, $rules, $naming);
        $this->assertFalse($validator->isSuccess());
        $validator->customErrors(array(
            'rule1' => "Foo :params(0) bar :params(1) baz :params(2)"
        ));
        $errors = $validator->getErrors();
        $this->assertEquals("Foo Test 2 bar 3 baz test3", $errors[0]);
    }

    /**
     * REQUIRED TESTS
     */

    public function testEmptyInputRequired()
    {
        $rules = ['test' => ['required']];
        $inputs = array(
            'test' => ''
        );
        $validator = Validator::validate($inputs, $rules);
        $this->assertFalse($validator->isSuccess());
    }

    public function testNullInputRequired()
    {
        $rules = ['test' => ['required']];
        $inputs = array(
            'test' => null
        );
        $validator = Validator::validate($inputs, $rules);
        $this->assertFalse($validator->isSuccess());
    }

    public function testOnlyWhiteSpaceInput()
    {
        $rules = ['test' => ['required']];
        $inputs = array(
            'test' => ' '
        );
        $validator = Validator::validate($inputs, $rules);
        $this->assertFalse($validator->isSuccess());
    }

    public function testUnassignedInput()
    {
        $rules = ['test' => ['required']];
        $inputs = array();
        $validator = Validator::validate($inputs, $rules);
        $this->assertFalse($validator->isSuccess());
    }

    public function testNullInputArray()
    {
        $rules = ['test' => ['required']];
        $inputs = null;
        $validator = Validator::validate($inputs, $rules);
        $this->assertFalse($validator->isSuccess());
    }

    public function testZeroInput()
    {
        $rules = ['test' => ['required']];
        $inputs = array(
            'test' => '0'
        );
        $validator = Validator::validate($inputs, $rules);
        $this->assertTrue($validator->isSuccess());
    }

    public function testZeroPointZeroInput()
    {
        $rules = ['test' => ['required']];
        $inputs = array(
            'test' => 0.0
        );
        $validator = Validator::validate($inputs, $rules);
        $this->assertTrue($validator->isSuccess());
    }

    /**
     * URL TESTS
     */

    public function testHttpURLInput()
    {
        $rules = ['test' => ['url']];
        $inputs = array(
            'test' => "http://www.google.com"
        );
        $validator = Validator::validate($inputs, $rules);
        $this->assertTrue($validator->isSuccess());
    }

    public function testHttpsURLInput()
    {
        $rules = ['test' => ['url']];
        $inputs = array(
            'test' => "https://www.google.com"
        );
        $validator = Validator::validate($inputs, $rules);
        $this->assertTrue($validator->isSuccess());
    }

    public function testMailtoInput()
    {
        $rules = ['test' => ['url']];
        $inputs = array(
            'test' => "mailto:geliscan@gmail.com"
        );
        $validator = Validator::validate($inputs, $rules);
        $this->assertTrue($validator->isSuccess());
    }

    public function testDomainInput()
    {
        $rules = ['test' => ['url']];
        $inputs = array(
            'test' => "www.google.com"
        );
        $validator = Validator::validate($inputs, $rules);
        $this->assertFalse($validator->isSuccess());
    }

    public function testEmailInput()
    {
        $rules = ['test' => ['url']];
        $inputs = array(
            'test' => "geliscan@gmail.com"
        );
        $validator = Validator::validate($inputs, $rules);
        $this->assertFalse($validator->isSuccess());
    }

    public function testFtpInput()
    {
        $rules = ['test' => ['url']];
        $inputs = array(
            'test' => "ftp://ftp.is.co.za.example.org/rfc/rfc1808.txt"
        );
        $validator = Validator::validate($inputs, $rules);
        $this->assertTrue($validator->isSuccess());
    }

    public function testTelnetInput()
    {
        $rules = ['test' => ['url']];
        $inputs = array(
            'test' => "telnet://melvyl.ucop.example.edu/"
        );
        $validator = Validator::validate($inputs, $rules);
        $this->assertTrue($validator->isSuccess());
    }

    public function testLdapInput()
    {
        $rules = ['test' => ['url']];
        $inputs = array(
            'test' => "ldap://[2001:db8::7]/c=GB?objectClass?one"
        );
        $validator = Validator::validate($inputs, $rules);
        $this->assertTrue($validator->isSuccess());
    }

    public function testAnyStringInput()
    {
        $rules = ['test' => ['url']];
        $inputs = array(
            'test' => "simple validator"
        );
        $validator = Validator::validate($inputs, $rules);
        $this->assertFalse($validator->isSuccess());
    }

}
