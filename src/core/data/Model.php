<?php
namespace core\data;

use DateTime;
use ReflectionProperty;
use function is_string;
use function is_array;
use function in_array;
use function strlen;
use function call_user_func;

/**
 * Used for storing and validating data only.
 */
abstract class Model {
    private static ?Model $emptyInstance = null;
    /**
     * Makes a property required when posting.<br>
     * `[..., SELF::RULE_REQUIRED]`
     * @var string
     */
    protected const RULE_REQUIRED = "required";

    /**
     * Makes a property need to have a string length greater than the minimum.<br>
     * `[..., SELF::RULE_MIN => 1]`
     * @var string
     */
    protected const RULE_MIN = "min";

    /**
     * Makes a property need to have a string length less than the maximum.<br>
     * `[..., SELF::RULE_MAX => 10]`
     * @var string
     */
    protected const RULE_MAX = "max";

    /**
     * Makes a property need to match another property.<br>
     * `[..., SELF::RULE_MATCH => "password"]` - property name<br>
     * `[..., SELF::RULE_MATCH => ["password"]]` - [property name]<br>
     * `[..., SELF::RULE_MATCH => ["password", "Password"]]` - [property name, error label]<br>
     * @var string
     */
    protected const RULE_MATCH = "match";

    protected const RULE_LEN_EQUALS = "len_equals";

    protected const RULE_DATE = "date";
    protected const RULE_IN = "in";

    public array $errors = [];

    /**
     * Abstract function for all models to implement. It determines what rules a property of a child needs to meet.
     * It is not required for all properties to have a rule.<br>
     * 
     * To start writing rules, you must write the name of the property as a string which is associated to another array. 
     * Multiple rules can be placed on a singular property.
     * @return array
     */
    abstract protected function rules(): array;

    /**
     * Uses the implemented `rules()` to check if the models properties are correct and matches the rules provided.
     * This has no access to the database therefore it can't check if something exists in the database, etc.
     * @return bool `true` if all properties match the model's rules.
     */
    public function validate(): bool {
        $this->parseSelf($this->defaultChecker(...));
        return empty($this->errors);
    }

    /**
     * Sets properties of the model to the associated values.
     * @param array $data An associative array in which the keys must match the property names of the model.
     * @return void
     */
    public function loadData(array $data): void {
        error_log(static::class . " loadData: " . json_encode($data));

        foreach ($data as $key => $value) {
            if (!property_exists($this, $key)) {
                continue;
            }

            if ($this->hasValue($key) && ($value === null || $value === '')) {
                continue;
            }

            $this->{$key} = ($value === '' && $this->isNullable($key)) ? null : $value;
        }
    }

    public function __get(string $name): mixed {
        return $this->{$name} ?? null;
    }

    public function hasAnyError(): bool {
        return !empty($this->errors);
    }

    public function hasError(string $attribute): bool {
        return !empty($this->errors[$attribute]);
    }

    public function getFirstError(string $attribute): string {
        return $this->errors[$attribute][0];
    }

    public function isEmpty(): bool {
        return false;
    }

    protected function parseSelf(callable $checker): void {
        foreach ($this->rules() as $attribute => $rules) {
            $value = $this->{$attribute};
            foreach ($rules as $key => $ruleValue) {
                if (is_string($key)) {
                    $ruleName = $key;
                    $param = $ruleValue;
                } else {
                    $ruleName = $ruleValue;
                    $param = null;
                }
                call_user_func($checker, $ruleName, $attribute, $param, $value);
            }
        }
    }

    protected function defaultChecker(string $ruleName, string $attribute, mixed $param, mixed $value): void {
        if ($ruleName === self::RULE_REQUIRED && empty($value)) {
            $this->addErrorForRule($attribute, self::RULE_REQUIRED);
        } if ($ruleName === self::RULE_MIN && strlen($value) < $param) {
            $this->addErrorForRule($attribute, self::RULE_MIN, $param);
        } if ($ruleName === self::RULE_MAX && strlen($value) > $param) {
            $this->addErrorForRule($attribute, self::RULE_MAX, $param);
        } if ($ruleName === self::RULE_MATCH) {
            if (is_array($param) && $value !== $this->{$param[0]}) {
                $label = $param[1] ?? $param[0];
                $this->addErrorForRule($attribute, self::RULE_MATCH, $label);
            } elseif ($value !== $this->{$param}) {
                $this->addErrorForRule($attribute, self::RULE_MATCH, $param);
            }
        } if ($ruleName === self::RULE_LEN_EQUALS && strlen($value) != $param) {
            $this->addErrorForRule($attribute, self::RULE_LEN_EQUALS, $param);
        } if ($ruleName === self::RULE_DATE && !empty($value)) {
            $date = DateTime::createFromFormat("Y-m-d", $value);
            if (!$date || !$date->format("Y-m-d") === $value) {
                $this->addErrorForRule($attribute, self::RULE_DATE);
            }
        } if ($ruleName === self::RULE_IN && !in_array($value, $param)) {
            $this->addErrorForRule($attribute, self::RULE_IN);
        }
    }

    protected function addErrorForRule(string $attribute, string $ruleName, string $param = ""): void {
        $message = $this->errorMessage()[$ruleName] ?? "";
        if (!empty($param)) {
            $message = str_replace("{{$ruleName}}", $param, $message);
        }
        $this->errors[$attribute][] = $message;
    }

    protected function addError(string $attribute, string $message): void {
        $this->errors[$attribute][] = $message;
    }

    protected function errorMessage(): array {
        return [
            self::RULE_REQUIRED => "This field is required!",
            self::RULE_MIN => "Minimum length of {min} is needed!",
            self::RULE_MAX => "Maximum length of {max} is needed!",
            self::RULE_MATCH => "This field does not match {match}!",
            self::RULE_LEN_EQUALS => "This field must be exactly {len_equals} long!",
            self::RULE_DATE => "This field is not a valid date!",
            self::RULE_IN => "This field is not part of a valid list!"
        ];
    }

    private function hasValue(string $attribute): bool {
        $reflection = new ReflectionProperty(static::class, $attribute);

        if (!$reflection->isInitialized($this)) {
            return false;
        }

        $current = $this->{$attribute};
        return $current !== null && $current !== '';
    }

    private function isNullable(string $attribute): bool {
        $reflection = new ReflectionProperty(static::class, $attribute);
        $type = $reflection->getType();
        return $type === null || $type->allowsNull();
    }

    public static function empty(): static {
        if (self::$emptyInstance === null) {
            self::$emptyInstance = new class extends Model {
                public function rules(): array {
                    return [];
                }

                public function isEmpty(): bool
                {
                    return true;
                }
            };
        }

        return self::$emptyInstance;
    }
}