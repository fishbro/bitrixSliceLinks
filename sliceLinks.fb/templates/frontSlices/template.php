<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>

<?if(count($arResult['ITEMS'])){?>
	<div class="item-views catalog sections1 front blocks front-sections">
		<div class="maxwidth-theme">
			<h2>Популярные объявления</h2>
			<a class="show_all pull-right btn" href="/catalog/"><span><?=GetMessage('S_TO_SHOW_ALL_SERVICES')?></span></a>

			<div class="tabs_ajax not_ajax">

				<div class="head-block">
                <?$first = true;?>
                    <?foreach($arResult['ITEMS'] as $key => $arProp){?>
                        <div class="item-link <?=($first ? 'active clicked' : '')?>">
                            <div class="font_upper_md">
                                <span class="dark-color"><?=($arParams['PROPERTY_NAMES'][$arProp['PROPERTY_CODE']])?$arParams['PROPERTY_NAMES'][$arProp['PROPERTY_CODE']]:$arProp['PROPERTY_NAME']?></span>
                            </div>
                        </div>
                        <?$first = false;?>
                    <?}?>
				</div>

				<div class="head-block media">
                    <?$first = true;?>
                    <?foreach($arResult['ITEMS'] as $key => $arProp){?>
                        <div class="item-link <?=($first ? 'active clicked' : '')?>">
                            <div class="font_upper_md">
                                <span class="dark-color"><?=($arParams['PROPERTY_NAMES'][$arProp['PROPERTY_CODE']])?$arParams['PROPERTY_NAMES'][$arProp['PROPERTY_CODE']]:$arProp['PROPERTY_NAME']?></span>
                            </div>
                        </div>
                        <?$first = false;?>
                    <?}?>
				</div>

				<div class="body-block">
					<div class="row">
						<div class="col-md-12">
                            <?$first = true;?>
                            <?foreach($arResult['ITEMS'] as $key => $arProp){?>
                                <div class="item-block <?=($first)?'active opacity1':''?>">		
                                    <div class="clearfix"></div>
                                    <ul class="filterLinks">
                                        <?foreach($arProp['LINKS'] as $link){?>
                                            <li><a href="<?=$link['LINK']?>" target="_blank"><?=$link['NAME']?> (<?=$link['COUNT']?>)</a></li>
                                        <?}?>
                                    </ul>
                                </div>
                                <?$first = false;?>
                            <?}?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
    <?}?>