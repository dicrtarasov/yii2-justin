<?php
/**
 * @author Igor A Tarasov <develop@dicr.org>
 * @version 12.07.20 10:58:01
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
class JustinFilter extends Model
{
    /** @var string */
    public const COMPARISON_EQUAL = 'equal';

    /** @var string */
    public const COMPARISON_NOT = 'not';

    /** @var string */
    public const COMPARISON_LESS = 'less';

    /** @var string */
    public const COMPARISON_LESS_OR_EQUAL = 'less or equal';

    /** @var string */
    public const COMPARISON_MORE = 'more';

    /** @var string */
    public const COMPARISON_MORE_OR_EQUAL = 'more or equal';

    /** @var string сравнение "в списке". Значение value должно быть массивом */
    public const COMPARISON_IN = 'in';

    /** @var string сравнение "не в списке". Значение value должно быть массивом */
    public const COMPARISON_NOT_IN = 'not in';

    /** @var string сравнение "between". Используется value и rightValue */
    public const COMPARISON_BETWEEN = 'between';

    /** @var string вхождение подстроки value */
    public const COMPARISON_LIKE = 'like';

    /** @var string[] операции сравнения */
    public const COMPARISONS = [
        self::COMPARISON_EQUAL, self::COMPARISON_NOT, self::COMPARISON_LESS, self::COMPARISON_LESS_OR_EQUAL,
        self::COMPARISON_MORE, self::COMPARISON_MORE_OR_EQUAL, self::COMPARISON_IN, self::COMPARISON_NOT_IN,
        self::COMPARISON_BETWEEN, self::COMPARISON_LIKE
    ];

    /** @var string название поля */
    public $field;

    /** @var string операция сравнения */
    public $comparison;

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
