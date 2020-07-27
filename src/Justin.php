<?php
/**
 * @author Igor A Tarasov <develop@dicr.org>
 * @version 27.07.20 07:52:13
 */

declare(strict_types = 1);
namespace dicr\justin;

/**
 * Константы Justin.
 */
interface Justin
{
    /** @var string адрес API */
    public const API_URL = 'https://api.justin.ua/justin_pms/hs/v2/runRequest';

    /** @var string тестовый API */
    public const TEST_URL = 'http://api.justin.ua/justin_pms_test/hs/v2/runRequest';

    /** @var string тестовый логин */
    public const TEST_LOGIN = 'Exchange';

    /** @var string тестовый пароль */
    public const TEST_PASSWD = 'Exchange';

    /** @var string */
    public const LANGUAGE_RU = 'ru';

    /** @var string */
    public const LANGUAGE_UA = 'ua';

    /** @var string */
    public const LANGUAGE_EN = 'en';

    /** @var string[] */
    public const LANGUAGES = [
        self::LANGUAGE_RU, self::LANGUAGE_UA, self::LANGUAGE_EN
    ];

    /** @var string запрос данных */
    public const REQUEST_TYPE_GET_DATA = 'getData';

    /** @var string запрос областей */
    public const REQUEST_NAME_REGION = 'cat_Region';

    /** @var string запрос областных районов */
    public const REQUEST_NAME_AREA = 'cat_areasRegion';

    /** @var string запрос городов */
    public const REQUEST_NAME_CITIES = 'cat_Cities';

    /** @var string запрос районов города */
    public const REQUEST_NAME_CITY_REGIONS = 'cat_cityRegions';

    /** @var string запрос улиц */
    public const REQUEST_NAME_STREETS = 'cat_cityStreets';

    /** @var string запрос типов отделений */
    public const REQUEST_NAME_BRANCH_TYPES = 'cat_branchType';

    /** @var string запрос отделений */
    public const REQUEST_NAME_DEPARTMENT = 'req_DepartmentsLang';

    /** @var string запрос расписания работы отделения */
    public const REQUEST_NAME_BRANCH_SCHEDULE = 'getScheduleBranch';

    /** @var string тип ответа - список информации */
    public const RESPONSE_TYPE_CATALOG = 'catalog';

    /** @var string тип ответа - запрос */
    public const RESPONSE_TYPE_REQUEST = 'request';

    /** @var string тип ответа - блок информации */
    public const RESPONSE_TYPE_INFO = 'infoData';

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
}
