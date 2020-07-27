<?php
/**
 * @author Igor A Tarasov <develop@dicr.org>
 * @version 27.07.20 07:51:46
 */

declare(strict_types = 1);
namespace dicr\justin;

use dicr\validate\ValidateException;
use yii\base\Model;
use function is_array;
use function is_scalar;

/**
 * Фильтр данных Justin.
 */
class JustinFilter extends Model implements Justin
{
    /** @var string название поля */
    public $field;

    /** @var string операция сравнения */
    public $comparison = self::COMPARISON_EQUAL;

    /** @var string|array сравниваемое значение */
    public $value;

    /** @var string второе сравниваемое значение для сравнения "between" */
    public $rightValue;

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            ['field', 'trim'],
            ['field', 'required'],
            ['field', 'string'],

            ['comparison', 'in', 'range' => self::COMPARISONS],

            ['value', 'required'],
            ['value', function($attribute) {
                // для сравнения типа вхождение в список значение должно быть списком
                if ($this->comparison === self::COMPARISON_IN || $this->comparison === self::COMPARISON_NOT_IN) {
                    if (! is_array($this->value)) {
                        $this->addError($attribute, 'Значение должно быть списком');
                    }
                } else {
                    // для остальных операций значение должно быть скалярным
                    if (! is_scalar($this->value)) {
                        $this->addError($attribute, 'Значение должно быть скалярным');
                    }

                    $this->value = trim((string)$this->value);
                }
            }],

            ['rightValue', 'trim'],
            ['rightValue', 'required', 'when' => function() {
                return $this->comparison === self::COMPARISON_BETWEEN;
            }]
        ];
    }

    /**
     * Возвращает JSON-структуру.
     *
     * @return array
     * @throws ValidateException
     */
    public function toJson()
    {
        if (! $this->validate()) {
            throw new ValidateException($this);
        }

        $json = [
            'name' => $this->field,
            'comparison' => $this->comparison,
            'leftValue' => $this->value,
        ];

        if ($this->comparison === self::COMPARISON_BETWEEN) {
            $json['rightValue'] = $this->rightValue;
        }

        return $json;
    }
}
