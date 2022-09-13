<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
use \Bitrix\Main;
use Bitrix\Main\Type;
use Bitrix\Main\Web\Json;
use \Bitrix\Main\Localization\Loc as Loc;
use Bitrix\Main\Entity;
use Bitrix\Main\Application;
use Bitrix\Main\Web\Cookie;

use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine;

Main\Loader::includeModule('iblock');
class PurchasesList extends CBitrixComponent  implements Controllerable
{

    const PURCHASES_IBLOCK_ID = 14;

    /**
     * проверяет подключение необходиимых модулей
     * @throws LoaderException
     */
    public function checkModules()
    {

    }


    /**
     * подготавливает входные параметры
     * @param array $arParams
     * @return array
     */
    public function onPrepareComponentParams($arParams)
    {
        return $arParams;
    }



    //////////////// НАЧАЛО: AJAX - МЕТОДЫ //////////////////////////////////////////////

    // Обязательный метод
    public function configureActions()
    {
        return [
             'getPurchases' => [
                'prefilters' => []
              ]
        ];
    }


    // закупки ajax
    public function getPurchasesAction($search = "", $filter = [], $page = 1)
    {
        $arResult = array("list" => [], "count" => 0);

        $filter = json_decode($filter, true);

        $arFilter = [];
        foreach ($filter as $key=>$val){
            $val = trim($val);
            if (empty($val)) continue;
            switch ($key){
                case "date":

                    if ($val > 2015){
                       $arFilter[">=PROPERTY_DATE_PUBLISH"] = trim(CDatabase::CharToDateFunction("01.01.$val 00:00:00"),"\'");
                       $arFilter["<=PROPERTY_DATE_PUBLISH"] = trim(CDatabase::CharToDateFunction("31.12.$val 23:59:59"),"\'");
                    }

                break;
                case "status":
                        $arFilter["PROPERTY_STATUS"] = $val;
                break;
            }
        }

        $search = trim($search);
        if ($search){
            $arFilter[] = array(
                "LOGIC" => "OR",
                array("NAME" => "%".$search."%"),
                array("PROPERTY_NUMBER" => "%".$search."%"),
            );
        }


        $arPurchases = $this->getPurchases($arFilter,$page);

        $arResult['count'] = $arPurchases["COUNT"];
        $arResult['list'] = array_values($arPurchases["LIST"]);

        return $arResult;
    }


    //////////////////// КОНЕЦ: AJAX - МЕТОДЫ /////////////////////////////////////

    private function getStatus()
    {
        $arResult = array("LIST" => []);
        $rsStatus = \CIBlockPropertyEnum::GetList(array("SORT"=>"ASC"), Array("IBLOCK_ID"=>self::PURCHASES_IBLOCK_ID, "CODE"=>"STATUS"));
        while($arStatus = $rsStatus->GetNext())
        {
            $arResult["LIST"][$arStatus["ID"]] = [
                "ID" => $arStatus["ID"],
                "CODE" => $arStatus["XML_ID"],
                "NAME" => $arStatus["VALUE"]
            ];
        }
        return $arResult;
    }


    //СПИСОК ЗАКУПОК
    private function getPurchases($arFilter = array(),$page = 1, $limit = 10)
    {
        $arResult = array("LIST" => array(), "COUNT" => 0);

        $arRealFilter = Array("IBLOCK_ID"=>self::PURCHASES_IBLOCK_ID, "ACTIVE"=>"Y");
        if (count($arFilter) > 0) $arRealFilter = array_merge($arRealFilter,$arFilter);

        $res = \CIBlockElement::GetList(Array("PROPERTY_DATE_PUBLISH" => "desc"), $arRealFilter, false, Array("nPageSize" => $limit, "iNumPage" => $page), array("ID", "IBLOCK_ID", "NAME", "ACTIVE"));
        while($ob = $res->GetNextElement()){
            $arFields = $ob->GetFields();
            $arFields["PROP"] = $ob->GetProperties();

            $arResult["LIST"][$arFields["ID"]] = [
                "ID" => $arFields["ID"],
                "NAME" => $arFields["NAME"],
                "DATE_PUBLISH" => date("d.m.Y",strtotime($arFields["PROP"]["DATE_PUBLISH"]["VALUE"])),
                "STATUS" => $arFields["PROP"]["STATUS"]["VALUE"],
                "NUMBER" => $arFields["PROP"]["NUMBER"]["VALUE"],
                "DATE_REQUEST" => date("d.m.Y",strtotime($arFields["PROP"]["DATE_REQUEST"]["VALUE"])),
                "DATE_DISCUSSION" => date("d.m.Y в H:i",strtotime($arFields["PROP"]["DATE_DISCUSSION"]["VALUE"])),
            ];
        }

        $arResult["COUNT"] = $res -> SelectedRowsCount();

        return $arResult;
    }

    //ДАННЫЕ
    private function getData($arFilter = array(), $page = 1, $limit = 10)
    {
        $arResult = array("DATA" => array(),"CACHE" => false);


        $arCacheParams = array("FILTER" => $arFilter, "PAGE" => $page, "LIMIT" => $limit);

        $cacheTime = "3600000";
        $cacheId = md5(serialize($arCacheParams));
        $cacheDir = '/' . SITE_ID . '/sibintek/purchaseslist';
        $cache = Bitrix\Main\Data\Cache::createInstance();
        if ($cache->initCache($cacheTime, $cacheId, $cacheDir)) {

            $arResult = $cache->getVars();
            $arResult["CACHE"] = true;
        } elseif ($cache->startDataCache()) {

            $arResult["DATA"] = $this->getPurchases(array(), $page, $limit);

            if (empty($arResult["DATA"]["LIST"])) {
                $cache->abortDataCache();
                return;
            }

            global $CACHE_MANAGER;
            $CACHE_MANAGER->StartTagCache($cacheDir);
            $CACHE_MANAGER->RegisterTag("iblock_id_".self::PURCHASES_IBLOCK_ID);
            $CACHE_MANAGER->EndTagCache();
            $cache->endDataCache($arResult);

        }

        return $arResult;

    }


    protected function showPage()
    {
        //фильтр: статус
        $this->arResult["STATUS"] = [["ID" => "0", "CODE" => "all","NAME" => "Все"]];
        $this->arResult["STATUS"] = array_merge($this->arResult["STATUS"],$this->getStatus()["LIST"]);

        //фильтр: время
        $year = date("Y");
        $this->arResult["DATE"] = [["CODE" => "all", "NAME" => "За все время"]];
        for($i=0; $i<3; $i++){
            $yearVal = $year-$i;
            $this->arResult["DATE"][] = ["CODE" => $yearVal, "NAME" => $yearVal." год"];
        }


        $arPurchases = $this->getPurchases([],1,1)["LIST"];

        $this->arResult["IS_PURCHASES"] = (count($arPurchases) > 0)? true : false;
    }







    public function setAccess()
    {

    }




    /**
     * выполняет логику работы компонента
     */
    public function executeComponent()
    {
        $this->checkModules();
        $this->showPage();
        $this->IncludeComponentTemplate();

    }

}
?>