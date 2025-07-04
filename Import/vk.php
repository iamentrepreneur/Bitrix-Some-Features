<?php

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;

try {
    Loader::includeModule("iblock");
} catch (LoaderException $e) {
    ShowError($e->getMessage());
}

$count = 1;
$iblockId = 9;

$data = [
    'domain' => 'GROUP_ID',
    'count' => 50,
    'access_token' => 'SERVICE_KEY',
    'v' => '5.199'
];

$ch = curl_init('https://api.vk.com/method/wall.get');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);
if (!isset($result['response']['items'])) {
    echo "Ошибка VK API:\n";
    print_r($result);
    exit;
}

$posts = $result['response']['items'];

foreach ($posts as $post) {

    $cleanText = removeEmojis($post['text']);

    $safeText = htmlspecialchars($cleanText);

    $textWithLinks = fixVkLinks($safeText);

    $text = textToParagraphsRawHtml($textWithLinks);

    $lines = preg_split('/\r\n|\r|\n/', $cleanText);
    $title = trim($lines[0] ?? '');
    if (mb_strlen($title) > 100) {
        $title = mb_substr($title, 0, 100) . '...';
    }

    $vkId = $post['id'];
    $activeFrom = date("d.m.Y H:i:s", $post['date']);

    $photoUrl = null;
    if (!empty($post['attachments'])) {
        foreach ($post['attachments'] as $attachment) {
            if ($attachment['type'] === 'photo' && isset($attachment['photo']['sizes'])) {
                $sizes = $attachment['photo']['sizes'];
                usort($sizes, fn($a, $b) => $b['width'] * $b['height'] <=> $a['width'] * $a['height']);
                $photoUrl = $sizes[0]['url'];
                break;
            }
        }
    }

    $fileArray = false;
    if ($photoUrl) {
        $fileArray = CFile::MakeFileArray($photoUrl);
    }

    // Проверяем, не добавлен ли уже такой пост
    $res = CIBlockElement::GetList([], [
        'IBLOCK_ID' => $iblockId,
        'PROPERTY_VK_ID' => $vkId
    ], false, false, ['ID']);

    if ($res->Fetch()) {
        echo "Пост $vkId уже существует, пропускаем\n";
        continue;
    }

    $el = new CIBlockElement;

    $arFields = [
        "IBLOCK_ID" => $iblockId,
        "NAME" => $title ?: "Пост ВК N" . $vkId,
        "ACTIVE" => "Y",
        "ACTIVE_FROM" => $activeFrom,
        "DETAIL_TEXT" => $text,
        "DETAIL_TEXT_TYPE" => "html",
        "PROPERTY_VALUES" => [
            "VK_ID" => $vkId,
        ],
    ];

    if ($fileArray) {
        $arFields["PREVIEW_PICTURE"] = $fileArray;
        $arFields["DETAIL_PICTURE"] = $fileArray;
    }

    $postId = $el->Add($arFields);

    if ($postId) {
        echo "Добавлен пост ID $postId (VK $vkId)\n";
    } else {
        echo "Ошибка добавления поста: " . $el->LAST_ERROR . "\n";
    }
}


function removeEmojis(string $string): string
{
    return preg_replace('/
        [\x{1F600}-\x{1F64F}]|    # лица
        [\x{1F300}-\x{1F5FF}]|    # символы и пиктограммы
        [\x{1F680}-\x{1F6FF}]|    # транспорт и карты
        [\x{2600}-\x{26FF}]|      # разные символы
        [\x{2700}-\x{27BF}]|      # дополнительные символы
        [\x{1F1E6}-\x{1F1FF}]     # флаги
    /ux', '', $string);
}

function fixVkLinks(string $text): string {
    return preg_replace_callback('/httpsvkcom([a-zA-Z0-9_]+)/', function($matches) {
        $handle = $matches[1];
        $url = "https://vk.com/" . $handle;
        return '<a href="' . $url . '" target="_blank" rel="nofollow">' . $url . '</a>';
    }, $text);
}

function textToParagraphsRawHtml(string $text): string {
    $lines = preg_split('/\r\n|\r|\n/', $text);
    $paragraphs = [];

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line !== '') {
            $paragraphs[] = '<p>' . $line . '</p>';
        }
    }

    return implode("\n", $paragraphs);
}