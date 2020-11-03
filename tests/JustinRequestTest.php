<?php
/**
 * @author Igor A Tarasov <develop@dicr.org>
 * @version 27.07.20 08:03:54
 */

declare(strict_types = 1);
namespace dicr\tests;

use dicr\justin\JustinModule;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\base\Exception;

use function array_key_first;

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
    private static function module() : JustinModule
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return Yii::$app->getModule('justin');
    }

    /**
     * Тест запросов.
     *
     * @throws Exception
     */
    public function testRegions() : void
    {
        $regions = self::module()->regions();
        self::assertIsArray($regions);
        self::assertNotEmpty($regions);

        $regionUuid = null;
        $regionName = null;

        foreach ($regions as $uuid => $name) {
            if (preg_match('~Киевская~ui', $name)) {
                $regionUuid = $uuid;
                $regionName = $name;
                break;
            }
        }

        self::assertNotEmpty($regionUuid);
        self::assertNotEmpty($regionName);
        echo 'Область ' . $regionName . ': ' . $regionUuid . "\n";

        $cities = self::module()->cities($regionUuid);
        self::assertIsArray($cities);
        self::assertNotEmpty($cities);

        $cityName = null;
        $cityUuid = null;

        foreach ($cities as $uuid => $name) {
            if (preg_match('~Киев~ui', $name)) {
                $cityUuid = $uuid;
                $cityName = $name;
                break;
            }
        }

        self::assertNotEmpty($cityUuid);
        self::assertNotEmpty($cityName);
        echo 'Город ' . $cityName . ': ' . $cityUuid . "\n";

        $departs = self::module()->departs($cityUuid);
        self::assertIsArray($departs);
        self::assertNotEmpty($departs);

        $departUuid = array_key_first($departs);
        $departName = $departs[$departUuid];

        echo 'Отделение ' . $departName . ': ' . $departUuid . "\n";
    }
}
