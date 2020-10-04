<?php
/**
 * @author Igor A Tarasov <develop@dicr.org>
 * @version 27.07.20 08:00:47
 */

declare(strict_types = 1);
namespace dicr\justin;

use yii\base\Exception;
use yii\web\Controller;
use yii\web\Response;

/**
 * Контроллер простых запросов JSON.
 *
 * @property-read JustinModule $module
 */
class JsonController extends Controller
{
    /**
     * Список областей.
     *
     * @return Response
     * @throws Exception
     */
    public function actionRegions() : Response
    {
        return $this->asJson($this->module->regions());
    }

    /**
     * Города области.
     *
     * @param string $region UUID области.
     * @return Response
     * @throws Exception
     */
    public function actionCities(string $region) : Response
    {
        return $this->asJson($this->module->cities($region));
    }

    /**
     * Отделения города.
     *
     * @param string $city UUID города
     * @return Response
     * @throws Exception
     */
    public function actionDeparts(string $city) : Response
    {
        return $this->asJson($this->module->departs($city));
    }
}
