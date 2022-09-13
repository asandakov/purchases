<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<?

\Bitrix\Main\UI\Extension::load("ui.vue");

?>
<?if ($arResult["IS_PURCHASES"]){?>
<div class="purchases-block" id="purchases-block">


        <div class="filter-block">

            <div class="filter-content">
                <div class="search-purchase-block">
                    <div class="search-purchase">
                        <div class="searchcnt">
                            <img src="/local/templates/.default/assets/img/icons/icon-search-gray.svg">
                            <input type="text" autocomplete="off" name="search"  placeholder="Поиск" v-model="search"  />
                        </div>
                    </div>
                </div>
                <div class="filter-date">
                    <div class="sel-cnt">
                        <select  name="filter.date" v-model="filter.date" @change="filterPurchases()">
                            <?
                            foreach($arResult["DATE"] as $arInterval){
                                           ?>
                                <option value="<?=$arInterval["CODE"]?>" ><?=$arInterval["NAME"]?></option>
                            <?}?>
                        </select>
                    </div>
                </div>
                <div class="filter-status">
                    <div class="sel-cnt">
                        <select name="filter.status" v-model="filter.status" @change="filterPurchases()">
                            <?
                            foreach($arResult["STATUS"] as $arStatus){
                            ?>
                                <option value="<?=$arStatus["ID"]?>" ><?=$arStatus["NAME"]?></option>
                            <?}?>
                        </select>
                    </div>
                </div>
            </div>
        </div>




    <div class="data-purchases" v-if="purchases.length>0">
        <table>
            <thead>
            <tr>
                <th>Дата публикации</th>
                <th>Номер</th>
                <th>Предмет закупки</th>
                <th>Статус</th>
                <th>Срок приема заявок</th>
                <th>Дата проведения переторжки/переговоров</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="(purchase, index) in purchases" :key="purchase.ID">
                <td><div class="title-td">Дата публикации</div><div class="txt-td">{{purchase.DATE_PUBLISH}}</div></td>
                <td><div class="title-td">Номер</div><div class="txt-td">{{purchase.NUMBER}}</div></td>
                <td><div class="title-td">Предмет закупки</div><div class="txt-td">{{purchase.NAME}}</div></td>
                <td><div class="title-td">Статус</div><div class="txt-td">{{purchase.STATUS}}</div></td>
                <td><div class="title-td">Срок приема заявок</div><div class="txt-td">{{purchase.DATE_REQUEST}}</div></td>
                <td><div class="title-td">Дата проведения переторжки/переговоров</div><div class="txt-td">{{purchase.DATE_DISCUSSION}}</div></td>
            </tr>
            </tbody>
        </table>
        <div class="more-purchase" @click="pagePurchases()" v-if="isPage">показать еще</div>
    </div>
    <div class="no-result"  v-else>нет данных</div>

</div>
<?}?>