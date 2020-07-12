<?php
/**
 * @author Igor A Tarasov <develop@dicr.org>
 * @version 12.07.20 16:57:33
 */

declare(strict_types = 1);
namespace dicr\justin;

use dicr\http\CachingClient;
use dicr\http\HttpCompressionBehavior;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\httpclient\Client;

/**
 * Модуль для работы с Justin.
 *
 * @property-read CachingClient $httpClient
 * @link https://justin.ua/api/api_justin_documentation.pdf
 */
class JustinApi extends Component
{
    /** @var string адрес API */
    public const API_URL = 'https://api.justin.ua/justin_pms/hs/v2/runRequest';

    /** @var string тестовый API */
    public const TEST_URL = 'http://api.justin.ua/justin_pms_test/hs/v2/runRequest';

    /** @var string тестовый логин */
    public const TEST_LOGIN = 'Exchange';

    /** @var string тестовый пароль */
    public const TEST_PASSWD = 'Exchange';

    /** @var string логин justin */
    public $login;

    /** @var string пароль justin */
    public $passwd;

    /** @var bool режим теста */
    public $debug = false;

    /** @var array конфиг клиента */
    public $httpClientConfig = [
        'class' => CachingClient::class,
        'as compression' => HttpCompressionBehavior::class
    ];

    /**
     * @inheritDoc
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();

        if (! $this->debug) {
            $this->login = trim($this->login);
            if (empty($this->login)) {
                throw new InvalidConfigException('login');
            }

            $this->passwd = trim($this->passwd);
            if (empty($this->passwd)) {
                throw new InvalidConfigException('passwd');
            }
        }
    }

    /**
     * Создает запрос.
     *
     * @param array $config
     * @return JustinRequest
     */
    public function createRequest(array $config = [])
    {
        return new JustinRequest($this, $config);
    }

    /**
     * HTTP-Client
     *
     * @return Client
     * @throws InvalidConfigException
     */
    public function getHttpClient()
    {
        /** @var Client $client */
        static $client;

        if (! isset($client)) {
            $client = Yii::createObject($this->httpClientConfig);
        }

        return $client;
    }
}
