<?php

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Loader;
use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\SectionTable;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;

class IblockElementManager
{
    protected int $iblockId;

    /**
     * @throws LoaderException
     * @throws Exception
     */
    public function __construct(int $iblockId)
    {
        if (!Loader::includeModule('iblock')) {
            throw new Exception("Не удалось подключить модуль iblock");
        }

        $this->iblockId = $iblockId;
    }

    // CREATE

    /**
     * @throws Exception
     */
    public function add(array $fields, array $properties = []): ?int
    {
        $element = new CIBlockElement();

        $fields['IBLOCK_ID'] = $this->iblockId;

        if (!empty($properties)) {
            $fields['PROPERTY_VALUES'] = $properties;
        }

        $id = $element->Add($fields);

        if ($id) {
            return (int)$id;
        } else {
            throw new Exception("Ошибка добавления элемента: " . $element->LAST_ERROR);
        }
    }

    // READ

    /**
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ArgumentException
     */
    public function getById(int $id): ?array
    {
        $element = ElementTable::getList([
            'select' => ['ID', 'NAME', 'CODE', 'ACTIVE', 'IBLOCK_ID'],
            'filter' => [
                '=ID' => $id,
                '=IBLOCK_ID' => $this->iblockId,
            ],
        ])->fetch();

        return $element ?: null;
    }

    /**
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ArgumentException
     */
    public function getList(array $filter = [], array $select = ['ID', 'NAME', 'CODE', 'IBLOCK_SECTION_ID', 'PREVIEW_PICTURE', 'DETAIL_PICTURE', 'PREVIEW_TEXT'], array $order = ['ID' => 'DESC'], int $limit = 10): array
    {
        $filter['IBLOCK_ID'] = $this->iblockId;

        $result = ElementTable::getList([
            'select' => $select,
            'filter' => $filter,
            'order' => $order,
            'limit' => $limit,
        ])->fetchAll();

        $iblock = CIBlock::GetArrayByID($this->iblockId);
        $template = $iblock['DETAIL_PAGE_URL'];

        foreach ($result as &$element) {
            $sectionPath = isset($element['IBLOCK_SECTION_ID']) && $element['IBLOCK_SECTION_ID']
                ? $this->getSectionCodePath($element['IBLOCK_SECTION_ID'])
                : '';

            $element['DETAIL_PAGE_URL'] = str_replace(
                ['#SECTION_CODE_PATH#', '#ELEMENT_CODE#'],
                [$sectionPath, $element['CODE']],
                $template
            );
        }

        return $result;
    }

    protected function getSectionCodePath(int $sectionId): string
    {
        $path = [];

        while ($sectionId > 0) {
            $section = SectionTable::getList([
                'select' => ['ID', 'CODE', 'IBLOCK_SECTION_ID'],
                'filter' => ['ID' => $sectionId],
            ])->fetch();

            if ($section) {
                array_unshift($path, $section['CODE']);
                $sectionId = (int)$section['IBLOCK_SECTION_ID'];
            } else {
                break;
            }
        }

        return implode('/', $path);
    }

    // UPDATE

    /**
     * @throws Exception
     */
    public function update(int $id, array $fields, array $properties = []): bool
    {
        $element = new CIBlockElement();

        if (!empty($properties)) {
            $element->SetPropertyValuesEx($id, $this->iblockId, $properties);
        }

        if ($element->Update($id, $fields)) {
            return true;
        }

        throw new Exception("Ошибка обновления элемента: " . $element->LAST_ERROR);
    }

    // DELETE

    /**
     * @throws Exception
     */
    public function delete(int $id): bool
    {
        if (CIBlockElement::Delete($id)) {
            return true;
        }

        throw new Exception("Ошибка удаления элемента ID {$id}");
    }
}
