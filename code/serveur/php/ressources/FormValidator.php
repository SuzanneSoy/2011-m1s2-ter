<?php
/**
 * The purpose of this class is to facilitate form writing in php.
 * It does this by:
 *  - facilitating value retrieval with reduced complex $_POST['var'] or
 *    $_GET['var'] syntax
 *  - encapsulates all primary form validation checking within a single class
 *
 */
class FormValidator {

    /** array of error codes */
    private $arErrors;
    /** 'post' or 'get' */
    private $method; 

    public function FormValidator($method) {
        /** resets array of error codes */
        $this->arErrors = array();
          /** 'post' or 'get' */
        $this->method = $method;
    }

    /**
     * 
     * @param <type> $name
     * @return <type>
     */
    public function getSimpleValue($name) {
        switch ($this->method) {
            case "post":
                if (!is_array($_POST["$name"])) {
                    return $_POST["$name"];
                }
                break;
            case "get":
                if (!is_array($_GET["$name"]))
                    return $_GET["$name"];
                break;
            default:
                echo "ERROR: getSimpleValue() error";
        }
        return NULL;
    }

    public function getCompoundValue($name) {
        switch ($this->method) {
            case "post":
                if (is_array($_POST["$name"]))
                    return $_POST["$name"];
                break;
            case "get":
                if (is_array($_GET["$name"]))
                    return $_GET["$name"];
                break;
            default:
                echo "ERROR: getCompoundValue() error";
        }
        return NULL;
    }

    public function isEmpty($name, $msg) {
        $value = $this->getSimpleValue($name);
        if (trim($value) == "") {
            //$msg += " (not a string) ";
            $this->arErrors[] = array("name" => $name, "value" => $value, "msg" => $msg);
            return true;
        }
        return false;
    }

    /**
     * $type can be any of "boolean", "integer", "double", "string", "array"
     * "object", "resource", "NULL", "unknown type"
     * @param <type> $type
     * @param <type> $name
     * @param <type> $msg
     * @return <type>
     */
    public function isOfType($type, $name, $msg) {
        $string = "";
        $value = $this->getSimpleValue($name);
        if ($type == gettype($value)) {
            return true;
        } else {
            $msg += "(not a(n) " + $type + ")";
            $this->arErrors[] = array("name" => $name, "value" => $value,
                "msg" => $msg);
        }
    }

    public function isSafeAlphaString($name, $msg) {
        $value = $this->getSimpleValue($name);
        $pattern = "/^[a-zA-Z]+$/";
        if (!preg_match($pattern, $value)) {
            $this->arErrors[] = array("name" => $name, "value" => $value, "msg" => $msg);
            return false;
        }
        return true;
    }

    public function isSafeNumericString($name, $msg) {
        $value = $this->getSimpleValue($name);
        $pattern = "/^-*([0-9]+|[0-9]*\.[0-9]+)$/";
        if (preg_match($pattern, $value)) {
            $this->arErrors[] = array("name" => $name, "value" => $value, "msg" => $msg);
            return false;
        }
        return true;
    }

    public function isSafeAlphaNumeric($name, $msg) {
        $value = $this->getSimpleValue($name);
        $pattern = "/^([a-zA-Z0-9])+([\.\-_][a-zA-Z0-9]*)*/";
        // TODO: Make is so that you can only have one . or - or _ in a row
        if (!preg_match($pattern, $value)) {
            $this->arErrors[] = array("name" => $name, "value" => $value, "msg" => $msg);
            return false;
        } return true;
    }


    public function isWithin($name, $min, $max, $msg) {
        $value = $this->getSimpleValue($field);
        if (!is_numeric($value) || $value < $min || $value > $max) {
            $this->arErrors[] = array("name" => $name, "value" => $value, "msg" => $msg);
            return false;
        } return true;
    }

    public function isSafeValidEmail($name, $msg) {
        $value = $this->getSimpleValue($name);
        //$pattern = "/^([a-zA-Z0-9])+([\.a-zA-Z0-9_-])*@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-]+)+/";
        $pattern = "/^([a-zA-Z0-9])+([\.\-_][a-zA-Z0-9]*)*@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-]+)+/";
        // TODO: Make is so that you can only have one . or - or _ in a row
        if (!preg_match($pattern, $value)) {
            $this->arErrors[] = array("name" => $name, "value" => $value, "msg" => $msg);
            return false;
        } return true;
    }

    public function isSafeValidPasssword($name, $msg){
        //TODO:...
    }

    public function addCostumErrorMsg($msg){
        $this->arErrors[] = array("name" => "**custom**", "value" => "**costum**", "msg" => $msg);
    }

    public function getArErrors() {
        return $this->arErrors;
    }

    function isValidForm() {
        if (sizeof($this->arErrors) > 0) {
            return false;
        }
        return true;
    }

    public function getMethod() {
        return $this->method;
    }
}
?>
