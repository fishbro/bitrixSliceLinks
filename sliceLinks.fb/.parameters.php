<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule("iblock"))
	return;

$arIBlockType = CIBlockParameters::GetIBlockTypes();

$arIBlock=array();
$rsIBlock = CIBlock::GetList(Array("SORT" => "ASC"), Array("TYPE" => $arCurrentValues["IBLOCK_TYPE"], "ACTIVE"=>"Y"));
while($arr=$rsIBlock->Fetch())
{
	$arIBlock[$arr["ID"]] = "[".$arr["ID"]."] ".$arr["NAME"];
}

$arProperty_LNS = array();
$rsProp = CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>$arCurrentValues["IBLOCK_ID"]));
while ($arr=$rsProp->Fetch())
{
	$arProperty[$arr["CODE"]] = "[".$arr["CODE"]."] ".$arr["NAME"];
	if (in_array($arr["PROPERTY_TYPE"], array("L", "N", "S", "E")))
	{
		$arProperty_LNS[$arr["CODE"]] = "[".$arr["CODE"]."] ".$arr["NAME"];
	}
}

$arComponentParameters = array(
	"PARAMETERS" => array(
		"IBLOCK_TYPE" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_TYPE"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlockType,
			"REFRESH" => "Y",
		),
		"IBLOCK_ID" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_ID"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlock,
			"REFRESH" => "Y",
			"ADDITIONAL_VALUES" => "Y",
		),
		"PROPERTIES" => array(
			"PARENT" => "LIST_SETTINGS",
			"NAME" => GetMessage("PROPERTIES"),
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"VALUES" => $arProperty_LNS,
			"ADDITIONAL_VALUES" => "Y",
		),
		"SMARTFILTER_PATH" => array(
			"NAME" => GetMessage("SMARTFILTER_PATH"),
			"DEFAULT" => '#SECTION_URL#option/#SMART_FILTER#-apply/',
			"VARIABLES" => array(
				"SECTION_URL",
				"SMART_FILTER",
			),
		),
		"CACHE_TIME"  =>  Array("DEFAULT"=>36000000),
		"CACHE_GROUPS" => array(
			"PARENT" => "CACHE_SETTINGS",
			"NAME" => GetMessage("CACHE_GROUPS"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
	),
);

if (isset($arCurrentValues['IBLOCK_ID']) && $arCurrentValues['IBLOCK_ID'] !== ''){
	$arSections = array();
	$rsSect = CIBlockSection::GetList(
		array('left_margin' => 'asc'), 
		array(
			'IBLOCK_ID' => $arCurrentValues['IBLOCK_ID'],
			'ACTIVE' => 'Y'
		)
	);
	while ($arSect = $rsSect->GetNext()){
		$arSections[$arSect["ID"]] = "[".$arSect["ID"]."] ".$arSect["NAME"];
	}

	$arComponentParameters['PARAMETERS']['SECTION_ID'] = array(
		"PARENT" => "LIST_SETTINGS",
		"NAME" => GetMessage("SECTION_ID"),
		"TYPE" => "LIST",
		"MULTIPLE" => "N",
		"VALUES" => $arSections,
	);
}

if(isset($arCurrentValues['PROPERTIES']) && count($arCurrentValues['PROPERTIES']) > 0){
	foreach ($arCurrentValues['PROPERTIES'] as $key => $code) {
		if($code){
			$arComponentParameters['PARAMETERS']['PROPERTY_'.$code.'_NAME'] = array(
				"PARENT" => "LIST_SETTINGS",
				"NAME" => GetMessage("PROPERTY_1").$code.GetMessage("PROPERTY_2"),
				"TYPE" => "STRING",
				"VALUE" => "",
			);
		}
	}
}