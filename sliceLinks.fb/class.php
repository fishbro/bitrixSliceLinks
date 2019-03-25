<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

// was made by fish_bro, use it as is or gtfo
    
\Bitrix\Main\Loader::includeModule('iblock');

class RelevantFacetUrlsComponent extends CBitrixComponent {

    const CACHE_TIME = 86400 * 31; // 31 ????

    public function onPrepareComponentParams($arParams) {
        global $USER;

        $result = array(
			"IBLOCK_TYPE" => trim($arParams["IBLOCK_TYPE"]),
            "IBLOCK_ID" => intval($arParams["IBLOCK_ID"]),
            // "DICTIONARY" => false, //now not used
            "PROPERTIES" => $arParams["PROPERTIES"],
            "SECTION_ID" => $arParams["SECTION_ID"],
            "SMARTFILTER_PATH" => $arParams["SMARTFILTER_PATH"],
            "CACHE_TYPE" => $arParams["CACHE_TYPE"],
			"CACHE_TIME" => isset($arParams["CACHE_TIME"]) ? $arParams["CACHE_TIME"] : self::CACHE_TIME,
        );

        foreach($arParams as $key => $param){
            if(stripos($key, 'PROPERTY_') !== false && stripos($key, '_NAME') !== false){
                $result['PROPERTY_NAMES'][str_replace(array('PROPERTY_','_NAME'),array('',''),$key)] = $param;
            }
        }
        
        return $result;
    }

    public function executeComponent() {
        if($this->startResultCache())
        {
            if(count($this->arParams['PROPERTIES']) && $this->arParams['PROPERTIES'][0] && $this->arParams['SECTION_ID']){
                $this->arResult['ITEMS'] = $this->get_filter_pages($this->arParams['PROPERTIES'], $this->arParams['SECTION_ID']);
            }else{
                $this->arResult['ITEMS'] = array();
            }
            $this->includeComponentTemplate();
        }
    }

    private function get_filter_pages($props, $section)
    {
        $filterBlock = [];
        
        $sections = self::get_filter_sections($section);
        foreach ($sections as $arSection) {
            $filter = self::getFilters($this->arParams['IBLOCK_ID'], $arSection['ID']);
            foreach ($filter as $propertyId => $filterValue) {
                $property = self::getPropertyById($propertyId);
                if (!in_array($property['CODE'], $props)) {
                    continue;
                }
                foreach ($filterValue as $value) {
                    if ($res = self::prepareResultArray($property, $value, $arSection)) {
                        $filterBlock[] = $res;
                    }
                }
                $filterBlock = self::array_msort($filterBlock, array('PROPERTY_SORT'=>'SORT_ASC','PROPERTY_VALUE'=>'SORT_ASC'));
            }
        }

        $result = array();
        foreach ($filterBlock as $filterElement) {
            if(!$result[$filterElement['PROPERTY_ID']]){
                $result[$filterElement['PROPERTY_ID']]['PROPERTY_ID'] = $filterElement['PROPERTY_ID'];
                $result[$filterElement['PROPERTY_ID']]['PROPERTY_NAME'] = $filterElement['PROPERTY_NAME'];
                $result[$filterElement['PROPERTY_ID']]['PROPERTY_CODE'] = $filterElement['PROPERTY_CODE'];
                $result[$filterElement['PROPERTY_ID']]['LINKS'] = array();
            }
            $result[$filterElement['PROPERTY_ID']]['LINKS'][] = array(
                'NAME' => $filterElement['PROPERTY_VALUE'],
                'COUNT' => $filterElement['PROPERTY_COUNT'],
                'LINK' => $filterElement['PROPERTY_LINK'],
            );
        }
        return $result;
    }
    private function get_filter_sections($section)
    {
        $rsSection = CIBlockSection::GetList(
            [],
            [
                "ACTIVE" => "Y",
                "GLOBAL_ACTIVE" => "Y",
                "IBLOCK_ID" => $this->arParams['IBLOCK_ID'],
            ],
            false,
            ["ID", "IBLOCK_ID", "SECTION_PAGE_URL"],
        false
        );
        $sections = [];
        while($arSection = $rsSection->GetNext()) {
            if($section && $arSection['ID'] != $section){
                continue;
            }
            $sections[] = $arSection;
        }

        return $sections;
    }

    private function getFilters($iblockID, $sectionId)
    {
        $facet = new \Bitrix\Iblock\PropertyIndex\Facet($iblockID);
        if (!$facet->isValid()) {
            return false;
    	}
        $facet->setSectionId($sectionId);
        $filter = array("ACTIVE_DATE" => "Y", "CHECK_PERMISSIONS" => "Y");
        $rs = $facet->query($filter, (array)$facetTypes);
        $propertiesList = \CIBlockSectionPropertyLink::getArray($iblockID, $sectionId);
        $facetResult = [];
        while ($facetElem = $rs->Fetch()) {
            if (\Bitrix\Iblock\PropertyIndex\Storage::isPropertyId($facetElem["FACET_ID"])) {
                $propertyId = \Bitrix\Iblock\PropertyIndex\Storage::facetIdToPropertyId($facetElem["FACET_ID"]);

                if ($propertiesList[$propertyId]["PROPERTY_TYPE"] == "S") {
                    if ($this->arParams['DICTIONARY'] === false) {
                        $this->arParams['DICTIONARY'] = new \Bitrix\Iblock\PropertyIndex\Dictionary($iblockID);
                    }
                    $facetElem["VALUE_INT"] = $facetElem["VALUE"];
                    $facetElem["VALUE"] = $this->arParams['DICTIONARY']->getStringById($facetElem["VALUE"]);
                }
                if ($propertiesList[$propertyId]["PROPERTY_TYPE"] == "N") {
                    unset(
                        $facetElem["VALUE"]
                    );
                } else {
                    unset(
                        $facetElem["MIN_VALUE_NUM"],
                        $facetElem["MAX_VALUE_NUM"]
                    );
                }
                unset(
                    $facetElem["FACET_ID"],
                    $facetElem["VALUE_FRAC_LEN"]
                );
                $facetResult[$propertyId][] = $facetElem;
            }
        }
        return $facetResult;
    }

    private function getPropertyById($propertyId)
    {
        static $propertiesList = [];
        if (!$propertiesList[$propertyId]) {
            $propertiesList[$propertyId] = CIBlockProperty::GetById($propertyId)->Fetch();
        }

        return $propertiesList[$propertyId];
    }

    private function prepareResultArray($property, $value, $section) 
    {
        $PROPERTY_TYPE = $property["PROPERTY_TYPE"];
        $PROPERTY_USER_TYPE = $property["USER_TYPE"];

        if ($property["USER_TYPE"] != "") {
            $arUserType = CIBlockProperty::GetUserType($PROPERTY_USER_TYPE);
            if(isset($arUserType["GetExtendedValue"]))
                $PROPERTY_TYPE = "Ux";
            elseif(isset($arUserType["GetPublicViewHTML"]))
                $PROPERTY_TYPE = "U";
        }
        
        $value['SORT'] = 500;
        switch($PROPERTY_TYPE) {
            case "U":
                $value['VALUE'] = call_user_func_array(
                    $arUserType["GetPublicViewHTML"],
                    array(
                        $property,
                        array("VALUE" => $value['VALUE']),
                        array("MODE" => "SIMPLE_TEXT"),
                    )
                );
    
                $arParamsTranslit = array("replace_space" => "-");
                $linkValue = CUtil::translit($value['VALUE'], "ru", $arParamsTranslit);
    
                break;
            case "L":
                $enum = CIBlockPropertyEnum::GetByID($value['VALUE']);
                if ($enum)
                {
                    $value['VALUE'] = $enum["VALUE"];
                    $value['SORT'] = $enum["SORT"];
    
                    $linkValue = toLower($enum["XML_ID"]);
                }
                else
                {
                    return null;
                }
                break;
            default:
                $arParamsTranslit = array("replace_space" => "-");
                $linkValue = CUtil::translit($value['VALUE'], "ru", $arParamsTranslit);
    
                break;
        }

        $smartfilterTemplate = $this->arParams['SMARTFILTER_PATH'];

        $link = str_replace(
            ['#SECTION_URL#', '#SMART_FILTER#'],
            [$section['SECTION_PAGE_URL'], strtolower($property['CODE'].'-is-'.$linkValue)],
            $smartfilterTemplate
        );

        return array(
            'PROPERTY_ID' => $property['ID'],
            'PROPERTY_CODE' => $property['CODE'],
            'PROPERTY_SORT' => $value['SORT'],
            'PROPERTY_NAME' => $property['NAME'],
            'PROPERTY_VALUE' => $value['VALUE'],
            'PROPERTY_COUNT' => $value['ELEMENT_COUNT'],
            'PROPERTY_LINK' => $link,
        );
    }

    private function array_msort($array, $cols)
    {
        $colarr = array();
        foreach ($cols as $col => $order) {
            $colarr[$col] = array();
            foreach ($array as $k => $row) { $colarr[$col]['_'.$k] = strtolower($row[$col]); }
        }
        $eval = 'array_multisort(';
        foreach ($cols as $col => $order) {
            $eval .= '$colarr[\''.$col.'\'],'.$order.',';
        }
        $eval = substr($eval,0,-1).');';
        eval($eval);
        $ret = array();
        foreach ($colarr as $col => $arr) {
            foreach ($arr as $k => $v) {
                $k = substr($k,1);
                if (!isset($ret[$k])) $ret[$k] = $array[$k];
                $ret[$k][$col] = $array[$k][$col];
            }
        }
        return $ret;
    }
}
