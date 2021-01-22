<?php
/*
 * @copyright 2019-2021 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license MIT
 * @version 23.01.21 02:30:37
 */

declare(strict_types = 1);
namespace dicr\justin;

use dicr\json\JsonEntity;

use function is_array;
use function is_scalar;

/**
 * Фильтр данных Justin.
 */
class JustinFilter extends JsonEntity implements Justin
{
    /** @var string название поля */
    public $field;

    /** @var string операция сравнения */
    public $comparison = self::COMPARISON_EQUAL;

    /** @var string|array сравниваемое значение */
    public $value;

    /** @var ?string второе сравниваемое значение для сравнения "between" */
    public $rightValue;

    /**
     * @inheritDoc
     */
    public function attributeFields() : array
    {
        return [
            'field' => 'name',
            'value' => 'leftValue'
        ];
    }

    /**
     * @inheritDoc
     */
    public function rules() : array
    {
        return [
            ['field', 'trim'],
            ['field', 'required'],
            ['field', 'string'],

            ['comparison', 'required'],
            ['comparison', 'in', 'range' => self::COMPARISONS],

            ['value', 'required'],
            ['value', function ($attribute) {
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
            ['rightValue', 'default'],
            ['rightValue', 'required', 'when' => fn(): bool => $this->comparison === self::COMPARISON_BETWEEN]
        ];
    }
}
