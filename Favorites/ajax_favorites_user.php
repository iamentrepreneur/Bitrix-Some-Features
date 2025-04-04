<?php
global $USER, $APPLICATION;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$GLOBALS['APPLICATION']->RestartBuffer();
use Bitrix\Main\Application;
use Bitrix\Main\Web\Cookie;
$application = Application::getInstance();
$context = $application->getContext();
global $APPLICATION;

$result = 0;
if($_REQUEST['id'])
{
    $_REQUEST['id'] = (int)$_REQUEST['id'];
    if(!$USER->IsAuthorized())
    {
        $arElements = isset($_COOKIE['favorites']) ? unserialize($_COOKIE['favorites']) : array();

        if(!in_array($_POST['id'], $arElements))
        {
            $arElements[] = $_POST['id'];
            $result = 1;
        } else {
            $key = array_search($_POST['id'], $arElements);
            unset($arElements[$key]);

            $result = 2;
        }

        if(empty($arElements)){
            setcookie("favorites", '', time() - 1, "/", $_SERVER['SERVER_NAME'], false);
        } else {
            setcookie("favorites", serialize($arElements), time() + 60*60*24*60, "/", $_SERVER['SERVER_NAME'], false);
        }
    }
    else {
        $idUser = $USER->GetID();
        $rsUser = CUser::GetByID($idUser);
        $arUser = $rsUser->Fetch();
        $arElements = $arUser['UF_FAVORITES'];

        if(!is_array($arElements)) {
            $arElements = array();
        }
        if(!in_array($_REQUEST['id'], $arElements))
        {
            $arElements[] = $_REQUEST['id'];
            $result = 1;
        }
        else {
            $key = array_search($_REQUEST['id'], $arElements);
            unset($arElements[$key]);
            $result = 2;
        }
        $USER->Update($idUser, Array("UF_FAVORITES" => $arElements));
    }
}
echo json_encode($result);
exit;
