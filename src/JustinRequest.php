<?php
/**
 * @author Igor A Tarasov <develop@dicr.org>
 * @version 12.07.20 13:55:44
 */

declare(strict_types = 1);
namespace dicr\justin;

use dicr\validate\ValidateException;
use InvalidArgumentException;
use Locale;
use Yii;
use yii\base\Exception;
use yii\base\Model;
use function array_merge;
use function gettype;
use function in_array;
use function is_array;
use function sha1;

/**
 * Запрос к Justin API.
 *
 * @property-read JustinApi $api
 *
 * @link https://justin.ua/api/api_justin_documentation.pdf
 */
class JustinRequest extends Model
{
    /** @var JustinApi */
    private $_api;

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

    /** @var string тип ответа - блок информации */
    public const RESPONSE_TYPE_INFO = 'infoData';

    /** @var string тип запроса */
    public $requestType = self::REQUEST_TYPE_GET_DATA;

    /** @var string название запроса */
    public $requestName;

    /** @var string тип ответа */
    public $responseType = self::RESPONSE_TYPE_CATALOG;

    /** @var string язык */
    public $language;

    /** @var string лимит возврата */
    public $limit;

    /** @var JustinFilter[]|array фильтры данных */
    public $filters;

    /**
     * JustinRequest constructor.
     *
     * @param JustinApi $api
     * @param array $config
     */
    public function __construct(JustinApi $api, array $config = [])
    {
        if (! $api instanceof JustinApi) {
            throw new InvalidArgumentException('module');
        }

        $this->_api = $api;

        parent::__construct($config);
    }

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            ['requestType', 'trim'],
            ['requestType', 'required'],

            ['requestName', 'trim'],
            ['requestName', 'required'],

            ['responseType', 'trim'],
            ['responseType', 'required'],

            ['language', 'trim'],
            ['language', 'default', 'value' => $this->defaultLanguage()],
            ['language', 'in', 'range' => self::LANGUAGES],

            ['limit', 'default'],
            ['limit', 'number', 'min' => 1],

            ['filters', 'default'],
            ['filters', function($attribute) {
                if (empty($this->filters)) {
                    $this->filters = null;
                } elseif (is_array($this->filters)) {
                    foreach ($this->filters as &$filter) {
                        if (is_array($filter)) {
                            $filter = new JustinFilter($filter);
                        }

                        if (! $filter instanceof JustinFilter) {
                            $this->addError($attribute, 'Некорректный тип фильтра: ' . gettype($filter));
                            break;
                        }

                        if (! $filter->validate()) {
                            $this->addError($attribute, 'Ошибка проверки фильтра');
                            break;
                        }
                    }

                    unset($filter);
                } else {
                    $this->addError($attribute, 'некорректный тип фильтров: ' . gettype($this->filters));
                }
            }]
        ];
    }

    /**
     * API Justin.
     *
     * @return JustinApi
     */
    public function getApi()
    {
        return $this->_api;
    }

    /**
     * Язык по-умолчанию.
     *
     * @return string|null
     */
    protected function defaultLanguage()
    {
        if (empty(Yii::$app->language)) {
            return null;
        }

        $lang = Locale::getDisplayLanguage(Yii::$app->language);
        if (empty($lang)) {
            return null;
        }

        $lang = strtolower($lang);
        return in_array($lang, self::LANGUAGES, true) ? $lang : null;
    }

    /**
     * Возвращает подпись.
     *
     * @return string
     */
    protected function sign()
    {
        $passwd = $this->api->debug ? JustinApi::TEST_PASSWD : $this->api->passwd;

        return sha1($passwd . ':' . date('Y-m-d'));
    }

    /**
     * Возвращает данные в JSON.
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
            'request' => $this->requestType,
            'type' => $this->responseType,
            'name' => $this->requestName
        ];

        if (! empty($this->language)) {
            $json['language'] = $this->language;
        }

        if (! empty($this->limit)) {
            $json['TOP'] = $this->limit;
        }

        if (! empty($this->filters)) {
            $json['filter'] = array_map(static function(JustinFilter $filter) {
                return $filter->toJson();
            }, $this->filters);
        }

        return $json;
    }

    /**
     * Выполнить запрос.
     *
     * @return array массив данных
     * @throws Exception
     */
    public function send()
    {
        $request = $this->api->httpClient->createRequest();

        $request->data = array_merge($this->toJson(), [
            'keyAccount' => $this->api->debug ? JustinApi::TEST_LOGIN : $this->api->login,
            'sign' => $this->sign()
        ]);

        $response = $request->send();
        if (! $response->isOk) {
            throw new Exception('Ошибка запроса: ' . $response->statusCode);
        }

        $json = $response->data;
        if (empty($json)) {
            throw new Exception('Некорректный ответ Justin: ' . $response->content);
        }

        if (empty($json['response']['status'])) {
            throw new Exception('Ошибка: ' . ($json['response']['message'] ?? ''));
        }

        return array_map(static function(array $item) {
            return $item['fields'];
        }, $json['data'] ?? []);
    }
}
