<?php
/**
 * @author Igor A Tarasov <develop@dicr.org>
 * @version 12.07.20 13:59:25
 */

declare(strict_types = 1);
namespace dicr\tests;

use dicr\justin\JustinApi;
use dicr\justin\JustinRequest;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use function count;

/**
 * Class JustinRequestTest
 */
class JustinRequestTest extends TestCase
{
    /**
     * Возвращает API.
     *
     * @return JustinApi
     * @throws InvalidConfigException
     */
    public function api()
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return Yii::$app->get('justin');
    }

    /**
     * Тест запросов.
     *
     * @throws Exception
     */
    public function testRegions()
    {
        $api = $this->api();

        $request = $api->createRequest([
            'requestName' => JustinRequest::REQUEST_NAME_REGION
        ]);

        $data = $request->send();
        self::assertIsArray($data);
        self::assertTrue(count($data) > 10);

        $item = reset($data);
        self::assertArrayHasKey('uuid', $item);
        self::assertArrayHasKey('descr', $item);
    }
}
