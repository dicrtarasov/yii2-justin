<?php
/**
 * @author Igor A Tarasov <develop@dicr.org>
 * @version 27.07.20 09:24:35
 */

declare(strict_types = 1);
namespace dicr\justin;

use dicr\http\CachingClient;
use dicr\http\HttpCompressionBehavior;
use Locale;
use Yii;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\base\Module;

use function asort;
use function date;
use function in_array;
use function sha1;
use function strtolower;

/**
 * Модуль для работы с Justin.
 *
 * @property-read CachingClient $httpClient
 * @link https://justin.ua/api/api_justin_documentation.pdf
 */
class JustinModule extends Module implements Justin
{
    /** @var string URL API */
    public $url = self::API_URL;

    /** @var string логин justin */
    public $login;

    /** @var string пароль justin */
    public $passwd;

    /** @var array конфиг клиента */
    public $httpClientConfig = [
        'class' => CachingClient::class,
        'as compression' => HttpCompressionBehavior::class
    ];

    /**
     * @inheritDoc
     * @throws InvalidConfigException
     */
    public function init() : void
    {
        parent::init();

        $this->url = trim((string)$this->url);
        if (empty($this->url)) {
            throw new InvalidConfigException('url');
        }

        $this->login = trim((string)$this->login);
        if (empty($this->login)) {
            throw new InvalidConfigException('login');
        }

        $this->passwd = trim((string)$this->passwd);
        if (empty($this->passwd)) {
            throw new InvalidConfigException('passwd');
        }

        $this->controllerNamespace = __NAMESPACE__;
    }

    /** @var CachingClient */
    private $_httpClient;

    /**
     * HTTP-Client
     *
     * @return CachingClient
     */
    public function getHttpClient() : CachingClient
    {
        if ($this->_httpClient === null) {
            $this->_httpClient = new CachingClient([
                'baseUrl' => $this->url,
                'as compression' => HttpCompressionBehavior::class
            ]);
        }

        return $this->_httpClient;
    }

    /**
     * Возвращает подпись.
     *
     * @return string
     */
    public function sign() : string
    {
        return sha1($this->passwd . ':' . date('Y-m-d'));
    }

    /**
     * Язык по-умолчанию.
     *
     * @return ?string
     */
    public static function defaultLanguage() : ?string
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
     * Создает запрос.
     *
     * @param array $config
     * @return JustinRequest
     */
    public function createRequest(array $config = []) : JustinRequest
    {
        return new JustinRequest($this, $config);
    }

    /** @var string[] */
    private $_regions;

    /**
     * Возвращает список областей.
     *
     * @return string[] uuid => name
     * @throws Exception
     */
    public function regions() : array
    {
        if (! isset($this->_regions)) {
            $request = $this->createRequest([
                'requestType' => JustinRequest::REQUEST_TYPE_GET_DATA,
                'requestName' => JustinRequest::REQUEST_NAME_REGION,
                'responseType' => JustinRequest::RESPONSE_TYPE_CATALOG
            ]);

            $this->_regions = [];

            foreach ($request->send() as $region) {
                $uuid = trim((string)($region['uuid'] ?? ''));
                $name = trim((string)($region['descr'] ?? ''));
                if (empty($uuid) || empty($name)) {
                    throw new Exception('Некорректные данные регионов');
                }

                $this->_regions[$uuid] = $name;
            }

            asort($this->_regions);
        }

        return $this->_regions;
    }

    /**
     * Города региона.
     *
     * @param string $regionUUID
     * @return string[] uuid => name
     * @throws Exception
     */
    public function cities(string $regionUUID) : array
    {
        $request = $this->createRequest([
            'requestType' => JustinRequest::REQUEST_TYPE_GET_DATA,
            'requestName' => JustinRequest::REQUEST_NAME_CITIES,
            'responseType' => JustinRequest::RESPONSE_TYPE_CATALOG,
            'filters' => [
                new JustinFilter([
                    'field' => 'objectOwner',
                    'value' => $regionUUID
                ])
            ]
        ]);

        $data = $request->send();
        $cities = [];
        foreach ($data as $city) {
            $regionUUID = trim((string)($city['uuid'] ?? ''));
            $name = trim((string)($city['descr'] ?? ''));
            if (empty($regionUUID) || empty($name)) {
                throw new Exception('Некорректные данные городов');
            }

            $cities[$regionUUID] = $name;
        }

        asort($cities);

        return $cities;
    }

    /**
     * Отделения города.
     *
     * @param string $cityUUID
     * @return array string[] uuid => name (address)
     * @throws Exception
     */
    public function departs(string $cityUUID) : array
    {
        $request = $this->createRequest([
            'requestType' => JustinRequest::REQUEST_TYPE_GET_DATA,
            'requestName' => JustinRequest::REQUEST_NAME_DEPARTMENT,
            'responseType' => JustinRequest::RESPONSE_TYPE_REQUEST,
            'filters' => [
                new JustinFilter([
                    'field' => 'city',
                    'value' => $cityUUID
                ])
            ],
            'params' => [
                'language' => JustinRequest::LANGUAGE_RU
            ]
        ]);

        $data = $request->send();
        $departs = [];

        foreach ($data as $dep) {
            $uuid = trim((string)($dep['Depart']['uuid'] ?? ''));
            $name = trim((string)($dep['descr'] ?? ''));
            $address = trim((string)($dep['address'] ?? ''));
            if (empty($uuid) || empty($name) || empty($address)) {
                throw new Exception('Некорректные данные отделений');
            }

            $departs[$uuid] = $name . ': ' . $address;
        }

        asort($departs);

        return $departs;
    }
}
