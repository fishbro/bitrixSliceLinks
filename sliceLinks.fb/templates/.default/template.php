<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>

<?if(count($arResult['ITEMS'])){?>
    <?foreach($arResult['ITEMS'] as $key => $arProp){?>
        <h2><?=$arProp['PROPERTY_NAME']?></h2>
        <ul class="filterLinks">
            <?foreach($arProp['LINKS'] as $link){?>
                <li><a href="<?=$link['LINK']?>" target="_blank"><?=$link['NAME']?> (<?=$link['COUNT']?>)</a></li>
            <?}?>
        </ul>
    <?}?>
<?}?>