<?php
/**
 * @author Igor A Tarasov <develop@dicr.org>
 * @version 27.07.20 07:55:41
 */

declare(strict_types = 1);
namespace dicr\justin;

use dicr\validate\ValidateException;
use InvalidArgumentException;
use Locale;
use Yii;
use yii\base\Exception;
use yii\base\Model;
use yii\httpclient\Client;
use function array_map;
use function gettype;
use function in_array;
use function is_array;

/**
 * Запрос к Justin API.
 *
 * @link https://justin.ua/api/api_justin_documentation.pdf
 */
class JustinRequest extends Model implements Justin
{
    /** @var JustinModule */
    protected $module;

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

    /** @var array дополнительные параметры запроса */
    public $params;

    /**
     * JustinRequest constructor.
     *
     * @param JustinModule $module
     * @param array $config
     */
    public function __construct(JustinModule $module, array $config = [])
    {
        if (! $module instanceof JustinModule) {
            throw new InvalidArgumentException('module');
        }

        $this->module = $module;

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
            }],

            ['params', 'default'],
            ['params', function($attribute) {
                if (! is_array($this->params)) {
                    $this->addError($attribute, 'Должен быть массивом');
                }
            }]
        ];
    }

    /**
     * API Justin.
     *
     * @return JustinModule
     */
    public function getModule()
    {
        return $this->module;
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

        return array_filter([
            'keyAccount' => $this->module->login,
            'sign' => $this->module->sign(),
            'request' => $this->requestType,
            'type' => $this->responseType,
            'name' => $this->requestName,
            'language' => $this->language,
            'TOP' => $this->limit,
            'filter' => empty($this->filters) ? null : array_map(static function(JustinFilter $filter) {
                return $filter->toJson();
            }, $this->filters),
            'params' => $this->params ?: null
        ], static function($val) {
            return $val !== null && $val !== '' && $val !== [];
        });
    }

    /**
     * Выполнить запрос.
     *
     * @return array массив данных
     * @throws Exception
     */
    public function send()
    {
        $client = $this->module->httpClient;

        $request = $client->post($this->module->url, $this->toJson());
        $request->format = Client::FORMAT_JSON;

        $response = $request->send();
        if (! $response->isOk) {
            throw new Exception('Ошибка запроса: ' . $response->statusCode);
        }

        $response->format = Client::FORMAT_JSON;
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
