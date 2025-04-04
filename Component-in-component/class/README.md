## В файл init.php добавляем функцию

```php
function ShowNavChain($template = '.default')
{
    global $APPLICATION;
    $APPLICATION->IncludeComponent(
    "bitrix:breadcrumb",
    "",
    Array(
        "START_FROM" => "0",
        "PATH" => "",
        "SITE_ID" => "s1"
    );
}
```

## В шаблоне в котором необходим вывод компонента необходимо разместить следующий код:

```php
//В начале компонента
$helper = new ComponentHelper($component);

//В необходимом месте вставки "хлебных-крошек"
$helper->deferredCall('ShowNavChain', array('.default'));

//... код шаблона ...

// И в конце шаблона обязательно вызвать 
$helper->saveCache();
```

## В папке шаблона создаём файл component_epilog.php с содержимым:

```php
ComponentHelper::handle($this);
```


## Не забываем прописать класс в autoload bitrix

```php
CModule::AddAutoloadClasses(
    '',
    array(
        'ComponentHelper' => '/local/php_interface/classes/ComponentHelper.php',
    )
);
```