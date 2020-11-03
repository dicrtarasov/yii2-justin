<?php
/**
 * @author Igor A Tarasov <develop@dicr.org>
 * @version 27.07.20 07:55:41
 */

declare(strict_types = 1);
namespace dicr\justin;

use dicr\json\EntityValidator;
use dicr\json\JsonEntity;
use dicr\validate\ValidateException;
use Yii;
use yii\base\Exception;
use yii\httpclient\Client;

use function array_map;
use function array_merge;
use function is_array;

/**
 * Запрос к Justin API.
 *
 * @link https://justin.ua/api/api_justin_documentation.pdf
 */
class JustinRequest extends JsonEntity implements Justin
{
    /** @var string тип запроса */
    public $requestType = self::REQUEST_TYPE_GET_DATA;

    /** @var string название запроса */
    public $requestName;

    /** @var string тип ответа */
    public $responseType = self::RESPONSE_TYPE_CATALOG;

    /** @var ?string язык */
    public $language;

    /** @var ?int лимит возврата */
    public $limit;

    /** @var JustinFilter[]|null фильтры данных */
    public $filters;

    /** @var ?array дополнительные параметры запроса */
    public $params;

    /** @var JustinModule */
    private $module;

    /**
     * JustinRequest constructor.
     *
     * @param JustinModule $module
     * @param array $config
     */
    public function __construct(JustinModule $module, array $config = [])
    {
        $this->module = $module;

        parent::__construct($config);
    }

    /**
     * @inheritDoc
     */
    public function attributeFields() : array
    {
        return [
            'requestType' => 'request',
            'responseType' => 'type',
            'requestName' => 'name',
            'limit' => 'TOP'
        ];
    }

    /**
     * @inheritDoc
     * @return array
     */
    public function attributeEntities() : array
    {
        return [
            'filters' => [JustinFilter::class]
        ];
    }

    /**
     * @inheritDoc
     */
    public function rules() : array
    {
        return [
            ['requestType', 'trim'],
            ['requestType', 'required'],

            ['requestName', 'trim'],
            ['requestName', 'required'],

            ['responseType', 'trim'],
            ['responseType', 'required'],

            ['language', 'trim'],
            ['language', 'default', 'value' => JustinModule::defaultLanguage()],
            ['language', 'in', 'range' => self::LANGUAGES],

            ['limit', 'default'],
            ['limit', 'number', 'min' => 1],
            ['limit', 'filter', 'filter' => 'intval', 'skipOnEmpty' => true],

            ['filters', 'default'],
            ['filters', EntityValidator::class, 'class' => JustinFilter::class],

            ['params', 'default'],
            ['params', function ($attribute) {
                if (empty($this->params)) {
                    $this->params = null;
                } elseif (! is_array($this->params)) {
                    $this->addError($attribute, 'Должен быть массивом');
                }
            }, 'skipOnEmpty' => true]
        ];
    }

    /**
     * Выполнить запрос.
     *
     * @return array массив данных
     * @throws Exception
     */
    public function send() : array
    {
        if (! $this->validate()) {
            throw new ValidateException($this);
        }

        $data = array_filter(array_merge($this->json, [
            'keyAccount' => $this->module->login,
            'sign' => $this->module->sign(),
        ]), static function ($val) : bool {
            return $val !== null && $val !== '' && $val !== [];
        });

        $req = $this->module->httpClient->post('', $data);
        $req->format = Client::FORMAT_JSON;

        Yii::debug('Запрос: ' . $req->toString(), __METHOD__);
        $res = $req->send();
        Yii::debug('Ответ: ' . $res->toString(), __METHOD__);

        if (! $res->isOk) {
            throw new Exception('HTTP-error: ' . $res->statusCode);
        }

        $res->format = Client::FORMAT_JSON;

        if (empty($res->data['response']['status'])) {
            throw new Exception('Ошибка: ' . ($res->data['response']['message'] ?? ''));
        }

        return array_map(static function (array $item) {
            return $item['fields'];
        }, $res->data['data'] ?? []);
    }
}
