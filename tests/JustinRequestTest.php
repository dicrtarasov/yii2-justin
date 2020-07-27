<?php
/**
 * @author Igor A Tarasov <develop@dicr.org>
 * @version 27.07.20 08:03:54
 */

declare(strict_types = 1);
namespace dicr\tests;

use dicr\justin\JustinFilter;
use dicr\justin\JustinModule;
use dicr\justin\JustinRequest;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use function array_shift;
use function count;

/**
 * Class JustinRequestTest
 */
class JustinRequestTest extends TestCase
{
    /** @var string */
    public const REGION_KIEV = 'acd34def-1d55-11e8-8e88-bc5ff4b8e882';

    /** @var string */
    public const CITY_KIEV = '32b69b95-9018-11e8-80c1-525400fb7782';

    /**
     * Возвращает модуль Justin.
     *
     * @return JustinModule
     */
    public function module()
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return Yii::$app->getModule('justin');
    }

    /**
     * Тест запросов.
     *
     * @throws Exception
     */
    public function testRegions()
    {
        $request = $this->module()->createRequest([
            'requestName' => JustinRequest::REQUEST_NAME_REGION
        ]);

        $regions = $request->send();
        self::assertIsArray($regions);
        self::assertTrue(count($regions) > 10);

        $region = null;
        foreach ($regions as $r) {
            if (preg_match('~Киевская~ui', $r['descr'] ?? '')) {
                $region = $r;
                break;
            }
        }

        self::assertNotEmpty($region['uuid'] ?? null);
        self::assertNotEmpty($region['descr'] ?? null);
        echo 'Область: ' . $region['uuid'] . ': ' . $region['descr'] . "\n";

        $request = $this->module()->createRequest([
            'requestName' => JustinRequest::REQUEST_NAME_CITIES,
            'filters' => [
                new JustinFilter([
                    'field' => 'objectOwner',
                    'value' => $region['uuid']
                ])
            ]
        ]);

        $cities = $request->send();
        self::assertIsArray($cities);

        $city = null;
        foreach ($cities as $c) {
            if (preg_match('~Киев~ui', $c['descr'] ?? '')) {
                $city = $c;
                break;
            }
        }

        self::assertNotEmpty($city['uuid'] ?? null);
        self::assertNotEmpty($city['descr'] ?? null);
        self::assertSame($city['objectOwner']['uuid'] ?? null, $region['uuid']);
        echo 'Город: ' . $city['uuid'] . ': ' . $city['descr'] . "\n";

        $request = $this->module()->createRequest([
            'requestName' => JustinRequest::REQUEST_NAME_DEPARTMENT,
            'responseType' => JustinRequest::RESPONSE_TYPE_REQUEST,
            'filters' => [
                new JustinFilter([
                    'field' => 'city',
                    'value' => $city['uuid']
                ])
            ],
            'params' => [
                'language' => JustinRequest::LANGUAGE_RU
            ]
        ]);

        $departs = $request->send();
        self::assertIsArray($departs);

        $depart = array_shift($departs);
        self::assertNotEmpty($depart['Depart']['uuid'] ?? null);
        self::assertNotEmpty($depart['descr'] ?? null);
        self::assertNotEmpty($depart['address'] ?? null);
        self::assertSame($depart['city']['uuid'] ?? null, $city['uuid']);
        echo 'Отделение: ' . $depart['Depart']['uuid'] . ': ' . $depart['descr'] . ': ' . $depart['address'] . "\n";
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function testShortApi()
    {
        self::assertNotEmpty($this->module()->regions());
        self::assertNotEmpty($this->module()->cities(self::REGION_KIEV));
        self::assertNotEmpty($this->module()->departs(self::CITY_KIEV));
    }
}
